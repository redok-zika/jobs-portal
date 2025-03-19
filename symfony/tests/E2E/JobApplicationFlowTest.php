<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class JobApplicationFlowTest extends WebTestCase
{
    public function testCompleteApplicationFlow(): void
    {
        $client = static::createClient();

        // 1. Get list of jobs
        $client->request('GET', '/api/jobs');
        $this->assertResponseIsSuccessful();

        $jobsList = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($jobsList['data']);

        $firstJob = $jobsList['data'][0];

        // 2. Get job detail
        $client->request('GET', sprintf('/api/jobs/%s', $firstJob['id']));
        $this->assertResponseIsSuccessful();

        $jobDetail = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame($firstJob['id'], $jobDetail['id']);

        // 3. Submit application
        $payload = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+420123456789',
            'message' => 'I am very interested in this position',
        ];

        $client->request(
            'POST',
            sprintf('/api/jobs/%s/apply', $jobDetail['id']),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $response);
    }
}
