<?php

namespace App\Controller\Feedback;

use App\Controller\Base\AbstractController;
use App\Repository\FeedbackResultRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardStatsController extends AbstractController
{
    #[Route('/api/dashboard/stats', name: 'api_dashboard_stats', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function __invoke(FeedbackResultRepository $repository): JsonResponse
    {
        $user = $this->getUser();
        $userStats = $repository->findUserStats($user);

        $platformStats = $repository->findAverageStats();

        if ((int)$userStats['totalPresentations'] === 0) {
            return $this->json([
                'userAverageScore' => 0,
                'totalUserPresentations' => 0,
                'platformAverageScore' => round((float)$platformStats['platformAverage'], 1)
            ]);
        }

        $formattedStats = [
            'averageScore' => round((float)$userStats['averageScore'], 1),
            'totalPresentations' => (int)$userStats['totalPresentations'],
            'platformAverageScore' => round((float)$platformStats['platformAverage'], 1)
        ];

        return $this->json($formattedStats);
    }
}
