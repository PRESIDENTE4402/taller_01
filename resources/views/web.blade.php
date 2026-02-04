<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taller Automotriz Profesional</title>
    <meta name="description" content="Taller mecánico profesional. Diagnóstico, mantenimiento y reparación automotriz.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Prompt:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-light">

    <header class="bg-dark text-white">
        <div class="container py-3 d-flex justify-content-between align-items-center">
            <h2 class="m-0">Taller Automotriz</h2>
            <nav>
                <a href="{{ route('home') }}" class="text-white me-3">Inicio</a>
                <a href="{{ route('servicios') }}" class="text-white me-3">Servicios</a>
                <a href="{{ route('nosotros') }}" class="text-white me-3">Nosotros</a>
                <a href="{{ route('contacto') }}" class="text-white">Contacto</a>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="bg-dark text-white mt-5">
        <div class="container py-4 text-center">
            <p class="mb-1">© 2026 Taller Automotriz Profesional</p>
            <small>Diagnóstico • Mantenimiento • Reparación</small>
        </div>
    </footer>

</body>

</html>