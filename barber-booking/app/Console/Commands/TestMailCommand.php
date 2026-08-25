<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Envía un correo de prueba y explica el resultado en castellano.
 *
 * Existe porque la configuración de correo falla de formas silenciosas: con
 * MAIL_MAILER=log el sistema "envía" sin que nadie reciba nada, y con Gmail el
 * error más común (usar la contraseña de la cuenta en vez de una contraseña de
 * aplicación) devuelve un mensaje que no dice eso en ningún lado.
 */
class TestMailCommand extends Command
{
    protected $signature = 'mail:test {destinatario : Correo al que enviar la prueba}';

    protected $description = 'Envía un correo de prueba para verificar la configuración de envío';

    public function handle(): int
    {
        $destinatario = $this->argument('destinatario');
        $transporte = config('mail.default');

        $this->newLine();
        $this->line("  Transporte configurado: <options=bold>{$transporte}</>");

        if ($transporte === 'log') {
            $this->newLine();
            $this->error('  MAIL_MAILER=log: los correos se escriben en storage/logs/laravel.log');
            $this->line('  y NO llegan a nadie. Configurá un proveedor real antes de entregar.');
            $this->newLine();

            return self::FAILURE;
        }

        $this->line('  Servidor: ' . config('mail.mailers.smtp.host') . ':' . config('mail.mailers.smtp.port'));
        $this->line('  Remitente: ' . config('mail.from.address'));
        $this->line("  Enviando a: {$destinatario} ...");

        try {
            Mail::raw(
                "Prueba de envío del sistema de reservas.\n\n"
                . 'Si estás leyendo esto, la configuración de correo funciona.',
                fn ($mensaje) => $mensaje->to($destinatario)->subject('Prueba de correo - Sistema de reservas'),
            );
        } catch (\Throwable $e) {
            $this->newLine();
            $this->error('  FALLÓ EL ENVÍO');
            $this->line('  ' . $e->getMessage());
            $this->newLine();
            $this->line('  Causas frecuentes con Gmail:');
            $this->line('   - Se usó la contraseña de la cuenta. Gmail exige una CONTRASEÑA DE APLICACIÓN de 16 caracteres.');
            $this->line('   - La verificación en dos pasos no está activada (sin ella no se pueden crear contraseñas de aplicación).');
            $this->line('   - MAIL_FROM_ADDRESS no coincide con MAIL_USERNAME: Gmail rechaza remitentes ajenos.');
            $this->newLine();

            return self::FAILURE;
        }

        $this->newLine();
        $this->info("  ENVIADO. Revisá la bandeja de {$destinatario} (mirá también correo no deseado).");
        $this->newLine();

        return self::SUCCESS;
    }
}
