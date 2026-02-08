@extends('layouts.panel')

@section('title', 'Mi Credencial')
@section('subtitle', 'Código QR para control de asistencia')

@section('content')
    <div class="flex flex-col items-center justify-center min-h-[60vh]">

        <div class="card w-96 bg-[#1A1B1E] shadow-2xl border border-gray-800 relative overflow-hidden group">
            <!-- Decoration -->
            <div class="absolute top-0 left-0 w-full h-1 bg-[#1C69D4]"></div>
            <div class="absolute -right-10 -top-10 w-32 h-32 bg-blue-600/10 rounded-full blur-2xl"></div>

            <div class="card-body items-center text-center p-8">
                <h2 class="card-title text-white text-2xl mb-1">{{ Auth::user()->name }}</h2>
                <p class="text-gray-500 text-sm font-mono mb-6">ID: {{ Auth::user()->id }}</p>

                <div class="bg-white p-4 rounded-xl shadow-inner mb-6">
                    <div id="qrcode"></div>
                </div>

                <p class="text-xs text-gray-500 max-w-[200px]">
                    Presenta este código QR al responsable de asistencia para registrar tu entrada o salida.
                </p>
            </div>
        </div>

        <button onclick="window.print()" class="btn btn-ghost mt-8 text-gray-500 hover:text-white">
            <i class="fas fa-print mr-2"></i> Imprimir Credencial
        </button>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        const userId = "{{ Auth::user()->id }}";

        // Generate QR
        new QRCode(document.getElementById("qrcode"), {
            text: userId,
            width: 180,
            height: 180,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    </script>
@endpush