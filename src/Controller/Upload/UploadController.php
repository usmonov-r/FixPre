<?php

namespace App\Controller\Upload;

 use App\Message\ProcessPresentationJob;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Uid\Uuid;


class UploadController extends AbstractController
{
    #[Route('/api/upload', name: 'api_upload', methods: ['POST'])]
    public function upload(
        Request $request,
        MessageBusInterface $bus,
        SluggerInterface $slugger,
        string $uploadsDirectory
    ): Response {
        $file = $request->files->get('presentation');

        if(!$file) {
            return $this->json(['error' => 'No file uploaded. ', 400]);
        }

        $jobId = Uuid::v4()->toRfc4122();

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
