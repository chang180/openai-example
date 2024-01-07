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

Route::get('/', function () {
    $chat = new Chat();

    $poem = $chat
        ->systemMessage('Your are a poetic assistant ,skilled in explaining complex programming concepts with creative flair in traditional chinese.')
        ->send("Compose a poem that explains the concept of recursion in programming in Traditional Chinese.");

    $smarterPoem = $chat->reply("Cool, can you write a poem about recursion in programming in Traditional Chinese?");

    return view('welcome', ['poem' => $smarterPoem]);
});
