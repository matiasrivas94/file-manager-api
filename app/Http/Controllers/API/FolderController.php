<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FolderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //Carpetas activas
        $folders = Folder::orderBy('created_at', 'desc')->get();
        return response()->json($folders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //Valida que el nombre sea obligatorio y no mayor a 255 caracteres
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $folder = Folder::create([
            'name' => $request->name,
        ]);

        return response()->json($folder, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $folder = Folder::findOrFail($id);
        return response()->json($folder);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $folder = Folder::findOrFail($id);
        $folder->update([
            'name' => $request->name,
        ]);

        return response()->json($folder);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $folder = Folder::findOrFail($id);
        $fileCount = $folder->files()->count();
        
        // Si tiene archivos, los elimina junto con la carpeta
        if ($fileCount > 0) {
            // Eliminar archivos físicos y registros
            foreach ($folder->files as $file) {
                Storage::delete($file->path);
                $file->delete();
            }
        }
        
        $folder->delete();
        
        return response()->json([
            'message' => $fileCount > 0 
                ? "Carpeta eliminada junto con {$fileCount} archivo(s)" 
                : "Carpeta eliminada"
        ]);
    }

    // Método para obtener info antes de eliminar
    public function info($id) {
        $folder = Folder::findOrFail($id);
        return response()->json([
            'folder' => $folder,
            'file_count' => $folder->files()->count()
        ]);
    }
}
