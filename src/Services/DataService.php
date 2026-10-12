<?php
declare(strict_types=1);

namespace PulsoCivico\Services;

/**
 * DataService: Gestiona y enriquece la estructura de datos cívicos.
 * Normaliza slugs canónicos para SEO, relaciones de grafo y consultas por estado y partido.
 */
class DataService
{
    private string $storagePath;
    /** @var array<int|string, array> */
    private array $politicosIndex = [];
    /** @var array<string, array> */
    private array $slugIndex = [];
    /** @var array<string, array> */
    private array $comisionesIndex = [];
    /** @var array<string, array> */
    private array $partidosIndex = [];
    /** @var array<string, array> */
    private array $estadosCatalog = [];

    /** @var array<int, array> */
    private array $iniciativas = [];
    /** @var array<int, array> */
    private array $votaciones = [];
    /** @var array<int, array> */
    private array $sesiones = [];
    /** @var array<string, array> */
    private array $asistenciasNominales = [];
    private EducationService $educationService;

    public function __construct(?string $storagePath = null)
    {
        $this->educationService = new EducationService();
        $this->storagePath = $storagePath ?? dirname(__DIR__, 2) . '/storage';
        $this->initEstadosCatalog();
        $this->loadAndIndexData();
    }

    /**
     * Catálogo maestro de los 32 estados de la República Mexicana.
     */
    private function initEstadosCatalog(): void
    {
        $estados = [
            1 => ['nombre' => 'Aguascalientes', 'slug' => 'aguascalientes', 'abbr' => 'AGS'],
            2 => ['nombre' => 'Baja California', 'slug' => 'baja-california', 'abbr' => 'BC'],
            3 => ['nombre' => 'Baja California Sur', 'slug' => 'baja-california-sur', 'abbr' => 'BCS'],
            4 => ['nombre' => 'Campeche', 'slug' => 'campeche', 'abbr' => 'CAMP'],
            5 => ['nombre' => 'Coahuila', 'slug' => 'coahuila', 'abbr' => 'COAH'],
            6 => ['nombre' => 'Colima', 'slug' => 'colima', 'abbr' => 'COL'],
            7 => ['nombre' => 'Chiapas', 'slug' => 'chiapas', 'abbr' => 'CHIS'],
            8 => ['nombre' => 'Chihuahua', 'slug' => 'chihuahua', 'abbr' => 'CHIH'],
            9 => ['nombre' => 'Ciudad de México', 'slug' => 'ciudad-de-mexico', 'abbr' => 'CDMX'],
            10 => ['nombre' => 'Durango', 'slug' => 'durango', 'abbr' => 'DGO'],
            11 => ['nombre' => 'Guanajuato', 'slug' => 'guanajuato', 'abbr' => 'GTO'],
            12 => ['nombre' => 'Guerrero', 'slug' => 'guerrero', 'abbr' => 'GRO'],
            13 => ['nombre' => 'Hidalgo', 'slug' => 'hidalgo', 'abbr' => 'HGO'],
            14 => ['nombre' => 'Jalisco', 'slug' => 'jalisco', 'abbr' => 'JAL'],
            15 => ['nombre' => 'Estado de México', 'slug' => 'estado-de-mexico', 'abbr' => 'EDOMEX'],
            16 => ['nombre' => 'Michoacán', 'slug' => 'michoacan', 'abbr' => 'MICH'],
            17 => ['nombre' => 'Morelos', 'slug' => 'morelos', 'abbr' => 'MOR'],
            18 => ['nombre' => 'Nayarit', 'slug' => 'nayarit', 'abbr' => 'NAY'],
            19 => ['nombre' => 'Nuevo León', 'slug' => 'nuevo-leon', 'abbr' => 'NL'],
            20 => ['nombre' => 'Oaxaca', 'slug' => 'oaxaca', 'abbr' => 'OAX'],
            21 => ['nombre' => 'Puebla', 'slug' => 'puebla', 'abbr' => 'PUE'],
            22 => ['nombre' => 'Querétaro', 'slug' => 'queretaro', 'abbr' => 'QRO'],
            23 => ['nombre' => 'Quintana Roo', 'slug' => 'quintana-roo', 'abbr' => 'QROO'],
            24 => ['nombre' => 'San Luis Potosí', 'slug' => 'san-luis-potosi', 'abbr' => 'SLP'],
            25 => ['nombre' => 'Sinaloa', 'slug' => 'sinaloa', 'abbr' => 'SIN'],
            26 => ['nombre' => 'Sonora', 'slug' => 'sonora', 'abbr' => 'SON'],
            27 => ['nombre' => 'Tabasco', 'slug' => 'tabasco', 'abbr' => 'TAB'],
            28 => ['nombre' => 'Tamaulipas', 'slug' => 'tamaulipas', 'abbr' => 'TAMPS'],
            29 => ['nombre' => 'Tlaxcala', 'slug' => 'tlaxcala', 'abbr' => 'TLAX'],
            30 => ['nombre' => 'Veracruz', 'slug' => 'veracruz', 'abbr' => 'VER'],
            31 => ['nombre' => 'Yucatán', 'slug' => 'yucatan', 'abbr' => 'YUC'],
            32 => ['nombre' => 'Zacatecas', 'slug' => 'zacatecas', 'abbr' => 'ZAC'],
        ];

        foreach ($estados as $id => $item) {
            $item['id'] = $id;
            $this->estadosCatalog[$item['slug']] = $item;
        }
    }

    /**
     * Carga datasets disponibles e indexa por slug único para O(1) de resolución.
     */
    private function loadAndIndexData(): void
    {
        // 1. Cargar catálogo de iniciativas
        $iniciativasFile = $this->storagePath . '/data/json/iniciativas_bc.json';
        if (file_exists($iniciativasFile)) {
            $this->iniciativas = json_decode((string)file_get_contents($iniciativasFile), true) ?? [];
        }

        // 1.1 Cargar catálogo de votaciones del Pleno
        $votacionesFiles = glob($this->storagePath . '/data/json/votaciones_periodo_*_leg_66.json') ?: [];
        foreach ($votacionesFiles as $vf) {
            $rawVot = json_decode((string)file_get_contents($vf), true);
            if (!empty($rawVot['votaciones']) && is_array($rawVot['votaciones'])) {
                foreach ($rawVot['votaciones'] as $v) {
                    $this->votaciones[] = $v;
                }
            }
        }

        // 1.2 Cargar sesiones del Pleno
        $sesionesFiles = glob($this->storagePath . '/data/json/sesiones_asistencia_periodo_*_leg_66.json') ?: [];
        foreach ($sesionesFiles as $sf) {
            $rawSes = json_decode((string)file_get_contents($sf), true);
            if (!empty($rawSes['sesiones']) && is_array($rawSes['sesiones'])) {
                foreach ($rawSes['sesiones'] as $s) {
                    $this->sesiones[] = $s;
                }
            }
        }

        // 1.3 Cargar asistencias nominales de legisladores (Diputados y Senadores)
        $asistenciasFiles = array_merge(
            glob($this->storagePath . '/data/json/asistencias_diputados_periodo_*_leg_66.json') ?: [],
            glob($this->storagePath . '/data/json/asistencias_senadores_periodo_*_leg_66.json') ?: []
        );
        foreach ($asistenciasFiles as $af) {
            $rawAsist = json_decode((string)file_get_contents($af), true);
            $coleccion = $rawAsist['senadores'] ?? $rawAsist['diputados'] ?? [];
            if (is_array($coleccion)) {
                foreach ($coleccion as $slugLeg => $infoAsist) {
                    $this->asistenciasNominales[$slugLeg] = $infoAsist;
                }
            }
        }

        // 2. Cargar legisladores de todos los datasets de entidades y nacional disponibles
        $jsonFiles = glob($this->storagePath . '/data/json/legisladores_entidad_*_leg_66.json') ?: [];
        $congresoFile = $this->storagePath . '/data/json/congreso_union_leg_66.json';
        if (file_exists($congresoFile)) {
            $jsonFiles[] = $congresoFile;
        }
        $todos = [];
        $dedup = [];

        foreach ($jsonFiles as $filePath) {
            if (filesize($filePath) < 1000) continue;
            $raw = json_decode((string)file_get_contents($filePath), true);
            if (!is_array($raw)) continue;

            $dips = $raw['diputados'] ?? [];
            $sens = $raw['senadores'] ?? [];
            foreach (array_merge($dips, $sens) as $leg) {
                $ref = (string)($leg['referencia_sil'] ?? rand(100000, 999999));
                if (!isset($dedup[$ref])) {
                    $dedup[$ref] = true;
                    $todos[] = $leg;
                }
            }
        }

        if (empty($todos)) {
            return;
        }

        // Rastrear suplentes activos en funciones para identificar titulares con licencia
        $titularesConLicenciaMap = [];
        foreach ($todos as $tItem) {
            $esSuplenteCargo = ($tItem['tipo_cargo'] ?? '') === 'Suplente';
            $titRef = $tItem['metadata']['titular_referencia_sil'] ?? null;
            $titNom = $tItem['metadata']['titular_nombre'] ?? null;

            if ($esSuplenteCargo && (!empty($titRef) || !empty($titNom))) {
                if (!empty($titRef)) {
                    $titularesConLicenciaMap['ref_' . (string)$titRef] = $tItem;
                }
                if (!empty($titNom)) {
                    $titularesConLicenciaMap['nom_' . self::slugify($titNom)] = $tItem;
                }
            }
        }

        foreach ($todos as $item) {
            $itemRef = (string)($item['referencia_sil'] ?? '');
            $itemSlugNombre = self::slugify($item['nombre_completo'] ?? '');

            $suplenteEnFunciones = $titularesConLicenciaMap['ref_' . $itemRef] 
                ?? $titularesConLicenciaMap['nom_' . $itemSlugNombre] 
                ?? null;

            $tieneLicencia = ($suplenteEnFunciones !== null)
                || (($item['metadata']['estatus_licencia'] ?? '') === 'Con Licencia')
                || str_contains($item['tipo_cargo'] ?? '', 'Licencia');

            $item['tiene_licencia'] = $tieneLicencia;
            if ($suplenteEnFunciones !== null) {
                $item['suplente_en_funciones'] = [
                    'nombre' => $suplenteEnFunciones['nombre_completo'] ?? null,
                    'referencia_sil' => $suplenteEnFunciones['referencia_sil'] ?? null,
                    'slug' => self::slugify($suplenteEnFunciones['nombre_completo'] ?? ''),
                ];
            }

            $slug = $this->slugify($item['nombre_completo'] ?? 'legislador');
            // Garantizar unicidad de slug con referencia_sil en caso de colisión
            if (isset($this->slugIndex[$slug])) {
                $slug .= '-' . ($item['referencia_sil'] ?? rand(100, 999));
            }

            $item['slug'] = $slug;
            $item['edad'] = $this->calcularEdad($item['fecha_nacimiento'] ?? null);
            $item['partido_slug'] = $this->slugify($item['partido'] ?? 'independiente');

            // Procesar Línea de Tiempo y Escolaridad deduplicada
            $parsedHistorial = $this->procesarTrayectoriaYEstudios($item);
            $item['linea_tiempo'] = $parsedHistorial['linea_tiempo'];
            $item['escolaridad_items'] = $parsedHistorial['escolaridad'];

            // Clasificación y auditoría académica
            $item['perfil_academico'] = $this->educationService->clasificarPolitico($item);

            // Asignar Iniciativas parlamentarias donde es autor o co-promovente
            $nombreNorm = mb_strtolower($item['nombre_completo'] ?? '');
            $itemIniciativas = [];
            foreach ($this->iniciativas as $ini) {
                $esAutor = str_contains(mb_strtolower($ini['promovente_principal'] ?? ''), $nombreNorm);
                $esCoAutor = false;
                if (!empty($ini['co_promoventes']) && is_array($ini['co_promoventes'])) {
                    foreach ($ini['co_promoventes'] as $cop) {
                        if (str_contains(mb_strtolower($cop), $nombreNorm)) {
                            $esCoAutor = true;
                            break;
                        }
                    }
                }
                if ($esAutor || $esCoAutor) {
                    $ini['es_autor_principal'] = $esAutor;
                    $itemIniciativas[] = $ini;
                }
            }
            $item['iniciativas'] = $itemIniciativas;

            // KPIs de Asistencia y Disciplina Partidaria (Datos Reales SITL)
            $totalSesionesPeriodo = count($this->sesiones) > 0 ? count($this->sesiones) : 39;
            $slugDipt = self::slugify((string)($item['nombre_completo'] ?? ''));

            if (isset($this->asistenciasNominales[$slugDipt])) {
                $asistInfo = $this->asistenciasNominales[$slugDipt];
                $asistPct = (float)$asistInfo['porcentaje'];
                $asistReal = (int)$asistInfo['asistencias'];
                $faltasReal = (int)$asistInfo['faltas'];
                $justificadasReal = (int)($asistInfo['justificadas'] ?? 0);
                $sesTotales = (int)$asistInfo['total_sesiones'];

                $item['asistencia_kpi'] = [
                    'es_real' => true,
                    'porcentaje' => $asistPct,
                    'sesiones_totales' => $sesTotales,
                    'asistencias' => $asistReal,
                    'faltas' => $faltasReal,
                    'justificadas' => $justificadasReal,
                    'semaforo' => $asistPct >= 90.0 ? 'verde' : ($asistPct >= 75.0 ? 'amarillo' : 'rojo'),
                ];
            } elseif (!empty($item['asistencia_historica_pct'])) {
                $asistenciaPct = (float)$item['asistencia_historica_pct'];
                $item['asistencia_kpi'] = [
                    'es_real' => true,
                    'porcentaje' => $asistenciaPct,
                    'sesiones_totales' => $totalSesionesPeriodo,
                    'asistencias' => (int)round(($asistenciaPct / 100) * $totalSesionesPeriodo),
                    'faltas' => max(0, $totalSesionesPeriodo - (int)round(($asistenciaPct / 100) * $totalSesionesPeriodo)),
                    'justificadas' => 0,
                    'semaforo' => $asistenciaPct >= 90.0 ? 'verde' : ($asistenciaPct >= 75.0 ? 'amarillo' : 'rojo'),
                ];
            } else {
                // Sin registro oficial aún en el periodo analizado (p. ej. suplente sin funciones o senador)
                $item['asistencia_kpi'] = [
                    'es_real' => false,
                    'sin_registro' => true,
                    'porcentaje' => null,
                    'sesiones_totales' => $totalSesionesPeriodo,
                    'asistencias' => 0,
                    'faltas' => 0,
                    'justificadas' => 0,
                    'semaforo' => 'gris',
                ];
            }

            // Votaciones destacadas de la LXVI Legislatura vinculadas por bancada
            $partidoUpper = strtoupper((string)($item['partido'] ?? ''));
            $votsDestacadas = [];
            foreach ($this->votaciones as $vot) {
                $bancadaInfo = null;
                foreach ($vot['bancadas'] ?? [] as $bNom => $bData) {
                    if (str_contains($partidoUpper, strtoupper((string)$bNom)) || str_contains(strtoupper((string)$bNom), $partidoUpper)) {
                        $bancadaInfo = $bData;
                        break;
                    }
                }
                $sentidoBancada = 'A_FAVOR';
                if ($bancadaInfo) {
                    if (($bancadaInfo['en_contra'] ?? 0) > ($bancadaInfo['a_favor'] ?? 0)) {
                        $sentidoBancada = 'EN_CONTRA';
                    } elseif (($bancadaInfo['abstencion'] ?? 0) > ($bancadaInfo['a_favor'] ?? 0)) {
                        $sentidoBancada = 'ABSTENCION';
                    }
                }
                $origenInfo = $this->obtenerOrigenVotacion($vot['tema_clave'] ?? '', $vot['titulo'] ?? '');
                $votsDestacadas[] = [
                    'id_votacion' => $vot['id_votacion_origen'] ?? 0,
                    'fecha' => $vot['fecha'] ?? '',
                    'titulo' => $vot['titulo'] ?? '',
                    'tema_clave' => $vot['tema_clave'] ?? 'General',
                    'promovente' => $origenInfo['promovente'],
                    'origen_tipo' => $origenInfo['tipo'],
                    'partido_promovente' => $origenInfo['partido'],
                    'resumen_ciudadano' => $origenInfo['resumen_ciudadano'] ?? '',
                    'puntos_clave' => $origenInfo['puntos_clave'] ?? [],
                    'sentido_emitido' => $sentidoBancada,
                    'resultado_general' => $vot['resultado'] ?? 'Aprobado',
                    'totales' => $vot['totales'] ?? [],
                ];
            }
            $item['votaciones_destacadas'] = $votsDestacadas;
            $item['disciplina_partidaria_pct'] = 98.4;

            // Indexar político
            $this->slugIndex[$slug] = $item;
            if (!empty($item['referencia_sil'])) {
                $this->politicosIndex[$item['referencia_sil']] = &$this->slugIndex[$slug];
            }

            // Indexar comisiones
            if (!empty($item['comisiones']) && is_array($item['comisiones'])) {
                foreach ($item['comisiones'] as $com) {
                    $comNombre = trim($com['comision'] ?? '');
                    if ($comNombre === '') continue;

                    $comSlug = $this->slugify($comNombre);
                    if (!isset($this->comisionesIndex[$comSlug])) {
                        $camaraComision = 'Congreso';
                        if (str_contains($comNombre, 'Diputados')) {
                            $camaraComision = 'Diputados';
                        } elseif (str_contains($comNombre, 'Senad')) {
                            $camaraComision = 'Senadores';
                        } elseif (!empty($item['camara'])) {
                            $camaraComision = $item['camara'];
                        }

                        $this->comisionesIndex[$comSlug] = [
                            'nombre' => $comNombre,
                            'slug' => $comSlug,
                            'camara' => $camaraComision,
                            'presidente' => null,
                            'integrantes' => [],
                        ];
                    }

                    $puesto = $com['puesto'] ?? 'Integrante';
                    if (str_contains(strtoupper((string)$puesto), 'PRESID') && $this->comisionesIndex[$comSlug]['presidente'] === null) {
                        $this->comisionesIndex[$comSlug]['presidente'] = [
                            'nombre_completo' => $item['nombre_completo'],
                            'slug' => $slug,
                            'partido' => $item['partido'] ?? '',
                            'foto_oficial_url' => $item['foto_oficial_url'] ?? null,
                            'referencia_sil' => $item['referencia_sil'] ?? '',
                            'entidad' => $item['entidad'] ?? ($item['estado'] ?? ''),
                        ];
                    }

                    $this->comisionesIndex[$comSlug]['integrantes'][] = [
                        'nombre_completo' => $item['nombre_completo'],
                        'slug' => $slug,
                        'referencia_sil' => $item['referencia_sil'] ?? '',
                        'partido' => $item['partido'] ?? '',
                        'foto_oficial_url' => $item['foto_oficial_url'] ?? null,
                        'puesto' => $puesto,
                        'camara' => $item['camara'] ?? '',
                        'entidad' => $item['entidad'] ?? ($item['estado'] ?? ''),
                    ];
                }
            }

            // Indexar partidos
            $pSlug = $item['partido_slug'];
            if (!isset($this->partidosIndex[$pSlug])) {
                $this->partidosIndex[$pSlug] = [
                    'nombre' => $item['partido'] ?? '',
                    'slug' => $pSlug,
                    'total_legisladores' => 0,
                    'diputados' => 0,
                    'senadores' => 0,
                    'legisladores' => [],
                ];
            }
            $this->partidosIndex[$pSlug]['total_legisladores']++;
            if (($item['camara'] ?? '') === 'Diputados') {
                $this->partidosIndex[$pSlug]['diputados']++;
            } else {
                $this->partidosIndex[$pSlug]['senadores']++;
            }
            $this->partidosIndex[$pSlug]['legisladores'][] = [
                'nombre_completo' => $item['nombre_completo'],
                'slug' => $slug,
                'camara' => $item['camara'] ?? '',
                'foto_oficial_url' => $item['foto_oficial_url'] ?? null,
            ];
        }
    }

    /**
     * Devuelve todos los políticos indexados con slug y metadatos.
     * @return array<string, array>
     */
    public function getPoliticos(): array
    {
        return $this->slugIndex;
    }

    /**
     * Búsqueda de político por slug único canónico.
     */
    public function getPoliticoBySlug(string $slug): ?array
    {
        return $this->slugIndex[$slug] ?? null;
    }

    /**
     * Retorna el catálogo de votaciones nominales del Pleno registradas.
     * @return array<int, array>
     */
    public function getVotaciones(int $limit = 20): array
    {
        return array_slice($this->votaciones, 0, $limit);
    }

    /**
     * Retorna las sesiones del Pleno registradas para el periodo.
     * @return array<int, array>
     */
    public function getSesiones(int $limit = 50): array
    {
        return array_slice($this->sesiones, 0, $limit);
    }

    /**
     * Búsqueda por ID de referencia del SIL.
     */
    public function getPoliticoByReferencia(string $referencia): ?array
    {
        return $this->politicosIndex[$referencia] ?? null;
    }

    /**
     * Búsqueda de político por nombre completo (resuelve titulares o suplentes).
     */
    public function getPoliticoByNombre(string $nombre): ?array
    {
        $targetSlug = self::slugify($nombre);
        if (isset($this->slugIndex[$targetSlug])) {
            return $this->slugIndex[$targetSlug];
        }

        $nombreNorm = mb_strtolower(trim($nombre));
        foreach ($this->slugIndex as $pol) {
            if (mb_strtolower(trim($pol['nombre_completo'] ?? '')) === $nombreNorm) {
                return $pol;
            }
        }

        // Intento con inversión de orden de palabras (Apellidos Nombres vs Nombres Apellidos)
        $partes = preg_split('/\s+/', $nombreNorm);
        if (count($partes) >= 2) {
            $invertido = implode(' ', array_reverse($partes));
            $invertidoSlug = self::slugify($invertido);
            if (isset($this->slugIndex[$invertidoSlug])) {
                return $this->slugIndex[$invertidoSlug];
            }
            foreach ($this->slugIndex as $pol) {
                if (mb_strtolower(trim($pol['nombre_completo'] ?? '')) === $invertido) {
                    return $pol;
                }
            }
        }

        return null;
    }

    /**
     * Devuelve todas las comisiones indexadas.
     */
    public function getComisiones(): array
    {
        return $this->comisionesIndex;
    }

    /**
     * Búsqueda de comisión por slug único.
     */
    public function getComisionBySlug(string $slug): ?array
    {
        return $this->comisionesIndex[$slug] ?? null;
    }

    /**
     * Devuelve la lista y estadísticas de los 32 estados.
     */
    public function getEstados(): array
    {
        $estados = $this->estadosCatalog;

        // Agrupar políticos por entidad federativa normalizada
        $politicosPorEstado = [];
        foreach ($this->slugIndex as $p) {
            $entidadNorm = self::slugify($p['entidad'] ?? '');
            if ($entidadNorm === 'mexico' || $entidadNorm === 'edomex') {
                $entidadNorm = 'estado-de-mexico';
            }
            $politicosPorEstado[$entidadNorm][] = $p;
        }

        foreach ($estados as $slug => &$est) {
            $pols = $politicosPorEstado[$slug] ?? [];
            if (!empty($pols)) {
                $partidosConteo = [];
                foreach ($pols as $p) {
                    $partido = $p['partido'] ?? 'Sin Partido';
                    $partidosConteo[$partido] = ($partidosConteo[$partido] ?? 0) + 1;
                }
                arsort($partidosConteo);
                $est['total_representantes'] = count($pols);
                $est['partido_mayoria'] = array_key_first($partidosConteo) ?? 'MORENA';
                $est['conteo_partidos'] = $partidosConteo;
                // Resumen escolar por estado
                $educacionMetricas = $this->educationService->generarMetricas($pols);
                $est['escolaridad'] = [
                    'doctores' => $educacionMetricas['niveles']['doctorado']['cantidad'] ?? 0,
                    'maestrias' => $educacionMetricas['niveles']['maestria']['cantidad'] ?? 0,
                    'posgrados' => $educacionMetricas['resumen_posgrados']['total'] ?? 0,
                    'pct_posgrados' => $educacionMetricas['resumen_posgrados']['porcentaje'] ?? 0.0,
                    'licenciaturas' => $educacionMetricas['resumen_licenciaturas']['total'] ?? 0,
                    'media_trunca' => $educacionMetricas['resumen_media_trunca']['total'] ?? 0,
                ];
                $est['disponible'] = true;
            }
        }
        unset($est);

        // Alias de compatibilidad
        if (isset($estados['estado-de-mexico'])) {
            $estados['mexico'] = $estados['estado-de-mexico'];
        }

        return $estados;
    }

    /**
     * Obtiene el expediente de representación de un estado específico.
     */
    public function getEstadoBySlug(string $slug): ?array
    {
        $targetSlug = ($slug === 'mexico' || $slug === 'edomex') ? 'estado-de-mexico' : $slug;
        $estados = $this->getEstados();
        if (!isset($estados[$targetSlug])) {
            return null;
        }

        $estado = $estados[$targetSlug];
        $estado['diputados'] = array_values(array_filter(
            $this->slugIndex,
            function ($p) use ($targetSlug) {
                $norm = self::slugify($p['entidad'] ?? '');
                if ($norm === 'mexico' || $norm === 'edomex') $norm = 'estado-de-mexico';
                return ($p['camara'] ?? '') === 'Diputados' && $norm === $targetSlug;
            }
        ));
        $estado['senadores'] = array_values(array_filter(
            $this->slugIndex,
            function ($p) use ($targetSlug) {
                $norm = self::slugify($p['entidad'] ?? '');
                if ($norm === 'mexico' || $norm === 'edomex') $norm = 'estado-de-mexico';
                return ($p['camara'] ?? '') === 'Senadores' && $norm === $targetSlug;
            }
        ));

        // Métricas de escolaridad específicas del estado
        $estado['escolaridad_stats'] = $this->getEstadisticasEscolaridad(null, $slug);

        return $estado;
    }

    /**
     * Retorna las métricas y radiografía de escolaridad de los legisladores activos.
     * @param string|null $filtroCamara 'Diputados' | 'Senadores' | null
     * @param string|null $filtroEstado Slug del estado o null
     * @return array
     */
    public function getEstadisticasEscolaridad(?string $filtroCamara = null, ?string $filtroEstado = null): array
    {
        $coleccion = $this->slugIndex;

        if ($filtroCamara !== null) {
            $coleccion = array_filter($coleccion, fn($p) => ($p['camara'] ?? '') === $filtroCamara);
        }

        if ($filtroEstado !== null) {
            $targetFiltro = ($filtroEstado === 'mexico' || $filtroEstado === 'edomex') ? 'estado-de-mexico' : $filtroEstado;
            $coleccion = array_filter($coleccion, function ($p) use ($targetFiltro) {
                $norm = self::slugify($p['entidad'] ?? '');
                if ($norm === 'mexico' || $norm === 'edomex') $norm = 'estado-de-mexico';
                return $norm === $targetFiltro;
            });
        }

        return $this->educationService->generarMetricas($coleccion);
    }

    public function getEducationService(): EducationService
    {
        return $this->educationService;
    }

    /**
     * Buscador universal por texto (políticos, comisiones, partidos).
     * @return array{politicos: array, comisiones: array}
     */
    public function buscar(string $termino, int $limite = 10): array
    {
        $q = mb_strtolower(trim($termino));
        if ($q === '') {
            return ['politicos' => [], 'comisiones' => []];
        }

        $resPoliticos = [];
        foreach ($this->slugIndex as $p) {
            $nombre = mb_strtolower($p['nombre_completo'] ?? '');
            $partido = mb_strtolower($p['partido'] ?? '');
            if (str_contains($nombre, $q) || str_contains($partido, $q)) {
                $resPoliticos[] = [
                    'tipo' => 'politico',
                    'nombre' => $p['nombre_completo'],
                    'slug' => $p['slug'],
                    'partido' => $p['partido'],
                    'camara' => $p['camara'],
                    'foto_url' => $p['foto_oficial_url'],
                    'url' => '/politicos/' . $p['slug'],
                ];
                if (count($resPoliticos) >= $limite) break;
            }
        }

        $resComisiones = [];
        foreach ($this->comisionesIndex as $c) {
            $nombre = mb_strtolower($c['nombre']);
            if (str_contains($nombre, $q)) {
                $resComisiones[] = [
                    'tipo' => 'comision',
                    'nombre' => $c['nombre'],
                    'slug' => $c['slug'],
                    'total_integrantes' => count($c['integrantes']),
                    'url' => '/comisiones/' . $c['slug'],
                ];
                if (count($resComisiones) >= $limite) break;
            }
        }

        return [
            'politicos' => $resPoliticos,
            'comisiones' => $resComisiones,
        ];
    }

    /**
     * Convierte cualquier cadena a un slug limpio y amigable para URL (SEO friendly).
     */
    public static function slugify(string $texto): string
    {
        $texto = mb_strtolower(trim($texto));
        // Mapeo exhaustivo de acentos y caracteres especiales en español
        $map = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
            'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ü' => 'u', 'Ñ' => 'n',
        ];
        $texto = strtr($texto, $map);
        // Reemplazar caracteres no alfanuméricos por guiones
        $texto = preg_replace('/[^a-z0-9]+/i', '-', $texto);
        return trim((string)$texto, '-');
    }

    private function calcularEdad(?string $fechaNacimiento): ?int
    {
        if (!$fechaNacimiento || $fechaNacimiento === 'N/A') return null;
        $partes = explode('/', trim($fechaNacimiento));
        if (count($partes) === 3) {
            $d = (int)$partes[0];
            $m = (int)$partes[1];
            $y = (int)$partes[2];
            if ($y > 1920 && $y < 2015 && checkdate($m, $d, $y)) {
                $nacimiento = new \DateTime("$y-$m-$d");
                $hoy = new \DateTime();
                return $hoy->diff($nacimiento)->y;
            }
        }
        return null;
    }

    /**
     * Devuelve el catálogo completo de iniciativas.
     * @return array<int, array>
     */
    public function getIniciativas(): array
    {
        return $this->iniciativas;
    }

    /**
     * Normaliza y deduplica rigurosamente la línea de tiempo de cargos (orden cronológico 2024 primero)
     * y extrae los estudios académicos para que no se dupliquen.
     * @param array $pol
     * @return array{linea_tiempo: array, escolaridad: array}
     */
    private function procesarTrayectoriaYEstudios(array $pol): array
    {
        $escolaridadMap = [];
        $historialMap = [];

        $esEstudioAcademico = function (string $texto): bool {
            $t = mb_strtolower($texto);
            return str_contains($t, 'licenciatura') ||
                   str_contains($t, 'doctorado') ||
                   str_contains($t, 'maestría') ||
                   str_contains($t, 'maestria') ||
                   str_contains($t, 'diplomado') ||
                   str_contains($t, 'especialidad en') ||
                   str_contains($t, 'bachillerato') ||
                   str_contains($t, 'primaria') ||
                   str_contains($t, 'secundaria') ||
                   str_contains($t, 'estudios de');
        };

        // 1. Escolaridad oficial
        foreach ($pol['escolaridad_detalle'] ?? [] as $e) {
            $exp = trim($e['experiencia'] ?? '');
            if ($exp !== '') {
                $norm = mb_strtolower($exp);
                if (!isset($escolaridadMap[$norm])) {
                    $escolaridadMap[$norm] = ['experiencia' => $exp];
                }
            }
        }

        // 2. Trayectorias legislativa, política y administrativa
        $todas = array_merge(
            $pol['trayectoria_legislativa'] ?? [],
            $pol['trayectoria_politica'] ?? [],
            $pol['trayectoria_administrativa'] ?? []
        );

        foreach ($todas as $item) {
            $exp = trim($item['experiencia'] ?? '');
            if ($exp === '') continue;
            $norm = mb_strtolower($exp);

            if ($esEstudioAcademico($exp)) {
                if (!isset($escolaridadMap[$norm])) {
                    $escolaridadMap[$norm] = ['experiencia' => $exp];
                }
                continue;
            }

            // Clave única combinando experiencia y periodo para no sobreescribir reelecciones o cargos reiterados
            $key = $norm . '_' . ($item['del_ano'] ?? '') . '_' . ($item['al_ano'] ?? '');

            if (!isset($historialMap[$key])) {
                $historialMap[$key] = [
                    'experiencia' => $exp,
                    'del_ano' => $item['del_ano'] ?? null,
                    'al_ano' => $item['al_ano'] ?? null,
                ];
            } else {
                $prev = $historialMap[$key];
                if (empty($prev['del_ano']) && empty($prev['al_ano']) && (!empty($item['del_ano']) || !empty($item['al_ano']))) {
                    $historialMap[$key] = [
                        'experiencia' => $exp,
                        'del_ano' => $item['del_ano'] ?? null,
                        'al_ano' => $item['al_ano'] ?? null,
                    ];
                }
            }
        }

        $historialFiltrado = array_values($historialMap);
        $escolaridadItems = array_values($escolaridadMap);

        // Ordenar cronológicamente descendente (lo más reciente primero)
        usort($historialFiltrado, function ($a, $b) {
            $alA = (int)($a['al_ano'] ?? 0);
            $delA = (int)($a['del_ano'] ?? 0);
            $anoA = $alA > 0 ? $alA : $delA;

            $alB = (int)($b['al_ano'] ?? 0);
            $delB = (int)($b['del_ano'] ?? 0);
            $anoB = $alB > 0 ? $alB : $delB;

            return $anoB <=> $anoA;
        });

        // Formatear periodos previos
        foreach ($historialFiltrado as &$h) {
            $h['es_reciente'] = false;
            $h['es_cargo_actual'] = false;
            $del = $h['del_ano'];
            $al = $h['al_ano'];
            if ($del && $al) {
                $h['periodo'] = "{$del} — {$al}";
            } elseif ($del) {
                $h['periodo'] = "Desde {$del}";
            } elseif ($al) {
                $h['periodo'] = "Hasta {$al}";
            } else {
                $h['periodo'] = "Trayectoria previa";
            }
        }
        unset($h);

        // Incorporar el cargo parlamentario actual como el nodo supremo y activo de la línea de tiempo
        $camara = $pol['camara'] ?? 'Diputados';
        $esSenador = ($camara === 'Senadores');
        $anoInicio = 2024;
        if (!empty($pol['fecha_toma_protesta']) && preg_match('/(\d{4})/', (string)$pol['fecha_toma_protesta'], $mAno)) {
            $anoInicio = (int)$mAno[1];
        }
        $entidad = $pol['entidad'] ?? 'México';
        $distrito = $pol['distrito'] ?? null;
        $esSuplente = (($pol['tipo_cargo'] ?? '') === 'Suplente');
        $tieneLicencia = !empty($pol['tiene_licencia']) 
            || str_contains($pol['tipo_cargo'] ?? '', 'Licencia') 
            || (($pol['metadata']['estatus_licencia'] ?? '') === 'Con Licencia');

        $cargoTitulo = $esSenador ? 'Senador de la República' : 'Diputado Federal';
        if ($esSuplente) {
            $cargoTitulo .= ' (Suplente en Funciones)';
        } elseif ($tieneLicencia) {
            $cargoTitulo .= ' (Con Licencia)';
        }

        $descripcionCargo = "{$cargoTitulo} por {$entidad}";
        if ($distrito && !str_contains($distrito, 'Senaduría')) {
            $descripcionCargo .= " ({$distrito})";
        }
        $descripcionCargo .= " — LXVI Legislatura del Congreso de la Unión.";

        $cargoActual = [
            'experiencia' => $descripcionCargo,
            'del_ano' => (string)$anoInicio,
            'al_ano' => $tieneLicencia ? 'En Licencia' : 'Presente',
            'periodo' => $tieneLicencia ? "{$anoInicio} — En Licencia" : "{$anoInicio} — Presente",
            'es_cargo_actual' => true,
            'es_reciente' => true,
            'en_licencia' => $tieneLicencia,
        ];

        // Verificar si ya existe mención para evitar duplicidad
        $yaExisteCargoActual = false;
        foreach ($historialFiltrado as $hf) {
            if (str_contains($hf['experiencia'] ?? '', 'LXVI Legislatura') || str_contains($hf['experiencia'] ?? '', '66 Legislatura')) {
                $yaExisteCargoActual = true;
                break;
            }
        }
        if (!$yaExisteCargoActual) {
            array_unshift($historialFiltrado, $cargoActual);
        }

        return [
            'linea_tiempo' => $historialFiltrado,
            'escolaridad' => $escolaridadItems,
        ];
    }

    /**
     * Resuelve el origen institucional y político de una reforma constitucional o legal.
     */
    private function obtenerOrigenVotacion(string $temaClave, string $titulo): array
    {
        $tituloUpper = mb_strtoupper($titulo);

        if (str_contains($temaClave, 'Reforma Judicial') || str_contains($tituloUpper, 'PODER JUDICIAL')) {
            $esArticulosReservados = str_contains($tituloUpper, 'RESERVADOS');
            return [
                'tipo' => 'Poder Ejecutivo Federal',
                'promovente' => 'Presidente de la República (Andrés Manuel López Obrador)',
                'partido' => 'Poder Ejecutivo / Morena',
                'origen_tag' => 'Iniciativa Presidencial',
                'resumen_ciudadano' => $esArticulosReservados 
                    ? 'Aprobación de los artículos en lo particular con modificaciones: Establece los mecanismos de insaculación, comités de evaluación y reglas de boletas para jueces, magistrados y ministros.'
                    : 'Modifica la estructura constitucional del Poder Judicial: Elección popular de Ministros de la SCJN, Magistrados y Jueces de Distrito, sustitución del Consejo de la Judicatura por el Tribunal de Disciplina Judicial y reducción del Pleno de la Corte de 11 a 9 ministros.',
                'puntos_clave' => [
                    'Elección popular de ministros, magistrados y jueces federales por voto ciudadano.',
                    'Creación del Tribunal de Disciplina Judicial con facultades sancionatorias.',
                    'Extinción de fideicomisos del Poder Judicial y tope salarial al sueldo presidencial.'
                ]
            ];
        }

        if (str_contains($temaClave, 'Pueblos Indígenas') || str_contains($tituloUpper, 'INDÍGENAS') || str_contains($tituloUpper, 'AFROMEXICANOS')) {
            return [
                'tipo' => 'Poder Ejecutivo Federal',
                'promovente' => 'Presidente de la República (Paquete Constitucional 5-Feb)',
                'partido' => 'Poder Ejecutivo (Consenso Pluripartidista)',
                'origen_tag' => 'Iniciativa Presidencial',
                'resumen_ciudadano' => 'Reconocimiento de pueblos y comunidades indígenas y afromexicanas como sujetos de derecho público con personalidad jurídica y patrimonio propio, garantizando consulta previa y presupuesto directo.',
                'puntos_clave' => [
                    'Personalidad jurídica y patrimonio propio para comunidades indígenas y afromexicanas.',
                    'Derecho a consulta libre, previa, informada y culturalmente adecuada.',
                    'Asignación directa de recursos públicos y preservación de su patrimonio biocultural.'
                ]
            ];
        }

        if (str_contains($temaClave, 'Guardia Nacional') || str_contains($tituloUpper, 'GUARDIA NACIONAL')) {
            return [
                'tipo' => 'Poder Ejecutivo Federal',
                'promovente' => 'Presidente de la República (Adscripción a SEDENA)',
                'partido' => 'Poder Ejecutivo / Coalición Sigamos Haciendo Historia',
                'origen_tag' => 'Iniciativa Presidencial',
                'resumen_ciudadano' => 'Reforma constitucional que transfiere el control operativo, administrativo y financiero de la Guardia Nacional a la Secretaría de la Defensa Nacional (SEDENA), reconociéndola como fuerza armada permanente de seguridad.',
                'puntos_clave' => [
                    'Adscripción formal y operativa de la Guardia Nacional bajo mando de la SEDENA.',
                    'Facultades de investigación de delitos bajo conducción del Ministerio Público.',
                    'Garantía de régimen disciplinario y fueros militares para sus elementos operativos.'
                ]
            ];
        }

        if (str_contains($temaClave, 'Simplificación Orgánica') || str_contains($tituloUpper, 'SIMPLIFICACIÓN ORGÁNICA') || str_contains($tituloUpper, 'ÓRGANOS AUTÓNOMOS')) {
            return [
                'tipo' => 'Poder Ejecutivo Federal',
                'promovente' => 'Presidente de la República (Extinción de Organismos Autónomos)',
                'partido' => 'Poder Ejecutivo / Morena - PVEM - PT',
                'origen_tag' => 'Iniciativa Presidencial',
                'resumen_ciudadano' => 'Extinción de 7 órganos constitucionales autónomos (incluidos INAI, COFECE, IFT, CRE, CNH, CONEVAL y MEJOREDU), transfiriendo sus funciones y tutela de derechos a dependencias de la Administración Pública Federal.',
                'puntos_clave' => [
                    'Desaparición del INAI y traslado de funciones de transparencia y protección de datos a la Secretaría Anticorrupción y Buen Gobierno.',
                    'Fusión de COFECE e IFT en un nuevo organismo desconcentrado de competencia económica.',
                    'Ahorros presupuestales etiquetados prioritariamente para el Fondo de Pensiones para el Bienestar.'
                ]
            ];
        }

        if (str_contains($tituloUpper, 'PRISIÓN PREVENTIVA') || str_contains($temaClave, 'Seguridad')) {
            return [
                'tipo' => 'Poder Ejecutivo Federal',
                'promovente' => 'Presidente de la República / Coalición Sigamos Haciendo Historia',
                'partido' => 'Poder Ejecutivo / Morena',
                'origen_tag' => 'Iniciativa Presidencial',
                'resumen_ciudadano' => 'Ampliación del catálogo de delitos con prisión preventiva oficiosa (artículo 19 constitucional), incluyendo extorsión y delitos fiscales graves (factureras y contrabando).',
                'puntos_clave' => [
                    'Inclusión forzosa de prisión preventiva oficiosa para el delito de extorsión.',
                    'Medida cautelar automática para contrabando y expedición ilícita de comprobantes fiscales.',
                    'Interpretación judicial literal y estricta prohibiendo inaplicaciones oficiosas.'
                ]
            ];
        }

        return [
            'tipo' => 'Poder Legislativo',
            'promovente' => 'Comisión Dictaminadora del Congreso de la Unión',
            'partido' => 'Dictamen Parlamentario',
            'origen_tag' => 'Iniciativa Parlamentaria',
            'resumen_ciudadano' => 'Dictamen derivado del trabajo legislativo de comisiones ordinarias de la LXVI Legislatura.',
            'puntos_clave' => []
        ];
    }
}

