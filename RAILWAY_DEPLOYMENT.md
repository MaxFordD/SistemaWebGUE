# Deployment a Railway - Sistema Web GUE

## Configuracion Completada

Este proyecto esta configurado para usar PHP 8.2 en Railway.

### Archivos Creados

1. **Procfile** - Define como Railway inicia la aplicacion
2. **nixpacks.toml** - Especifica PHP 8.2 y comandos de build
3. **railway.sh** - Script de deployment (opcional)
4. **.railwayignore** - Archivos excluidos del deployment

## Pasos para Deploy en Railway

### 1. Crear Cuenta en Railway
- Ve a https://railway.app
- Registrate con GitHub

### 2. Crear Nuevo Proyecto
- Click en "New Project"
- Selecciona "Deploy from GitHub repo"
- Autoriza Railway para acceder a tu repositorio
- Selecciona el repositorio SistemaWebGUE

### 3. Configurar Base de Datos
Railway detectara automaticamente que necesitas MySQL.

- Click en "Add Plugin"
- Selecciona "MySQL"
- Railway creara una base de datos automaticamente

### 4. Configurar Variables de Entorno

En el dashboard de Railway, ve a la seccion "Variables" y agrega:

#### Variables Obligatorias:

```
APP_NAME=SistemaWebGUE
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://tu-app.up.railway.app

LOG_CHANNEL=stack
LOG_LEVEL=error

# Database (Railway las proporciona automaticamente al agregar MySQL plugin)
DB_CONNECTION=mysql
DB_HOST=${{MYSQL.MYSQLHOST}}
DB_PORT=${{MYSQL.MYSQLPORT}}
DB_DATABASE=${{MYSQL.MYSQLDATABASE}}
DB_USERNAME=${{MYSQL.MYSQLUSER}}
DB_PASSWORD=${{MYSQL.MYSQLPASSWORD}}

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

#### Generar APP_KEY:
Para generar el APP_KEY, puedes:
1. Ejecutar localmente: `php artisan key:generate --show`
2. O usar el comando en Railway una vez desplegado

### 5. Configurar Dominio (Opcional)
- En "Settings" > "Domains"
- Railway te da un dominio gratuito: `*.up.railway.app`
- Puedes agregar un dominio personalizado si lo tienes

### 6. Deploy
- Railway automaticamente detectara los cambios cuando hagas push a tu rama principal
- El primer deploy puede tardar 3-5 minutos

### 7. Ejecutar Migraciones (Primera vez)

Despues del primer deploy, necesitas ejecutar las migraciones:

1. Ve a tu proyecto en Railway
2. Click en tu servicio
3. Ve a "Deploy Logs" o "Settings"
4. Usa el comando: `php artisan migrate --force`

Para ejecutar comandos en Railway:
- Puedes usar la CLI de Railway: https://docs.railway.app/develop/cli
- O usar el "Service" > "Settings" > "Deploy Command"

## Comandos Utiles de Railway CLI

Instalar Railway CLI:
```bash
npm i -g @railway/cli
```

Login:
```bash
railway login
```

Conectar al proyecto:
```bash
railway link
```

Ejecutar comandos:
```bash
railway run php artisan migrate
railway run php artisan db:seed
```

Ver logs:
```bash
railway logs
```

## Migraciones y Datos Iniciales

Si necesitas cargar los datos iniciales que tienes en `datos_iniciales.sql` y los stored procedures:

1. Usa Railway CLI para conectarte a la base de datos
2. O importa usando MySQL Workbench conectandote a la base de datos de Railway
3. Las credenciales de la base de datos estan en las variables de entorno de Railway

## Troubleshooting

### Error: "No application encryption key has been specified"
Solucion: Genera y configura APP_KEY en las variables de entorno

### Error: "SQLSTATE[HY000] [2002] Connection refused"
Solucion: Verifica que las variables de base de datos esten correctamente configuradas

### Error 500 despues del deploy
Solucion:
1. Verifica los logs: `railway logs`
2. Asegurate que APP_DEBUG=false en produccion
3. Verifica que todas las variables de entorno esten configuradas

### Los assets CSS/JS no cargan
Solucion: Verifica que APP_URL este correctamente configurado con tu dominio de Railway

## Notas Importantes

- **PHP Version**: El proyecto usara PHP 8.2 como se especifica en `nixpacks.toml`
- **Composer**: Las dependencias se instalan automaticamente durante el build
- **Cache**: Laravel cachea configuracion, rutas y vistas durante el build
- **Logs**: Los logs se pueden ver en Railway Dashboard > Deploy Logs
- **Storage**: Railway proporciona almacenamiento efimero. Para archivos permanentes considera usar S3 o similar

## Monitoreo y Mantenimiento

- Railway te notifica de errores via email
- Puedes configurar alertas en "Settings" > "Health Checks"
- Los deploys son automaticos cuando haces push a la rama principal
- Railway ofrece un plan gratuito con $5 de credito mensual

## Proximos Pasos

1. Hacer push de estos cambios a tu repositorio
2. Configurar el proyecto en Railway
3. Agregar las variables de entorno
4. Ejecutar migraciones
5. Importar datos iniciales si es necesario

Para cualquier duda, consulta la documentacion de Railway: https://docs.railway.app
