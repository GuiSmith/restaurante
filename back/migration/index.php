<?php

// Incluindo as classes
require_once 'Migration.php';

// Criando a instância de migração
$migration = new Migration();

// Executando as migrações
$migration->migrate();

?>