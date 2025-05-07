<?php
// Este archivo se carga UNA vez antes de todos los tests

// Define la constante que usa Dolibarr internamente
define('DOL_DOCUMENT_ROOT', 'C:/xampp/htdocs/dolibarr/dolibarr/htdocs');

// Stubs para funciones globales de Dolibarr
if (!function_exists('dol_print_date')) {
    function dol_print_date($t,$m=0,$f=0){ return date('Y-m-d H:i:s',$t); }
}
if (!function_exists('dol_syslog')) {
    function dol_syslog($m,$l=0){} 
}

// Carga Composer y luego las clases de Dolibarr
require_once __DIR__ . '/../vendor/autoload.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/db/mysqli.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/stock/class/mouvementstock.class.php';
require_once __DIR__ . '/../htdocs/product/class/productbatch.class.php';


// Sólo aquí: creamos el alias
if (!class_exists('Mouvement')) {
    class Mouvement extends MouvementStock {}
}
