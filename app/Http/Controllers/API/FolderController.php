<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use Illuminate\Http\Request;

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
        //Eliminar (soft delete) una carpeta
        $folder = Folder::findOrFail($id);
        $folder->delete();

        return response()->json(['message' => 'Carpeta eliminada correctamente.']);
    }
}
