#!/bin/sh

echo "Iniciando entrypoint..."

# Verificar se o .env existe, se não, copiar do .env.example
if [ ! -f .env ]; then
    echo "Criando .env a partir do .env.example..."
    cp .env.example .env
fi

# Instalar dependências do Composer
echo "Instalando dependências do Composer..."
composer install --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-req=ext-intl --ignore-platform-req=ext-zip

# Gerar key da aplicação
echo "Gerando chave da aplicação..."
php artisan key:generate

# Criar arquivo do banco SQLite se não existir
echo "Verificando banco de dados SQLite..."
if [ ! -f database/database/database.sqlite ]; then
    echo "Criando database.sqlite..."
    mkdir -p database/database
    touch database/database/database.sqlite
    echo "Arquivo database.sqlite criado com sucesso!"
fi

# Verificar se a tabela users existe e está vazia (execução inicial)
if php artisan tinker --execute="echo (new Illuminate\Support\Facades\Schema)->hasTable('users') && \App\Models\User::count() === 0 ? 'empty' : 'not_empty';" | grep -q "empty"; then
    echo "Banco vazio, executando migrations e seeds..."
    php artisan migrate --force
    php artisan db:seed --force
else
    echo "Banco já populado, executando apenas migrations..."
    php artisan migrate --force
fi

# Instalar Filament se ainda não estiver instalado
echo "Verificando instalação do Filament..."
if [ ! -f vendor/filament/filament/composer.json ]; then
    echo "Instalando Filament..."
    php artisan filament:install --no-interaction
fi

# Limpar caches
echo "Limpando caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Configurar permissões
echo "Configurando permissões..."
chmod -R 775 storage bootstrap/cache

echo "Entrypoint concluído com sucesso!"

# Manter o container rodando
if [ "$1" = "queue" ]; then
    echo "Iniciando queue worker..."
    exec php artisan queue:work --sleep=3 --tries=3
else
    echo "Container pronto!"
    exec "$@"
fi
