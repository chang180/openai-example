<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>OpenAI poem example</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="h-full grid place-items-center p-6">
    @if (session('file'))
        <div>
            <a href="{{ session('file') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" download>Download Audio</a>
            <a href="/" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Home</a>
        </div>
    @else
        <form action="/roast" method="POST" class="flex flex-col items-center">
            @csrf
            <label for="topic" class="text-2xl font-bold mb-4">Roast:</label>
            <textarea name="topic" id="topic" rows="10" cols="50" class="border border-gray-400 rounded-lg p-2 mb-4"
                placeholder="What do you want us to roast?" required></textarea>
            <button type="submit"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Roast!</button>
        </form>
    @endif
</body>

</html>
