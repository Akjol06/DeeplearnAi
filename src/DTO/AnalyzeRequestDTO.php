<?php

namespace App\DTO;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class AnalyzeRequestDTO
{
    #[Assert\NotBlank(message: "Тема не может быть пустой")]
    public ?string $topic = null;

    #[Assert\NotNull(message: "Файл аудио обязателен")]
    public UploadedFile $audio;
}