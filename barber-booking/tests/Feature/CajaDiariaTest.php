<?php

namespace Tests\Feature;

use App\Models\BarberShop;
use App\Models\CashRegister;
use App\Models\Reservation;
use App\Models\Transfer;
use App\Models\User;
use App\Services\TenantProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * La caja es la función por la que se paga el sistema: si el cuadre miente, el
 * dueño deja de confiar en todo lo demás.
 */
class CajaDiariaTest extends TestCase
{
    use RefreshDatabase;

    private User $dueño;
    private CashRegister $cajaActual;
    private BarberShop $barberia;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dueño = User::factory()->create(['is_active' => true]);
        $this->barberia = app(TenantProvisioningService::class)->provision($this->dueño, 'Barbería de Prueba');
    }

    private function abrirCaja(float $inicial = 100.00): CashRegister
    {
        return CashRegister::create([
            'barber_shop_id' => $this->barberia->id,
            'date' => today(),
            'opening_amount' => $inicial,
            'status' => 'open',
            'opened_by' => $this->dueño->id,
            'opened_at' => now(),
        ]);
    }

    private function cobrarEnEfectivo(float $monto): void
    {
        Reservation::create([
            'barber_shop_id' => $this->barberia->id,
            'service_id' => $this->barberia->services()->first()->id,
            'consultant_id' => $this->dueño->id,
            'customer_name' => 'Cliente Efectivo',
            'customer_phone' => '0990000000',
            'reservation_date' => today(),
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'reservation_status' => 'confirmada',
            'total_amount' => $monto,
            'payment_status' => 'pagado',
        ]);
    }

    private function cobrarPorTransferencia(float $monto): void
    {
        Transfer::create([
            'barber_shop_id' => $this->barberia->id,
            'amount' => $monto,
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'confirmed_by' => $this->dueño->id,
        ]);
    }

    public function test_un_dia_solo_con_efectivo_cuadra(): void
    {
        $caja = $this->abrirCaja(100.00);
        $this->cobrarEnEfectivo(10.00);

        // El dueño abre con 100, cobra 10 en efectivo y cuenta 110 en el cajón.
        $caja->close(110.00, $this->dueño->id);
        $caja->refresh();

        $this->assertSame('0.00', (string) $caja->difference_amount, 'Un día solo con efectivo debe cuadrar en cero.');
    }

    public function test_una_transferencia_no_debe_descuadrar_el_efectivo(): void
    {
        $caja = $this->abrirCaja(100.00);
        $this->cobrarEnEfectivo(10.00);
        $this->cobrarPorTransferencia(50.00);

        // El cajón tiene 110: los 100 de apertura más los 10 en efectivo.
        // Los 50 de la transferencia están en el BANCO, no en el cajón.
        $caja->close(110.00, $this->dueño->id);
        $caja->refresh();

        $this->assertSame(
            '0.00',
            (string) $caja->difference_amount,
            "El cajón tiene exactamente lo que debe tener, pero el sistema reporta un faltante de {$caja->difference_amount}. "
            . 'Una transferencia entra al banco y no al cajón: sumarla al efectivo esperado inventa un descuadre.',
        );
    }

    private function gasto(float $monto): void
    {
        \App\Models\CashMovement::create([
            'cash_register_id' => $this->cajaActual->id,
            'type' => 'expense',
            'amount' => $monto,
            'description' => 'Compra de insumos',
            'performed_by' => $this->dueño->id,
        ]);
    }

    private function ingresoManual(float $monto): void
    {
        \App\Models\CashMovement::create([
            'cash_register_id' => $this->cajaActual->id,
            'type' => 'income',
            'amount' => $monto,
            'description' => 'Venta de producto',
            'performed_by' => $this->dueño->id,
        ]);
    }

    public function test_los_gastos_salen_del_cajon(): void
    {
        $this->cajaActual = $caja = $this->abrirCaja(100.00);
        $this->cobrarEnEfectivo(20.00);
        $this->gasto(15.00);

        // 100 + 20 - 15 = 105
        $caja->close(105.00, $this->dueño->id);

        $this->assertSame('0.00', (string) $caja->refresh()->difference_amount);
        $this->assertSame('105.00', (string) $caja->system_amount);
    }

    public function test_la_venta_de_productos_entra_al_cajon(): void
    {
        $this->cajaActual = $caja = $this->abrirCaja(50.00);
        $this->ingresoManual(8.00);

        $caja->close(58.00, $this->dueño->id);

        $this->assertSame('0.00', (string) $caja->refresh()->difference_amount);
    }

    public function test_detecta_un_faltante_real(): void
    {
        // Si de verdad falta plata, el sistema TIENE que avisar. Arreglar el
        // falso descuadre no puede volverlo ciego al descuadre verdadero.
        $this->cajaActual = $caja = $this->abrirCaja(100.00);
        $this->cobrarEnEfectivo(20.00);

        $caja->close(110.00, $this->dueño->id); // faltan 10

        $this->assertSame('-10.00', (string) $caja->refresh()->difference_amount);
    }

    public function test_detecta_un_sobrante(): void
    {
        $this->cajaActual = $caja = $this->abrirCaja(100.00);
        $caja->close(105.00, $this->dueño->id);

        $this->assertSame('5.00', (string) $caja->refresh()->difference_amount);
    }

    public function test_una_caja_cerrada_no_se_altera_despues(): void
    {
        // El cierre es una foto del día. Si una reserva se marca pagada al día
        // siguiente, el cuadre de ayer no puede cambiar solo.
        $this->cajaActual = $caja = $this->abrirCaja(100.00);
        $caja->close(100.00, $this->dueño->id);

        $this->cobrarEnEfectivo(30.00);
        $caja->recalculate();

        $this->assertSame('100.00', (string) $caja->refresh()->system_amount);
        $this->assertSame('0.00', (string) $caja->difference_amount);
    }

    public function test_cerrar_dos_veces_no_pisa_el_cierre_original(): void
    {
        $this->cajaActual = $caja = $this->abrirCaja(100.00);
        $caja->close(90.00, $this->dueño->id);

        $caja->close(500.00, $this->dueño->id);

        $this->assertSame('90.00', (string) $caja->refresh()->real_amount);
        $this->assertSame('closed', $caja->status);
    }

    public function test_la_transferencia_igual_queda_registrada_como_ingreso(): void
    {
        // Sacarla del cuadre de efectivo no puede hacerla desaparecer: es plata
        // que la barbería facturó y tiene que verse en el día.
        $this->cajaActual = $caja = $this->abrirCaja(100.00);
        $this->cobrarEnEfectivo(10.00);
        $this->cobrarPorTransferencia(50.00);
        $caja->close(110.00, $this->dueño->id);
        $caja->refresh();

        $this->assertSame('50.00', (string) $caja->transfer_income_amount);
        $this->assertSame('10.00', (string) $caja->cash_income_amount);
    }
}
