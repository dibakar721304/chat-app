<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// API endpoint for creating a new chat group
$app->post('/groups', function (Request $request, Response $response) {
    $logger = $this->get('logger');
    $parsedBody = $request->getBody();
    $parsedBody = json_decode($parsedBody, true);
    $name = $parsedBody['name'];
    if(!array_key_exists("name", $parsedBody)) {
        $logger->error(ERROR_MESSAGE_MISSING_DETAILS_FOR_CHAT_GROUP_CREATION);
        return make_response_message(401, ERROR_MESSAGE_MISSING_DETAILS_FOR_CHAT_GROUP_CREATION, $response);
    }
    if(doesChatGroupNameAlreadyExists($request, $response)) {
        $logger->error(ERROR_MESSAGE_ALREADY_EXISTING_CHAT_GROUP);
        return make_response_message(400, ERROR_MESSAGE_ALREADY_EXISTING_CHAT_GROUP, $response);
    }
    $parsedBody = $request->getBody();
    $parsedBody = json_decode($parsedBody, true);
    $name = $parsedBody['name'];
    $pdo = $this->get('dbConnection');
    $stmt = $pdo->prepare('INSERT INTO chat_groups (chat_group_name) VALUES (:name)');

    $stmt->bindParam(':name', $name);
    try {
        $result = $stmt->execute();
        $logger->debug(SUCCESS_MESSAGE_FOR_CHAT_GROUP_CREATION);
    } catch (Exception $e) {
        $logger->debug(ERROR_MESSAGE_FOR_CHAT_GROUP_CREATION);
        return make_response_message(500, $e->getMessage(), $response);
    }

    return make_response_message(200, SUCCESS_MESSAGE_FOR_CHAT_GROUP_CREATION, $response);
});

// API endpoint for listing all chat groups
$app->get('/groups', function (Request $request, Response $response) {
    $logger = $this->get('logger');
    $pdo = $this->get('dbConnection');
    $groups = $pdo->query("SELECT * FROM chat_groups")->fetchAll(PDO::FETCH_OBJ);
    $logger->debug(SUCCESS_MESSAGE_FOR_FETCHING_CHAT_GROUP_DETAILS);
    return make_response_json($groups, $response);
});

// API endpoint for joining a group
$app->post('/groups/join/', function (Request $request, Response $response) {
    $logger = $this->get('logger');
    $parsedBody = $request->getBody();
    $parsedBody = json_decode($parsedBody, true);
    $groupId = $parsedBody["groupId"];
    $userId = $parsedBody["userId"];
    if(!array_key_exists("userId", $parsedBody)||!array_key_exists("groupId", $parsedBody)) {
        $logger->error(ERROR_MESSAGE_MISSING_DETAILS_FOR_CHAT_GROUP_JOINING);
        return make_response_message(401, ERROR_MESSAGE_MISSING_DETAILS_FOR_CHAT_GROUP_JOINING, $response);
    }

    if(!isExistingChatGroup($request)) {
        $logger->error(ERROR_MESSAGE_NOT_EXISTING_CHAT_GROUP);
        return make_response_message(400, ERROR_MESSAGE_NOT_EXISTING_CHAT_GROUP, $response);
    }
    $activeChatResult = isActiveChatExist($request);
    if($activeChatResult) {
        $logger->error(ERROR_MESSAGE_FOR_ACTIVE_USER_IN_CHAT_GROUP);
        return make_response_message(400, ERROR_MESSAGE_FOR_ACTIVE_USER_IN_CHAT_GROUP, $response);
    }
    $pdo = $this->get('dbConnection');
    $stmt = $pdo->prepare('INSERT INTO chat_group_user (chat_group_id, user_id,status) VALUES (:groupId, :userId, "ACTIVE")');
    $stmt->bindParam(':groupId', $groupId);
    $stmt->bindParam(':userId', $userId);
    try {
        $result = $stmt->execute();
        $logger->debug(SUCCESS_MESSAGE_FOR_CHAT_GROUP_JOINING);
    } catch (Exception $e) {
        $logger->debug(ERROR_MESSAGE_FOR_CHAT_GROUP_JOINING);
        return make_response_message(500, $e->getMessage(), $response);
    }
    return make_response_message(201, SUCCESS_MESSAGE_FOR_CHAT_GROUP_JOINING, $response);
});
// API endpoint for leaving a chat group
$app->post('/groups/leave/', function (Request $request, Response $response) {
    $logger = $this->get('logger');
    $parsedBody = $request->getBody();
    $parsedBody = json_decode($parsedBody, true);
    $groupId = $parsedBody["groupId"];
    $userId = $parsedBody["userId"];
    if(!array_key_exists("userId", $parsedBody)||!array_key_exists("groupId", $parsedBody)) {
        $logger->error(ERROR_MESSAGE_MISSING_DETAILS_FOR_CHAT_GROUP_LEAVING);
        return make_response_message(401, ERROR_MESSAGE_MISSING_DETAILS_FOR_CHAT_GROUP_LEAVING, $response);
    }
    if(!isExistingChatGroup($request)) {
        $logger->error(ERROR_MESSAGE_NOT_EXISTING_CHAT_GROUP);
        return make_response_message(400, ERROR_MESSAGE_NOT_EXISTING_CHAT_GROUP, $response);
    }
    if(hasUserAlreadyLeft($request)) {
        $logger->error(ERROR_MESSAGE_FOR_USER_ALREADY_LEFT);
        return make_response_message(400, ERROR_MESSAGE_FOR_USER_ALREADY_LEFT, $response);
    }
    $pdo = $this->get('dbConnection');
    $timestamp = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare('UPDATE chat_group_user set status="INACTIVE",left_time = :timestamp where user_id= :userId and chat_group_id =:groupId');
    $stmt->bindParam(':groupId', $groupId);
    $stmt->bindParam(':userId', $userId);
    $stmt->bindParam(':timestamp', $timestamp);
    try {
        $result = $stmt->execute();
        $logger->debug(SUCCESS_MESSAGE_FOR_CHAT_GROUP_LEAVING);
    } catch (Exception $e) {
        $logger->error(ERROR_MESSAGE_FOR_CHAT_GROUP_LEAVING);
        return make_response_message(500, $e->getMessage(), $response);
    }
    return make_response_message(200, SUCCESS_MESSAGE_FOR_CHAT_GROUP_LEAVING, $response);
});
