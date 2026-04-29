<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    public function run()
    {
        $methods = [
            ['code' => 'paypal', 'name' => 'PayPal', 'is_active' => true],
            ['code' => 'stripe', 'name' => 'Stripe', 'is_active' => false],
            ['code' => 'cash', 'name' => 'Efectivo', 'is_active' => true],
            ['code' => 'transfer', 'name' => 'Transferencia', 'is_active' => true],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(['code' => $method['code']], $method);
        }
    }
}
