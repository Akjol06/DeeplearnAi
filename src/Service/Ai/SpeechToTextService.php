<?php

namespace App\Service\Ai;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SpeechToTextService
{
    public function __construct(
        private HttpClientInterface $client,
        private string $assemblyApiKey
    ) {}

    public function transcribe(UploadedFile $audio): string
    {
        if ($audio->getError() !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('File upload error: ' . $audio->getError());
        }

        // 1) загрузка аудио
        $uploadResponse = $this->client->request(
            'POST',
            'https://api.assemblyai.com/v2/upload',
            [
                'headers' => [
                    'Authorization' => $this->assemblyApiKey,
                    'Content-Type' => 'application/octet-stream',
                ],
                'body' => file_get_contents($audio->getPathname()),
            ]
        );

        $uploadData = $uploadResponse->toArray();
        if (!isset($uploadData['upload_url'])) {
            throw new \RuntimeException('AssemblyAI upload failed: ' . $uploadResponse->getContent(false));
        }
        $audioUrl = $uploadData['upload_url'];
        if (empty($audioUrl)) {
            throw new \RuntimeException('Audio URL is empty, cannot create transcript');
        }

        // 2) отправка на транскрипцию
        $transcriptResponse = $this->client->request(
            'POST',
            'https://api.assemblyai.com/v2/transcript',
            [
                'headers' => [
                    'Authorization' => $this->assemblyApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'audio_url' => $audioUrl,
                    'speech_models' => ['universal-2'],
                    'language_detection' => true,
                    'punctuate' => true,
                    'format_text' => true,
                ],
            ]
        );

        $transcriptData = $transcriptResponse->toArray();
        $transcriptId = $transcriptData['id'];

        // 3) ожидание результата
        do {
            sleep(2);
            $statusResponse = $this->client->request('GET',
                "https://api.assemblyai.com/v2/transcript/{$transcriptId}",
                [
                    'headers' => [
                        'Authorization' => $this->assemblyApiKey,
                    ],
                ]
            );

            $statusData = $statusResponse->toArray();

        } while ($statusData['status'] !== 'completed');

        return $statusData['text'] ?? '';
    }
}