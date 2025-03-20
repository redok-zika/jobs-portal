<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class JobControllerTest extends WebTestCase
{
  /**
   * @throws Exception
   */
  public function testListJobs(): void
  {
    $client = static::createClient();
    $response = $client->request('GET', '/api/jobs');

    $this->assertResponseIsSuccessful();
    $this->assertResponseHeaderSame('Content-Type', 'application/json');

    $content = $client->getResponse()->getContent();
    if (!is_string($content)) {
      $this->fail('Response content is not a string');
    }

    $data = json_decode($content, true);
    if (!is_array($data)) {
      $this->fail('Response is not valid JSON');
    }

    $this->assertArrayHasKey('payload', $data);
    $this->assertArrayHasKey('meta', $data);
  }

  /**
   * @throws Exception
   * @throws ExpectationFailedException
   */
  public function testListJobsWithPagination(): void
  {
    $client = static::createClient();
    $response = $client->request('GET', '/api/jobs?page=2&limit=5');

    $this->assertResponseIsSuccessful();

    $content = $client->getResponse()->getContent();
    if (!is_string($content)) {
      $this->fail('Response content is not a string');
    }

    $data = json_decode($content, true);
    if (!is_array($data)) {
      $this->fail('Response is not valid JSON');
    }

    $this->assertArrayHasKey('meta', $data);
    $this->assertArrayHasKey('entries_total', $data['meta']);
  }

  /**
   * @throws Exception
   */
  public function testGetJobDetail(): void
  {
    $client = static::createClient();
    $response = $client->request('GET', '/api/jobs/431912');

    $this->assertResponseIsSuccessful();

    $content = $client->getResponse()->getContent();
    if (!is_string($content)) {
      $this->fail('Response content is not a string');
    }

    $data = json_decode($content, true);
    if (!is_array($data)) {
      $this->fail('Response is not valid JSON');
    }

    $this->assertArrayHasKey('payload', $data);
    $this->assertArrayHasKey('job_id', $data['payload']);
    $this->assertArrayHasKey('title', $data['payload']);
  }

  public function testJobApplication(): void
  {
    $client = static::createClient();
    $payload = [
      'name' => 'John Doe11',
      'email' => 'john@exampleX.com',
      'phone' => '+420123456780',
      'cover_letter' => 'I am interested in this position',
      'gdpr_agreement' => true
    ];

    $client->request(
      'POST',
      '/api/jobs/431912/apply',
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