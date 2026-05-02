<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\User;

class ServiceController extends Controller
{
    public function index(Request $request, $consultantId)
    {
        try {
            $consultant = User::where('id', $consultantId)
                ->where('rol_id', 2)
                ->first();

            if (!$consultant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Consultor no encontrado'
                ], 404);
            }

            $services = Service::where('consultant_id', $consultantId)
                ->where('is_active', true)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $services
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener servicios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->rol_id != 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo consultores pueden crear servicios'
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price_per_hour' => 'required|numeric|min:0',
                'duration_minutes' => 'nullable|integer|min:15|max:480'
            ]);

            $service = Service::create([
                'consultant_id' => $user->id,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'price_per_hour' => $validated['price_per_hour'],
                'duration_minutes' => $validated['duration_minutes'] ?? 60,
                'is_active' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Servicio creado correctamente',
                'data' => $service
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear servicio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            $service = Service::find($id);
            
            if (!$service) {
                return response()->json([
                    'success' => false,
                    'message' => 'Servicio no encontrado'
                ], 404);
            }

            if ($service->consultant_id != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado'
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|nullable|string',
                'price_per_hour' => 'sometimes|numeric|min:0',
                'duration_minutes' => 'sometimes|integer|min:15|max:480',
                'is_active' => 'sometimes|boolean'
            ]);

            $service->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Servicio actualizado',
                'data' => $service
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            $service = Service::find($id);
            
            if (!$service) {
                return response()->json([
                    'success' => false,
                    'message' => 'Servicio no encontrado'
                ], 404);
            }

            if ($service->consultant_id != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado'
                ], 403);
            }

            // Soft delete - marcar como inactivo
            $service->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Servicio desactivado'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
