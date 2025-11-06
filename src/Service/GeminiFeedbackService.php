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

        $fullPrompt = $this->buildJsonPrompt($slidesData);
        if(trim($fullPrompt) === ''){
            return ['error' => 'No text was extracted from the presentation.'];
        }

        try {


            $result = $client->generativeModel('gemini-2.0-flash')
                ->generateContent(new TextPart($fullPrompt));

            $responseText = $result->text();
            $cleanedText = preg_replace('/^```json\s*(.*?)\s*```$/s', '$1', $responseText);
            if (is_null($cleanedText)) {
                $cleanedText = trim(str_replace(['```json', '```'], '', $responseText));
            }
            $jsonFeedBack = json_decode($cleanedText, true);

            if (json_last_error() !== JSON_ERROR_NONE){
                throw new \Exception('Failed to decode AI JSON response');
            }

            return $jsonFeedBack;
        } catch (\Exception $e) {
            return ['error' => 'Gemini API call failed: ' . $e->getMessage()];
        }
    }
    private function buildJsonPrompt(array $slideData): string
    {
        $prompt = "You are an expert presentation coach. Analyze the text for the following presentation slides. You MUST respond with only a JSON object. Do not add any introductory text or markdown formatting like \`\`\`json.

        The JSON object you return must use the slide name (e.g., \"slide_1\") as the key. Each slide's value should be a JSON object with this exact structure:

        {
          \"overall_score\": \"A single score from 1-10 rating the *entire* presentation's quality.\",
          \"clarity_feedback\": \"1-2 sentences explaining how clear the main point is.\",
          \"conciseness_feedback\": \"1-2 sentences on whether the text is too wordy or just right.\",
          \"typos_grammar\": \"A string listing any typos or grammar mistakes, or 'None found.'.\",
          \"quick_fix_suggestion\": \"Rewrite the slide's text to be more clear, concise, and impactful. Keep it under 30 words.\"
        }

        Here is the presentation text:\n\n";
        foreach ($slideData as $slideName => $text) {
            if (!empty(trim($text))) {
                $prompt .= "--- $slideName ---\n";
                $prompt .= "$text\n\n";
            }
        }
        return trim($prompt);
    }
}
