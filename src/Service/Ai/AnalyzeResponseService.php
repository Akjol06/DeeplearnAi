<?php

namespace App\Service\Ai;

class AnalyzeResponseService
{
    public function simplifyResponse(array $openAiResponse): array
    {
        $content = $openAiResponse['choices'][0]['message']['content'] ?? '';

        // Убираем возможные markdown-коды ```json ... ```
        if (str_starts_with($content, '```')) {
            $lines = explode("\n", $content);
            if (count($lines) >= 3) {
                $content = implode("\n", array_slice($lines, 1, -1));
            }
        }

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'error' => 'Cannot parse assistant response as JSON',
                'raw' => $content,
            ];
        }

        return [
            'score' =>
                $data['score']
                ?? $data['оценка_понимания_темы']
                    ?? null,

            'correct_aspects' =>
                $data['correct_aspects']
                ?? $data['правильные_аспекты']
                    ?? [],

            'mistakes' =>
                $data['mistakes']
                ?? $data['ошибки']
                    ?? [],

            'recommendations' =>
                $data['recommendations']
                ?? $data['рекомендации']
                    ?? [],
        ];
    }
}