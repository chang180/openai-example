<?php

namespace App\Rules;

use App\AI\Assistant;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use OpenAI\Laravel\Facades\OpenAI;

class SpamFree implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $response = (new Assistant)
            ->systemMessage('You are a forum moderator who always responds JSON format reply.')
            ->send(<<<EOT
                Please inspect the following text and determine if it is spam.

                {$value}

                Expected Response Example:

                {
                    "isSpam": true,
                    "reason": "This is spam because it is an advertisement."
                }
                EOT);

        $response = json_decode($response);

        if ($response->isSpam) {
            $fail("Spam was detected.");
            session(['response' => $response->reason]);
        } else {
            session(['response' => 'This is not spam.']);
        }
    }
}
