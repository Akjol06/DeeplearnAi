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

        $toArray = function($field) {
            if (empty($field)) {
                return [];
            }
            // Если ассоциативный массив — берем только значения
            if (array_values($field) !== $field) {
                return array_values($field);
            }
            return $field;
        };

        return [
            'score' => $data['score'] ?? $data['оценка'] ?? 0,
            'correct_aspects' => $toArray($data['correct_aspects'] ?? $data['правильно'] ?? []),
            'mistakes' => $toArray($data['mistakes'] ?? $data['ошибки'] ?? []),
            'recommendations' => $toArray($data['recommendations'] ?? $data['рекомендации'] ?? []),
        ];
    }
}