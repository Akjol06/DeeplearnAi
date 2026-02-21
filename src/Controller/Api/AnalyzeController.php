<?php

namespace App\Controller\Api;

use App\Service\Ai\AnalyzeResponseService;
use App\Service\Ai\SpeechToTextService;
use App\Service\Ai\TextAnalyzeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class AnalyzeController extends AbstractController
{
    public function __construct(
        private TextAnalyzeService $textAnalyze,
        private SpeechToTextService $speechToText,
    ) {}

    #[Route('/analyze', name: 'app_analyze', methods: ['POST'])]
    public function analyze(Request $request, AnalyzeResponseService $simplifier): JsonResponse
    {
        $audioFile = $request->files->get('audio');
        $topic = $request->request->get('topic');

        if (!$audioFile || !$topic) {
            return $this->json(['error' => 'Audio and topic are required'], 400);
        }

        $text = $this->speechToText->transcribe($audioFile);
        $openAiResult = $this->textAnalyze->analyze($topic, $text);
        $cleanResult = $simplifier->simplifyResponse($openAiResult);

        return $this->json([
            'transcribed_text' => $text,
            'analysis' => $cleanResult,
        ]);
    }
}