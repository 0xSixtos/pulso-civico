<?php
declare(strict_types=1);

use PulsoCivico\Core\Router;
use PulsoCivico\Core\ViewHelper;

/** @var array{title?: string, description?: string, canonical?: string, og_image?: string, schema?: array} $seo */
$pageTitle = $seo['title'] ?? 'El Pulso de la Política — Observatorio del Congreso Mexicano';
$pageDesc = $seo['description'] ?? 'Monitoreo ciudadano y radiografía del poder legislativo en México.';
$canonicalUrl = $seo['canonical'] ?? Router::url('/');
$ogImage = $seo['og_image'] ?? Router::url('/assets/og-cover.png');
$homeUrl = Router::url('/');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= ViewHelper::e($pageTitle) ?></title>
    <meta name="description" content="<?= ViewHelper::e($pageDesc) ?>">
    <link rel="canonical" href="<?= ViewHelper::e($canonicalUrl) ?>">

    <!-- Open Graph / Redes Sociales -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= ViewHelper::e($pageTitle) ?>">
    <meta property="og:description" content="<?= ViewHelper::e($pageDesc) ?>">
    <meta property="og:url" content="<?= ViewHelper::e($canonicalUrl) ?>">
    <meta property="og:image" content="<?= ViewHelper::e($ogImage) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= ViewHelper::e($pageTitle) ?>">
    <meta name="twitter:description" content="<?= ViewHelper::e($pageDesc) ?>">
    <meta name="twitter:image" content="<?= ViewHelper::e($ogImage) ?>">

    <!-- Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <?php if (!empty($seo['schema'])): ?>
    <!-- Schema.org Datos Estructurados JSON-LD -->
    <script type="application/ld+json">
        <?= json_encode($seo['schema'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
    </script>
    <?php endif; ?>

    <style>
        :root {
            --ios-bg: #F2F2F7;
            --ios-surface: #FFFFFF;
            --ios-text: #1C1C1E;
            --ios-secondary: #8E8E93;
            --ios-separator: rgba(60, 60, 67, 0.12);
            --ios-blue: #007AFF;
            --ios-blue-light: #EBF3FF;
            --ios-gray-fill: #E5E5EA;
            --ios-card-radius: 20px;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "SF Pro Text", "Segoe UI", Roboto, sans-serif;
            background-color: var(--ios-bg);
            color: var(--ios-text);
            -webkit-font-smoothing: antialiased;
            letter-spacing: -0.015em;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Glassmorphism Navbar */
        .navbar-ios {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--ios-separator);
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .party-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.78rem;
            font-weight: 600;
            background: #F2F2F7;
            color: var(--ios-text);
            border: 1px solid rgba(0, 0, 0, 0.06);
            line-height: 1;
        }

        .party-icon {
            flex-shrink: 0;
            vertical-align: middle;
        }

        .card-civic {
            background: var(--ios-surface);
            border-radius: var(--ios-card-radius);
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-civic:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
        }

        .breadcrumb-ios {
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }
        .breadcrumb-ios a {
            color: var(--ios-secondary);
            text-decoration: none;
        }
        .breadcrumb-ios a:hover {
            color: var(--ios-blue);
        }

        /* Spotlight Modal Search */
        .spotlight-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(8px);
            z-index: 2000;
            display: none;
            align-items: flex-start;
            justify-content: center;
            padding-top: 10vh;
        }
        .spotlight-card {
            background: #FFFFFF;
            border-radius: 20px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-ios py-2">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-dark" href="<?= $homeUrl ?>">
            <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                <i class="bi bi-activity"></i>
            </span>
            <span>El Pulso de la Política</span>
            <span class="badge bg-light text-secondary border fw-normal" style="font-size: 0.65rem;">LXVI</span>
        </a>

        <div class="d-flex align-items-center gap-2">
            <a href="<?= $homeUrl ?>#seccion-mapa" class="btn btn-sm btn-light border rounded-pill px-3 d-none d-lg-inline-flex align-items-center gap-1 text-secondary" style="font-size: 0.82rem;">
                <i class="bi bi-geo-alt text-danger"></i>
                <span>Mapa Político</span>
            </a>
            <a href="<?= Router::url('/estudios') ?>" class="btn btn-sm btn-light border rounded-pill px-3 d-none d-md-inline-flex align-items-center gap-1 text-secondary" style="font-size: 0.82rem;">
                <i class="bi bi-mortarboard text-primary"></i>
                <span>Nivel de Estudios</span>
            </a>
            <a href="<?= Router::url('/comisiones') ?>" class="btn btn-sm btn-light border rounded-pill px-3 d-none d-md-inline-flex align-items-center gap-1 text-secondary" style="font-size: 0.82rem;">
                <i class="bi bi-briefcase text-secondary"></i>
                <span>Comisiones</span>
            </a>
            <button class="btn btn-sm btn-light border rounded-pill px-3 d-flex align-items-center gap-2 text-secondary" id="btnOpenSpotlight" type="button">
                <i class="bi bi-search"></i>
                <span class="d-none d-sm-inline">Buscar político o comisión...</span>
                <kbd class="bg-white border text-muted d-none d-md-inline-block px-1" style="font-size: 0.65rem;">Ctrl+K</kbd>
            </button>
        </div>
    </div>
</nav>

<!-- Modal Spotlight Search Universal -->
<div class="spotlight-backdrop" id="spotlightModal">
    <div class="spotlight-card">
        <div class="p-3 border-bottom d-flex align-items-center gap-2">
            <i class="bi bi-search text-secondary fs-5"></i>
            <input type="text" id="spotlightInput" class="form-control border-0 shadow-none fs-6" placeholder="Escribe el nombre de un legislador, estado o comisión..." autocomplete="off">
            <button type="button" class="btn btn-sm btn-light rounded-circle" id="btnCloseSpotlight">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="p-2" id="spotlightResults" style="max-height: 380px; overflow-y: auto;">
            <div class="text-center text-muted p-4 small">Comienza a escribir para buscar...</div>
        </div>
    </div>
</div>

<main class="flex-grow-1 py-4">
    <div class="container">
