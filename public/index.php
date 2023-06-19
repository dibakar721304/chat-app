<?php

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use DI\Container;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Formatter\LineFormatter;
use Psr\Container\ContainerInterface;

require __DIR__ . '/../vendor/autoload.php';
require '../src/db/chatDatabase.php';
require '../src/utils/make_response.php';
require '../src/utils/validation.php';
require '../src/utils/constants.php';

$container = new Container();
#container configurations for database
$container->set('dbConnection', function () {

    try {
        $sqlite = "sqlite:../src/db/chatDatabase.sqlite";
        $db = new PDO($sqlite);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $e) {
        die('Database connection failed: ' . $e->getMessage());
    }
});
// #container configurations for logging
$container->set('logger', function (ContainerInterface $container) {
    $logger = new Logger('chat-app');
    $consoleHandler = new StreamHandler('php://stdout', Logger::INFO);
    $logger->pushHandler(new StreamHandler(__DIR__ . '/logs/app.log', Logger::INFO));
    $consoleFormatter = new LineFormatter("[%datetime%] %level_name%: %message%\n");
    //$consoleHandler = new ErrorLogHandler();
    $consoleFormatter->includeStacktraces(true);
    $consoleHandler->setFormatter($consoleFormatter);
    $logger->pushHandler($consoleHandler);
    return $logger;
});
$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$app->get('/', function (Request $request, Response $response) {
    return make_response_message(200, "Server running", $response);
});
#Users
require "../src/routes/users.php";
#Chats
require "../src/routes/chatGroups.php";
#Messages
require "../src/routes/messages.php";

$app->run();
