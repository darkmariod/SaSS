<?php

namespace Tests\Feature;

use App\Models\BarberShop;
use App\Models\User;
use App\Services\TenantProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El slug es la dirección pública de la barbería y termina impresa en un código
 * QR. Un slug inválido no rompe el panel: rompe en silencio la única vía por la
 * que entran los clientes, y nadie se entera hasta que alguien pregunta por qué
 * no llegan reservas.
 *
 * Caso real: al renombrar la barbería desde el panel quedó guardado
 * "Seven Barber " —con mayúsculas y espacio final— y la página pública devolvió
 * 404 durante días.
 */
class BarberShopSlugTest extends TestCase
{
    use RefreshDatabase;

    private function barberia(): BarberShop
    {
        $dueño = User::factory()->create(['is_active' => true]);

        return app(TenantProvisioningService::class)->provision($dueño, 'Barbería de Prueba');
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function slugsSucios(): array
    {
        return [
            'con espacio al final'   => ['Seven Barber ', 'seven-barber'],
            'con mayúsculas'         => ['Seven Barber', 'seven-barber'],
            'con espacios internos'  => ['  barber  shop  ', 'barber-shop'],
            'con acentos'            => ['Barbería Céntrica', 'barberia-centrica'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('slugsSucios')]
    public function test_el_slug_se_normaliza_al_guardarlo(string $entrada, string $esperado): void
    {
        $barberia = $this->barberia();

        $barberia->update(['slug' => $entrada]);

        $this->assertSame($esperado, $barberia->fresh()->slug);
    }


    public function test_cualquier_entrada_produce_un_slug_valido_para_una_url(): void
    {
        // Para entradas raras no interesa el texto exacto que salga, sino que
        // el resultado sea siempre usable en una dirección: minúsculas,
        // números y guiones. Eso es lo que evita el 404.
        $barberia = $this->barberia();

        foreach (['Seven/Barber #1', '¡¿Barbería?!', 'A—B', '***'] as $entrada) {
            $barberia->update(['slug' => $entrada]);
            $slug = $barberia->fresh()->slug;

            $this->assertMatchesRegularExpression(
                '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                $slug,
                "La entrada \"{$entrada}\" produjo un slug inservible: \"{$slug}\".",
            );
        }
    }

    public function test_un_slug_vacio_cae_al_nombre_y_nunca_queda_en_blanco(): void
    {
        $barberia = $this->barberia();
        $barberia->update(['name' => 'Seven Barber', 'slug' => '   ']);

        $this->assertSame('seven-barber', $barberia->fresh()->slug);
    }



    public function test_una_direccion_vieja_sigue_llevando_a_la_barberia(): void
    {
        // Un QR impreso no se puede reimprimir cada vez que el dueño renombra su
        // barbería. La dirección anterior tiene que seguir funcionando.
        $barberia = $this->barberia();
        $viejo = $barberia->slug;

        $barberia->update(['name' => 'Seven Barber', 'slug' => 'seven-barber']);

        $this->assertNotSame($viejo, $barberia->fresh()->slug);
        $this->get("/barberia/{$viejo}")->assertOk();
        $this->get("/barberia/{$viejo}/reservar")->assertOk();
    }

    public function test_una_direccion_que_nunca_existio_sigue_dando_404(): void
    {
        $this->barberia();

        $this->get('/barberia/esta-no-existe')->assertNotFound();
    }

    public function test_demo_lleva_a_una_barberia_que_existe(): void
    {
        // /demo es el enlace que se le pasa a un comprador. Antes esta lógica
        // vivía en la raíz con el slug escrito a mano y, al renombrar la
        // barbería, llevaba a una página inexistente.
        $barberia = $this->barberia();
        $barberia->update(['name' => 'Seven Barber', 'slug' => 'Seven Barber ']);

        $destino = $this->get('/demo')->assertRedirect()->headers->get('Location');

        $this->get($destino)->assertOk();
        $this->assertStringContainsString($barberia->fresh()->slug, $destino);
    }

    public function test_la_pagina_publica_responde_despues_de_renombrar(): void
    {
        // Esta es la prueba que importa: no basta con que el dato quede lindo en
        // la base, la URL tiene que abrir.
        $barberia = $this->barberia();
        $barberia->update(['name' => 'Seven Barber', 'slug' => 'Seven Barber ']);

        $slug = $barberia->fresh()->slug;

        $this->get("/barberia/{$slug}")->assertOk();
        $this->get("/barberia/{$slug}/reservar")->assertOk();
    }
}
