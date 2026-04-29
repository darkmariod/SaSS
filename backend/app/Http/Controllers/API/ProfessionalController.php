<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AvailabilityService;
use App\Models\Professional;
use App\Http\Resources\AvailabilitySlotResource;

class ProfessionalController extends Controller
{
    protected $availabilityService;

    public function __construct(AvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    /**
     * @OA\Get(
     *     path="/professionals/{id}/availability",
     *     summary="Obtener disponibilidad de un profesional",
     *     tags={"Profesionales"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="date", in="query", required=true, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="branch_id", in="query", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="service_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Slots disponibles")
     * )
     */
    public function availability(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'branch_id' => 'required|exists:branches,id',
            'service_id' => 'nullable|exists:services,id',
        ]);

        $slots = $this->availabilityService->getAvailableSlots(
            $request->branch_id,
            $id,
            $request->date,
            $request->service_id
        );

        return response()->json([
            'success' => true,
            'data' => AvailabilitySlotResource::collection($slots)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/professionals",
     *     summary="Listar profesionales",
     *     tags={"Profesionales"},
     *     @OA\Parameter(name="branch_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Listado")
     * )
     */
    public function index(Request $request)
    {
        $query = Professional::where('is_active', true);
        
        if ($request->has('branch_id')) {
            $query->whereHas('branches', function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            });
        }

        $professionals = $query->with('services')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $professionals
        ]);
    }

    /**
     * @OA\Get(
     *     path="/professionals/{id}",
     *     summary="Detalle de profesional",
     *     tags={"Profesionales"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detalle")
     * )
     */
    public function show($id)
    {
        $professional = Professional::with(['services.service', 'branches'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $professional
        ]);
    }
}
