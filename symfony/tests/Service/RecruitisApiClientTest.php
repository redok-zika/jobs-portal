<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\RecruitisApiClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RecruitisApiClientTest extends TestCase
{
    private MockHandler $mockHandler;
    private RecruitisApiClient $client;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $httpClient = new Client(['handler' => $handlerStack]);
        $cache = new ArrayAdapter();

        $this->client = new RecruitisApiClient($httpClient, $cache, 'test_api_key');
    }

  /**
   * @dataProvider validJobsDataProvider
   */
    public function testGetJobsReturnsValidData(array $responseData, int $expectedCount): void
    {
        $this->mockHandler->append(
            new Response(200, [], json_encode($responseData))
        );

        $result = $this->client->getJobs(1, 10);

        $this->assertCount($expectedCount, $result['payload']);
        $this->assertArrayHasKey('meta', $result);
    }

  /**
   * @dataProvider validJobDataProvider
   */
    public function testGetJobReturnsValidData(array $responseData): void
    {
        $this->mockHandler->append(
            new Response(200, [], json_encode($responseData))
        );

        $result = $this->client->getJob('1');

        $this->assertArrayHasKey('job_id', $result);
        $this->assertArrayHasKey('title', $result);
    }

  /**
   * @dataProvider validApplicationDataProvider
   */
    public function testSubmitApplicationSuccess(array $applicationData): void
    {
        $this->mockHandler->append(
            new Response(200, [], json_encode([
            'meta' => ['code' => 'api.created'],
            'payload' => ['id' => '123']
            ]))
        );

        $result = $this->client->submitApplication('1', $applicationData);

        $this->assertArrayHasKey('payload', $result);
        $this->assertEquals('123', $result['payload']['id']);
    }

  /**
   * @dataProvider validSalaryDataProvider
   */
    public function testSubmitApplicationWithSalary(array $applicationData): void
    {
        $this->mockHandler->append(
            new Response(200, [], json_encode([
            'meta' => ['code' => 'api.created'],
            'payload' => ['id' => '123']
            ]))
        );

        $result = $this->client->submitApplication('1', $applicationData);

        $this->assertArrayHasKey('payload', $result);
        $this->assertEquals('123', $result['payload']['id']);
    }

    public function testGetJobsWithInvalidPaginationThrowsException(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->client->getJobs(0, 0);
    }

    public function testGetJobWithEmptyIdThrowsException(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->client->getJob('');
    }

    public function testSubmitApplicationWithMissingRequiredFieldsThrowsException(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->client->submitApplication('1', []);
    }

    public function testSubmitApplicationWithInvalidEmailThrowsException(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->client->submitApplication('1', [
        'name' => 'Test User',
        'email' => 'invalid-email'
        ]);
    }

    public function validJobsDataProvider(): array
    {
        return [
        'single job' => [
        [
          'payload' => [
            [
              'job_id' => 1,
              'title' => 'PHP Developer',
              'description' => 'Job description'
            ]
          ],
          'meta' => ['total' => 1]
        ],
        1
        ],
        'multiple jobs' => [
        [
          'payload' => [
            [
              'job_id' => 1,
              'title' => 'PHP Developer',
              'description' => 'Job description'
            ],
            [
              'job_id' => 2,
              'title' => 'JavaScript Developer',
              'description' => 'Job description'
            ]
          ],
          'meta' => ['total' => 2]
        ],
        2
        ]
        ];
    }

    public function validJobDataProvider(): array
    {
        return [
        'complete job data' => [
        [
          'job_id' => 1,
          'title' => 'PHP Developer',
          'description' => 'Job description',
          'salary' => [
            'min' => 50000,
            'max' => 70000,
            'currency' => 'CZK',
            'unit' => 'month'
          ]
        ]
        ]
        ];
    }

    public function validApplicationDataProvider(): array
    {
        return [
        'minimal valid data' => [
        [
          'name' => 'John Doe',
          'email' => 'john@example.com',
          'phone' => '+420123456789',
          'gdpr_agreement' => true
        ]
        ]
        ];
    }

    public function validSalaryDataProvider(): array
    {
        return [
        'valid salary data' => [
        [
          'name' => 'John Doe',
          'email' => 'john@example.com',
          'phone' => '+420123456789',
          'gdpr_agreement' => true,
          'salary' => [
            'amount' => 50000,
            'currency' => 'CZK',
            'unit' => 'month',
            'type' => 0
          ]
        ]
        ]
        ];
    }
}
