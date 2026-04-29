<?php
namespace App\Services;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Reservation;
use App\Models\WebhookLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal;
use Illuminate\Support\Str;

class PaymentService
{
    public function createPayment($userId, $data)
    {
        return DB::transaction(function () use ($userId, $data) {
            $reservation = Reservation::where('user_id', $userId)
                ->findOrFail($data['reservation_id']);

            if ($reservation->payment_status !== 'pending') {
                throw new \Exception('La reserva ya tiene un pago asociado');
            }

            $paymentMethod = PaymentMethod::where('code', $data['payment_method_code'])
                ->where('is_active', true)
                ->firstOrFail();

            $payment = Payment::create([
                'reservation_id' => $reservation->id,
                'payment_method_id' => $paymentMethod->id,
                'reference' => Str::uuid()->toString(),
                'amount' => $reservation->total_amount,
                'status' => 'pending',
            ]);

            if ($paymentMethod->code === 'paypal') {
                return $this->createPayPalOrder($payment);
            }

            return [
                'payment_id' => $payment->id,
                'reference' => $payment->reference,
                'status' => 'pending',
            ];
        });
    }

    protected function createPayPalOrder(Payment $payment)
    {
        $provider = new PayPal();
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        $order = $provider->createOrder([
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "reference_id" => $payment->reference,
                    "custom_id" => $payment->reference,
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => $payment->amount
                    ]
                ]
            ],
            "application_context" => [
                "return_url" => url('/api/paypal/success'),
                "cancel_url" => url('/api/paypal/cancel')
            ]
        ]);

        $payment->transactions()->create([
            'transaction_id' => $order['id'],
            'status' => 'pending',
            'response_json' => json_encode($order),
        ]);

        $payment->update(['status' => 'processing']);

        return [
            'payment_id' => $payment->id,
            'reference' => $payment->reference,
            'paypal_order_id' => $order['id'],
            'approval_url' => collect($order['links'])->firstWhere('rel', 'approve')['href'] ?? null,
            'status' => 'processing',
        ];
    }

    public function handlePayPalWebhook($payload)
    {
        Log::info('PayPal Webhook:', $payload);

        $eventType = $payload['event_type'] ?? null;
        $resource = $payload['resource'] ?? [];

        $eventId = $payload['id'] ?? null;
        if ($eventId && WebhookLog::where('event_id', $eventId)->exists()) {
            Log::info('Webhook ya procesado:', ['event_id' => $eventId]);
            return ['status' => 'already_processed'];
        }

        WebhookLog::create([
            'provider' => 'paypal',
            'event_id' => $eventId,
            'event_type' => $eventType,
            'payload' => json_encode($payload),
        ]);

        if ($eventType === 'CHECKOUT.ORDER.APPROVED') {
            return $this->handleOrderApproved($resource);
        }

        if ($eventType === 'PAYMENT.CAPTURE.COMPLETED') {
            return $this->handleCaptureCompleted($resource);
        }

        return ['status' => 'ignored'];
    }

    protected function handleOrderApproved($resource)
    {
        $orderId = $resource['id'] ?? null;
        if (!$orderId) return ['status' => 'error'];

        $provider = new PayPal();
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        $capture = $provider->capturePaymentOrder($orderId);

        Log::info('Captura automática:', $capture);
        return ['status' => 'captured'];
    }

    protected function handleCaptureCompleted($resource)
    {
        $reference = $resource['custom_id'] ?? null;
        $transactionId = $resource['id'] ?? null;
        $status = $resource['status'] ?? null;

        if (!$reference || $status !== 'COMPLETED') {
            return ['status' => 'invalid_data'];
        }

        $payment = Payment::where('reference', $reference)
            ->whereIn('status', ['pending', 'processing'])
            ->first();

        if (!$payment) {
            Log::warning('Payment no encontrado para reference:', ['reference' => $reference]);
            return ['status' => 'payment_not_found'];
        }

        DB::transaction(function () use ($payment, $transactionId, $resource) {
            $payment->update(['status' => 'completed']);

            $payment->transactions()->updateOrCreate(
                ['transaction_id' => $transactionId],
                [
                    'status' => 'success',
                    'response_json' => json_encode($resource),
                ]
            );

            $payment->reservation->update([
                'reservation_status' => 'confirmed',
                'payment_status' => 'paid',
            ]);
        });

        return ['status' => 'completed'];
    }
}
