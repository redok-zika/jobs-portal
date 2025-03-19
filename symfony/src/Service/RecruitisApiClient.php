<?php

declare(strict_types=1);

namespace App\Service;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

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
   * @throws InvalidArgumentException
   */
    public function getJobs(int $page = 1, int $limit = 10, array $filters = []): array
    {
        $cacheKey = sprintf('jobs_page_%d_limit_%d', $page, $limit);

        if ($page < 1 || $limit < 1 || $limit > 100) {
            throw new BadRequestHttpException('Invalid pagination parameters');
        }

        $queryParams = array_merge([
        'page' => $page,
        'limit' => $limit,
        ], $this->prepareFilters($filters));

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($queryParams) {
            $item->expiresAfter(self::CACHE_TTL);

            try {
                $response = $this->httpClient->request('GET', self::API_URL . '/jobs', [
                'headers' => $this->getHeaders(),
                'query' => $queryParams,
                'http_errors' => false,
                ]);

                $data = json_decode($response->getBody()->getContents(), true);
                if (!isset($data['payload'])) {
                        throw new Exception('Neplatná odpověď z API - chybí payload.');
                }
                return $data;
            } catch (GuzzleException $e) {
                throw new ServiceUnavailableHttpException(null, 'Failed to fetch jobs from API', $e);
            }
        });
    }

    private function prepareFilters(array $filters): array
    {
        $validFilters = [];

      // Podporované filtry podle dokumentace
        if (isset($filters['workfield'])) {
            $validFilters['workfield'] = $filters['workfield'];
        }

        if (isset($filters['office_id'])) {
            $validFilters['office_id'] = $filters['office_id'];
        }

        if (isset($filters['channel_assignation'])) {
            $validFilters['channel_assignation'] = $filters['channel_assignation'];
        }

        return $validFilters;
    }

  /**
   * @throws Exception
   */
    private function validateApiResponse(array $data): void
    {
        if (!isset($data['meta']['code'])) {
            throw new Exception('Neplatná odpověď z API - chybí meta kód');
        }

        if (!isset(self::META_CODES[$data['meta']['code']])) {
            throw new Exception($data['meta']['message'] ?? 'Neočekávaná chyba při komunikaci s API');
        }
    }

  /**
   * @throws InvalidArgumentException
   */
    public function getJob(string $id): array
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

                return json_decode($response->getBody()->getContents(), true);
            } catch (GuzzleException $e) {
                throw new ServiceUnavailableHttpException(null, 'Failed to fetch job details from API', $e);
            }
        });
    }

  /**
   * @throws Exception
   */
    public function submitApplication(string $id, array $data): array
    {
        if (empty($id)) {
            throw new BadRequestHttpException('Job ID cannot be empty');
        }

      // Ensure job_id is set in the data
        $data['job_id'] = (int)$id;

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
            } elseif ($statusCode !== 200 && $statusCode !== 201) {
                throw new Exception(
                    self::ERROR_MESSAGES[$statusCode] ?? 'Neočekávaná chyba při komunikaci s API'
                );
            }

            $responseData = json_decode($response->getBody()->getContents(), true);
            $this->validateApiResponse($responseData);
            return $responseData;
        } catch (GuzzleException $e) {
            throw new ServiceUnavailableHttpException(null, 'Failed to submit application', $e);
        }
    }

    private function validateApplicationData(array $data): void
    {
        $requiredFields = ['job_id', 'name'];
        $missingFields = array_diff($requiredFields, array_keys($data));

        if (!empty($missingFields)) {
            throw new BadRequestHttpException(
                sprintf('Chybí povinná pole: %s', implode(', ', $missingFields))
            );
        }

        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new BadRequestHttpException('Neplatný formát emailu');
        }

        if (isset($data['attachments'])) {
            $this->validateAttachments($data['attachments']);
        }

        if (isset($data['salary'])) {
            $this->validateSalary($data['salary']);
        }
    }

    private function validateAttachments(array $attachments): void
    {
        foreach ($attachments as $attachment) {
            if (!isset($attachment['filename'])) {
                throw new BadRequestHttpException('Chybí název souboru u přílohy');
            }

            if (!isset($attachment['type']) || !is_numeric($attachment['type']) || $attachment['type'] < 1 || $attachment['type'] > 6) {
                throw new BadRequestHttpException('Neplatný typ přílohy');
            }

            if (isset($attachment['path']) && !filter_var($attachment['path'], FILTER_VALIDATE_URL)) {
                throw new BadRequestHttpException('Neplatná URL adresa přílohy');
            }

            if (!isset($attachment['path']) && !isset($attachment['base64'])) {
                throw new BadRequestHttpException('Chybí obsah přílohy (path nebo base64)');
            }
        }
    }

    private function validateSalary(array $salary): void
    {
        if (!isset($salary['amount']) || !is_numeric($salary['amount']) || $salary['amount'] < 0) {
            throw new BadRequestHttpException('Neplatná částka platu');
        }

        $validCurrencies = ['CZK', 'USD', 'EUR', 'BGN', 'RON', 'HUF'];
        if (!isset($salary['currency']) || !in_array($salary['currency'], $validCurrencies)) {
            throw new BadRequestHttpException('Neplatná měna');
        }

        $validUnits = ['month', 'manday', 'hour', 'year'];
        if (!isset($salary['unit']) || !in_array($salary['unit'], $validUnits)) {
            throw new BadRequestHttpException('Neplatná jednotka platu');
        }

        $validTypes = [0, 1, 2, 3, 4, 5, 6];
        if (!isset($salary['type']) || !in_array($salary['type'], $validTypes)) {
            throw new BadRequestHttpException('Neplatný typ pracovního poměru');
        }
    }

    private function getHeaders(): array
    {
        return [
        'Authorization' => 'Bearer ' . $this->apiKey,
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        ];
    }
}
