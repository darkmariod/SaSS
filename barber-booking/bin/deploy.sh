#!/usr/bin/env bash
#
# Despliegue al VPS.
#
# El servidor no tiene git ni pipeline: el código se sincroniza desde esta
# máquina. Ese hueco ya causó fallos difíciles de ver —un bundle de JavaScript
# viejo pidiendo una ruta que ya no existía, migraciones sin correr, modelos
# desactualizados— así que este script hace SIEMPRE la secuencia completa y
# termina con una verificación real contra el servidor.
#
# Credenciales por variables de entorno: este repositorio es público y una
# contraseña escrita aquí quedaría publicada.
#
#   export DEPLOY_PASS='...'
#   ./bin/deploy.sh
#
# Con clave SSH configurada (recomendado) no hace falta DEPLOY_PASS.

set -euo pipefail

DEPLOY_HOST="${DEPLOY_HOST:-108.174.152.179}"
DEPLOY_PORT="${DEPLOY_PORT:-22022}"
DEPLOY_USER="${DEPLOY_USER:-root}"
DEPLOY_PATH="${DEPLOY_PATH:-/var/www/barber-booking}"
DEPLOY_URL="${DEPLOY_URL:-http://${DEPLOY_HOST}:3000}"

RAIZ="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$RAIZ"

if [[ -n "${DEPLOY_PASS:-}" ]]; then
    SSH=(sshpass -p "$DEPLOY_PASS" ssh -o StrictHostKeyChecking=no -p "$DEPLOY_PORT")
    RSYNC_SSH="sshpass -p $DEPLOY_PASS ssh -o StrictHostKeyChecking=no -p $DEPLOY_PORT"
else
    SSH=(ssh -p "$DEPLOY_PORT")
    RSYNC_SSH="ssh -p $DEPLOY_PORT"
fi

DESTINO="${DEPLOY_USER}@${DEPLOY_HOST}:${DEPLOY_PATH}"

paso() { printf '\n\033[1m==> %s\033[0m\n' "$1"; }

# --------------------------------------------------------------------------
paso 'Pruebas locales'
php artisan test --quiet

# --------------------------------------------------------------------------
paso 'Compilando assets'
npm run build --silent

# --------------------------------------------------------------------------
paso 'Respaldo de la base antes de tocar nada'
"${SSH[@]}" "${DEPLOY_USER}@${DEPLOY_HOST}" \
    '/usr/local/bin/barber-backup.sh' || echo '  (sin script de respaldo en el servidor)'

# --------------------------------------------------------------------------
paso 'Sincronizando código'
# NUNCA se envían .env ni la base de datos: son estado del servidor.
for dir in app routes config resources database/migrations database/seeders bin tests public/build; do
    rsync -rc --delete -e "$RSYNC_SSH" "$dir/" "${DESTINO}/${dir}/"
    echo "  ok  $dir"
done

# --------------------------------------------------------------------------
paso 'Migraciones, permisos y caché'
"${SSH[@]}" "${DEPLOY_USER}@${DEPLOY_HOST}" "
    set -e
    cd '$DEPLOY_PATH'
    chown -R www-data:www-data app routes config resources database/migrations database/seeders bin tests public/build
    sudo -u www-data php artisan migrate --force
    sudo -u www-data php artisan optimize:clear
    # opcache sirve el archivo viejo hasta revalidar: sin esto, un despliegue
    # puede parecer aplicado y no estarlo.
    systemctl reload php8.3-fpm
"

# --------------------------------------------------------------------------
paso 'Verificación contra el servidor'
if "${SSH[@]}" "${DEPLOY_USER}@${DEPLOY_HOST}" \
    "cd '$DEPLOY_PATH' && php bin/smoke.php '$DEPLOY_URL' '${SMOKE_EMAIL:-}' '${SMOKE_PASS:-}'"; then
    printf '\n\033[32mDESPLIEGUE OK\033[0m — %s\n\n' "$DEPLOY_URL"
else
    printf '\n\033[31mLA VERIFICACIÓN FALLÓ\033[0m — revisá la salida de arriba.\n\n'
    exit 1
fi
