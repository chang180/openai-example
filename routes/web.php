<?php

use App\AI\Assistant;
use App\Rules\SpamFree;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
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
    request()->validate([
        'body' => [
            'required',
            'string',
            'min:3',
            new SpamFree()
        ],
    ]);

    return redirect('/spam-detect');
})->name('spam-detect');

Route::get('/reset-spam-detect', function () {
    session()->forget('response');
    return redirect('/spam-detect');
})->name('reset-spam-detect');

// assistant example
Route::get('/assistant', function () {
    $file = OpenAI::files()->upload([
        'purpose' => 'assistants',
        'file' => fopen(storage_path('docs/parsing.md'), 'rb'),
    ]);

    $assistant = OpenAI::assistants()->create([
        'model' => 'gpt-3.5-turbo-1106',
        'name' => 'Laraparse Tutor',
        'instructions' => 'You are a helpful programming teacher',
        'tools' => [
            ['type' => 'retrieval'],
        ],
        'file_ids' => [
            $file->id,
        ]
    ]);

    $run = OpenAI::threads()->createAndRun([
        'assistant_id' => $assistant->id,
        'thread' => [
            'messages' => [
                ['role' => 'user', 'content' => 'How do I grab the first paragraph?']
            ]
        ]
    ]);

    do {
        sleep(1);
        $run = OpenAI::threads()->runs()->retrieve(
            threadId: $run->threadId,
            runId: $run->id
        );
    }while ($run->status !== 'completed');

    $messages = OpenAI::threads()->messages()->list($run->threadId);
    dd($messages); // @todo : to be continued
});
