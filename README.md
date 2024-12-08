# Projeto Restaurante - Configuração e Migrações

Este projeto tem como objetivo gerenciar o sistema de restaurante, com tabelas de **usuários**, **itens de cardápio**, **comandas**, **pedidos**, **pagamentos**, entre outros. Este README fornece instruções para instalar e configurar o ambiente no **Ubuntu** e executar as migrações para criar o banco de dados e tabelas necessárias.

## Estrutura do Projeto

A estrutura do projeto é a seguinte:

```
restaurante/
├── back/
│   ├── api/
│   │   ├── comanda.php
│   │   ├── itemcomanda.php
│   │   ├── log.php
│   │   ├── relatorios.php
│   │   └── config.php
│   ├── classes/
│   │   ├── Comanda.php
│   │   ├── CRUDModel.php
│   │   ├── Database.php
│   │   ├── Item.php
│   │   ├── ItemComanda.php
│   │   ├── Log.php
│   │   ├── Pagamento.php
│   │   ├── Relatorios.php
│   │   └── Usuario.php
│   ├── functions.php
│   └── migration/
│       ├── drop.php
│       ├── index.php
│       └── Migration.php
├── banco.sql
├── diagramas/
│   ├── banco_modelo_conceitual.brM3
│   └── banco_modelo_logico.brM3
├── testes/
│   ├── api/
│   │   ├── comanda
│   │   ├── fluxo
│   │   ├── fluxo_gpt
│   │   ├── item
│   │   ├── itemcomanda
│   │   ├── pagamento
│   │   └── usuario
│   ├── classes/
│   │   ├── comanda.php
│   │   ├── fluxo.php
│   │   ├── item.php
│   │   ├── itemcomanda.php
│   │   ├── log.php
│   │   └── pagamento.php
│   ├── index.php
│   └── utils.php
├── README.md
```

- **back/**: Contém o código PHP responsável pelas funcionalidades do sistema (APIs, migrações, funções auxiliares e classes principais).
- **banco.sql**: Contém o arquivo SQL com os comandos para criação de banco de dados e tabelas.
- **diagramas/**: Contém os diagramas do banco de dados.
- **testes/**: Contém scripts e ferramentas para teste do sistema.
- **README.md**: Este arquivo de documentação.

---

## Requisitos

- **Ubuntu 20.04+**
- **PostgreSQL 12+**
- **PHP 7.4+**
- **Composer** (para gerenciar dependências, se necessário)

---

## Passo 1: Instalar PostgreSQL

No Ubuntu, o PostgreSQL pode ser instalado facilmente através do comando `apt`. Execute os seguintes comandos para instalar o PostgreSQL:

```bash
sudo apt update
sudo apt install postgresql postgresql-contrib
```

Depois de instalar o PostgreSQL, verifique se o serviço está em execução:

```bash
sudo systemctl status postgresql
```

---

## Passo 2: Criar Banco de Dados e Usuário

1. **Conectar ao PostgreSQL como superusuário**:

```bash
sudo -u postgres psql
```

2. **Criar o banco de dados `restaurante`**:

```sql
CREATE DATABASE restaurante;
```

3. **Criar o usuário** (se necessário) e conceder permissões:

```sql
CREATE USER smith WITH PASSWORD 'sua_senha';
GRANT ALL PRIVILEGES ON DATABASE restaurante TO smith;
```

4. **Verifique se o banco de dados foi criado**:

```sql
\l
```

5. **Saia do PostgreSQL**:

```sql
\q
```

---

## Passo 3: Configuração do PHP

1. **Instalar o PHP e extensões necessárias**:

```bash
sudo apt install php php-pgsql php-cli php-curl
```

2. **Verificar a instalação do PHP**:

```bash
php -v
```

---

## Passo 4: Configuração do Projeto

1. **Clonar o repositório do projeto**:

```bash
git clone https://github.com/guismith/restaurante.git
cd restaurante
```

---

## Passo 5: Executar as Migrações

No diretório `restaurante/back/migration`, existe o arquivo `Migration.php` que executa as migrações no banco de dados PostgreSQL.

1. **Verifique se as migrações estão configuradas corretamente**.

No projeto, há uma classe de migração que pode ser executada para criar ou atualizar tabelas no banco de dados `restaurante`.

2. **Rodar a migração**:

```bash
php back/migration/index.php
```

Esse comando executa o script de migração e cria/atualiza as tabelas necessárias no banco de dados.

---

## Passo 6: Testar a Conexão

Agora, você pode testar a aplicação para garantir que tudo está funcionando corretamente e que a conexão com o banco de dados está estabelecida com sucesso.

Acesse `http://localhost/restaurante/back/api/relatorios.php` no navegador para verificar se a aplicação está rodando corretamente.
O resultado deve ser uma lista de views, relatórios disponíveis.

---

## Passo 7: Conectar a Aplicação ao Banco de Dados

No código da aplicação, garanta que as configurações de conexão com o banco de dados PostgreSQL estejam corretas. No seu arquivo de configuração, como `config.php`, os detalhes devem ser semelhantes a este:

```php
$dsn = "pgsql:host=localhost;port=5432;dbname=restaurante;user=smith;password=sua_senha";
```

---

## Conclusão

Agora, a aplicação deve estar configurada e as migrações aplicadas corretamente. A partir daqui, você pode continuar o desenvolvimento, adicionar funcionalidades e realizar o gerenciamento do banco de dados conforme necessário.

---

## Problemas Comuns

- **Acesso Negado**: Se você encontrar um erro de "Acesso negado", verifique as permissões do usuário no PostgreSQL e a configuração do `pg_hba.conf`.
- **Dependências PHP**: Se você tiver problemas com dependências PHP, execute `composer install` para garantir que todas as dependências sejam instaladas corretamente.
- **Erros de Migração**: Se a migração falhar, verifique os logs de erros ou os detalhes da consulta SQL no arquivo de migração.

---