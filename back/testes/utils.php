<?php

function echo_var_dump($var, string $text = ""){
    echo "<p>";
    echo empty($text) ? "":$text.": ";
    var_dump($var);
    echo "</p>";
}