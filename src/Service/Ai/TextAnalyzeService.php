<?php

namespace App\Service\Ai;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TextAnalyzeService
{
    public function __construct(
        private HttpClientInterface $client,
        private string $openAiApiKey,
    ) {}

    public function analyze(string $topic, string $studentText): array
    {
        $prompt = "Тема: $topic

        Объяснение студента:
        $studentText

        Оцени понимание темы по 100-балльной шкале.
        Выдели:
        1) Что объяснено правильно
        2) Ошибки
        3) Рекомендации
        Ответ верни в JSON.
        ";

        $response = $this->client->request('POST',
            'https://api.openai.com/v1/chat/completions',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->openAiApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4.1-mini',
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ]
                ]
            ]
        );

        return $response->toArray();
    }
}