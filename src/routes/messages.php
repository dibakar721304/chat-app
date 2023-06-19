<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

#Send message
$app->post(
    '/chats/message/',
    function (Request $request, Response $response) {
        $logger = $this->get('logger');
        $parsedBody = $request->getBody();
        $parsedBody = json_decode($parsedBody, true);
        if(!array_key_exists("userId", $parsedBody) || !array_key_exists("message", $parsedBody)||!array_key_exists("groupId", $parsedBody)) {
            $logger->error(ERROR_MESSAGE_MISSING_DETAILS_WHILE_SENDING_MESSAGE);
            return make_response_message(401, ERROR_MESSAGE_MISSING_DETAILS_WHILE_SENDING_MESSAGE, $response);
        }
        if(!isExistingChatGroup($request)) {
            $logger->error(ERROR_MESSAGE_NOT_EXISTING_CHAT_GROUP);
            return make_response_message(400, ERROR_MESSAGE_NOT_EXISTING_CHAT_GROUP, $response);
        }
        $userId = $parsedBody["userId"];
        $groupId = $parsedBody["groupId"];
        $message = $parsedBody["message"];
        $activeChatResult = isActiveChatExist($request);
        if(!$activeChatResult) {
            $logger->error(ERROR_MESSAGE_INACTIVE_CHAT);
            return make_response_message(400, ERROR_MESSAGE_INACTIVE_CHAT, $response);
        }
        $chatGroupUserId=$activeChatResult['chat_group_user_id'];
        $pdo = $this->get('dbConnection');
        $stmt = $pdo->prepare('select message_id from messages where user_id=? and chat_group_user_id=? order by message_id desc limit 1');
        $stmt->bindParam(1, $userId);
        $stmt->bindParam(2, $chatGroupUserId);
        $stmt->execute();
        $isPreviousMessageExist = $stmt->fetch(PDO::FETCH_ASSOC);
        # check if previous chat exists
        if(!$isPreviousMessageExist) {
            #Create a new chat
            $logger->info(CREATING_NEW_CHAT_TO_SEND_MESSAGE);
            $stmt = $pdo->prepare('INSERT INTO chat_group_user (user_id, chat_group_id, status) VALUES(?,?,"ACTIVE")');
            $stmt->bindParam(1, $userId);
            $stmt->bindParam(2, $groupId);
            try {
                $stmt->execute();
                $chatGroupUserId = $pdo->lastInsertId();
                $logger->debug(SUCCESS_CREATING_NEW_CHAT_TO_SEND_MESSAGE);
            } catch(Exception $e) {
                $logger->error(ERROR_CREATING_NEW_CHAT_TO_SEND_MESSAGE);
                return make_response_message(401, $e->getMessage(), $response);
            }
            #Add new message to the chat
            $logger->info(ADDING_NEW_MESSAGE_TO_CHAT);
            $stmt = $pdo->prepare('INSERT INTO messages (chat_group_user_id,user_id,message) VALUES(?, ?, ?)');
            $stmt->bindParam(1, $chatGroupUserId);
            $stmt->bindParam(2, $userId);
            $stmt->bindParam(3, $message);
            try {
                $stmt->execute();
                $logger->debug(SUCCESS_CREATING_NEW_MESSAGE_TO_CHAT);
            } catch(Exception $e) {
                $logger->error(ERROR_MESSAGE_CREATING_NEW_CHAT_MESSAGE);
                return make_response_message(401, $e->getMessage(), $response);
            }


        } else {
            #Add message to existing chat
            $logger->info(ADDING_MESSAGES_TO_EXISTING_CHAT);
            $stmt = $pdo->prepare('INSERT INTO Messages(chat_group_user_id,user_id,message) VALUES(?, ?, ?)');
            $stmt->bindParam(1, $chatGroupUserId);
            $stmt->bindParam(2, $userId);
            $stmt->bindParam(3, $message);
            try {
                $stmt->execute();
                $logger->debug(ADDING_MESSAGES_TO_EXISTING_CHAT);
            } catch(Exception $e) {
                $logger->error(ERROR_ADDING_MESSAGES_TO_EXISTING_CHAT);
                return make_response_message(401, $e->getMessage(), $response);
            }

        }
        $logger->debug(SUCCESS_CREATING_NEW_MESSAGE_TO_CHAT);
        return make_response_message(200, SUCCESS_CREATING_NEW_MESSAGE_TO_CHAT, $response);

    }
);

# Retrieve messages in a chat
$app->get(
    '/messages/chats/{groupId}/{userId}',
    function (Request $request, Response $response, $args) {
        $logger = $this->get('logger');
        $parsedParam = $request->getQueryParams();
        if(!array_key_exists("groupId", $parsedParam)||!array_key_exists("userId", $parsedParam)) {
            $logger->error(ERROR_MESSAGE_MISSING_DETAILS_WHILE_FETCHING_MESSAGE);
            return make_response_message(401, ERROR_MESSAGE_MISSING_DETAILS_WHILE_FETCHING_MESSAGE, $response);
        }
        $pdo = $this->get('dbConnection');
        $groupId= $parsedParam['groupId'];
        $userId= $parsedParam['userId'];
        $stmt = $pdo->prepare('SELECT * FROM chat_groups WHERE chat_group_id= :groupId');
        $stmt->bindParam(':groupId', $groupId);
        $stmt->execute();
        $isExistingChatGroup = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!$isExistingChatGroup) {
            $logger->error(ERROR_MESSAGE_NOT_EXISTING_CHAT_GROUP);
            return make_response_message(400, ERROR_MESSAGE_NOT_EXISTING_CHAT_GROUP, $response);
        }
        $pdo = $this->get('dbConnection');
        $stmt = $pdo->prepare('SELECT chat_group_user_id FROM chat_group_user WHERE chat_group_id= :groupId and user_id=:userId and status="ACTIVE" order by chat_group_user_id desc limit 1');
        $stmt->bindParam(':groupId', $groupId);
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        $activeChatResult =  $stmt->fetch(PDO::FETCH_ASSOC);
        if(!$activeChatResult) {
            $logger->error(ERROR_MESSAGE_INACTIVE_CHAT);
            return make_response_message(400, ERROR_MESSAGE_INACTIVE_CHAT, $response);
        }
        #Get messages for chat
        $logger->info(FETCHING_MESSAGES_FOR_CHAT_GROUP);
        $stmt = $pdo->prepare('select * from messages where chat_group_user_id in (select chat_group_user_id from chat_group_user where chat_group_id=:groupId) order by message_id desc');
        $stmt->bindParam(1, $groupId);
        try {
            $stmt->execute();
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $logger->debug(SUCCESS_FETCHING_MESSAGES_FOR_CHAT_GROUP);
        } catch(Exception $e) {
            $logger->error(ERROR_WHILE_FETCHING_MESSAGES_FOR_CHAT_GROUP);
            return make_response_message(401, $e->getMessage(), $response);
        }


        #If no message exist
        if(!$messages) {
            $logger->error(ERROR_MESSAGE_NOT_FOUND_FOR_CHAT_GROUP);

            return make_response_message(400, ERROR_MESSAGE_NOT_FOUND_FOR_CHAT_GROUP, $response);
        }

        return make_response_json($messages, $response);
    }
);
