# 🚀 GUÍA RÁPIDA — Barber Booking a PRODUCCIÓN

> **Objetivo**: Hoy tenés la app funcionando en el mundo real para la demo con tu cliente.
> **Tiempo estimado**: 30-45 minutos si ya tenés el VPS.

---

## 📦 PASO 0 — Lo que necesitás ANTES de empezar

- [ ] Una **VPS** (DigitalOcean, Linode, Hetzner, etc.) — Ubuntu 22.04+, mínimo 2GB RAM
- [ ] **Puertos 80 y 443 abiertos** en el firewall de la VPS
- [ ] Acceso **root** o **sudo** a la VPS
- [ ] Tu código en **GitHub**

---

## 🐙 PASO 1 — Subir código a GitHub (5 min)

Si no creaste el repo aún, hace esto en tu terminal LOCAL:

```bash
# 1. Crear el repo en GitHub desde terminal
gh repo create barber-booking --private --source=. --push

# O si preferís desde la web:
#   1. Andá a github.com/new
#   2. Creá "barber-booking" (privado)
#   3. No marques nada más
#   4. En tu terminal:
#      git remote add origin git@github.com:TU_USUARIO/barber-booking.git
#      git push -u origin main
```

✅ Código subido. No te olvides de esto.

---

## 📡 PASO 2 — DuckDNS (el dominio gratis) (10 min)

DuckDNS te da un dominio `.duckdns.org` **GRATIS** para toda la vida.

### 2.1 Crear tu dominio

```
1. Andá a → https://duckdns.org
2. Iniciá sesión con:
   ┌──────────────┐
   │  Google      │ ← el más fácil
   │  GitHub      │
   │  Twitter     │
   │  Persona     │
   └──────────────┘

3. Elegí cualquiera, la que tengas
4. Te va a aparecer esta pantalla:
```

![Pantalla DuckDNS](https:// duckdns.org)

### 2.2 Crear el subdominio

```
1. En la página principal, vas a ver un campo que dice "domains"
2. Escribí el nombre que quieras, por ejemplo:
   
   ┌──────────────────────┐
   │ barberia             │ ← poné el que quieras
   └──────────────────────┘
   
3. Hacé clic en "add domain"
4. Te va a aparecer algo así:

   ┌──────────────────────────────────────┐
   │ domain:  barberia.duckdns.org        │
   │ token:   abc123-def456-ghi789        │ ← GUARDALO
   │ ip:      current_ip                  │
   └──────────────────────────────────────┘
```

**📝 IMPORTANTE**: Copiate el **TOKEN** en un bloc de notas. Lo vas a necesitar.

### 2.3 Apuntar el dominio a tu VPS

```
1. Abrí la página de DuckDNS de nuevo (https://duckdns.org)
2. Vas a ver tu dominio creado
3. Donde dice "current ip", poné la IP de tu VPS
   ┌───────────────────────┐
   │ current ip: 203.0.113.5  ← la IP de tu servidor
   └───────────────────────┘
4. Hacé clic en "update ip"
```

### 2.4 Para que la IP se actualice sola (IMPORTANTE)

Si tu VPS cambia de IP (raro pero puede pasar), DuckDNS deja de apuntar bien.

**En tu VPS** (via SSH), corre esto UNA SOLA VEZ:

```bash
# Reemplazá "TU_TOKEN" por el token que copiaste antes
# Reemplazá "barberia" por tu dominio
TOKEN=abc123-def456-ghi789
DOMINIO=barberia

# Crear script de actualización
sudo tee /etc/cron.d/duckdns <<EOF
*/5 * * * * root curl -s "https://www.duckdns.org/update?domains=${DOMINIO}&token=${TOKEN}&ip=" > /dev/null 2>&1
EOF
```

Esto actualiza DuckDNS cada 5 minutos automáticamente.

**✅ DuckDNS listo**. Tu dominio `barberia.duckdns.org` ya apunta a tu VPS.

---

## 🏗️ PASO 3 — Instalar Dokploy en tu VPS (15 min)

Conectate a tu VPS por SSH y copiá esto:

```bash
curl -fsSL https://install.dokploy.com | sh
```

Esperá que termine (unos 5-10 minutos). Te va a pedir:

```
1. Enter your email: poné tu@email.com         ← para Let's Encrypt
2. Enter FQDN (domain): barberia.duckdns.org   ← tu dominio DuckDNS
```

Al final te va a dar una URL como:
```
https://barberia.duckdns.org:3000
```
Esa es la URL de **Dokploy Admin**. Guardala.

**¿Puerto 3000 cerrado?** Corré esto:
```bash
sudo ufw allow 3000/tcp
sudo ufw reload
```

Andá a esa URL desde tu navegador. Creá tu cuenta (primer registro = admin).

---

## 🚢 PASO 4 — Configurar Dokploy (10 min)

Adentro de Dokploy:

### 4.1 Crear Project + Environment

```
1. Projects → New Project
   Name: barber-booking

2. Environments → New Environment
   Name: production
   Branch: main
```

### 4.2 Agregar Environment Variables

En la pestaña **Environment Variables** del proyecto, agregá TODO esto:

```bash
APP_NAME=BookingEc
APP_ENV=production
APP_KEY=base64:aCAxnidrSaGpcEKd7GuPtwWIjlamdbHTn7qWoWVRtGU=
APP_DEBUG=false
APP_URL=https://barberia.duckdns.org

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=barber_booking
DB_USERNAME=barber
DB_PASSWORD=Demo123456!      ← CAMBIALO

MYSQL_ROOT_PASSWORD=Root123456!     ← CAMBIALO

QUEUE_CONNECTION=database
```

⚠️ Cambiá `DB_PASSWORD` y `MYSQL_ROOT_PASSWORD` por algo SEGURO.  
⚠️ Cambiá `APP_URL` por **tu dominio real** de DuckDNS.

### 4.3 Agregar el Servicio (Docker Compose)

```
1. Services → New Service
2. Type: Compose
3. Source: Git Repository
4. Repository: github.com/TU_USUARIO/barber-booking
5. Branch: main
6. Docker Compose Path: docker-compose.yml
```

Dokploy va a:
- Leer el `docker-compose.yml`
- Mostrarte los 5 servicios (nginx, php-fpm, queue, scheduler, mysql)
- Arrancar a construirlos

### 4.4 Configurar el Dominio

En el servicio **nginx**:

```
1. Pestaña Domains → Create Domain
2. Host: barberia.duckdns.org
3. Container Port: 80
4. HTTPS: ON (activado)
5. Certificate: Let's Encrypt
6. Create
```

### 4.5 Darle al Deploy

```
1. Services → (tu servicio) → Deploy
2. Esperá que termine. La primera vez tarda 5-10 minutos
   (tiene que compilar assets de Vue, instalar Composer, etc.)
3. Cuando veas verde, probá:
   → https://barberia.duckdns.org/health            → debe mostrar "healthy"
   → https://barberia.duckdns.org/admin              → login de admin
```

---

## 🎯 PASO 5 — Entrar al panel

```bash
URL:  https://barberia.duckdns.org/admin
USER: admin@test.com
PASS: password
```

⚠️ **CAMBIÁ ESTA CONTRASEÑA AHORA MISMO** antes de la demo.

---

## 🧠 RESUMEN RAPIDO (si tenés experiencia)

```
1. duckdns.org → crear cuenta → crear dominio → poner IP del VPS
2. SSH a VPS → curl -fsSL https://install.dokploy.com | sh
3. Dokploy → Project → Environment Variables (copiar de .env.production)
4. Dokploy → Service → Compose → conectar GitHub
5. Dokploy → Domains → poner tu dominio DuckDNS → HTTPS ON
6. Deploy → esperar → probar /health
```

---

## 🆘 Problemas comunes

| Problema | Solución |
|----------|----------|
| **DuckDNS no resuelve** | Esperá 5-10 minutos (propagación DNS). Verificá que la IP esté bien. |
| **Traefik da error SSL** | `docker restart dokploy-traefik` (bug conocido, reintenta ACME) |
| **Puerto 80/443 cerrado** | `sudo ufw allow 80/tcp && sudo ufw allow 443/tcp && sudo ufw reload` |
| **No puedo entrar a Dokploy** | `sudo ufw allow 3000/tcp` y verificá que el servicio esté corriendo |
| **Error de conexión MySQL** | Verificá que `DB_HOST=mysql` (es el nombre del servicio, no una IP) |
| **Sale error 503** | Esperá que termine el build. Verificá los logs en Dokploy → Services → Logs |

---

**¿Vas con eso?** Si te trabás en algún paso, mandame mensaje y lo resolvemos. Mañana la app está en el aire, no hay drama. 💪
