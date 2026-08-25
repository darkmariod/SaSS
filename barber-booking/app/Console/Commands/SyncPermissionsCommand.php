<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Crea los permisos que exigen las políticas de Shield y se los asigna a cada rol.
 *
 * Sin esto el panel queda de solo lectura sin decir por qué: las páginas cargan
 * (los recursos autorizan por rol) pero cada botón de crear, editar o borrar se
 * esconde, porque las políticas preguntan por un permiso que nunca se creó.
 * Ocurrió en producción: el dueño veía la tabla de cajas y no tenía forma de
 * abrir una.
 *
 * Es idempotente: se puede correr en cada despliegue.
 */
class SyncPermissionsCommand extends Command
{
    protected $signature = 'permissions:sync';

    protected $description = 'Crea los permisos del panel y los asigna a los roles';

    /** Modelos que administra el panel. */
    private const MODELOS = [
        'BarberShop', 'BarberProfile', 'BarberAvailability', 'Service',
        'Reservation', 'Transfer', 'CashRegister', 'CashMovement',
        'BarberPayment', 'User', 'Role',
    ];

    /** Acciones que consultan las políticas generadas por Shield. */
    private const ACCIONES = [
        'ViewAny', 'View', 'Create', 'Update', 'Delete', 'DeleteAny',
        'Restore', 'RestoreAny', 'ForceDelete', 'ForceDeleteAny',
        'Replicate', 'Reorder',
    ];

    /** Lo que un barbero puede tocar: su agenda, nada de dinero ni de configuración. */
    private const MODELOS_BARBERO = ['Reservation', 'Service', 'BarberProfile', 'BarberAvailability'];
    private const ACCIONES_BARBERO = ['ViewAny', 'View', 'Update'];

    public function handle(): int
    {
        $creados = 0;

        foreach (self::MODELOS as $modelo) {
            foreach (self::ACCIONES as $accion) {
                $permiso = Permission::firstOrCreate([
                    'name' => "{$accion}:{$modelo}",
                    'guard_name' => 'web',
                ]);

                $creados += $permiso->wasRecentlyCreated ? 1 : 0;
            }
        }

        $todos = Permission::where('guard_name', 'web')->pluck('name')->all();

        // admin y owner administran todo su negocio.
        foreach (['admin', 'super_admin', 'owner'] as $nombreRol) {
            $rol = Role::firstOrCreate(['name' => $nombreRol, 'guard_name' => 'web']);
            $rol->syncPermissions($todos);
            $this->line("  {$nombreRol}: " . count($todos) . ' permisos');
        }

        $delBarbero = [];
        foreach (self::MODELOS_BARBERO as $modelo) {
            foreach (self::ACCIONES_BARBERO as $accion) {
                $delBarbero[] = "{$accion}:{$modelo}";
            }
        }

        $barbero = Role::firstOrCreate(['name' => 'barber', 'guard_name' => 'web']);
        $barbero->syncPermissions($delBarbero);
        $this->line('  barber: ' . count($delBarbero) . ' permisos (agenda, sin dinero)');

        // El cliente no entra al panel.
        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web'])->syncPermissions([]);
        $this->line('  customer: 0 permisos');

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->newLine();
        $this->info("  Permisos nuevos creados: {$creados}. Total: " . count($todos) . '.');

        return self::SUCCESS;
    }
}
