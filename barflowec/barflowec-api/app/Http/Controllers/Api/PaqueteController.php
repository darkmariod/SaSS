<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Paquete;
use Illuminate\Http\Request;

class PaqueteController extends Controller
{
    public function index()
    {
        return response()->json(Paquete::latest()->paginate(10));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'guests_min' => ['nullable', 'integer', 'min:1'],
            'guests_max' => ['nullable', 'integer', 'min:1'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        return response()->json(Paquete::create($data), 201);
    }

    public function show(Paquete $paquete)
    {
        return response()->json($paquete);
    }

    public function update(Request $request, Paquete $paquete)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'guests_min' => ['nullable', 'integer', 'min:1'],
            'guests_max' => ['nullable', 'integer', 'min:1'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $paquete->update($data);

        return response()->json($paquete);
    }

    public function destroy(Paquete $paquete)
    {
        $paquete->delete();

        return response()->json(['message' => 'Paquete eliminado.']);
    }
}
