<?php
declare(strict_types=1);

require_once __DIR__ . '/core/Linter.php';
require_once __DIR__ . '/core/GitOps.php';
require_once __DIR__ . '/core/Inspector.php';
require_once __DIR__ . '/core/ScraperRouter.php';

use PulsoCivico\Tools\Linter;
use PulsoCivico\Tools\GitOps;
use PulsoCivico\Tools\Inspector;
use PulsoCivico\Tools\ScraperRouter;

$rootDir = dirname(__DIR__);
$action = $argv[1] ?? 'help';
$target = $argv[2] ?? '';
$extra  = $argv[3] ?? '';

$response = [
    'action' => $action,
    'timestamp' => date('Y-m-d H:i:s'),
];

switch ($action) {
    case 'lint':
        // target puede ser una lista de archivos separados por coma o un solo archivo
        if ($target === '') {
            $response['error'] = 'Debe especificar al menos un archivo para lint.';
            break;
        }
        $files = explode(',', $target);
        // Limpiar rutas relativas o absolutas
        $resolvedFiles = array_map(function ($f) use ($rootDir) {
            $f = trim($f);
            if (!preg_match('/^[a-zA-Z]:\\\\|^\//', $f)) {
                return $rootDir . '/' . ltrim($f, '/\\');
            }
            return $f;
        }, $files);

        $result = Linter::checkFiles($resolvedFiles);
        $response = array_merge($response, $result);
        break;

    case 'lint:dir':
        $dir = $target !== '' ? $target : $rootDir . '/src';
        if (!preg_match('/^[a-zA-Z]:\\\\|^\//', $dir)) {
            $dir = $rootDir . '/' . ltrim($dir, '/\\');
        }
        $result = Linter::checkDirectory($dir);
        $response = array_merge($response, $result);
        break;

    case 'git:status':
        $result = GitOps::status();
        $response = array_merge($response, $result);
        break;

    case 'git:diff':
        $result = GitOps::diffSummary();
        $response = array_merge($response, $result);
        break;

    case 'health':
    case 'check':
        $result = Inspector::checkHealth($rootDir);
        $response = array_merge($response, $result);
        break;

    case 'search':
    case 'find':
        $result = Inspector::searchCode($rootDir, $target, $extra);
        $response = array_merge($response, $result);
        break;

    case 'scrape':
    case 'ingest':
        // Target puede ser 'sil', 'nacional' o un ID numérico de entidad
        $source = 'sil';
        $params = [];
        if ($target === 'nacional' || $target === 'all') {
            $params['nacional'] = true;
        } elseif (is_numeric($target)) {
            $params['entidad'] = (int)$target;
        } elseif ($target !== '') {
            $source = $target;
        }

        if ($extra !== '') {
            if ($extra === 'nacional' || $extra === 'all') {
                $params['nacional'] = true;
            } elseif (is_numeric($extra)) {
                $params['entidad'] = (int)$extra;
            } else {
                $decoded = json_decode($extra, true);
                if (is_array($decoded)) {
                    $params = array_merge($params, $decoded);
                } elseif (str_contains($extra, '=')) {
                    foreach (explode(',', $extra) as $pair) {
                        $pParts = explode('=', trim($pair), 2);
                        if (count($pParts) === 2) {
                            $k = trim($pParts[0]);
                            $v = trim($pParts[1]);
                            $params[$k] = is_numeric($v) ? (int)$v : $v;
                        }
                    }
                }
            }
        }
        $result = ScraperRouter::run($rootDir, $source, $params);
        $response = array_merge($response, $result);
        break;

    case 'stats:educacion':
        spl_autoload_register(function (string $class) use ($rootDir): void {
            $prefix = 'PulsoCivico\\';
            $baseDir = $rootDir . '/src/';
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) return;
            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) require_once $file;
        });
        $ds = new \PulsoCivico\Services\DataService();
        $targetEstado = $target !== '' ? $target : null;
        $response['target'] = $targetEstado;
        $response['estadisticas'] = $ds->getEstadisticasEscolaridad(null, $targetEstado);
        break;

    case 'stats:instituciones':
        spl_autoload_register(function (string $class) use ($rootDir): void {
            $prefix = 'PulsoCivico\\';
            $baseDir = $rootDir . '/src/';
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) return;
            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) require_once $file;
        });
        $ds = new \PulsoCivico\Services\DataService();
        $stats = $ds->getEstadisticasEscolaridad();
        $response['resumen_tipo_institucion'] = $stats['resumen_tipo_institucion'] ?? [];
        $response['por_camara_institucion'] = $stats['por_camara_institucion'] ?? [];
        $response['por_partido_institucion'] = $stats['por_partido_institucion'] ?? [];
        $response['top_instituciones_publicas'] = $stats['top_instituciones_publicas'] ?? [];
        $response['top_instituciones_privadas'] = $stats['top_instituciones_privadas'] ?? [];
        $response['top_instituciones'] = $stats['top_instituciones'] ?? [];
        break;




    case 'sync:paths':
        // Fuente: argumento (run.ps1 sync:paths "<ruta>") o variable de entorno PULSO_PATHS_SOURCE
        $sourcePaths = $target !== '' ? $target : (string) (getenv('PULSO_PATHS_SOURCE') ?: '');
        $destPaths = $rootDir . '/storage/data/mexico_paths.json';
        if (file_exists($sourcePaths)) {
            @mkdir(dirname($destPaths), 0777, true);
            copy($sourcePaths, $destPaths);
            $response['synced'] = true;
            $response['dest'] = $destPaths;
        } else {
            $response['synced'] = false;
            $response['error'] = 'Source paths not found';
        }
        break;

    case 'politico':
        spl_autoload_register(function (string $class) use ($rootDir): void {
            $prefix = 'PulsoCivico\\';
            $baseDir = $rootDir . '/src/';
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) return;
            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) require_once $file;
        });
        $ds = new \PulsoCivico\Services\DataService();
        $targetSlug = $target !== '' ? $target : 'ayala-robles-armando';
        $pol = $ds->getPoliticoBySlug($targetSlug);
        $response['politico'] = [
            'nombre' => $pol['nombre_completo'] ?? null,
            'slug' => $pol['slug'] ?? null,
            'suplente_nombre' => $pol['suplente_nombre'] ?? null,
            'tipo_cargo' => $pol['tipo_cargo'] ?? null,
            'entidad' => $pol['entidad'] ?? null,
            'distrito' => $pol['distrito'] ?? null,
            'principio' => $pol['principio_eleccion'] ?? null,
            'titular_referencia' => $pol['metadata']['titular_referencia_sil'] ?? null,
            'titular_nombre' => $pol['metadata']['titular_nombre'] ?? null,
            'titular_encontrado' => ($ds->getPoliticoByReferencia($pol['metadata']['titular_referencia_sil'] ?? '') ?? $ds->getPoliticoByNombre($pol['metadata']['titular_nombre'] ?? ''))['slug'] ?? null,
            'suplente_encontrado' => ($ds->getPoliticoByReferencia($pol['metadata']['suplente_referencia_sil'] ?? '') ?? $ds->getPoliticoByNombre($pol['suplente_nombre'] ?? ''))['slug'] ?? null,
            'asistencia_kpi' => $pol['asistencia_kpi'] ?? null,
            'disciplina_partidaria_pct' => $pol['disciplina_partidaria_pct'] ?? null,
            'votaciones_destacadas' => $pol['votaciones_destacadas'] ?? [],
            'metadata' => $pol['metadata'] ?? [],
            'linea_tiempo' => $pol['linea_tiempo'] ?? [],
        ];
        $ref = $pol['referencia_sil'] ?? '9228516';
        $rawPath = $rootDir . "/storage/raw/html/profile_{$ref}.html";
        if (file_exists($rawPath)) {
            $html = (string)file_get_contents($rawPath);
            $htmlUtf8 = mb_convert_encoding($html, 'UTF-8', 'ISO-8859-1, UTF-8');
            preg_match_all('/<tr[^>]*>.*?<\/tr>/is', $htmlUtf8, $rows);
            $foundRows = [];
            foreach ($rows[0] as $r) {
                if (stripos($r, 'Suplente de:') !== false) {
                    $foundRows[] = $r;
                }
            }
        }
        break;

    case 'check:trunca':
        spl_autoload_register(function (string $class) use ($rootDir): void {
            $prefix = 'PulsoCivico\\';
            $baseDir = $rootDir . '/src/';
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) return;
            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) require_once $file;
        });
        $ds = new \PulsoCivico\Services\DataService();
        $ed = $ds->getEducationService();
        $casos = [];
        foreach ($ds->getPoliticos() as $p) {
            $analisis = $ed->clasificarPolitico($p);
            // Casos donde tiene_trunca es true pero el grado_maximo es licenciatura o posgrado
            // O donde dice Licenciatura pero también trunca
            if ($analisis['tiene_trunca']) {
                $casos[] = [
                    'nombre' => $p['nombre_completo'],
                    'slug' => $p['slug'],
                    'grado_maximo' => $analisis['grado_maximo'],
                    'categoria_grupo' => $ed->determinarCategoriaAgrupada($analisis['grado_maximo']),
                    'titulo_destacado' => $analisis['titulo_destacado'],
                    'estudios_clasificados' => $analisis['estudios_clasificados'],
                ];
            }
        }
        $response['total_casos_trunca'] = count($casos);
        $response['casos'] = array_slice($casos, 0, 15);
        break;

    case 'votaciones':
        spl_autoload_register(function (string $class) use ($rootDir): void {
            $prefix = 'PulsoCivico\\';
            $baseDir = $rootDir . '/src/';
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) return;
            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) require_once $file;
        });
        $ds = new \PulsoCivico\Services\DataService();
        $response['votaciones'] = $ds->getVotaciones(20);
        break;

    case 'sesiones':
        spl_autoload_register(function (string $class) use ($rootDir): void {
            $prefix = 'PulsoCivico\\';
            $baseDir = $rootDir . '/src/';
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) return;
            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) require_once $file;
        });
        $ds = new \PulsoCivico\Services\DataService();
        $response['sesiones'] = $ds->getSesiones(50);
        break;

    case 'check:suplentes':
        $htmlDir = $rootDir . '/storage/raw/html';
        $files = glob($htmlDir . '/profile_*.html');
        $suplentesDe = [];
        $suplentes = [];
        foreach ($files as $file) {
            $ref = basename($file, '.html');
            $ref = str_replace('profile_', '', $ref);
            $content = (string)file_get_contents($file);
            $contentUtf8 = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1, UTF-8');
            if (preg_match('/<b>Suplente de:<\/b>\s*<\/td>\s*<td>(.*?)<\/td>/is', $contentUtf8, $m)) {
                $rawTd = $m[1];
                $linkRef = null;
                if (preg_match('/Referencia=(\d+)/i', $rawTd, $refM)) {
                    $linkRef = $refM[1];
                }
                $cleanName = trim(strip_tags($rawTd));
                $cleanName = preg_replace('/^(Diputad[ao]|Senador[a]?)\s+/iu', '', $cleanName);
                $cleanName = trim(html_entity_decode($cleanName));
                $suplentesDe[$ref] = [
                    'titular_nombre' => $cleanName,
                    'titular_referencia' => $linkRef,
                ];
            }
            if (preg_match('/<b>Suplente:<\/b>\s*<\/td>\s*<td>(.*?)<\/td>/is', $contentUtf8, $m)) {
                $rawTd = $m[1];
                $linkRef = null;
                if (preg_match('/Referencia=(\d+)/i', $rawTd, $refM)) {
                    $linkRef = $refM[1];
                }
                $cleanName = trim(strip_tags($rawTd));
                $cleanName = preg_replace('/^(Diputad[ao]|Senador[a]?)\s+/iu', '', $cleanName);
                $cleanName = trim(html_entity_decode($cleanName));
                if ($linkRef !== null || $cleanName !== '') {
                    $suplentes[$ref] = [
                        'suplente_nombre' => $cleanName,
                        'suplente_referencia' => $linkRef,
                    ];
                }
            }
        }
        spl_autoload_register(function (string $class) use ($rootDir): void {
            $prefix = 'PulsoCivico\\';
            $baseDir = $rootDir . '/src/';
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) return;
            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) require_once $file;
        });
        $ds = new \PulsoCivico\Services\DataService();
        $titularesEstado = [];
        foreach ($suplentesDe as $sRef => $tData) {
            $nom = $tData['titular_nombre'];
            $tRef = $tData['titular_referencia'];
            $foundByNom = $ds->getPoliticoByNombre($nom);
            $foundByRef = $tRef ? $ds->getPoliticoByReferencia($tRef) : null;
            $titularesEstado[$sRef] = [
                'suplente_ref' => $sRef,
                'titular_nombre' => $nom,
                'titular_referencia' => $tRef,
                'encontrado_en_db' => ($foundByRef !== null || $foundByNom !== null),
                'slug' => $foundByRef['slug'] ?? $foundByNom['slug'] ?? null,
            ];
        }
        $response['total_suplentes_de'] = count($suplentesDe);
        $response['analisis_titulares'] = $titularesEstado;
        break;

    case 'ingest:titular':
        spl_autoload_register(function (string $class) use ($rootDir): void {
            $prefix = 'PulsoCivico\\';
            $baseDir = $rootDir . '/src/';
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) return;
            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) require_once $file;
        });

        $titularRef = $target !== '' ? $target : '9228515';
        $logger = new \PulsoCivico\Core\Logger($rootDir . '/storage/logs');
        $client = new \PulsoCivico\Core\HttpClient(['timeout' => 15], $logger);

        // 1. Descargar HTML de perfil
        $rawPath = $rootDir . "/storage/raw/html/profile_{$titularRef}.html";
        if (!file_exists($rawPath) || filesize($rawPath) < 1000) {
            $url = "http://sil.gobernacion.gob.mx/Librerias/pp_PerfilLegislador.php?Referencia={$titularRef}";
            $res = $client->request('GET', $url);
            if ($res['status'] === 200 && strlen($res['body']) > 1000) {
                file_put_contents($rawPath, $res['body']);
            }
        }

        // 2. Descargar foto
        $photoDir = $rootDir . '/storage/raw/photos';
        if (!is_dir($photoDir)) mkdir($photoDir, 0777, true);
        $photoDest = "$photoDir/{$titularRef}.jpg";
        $fotoUrl = "http://sil.gobernacion.gob.mx/Archivos/Fotos/{$titularRef}.jpg";
        if (!file_exists($photoDest)) {
            $client->downloadFile($fotoUrl, $photoDest);
        }

        // 3. Parsear perfil
        $cfg = require $rootDir . '/config/config.php';
        $silService = new \PulsoCivico\Services\SilIngestService($cfg, $client, $logger);

        // Usar reflection para llamar a fetchAndParseProfile
        $refMethod = new \ReflectionMethod($silService, 'fetchAndParseProfile');
        $refMethod->setAccessible(true);
        $perfilData = $refMethod->invoke($silService, $titularRef);

        $htmlRaw = (string)file_get_contents($rawPath);
        $htmlUtf8 = mb_convert_encoding($htmlRaw, 'UTF-8', 'ISO-8859-1, UTF-8');

        // Nombre
        $nombre = null;
        if (preg_match('/<b>(?:Diputad[ao]|Senador[a]?)\s+(.*?)<\/b>/iu', $htmlUtf8, $nm)) {
            $nombre = trim(strip_tags($nm[1]));
        }
        $nombre = html_entity_decode($nombre ?? "Legislador {$titularRef}", ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $nombre = trim(preg_replace('/\s+/', ' ', $nombre));

        // Invertir a Apellidos Nombres si viene Nombres Apellidos
        // Si no tiene coma y son 3 o 4 palabras: "Betzabé Martínez Arango" -> "Martínez Arango Betzabé"
        $partes = preg_split('/\s+/', $nombre);
        if (count($partes) === 3) {
            $nombreOrdenado = $partes[1] . ' ' . $partes[2] . ' ' . $partes[0];
        } elseif (count($partes) === 4) {
            $nombreOrdenado = $partes[2] . ' ' . $partes[3] . ' ' . $partes[0] . ' ' . $partes[1];
        } else {
            $nombreOrdenado = $nombre;
        }

        // Partido
        $partido = 'Morena';
        if (preg_match('/Partido:<\/b>\s*<\/td>\s*<td[^>]*>(.*?)<\/td>/iu', $htmlUtf8, $pm)) {
            $partido = trim(strip_tags($pm[1]));
        }

        // Entidad
        $entidad = 'Durango';
        if (preg_match('/Entidad:<\/b>\s*<\/td>\s*<td[^>]*>(.*?)<\/td>/iu', $htmlUtf8, $em)) {
            $entidad = trim(strip_tags($em[1]));
        }

        // Principio
        $principio = 'Mayoría Relativa';
        if (preg_match('/Principio de elecci[oó]n:<\/b>\s*<\/td>\s*<td[^>]*>(.*?)<\/td>/iu', $htmlUtf8, $prm)) {
            $principio = trim(strip_tags($prm[1]));
        }

        $camara = (preg_match('/<b>(?:Senador|Senadora)\b/iu', $htmlUtf8) || stripos($htmlUtf8, 'Cámara de Senadores') !== false && !preg_match('/<b>(?:Diputad[ao])\b/iu', $htmlUtf8)) ? 'Senadores' : 'Diputados';

        $suplenteNombre = $perfilData['suplente'] ?? null;
        $suplenteRef = $perfilData['suplente_referencia_sil'] ?? null;

        $dto = new \PulsoCivico\DTO\LegisladorDTO(
            referenciaSil: $titularRef,
            nombreCompleto: $nombreOrdenado,
            camara: $camara,
            legislatura: 66,
            partido: $partido,
            principioEleccion: $principio,
            entidad: $entidad,
            distrito: $perfilData['distrito'] ?? null,
            tipoCargo: 'Titular (Con Licencia)',
            suplenteNombre: $suplenteNombre,
            fechaTomaProtesta: '29/08/2024',
            fechaNacimiento: $perfilData['nacimiento'] ?? null,
            correoInstitucional: $perfilData['correo'] ?? null,
            telefono: $perfilData['telefono'] ?? null,
            ubicacionOficina: $perfilData['ubicacion'] ?? 'Palacio Legislativo de San Lázaro',
            ultimoGradoEstudios: $perfilData['ultimo_grado_estudios'] ?? null,
            preparacionAcademica: $perfilData['preparacion_academica'] ?? null,
            experienciaLegislativaResumen: $perfilData['experiencia_legislativa_resumen'] ?? null,
            fotoOficialUrl: $fotoUrl,
            fotoLocalPath: file_exists($photoDest) ? $photoDest : null,
            comisiones: $perfilData['comisiones'] ?? [],
            trayectoriaPolitica: $perfilData['trayectoria_politica'] ?? [],
            trayectoriaLegislativa: $perfilData['trayectoria_legislativa'] ?? [],
            trayectoriaAdministrativa: $perfilData['trayectoria_administrativa'] ?? [],
            escolaridadDetalle: $perfilData['escolaridad_detalle'] ?? [],
            licencias: $perfilData['licencias'] ?? [],
            iniciativas: [],
            puntosAcuerdo: [],
            redesSociales: $perfilData['redes_sociales'] ?? [],
            metadata: [
                'fuente' => 'SIL SEGOB',
                'titular_referencia_sil' => null,
                'titular_nombre' => null,
                'suplente_referencia_sil' => $suplenteRef,
                'estatus_licencia' => 'Con Licencia',
                'fecha_extraccion' => date('c'),
            ]
        );

        // 4. Agregar a congreso_union_leg_66.json
        $congresoFile = $rootDir . '/storage/data/json/congreso_union_leg_66.json';
        $cData = json_decode((string)file_get_contents($congresoFile), true);
        $grupo = ($camara === 'Senadores') ? 'senadores' : 'diputados';

        // Evitar duplicados
        $yaExiste = false;
        foreach ($cData[$grupo] as $idx => $existente) {
            if (($existente['referencia_sil'] ?? '') === $titularRef) {
                $cData[$grupo][$idx] = $dto->toArray();
                $yaExiste = true;
                break;
            }
        }
        if (!$yaExiste) {
            $cData[$grupo][] = $dto->toArray();
            if ($grupo === 'diputados') {
                $cData['metadata']['total_diputados']++;
            } else {
                $cData['metadata']['total_senadores']++;
            }
            $cData['metadata']['total_general']++;
        }

        file_put_contents($congresoFile, json_encode($cData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $response['ok'] = true;
        $response['legislador_ingestado'] = [
            'nombre' => $dto->nombreCompleto,
            'slug' => \PulsoCivico\Services\DataService::slugify($dto->nombreCompleto),
            'cargo' => $dto->tipoCargo,
            'suplente' => $dto->suplenteNombre,
            'redes' => count($dto->redesSociales),
            'foto' => file_exists($photoDest),
        ];
        break;

    case 'enrich:social':
        spl_autoload_register(function (string $class) use ($rootDir): void {
            $prefix = 'PulsoCivico\\';
            $baseDir = $rootDir . '/src/';
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) return;
            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) require_once $file;
        });
        $result = \PulsoCivico\Services\SilIngestService::enrichExistingJsonWithSocial($rootDir);
        $response = array_merge($response, $result);
        break;

    case 'enrich:suplentes':
        spl_autoload_register(function (string $class) use ($rootDir): void {
            $prefix = 'PulsoCivico\\';
            $baseDir = $rootDir . '/src/';
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) return;
            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) require_once $file;
        });
        $result = \PulsoCivico\Services\SilIngestService::enrichExistingJsonWithSuplentes($rootDir);
        $response = array_merge($response, $result);
        break;

    case 'status:avance':
        spl_autoload_register(function (string $class) use ($rootDir): void {
            $prefix = 'PulsoCivico\\';
            $baseDir = $rootDir . '/src/';
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) return;
            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) require_once $file;
        });
        $entidades = \PulsoCivico\Services\SilIngestService::ENTIDADES;
        $jsonDir = $rootDir . '/storage/data/json';
        $completados = [];
        $pendientes = [];
        $totalLegisladores = 0;
        foreach ($entidades as $id => $nombre) {
            $f = "$jsonDir/legisladores_entidad_{$id}_leg_66.json";
            if (file_exists($f) && filesize($f) > 500) {
                $d = json_decode((string)file_get_contents($f), true);
                $dips = count($d['diputados'] ?? []);
                $sens = count($d['senadores'] ?? []);
                $tot = $dips + $sens;
                $totalLegisladores += $tot;
                $completados[$id] = [
                    'nombre' => $nombre,
                    'diputados' => $dips,
                    'senadores' => $sens,
                    'total' => $tot,
                ];
            } else {
                $pendientes[$id] = $nombre;
            }
        }
        $response['resumen'] = [
            'completados_count' => count($completados),
            'pendientes_count' => count($pendientes),
            'total_estados' => 32,
            'porcentaje' => round((count($completados) / 32) * 100, 1) . '%',
            'total_legisladores' => $totalLegisladores,
        ];
        $response['completados'] = $completados;
        $response['pendientes'] = $pendientes;
        break;

    case 'scrape:pendientes':
        spl_autoload_register(function (string $class) use ($rootDir): void {
            $prefix = 'PulsoCivico\\';
            $baseDir = $rootDir . '/src/';
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) return;
            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) require_once $file;
        });
        $config = require $rootDir . '/config/config.php';
        $logger = new \PulsoCivico\Core\Logger($config['paths']['logs'], 'ROUTER-SCRAPER');
        $httpClient = new \PulsoCivico\Core\HttpClient($config['sil'], $logger);
        $service = new \PulsoCivico\Services\SilIngestService($config, $httpClient, $logger);

        $entidades = \PulsoCivico\Services\SilIngestService::ENTIDADES;
        $jsonDir = $rootDir . '/storage/data/json';
        $pendientes = [];
        foreach ($entidades as $id => $nombre) {
            $f = "$jsonDir/legisladores_entidad_{$id}_leg_66.json";
            if (!file_exists($f) || filesize($f) < 500) {
                $pendientes[$id] = $nombre;
            } else {
                $d = json_decode((string)file_get_contents($f), true);
                if (empty($d['diputados']) || empty($d['senadores'])) {
                    $pendientes[$id] = $nombre;
                }
            }
        }

        $procesados = [];
        $errores = [];
        foreach ($pendientes as $pId => $pNombre) {
            echo "--> Iniciando [{$pId}/32] {$pNombre}...\n";
            try {
                $res = $service->ingestByEntidad($pId, 66, true);
                $procesados[$pId] = [
                    'nombre' => $pNombre,
                    'diputados' => count($res['diputados'] ?? []),
                    'senadores' => count($res['senadores'] ?? []),
                    'total' => $res['total'] ?? 0,
                ];
                echo "--> OK [{$pId}/32] {$pNombre}: " . ($res['total'] ?? 0) . " legisladores.\n";
                // Pausa sigilosa de 3 segundos entre estados
                sleep(3);
            } catch (\Throwable $e) {
                $errores[$pId] = $e->getMessage();
                echo "--> ERROR en [{$pId}/32] {$pNombre}: " . $e->getMessage() . "\n";
                sleep(5);
            }
        }

        $response['procesados'] = $procesados;
        $response['errores'] = $errores;
        $response['total_procesados'] = count($procesados);
        break;

    case 'consolidate:nacional':
        spl_autoload_register(function (string $class) use ($rootDir): void {
            $prefix = 'PulsoCivico\\';
            $baseDir = $rootDir . '/src/';
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) return;
            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) require_once $file;
        });
        $entidades = \PulsoCivico\Services\SilIngestService::ENTIDADES;
        $jsonDir = $rootDir . '/storage/data/json';
        $todosDiputados = [];
        $todosSenadores = [];
        $entidadesResumen = [];

        foreach ($entidades as $id => $nombre) {
            $f = "$jsonDir/legisladores_entidad_{$id}_leg_66.json";
            if (file_exists($f)) {
                $d = json_decode((string)file_get_contents($f), true);
                if (is_array($d)) {
                    $dips = $d['diputados'] ?? [];
                    $sens = $d['senadores'] ?? [];
                    foreach ($dips as $dip) {
                        $todosDiputados[] = $dip;
                    }
                    foreach ($sens as $sen) {
                        $todosSenadores[] = $sen;
                    }
                    $entidadesResumen[$id] = [
                        'nombre' => $nombre,
                        'diputados' => count($dips),
                        'senadores' => count($sens),
                        'total' => count($dips) + count($sens),
                    ];
                }
            }
        }

        $resumenNacional = [
            'titulo' => 'Directorio Consolidado Nacional - LXVI Legislatura',
            'alcance' => '32 Entidades Federativas (100% Cobertura Nacional)',
            'generado_en' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'entidades_procesadas' => count($entidadesResumen),
            'total_diputados' => count($todosDiputados),
            'total_senadores' => count($todosSenadores),
            'total_general' => count($todosDiputados) + count($todosSenadores),
            'entidades' => $entidadesResumen,
        ];

        $archivoConsolidado = "$jsonDir/congreso_union_leg_66.json";
        $dataConsolidada = [
            'metadata' => [
                'titulo' => 'Congreso de la Unión de México - LXVI Legislatura',
                'alcance' => 'Nacional (32 Entidades Federativas)',
                'generado_en' => $resumenNacional['generado_en'],
                'total_diputados' => count($todosDiputados),
                'total_senadores' => count($todosSenadores),
                'total_general' => count($todosDiputados) + count($todosSenadores),
            ],
            'diputados' => $todosDiputados,
            'senadores' => $todosSenadores,
        ];
        file_put_contents($archivoConsolidado, json_encode($dataConsolidada, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $archivoResumen = "$jsonDir/directorio_nacional_resumen.json";
        file_put_contents($archivoResumen, json_encode($resumenNacional, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $response['resumen_nacional'] = $resumenNacional;
        $response['archivo_consolidado'] = $archivoConsolidado;
        $response['archivo_resumen'] = $archivoResumen;
        break;

    case 'help':
    default:
        $response['message'] = "CLI Gateway Pulso Civico";
        $response['commands'] = [
            'lint <file1,file2...>'  => 'Valida sintaxis PHP de uno o varios archivos',
            'lint:dir [dir]'         => 'Valida sintaxis PHP de todo un directorio recursivamente',
            'git:status'             => 'Estado limpio y estructurado de git',
            'git:diff'               => 'Resumen de cambios de git',
            'health'                 => 'Chequeo completo de salud del proyecto (sintaxis y carpetas)',
            'search <term> [dir]'    => 'Búsqueda de código interna',
            'scrape <fuente> [json]' => 'Ejecuta ingesta de datos por fuente (ej: sil)',
        ];
        break;
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
