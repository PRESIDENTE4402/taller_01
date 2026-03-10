<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plantillas_mensajes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique(); // cita_confirmacion, cita_recordatorio, orden_cotizacion, orden_listo, orden_fase
            $table->string('asunto')->nullable(); // Para correos
            $table->text('cuerpo'); // Contenido con placeholders como {cliente}, {orden}, {placa}, {link}
            $table->enum('tipo_canal', ['whatsapp', 'email', 'ambos'])->default('ambos');
            $table->string('categoria'); // Citas, Órdenes
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Seed default templates
        $templates = [
            [
                'nombre' => 'Confirmación de Cita',
                'slug' => 'cita_confirmacion',
                'asunto' => 'Confirmación de tu Cita en Taller',
                'cuerpo' => "Hola {cliente}, te confirmamos tu cita para el vehículo {vehiculo} (Placa: {placa}) el día {fecha} a las {hora}. Te esperamos en nuestra sucursal {sucursal}.",
                'tipo_canal' => 'ambos',
                'categoria' => 'Citas'
            ],
            [
                'nombre' => 'Recordatorio de Cita',
                'slug' => 'cita_recordatorio',
                'asunto' => 'Recordatorio: Mañana tienes una cita con nosotros',
                'cuerpo' => "Hola {cliente}, te recordamos tu cita de mañana {fecha} a las {hora} para tu vehículo {vehiculo}. ¡No olvides traerlo a tiempo!",
                'tipo_canal' => 'ambos',
                'categoria' => 'Citas'
            ],
            [
                'nombre' => 'Envío de Cotización',
                'slug' => 'orden_cotizacion',
                'asunto' => 'Cotización de Servicio - Orden #{codigo_orden}',
                'cuerpo' => "Hola {cliente}, te enviamos la cotización de los servicios para tu vehículo {vehiculo} (Placa: {placa}).\n\nPuedes ver el detalle aquí: {link}\n\nQuedamos a la espera de tu autorización.",
                'tipo_canal' => 'ambos',
                'categoria' => 'Órdenes'
            ],
            [
                'nombre' => 'Vehículo Listo',
                'slug' => 'orden_listo',
                'asunto' => '¡Tu vehículo ya está listo! - Orden #{codigo_orden}',
                'cuerpo' => "¡Buenas noticias {cliente}! Los trabajos en tu vehículo {vehiculo} han finalizado. Ya puedes pasar a recogerlo en sucursal {sucursal}.\n\nVer detalle: {link}",
                'tipo_canal' => 'ambos',
                'categoria' => 'Órdenes'
            ],
            [
                'nombre' => 'Cambio de Fase / Avance',
                'slug' => 'orden_fase',
                'asunto' => 'Actualización de tu vehículo - Orden #{codigo_orden}',
                'cuerpo' => "Hola {cliente}, te informamos que tu vehículo {vehiculo} ha cambiado a la fase: {fase}.\n\nSeguimos trabajando. Ver avances aquí: {link}",
                'tipo_canal' => 'ambos',
                'categoria' => 'Órdenes'
            ],
        ];

        foreach ($templates as $template) {
            DB::table('plantillas_mensajes')->insert(array_merge($template, ['created_at' => now(), 'updated_at' => now()]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantillas_mensajes');
    }
};
