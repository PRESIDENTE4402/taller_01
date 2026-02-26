<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LandingImagesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('landing_images')->truncate();

        DB::table('landing_images')->insert([
            [
                'type' => 'service',
                'title' => 'Servicio de Mantenimiento',
                'description' => 'Mantenimiento profesional de vehículos BMW',
                'image_url' => 'https://img.freepik.com/foto-gratis/mecanico-haciendo-servicio-mantenimiento-coche_1303-26804.jpg',
                'cloudinary_public_id' => 'landing-images/services/mecanico-mantenimiento',
                'alt_text' => 'Mecánico realizando servicio de mantenimiento a vehículo',
                'order' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'service',
                'title' => 'Diagnóstico Avanzado',
                'description' => 'Herramientas de diagnóstico de última generación',
                'image_url' => 'https://img.freepik.com/foto-gratis/hombre-herramienta-diagnostico-taller-coches_1303-26818.jpg',
                'cloudinary_public_id' => 'landing-images/services/diagnostico-taller',
                'alt_text' => 'Herramientas de diagnóstico profesionales en taller',
                'order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'service',
                'title' => 'Carga de Vehículos Eléctricos',
                'description' => 'Servicio de carga rápida para vehículos eléctricos',
                'image_url' => 'https://img.freepik.com/foto-gratis/primer-plano-coche-electrico-cargando_23-2148972418.jpg',
                'cloudinary_public_id' => 'landing-images/services/coche-electrico',
                'alt_text' => 'Vehículo eléctrico en proceso de carga',
                'order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'gallery',
                'title' => 'Trabajo Realizado 1',
                'description' => 'Proyecto de reparación completado exitosamente',
                'image_url' => 'https://res.cloudinary.com/dcfodug6m/image/upload/v1768881736/WhatsApp_Image_2026-01-19_at_8.46.46_PM_je6mv6.jpg',
                'cloudinary_public_id' => 'landing-images/gallery/WhatsApp_Image_2026-01-19_at_8.46.46_PM_je6mv6',
                'alt_text' => 'Vehículo reparado en taller',
                'order' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'gallery',
                'title' => 'Trabajo Realizado 2',
                'description' => 'Servicio de mantenimiento general completado',
                'image_url' => 'https://res.cloudinary.com/dcfodug6m/image/upload/v1768881857/WhatsApp_Image_2026-01-19_at_8.47.39_PM_wgqejr.jpg',
                'cloudinary_public_id' => 'landing-images/gallery/WhatsApp_Image_2026-01-19_at_8.47.39_PM_wgqejr',
                'alt_text' => 'Servicio de mantenimiento realizado',
                'order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'gallery',
                'title' => 'Trabajo Realizado 3',
                'description' => 'Reparación de sistemas complejos',
                'image_url' => 'https://res.cloudinary.com/dcfodug6m/image/upload/v1768882560/WhatsApp_Image_2026-01-19_at_8.49.29_PM_mr3mzj.jpg',
                'cloudinary_public_id' => 'landing-images/gallery/WhatsApp_Image_2026-01-19_at_8.49.29_PM_mr3mzj',
                'alt_text' => 'Reparación compleja en proceso',
                'order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'gallery',
                'title' => 'Trabajo Realizado 4',
                'description' => 'Servicio integral de vehículo',
                'image_url' => 'https://res.cloudinary.com/dcfodug6m/image/upload/v1768882416/WhatsApp_Image_2026-01-19_at_8.48.17_PM_e0p4f2.jpg',
                'cloudinary_public_id' => 'landing-images/gallery/WhatsApp_Image_2026-01-19_at_8.48.17_PM_e0p4f2',
                'alt_text' => 'Vehículo después de servicio integral',
                'order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
