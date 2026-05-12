<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\Evento;
use App\Models\Ingrediente;
use App\Models\Pago;
use App\Models\Paquete;
use App\Models\Receta;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@barflowec.com'],
            [
                'name' => 'Super Admin',
                'password' => 'password',
                'role' => 'super_admin',
            ]
        );

        $cliente = Cliente::create([
            'name' => 'Eventos Andrade',
            'email' => 'contacto@eventosandrade.com',
            'phone' => '0999999999',
            'company' => 'Eventos Andrade',
            'identification' => '1799999999001',
            'address' => 'Quito, Ecuador',
            'notes' => 'Cliente corporativo frecuente.',
            'status' => 'activo',
        ]);

        Ingrediente::insert([
            [
                'name' => 'Ron blanco',
                'unit' => 'ml',
                'stock' => 5000,
                'min_stock' => 1000,
                'cost' => 18,
                'status' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Hierbabuena',
                'unit' => 'ramo',
                'stock' => 20,
                'min_stock' => 5,
                'cost' => 1.50,
                'status' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Limón',
                'unit' => 'unidad',
                'stock' => 80,
                'min_stock' => 20,
                'cost' => 0.15,
                'status' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Receta::create([
            'name' => 'Mojito clásico',
            'description' => 'Cóctel refrescante con ron, limón y hierbabuena.',
            'preparation' => 'Macerar limón con hierbabuena, agregar ron, hielo y soda.',
            'servings' => 1,
            'price' => 6.50,
            'status' => 'activa',
        ]);

        Paquete::create([
            'name' => 'Bar básico 50 personas',
            'description' => 'Servicio básico de coctelería para eventos pequeños.',
            'guests_min' => 20,
            'guests_max' => 50,
            'price' => 450,
            'status' => 'activo',
        ]);

        $cotizacion = Cotizacion::create([
            'cliente_id' => $cliente->id,
            'quote_number' => 'COT-0001',
            'event_type' => 'Boda',
            'event_date' => '2026-05-18',
            'guests' => 80,
            'subtotal' => 850,
            'tax' => 102,
            'total' => 952,
            'status' => 'pendiente',
            'notes' => 'Primera cotización demo.',
        ]);

        $evento = Evento::create([
            'cliente_id' => $cliente->id,
            'cotizacion_id' => $cotizacion->id,
            'name' => 'Boda Cumbayá',
            'event_date' => '2026-05-18',
            'location' => 'Cumbayá',
            'bartender_name' => 'Carlos Ruiz',
            'status' => 'programado',
            'notes' => 'Evento demo conectado a cotización.',
        ]);

        Pago::create([
            'cotizacion_id' => $cotizacion->id,
            'evento_id' => $evento->id,
            'amount' => 300,
            'payment_method' => 'transferencia',
            'paid_at' => '2026-05-11',
            'reference' => 'TRX-001',
            'status' => 'pagado',
            'notes' => 'Anticipo inicial.',
        ]);
    }
}
