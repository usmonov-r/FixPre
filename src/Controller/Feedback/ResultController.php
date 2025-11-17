<?php

namespace App\Controller\Feedback;
use App\Repository\FeedbackResultRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ResultController extends  AbstractController
{
    #[Route('/api/results/{jobId}', name: 'api_get_results', methods: ['GET'])]
    public function getResult(
        string $jobId,
        FeedbackResultRepository $repository
    ): Response {

        $result = $repository->findOneBy(['job_id' => $jobId]);
        if(!$result){
            return $this->json(['error' => 'Job Not Found.'], 404);
        }

        $data = [
            'jobId' => $result->getJobId(),
            'status' => $result->getStatus(),
            'feedback' => $result->getFeedback(),
            'overallScore' => $result->getOverallScore(),
            'createdAt' => $result->getCreatedAt()->format('c')
        ];
        return $this->json($data);
    }

}
