<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'BMW Service') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Prompt:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />


    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #000;
        }
    </style>
</head>

{{-- ... cabecera igual ... --}}

<body class="antialiased min-h-screen bg-black text-white">
    <div class="flex flex-col lg:flex-row w-full min-h-screen">

        <div class="hidden lg:flex lg:w-1/2 relative bg-gray-900 border-r border-gray-800">
            <img src="https://images.unsplash.com/photo-1555215695-3004980ad54e?q=80&w=2070&auto=format&fit=crop"
                alt="BMW M Workshop" class="absolute inset-0 w-full h-full object-cover opacity-60">
            <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent"></div>

            <div class="relative z-10 w-full flex flex-col justify-between p-12">
                <div class="flex items-center gap-3">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/44/BMW.svg/2048px-BMW.svg.png"
                        alt="BMW Logo" class="h-10 w-auto">
                    <div class="text-white font-bold italic tracking-wider text-xl">
                        BMW <span class="text-white font-light">Service</span>
                    </div>
                </div>

                <div class="mb-10">
                    <h1 class="text-5xl font-bold text-white mb-4 leading-tight">Excelencia en <br>Cada Detalle</h1>
                    <p class="text-gray-300 text-lg max-w-md border-l-4 border-blue-600 pl-4">
                        Accede a la plataforma de gestión de servicios más avanzada.
                    </p>
                </div>
            </div>
        </div>

        <div
            class="w-full lg:w-1/2 flex flex-col justify-start lg:justify-center items-center bg-[#0a0a0a] p-6 lg:p-12 overflow-y-auto py-20">
            <div class="w-full max-w-md relative z-10">
                @yield('content')
            </div>

            <div class="mt-12 text-center text-xs text-gray-600">
                &copy; {{ date('Y') }} Tecnimecanica California. BMW Service Partner.
            </div>
        </div>
    </div>
    @stack('scripts')
</body>

</html>

</html>