<?php
declare(strict_types=1);

/**
 * Pulso Cívico — Front Controller Principal (SEO-First & Observatorio Cívico).
 * Enrutador de URLs canónicas, buscador y mapa interactivo de la República Mexicana.
 */

// Autoload PSR-4 nativo
spl_autoload_register(function (string $class): void {
    $prefix = 'PulsoCivico\\';
    $baseDir = __DIR__ . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

use PulsoCivico\Core\Router;
use PulsoCivico\Controllers\WebController;

$router = new Router();
$ctrl = new WebController();

// Rutas Canónicas
$router->get('/', [$ctrl, 'home']);
$router->get('/politicos/{slug}', [$ctrl, 'politico']);
$router->get('/estados/{slug}', [$ctrl, 'estado']);
$router->get('/comisiones', [$ctrl, 'comisiones']);
$router->get('/comisiones/{slug}', [$ctrl, 'comision']);
$router->get('/estudios', [$ctrl, 'estudios']);
$router->get('/estudios/{categoria}', [$ctrl, 'estudios']);
$router->get('/sitemap.xml', [$ctrl, 'sitemap']);

// APIs Internas
$router->get('/api/buscar', [$ctrl, 'apiBuscar']);
$router->get('/api/estados/{slug}', [$ctrl, 'apiEstado']);

// Despachar petición
$router->dispatch();
