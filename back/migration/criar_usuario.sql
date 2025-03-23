-- 1. Criar o usuário 'smith' com senha
CREATE USER smith WITH PASSWORD 'Dansiguer@2014';

-- 2. Conceder permissões para o banco de dados 'database'
GRANT CONNECT ON DATABASE database TO smith;

-- 3. Conceder permissões de uso de schemas
GRANT USAGE ON SCHEMA public TO smith;

-- 4. Conceder permissões de criação de objetos no schema
GRANT CREATE ON SCHEMA public TO smith;

-- 5. Conceder permissões nas tabelas existentes
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO smith;

-- 6. Conceder permissões de manipulação de funções e triggers
GRANT EXECUTE ON ALL FUNCTIONS IN SCHEMA public TO smith;

-- 7. Conceder permissões para alterar os tipos ENUM e os tipos existentes
GRANT USAGE, SELECT ON ALL TYPES IN SCHEMA public TO smith;

-- 8. Conceder permissões de criação de objetos (se necessário para o script de migração)
GRANT CREATE ON DATABASE database TO smith;
