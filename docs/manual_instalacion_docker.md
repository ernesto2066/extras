# Manual de Instalación con Docker - POSITIVOS

## Introducción

Este documento técnico describe el proceso de instalación y configuración del entorno de desarrollo para el proyecto POSITIVOS utilizando Laravel Sail y Docker. Esta configuración proporciona un entorno de desarrollo consistente para todos los miembros del equipo, eliminando problemas de "en mi máquina funciona".

## Tecnologías Utilizadas

- **Docker** - Plataforma de contenedores
- **Laravel Sail** - Entorno de desarrollo Docker para Laravel
- **WSL2** - Subsistema Windows para Linux (para usuarios de Windows)
- **Ubuntu** - Distribución Linux recomendada para WSL2

## Requisitos Previos

- **Windows**: Windows 10 versión 2004+ / Windows 11 con WSL2 habilitado
- **macOS**: macOS Catalina (10.15) o superior
- **Linux**: Ubuntu 20.04 o superior

## Instalación en Windows con WSL2

### 1. Configuración de WSL2

```bash
# Habilitar WSL y la característica de Máquina Virtual
dism.exe /online /enable-feature /featurename:Microsoft-Windows-Subsystem-Linux /all /norestart
dism.exe /online /enable-feature /featurename:VirtualMachinePlatform /all /norestart

# Reiniciar el equipo
# Luego, establecer WSL2 como versión predeterminada
wsl --set-default-version 2

# Instalar Ubuntu desde Microsoft Store o descargar de la web oficial
```

### 2. Instalar Docker en WSL2 (sin Docker Desktop)

```bash
# Actualizar paquetes
sudo apt update

# Instalar paquetes necesarios
sudo apt install --no-install-recommends apt-transport-https ca-certificates curl gnupg2

# Añadir clave GPG oficial de Docker
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

# Añadir repositorio de Docker estable
echo "deb [arch=amd64 signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Actualizar índice de paquetes
sudo apt update

# Instalar Docker Engine
sudo apt install docker-ce docker-ce-cli containerd.io

# Añadir usuario al grupo docker para ejecutar Docker sin sudo
sudo usermod -aG docker $USER

# Iniciar el servicio Docker
sudo service docker start

# Para iniciar Docker automáticamente al arrancar WSL, añadir al archivo ~/.bashrc
echo '# Iniciar Docker automáticamente' >> ~/.bashrc
echo 'if service docker status 2>&1 | grep -q "is not running"; then' >> ~/.bashrc
echo '  wsl.exe -d "${WSL_DISTRO_NAME}" -u root -e /usr/sbin/service docker start >/dev/null 2>&1' >> ~/.bashrc
echo 'fi' >> ~/.bashrc

# Reiniciar shell o abrir nueva terminal
newgrp docker
```

### 3. Instalar Herramientas Adicionales

```bash
# Instalar Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/download/v2.18.1/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Instalar PHP, Composer y Git
sudo apt install php-cli unzip git
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
```

## Configuración del Proyecto POSITIVOS

### 1. Clonar el Repositorio

```bash
# Navegar a la ubicación donde se almacenará el proyecto
cd ~/proyectos

# Clonar el repositorio
git clone https://github.com/ernesto2066/extras.git positivos
cd positivos

# Cambiar a la rama develop
git checkout develop
```

### 2. Configurar Laravel Sail

```bash
# Crear archivo .env a partir del ejemplo
cp .env.example .env

# Editar variables de entorno en .env
nano .env

# Ejemplo de configuración para .env (ajustar según sea necesario)
APP_NAME=POSITIVOS
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=positivos
DB_USERNAME=sail
DB_PASSWORD=password

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_HOST=redis

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="notificaciones@positivos.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 3. Instalar Laravel Sail

```bash
# Instalar Sail utilizando Composer
composer require laravel/sail --dev

# Publicar los archivos de Docker
php artisan sail:install --with=mysql,redis,mailhog
```

### 4. Configurar Alias para Sail

```bash
# Añadir alias para facilitar el uso de Sail
echo 'alias sail="./vendor/bin/sail"' >> ~/.bashrc
source ~/.bashrc
```

## Representación Visual de la Arquitectura Docker

```
┌───────────────────────────────────────────────┐
│                                               │
│             POSITIVOS - DOCKER                │
│                                               │
├───────────────┬───────────────┬───────────────┤
│               │               │               │
│    Laravel    │     MySQL     │     Redis     │
│               │               │               │
├───────────────┼───────────────┼───────────────┤
│               │               │               │
│    Mailhog    │     Node.js   │     PHP-FPM   │
│               │               │               │
└───────────────┴───────────────┴───────────────┘
```

## Iniciar el Entorno de Desarrollo

### 1. Construir e Iniciar Contenedores

```bash
# Construir imágenes la primera vez
sail build

# Iniciar contenedores en segundo plano
sail up -d
```

### 2. Configuración Inicial de la Aplicación

```bash
# Generar clave de aplicación
sail artisan key:generate

# Ejecutar migraciones de base de datos
sail artisan migrate

# Ejecutar seeders para datos iniciales
sail artisan db:seed

# Instalar dependencias de Node.js
sail npm install

# Compilar assets
sail npm run dev
```

## Comandos Útiles de Sail

```bash
# Ver estado de los contenedores
sail ps

# Detener contenedores
sail stop

# Reiniciar contenedores
sail restart

# Ejecutar comando Artisan
sail artisan [comando]

# Ejecutar pruebas
sail test

# Acceder a la base de datos
sail mysql

# Ejecutar comando en el contenedor PHP
sail shell

# Ver registros
sail logs

# Ver registros en tiempo real
sail logs -f
```

## Configuración de Filament

### 1. Instalación de Paquetes de Filament

```bash
# Instalar Filament
sail composer require filament/filament

# Publicar assets de Filament
sail artisan vendor:publish --tag=filament-config

# Instalar Spatie Laravel Permission
sail composer require spatie/laravel-permission

# Publicar migraciones de spatie/laravel-permission
sail artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### 2. Configuración del Panel Administrativo

```php
// Configurar el panel en AdminPanelProvider.php
return $panel
    ->default()
    ->id('admin')
    ->path('positivo')    // URL en /positivo
    ->login()
    ->colors([
        'primary' => Color::Green,
    ])
    ->brandName('POSITIVOS')
    ->brandLogo(asset('images/logo-positivos.png'))
    ->favicon(asset('images/favicon.ico'));
```

## Comandos Docker Específicos

### Comandos Avanzados

```bash
# Ver uso de recursos
docker stats

# Limpiar volúmenes no utilizados
docker volume prune

# Limpiar imágenes no utilizadas
docker image prune

# Limpiar todo lo no utilizado (¡cuidado!)
docker system prune -a
```

### Solución de Problemas Comunes

#### Problema: Puerto en uso

```bash
# Verificar qué aplicación está usando el puerto
sudo lsof -i :80

# Cambiar puerto en docker-compose.yml
# - '80:80' → - '8080:80'
```

#### Problema: Permisos de archivos

```bash
# Arreglar permisos
sail artisan cache:clear
sudo chown -R $USER:$USER .
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
```

#### Problema: Base de datos no accesible

```bash
# Reiniciar contenedor de base de datos
docker restart positivos_mysql_1

# Verificar conexión
sail artisan tinker
DB::connection()->getPdo();
```

## Flujo de Trabajo Recomendado

1. Iniciar el entorno: `sail up -d`
2. Trabajar en tu rama de características: `git checkout feature/nombre-funcionalidad`
3. Ejecutar migraciones si es necesario: `sail artisan migrate`
4. Desarrollar y probar
5. Detener el entorno al finalizar: `sail down`

## Integración con CI/CD

Para entornos de integración continua, usar estas variables de entorno:

```
APP_ENV=testing
DB_CONNECTION=mysql
DB_HOST=mysql-testing
DB_DATABASE=testing
CACHE_DRIVER=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
```

## Referencias

- [Documentación de Laravel Sail](https://laravel.com/docs/10.x/sail)
- [Documentación de Docker](https://docs.docker.com/)
- [Tutorial de Docker en WSL2](https://docs.docker.com/desktop/windows/wsl/)
- [Video Tutorial: Instalar Docker en WSL sin Docker Desktop](https://www.youtube.com/watch?v=cv7Iyohhmo4)
- [Lista de Reproducción: Docker y Laravel](https://www.youtube.com/playlist?list=PLppGxI1UpbyvNqygSzsxqJdFQEq4_riBA)
