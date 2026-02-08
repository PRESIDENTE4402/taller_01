<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cita;
use App\Mail\CitaReminder;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'citas:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía correos electrónicos de recordatorio para las citas de mañana';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando envío de recordatorios de citas...');

        // Buscar citas programadas para MAÑANA
        // Usamos whereDate para obtener todas las citas del día completo
        $tomorrow = Carbon::tomorrow()->toDateString();
        
        $citas = Cita::with(['cliente', 'vehiculo.marca', 'vehiculo.modelo'])
            ->whereDate('fecha_programada', $tomorrow)
            ->whereIn('estado', ['pendiente', 'confirmada']) // Solo enviar a citas activas
            ->get();

        if ($citas->isEmpty()) {
            $this->info("No hay citas programadas para mañana ($tomorrow).");
            return;
        }

        $count = 0;
        foreach ($citas as $cita) {
            // Verificar si el cliente tiene email
            if ($cita->cliente && $cita->cliente->email) {
                try {
                    Mail::to($cita->cliente->email)->send(new CitaReminder($cita));
                    $this->info("Recordatorio enviado a: {$cita->cliente->nombre} ({$cita->cliente->email})");
                    $count++;
                } catch (\Exception $e) {
                    $this->error("Error al enviar a {$cita->cliente->nombre}: " . $e->getMessage());
                }
            } else {
                $this->warn("Cliente {$cita->cliente->nombre} no tiene email registrado. Cita ID: {$cita->id}");
            }
        }

        $this->info("Proceso finalizado. Total correos enviados: $count");
    }
}
