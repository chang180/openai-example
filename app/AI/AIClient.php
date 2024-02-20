<?php

namespace App\AI;

use OpenAI\Responses\Assistants\AssistantResponse;
use OpenAI\Responses\Threads\Runs\ThreadRunResponse;
use OpenAI\Responses\Threads\Messages\ThreadMessageListResponse;
use OpenAI\Responses\Threads\ThreadResponse;

interface AIClient
{
    public function retrieveAssistant($assistantId): AssistantResponse;

    public function createAssistant(array $config): AssistantResponse;

    public function uploadFile(string $file, AssistantResponse $assistant): void;

    public function createThread(array $parameters): ThreadResponse;

    public function createMessage(string $message, string $threadId): void;

    public function messages(string $threadId): ThreadMessageListResponse;

    public function run(string $threadId, AssistantResponse $assistant): ThreadMessageListResponse;

    public function runStatus(ThreadRunResponse $run): bool;
}
