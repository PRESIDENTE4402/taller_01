import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css',
                'resources/js/app.js',
                'resources/js/landing.js',
                'resources/js/auth/login.js',
                'resources/js/panel/marcas.js',
                'resources/js/panel/versiones.js',
                'resources/js/panel/sucursales.js',
                'resources/js/panel/roles.js',
                'resources/js/panel/usuarios.js',
                'resources/js/panel/citas.js',
                'resources/js/panel/asistencia.js',
                'resources/js/panel/categorias.js',
                'resources/js/panel/repuestos.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },

        host: 'localhost',
        hmr: {
            host: 'localhost',
        },


    },
});
