#!/bin/sh
# =============================================================================
# Production Startup Script — Barber Booking
# =============================================================================
# Se ejecuta CADA VEZ que arranca el contenedor php-fpm.
# Corre migraciones y optimizaciones de Laravel, luego inicia PHP-FPM.
# =============================================================================
set -e

echo "=== [Laravel] Arrancando aplicación ==="

# ── Crear storage link (idempotente) ──
php artisan storage:link --force 2>/dev/null || true

# ── Optimizaciones de Laravel ──
echo "=== [Laravel] Cacheando config ==="
php artisan config:cache

echo "=== [Laravel] Cacheando rutas ==="
php artisan route:cache

echo "=== [Laravel] Cacheando vistas ==="
php artisan view:cache

echo "=== [Laravel] Cacheando eventos ==="
php artisan event:cache

# ── Asegurar que SQLite existe (para cache locks si usa database driver) ──
touch database/database.sqlite

# ── Migraciones (solo si la base de datos responde) ──
echo "=== [Laravel] Corriendo migraciones ==="
php artisan migrate --force

# ── Iniciar PHP-FPM ──
echo "=== [Laravel] Listo. Iniciando PHP-FPM ==="
exec docker-php-entrypoint php-fpm
