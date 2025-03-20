<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\RecruitisApiClient;
use App\Types\ApplicationData;
use Exception;
use InvalidArgumentException;
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
   * @throws Exception
   * @throws \Psr\Cache\InvalidArgumentException
   */
    #[Route('/jobs', name: 'api_jobs_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        try {
            $jobs = $this->apiClient->getJobs($page, $limit);
            return $this->json([
            'payload' => $jobs->payload,
            'meta' => $jobs->meta
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
   * @throws Exception
   * @throws \Psr\Cache\InvalidArgumentException
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

  /**
   * @throws Exception
   */
    #[Route('/jobs/{id}/apply', name: 'api_jobs_apply', methods: ['POST'])]
    public function apply(string $id, Request $request): JsonResponse
    {
        $content = $request->getContent();
        if (!is_string($content)) {
            throw new InvalidArgumentException('Invalid request content');
        }

        $data = json_decode($content, true);
        if (!is_array($data)) {
            throw new InvalidArgumentException('Invalid JSON data');
        }

        $applicationData = new ApplicationData();
        $applicationData->name = $data['name'] ?? '';
        $applicationData->job_id = (int)$id;
        $applicationData->email = $data['email'] ?? '';
        $applicationData->phone = $data['phone'] ?? '';
        $applicationData->coverLetter = $data['cover_letter'] ?? '';
        $applicationData->gdprAgreement = (bool)($data['gdpr_agreement'] ?? false);
        $applicationData->linkedin = $data['linkedin'] ?? null;
        $applicationData->facebook = $data['facebook'] ?? null;
        $applicationData->twitter = $data['twitter'] ?? null;
        $applicationData->source = 'web';

        try {
            $response = $this->apiClient->submitApplication($id, $applicationData);
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
