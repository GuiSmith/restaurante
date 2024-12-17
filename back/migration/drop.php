<?php

// Incluindo as classes
require_once 'Migration.php';

// Criando a instância de migração
$migration = new Migration();

$migration->drop_schemas();
