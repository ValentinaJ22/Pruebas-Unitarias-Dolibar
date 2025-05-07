<?php
// main.inc.php - Archivo de configuración e inicialización de Dolibarr

// Iniciar la sesión de PHP (si no se ha hecho ya en otro archivo)
if (!isset($_SESSION)) {
    session_start();
}

// Incluir configuraciones generales (configuración de la base de datos, rutas, etc.)
require_once __DIR__ . '/conf/conf.php';  // Configuración general (base de datos, idioma, etc.)

// Definir las rutas esenciales para los recursos
define('DOL_DOCUMENT_ROOT', __DIR__);  // Raíz de documentos de Dolibarr
define('DOL_DATA_ROOT', __DIR__ . '/data');  // Ruta para los datos

// Inclusión de librerías esenciales
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';  // Librerías de administración
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin/entlib.php';  // Librerías adicionales si es necesario

// Comprobar que el entorno es seguro y no se están realizando inyecciones de código
if (!analyseVarsForSqlAndScriptsInjection($_SERVER['REQUEST_URI'], 2)) {
    die('Acceso denegado por seguridad');
}

// Inicializar la configuración de la base de datos (común para todas las páginas)
$db = new DB_Dolibarr();

// Comprobar la conexión a la base de datos
if ($db->connected) {
    // Continuar con la carga de la aplicación
} else {
    die('No se puede conectar a la base de datos');
}

// Definir la codificación y los parámetros globales
header('Content-Type: text/html; charset=utf-8');
setlocale(LC_ALL, 'es_ES.UTF-8');  // Establecer el idioma y la configuración regional

// Inicializar las clases necesarias para el funcionamiento de la aplicación
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin/libadmin.php';  // Librerías de administración adicionales
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin/libsys.php';  // Sistema de gestión de errores y notificaciones

// Inicialización de variables globales, por ejemplo, para la sesión o el usuario
$user = new User($db);  // Crear un objeto de usuario

// Comprobar si el usuario está autenticado
if (!$user->isAuthenticated()) {
    die('Acceso no autorizado');
}

// Otras configuraciones necesarias para las pruebas
// Esto puede incluir configuración de logging, manejo de errores, etc.
