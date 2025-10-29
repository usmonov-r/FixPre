<?php

namespace App\Service;

use GeminiAPI\Client;
use GeminiAPI\Resources\ModelName;
use GeminiAPI\Resources\Parts\TextPart;

class GeminiFeedbackService
{
    private string $geminiApiKey;

    public function __construct(string $geminiApiKey)
    {
        $this->geminiApiKey = $geminiApiKey;
    }
    public function getFeedbackForPresentation(array $slidesData): array
    {
        $client = (new Client($this->geminiApiKey))
            ->withV1BetaVersion();

        $fullPrompt = "You are an expert presentation coach. Analyze the
        following presentation text, slide by slide. Provide 1-2 bullet
        points of constructive feedback for *each* slide.\n\n";

        foreach ($slidesData as $slideName => $text) {
            if (!empty(trim($text))) {
                $fullPrompt .= "--- $slideName --- \n";
                $fullPrompt .= "$text\n\n";
            }
        }
        if(trim($fullPrompt) === ''){
            return ['error' => 'No text was extracted from the presentation.'];
        }
        try {
            // --- x THE FIX ---
            error_log("Ready full prompt: $fullPrompt");
            $result = $client->generativeModel('gemini-2.0-flash')
                ->generateContent(new TextPart($fullPrompt));

            return [
                'full_feedback' => $result->text()
            ];

        } catch (\Exception $e) {
            return ['error' => 'Gemini API call failed: ' . $e->getMessage()];
        }
    }
}
