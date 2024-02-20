<?php

namespace App\AI;

use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Assistants\AssistantResponse;
use OpenAI\Responses\Threads\Runs\ThreadRunResponse;
use OpenAI\Responses\Threads\Messages\ThreadMessageListResponse;

class LaraparseAssistant
{
    protected AssistantResponse $assistant;

    protected string $threadId;

    protected OpenAIClient $client;

    public function __construct(string $assistantId, ?AIClient $client = null)
    {
        $this->client = $client ??= new OpenAIClient();

        $this->assistant = $this->client->retrieveAssistant($assistantId);
    }

    public static function create(array $config = []): static
    {
        $defaultConfig = [
            'model' => 'gpt-3.5-turbo-1106',
            'name' => 'Laraparse Tutor',
            'instructions' => 'You are a helpful programming teacher',
            'tools' => [
                ['type' => 'retrieval'],
            ]
        ];

        $assistant = (new OpenAIClient())->createAssistant(array_merge_recursive($defaultConfig, $config));

        return new static($assistant->id);
    }

    public function educate(string $file): static
    {
        $this->client->uploadFile($file, $this->assistant);

        return $this;
    }

    public function createThread(array $parameters = []): static
    {
        $thread = $this->client->createThread($parameters);

        $this->threadId = $thread->id;

        return $this;
    }

    public function messages(): ThreadMessageListResponse
    {
        return $this->client->messages($this->threadId);
    }

    public function write(string $message): static
    {
        $this->client->createMessage($message, $this->threadId);

        return $this;
    }

    public function send()
    {
        return $this->client->run($this->threadId, $this->assistant);
    }
}
