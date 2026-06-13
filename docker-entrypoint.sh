#!/bin/bash

# 1. Corregir Apache inmediatamente antes de cualquier otra acción
echo "Corrigiendo módulos MPM de Apache..."
a2dismod mpm_event mpm_worker || true
a2enmod mpm_prefork || true

# Configurar puerto dinámico para Railway (con fallback a 80)
export PORT=${PORT:-80}
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf


# 2. Optimizar Laravel (Caché)
echo "Optimizando la aplicación..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 3. Correr migraciones de forma segura (Opcional por ahora)
# php artisan migrate --force

# 4. Iniciar Apache en primer plano (Comando crucial para que el contenedor no muera)
echo "Iniciando Apache..."
exec apache2-foreground
