<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\RecruitisApiClient;
use App\Types\ApplicationData;
use App\Types\SalaryData;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

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
   * @throws Exception
   * @throws InvalidArgumentException
   * @throws BadRequestHttpException
   * @throws ServiceUnavailableHttpException
   * @throws \PHPUnit\Framework\Exception
   * @dataProvider validJobsDataProvider
   */
  public function testGetJobsReturnsValidData(array $responseData, int $expectedCount): void
  {
    $this->mockHandler->append(
      new Response(200, [], (string)json_encode($responseData))
    );

    $result = $this->client->getJobs(1, 10);

    $this->assertCount($expectedCount, $result->payload);
    $this->assertArrayHasKey('total', $result->meta);
  }

  /**
   * @throws Exception
   * @throws InvalidArgumentException
   * @throws BadRequestHttpException
   * @throws ServiceUnavailableHttpException
   * @throws \PHPUnit\Framework\Exception
   * @dataProvider validJobDataProvider
   */
  public function testGetJobReturnsValidData(array $responseData): void
  {
    $this->mockHandler->append(
      new Response(200, [], (string)json_encode(["payload" => $responseData, "meta" =>[]]))
    );


    //var_dump($responseData);exit;
    $result = $this->client->getJob('431912');

    $this->assertNotNull($result->payload);
    $this->assertGreaterThan(0, $result->payload["job_id"]);
    $this->assertNotEmpty($result->payload["title"]);
  }

  /**
   * @throws Exception
   * @dataProvider validApplicationDataProvider
   */
  public function testSubmitApplicationSuccess(array $applicationData): void
  {
    $this->mockHandler->append(
      new Response(200, [], (string)json_encode([
        'meta' => ['code' => 'api.created'],
        'payload' => ['id' => '123']
      ]))
    );

    $data = new ApplicationData(
      name: $applicationData['name'],
      email: $applicationData['email'],
      phone: $applicationData['phone'],
      gdprAgreement: true
    );

    $result = $this->client->submitApplication('431912', $data);

    $this->assertArrayHasKey('id', $result->payload);
    $this->assertEquals('123', $result->payload['id']);
  }

  /**
   * @throws Exception
   * @dataProvider validSalaryDataProvider
   */
  public function testSubmitApplicationWithSalary(array $applicationData): void
  {
    $this->mockHandler->append(
      new Response(200, [], (string)json_encode([
        'meta' => ['code' => 'api.created'],
        'payload' => ['id' => '123']
      ]))
    );

    $salary = new SalaryData(
      amount: $applicationData['salary']['amount'],
      currency: $applicationData['salary']['currency'],
      unit: $applicationData['salary']['unit'],
      type: $applicationData['salary']['type']
    );

    $data = new ApplicationData(
      name: $applicationData['name'],
      email: $applicationData['email'],
      phone: $applicationData['phone'],
      gdprAgreement: true,
      salary: $salary
    );

    $result = $this->client->submitApplication('431912', $data);

    $this->assertArrayHasKey('id', $result->payload);
    $this->assertEquals('123', $result->payload['id']);
  }

  /**
   * @throws Exception
   * @throws InvalidArgumentException
   * @throws BadRequestHttpException
   * @throws ServiceUnavailableHttpException
   */
  public function testGetJobsWithInvalidPaginationThrowsException(): void
  {
    $this->expectException(BadRequestHttpException::class);
    $this->client->getJobs(0, 0);
  }

  /**
   * @throws Exception
   * @throws InvalidArgumentException
   * @throws BadRequestHttpException
   * @throws ServiceUnavailableHttpException
   */
  public function testGetJobWithEmptyIdThrowsException(): void
  {
    $this->expectException(BadRequestHttpException::class);
    $this->client->getJob('');
  }

  /**
   * @throws Exception
   */
  public function testSubmitApplicationWithMissingRequiredFieldsThrowsException(): void
  {
    $this->expectException(BadRequestHttpException::class);
    $this->client->submitApplication('431912', new ApplicationData(name: ''));
  }

  /**
   * @throws Exception
   */
  public function testSubmitApplicationWithInvalidEmailThrowsException(): void
  {
    $this->expectException(BadRequestHttpException::class);
    $this->client->submitApplication('431912', new ApplicationData(
      name: 'Test User',
      email: 'invalid-email'
    ));
  }

  public static function validJobsDataProvider(): array
  {
    return [
      'single job' => [
        [
          'payload' => [
            [
              'job_id' => 431912,
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
              'job_id' => 431912,
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

  public static function validJobDataProvider(): array
  {
    return [
      'complete job data' => [
        [
          'job_id' => 431912,
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

  public static function validApplicationDataProvider(): array
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

  public static function validSalaryDataProvider(): array
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
            'type' => 1
          ]
        ]
      ]
    ];
  }
}