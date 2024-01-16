<?php

use App\AI\Chat;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

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

Route::get('/poem', function () {
    $chat = new Chat();

    $poem = $chat
        ->systemMessage('Your are a poetic assistant ,skilled in explaining complex programming concepts with creative flair in traditional chinese.')
        ->send("Compose a poem that explains the concept of recursion in programming in Traditional Chinese.");

    $smarterPoem = $chat->reply("Cool, can you write a poem about recursion in programming in Traditional Chinese?");

    return view('welcome', ['poem' => $smarterPoem]);
});

Route::get('/', function () {
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

    $mp3 = (new Chat())->send(
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

    return redirect('/')->with([
        'file' => $file,
        'flash' => 'Boom, Roasted!'
    ]);
});
