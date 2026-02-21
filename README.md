# DeeplearnAi

Бэкенд-сервис на **Symfony 7.3** для анализа устных ответов студентов: загрузка аудио → транскрипция речи → оценка понимания темы с помощью ИИ.

## Возможности

- **Транскрипция аудио** — через [AssemblyAI](https://www.assemblyai.com/) (речь в текст).
- **Анализ ответа** — через [OpenAI](https://platform.openai.com/) (GPT-4.1-mini): оценка по 100-балльной шкале, что объяснено правильно, ошибки и рекомендации.
- **REST API** — один endpoint для загрузки аудио и темы, ответ в JSON.

## Требования

- PHP >= 8.2
- Composer
- PostgreSQL (или SQLite/MySQL — настраивается в `.env`)
- Ключи API: OpenAI, AssemblyAI

## Установка

```bash
cd deeplearn-ai
composer install
```

## Конфигурация

1. Скопируйте `.env` в `.env.local` и задайте свои значения:

```bash
cp .env .env.local
```

2. Обязательные переменные в `.env.local`:

| Переменная | Описание |
|------------|----------|
| `OPENAI_API_KEY` | Ключ API OpenAI |
| `ASSEMBLYAI_API_KEY` | Ключ API AssemblyAI |
| `CORS_ALLOW_ORIGIN` | Разрешённый origin для CORS (например, `http://localhost:5173`) |


## Запуск

Локальный сервер:

```bash
symfony server:start
```

или встроенный PHP-сервер:

```bash
php -S localhost:8000 -t public
```

Приложение доступно по адресу `http://localhost:8000` (или порт, указанный в `symfony server:start`).

## API

### POST `/api/analyze`

Анализ устного ответа студента по заданной теме.

**Тело запроса:** `multipart/form-data`

| Поле   | Тип   | Обязательно | Описание                    |
|--------|--------|-------------|-----------------------------|
| `audio`| файл   | да          | Аудиофайл (речь студента)   |
| `topic`| строка | да          | Тема, по которой оценивать  |

**Ответ 200:** JSON

```json
{
  "transcribed_text": "Текст, распознанный из аудио",
  "analysis": {
    "score": 85,
    "correct_aspects": ["Правильно объяснён пункт 1", "..."],
    "mistakes": ["Ошибка в определении X", "..."],
    "recommendations": ["Обратить внимание на Y", "..."]
  }
}
```

**Ответ 400:** при отсутствии `audio` или `topic`:

```json
{ "error": "Audio and topic are required" }
```

## Структура проекта

```
deeplearn-ai/
├── config/              # Конфигурация Symfony и пакетов
├── public/               # Точка входа (index.php)
├── src/
│   ├── Controller/Api/   # AnalyzeController — endpoint /api/analyze
│   ├── DTO/              # AnalyzeRequestDTO (валидация запроса)
│   ├── Service/Ai/       # Сервисы ИИ
│   │   ├── SpeechToTextService   # AssemblyAI — транскрипция
│   │   ├── TextAnalyzeService    # OpenAI — анализ текста
│   │   └── AnalyzeResponseService # Приведение ответа к единому формату
│   └── Kernel.php
├── tests/
├── .env                  # Шаблон переменных окружения
└── composer.json
```

## Стек

- **Symfony 7.3** — фреймворк
- **API Platform 3.x** — REST API
- **Doctrine ORM** — работа с БД
- **Nelmio CORS** — CORS для фронтенда (например, на `localhost:5173`)
- **Symfony HttpClient** — запросы к AssemblyAI и OpenAI