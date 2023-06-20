<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

require __DIR__ . '/../vendor/autoload.php';

class MessagesTest extends TestCase
{
    protected $client;




    protected function setUp(): void
    {
        $this->client = new Client([
            'base_uri' => 'http://localhost:9998',
            'http_errors' => false,
        ]);
    }

    public function testSendMessages_Success()
    {
        $response = $this->client->post('/groups/join/', [
            'json' => [
                'userId' => 1,
                'groupId'=>1
            ],
        ]);
        $response = $this->client->post('/chats/message/', [
            'json' => [
                'userId' => 1,
                'groupId'=> 1,
                'message'=> 'Sending first message'
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getBody(), true);
        $this->assertEquals('New message has been successfully created to chat.', $responseData['message']);
    }
    public function testSendMessages_bad_request()
    {

        $response = $this->client->post('/chats/message/', [
            'json' => [
                'wrongUserId' => 1,
                'groupId'=> 1,
                'message'=> 'Sending first message'
            ],
        ]);

        $this->assertEquals(401, $response->getStatusCode());

        $responseData = json_decode($response->getBody(), true);
        $this->assertEquals('Message details are missing.', $responseData['message']);
    }
    public function testSendMessages_failure()
    {

        $response = $this->client->post('/chats/message/', [
            'json' => [
                'userId' => 1,
                'groupId'=> 500,
                'message'=> 'Sending first message'
            ],
        ]);

        $this->assertEquals(400, $response->getStatusCode());

        $responseData = json_decode($response->getBody(), true);
        $this->assertEquals('Chat group does not exist.', $responseData['message']);
    }




}
