@extends('layouts.panel')

@section('title', 'Todas las Notificaciones')
@section('subtitle', 'Historial completo de alertas del sistema')

@section('content')
<div class="h-full flex flex-col gap-6">

    <div class="flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Tus Notificaciones</h2>
            <p class="text-sm text-gray-500">Puedes revisar todo el historial de movimientos.</p>
        </div>
        <form action="{{ route('panel.notifications.markAllRead') }}" method="POST" id="markAllReadForm">
            @csrf
            <button type="submit" class="btn btn-outline btn-primary shadow-sm">
                <i class="fas fa-check-double"></i> Mácar todas como leídas
            </button>
        </form>
    </div>

    @if(session('success'))
        <div role="alert" class="alert alert-success shadow-sm rounded-lg py-2">
            <i class="fas fa-check-circle"></i>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm overflow-y-auto custom-scrollbar border border-gray-100 flex-1">
        @if($notifications->count() > 0)
            <ul class="divide-y divide-gray-100">
                @foreach($notifications as $notification)
                    <li class="p-4 {{ is_null($notification->read_at) ? 'bg-blue-50/50' : 'hover:bg-gray-50 transition-colors' }}">
                        <a href="{{ route('panel.notifications.read', $notification->id) }}" class="flex items-start gap-4">
                            @php
                                $type = str_replace('App\\Notifications\\', '', $notification->type);
                                $icon = 'fa-bell';
                                $color = 'bg-gray-100 text-gray-500';
                                
                                if($type == 'ActividadTaller'){
                                    $tipoActividad = $notification->data['tipo'] ?? '';
                                    if($tipoActividad == 'pago_recibido'){ $icon = 'fa-money-bill-wave'; $color = 'bg-green-100 text-green-600'; }
                                    elseif($tipoActividad == 'orden_creada'){ $icon = 'fa-file-invoice'; $color = 'bg-blue-100 text-blue-600'; }
                                    elseif($tipoActividad == 'orden_estado'){ $icon = 'fa-sync'; $color = 'bg-blue-100 text-blue-600'; }
                                    elseif($tipoActividad == 'tarea_asignada'){ $icon = 'fa-tools'; $color = 'bg-purple-100 text-purple-600'; }
                                    elseif($tipoActividad == 'cita_cancelada'){ $icon = 'fa-calendar-times'; $color = 'bg-red-100 text-red-600'; }
                                    elseif($tipoActividad == 'detalle_agregado'){ $icon = 'fa-plus-circle'; $color = 'bg-emerald-100 text-emerald-600'; }
                                    elseif($tipoActividad == 'detalle_eliminado'){ $icon = 'fa-minus-circle'; $color = 'bg-orange-100 text-orange-600'; }
                                    elseif($tipoActividad == 'orden_pausada'){ $icon = 'fa-clock-rotate-left'; $color = 'bg-orange-100 text-orange-600'; }
                                } elseif($type == 'NuevaNotaTarea'){
                                    $icon = 'fa-comment-alt'; $color = 'bg-yellow-100 text-yellow-600';
                                } elseif($type == 'TareaAsignada'){
                                    $icon = 'fa-tasks'; $color = 'bg-indigo-100 text-indigo-600';
                                } elseif($type == 'TareaFinalizada'){
                                    $icon = 'fa-check-circle'; $color = 'bg-green-100 text-green-600';
                                }
                            @endphp
                            <div class="mt-1 flex-shrink-0 w-10 h-10 rounded-full {{ $color }} flex items-center justify-center shadow-sm">
                                <i class="fas {{ $icon }} text-lg"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold {{ is_null($notification->read_at) ? 'text-gray-900' : 'text-gray-700' }}">
                                    {{ $notification->data['mensaje'] ?? 'Notificación del sistema' }}
                                </p>
                                <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                                    <span class="flex items-center"><i class="far fa-clock mr-1"></i> {{ $notification->created_at->diffForHumans() }}</span>
                                    <span class="flex items-center"><i class="far fa-calendar-alt mr-1"></i> {{ $notification->created_at->format('d/m/Y H:i') }}</span>
                                    @if(is_null($notification->read_at))
                                        <span class="flex items-center text-blue-600 font-bold"><span class="w-2 h-2 rounded-full bg-blue-500 mr-1 animate-pulse"></span> Nueva</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-shrink-0 text-gray-400">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="p-10 text-center flex flex-col items-center justify-center">
                <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mb-4">
                    <i class="fas fa-bell-slash text-2xl text-gray-300"></i>
                </div>
                <h4 class="text-gray-600 font-bold">Sin Historial</h4>
                <p class="text-sm text-gray-400 mt-1">Todavía no has recibido ninguna notificación en el sistema.</p>
            </div>
        @endif
    </div>

    @if($notifications->hasPages())
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            {{ $notifications->links() }}
        </div>
    @endif

</div>
@endsection

@push('scripts')
    @vite('resources/js/panel/notifications.js')
@endpush
