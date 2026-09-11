# Sistema de Conferência de Peso

Sistema mobile-first em Laravel para conferência de peso em balanças, com integração automática para abertura de chamados no GLPI via API REST.

## Requisitos

- PHP 8.2+
- Composer
- MariaDB ou MySQL (o projeto usa MariaDB local na porta 3307, sem precisar de sudo)
- Acesso à API do GLPI (v2) para abertura de chamados

## Instalação

```bash
# 1. Instalar dependências
composer install

# 2. Copiar configuração
cp .env.example .env
php artisan key:generate

# 3. Configurar o .env conforme necessário (banco, GLPI)

# 4. Iniciar ambiente completo (banco + migrations + seed + servidor)
./start.sh
# Servidor em http://localhost:8088 (use APP_PORT=8000 ./start.sh para outra porta)
```

> Alternativas:
> - `./start.sh --reset-db` recria o banco do zero (dados e seed)
> - `./start.sh --stop` para o MariaDB local

Se preferir usar o banco manualmente, edite as variáveis `DB_*` no `.env` e rode:

```bash
php artisan migrate --seed
php artisan serve --port=8088
```

## Acesso inicial (após seed)

| Perfil | Usuário | Senha |
|--------|---------|-------|
| Admin | admin | admin123 |
| Gerente | gerente | gerente123 |
| Coletor | coletor | coletor123 |

> O login é feito com **usuário + senha** (não usa e-mail). O e-mail é opcional no cadastro.

## Funcionalidades

### Níveis de acesso
- **Admin**: acesso total (cadastros, coleta, relatórios, usuários)
- **Gerente**: coleta, histórico, calendário e relatórios da sua loja
- **Coletor**: apenas coleta de peso na loja atribuída

### Coleta de peso (mobile-first)
1. Seleciona a loja → balança (mostra a tolerância configurada)
2. Digita o peso esperado e o peso real (em kg)
3. O sistema calcula a diferença e compara com a tolerância
4. Dentro da tolerância → salva como **aprovado**
5. Fora da tolerância → pergunta se deseja abrir chamado no GLPI
   - **Sim** → abre chamado automaticamente via API
   - **Não** → registra como reprovado sem chamado

### Cadastros
- **Lojas**: nome, código, endereço, cidade/UF, telefone
- **Balanças**: vínculo com loja, marca, modelo, serial, capacidade e **tolerância em kg** (configurável por balança)

### Calendário
- Visualização mensal por loja
- Dias verdes = todos aprovados, amarelo = com ressalva, vermelho = houve diferença fora da tolerância
- Clique no dia para ver as conferências detalhadas

### Relatórios
- Filtro por loja e período
- Resumo de aprovadas/reprovadas
- Por loja e por balança
- Lista de conferências fora da tolerância
- Exportação em CSV

## Integração GLPI

A configuração do GLPI é feita pelo próprio sistema, sem precisar editar o `.env`:

1. Acesse **Admin → Configuração GLPI** (somente usuários admin) e informe:
   - URL da API (ex.: `http://192.168.10.216/api.php/v2`)
   - OAuth Client ID / Client Secret
   - Usuário e senha da conta API
   - Entidade padrão (ID), entidades recursivas e categoria do chamado (opcional)
2. Clique em **Testar conexão** para validar as credenciais e listar as entidades disponíveis.
3. No cadastro de cada **loja**, informe a **entidade GLPI** responsável por suas balanças.
   O chamado é aberto na entidade da loja (ou na padrão, se a loja não tiver).

> Os campos `client_secret` e `senha` ficam armazenados criptografados no banco (`settings`).
> Deixe-os em branco ao salvar para manter os valores atuais.

### Criar o cliente OAuth no GLPI
1. Acesse o GLPI como administrador
2. **Configuração → OAuth Clients** → adicionar cliente
3. Scope: `api`
4. Use o client_id e client_secret gerados na tela de Configuração GLPI

## Estrutura do banco

| Tabela | Descrição |
|--------|-----------|
| `users` | Usuários com role (admin/gerente/coletor) e loja |
| `lojas` | Lojas cadastradas |
| `balancas` | Balanças por loja com tolerância em kg |
| `conferencias` | Cabeçalho de cada conferência (status, pesos, diferença) |
| `conferencia_itens` | Itens conferidos em cada conferência |
| `tickets_glpi` | Registro dos chamados abertos/erros no GLPI |
| `settings` | Configurações do sistema (credenciais GLPI criptografadas) |

O chamado GLPI contém: loja, data, balança, item, peso esperado, peso real, diferença, tolerância, % de variação e colaborador. A prioridade é calculada automaticamente conforme a gravidade da diferença.

## Banco de dados MariaDB local

O `start.sh` inicializa um MariaDB isolado (dados em `mariadb-data/`, socket em `mariadb-run/mysql.sock`, porta `3307`) sem necessidade de sudo. Credenciais de desenvolvimento:
- Usuário: `balancas` | Senha: `balancas123` | Banco: `balancas`

Se preferir usar o MySQL do sistema, ajuste `DB_*` no `.env`.