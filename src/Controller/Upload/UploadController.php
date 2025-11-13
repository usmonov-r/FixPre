<?php

namespace App\Controller\Upload;

use App\Repository\UserRepository;
use App\Entity\FeedbackResult;
use App\Message\ProcessPresentationJob;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Uid\Uuid;


class UploadController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    )
    {}

    #[Route('/api/upload', name: 'api_upload', methods: ['POST'])]
    public function upload(
        Request $request,
        MessageBusInterface $bus,
        SluggerInterface $slugger,
        string $uploadsDirectory,
        UserRepository  $userRepo,
    ): Response {
        $file = $request->files->get('presentation');

        if(!$file) {
            return $this->json(['error' => 'No file uploaded. ', 400]);
        }

        $jobId = Uuid::v4()->toRfc4122();

        $feedbackResult = new FeedbackResult();
        $feedbackResult->setJobId($jobId);

        $userDTO = $this->getUser();

        if($userDTO){
            $realUser = $userRepo->findOneBy(['email' => $userDTO->getEmail()]);

            if($realUser){
                $feedbackResult->setUser($realUser);
            }
        }

        if($this->getUser()) {
            $feedbackResult->setUser($this->getUser());
        }
        $this->entityManager->persist($feedbackResult);

        $newFileName = $jobId . '.' . $file->guessExtension();

        try{
            $file->move($uploadsDirectory, $newFileName);
        }catch (\Exception $e) {
            return $this->json(['error' => 'Could not save file.'], 500);
        }

        $filepath = $uploadsDirectory . '/' . $newFileName;
        $bus->dispatch(new ProcessPresentationJob($jobId, $filepath));

        return $this->json(['jobId' => $jobId], 202);

    }
}
