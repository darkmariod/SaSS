<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ingrediente;
use Illuminate\Http\Request;

class IngredienteController extends Controller
{
    public function index()
    {
        return response()->json(Ingrediente::latest()->paginate(10));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:50'],
            'stock' => ['nullable', 'numeric', 'min:0'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        return response()->json(Ingrediente::create($data), 201);
    }

    public function show(Ingrediente $ingrediente)
    {
        return response()->json($ingrediente);
    }

    public function update(Request $request, Ingrediente $ingrediente)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:50'],
            'stock' => ['nullable', 'numeric', 'min:0'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $ingrediente->update($data);

        return response()->json($ingrediente);
    }

    public function destroy(Ingrediente $ingrediente)
    {
        $ingrediente->delete();

        return response()->json(['message' => 'Ingrediente eliminado.']);
    }
}
