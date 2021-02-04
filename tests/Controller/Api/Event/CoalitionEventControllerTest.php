<?php

namespace Tests\App\Controller\Api\Event;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\Event\EventTypeEnum;
use App\OAuth\Model\GrantTypeEnum;
use App\OAuth\Model\Scope;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group api
 */
class CoalitionEventControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    public function testAnAnonymousCannotCreateCoalitionEvent(): void
    {
        $this->client->request(Request::METHOD_POST, '/api/v3/events');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testAnAdherentCanCreateCoalitionEvent(): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_11_UUID,
            'Ca1#79T6s^kCxqLc9sp$WbtqdOOsdf1iQ',
            GrantTypeEnum::PASSWORD,
            Scope::WRITE_EVENT,
            'carl999@example.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(Request::METHOD_POST, '/api/v3/events', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ], json_encode([
            'type' => EventTypeEnum::TYPE_COALITION,
            'name' => 'My event',
        ]));

        $this->assertResponseStatusCodeSame(400);

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('violations', $response);
        $this->assertCount(3, $response['violations']);

        $this->client->request(Request::METHOD_POST, '/api/v3/events', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ], json_encode([
            'type' => EventTypeEnum::TYPE_COALITION,
            'name' => 'My event',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
            'time_zone' => 'Europe/Paris',
            'begin_at' => '2021-01-29 16:30:30',
            'finish_at' => '2021-01-29 16:30:30',
            'capacity' => 10,
            'visioUrl' => 'https://en-marche.fr/reunions/123',
            'interests' => ['agriculture'],
       ]));

        $this->assertResponseStatusCodeSame(201);

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->client->request(Request::METHOD_PUT, sprintf('/api/v3/events/%s', $response['uuid']), [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ]);

        $this->assertResponseStatusCodeSame(200);

        $response = json_decode($this->client->getResponse()->getContent(), true);

        self::assertSame('My event', $response['name']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();
    }

    protected function tearDown(): void
    {
        $this->kill();

        parent::tearDown();
    }
}
