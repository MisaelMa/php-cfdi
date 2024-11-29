<?php

namespace Sat\Utils;

class Map
{
    public static function sortObject(array $obj, array $order): array
    {
        $sortedObj = [];

        // Añadir elementos en el orden especificado
        foreach ($order as $key) {
            if (array_key_exists($key, $obj)) {
                $sortedObj[$key] = $obj[$key];
            }
        }

        // Añadir los elementos restantes que no están en el orden
        foreach ($obj as $key => $value) {
            if (!array_key_exists($key, $sortedObj)) {
                $sortedObj[$key] = $value;
            }
        }

        return $sortedObj;
    }
}
