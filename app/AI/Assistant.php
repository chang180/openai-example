<?php

namespace App\AI;

use OpenAI\Laravel\Facades\OpenAI;

class Assistant
{
    protected array $messages = [];

    public function __construct(array $messages = [])
    {
        $this->messages = $messages;
    }

    public function send(string $message, bool $speech = false): string
    {
        $this->addMessage($message);

        $response = OpenAI::chat()->create([
            "model" => "gpt-3.5-turbo",
            "messages" => $this->messages,
        ])->choices[0]->message->content;

        if ($response) {
            $this->addMessage($response, 'assistant');
        }

        return $speech ? $this->speech($response) : $response;
    }

    protected function speech(string $message): string
    {
        // release time limit
        set_time_limit(0);
        $mp3 = OpenAI::audio()->speech([
            "model" => "tts-1-hd",
            "input" => $message,
            "voice" => "shimmer",
            // "response_format" => "mp3",
            // "speed" => 0.9,
        ]);
        return $mp3;
    }

    public function messages(): array
    {
        return $this->messages;
    }

    public function systemMessage(string $message): self
    {
        $this->addMessage($message, 'system');

        return $this;
    }

    public function reply(string $message): string
    {
        return $this->send($message);
    }

    public function visualize(string $prompt, array $options = []): string
    {
        $this->addMessage($prompt);

        $prompt = collect($this->messages)->where('role', 'user')->pluck('content')->join("\n");

        logger($prompt);

        $options = array_merge([
            'prompt' => $prompt,
            'model' => 'dall-e-2'
        ], $options);

        $url =  (string)OpenAI::images()->create($options)->data[0]['url'];
        $this->addMessage($url, 'assistant');

        return $url;
    }

    protected function addMessage(string $message, string $role = 'user'): self
    {
        $this->messages[] = [
            "role" => $role,
            "content" => $message
        ];

        return $this;
    }

    protected function getPoem(): string
    {
        return 'This is a poem';
    }
}
