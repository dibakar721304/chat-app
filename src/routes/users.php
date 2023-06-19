<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

# Get users
$app->get(
    '/users',
    function (Request $request, Response $response) {


        $logger = $this->get('logger');
        $pdo = $this->get('dbConnection');
        $users = $pdo->query("SELECT user_id, username FROM Users")->fetchAll(PDO::FETCH_OBJ);
        $logger->info(SUCCESS_MESSAGE_FOR_FETCHING_USER_DETAILS);
        return make_response_json($users, $response);
    }
);

# Create user
$app->post(
    '/users',
    function (Request $request, Response $response) {
        $logger = $this->get('logger');
        $parsedBody = $request->getBody();
        $parsedBody = json_decode($parsedBody, true);
        if(!array_key_exists("username", $parsedBody)) {
            $logger->error(ERROR_MESSAGE_MISSING_DETAILS_FOR_USER);
            return make_response_message(401, ERROR_MESSAGE_MISSING_DETAILS_FOR_USER, $response);
        }
        if(isExistingUser($request)) {
            $logger->error(ERROR_MESSAGE_ALREADY_EXISTING_USER);
            return make_response_message(401, ERROR_MESSAGE_ALREADY_EXISTING_USER, $response);
        }
        $username = $parsedBody["username"];

        $pdo = $this->get('dbConnection');
        $stmt = $pdo->prepare('INSERT INTO Users (username) VALUES( :username)');
        $stmt->bindParam(':username', $username);
        try {
            $result = $stmt->execute();
            $logger->info(SUCCESS_MESSAGE_FOR_USER_CREATION);
        } catch (Exception $e) {
            $logger->error(ERROR_MESSAGE_FOR_USER_CREATION);
            return make_response_message(401, $e->getMessage(), $response);
        }

        return make_response_message(200, SUCCESS_MESSAGE_FOR_USER_CREATION, $response);
    }
);
