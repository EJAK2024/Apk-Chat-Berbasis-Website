<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        const pusher = new Pusher('{{ env("REVERB_APP_KEY") }}', {
            wsHost: 'localhost',
            wsPort: 8080,
            forceTLS: false,
            enabledTransports: ['ws'],
            cluster: 'mt1',
        });
        window.ReverbPusher = pusher;
        pusher.connection.bind('connected', () => {
            console.log('WebSocket connected!');
        });
    </script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; width: 100%; overflow: hidden; font-family: sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>