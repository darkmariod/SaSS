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
                'name' => 'Admin',
                'password' => 'password',
                'role' => 'admin',
            ]
        );

        $hacienda = Cliente::create([
            'name' => 'Hacienda La Esperanza',
            'email' => 'eventos@haciendalaesperanza.com',
            'phone' => '0998421100',
            'company' => 'Hacienda La Esperanza',
            'identification' => '1790012345001',
            'address' => 'Cumbayá, Quito',
            'notes' => 'Cliente ideal para bodas y eventos premium.',
            'status' => 'activo',
        ]);

        $andrade = Cliente::create([
            'name' => 'Eventos Andrade',
            'email' => 'contacto@eventosandrade.com',
            'phone' => '0987654321',
            'company' => 'Eventos Andrade',
            'identification' => '1799999999001',
            'address' => 'Quito, Ecuador',
            'notes' => 'Empresa recurrente para coordinación de eventos sociales.',
            'status' => 'activo',
        ]);

        $nova = Cliente::create([
            'name' => 'Corporativo Nova',
            'email' => 'rrhh@corporativonova.com',
            'phone' => '0975544332',
            'company' => 'Corporativo Nova',
            'identification' => '1798887776001',
            'address' => 'Av. República, Quito',
            'notes' => 'Cliente corporativo para lanzamientos y eventos internos.',
            'status' => 'activo',
        ]);

        $boda = Cliente::create([
            'name' => 'Boda M & J',
            'email' => 'maria.jose@email.com',
            'phone' => '0993322110',
            'company' => null,
            'identification' => '1712345678',
            'address' => 'Samborondón',
            'notes' => 'Pareja interesada en paquete premium de boda.',
            'status' => 'activo',
        ]);

        Receta::insert([
            [
                'name' => 'Coordinación integral',
                'description' => 'Planificación y coordinación general del evento.',
                'preparation' => 'Reunión inicial, cronograma, proveedores, montaje y control del evento.',
                'servings' => 1,
                'price' => 450,
                'status' => 'activa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Catering premium',
                'description' => 'Servicio gastronómico para eventos sociales y corporativos.',
                'preparation' => 'Menú personalizado, montaje de estaciones y servicio durante el evento.',
                'servings' => 80,
                'price' => 1600,
                'status' => 'activa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Decoración floral',
                'description' => 'Ambientación floral para ceremonia, recepción y mesas.',
                'preparation' => 'Diseño floral, centros de mesa, arco principal y decoración de espacios.',
                'servings' => 1,
                'price' => 750,
                'status' => 'activa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sonido e iluminación',
                'description' => 'Sistema de audio, luces ambientales y soporte técnico.',
                'preparation' => 'Instalación, pruebas, operación durante evento y desmontaje.',
                'servings' => 1,
                'price' => 680,
                'status' => 'activa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Personal de servicio',
                'description' => 'Equipo operativo para atención de invitados.',
                'preparation' => 'Asignación de meseros, coordinador operativo y asistentes de montaje.',
                'servings' => 6,
                'price' => 360,
                'status' => 'activa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Ingrediente::insert([
            [
                'name' => 'Mesas cocteleras',
                'unit' => 'unidad',
                'stock' => 35,
                'min_stock' => 10,
                'cost' => 12,
                'status' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mantelería blanca',
                'unit' => 'unidad',
                'stock' => 80,
                'min_stock' => 25,
                'cost' => 4.50,
                'status' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Equipo de sonido',
                'unit' => 'unidad',
                'stock' => 4,
                'min_stock' => 2,
                'cost' => 180,
                'status' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Centros de mesa',
                'unit' => 'unidad',
                'stock' => 60,
                'min_stock' => 20,
                'cost' => 8,
                'status' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Vajilla premium',
                'unit' => 'unidad',
                'stock' => 140,
                'min_stock' => 50,
                'cost' => 2.25,
                'status' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sillas Tiffany',
                'unit' => 'unidad',
                'stock' => 25,
                'min_stock' => 60,
                'cost' => 5.50,
                'status' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Paquete::insert([
            [
                'name' => 'Boda Premium 100 invitados',
                'description' => 'Coordinación, decoración, catering, sonido y personal para boda premium.',
                'guests_min' => 80,
                'guests_max' => 120,
                'price' => 4200,
                'status' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Evento Corporativo 80 invitados',
                'description' => 'Producción integral para lanzamientos, cenas empresariales y eventos internos.',
                'guests_min' => 50,
                'guests_max' => 90,
                'price' => 2800,
                'status' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cumpleaños Social 40 invitados',
                'description' => 'Paquete social con ambientación, servicio y coordinación básica.',
                'guests_min' => 25,
                'guests_max' => 45,
                'price' => 1250,
                'status' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $propBoda = Cotizacion::create([
            'cliente_id' => $hacienda->id,
            'quote_number' => 'PROP-0001',
            'event_type' => 'Boda',
            'event_date' => '2026-05-24',
            'guests' => 100,
            'subtotal' => 4200,
            'tax' => 504,
            'total' => 4704,
            'status' => 'aprobada',
            'notes' => 'Propuesta premium aprobada para boda en hacienda.',
        ]);

        $propCorporativa = Cotizacion::create([
            'cliente_id' => $nova->id,
            'quote_number' => 'PROP-0002',
            'event_type' => 'Corporativo',
            'event_date' => '2026-05-30',
            'guests' => 80,
            'subtotal' => 2800,
            'tax' => 336,
            'total' => 3136,
            'status' => 'enviada',
            'notes' => 'Propuesta enviada para lanzamiento de marca.',
        ]);

        $propSocial = Cotizacion::create([
            'cliente_id' => $boda->id,
            'quote_number' => 'PROP-0003',
            'event_type' => 'Cumpleaños',
            'event_date' => '2026-06-05',
            'guests' => 40,
            'subtotal' => 1250,
            'tax' => 150,
            'total' => 1400,
            'status' => 'pendiente',
            'notes' => 'Pendiente confirmación de fecha y alcance.',
        ]);

        Evento::create([
            'cliente_id' => $hacienda->id,
            'cotizacion_id' => $propBoda->id,
            'name' => 'Boda Hacienda La Esperanza',
            'event_date' => '2026-05-24',
            'location' => 'Cumbayá',
            'bartender_name' => 'Equipo operativo A',
            'status' => 'confirmado',
            'notes' => 'Montaje inicia 09:00. Ceremonia 16:00.',
        ]);

        Evento::create([
            'cliente_id' => $nova->id,
            'cotizacion_id' => $propCorporativa->id,
            'name' => 'Lanzamiento Corporativo Nova',
            'event_date' => '2026-05-30',
            'location' => 'Quito',
            'bartender_name' => 'Equipo operativo B',
            'status' => 'programado',
            'notes' => 'Evento empresarial con montaje de escenario e iluminación.',
        ]);

        Evento::create([
            'cliente_id' => $boda->id,
            'cotizacion_id' => $propSocial->id,
            'name' => 'Cumpleaños Social Samborondón',
            'event_date' => '2026-06-05',
            'location' => 'Samborondón',
            'bartender_name' => 'Por asignar',
            'status' => 'programado',
            'notes' => 'Pendiente confirmar proveedor de sonido.',
        ]);

        Pago::create([
            'cotizacion_id' => $propBoda->id,
            'evento_id' => 1,
            'amount' => 2352,
            'payment_method' => 'transferencia',
            'paid_at' => '2026-05-12',
            'reference' => 'ANT-PROP-0001',
            'status' => 'pagado',
            'notes' => 'Anticipo 50% de boda premium.',
        ]);

        Pago::create([
            'cotizacion_id' => $propBoda->id,
            'evento_id' => 1,
            'amount' => 2352,
            'payment_method' => 'transferencia',
            'paid_at' => null,
            'reference' => 'SALDO-PROP-0001',
            'status' => 'pendiente',
            'notes' => 'Saldo pendiente antes del evento.',
        ]);

        Pago::create([
            'cotizacion_id' => $propCorporativa->id,
            'evento_id' => 2,
            'amount' => 1000,
            'payment_method' => 'transferencia',
            'paid_at' => '2026-05-12',
            'reference' => 'ANT-PROP-0002',
            'status' => 'pagado',
            'notes' => 'Anticipo evento corporativo.',
        ]);
    }
}
