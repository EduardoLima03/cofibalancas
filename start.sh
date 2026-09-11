#!/bin/bash
# Inicia o ambiente de desenvolvimento do sistema de Balanças
# Uso: ./start.sh  |  ./start.sh --reset-db  |  ./start.sh --stop

set -e

MYSQL_PORT=3307
APP_PORT="${APP_PORT:-8088}"
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
MY_CNF="$PROJECT_DIR/mariadb-balancas.cnf"

if [ ! -f "$MY_CNF" ]; then
cat > "$MY_CNF" <<EOF
[mysqld]
datadir=$PROJECT_DIR/mariadb-data
socket=$PROJECT_DIR/mariadb-run/mysql.sock
port=$MYSQL_PORT
pid-file=$PROJECT_DIR/mariadb-run/mysqld.pid
bind-address=127.0.0.1
log-error=$PROJECT_DIR/mariadb-run/error.log
[client]
socket=$PROJECT_DIR/mariadb-run/mysql.sock
[mysql]
socket=$PROJECT_DIR/mariadb-run/mysql.sock
EOF
fi

if [ ! -d "$PROJECT_DIR/mariadb-data" ]; then
    echo "[DB] Inicializando banco de dados..."
    mariadb-install-db --user=$(whoami) --datadir="$PROJECT_DIR/mariadb-data" --auth-root-authentication-method=normal > /dev/null 2>&1
fi

mkdir -p "$PROJECT_DIR/mariadb-run"

SOCKET="$PROJECT_DIR/mariadb-run/mysql.sock"
MYSQL_CMD=(mysql -h 127.0.0.1 -P "$MYSQL_PORT" -u balancas -pbalancas123)
if [ "$1" = "--stop" ]; then
    mysqladmin -h 127.0.0.1 -P "$MYSQL_PORT" -u balancas -pbalancas123 shutdown > /dev/null 2>&1 && echo "[DB] MariaDB parado." || echo "[DB] Nada para parar."
    exit 0
fi
if ! mysqladmin -h 127.0.0.1 -P "$MYSQL_PORT" -u balancas -pbalancas123 ping > /dev/null 2>&1; then
    echo "[DB] Iniciando MariaDB na porta $MYSQL_PORT..."
    mkdir -p "$PROJECT_DIR/mariadb-run"
    setsid mariadbd --defaults-file="$MY_CNF" --user=$(whoami) </dev/null > /dev/null 2>&1 &
    for i in $(seq 1 20); do
        sleep 1
        if mysqladmin -h 127.0.0.1 -P "$MYSQL_PORT" -u balancas -pbalancas123 ping > /dev/null 2>&1; then break; fi
    done
fi
if ! mysqladmin -h 127.0.0.1 -P "$MYSQL_PORT" -u balancas -pbalancas123 ping > /dev/null 2>&1; then
    echo "[ERRO] Não foi possível iniciar o MariaDB. Veja $PROJECT_DIR/mariadb-run/error.log" >&2
    exit 1
fi
echo "[DB] MariaDB rodando na porta $MYSQL_PORT"

cd "$PROJECT_DIR"
if [ "$1" = "--reset-db" ]; then
    echo "[DB] Recriando banco e seed..."
    "${MYSQL_CMD[@]}" -e "DROP DATABASE IF EXISTS balancas;" 2>/dev/null \
      || mysql -h 127.0.0.1 -P "$MYSQL_PORT" -u root -e "DROP DATABASE IF EXISTS balancas;"
fi

"${MYSQL_CMD[@]}" -e "CREATE DATABASE IF NOT EXISTS balancas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null \
  || mysql -h 127.0.0.1 -P "$MYSQL_PORT" -u root -e "CREATE DATABASE IF NOT EXISTS balancas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo "[DB] Banco 'balancas' pronto"

TABLES=$("${MYSQL_CMD[@]}" -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='balancas';" 2>/dev/null || true)
if [ -z "$TABLES" ] || [ "$TABLES" = "0" ]; then
    echo "[APP] Executando migrations..."
    php artisan migrate --force
fi

USERS=$("${MYSQL_CMD[@]}" -N -e "SELECT COUNT(*) FROM balancas.users;" 2>/dev/null || true)
if [ -z "$USERS" ] || [ "$USERS" = "0" ]; then
    echo "[APP] Executando seed..."
    php artisan db:seed --force
fi

echo "[APP] Iniciando servidor..."

FOUND_PORT=""
for candidate in $APP_PORT 8089 8090 8091 8092 8093; do
    if ! ss -tln 2>/dev/null | grep -qE ":$candidate\b"; then
        FOUND_PORT="$candidate"
        break
    fi
done
if [ -z "$FOUND_PORT" ]; then
    echo "[ERRO] Nenhuma porta livre encontrada." >&2
    exit 1
fi

echo "[APP] Iniciando servidor em http://localhost:$FOUND_PORT ..."
php artisan serve --port=$FOUND_PORT