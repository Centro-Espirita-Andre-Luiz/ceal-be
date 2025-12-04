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

# Gerar key da aplicação se não existir
echo "Verificando chave da aplicação..."
if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
    echo "Gerando chave da aplicação..."
    php artisan key:generate
fi

# ===========================================
# REMOVER PARTE DO SQLITE E ADICIONAR MYSQL
# ===========================================

# Aguardar MySQL estar pronto (apenas se necessário)
echo "Verificando conexão com MySQL..."
MAX_RETRIES=30
RETRY_COUNT=0

while ! php -r "
try {
    \$pdo = new PDO('mysql:host=mysql;dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
    echo 'Conectado ao MySQL com sucesso!';
    exit(0);
} catch (PDOException \$e) {
    echo 'Aguardando MySQL...';
    exit(1);
}
" 2>/dev/null; do
    RETRY_COUNT=$((RETRY_COUNT + 1))
    if [ $RETRY_COUNT -ge $MAX_RETRIES ]; then
        echo "❌ MySQL não está respondendo após $MAX_RETRIES tentativas"
        exit 1
    fi
    echo "⏳ Aguardando MySQL... ($RETRY_COUNT/$MAX_RETRIES)"
    sleep 2
done

# Verificar se as migrações já foram executadas
echo "Verificando status do banco de dados..."
MIGRATIONS_TABLE_EXISTS=$(php artisan tinker --execute="echo Illuminate\Support\Facades\Schema::hasTable('migrations') ? 'true' : 'false';" 2>/dev/null)

if [ "$MIGRATIONS_TABLE_EXISTS" = "false" ]; then
    echo "Banco vazio, executando migrations e seeds..."
    php artisan migrate --force

    php artisan db:seed
else
    echo "Banco já populado, executando apenas migrations pendentes..."
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
php artisan route:clear

# Configurar permissões
echo "Configurando permissões..."
chmod -R 775 storage bootstrap/cache

# Otimizar aplicação (produção)
echo "Otimizando aplicação..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Entrypoint concluído com sucesso!"

# Manter o container rodando
if [ "$1" = "queue" ]; then
    echo "🚀 Iniciando queue worker..."
    exec php artisan queue:work --sleep=3 --tries=3
else
    echo "✅ Container pronto para receber conexões!"
    exec "$@"
fi
