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

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('payload', $response);
        $this->assertNotEmpty($response['payload']);

        $firstJob = $response['payload'][0];

      // 2. Get job detail
        $client->request('GET', sprintf('/api/jobs/%s', $firstJob['job_id']));
        $this->assertResponseIsSuccessful();

        $jobResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('payload', $jobResponse);
        $this->assertSame($firstJob['job_id'], $jobResponse['payload']['job_id']);

      // 3. Submit application
        $payload = [
        'name' => 'John Doe3',
        'email' => 'john@example.com3',
        'phone' => '+4201234567893',
        'cover_letter' => 'I am very interested in this position',
        'gdpr_agreement' => true
        ];

        $client->request(
            'POST',
            sprintf('/api/jobs/%s/apply', $firstJob['job_id']),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('payload', $response);
        $this->assertArrayHasKey('id', $response['payload']);
    }
}
