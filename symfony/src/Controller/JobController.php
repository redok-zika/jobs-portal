<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\RecruitisApiClient;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class JobController extends AbstractController
{
    public function __construct(
        private readonly RecruitisApiClient $apiClient
    ) {
    }

  /**
   * @throws InvalidArgumentException
   */
    #[Route('/jobs', name: 'api_jobs_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        try {
            $jobs = $this->apiClient->getJobs($page, $limit);
            return $this->json([
            'payload' => $jobs['payload'] ?? [],
            'meta' => $jobs['meta'] ?? []
            ]);
        } catch (Exception $e) {
            return $this->json([
            'error' => $e->getMessage(),
            'meta' => [
            'code' => 'api.error',
            'message' => 'Failed to fetch jobs'
            ]
            ], 500);
        }
    }

  /**
   * @throws InvalidArgumentException
   */
    #[Route('/jobs/{id}', name: 'api_jobs_detail', methods: ['GET'])]
    public function detail(string $id): JsonResponse
    {
        try {
            $job = $this->apiClient->getJob($id);
            return $this->json($job);
        } catch (Exception) {
            return $this->json(['error' => 'Job not found'], 404);
        }
    }

    #[Route('/jobs/{id}/apply', name: 'api_jobs_apply', methods: ['POST'])]
    public function apply(string $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $response = $this->apiClient->submitApplication($id, $data);
            return $this->json($response);
        } catch (Exception $e) {
            return $this->json([
            'error' => $e->getMessage(),
            'meta' => [
            'code' => 'api.error',
            'message' => 'Failed to submit application'
            ]
            ], 400);
        }
    }
}
