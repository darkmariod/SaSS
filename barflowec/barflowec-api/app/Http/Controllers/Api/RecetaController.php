<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Receta;
use Illuminate\Http\Request;

class RecetaController extends Controller
{
    public function index()
    {
        return response()->json(Receta::latest()->paginate(10));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'preparation' => ['nullable', 'string'],
            'servings' => ['nullable', 'integer', 'min:1'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        return response()->json(Receta::create($data), 201);
    }

    public function show(Receta $receta)
    {
        return response()->json($receta);
    }

    public function update(Request $request, Receta $receta)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'preparation' => ['nullable', 'string'],
            'servings' => ['nullable', 'integer', 'min:1'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $receta->update($data);

        return response()->json($receta);
    }

    public function destroy(Receta $receta)
    {
        $receta->delete();

        return response()->json(['message' => 'Receta eliminada.']);
    }
}
