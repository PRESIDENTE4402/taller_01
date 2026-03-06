<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\LandingImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Cloudinary\Cloudinary;

class LandingImageController extends Controller
{
    private $cloudinary;

    public function __construct()
    {
        if (class_exists(\Cloudinary\Cloudinary::class)) {
            $this->cloudinary = new \Cloudinary\Cloudinary([
                'cloud' => [
                    'cloud_name' => config('cloudinary.cloud_name'),
                    'api_key' => config('cloudinary.api_key'),
                    'api_secret' => config('cloudinary.api_secret'),
                ]
            ]);
        } else {
            $this->cloudinary = null;
        }
    }

    /**
     * Mostrar página de gestión de imágenes
     */
    public function index()
    {
        return view('panel.mantenimientos.imagenes_landing.index');
    }

    /**
     * API: Obtener lista de imágenes para DataTable
     */
    public function list(Request $request)
    {
        $images = LandingImage::query()
            ->orderBy('type')
            ->orderBy('order')
            ->get();

        return response()->json($images);
    }

    /**
     * Guardar nueva imagen
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'type' => 'required|in:logo,service,gallery,video,about',
            'image_url' => 'required|url',
            'cloudinary_public_id' => 'nullable|string',
            'alt_text' => 'nullable|max:255',
            'description' => 'nullable|string',
        ]);

        $image = LandingImage::create([
            'type' => $request->type,
            'title' => $request->title,
            'description' => $request->description,
            'image_url' => $request->image_url,
            'cloudinary_public_id' => $request->cloudinary_public_id,
            'alt_text' => $request->alt_text,
            'order' => $request->order ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Invalidar cache
        cache()->forget('landing_images');

        return response()->json([
            'success' => true,
            'message' => 'Imagen guardada correctamente',
            'data' => $image
        ]);
    }

    /**
     * Actualizar imagen existente
     */
    public function update(Request $request, $id)
    {
        $image = LandingImage::findOrFail($id);

        $request->validate([
            'title' => 'required|max:255',
            'type' => 'required|in:logo,service,gallery,video,about',
            'image_url' => 'nullable|url',
            'cloudinary_public_id' => 'nullable|string',
            'alt_text' => 'nullable|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        // Si se está reemplazando el archivo, eliminar el anterior de Cloudinary
        $newPublicId = $request->cloudinary_public_id;
        $oldPublicId = $image->cloudinary_public_id;
        if ($newPublicId && $oldPublicId && $newPublicId !== $oldPublicId) {
            $this->deleteFromCloudinary($oldPublicId, $image->type);
        }

        $image->update($request->only([
            'title',
            'type',
            'image_url',
            'cloudinary_public_id',
            'description',
            'alt_text',
            'order',
            'is_active'
        ]));

        cache()->forget('landing_images');

        return response()->json([
            'success' => true,
            'message' => 'Actualizado correctamente',
            'data' => $image->fresh()
        ]);
    }

    /**
     * Eliminar imagen
     */
    public function destroy($id)
    {
        $image = LandingImage::findOrFail($id);

        if ($image->cloudinary_public_id) {
            $this->deleteFromCloudinary($image->cloudinary_public_id, $image->type);
        }

        $image->delete();
        cache()->forget('landing_images');

        return response()->json([
            'success' => true,
            'message' => 'Imagen eliminada correctamente'
        ]);
    }

    private function deleteFromCloudinary(string $publicId, string $type = 'image'): void
    {
        if (!$this->cloudinary) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($publicId);
            return;
        }
        
        try {
            $resourceType = ($type === 'video' || str_starts_with($publicId, 'video/')) ? 'video' : 'image';
            $this->cloudinary->uploadApi()->destroy($publicId, ['resource_type' => $resourceType]);
        } catch (\Exception $e) {
            \Log::warning('Error eliminando de Cloudinary: ' . $e->getMessage());
        }
    }

    /**
     * Subir imagen a Cloudinary
     */
    public function upload(Request $request)
    {
        // Aumentar límites de PHP en tiempo de ejecución para videos
        $isVideo = $request->input('resource_type') === 'video';
        if ($isVideo) {
            @ini_set('upload_max_filesize', '200M');
            @ini_set('post_max_size', '200M');
        }

        $request->validate([
            'file' => $isVideo
                ? 'required|mimes:mp4,mov,avi,webm,mpeg,mpg|max:204800'
                : 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'type' => 'required|in:logo,service,gallery,video,about',
        ], [
            'file.max' => $isVideo ? 'El video no puede ser mayor a 200MB' : 'La imagen no puede ser mayor a 10MB',
            'file.mimes' => $isVideo ? 'Solo se permiten videos MP4, MOV, AVI o WebM' : 'Solo se permiten imágenes JPG, PNG, GIF o WebP',
        ]);

        try {
            $file = $request->file('file');
            $type = $request->type;

            if ($this->cloudinary) {
                // Subir a Cloudinary con carpeta según tipo
                $response = $this->cloudinary->uploadApi()->upload(
                    $file->getRealPath(),
                    [
                        'folder' => "landing-images/{$type}",
                        'resource_type' => 'auto',
                    ]
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Imagen subida a Cloudinary',
                    'data' => [
                        'public_id' => $response['public_id'],
                        'url' => $response['secure_url'],
                        'width' => $response['width'] ?? null,
                        'height' => $response['height'] ?? null,
                    ]
                ]);
            } else {
                // Fallback a almacenamiento local si Cloudinary no está disponible
                $path = $file->store("landing-images/{$type}", 'public');
                return response()->json([
                    'success' => true,
                    'message' => 'Imagen subida localmente',
                    'data' => [
                        'public_id' => $path,
                        'url' => asset('storage/' . $path),
                        'width' => null,
                        'height' => null,
                    ]
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API Pública: Obtener imágenes para welcome.blade.php
     */
    public function getPublicImages()
    {
        $images = Cache::remember('landing_images', 3600, function () {
            return LandingImage::active()
                ->orderBy('order')
                ->get();
        });

        return response()->json($images);
    }
}
