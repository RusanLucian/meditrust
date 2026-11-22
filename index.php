<?php

/**
 * MediTrust - Front Controller
 */

// 1. Încarcă configurația globală
require_once __DIR__ . '/config.php';

// 2. Înregistrează Autoloader-ul PSR-4 pentru namespace-ul App\
require_once __DIR__ . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

// 3. Inițializează sesiunea și ciclul de flash messages
\App\Core\Session::start();
\App\Core\Session::ageFlash();

// 4. Încarcă funcțiile globale helper
require_once __DIR__ . '/app/helpers.php';

// 5. Încarcă definirea rutelor
require_once __DIR__ . '/routes/web.php';

// 6. Procesează cererea HTTP prin Router și trimite răspunsul
$request = new \App\Core\Request();
$response = \App\Core\Router::dispatch($request);
$response->send();
