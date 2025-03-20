<?php

declare(strict_types=1);

namespace App\Service;

use App\Types\ApiResponse;
use App\Types\ApplicationData;
use App\Types\AttachmentData;
use App\Types\JobFilters;
use App\Types\JobResponse;
use App\Types\SalaryData;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class RecruitisApiClient
{
    private const API_URL = 'https://api.recruitis.io/api2';
    private const CACHE_TTL = 3600; // 1 hour
    private const ERROR_MESSAGES = [
    400 => 'Neplatný požadavek',
    401 => 'Neplatný API klíč',
    403 => 'Nemáte oprávnění k této operaci',
    404 => 'Záznam nebyl nalezen',
    429 => 'Překročen limit požadavků na API',
    500 => 'Chyba serveru',
    ];

    private const META_CODES = [
    'api.ok' => true,
    'api.found' => true,
    'api.created' => true,
    'api.modified' => true,
    'api.deleted' => true,
    ];

    public function __construct(
        private readonly Client $httpClient,
        private readonly CacheInterface $cache,
        private readonly string $apiKey
    ) {
    }

  /**
   * @throws ServiceUnavailableHttpException
   * @throws Exception
   * @throws BadRequestHttpException|InvalidArgumentException
   */
    public function getJobs(int $page = 1, int $limit = 10, JobFilters $filters = new JobFilters()): ApiResponse
    {
        $cacheKey = sprintf('jobs_page_%d_limit_%d', $page, $limit);

        if ($page < 1 || $limit < 1 || $limit > 100) {
            throw new BadRequestHttpException('Invalid pagination parameters');
        }

        $queryParams = [
        'page' => $page,
        'limit' => $limit,
        ...$this->prepareFilters($filters),
        ];

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($queryParams) {
            $item->expiresAfter(self::CACHE_TTL);

            try {
                $response = $this->httpClient->request('GET', self::API_URL . '/jobs', [
                'headers' => $this->getHeaders(),
                'query' => $queryParams,
                'http_errors' => false,
                ]);

                $data = json_decode($response->getBody()->getContents(), true);
                if (!is_array($data) || !isset($data['payload'])) {
                        throw new Exception('Neplatná odpověď z API - chybí payload');
                }

                return new ApiResponse(
                    payload: (array)$data['payload'],
                    meta: (array)($data['meta'] ?? [])
                );
            } catch (GuzzleException $e) {
                throw new ServiceUnavailableHttpException(null, 'Failed to fetch jobs from API', $e);
            }
        });
    }

    private function prepareFilters(JobFilters $filters): array
    {
        $validFilters = [];

      // Podporované filtry podle dokumentace
        if ($filters->workfield !== null) {
            $validFilters['workfield'] = $filters->workfield;
        }

        if ($filters->officeId !== null) {
            $validFilters['office_id'] = $filters->officeId;
        }

        if ($filters->channelAssignation !== null) {
            $validFilters['channel_assignation'] = $filters->channelAssignation;
        }

        return $validFilters;
    }

  /**
   * @throws Exception
   */
    private function validateApiResponse(ApiResponse $data): void
    {
        if (!isset($data->meta['code'])) {
            throw new Exception('Neplatná odpověď z API - chybí meta kód');
        }

        if (!isset(self::META_CODES[$data->meta['code']])) {
            throw new Exception($data->meta['message'] ?? 'Neočekávaná chyba při komunikaci s API');
        }
    }

  /**
   * @throws ServiceUnavailableHttpException
   * @throws Exception
   * @throws BadRequestHttpException|InvalidArgumentException
   */
    public function getJob(string $id): JobResponse
    {
        $cacheKey = sprintf('job_%s', $id);

        if (empty($id)) {
            throw new BadRequestHttpException('Job ID cannot be empty');
        }

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($id) {
            $item->expiresAfter(self::CACHE_TTL);

            try {
                $response = $this->httpClient->request('GET', self::API_URL . '/jobs/' . $id, [
                'headers' => $this->getHeaders(),
                'http_errors' => false,
                ]);

                $statusCode = $response->getStatusCode();
                if ($statusCode !== 200) {
                        throw new Exception(
                            self::ERROR_MESSAGES[$statusCode] ?? 'Neočekávaná chyba při komunikaci s API'
                        );
                }

                $data = json_decode($response->getBody()->getContents(), true);
                if (!is_array($data)) {
                    throw new Exception('Neplatná odpověď z API');
                }

                return new JobResponse(
                    ...$data
                );
            } catch (GuzzleException $e) {
                throw new ServiceUnavailableHttpException(null, 'Failed to fetch job details from API', $e);
            }
        });
    }

  /**
   * @throws ServiceUnavailableHttpException
   * @throws Exception
   * @throws BadRequestHttpException
   */
    public function submitApplication(string $id, ApplicationData $data): ApiResponse
    {
        if (empty($id)) {
            throw new BadRequestHttpException('Job ID cannot be empty');
        }

      // Ensure job_id is set in the data
        $data->job_id = (int)$id;

        $this->validateApplicationData($data);

        try {
            $response = $this->httpClient->request('POST', self::API_URL . '/answers', [
            'headers' => $this->getHeaders(),
            'http_errors' => false,
            'json' => $data,
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode === 400) {
                $error = json_decode($response->getBody()->getContents(), true);
                throw new Exception($error['meta']['message'] ?? 'Invalid request data');
            } else {
                if ($statusCode !== 200 && $statusCode !== 201) {
                    throw new Exception(
                        self::ERROR_MESSAGES[$statusCode] ?? 'Neočekávaná chyba při komunikaci s API'
                    );
                }
            }

            $responseData = json_decode($response->getBody()->getContents(), true);
            if (!is_array($responseData)) {
                throw new Exception('Neplatná odpověď z API');
            }

            $apiResponse = new ApiResponse(
                payload: (array)($responseData['payload'] ?? []),
                meta: (array)($responseData['meta'] ?? [])
            );
            $this->validateApiResponse($apiResponse);

            return $apiResponse;
        } catch (GuzzleException $e) {
            throw new ServiceUnavailableHttpException(null, 'Failed to submit application', $e);
        }
    }

  /**
   * @throws BadRequestHttpException
   */
    private function validateApplicationData(ApplicationData $data): void
    {
        if (!$data->job_id) {
            throw new BadRequestHttpException('Chybí povinné pole: job_id');
        }

        if (!$data->name) {
            throw new BadRequestHttpException('Chybí povinné pole: name');
        }

        if ($data->email && !filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
            throw new BadRequestHttpException('Neplatný formát emailu');
        }

        if ($data->attachments) {
            $this->validateAttachments($data->attachments);
        }

        if ($data->salary) {
            $this->validateSalary($data->salary);
        }
    }

  /**
   * @param array<AttachmentData> $attachments
   *
   * @throws BadRequestHttpException
   */
    private function validateAttachments(array $attachments): void
    {
        foreach ($attachments as $attachment) {
            if (!$attachment->filename) {
                throw new BadRequestHttpException('Chybí název souboru u přílohy');
            }

            if (!$attachment->type || $attachment->type < 1 || $attachment->type > 6) {
                throw new BadRequestHttpException('Neplatný typ přílohy');
            }

            if ($attachment->path && !filter_var($attachment->path, FILTER_VALIDATE_URL)) {
                throw new BadRequestHttpException('Neplatná URL adresa přílohy');
            }

            if (!$attachment->path && !$attachment->base64) {
                throw new BadRequestHttpException('Chybí obsah přílohy (path nebo base64)');
            }
        }
    }

  /**
   * @throws BadRequestHttpException
   */
    private function validateSalary(SalaryData $salary): void
    {
        if (!$salary->amount || $salary->amount < 0) {
            throw new BadRequestHttpException('Neplatná částka platu');
        }

        $validCurrencies = ['CZK', 'USD', 'EUR', 'BGN', 'RON', 'HUF'];
        if (!$salary->currency || !in_array($salary->currency, $validCurrencies)) {
            throw new BadRequestHttpException('Neplatná měna');
        }

        $validUnits = ['month', 'manday', 'hour', 'year'];
        if (!$salary->unit || !in_array($salary->unit, $validUnits)) {
            throw new BadRequestHttpException('Neplatná jednotka platu');
        }

        $validTypes = [0, 1, 2, 3, 4, 5, 6];
        if (!$salary->type || !in_array($salary->type, $validTypes)) {
            throw new BadRequestHttpException('Neplatný typ pracovního poměru');
        }
    }

  /**
   * @return array<string, string>
   */
    private function getHeaders(): array
    {
        return [
        'Authorization' => 'Bearer ' . $this->apiKey,
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        ];
    }
}
