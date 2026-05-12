<?php

namespace App\Console\Commands;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BarflowDemo extends Command
{
    protected $signature = 'barflow:demo {--fresh : Limpiar base de datos antes de sembrar}';

    protected $description = 'Carga datos demo de BarFlowEC y muestra resumen en terminal';

    public function handle(): int
    {
        if ($this->option('fresh')) {
            if ($this->confirm('¿Estás seguro de limpiar toda la base de datos?')) {
                $this->call('migrate:fresh', ['--seed' => false]);
                $this->info('🧹 Base de datos limpiada.');
            } else {
                $this->warn('Operación cancelada.');

                return Command::FAILURE;
            }
        }

        $this->info('🚀 Cargando datos demo de BarFlowEC...');
        $this->newLine();

        $this->call('db:seed', ['class' => DatabaseSeeder::class]);

        $this->newLine();
        $this->info('📊 RESUMEN DE DATOS CARGADOS');
        $this->info('═══════════════════════════════');

        $this->table(
            ['Entidad', 'Cantidad', 'Estado'],
            [
                ['👤 Usuarios', DB::table('users')->count(), '✅'],
                ['🏢 Clientes', DB::table('clientes')->count(), '✅'],
                ['📋 Servicios', DB::table('recetas')->count(), '✅'],
                ['📦 Paquetes', DB::table('paquetes')->count(), '✅'],
                ['📦 Recursos', DB::table('ingredientes')->count(), '✅'],
                ['📄 Propuestas', DB::table('cotizaciones')->count(), '✅'],
                ['📅 Eventos', DB::table('eventos')->count(), '✅'],
                ['💰 Pagos', DB::table('pagos')->count(), '✅'],
            ]
        );

        $this->newLine();
        $this->info('🔑 Credenciales de acceso:');
        $this->line('   Email:    admin@barflowec.com');
        $this->line('   Password: password');
        $this->newLine();

        $this->info('📋 Próximos eventos:');
        $eventos = DB::table('eventos')
            ->join('clientes', 'eventos.cliente_id', '=', 'clientes.id')
            ->select('eventos.name', 'clientes.name as cliente', 'eventos.event_date', 'eventos.status')
            ->orderBy('eventos.event_date')
            ->get()
            ->toArray();

        if (! empty($eventos)) {
            $this->table(
                ['Evento', 'Cliente', 'Fecha', 'Estado'],
                array_map(fn ($e) => [$e->name, $e->cliente, $e->event_date, $e->status], $eventos)
            );
        }

        $this->newLine();
        $this->info('💡 Para iniciar el servidor: php artisan serve');
        $this->info('🌐 Frontend: npm run dev (en barflowec-web/)');
        $this->newLine();

        $this->info('✅ Datos demo cargados exitosamente.');

        return Command::SUCCESS;
    }
}
