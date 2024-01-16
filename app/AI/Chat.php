<?php

namespace App\AI;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Http;

class Chat
{
    protected array $messages = [];

    public function send(string $message, bool $speech = false): string
    {
        $this->messages[] = [
            "role" => "user",
            "content" => $message
        ];

        $response = OpenAI::chat()->create([
                "model" => "gpt-3.5-turbo",
                "messages" => $this->messages,
        ])->choices[0]->message->content;

        if($response) {
            $this->messages[] = [
                "role" => "assistant",
                "content" => $response
            ];
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
        $this->messages[] = [
            "role" => "system",
            "content" => $message
        ];

        return $this;
    }

    public function reply(string $message): string
    {
        return $this->send($message);
    }

    protected function getPoem(): string
    {
        return 'This is a poem';
    }

}
