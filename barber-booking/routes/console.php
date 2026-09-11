<?php

use Illuminate\Support\Facades\Schedule;

// Recordatorios de las citas de mañana, cada tarde. A esa hora el cliente ya
// sabe cómo viene su día siguiente y todavía puede avisar si no llega.
Schedule::command('reminders:send')
    ->dailyAt('18:00')
    ->timezone('America/Guayaquil')
    ->withoutOverlapping();
