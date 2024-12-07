<?php

// Incluindo as classes
require_once 'Migration.php';

// Criando a instância de migração
$migration = new Migration();

if($migration->drop_schemas()){
    $migration->migrate();
}


?>