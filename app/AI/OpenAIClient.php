<?php

namespace App\AI;

use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Threads\ThreadResponse;
use OpenAI\Responses\Assistants\AssistantResponse;
use OpenAI\Responses\Threads\Runs\ThreadRunResponse;
use OpenAI\Responses\Threads\Messages\ThreadMessageListResponse;

class OpenAIClient implements AIClient
{
    public function retrieveAssistant($assistantId): AssistantResponse
    {
        return OpenAI::assistants()->retrieve($assistantId);
    }

    public function createAssistant(array $config): AssistantResponse
    {
        return OpenAI::assistants()->create($config);
    }

    public function uploadFile(string $file, AssistantResponse $assistant): void
    {
        $file =  OpenAI::files()->upload([
            'purpose' => 'assistants',
            'file' => fopen($file, 'rb'),
        ]);

        OpenAI::assistants()
        ->files()
            ->create($this->assistant->id, ['file_id' => $file->id]);
    }

    public function createThread(array $parameters): ThreadResponse
    {
        return OpenAI::threads()->create($parameters);
    }

    public function createMessage(string $message, string $threadId): void
    {
        OpenAI::threads()->messages()->create($threadId, [
            'role' => 'user',
            'content' => $message
        ]);
    }

    public function messages(string $threadId): ThreadMessageListResponse
    {
        return OpenAI::threads()->messages()->list($threadId);
    }

    public function run(string $threadId, AssistantResponse $assistant): ThreadMessageListResponse
    {
        $run = OpenAI::threads()->runs()->create($threadId, [
            'assistant_id' => $assistant->id
        ]);

        while ($this->runStatus($run)) {
            sleep(1);
        }

        return $this->messages($threadId);
    }

    public function runStatus(ThreadRunResponse $run): bool
    {
        $run = OpenAI::threads()->runs()->retrieve(
            threadId: $run->threadId,
            runId: $run->id
        );

        return $run->status !== 'completed';
    }
}
