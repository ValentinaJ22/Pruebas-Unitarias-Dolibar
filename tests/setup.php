<?php
// tests/setup.php

// Verificar si la función ya está definida antes de declararla
if (!function_exists('analyseVarsForSqlAndScriptsInjection')) {
    function analyseVarsForSqlAndScriptsInjection($uri, $type) {
        return true;  // Ignorar la validación de inyecciones durante las pruebas
    }
}