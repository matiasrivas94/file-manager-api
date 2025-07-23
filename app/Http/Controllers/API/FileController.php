<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //Listar todos los archivos NO eliminados
        $files = File::with('folder')->orderBy('created_at', 'desc')->get();
        return response()->json($files);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,ppt,pptx,txt|max:10240', // máx 10MB
            'folder_id' => 'required|exists:folders,id',
        ]);

        $uploadedFile = $request->file('file');

        // Generar nombre único
        $newName = Str::uuid()->toString() . '.' . $uploadedFile->getClientOriginalExtension();

        // Guardar en storage/app/files/
        $path = $uploadedFile->storeAs('files', $newName);

        // Crear registro en la base de datos
        $file = File::create([
            'name' => $newName,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'mime_type' => $uploadedFile->getClientMimeType(),
            'size' => $uploadedFile->getSize(),
            'path' => $path,
            'folder_id' => $request->folder_id,
        ]);

        return response()->json($file, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $file = File::with('folder')->findOrFail($id);
        return response()->json($file);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //Actualiza el nombre o mover el archivo a otra carpeta.
       $request->validate([
            'folder_id' => 'sometimes|exists:folders,id',
            'original_name' => 'sometimes|string|max:255',
        ]);

        $file = File::findOrFail($id);

        $file->update($request->only(['folder_id', 'original_name']));

        return response()->json($file);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $file = File::findOrFail($id);
        $file->delete(); // soft delete

        return response()->json(['message' => 'Archivo eliminado correctamente.']);
    }

    /**
     * Restaurar archivo eliminado.
     */
    public function restore($id)
    {
        $file = File::onlyTrashed()->findOrFail($id);
        $file->restore();

        return response()->json(['message' => 'Archivo restaurado con éxito.']);
    }

    /**
     * Borrar archivo definitivamente.
     */
    public function forceDelete($id)
    {
        $file = File::onlyTrashed()->findOrFail($id);

        // Eliminar archivo físico si existe!
        if (Storage::exists($file->path)) {
            Storage::delete($file->path);
        }

        // Eliminar registro en base de datos!
        $file->forceDelete();

        return response()->json(['message' => 'Archivo eliminado permanentemente.']);
    }
}
