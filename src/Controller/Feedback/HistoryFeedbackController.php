<?php

namespace App\Controller\Feedback;

//use App\Controller\Base\AbstractController;
use App\Repository\FeedbackResultRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HistoryFeedbackController extends AbstractController
{
    #[Route('/api/feedback/history', name: 'api_get_history', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getHistory(FeedbackResultRepository $repository): JsonResponse
    {
        $user = $this->getUser();
        $results = $repository->findBy(
            ['user' => $user],
            ['created_at' => 'DESC']
        );

        return $this->json($results, 200, [], ['groups' => 'feedback:read']);
    }

}
