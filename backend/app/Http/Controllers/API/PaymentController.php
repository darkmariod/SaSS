<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CreatePaymentRequest;
use App\Services\PaymentService;
use App\Models\Payment;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function create(CreatePaymentRequest $request)
    {
        try {
            $result = $this->paymentService->createPayment(
                $request->user()->id,
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Pago iniciado',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear pago',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function paypalWebhook(Request $request)
    {
        $result = $this->paymentService->handlePayPalWebhook($request->all());

        return response()->json([
            'message' => $result['status'] ?? 'processed'
        ]);
    }

    public function myPayments(Request $request)
    {
        $payments = Payment::with(['reservation.branch', 'reservation.professional', 'paymentMethod'])
            ->whereHas('reservation', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }
}
