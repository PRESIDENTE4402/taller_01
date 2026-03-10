<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventario_recepcion_items', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique(); // encendedor, radio, etc
            $table->enum('tipo', ['si_no', 'cantidad', 'tapiceria', 'documentos'])->default('si_no');
            $table->enum('seccion', ['documentos_accesorios', 'herramientas_exterior'])->default('documentos_accesorios');
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Seed default items
        $items = [
            // Sección 1: Documentos y Accesorios
            ['nombre' => 'Documentos', 'slug' => 'documentos', 'tipo' => 'documentos', 'seccion' => 'documentos_accesorios', 'orden' => 1],
            ['nombre' => 'Encendedor', 'slug' => 'encendedor', 'tipo' => 'si_no', 'seccion' => 'documentos_accesorios', 'orden' => 2],
            ['nombre' => 'Radio / Frontal', 'slug' => 'radio', 'tipo' => 'si_no', 'seccion' => 'documentos_accesorios', 'orden' => 3],
            ['nombre' => 'Llavero', 'slug' => 'llavero', 'tipo' => 'si_no', 'seccion' => 'documentos_accesorios', 'orden' => 4],
            ['nombre' => 'Control Alarma', 'slug' => 'control_alarma', 'tipo' => 'si_no', 'seccion' => 'documentos_accesorios', 'orden' => 5],
            ['nombre' => 'Batería', 'slug' => 'bateria', 'tipo' => 'si_no', 'seccion' => 'documentos_accesorios', 'orden' => 6],
            ['nombre' => 'Tricket (Gato)', 'slug' => 'tricket', 'tipo' => 'si_no', 'seccion' => 'documentos_accesorios', 'orden' => 7],
            ['nombre' => 'Barilla de Gato', 'slug' => 'barilla', 'tipo' => 'si_no', 'seccion' => 'documentos_accesorios', 'orden' => 8],
            ['nombre' => 'Llave Seguridad', 'slug' => 'llave_seguridad', 'tipo' => 'si_no', 'seccion' => 'documentos_accesorios', 'orden' => 9],
            ['nombre' => 'Llave de Chuchos', 'slug' => 'llave_chuchos', 'tipo' => 'si_no', 'seccion' => 'documentos_accesorios', 'orden' => 10],
            ['nombre' => 'Estado Tapicería', 'slug' => 'tapiceria', 'tipo' => 'tapiceria', 'seccion' => 'documentos_accesorios', 'orden' => 11],

            // Sección 2: Herramientas y Exterior
            ['nombre' => 'Llanta Repuesto', 'slug' => 'llanta_repuesto', 'tipo' => 'si_no', 'seccion' => 'herramientas_exterior', 'orden' => 12],
            ['nombre' => 'Estuche Herram.', 'slug' => 'herramientas', 'tipo' => 'si_no', 'seccion' => 'herramientas_exterior', 'orden' => 13],
            ['nombre' => 'Extinguidor', 'slug' => 'extinguidor', 'tipo' => 'si_no', 'seccion' => 'herramientas_exterior', 'orden' => 14],
            ['nombre' => 'Cables Corriente', 'slug' => 'cables', 'tipo' => 'si_no', 'seccion' => 'herramientas_exterior', 'orden' => 15],
            ['nombre' => 'Antena', 'slug' => 'antena', 'tipo' => 'si_no', 'seccion' => 'herramientas_exterior', 'orden' => 16],
            ['nombre' => 'Tapón Tanque', 'slug' => 'tapon_tanque', 'tipo' => 'si_no', 'seccion' => 'herramientas_exterior', 'orden' => 17],
            ['nombre' => 'Chibola Palanca', 'slug' => 'chibola', 'tipo' => 'si_no', 'seccion' => 'herramientas_exterior', 'orden' => 18],
            ['nombre' => 'Tapones Ruedas', 'slug' => 'tapones_ruedas', 'tipo' => 'cantidad', 'seccion' => 'herramientas_exterior', 'orden' => 19],
            ['nombre' => 'Chuchos (Tuercas)', 'slug' => 'chuchos', 'tipo' => 'cantidad', 'seccion' => 'herramientas_exterior', 'orden' => 20],
            ['nombre' => 'Plumillas', 'slug' => 'plumillas', 'tipo' => 'cantidad', 'seccion' => 'herramientas_exterior', 'orden' => 21],
            ['nombre' => 'Alfombras', 'slug' => 'alfombras', 'tipo' => 'cantidad', 'seccion' => 'herramientas_exterior', 'orden' => 22],
            ['nombre' => 'Retrovisores', 'slug' => 'retrovisores', 'tipo' => 'cantidad', 'seccion' => 'herramientas_exterior', 'orden' => 23],
            ['nombre' => 'Triangulos', 'slug' => 'triangulos', 'tipo' => 'cantidad', 'seccion' => 'herramientas_exterior', 'orden' => 24],
        ];

        foreach ($items as $item) {
            DB::table('inventario_recepcion_items')->insert(array_merge($item, ['created_at' => now(), 'updated_at' => now()]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario_recepcion_items');
    }
};
