<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class JobControllerTest extends WebTestCase
{
    public function testListJobs(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/jobs');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('meta', $response);
    }

    public function testListJobsWithPagination(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/jobs?page=2&limit=5');

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('meta', $response);
        $this->assertSame(2, $response['meta']['page']);
    }

    public function testGetJobDetail(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/jobs/1');

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('title', $response);
    }

    public function testJobApplication(): void
    {
        $client = static::createClient();
        $payload = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+420123456789',
            'message' => 'I am interested in this position',
        ];

        $client->request(
            'POST',
            '/api/jobs/1/apply',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testInvalidJobApplication(): void
    {
        $client = static::createClient();
        $payload = []; // Empty payload should fail

        $client->request(
            'POST',
            '/api/jobs/1/apply',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }
}
