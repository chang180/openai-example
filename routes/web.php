<?php

use App\AI\Assistant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use OpenAI\Laravel\Facades\OpenAI;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/poem', function () {
    $chat = new Assistant();

    $poem = $chat
        ->systemMessage('Your are a poetic assistant ,skilled in explaining complex programming concepts with creative flair in traditional chinese.')
        ->send("Compose a poem that explains the concept of recursion in programming in Traditional Chinese.");

    $smarterPoem = $chat->reply("Cool, can you write a poem about recursion in programming in Traditional Chinese?");

    return view('poem', ['poem' => $smarterPoem]);
});

Route::get('/roast', function () {
    if (session()->has('file') && session('flag')) {
        session(['flag' => false]);
        return view('roast');
    }
    session()->forget('file');
    // session(['file' => file_get_contents(public_path('roasts/file.mp3'))]);
    return view('roast');
});

Route::post('/roast', function () {
    $attributes = request()->validate([
        'topic' => ['required', 'string', 'min:2', 'max:255'],
    ]);
    $prompt = "Pleast roast {$attributes['topic']} in a sarcastic tone in Traditional Chinese.";

    $mp3 = (new Assistant())->send(
        message: $prompt,
        speech: true
    );

    $file = '/roasts/' . md5($mp3) . '.mp3';

    if (!file_exists(public_path('roasts'))) {
        mkdir(public_path('roasts'));
    }
    file_put_contents(public_path($file), $mp3);
    session([
        'file' => $file,
        'flag' => true
    ]);

    return redirect('/roast')->with([
        'file' => $file,
        'flash' => 'Boom, Roasted!'
    ]);
});

Route::get('/image', function () {

    return view('image', [
        'messages' => session('messages', []),
    ]);
});

Route::post('/image', function () {
    $attributes = request()->validate([
        'description' => ['required', 'string', 'min:3'],
    ]);

    $assistant = new Assistant(session('messages', []));

    $assistant->visualize($attributes['description']);

    session(['messages' => $assistant->messages()]);

    return redirect('/image');
});

Route::post('/resetImage', function () {
    session(['messages' => []]);

    return redirect('/image');
});

// spam detect example

Route::get('/spam-detect', function () {

    return view('spam-detect');
});

Route::post('/spam-detect', function () {
    $attributes = request()->validate([
        'body' => ['required', 'string', 'min:3'],
    ]);
    $response = OpenAI::chat()->create([
        'model' => 'gpt-3.5-turbo-1106',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a forum moderator with ability to produce JSON format reply.'],
            [
                'role' => 'user',
                'content' => <<<EOT
                Please inspect the following text and determine if it is spam.

                {$attributes['body']}

                Expected Response Example:

                {
                    "isSpam": true,
                    "reason": "This is spam because it is an advertisement."
                }
                EOT,
            ],
        ],
        // 'response_format' => ['type' => 'json_object']
    ])->choices[0]->message->content;

    $response = json_decode($response);
    session(['response' => $response->isSpam ? 'This is spam because ' . $response->reason : 'This is not spam.']);
    return redirect('/spam-detect');
})->name('spam-detect');

Route::get('/reset-spam-detect', function () {
    session()->forget('response');
    return redirect('/spam-detect');
})->name('reset-spam-detect');



