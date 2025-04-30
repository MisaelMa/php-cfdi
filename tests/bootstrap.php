<?php

// Cargar el autoloader principal
require __DIR__ . '/../vendor/autoload.php';

// Cargar el autoloader del paquete cfdi si existe
if (file_exists(__DIR__ . '/../packages/cfdi/vendor/autoload.php')) {
    require __DIR__ . '/../packages/cfdi/vendor/autoload.php';
}