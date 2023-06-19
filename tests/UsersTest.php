<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

require __DIR__ . '/../vendor/autoload.php';

class UsersTest extends TestCase
{
    protected $client;

    protected $username;

    protected function setUp(): void
    {
        $this->client = new Client([
            'base_uri' => 'http://localhost:9998',
            'http_errors' => false,
        ]);
    }

    public function testCreateUserSuccess()
    {
        print_r($username);
        $response = $this->client->post('/users', [
            'json' => [
                'username' => 'TestRandomUser'
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getBody(), true);
        $this->assertEquals('Operation successful for user create request.', $responseData['message']);
    }
    public function testGetResource()
    {

        $response = $this->client->get('/users');
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getBody(), true);
        $this->assertNotEmpty($responseData);
    }
    public function testCreateUserFailure()
    {
    error_log($username);
    $response = $this->client->post('/users', [
        'json' => [
            'username' => 'TestRandomUser'
            ],
        ]);

        $this->assertEquals(401, $response->getStatusCode());

        $responseData = json_decode($response->getBody(), true);
        $this->assertEquals('User already exists with the given name.', $responseData['message']);

}
}
