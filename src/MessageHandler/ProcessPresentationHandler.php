<?php

namespace App\MessageHandler;

use App\Entity\FeedbackResult;
use Doctrine\ORM\EntityManagerInterface;
use App\Message\ProcessPresentationJob;
use App\Service\GeminiFeedbackService;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\Shape\RichText;
use PhpOffice\PhpPresentation\Shape\RichText\TextRun;
use PhpOffice\PhpPresentation\Shape\Table;
use PhpOffice\PhpPresentation\Shape\Group;

#[AsMessageHandler]
class ProcessPresentationHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private GeminiFeedbackService $geminiService
    ){
    }

    public function __invoke(ProcessPresentationJob $job)
    {
        $jobId = $job->getJobId();
        $filepath = $job->getFilePath();

        $result = new FeedbackResult();
        $result->setJobId($jobId);
        $result->setStatus('pending');
        $this->entityManager->persist($result);
        $this->entityManager->flush();

        error_log("--- WORKER: Job $jobId, Status set a PENDING  ---- ");

        try {
            $extractedText = $this->parsePresentation($filepath);
            error_log(" -- WORKER: Extracted Text for $jobId --- ");

            $aiFeedback = $this->geminiService->getFeedbackForPresentation($extractedText);
            error_log("WORKER: Got ai  $jobId --- ");

            $result->setStatus('complete');
            $result->setFeedback($aiFeedback);

        } catch (\Exception $e) {
            error_log("--- WORKER: FAILED for $jobId --- ");
            error_log($e->getMessage());
            $result->setStatus('failed');
            $result->setFeedback(['error' => $e->getMessage()]);
        } finally {
            $this->entityManager->flush();

            if(file_exists($filepath)){
                unlink($filepath);

            }
        }

        // SAVE THE FINAL RESULT
        $this->entityManager->flush();
        error_log(" --- WORKER: job $jobId finished");
    }

    private function parsePresentation(string $filepath): array
    {
        $presentation = IOFactory::load($filepath);

        $slideFeedback = [];
        $slideNumber = 1;

        foreach ($presentation->getAllSlides() as $slide) {
            $slideText = $this->findTextInShape($slide->getShapeCollection());
            $slideFeedback['slide_' . $slideNumber] = implode("\n", $slideText);
            $slideNumber++;
        }
        return $slideFeedback;
    }
    private function findTextInShape(iterable $shapes): array
    {
        $textFound = [];

        foreach($shapes as $shape) {
//            error_log('Shape type: ' . get_class($shape));
            if($shape instanceof Group){
                $textFound = array_merge($textFound, $this->findTextInShape($shape->getShapeCollection()));
//                $groupText = $this->findTextInShape($shape->getShapeCollection());
//                $textFound = array_merge($textFound, $groupText);

            }elseif ($shape instanceof Table) {
                foreach($shape->getRows() as $row){
                    foreach($row->getCells() as $cell){
                        $cellText = $this->extractTextFromRichText($cell);
                        $textFound = array_merge($textFound, $cellText);
                    }
                }
            } elseif($shape instanceof  RichText ||
                     $shape instanceof  AutoShape ||
                     $shape instanceof TextBox) {
                $shapeText = $this->extractTextFromRichText($shape);
                $textFound = array_merge($textFound, $shapeText);
            }
        }
        return $textFound;
    }

    private function extractTextFromRichText($shape): array
    {
        $textFound = [];
        foreach($shape->getParagraphs() as $paragraph){
            foreach ($paragraph->getRichTextElements() as $element){
                if (method_exists($element, 'getText')) {
                    $text = trim($element->getText());
                    if ($text !== '') {
                        $textFound[] = $text;
                    }
                }
            }
        }

        return $textFound;
    }


}
