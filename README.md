# Projeto Restaurante - Configuração e Migrações

## REQUISITOS
• Cadastro de itens do cardápio:
• Permitir abrir e fechar comandas
• Permitir adicionar itens do cardápio em uma comanda
• Enviar Ordens de Produção para Copa e Cozinha
• Relatório de vendas diária
• Cadastro de usuários

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

# Caminhos da API

Aqui você verá os caminhos disponíveis para interagir com a API do sistema de restaurante. Para cada requisito, temos o caminho correspondente e exemplos de requisições utilizando `cURL` para facilitar os testes.

## Cadastro de Itens do Cardápio
- **Caminho:** `restaurante/back/api/item.php`
- **Caminho para Testes CURL:** `restaurante/testes/api/item`

### Exemplo de Requisição
Cadastrar um item no cardápio:
```bash
curl -X POST "http://localhost/restaurante/back/api/item.php" \
-H "Content-Type: application/json" \
-d '{
    "descricao": "Coxinha",
    "valor": "5.99",
    "tipo": "prato"
}'
```

## Abrir e Fechar Comandas
- **Caminho:** `restaurante/back/api/comanda.php`
- **Caminho para Testes CURL:** `restaurante/testes/api/comanda`

### Exemplos de Requisições
Abrir uma comanda:
```bash
curl -X POST "http://localhost/restaurante/back/api/comanda.php"
```

Fechar uma comanda:
```bash
curl -X PUT "http://localhost/restaurante/back/api/comanda.php" \
-H "Content-Type: application/json" \
-d '{
    "id": "3"
}'
```

## Adicionar Itens do Cardápio em uma Comanda
- **Caminho:** `restaurante/back/api/itemcomanda.php`
- **Caminho para Testes CURL:** `restaurante/testes/api/itemcomanda`

### Exemplos de Requisições
Adicionar um item na comanda:
```bash
curl -X POST "http://localhost/restaurante/back/api/itemcomanda.php" \
-H "Content-Type: application/json" \
-d '{
    "id_comanda": "6",
    "id_item": "1",
    "quantidade": "3"
}'
```

Atualizar o status de um item na comanda:
```bash
curl -X PUT "http://localhost/restaurante/back/api/itemcomanda.php" \
-H "Content-Type: application/json" \
-d '{
    "id": "1",
    "status": "pronto"
}'
```

## Enviar Ordens de Produção para Copa e Cozinha
- **Caminho:** `restaurante/back/api/relatorios.php`
- **Caminho para Testes CURL:** `restaurante/testes/api/relatorios`

### Exemplos de Requisições
Obter ordens de produção para a cozinha:
```bash
curl -X GET "http://localhost/restaurante/back/api/relatorios.php?view=ordens_producao_cozinha"
```

Obter ordens de produção para a copa:
```bash
curl -X GET "http://localhost/restaurante/back/api/relatorios.php?view=ordens_producao_copa"
```

## Relatório de Vendas Diária
- **Caminho:** `restaurante/back/api/relatorios.php`
- **Caminho para Testes CURL:** `restaurante/testes/api/relatorios`

## Cadastro de Usuários
- **Caminho:** `restaurante/back/api/usuario.php`
- **Caminho para Testes CURL:** `restaurante/testes/api/usuario`

### Exemplo de Requisição
Criar um usuário:
```bash
curl -X POST "http://localhost/restaurante/back/api/usuario.php" \
-H "Content-Type: application/json" \
-d '{
    "nome": "João Silva",
    "email": "joao.silva@email.com",
    "senha": "123456"
}'
```
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
- **Erros de Migração**: Se a migração falhar, verifique os logs de erros ou os detalhes da consulta SQL no arquivo de migração.
- **Erro de configuração do servidor**: Se aparecer uma mensagem dizendo que o servidor apache não foi configurado da forma correta, dever ser porque ele não está aceitando o arquivo .htaccess que permite métodos HTTP específicos dessa aplicação. Veja a seguir como resolver:

Para usar arquivos `.htaccess` no Apache, siga estas instruções:

1. **Certifique-se de que o módulo `mod_rewrite` esteja ativado**:
   Execute o seguinte comando para habilitar o módulo de reescrita:
   ```bash
   sudo a2enmod rewrite
   ```

2. **Permitir uso de `.htaccess` na configuração do Apache**:
   Edite o arquivo de configuração do Apache relacionado ao seu site. Geralmente, este arquivo está em `/etc/apache2/sites-available/`. Por exemplo:
   ```bash
   sudo nano /etc/apache2/sites-available/000-default.conf
   ```
   Encontre a seção `<Directory>` correspondente ao diretório raiz do seu site (geralmente `/var/www/html/`) e altere o valor da diretiva `AllowOverride` para `All`:
   ```apache
   <Directory /var/www/html/>
       AllowOverride All
   </Directory>
   ```

3. **Reinicie o Apache**:
   Após as alterações, reinicie o Apache para aplicar as configurações:
   ```bash
   sudo systemctl restart apache2
   ```

4. **Criar o arquivo `.htaccess`**:
   Agora você pode criar e usar um arquivo `.htaccess` no diretório raiz do seu site (`/var/www/html/` ou o caminho configurado).
---