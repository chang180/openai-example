<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>OpenAI example</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="h-full grid place-items-center bg-gray-100">
    <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-md">
        <h1 class="text-3xl font-bold mb-4">Create Reply</h1>
        <p class="mb-2">This comment will be displayed publicly so be careful what you share.</p>
        <form action="{{ route('spam-detect') }}" method="POST">
            @csrf
            <textarea name="body" id="text" cols="30" rows="10"
                class="w-full border border-gray-300 p-2 mb-4 rounded focus:outline-none focus:ring focus:border-blue-300"></textarea>
            <button type="submit"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Submit</button>
            <a href="{{ route('reset-spam-detect') }}" class="btn bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Reset</a>
            @if($errors->any())
                <div class="mt-4">
                    <h2 class="text-xl font-bold mb-2">Errors</h2>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li class="text-red-500">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            </form>
        @if (session('response'))
            <div class="mt-4">
                <h2 class="text-xl font-bold mb-2">Result</h2>
                <p>{{  session('response')  }}</p>
            </div>
        @endif
    </div>
</body>
</html>
