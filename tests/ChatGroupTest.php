<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

require __DIR__ . '/../vendor/autoload.php';

class ChatGroupTest extends TestCase
{
    protected $client;

    protected function setUp(): void
    {
        $this->client = new Client([
            'base_uri' => 'http://localhost:9998',
            'http_errors' => false,
        ]);
    }

    public function testCreateChatGroup_Success()
    {

        $response = $this->client->post('/groups', [
            'json' => [
                'name' => 'testChatGroup'
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getBody(), true);
        $this->assertEquals('Operation successful for chat group create request.', $responseData['message']);
    }
    public function testCreateChatGroup_Failure()
    {

        $response = $this->client->post('/groups', [
            'json' => [
                'name' => 'testChatGroup'
            ],
        ]);

        $this->assertEquals(400, $response->getStatusCode());

        $responseData = json_decode($response->getBody(), true);
        $this->assertEquals('Chat group already exists with this name.', $responseData['message']);
    }
    public function testCreateChatGroup__bad_request()
    {

        $response = $this->client->post('/groups', [
            'json' => [
                'randomName' => 'testChatGroup'
            ],
        ]);

        $this->assertEquals(401, $response->getStatusCode());

        $responseData = json_decode($response->getBody(), true);
        $this->assertEquals('Chat group details are missing.', $responseData['message']);
    }
    public function testJoinChatGroup__success()
    {

        $response = $this->client->post('/groups/join/', [
            'json' => [
                'userId' => 1,
                'groupId'=>1
            ],
        ]);

        $this->assertEquals(201, $response->getStatusCode());

        $responseData = json_decode($response->getBody(), true);
        $this->assertEquals('User has joined chat group succuessfully.', $responseData['message']);
    }
    public function testJoinChatGroup__failure()
    {

        $response = $this->client->post('/groups/join/', [
            'json' => [
                'userId' => 1,
                'groupId'=>200
            ],
        ]);

        $this->assertEquals(400, $response->getStatusCode());

        $responseData = json_decode($response->getBody(), true);
        $this->assertEquals('Chat group does not exist.', $responseData['message']);
    }


}
