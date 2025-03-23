\echo '===> Atributos da Role'
\du smith

\echo '===> Roles que o usuário pertence'
SELECT r.rolname AS role_name
FROM pg_roles r
JOIN pg_auth_members m ON r.oid = m.roleid
JOIN pg_roles u ON u.oid = m.member
WHERE u.rolname = 'smith';

\echo '===> Permissões em Tabelas'
SELECT grantee, privilege_type, table_schema, table_name
FROM information_schema.role_table_grants
WHERE grantee = 'smith'
ORDER BY table_schema, table_name;

\echo '===> Permissões em Esquemas'
SELECT nspname AS schema_name,
       pg_catalog.pg_get_userbyid(nspowner) AS owner,
       has_schema_privilege('smith', nspname, 'USAGE') AS usage_privilege,
       has_schema_privilege('smith', nspname, 'CREATE') AS create_privilege
FROM pg_catalog.pg_namespace
ORDER BY schema_name;

\echo '===> Permissões em Sequências'
SELECT n.nspname AS schema_name,
       c.relname AS sequence_name,
       pg_catalog.pg_get_userbyid(c.relowner) AS owner,
       has_sequence_privilege('smith', c.oid, 'USAGE') AS usage_privilege,
       has_sequence_privilege('smith', c.oid, 'SELECT') AS select_privilege,
       has_sequence_privilege('smith', c.oid, 'UPDATE') AS update_privilege
FROM pg_class c
JOIN pg_namespace n ON n.oid = c.relnamespace
WHERE c.relkind = 'S'
ORDER BY schema_name, sequence_name;


\echo '===> Permissões em Funções'
SELECT p.proname AS function_name,
       n.nspname AS schema_name,
       pg_catalog.pg_get_userbyid(p.proowner) AS owner,
       has_function_privilege('smith', p.oid, 'EXECUTE') AS execute_privilege
FROM pg_proc p
JOIN pg_namespace n ON p.pronamespace = n.oid
WHERE n.nspname NOT IN ('pg_catalog', 'information_schema')
ORDER BY schema_name, function_name;
