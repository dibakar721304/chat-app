<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

function isExistingChatGroup(Request $request)
{
    $parsedBody = $request->getBody();
    $parsedBody = json_decode($parsedBody, true);
    $groupId = $parsedBody["groupId"];
    $container = $GLOBALS['container'];
    $pdo = $container->get('dbConnection');
    $stmt = $pdo->prepare('SELECT * FROM chat_groups WHERE chat_group_id= :groupId');
    $stmt->bindParam(':groupId', $groupId);
    $stmt->execute();
    $isExistingChatGroup = $stmt->fetch(PDO::FETCH_ASSOC);
    return $isExistingChatGroup;

}
function isActiveChatExist(Request $request)
{
    $parsedBody = $request->getBody();
    $parsedBody = json_decode($parsedBody, true);
    $groupId = $parsedBody["groupId"];
    $userId = $parsedBody["userId"];
    $container = $GLOBALS['container'];
    $pdo = $container->get('dbConnection');
    $stmt = $pdo->prepare('SELECT chat_group_user_id FROM chat_group_user WHERE chat_group_id= :groupId and user_id=:userId and status="ACTIVE" order by chat_group_user_id desc limit 1');
    $stmt->bindParam(':groupId', $groupId);
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    $isActiveChatExist = $stmt->fetch(PDO::FETCH_ASSOC);
    return $isActiveChatExist;

}
function hasUserAlreadyLeft(Request $request)
{
    $parsedBody = $request->getBody();
    $parsedBody = json_decode($parsedBody, true);
    $groupId = $parsedBody["groupId"];
    $userId = $parsedBody["userId"];
    $container = $GLOBALS['container'];
    $pdo = $container->get('dbConnection');
    $stmt = $pdo->prepare('select status from chat_group_user where user_id= :userId and chat_group_id= :groupId order by chat_group_user_id desc limit 1');
    $stmt->bindParam(':groupId', $groupId);
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    $status = $stmt->fetch(PDO::FETCH_ASSOC);
    if(strcmp($status['status'], "ACTIVE")==0) {
        return false;
    }
    return true;

}
function doesChatGroupNameAlreadyExists(Request $request, Response $response)
{
    $parsedBody = $request->getBody();
    $parsedBody = json_decode($parsedBody, true);
    $container = $GLOBALS['container'];
    $pdo = $container->get('dbConnection');
    $name = $parsedBody['name'];
    $stmt = $pdo->prepare('SELECT * FROM chat_groups WHERE chat_group_name = ?');
    $stmt->bindParam(1, $name);
    $stmt->execute();
    $doesChatGroupNameAlreadyExist = $stmt->fetch(PDO::FETCH_ASSOC);
    return $doesChatGroupNameAlreadyExist;

}
function isExistingUser(Request $request)
{
    $parsedBody = $request->getBody();
    $parsedBody = json_decode($parsedBody, true);
    $container = $GLOBALS['container'];
    $pdo = $container->get('dbConnection');
    $username = $parsedBody['username'];
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->bindParam(1, $username);
    $stmt->execute();
    $isExistingUser = $stmt->fetch(PDO::FETCH_ASSOC);
    return $isExistingUser;

}
