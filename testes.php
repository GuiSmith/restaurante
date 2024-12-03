<?php

    require_once "back/classes/Usuario.php";

    $usuario = new Usuario(debug: true);

    var_dump($usuario);
    echo "<br>";

    var_dump($usuario->all());
?>