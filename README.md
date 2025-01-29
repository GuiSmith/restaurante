# Projeto Restaurante - Configuração
## Funcionalidades do Sistema
- [Cadastro de itens do cardápio](#itens-de-cardápio)
- [Permitir abrir e fechar comandas](#comandas)
- [Permitir adicionar itens do cardápio em uma comanda](#itens-do-cardápio-em-uma-comanda)
- [Permitir pagar comanda fechada](#pagamentos)
- [Enviar Ordens de Produção para Copa e Cozinha](#relatórios)
- [Relatório de vendas diária](#relatórios)
- [Usuários](#usuários)
---
## Estrutura do Projeto
``` bash
# 7 directories, 26 files
restaurante/
├── back
│   ├── api
│   │   ├── comanda.php
│   │   ├── config.php
│   │   ├── itemcomanda.php
│   │   ├── item.php
│   │   ├── log.php
│   │   ├── pagamento.php
│   │   ├── relatorios.php
│   │   └── usuario.php
│   ├── classes
│   │   ├── Comanda.php
│   │   ├── CRUDModel.php
│   │   ├── Database.php
│   │   ├── ItemComanda.php
│   │   ├── Item.php
│   │   ├── Log.php
│   │   ├── Pagamento.php
│   │   ├── Relatorios.php
│   │   └── Usuario.php
│   ├── functions.php
│   └── migration
│       ├── drop.php
│       ├── index.php
│       └── Migration.php
├── documentos
│   ├── diagramas
│   │   ├── banco_modelo_conceitual.brM3
│   │   └── banco_modelo_logico.brM3
│   ├── feedback.pdf
│   └── requisitos.pdf
└── README.md
```
## Caminhos da API
Aqui você verá os caminhos disponíveis para interagir com a API do sistema de restaurante. Para cada requisito, temos o caminho correspondente e exemplos de requisições utilizando `cURL` para facilitar os testes.
### Itens
- **Caminho:** `restaurante/back/api/item.php`
#### Cadastro
**Método:** `POST`
- **Campos:**
  - `valor` (obrigatório): Valor do item.
  - `descricao` (obrigatório): Descrição do item.
  - `tipo` (obrigatório): Tipo do item, pode ser `BEBIDA` ou `PRATO`.
``` bash
curl -X POST "http://localhost/restaurante/back/api/item.php" \
-H "Content-Type: application/json" \
-H "Authorization: Bearer abc123456" \
-d '{
    "descricao": "Coxinha",
    "valor": "5.99",
    "tipo": "prato"
}'
```
#### Listagem
**Método:** `GET`
``` bash
 # Listar todos
curl -X GET "http://localhost/restaurante/back/api/item.php" \
-H "Authorization: Bearer abc123456"

# Listar específico
curl -X GET "http://localhost/restaurante/back/api/item.php?id=3" \
-H "Authorization: Bearer abc123456" \
```
#### Atualização
**Método:** `PUT`
- **Campos:**
  - `id` (obrigatório): identificação do item.
  - `valor` (opcional): Valor do item.
  - `descricao` (opcional): Descrição do item.
  - `tipo` (opcional): Tipo do item, pode ser `BEBIDA` ou `PRATO`.
``` bash
curl -X PUT "http://localhost/restaurante/back/api/item.php" \
-H "Content-Type: application/json" \
-H "Authorization: Bearer abc123456" \
-d '{
    "id":3,
    "descricao":"Teste de edicao"
}'
```
#### Deleção
**Método:** `DELETE`
- **Campos:**
  - `id` (obrigatório): identificação do item.
``` bash
curl -X DELETE "http://localhost/restaurante/back/api/item.php?id=3" \
-H "Authorization: Bearer abc123456"
```
### Comandas
- **Caminho:** `restaurante/back/api/comanda.php`
#### Abertura/Cadastro
**Método:** `POST`
```bash
curl -X POST "http://localhost/restaurante/back/api/comanda.php" \
-H "Authorization: Bearer abc123456"
```
#### Listagem
**Método:** `GET`
- **Campos:**
  - `id` (obrigatório): identificação do item.
  - `valor` (opcional): Valor do item.
  - `descricao` (opcional): Descrição do item.
  - `tipo` (opcional): Tipo do item, pode ser `BEBIDA` ou `PRATO`.
``` bash
# Listar todos
curl -X GET "http://localhost/restaurante/back/api/comanda.php" \
-H "Authorization: Bearer abc123456"

# Listar específico
curl -X GET "http://localhost/restaurante/back/api/comanda.php?id=3" \
-H "Authorization: Bearer abc123456"
```
#### Fechamento/Atualização
**Método:** `PUT`
- **Campos:**
  - `id` (obrigatório): identificação da comanda.
```bash
curl -X PUT "http://localhost/restaurante/back/api/comanda.php" \
-H "Content-Type: application/json" \
-H "Authorization: Bearer abc123456" \
-d '{
    "id": "3"
}'
```
#### Deleção
**Método:** `DELETE`
- **Campos:**
  - `id` (obrigatório): identificação da comanda.
``` bash
curl -X DELETE "http://localhost/restaurante/back/api/comanda.php?id=3" \
-H "Authorization: Bearer abc123456"
```
### Itens de comanda
- **Caminho:** `restaurante/back/api/itemcomanda.php`
#### Cadastro
**Método:** `POST`
- **Campos:**
  - `id_comanda` (obrigatório): identificação da comanda.
  - `id_item` (obrigatório): identificação do item.
  - `quantidade` (obrigatório): quantidade.
  - `descontos` (opcional): desconto dado neste item da comanda.
  - `isento` (opcional): se este item da comanda é isento.
  - `status` (opcional): status do item na comanda.
```bash
curl -X POST "http://localhost/restaurante/back/api/itemcomanda.php" \
-H "Content-Type: application/json" \
-d '{
    "id_comanda": "6",
    "id_item": "1",
    "quantidade": "3",
    "descontos": "4.5",
    "isento": "false"
}'
```
#### Listagem
**Método:** `GET`
``` bash
 # Listar todos
curl -X GET "http://localhost/restaurante/back/api/itemcomanda.php" \
-H "Authorization: Bearer abc123456"

# Listar específico
curl -X GET "http://localhost/restaurante/back/api/itemcomanda.php?id=3" \
-H "Authorization: Bearer abc123456" \
```
#### Atualização
**Método:** `PUT`
- **Campos:**
  - `id` (obrigatório): identificação do item da comanda.
  - `quantidade` (opcional): quantidade.
  - `descontos` (opcional): desconto dado neste item da comanda.
  - `isento` (opcional): se este item da comanda é isento.
  - `status` (opcional): status do item na comanda.
```bash
curl -X PUT "http://localhost/restaurante/back/api/itemcomanda.php" \
-H "Content-Type: application/json" \
-d '{
    "id": "1",
    "status": "pronto"
}'
```
#### Deleção
**Método:** `DELETE`
- **Campos:**
  - `id` (obrigatório): identificação do item da comanda.
``` bash
curl -X DELETE "http://localhost/restaurante/back/api/itemcomanda.php?id=3" \
-H "Authorization: Bearer abc123456"
```
### Pagamento
- **Caminho** `restaurante/back/api/pagamento.php`
#### Cadastro
**Método:** `POST`
- **Campos:**
 - `id_comanda` (obrigatório): Identificação da comanda
 - `forma_pagamento` (obrigatório): Forma de pagamento
 - `valor` (obrigatório): Valor do pagamento
``` bash
curl -X POST "http://localhost/restaurante/back/api/pagamento.php" \
-H "Content-Type: application/json" \
-H "Authorization: Bearer acb123456" \
-d '{
  "id_comanda": "1",
  "forma_pagamento": "PIX",
  "valor": "4.99"
}'
```
#### Listagem
**Método:** `GET`
``` bash
curl -X GET "http://localhost/restaurante/back/api/pagamento.php" \
-H "Authorization: Bearer acb123456"
```
#### Deleção
**Método:** `DELETE`
- **Campos:**
 - `id` (obrigatório): Identificação do pagamento
``` bash
curl -X DELETE "http://localhost/restaurante/back/api/pagamento.php?id=1" \
-H "Authorization: Bearer acb123456"
```
### Relatórios
- **Caminho:** `restaurante/back/api/relatorios.php`
#### Listagem
**Método:** `GET`
``` bash
curl -X GET "http://localhost/restaurante/back/api/relatorios.php" \
-H "Authorization: Bearer abc123456"
```
#### Visualização
**Método:** `GET`
**Campos:**
- `view` (obrigatório): nome do relatório, liste para ver os nomes disponíveis
```bash
curl -X GET "http://localhost/restaurante/back/api/relatorios.php?view=ordens_producao_cozinha"
```
### Usuários
- **Caminho:** `restaurante/back/api/usuario.php`
#### Cadastro
**Método:** `POST`
- **Campos:**
  - `nome` (obrigatório): Nome completo do usuário.
  - `email` (obrigatório): Endereço de email válido e único.
  - `senha` (obrigatório): Senha com pelo menos 6 caracteres.
```bash
curl -X POST "http://localhost/restaurante/back/api/usuario.php" \
-H "Content-Type: application/json" \
-d '{
    "nome": "João Silva",
    "email": "joao.silva@email.com",
    "senha": "123456"
}'
```
- **Respostas Possíveis:**
  - Sucesso:
    ```json
    {
      "ok": true,
      "mensagem": "Usuario criado com sucesso",
      "id": 1
    }
    ```
  - Erro (exemplo):
    ```json
    {
      "ok": false,
      "mensagem": "E-mail ja cadastrado"
    }
    ```
#### Atualização
**Método:** `PUT`
- **Campos:**
  - `id` (obrigatório): Identificador do usuário.
  - `nome` (opcional): Novo nome completo.
  - `email` (opcional): Novo email válido e único.
  - `senha` (opcional): Nova senha com pelo menos 6 caracteres.
  - `ativo` (opcional): Status do usuário (ativo/inativo).
```bash
curl -X PUT "http://localhost/restaurante/back/api/usuario.php" \
-H "Content-Type: application/json" \
-d '{
    "id": 1,
    "nome": "João Atualizado",
    "email": "joao.atualizado@email.com"
}'
```

- **Respostas Possíveis:**
  - Sucesso:
    ```json
    {
      "ok": true,
      "mensagem": "usuario atualizado com sucesso",
      "rows": 1
    }
    ```
  - Erro (exemplo):
    ```json
    {
      "ok": false,
      "mensagem": "E-mail ja está sendo usado por outro usuario"
    }
    ```
#### Deleção
**Método:** `DELETE`
- **Campos:**
  - `id` (obrigatório): ID(s) do(s) usuário(s) a ser(em) deletado(s), separados por vírgula.
```bash
curl -X DELETE "http://localhost/restaurante/back/api/usuario.php" \
-H "Content-Type: application/json" \
-d '{
    "id": "1,2,3"
}'
```
- **Respostas Possíveis:**
  - Sucesso:
    ```json
    {
      "ok": true,
      "mensagem": "Registro deletado com sucesso"
    }
    ```
  - Erro (exemplo):
    ```json
    {
      "ok": false,
      "mensagem": "Nao e possivel deletar usuario pois outros registros dependem deste"
    }
    ```
#### Login
**Método:** `POST`
- **Campos:**
  - `email` (obrigatório): Endereço de email do usuário.
  - `senha` (obrigatório): Senha do usuário.
  - `login` (opcional): Necessário para informar que você quer realizar login
  - `token` (opcional): Token para autenticação automática.
```bash
curl -X POST "http://localhost/restaurante/back/api/usuario.php" \
-H "Content-Type: application/json" \
-d '{
    "email": "joao.silva@email.com",
    "senha": "123456",
    "login": "true"
}'
```
- **Respostas Possíveis:**
  - Sucesso (com token):
    ```json
    {
      "ok": true,
      "mensagem": "Login realizado com sucesso",
      "token": "abcdef123456"
    }
    ```
  - Erro (exemplo):
    ```json
    {
      "ok": false,
      "mensagem": "E-mail ou senha incorretos"
    }
    ```
### Estrutura de Resposta Genérica
Todas as respostas seguem o formato:
```json
{
  "ok": true|false,
  "mensagem": "Mensagem descritiva",
  "dados": { ... }
}
```
---
## Requisitos
- **Ubuntu 20.04+**
- **PostgreSQL 12+**
- **PHP 7.4+**
- **Composer** (para gerenciar dependências, se necessário)
---
### 1. Instalar PostgreSQL
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
### 2. Criar Banco de Dados e Usuário
#### 2.1 Conectar ao PostgreSQL como super usuário
```bash
sudo -u postgres psql
```
#### 2.2 Criar o banco de dados `restaurante`:
```sql
CREATE DATABASE restaurante;
```
#### 2.3 Criar o usuário (se necessário) e conceder permissões:
```sql
CREATE USER smith WITH PASSWORD 'sua_senha';
GRANT ALL PRIVILEGES ON DATABASE restaurante TO smith;
```
#### 2.4 Verifique se o banco de dados foi criado:
```sql
\l
```
5. **Saia do PostgreSQL**:

```sql
\q
```

---
### 3. Configuração do PHP
#### 3.1 Instalar o PHP e extensões necessárias:
```bash
sudo apt install php php-pgsql php-cli php-curl
```
#### 3.2 Verificar a instalação do PHP:
```bash
php -v
```
---
### 4. Configuração do Projeto
#### 4.1 Clonar o repositório do projeto:
```bash
git clone https://github.com/guismith/restaurante.git
cd restaurante
```
---
### 5. Executar as Migrações
No diretório `restaurante/back/migration`, existe o arquivo `Migration.php` que executa as migrações no banco de dados PostgreSQL.
Esse comando executa o script de migração e cria/atualiza as tabelas necessárias no banco de dados
Abra no navegador
```bash
php back/migration/index.php
```
---
### 6. Conectar a Aplicação ao Banco de Dados
No código da aplicação, garanta que as configurações de conexão com o banco de dados PostgreSQL estejam corretas. No seu arquivo de configuração, como `Database.php`, os detalhes devem ser semelhantes a este:
```php
$dsn = "pgsql:host=localhost;port=5432;dbname=restaurante;user=smith;password=sua_senha";
```
---
### 7. Testar a Conexão
Agora, você pode testar a aplicação para garantir que tudo está funcionando corretamente e que a conexão com o banco de dados está estabelecida com sucesso.

Acesse `http://localhost/restaurante/back/api/relatorios.php` no navegador para verificar se a aplicação está rodando corretamente.
O resultado deve ser uma lista de views, relatórios disponíveis.
## Conclusão
Agora, a aplicação deve estar configurada e as migrações aplicadas corretamente. A partir daqui, você pode continuar o desenvolvimento, adicionar funcionalidades e realizar o gerenciamento do banco de dados conforme necessário.
### Problemas Comuns
- **Acesso Negado**: Se você encontrar um erro de "Acesso negado", verifique as permissões do usuário no PostgreSQL e a configuração do `pg_hba.conf`.
- **Erros de Migração**: Se a migração falhar, verifique os erros que aparecem na tela ou os detalhes da consulta SQL no arquivo de migração.
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