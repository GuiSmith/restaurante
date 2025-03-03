<?php
    require 'classes/Usuario.php';
    // require 'classes/Database.php';

    $db = Database::getInstance();

    $usuario = new Usuario();
    var_dump($usuario);
    var_dump($usuario->fetch(['id' => 2]));
    

    echo "\n";

?>