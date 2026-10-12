<?php
declare(strict_types=1);

use PulsoCivico\Core\Router;
use PulsoCivico\Core\ViewHelper;

/**
 * @var array $politicosConEstudios Colección de políticos con perfil académico clasificado
 * @var array $escolaridadStats Métricas globales de escolaridad
 * @var string $categoriaActiva Categoría inicial preseleccionada ('todos' | 'posgrados' | 'licenciatura' | 'media-trunca')
 * @var array $seo Metadatos SEO
 */

$totalAuditados = $escolaridadStats['total_legisladores'] ?? count($politicosConEstudios);
$posgrados = $escolaridadStats['resumen_posgrados'] ?? ['total' => 0, 'porcentaje' => 0];
$licenciaturas = $escolaridadStats['resumen_licenciaturas'] ?? ['total' => 0, 'porcentaje' => 0];
$mediaTrunca = $escolaridadStats['resumen_media_trunca'] ?? ['total' => 0, 'porcentaje' => 0];

$homeUrl = Router::url('/');
$estudiosBaseUrl = Router::url('/estudios');

require __DIR__ . '/layout/header.php';
?>

<!-- Breadcrumbs -->
<nav aria-label="breadcrumb" class="breadcrumb-ios">
    <ol class="breadcrumb mb-3">
        <li class="breadcrumb-item"><a href="<?= $homeUrl ?>">Inicio</a></li>
        <li class="breadcrumb-item"><a href="<?= $homeUrl ?>#seccion-educacion">Radiografía Educativa</a></li>
        <li class="breadcrumb-item active" aria-current="page">Directorio por Nivel de Estudios</li>
    </ol>
</nav>

<!-- Hero de Auditoría Académica -->
<div class="card-civic p-4 p-md-5 mb-4 position-relative overflow-hidden">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-primary-subtle text-primary small fw-semibold mb-3">
                <i class="bi bi-mortarboard-fill"></i> Observatorio de Trayectorias y Formación
            </div>
            <h1 class="display-6 fw-bold text-dark mb-2">
                Nivel Académico de los Legisladores Federales
            </h1>
            <p class="text-secondary mb-3" style="max-width: 680px;">
                Consulta la formación y títulos de los <strong><?= (int)$totalAuditados ?></strong> representantes auditados de la LXVI Legislatura en la Cámara de Diputados y el Senado.
            </p>

            <!-- Nota metodológica sutil sobre unificación -->
            <div class="p-3 bg-light rounded-3 border d-flex align-items-start gap-2 mb-2" style="font-size: 0.8rem; line-height: 1.45;">
                <i class="bi bi-info-circle-fill text-primary flex-shrink-0 mt-1"></i>
                <div class="text-secondary">
                    <strong class="text-dark">Criterio de Certificación Terminal:</strong> En términos legales y de acreditación oficial ante la Secretaría de Educación Pública (SEP), quienes cuentan con estudios universitarios truncos o pasantía tienen como último grado formal acreditado con certificado terminal la <strong>Educación Media Superior</strong> (Bachillerato / Preparatoria).
                </div>
            </div>
        </div>

        <div class="col-lg-4 mt-4 mt-lg-0">
            <!-- Mini resumen de distribución -->
            <div class="row g-2 text-center">
                <div class="col-4">
                    <div class="p-3 bg-light rounded-3 border h-100">
                        <div class="h5 fw-bold text-primary mb-0"><?= $posgrados['porcentaje'] ?>%</div>
                        <div class="text-muted" style="font-size: 0.7rem;">Posgrado</div>
                        <span class="badge bg-primary-subtle text-primary rounded-pill mt-1" style="font-size: 0.68rem;"><?= $posgrados['total'] ?></span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="p-3 bg-light rounded-3 border h-100">
                        <div class="h5 fw-bold text-success mb-0"><?= $licenciaturas['porcentaje'] ?>%</div>
                        <div class="text-muted" style="font-size: 0.7rem;">Licenciatura</div>
                        <span class="badge bg-success-subtle text-success rounded-pill mt-1" style="font-size: 0.68rem;"><?= $licenciaturas['total'] ?></span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="p-3 bg-light rounded-3 border h-100">
                        <div class="h5 fw-bold text-warning-emphasis mb-0"><?= $mediaTrunca['porcentaje'] ?>%</div>
                        <div class="text-muted" style="font-size: 0.7rem;">Media / Trunca</div>
                        <span class="badge bg-warning-subtle text-dark rounded-pill mt-1" style="font-size: 0.68rem;"><?= $mediaTrunca['total'] ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Barra de Control Interactiva "Al Vuelo" -->
<div class="card-civic p-3 mb-4 border">
    <div class="row g-3 align-items-center">
        <!-- Pestañas de Categoría Académica -->
        <div class="col-12 col-xl-7">
            <div class="d-flex flex-wrap gap-2" id="selectorCategorias" role="group" aria-label="Filtro de nivel de estudios">
                <button type="button" 
                        class="btn btn-sm rounded-pill px-3 fw-semibold <?= $categoriaActiva === 'todos' ? 'btn-primary' : 'btn-light border text-dark' ?>" 
                        data-categoria="todos">
                    Todos <span class="badge bg-dark-subtle text-dark ms-1"><?= count($politicosConEstudios) ?></span>
                </button>
                <button type="button" 
                        class="btn btn-sm rounded-pill px-3 fw-semibold <?= $categoriaActiva === 'posgrados' ? 'btn-primary' : 'btn-light border text-dark' ?>" 
                        data-categoria="posgrados">
                    <i class="bi bi-mortarboard me-1"></i> Posgrados <span class="badge bg-primary-subtle text-primary ms-1"><?= $posgrados['total'] ?></span>
                </button>
                <button type="button" 
                        class="btn btn-sm rounded-pill px-3 fw-semibold <?= $categoriaActiva === 'licenciatura' ? 'btn-primary' : 'btn-light border text-dark' ?>" 
                        data-categoria="licenciatura">
                    <i class="bi bi-award me-1"></i> Licenciaturas <span class="badge bg-success-subtle text-success ms-1"><?= $licenciaturas['total'] ?></span>
                </button>
                <button type="button" 
                        class="btn btn-sm rounded-pill px-3 fw-semibold <?= $categoriaActiva === 'media-trunca' ? 'btn-primary' : 'btn-light border text-dark' ?>" 
                        data-categoria="media-trunca">
                    <i class="bi bi-layers-half me-1"></i> Media Superior / Trunca <span class="badge bg-warning-subtle text-dark ms-1"><?= $mediaTrunca['total'] ?></span>
                </button>
            </div>
        </div>

        <!-- Buscador en tiempo real y Filtro por Cámara -->
        <div class="col-12 col-xl-5">
            <div class="d-flex gap-2">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" 
                           id="buscadorEnVivo" 
                           class="form-control border-start-0" 
                           placeholder="Buscar por nombre, partido, carrera..." 
                           autocomplete="off">
                </div>

                <select id="filtroCamara" class="form-select form-select-sm" style="max-width: 125px;">
                    <option value="todas">Cámaras (Todas)</option>
                    <option value="Diputados">Diputados</option>
                    <option value="Senadores">Senadores</option>
                </select>

                <select id="filtroInstitucion" class="form-select form-select-sm" style="max-width: 155px;">
                    <option value="todas">Alma Máter (Todas)</option>
                    <option value="publica">🏛️ Univ. Pública</option>
                    <option value="privada">🎓 Univ. Privada</option>
                    <option value="mixta">⚖️ Formación Mixta</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Barra de Estado y Resultados -->
<div class="d-flex justify-content-between align-items-center mb-3 px-1 small text-muted">
    <div>
        Mostrando <strong class="text-dark" id="conteoVisible"><?= count($politicosConEstudios) ?></strong> representantes
        <span id="etiquetaFiltroActivo" class="text-secondary ms-1"></span>
    </div>
    <div>
        <a href="<?= $homeUrl ?>#seccion-educacion" class="text-decoration-none small text-secondary">
            &larr; Volver al resumen nacional
        </a>
    </div>
</div>

<!-- Grid de Personas / Políticos -->
<div class="row g-3 g-md-4 mb-5" id="gridLegisladores">
    <?php foreach ($politicosConEstudios as $p): 
        $analisis = $p['analisis_academico'];
        $gradoKey = $analisis['grado_maximo'] ?? 'sin_registro';
        $gradoInfo = $analisis['grado_info'] ?? [];
        $categoriaGrupo = $p['categoria_grupo'] ?? 'media-trunca';
        
        $nombre = $p['nombre_completo'] ?? '';
        $slug = $p['slug'] ?? '';
        $urlPerfil = Router::url('/politicos/' . $slug);
        
        $foto = !empty($p['referencia_sil']) && file_exists(dirname(__DIR__) . "/storage/raw/photos/{$p['referencia_sil']}.jpg")
            ? Router::url("/storage/raw/photos/{$p['referencia_sil']}.jpg")
            : ($p['foto_oficial_url'] ?? 'https://via.placeholder.com/150');

        $camara = $p['camara'] ?? 'Diputados';
        $partido = $p['partido'] ?? 'Sin Partido';
        $entidad = $p['entidad'] ?? ($p['estado'] ?? 'México');
        $tituloDestacado = $analisis['titulo_destacado'] ?? 'Sin registro';
        $disciplina = $analisis['disciplina_principal'] ?? 'General';
        $tieneTrunca = $analisis['tiene_trunca'] ?? false;
        $instResumen = $analisis['institucion_resumen'] ?? [];
        $almaMater = $instResumen['alma_mater_destacada'] ?? '';
        $tipoInst = $instResumen['tipo_predominante'] ?? 'no_determinada';
        $instInfo = $instResumen['info'] ?? [];
        $todasInst = implode(' ', $instResumen['instituciones'] ?? []);

        // Texto descriptivo para búsqueda en vivo
        $searchCorpus = mb_strtolower("{$nombre} {$partido} {$entidad} {$camara} {$tituloDestacado} {$disciplina} {$gradoInfo['label']} {$almaMater} {$todasInst}", 'UTF-8');
    ?>
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3 item-legislador" 
         data-categoria="<?= ViewHelper::e($categoriaGrupo) ?>" 
         data-camara="<?= ViewHelper::e($camara) ?>" 
         data-institucion="<?= ViewHelper::e($tipoInst) ?>" 
         data-search="<?= ViewHelper::e($searchCorpus) ?>">
        <div class="card-civic p-3 p-md-4 h-100 d-flex flex-column justify-content-between position-relative">
            <div>
                <!-- Encabezado de la Card: Partido y Cámara -->
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="badge bg-light text-secondary border small">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= ViewHelper::e($entidad) ?>
                    </span>
                    <?= ViewHelper::renderPartyBadge($partido) ?>
                </div>

                <!-- Foto y Datos Principales -->
                <div class="text-center mb-3">
                    <div class="position-relative d-inline-block mb-2">
                        <img src="<?= $foto ?>" 
                             alt="<?= ViewHelper::e($nombre) ?>" 
                             class="rounded-circle shadow-sm border object-fit-cover" 
                             style="width: 78px; height: 78px;"
                             loading="lazy"
                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?= urlencode($nombre) ?>&background=0D8ABC&color=fff';">
                        <span class="position-absolute bottom-0 end-0 badge rounded-pill bg-white text-secondary border shadow-xs" style="font-size: 0.65rem;" title="<?= ViewHelper::e($camara) ?>">
                            <?= $camara === 'Senadores' ? 'SEN' : 'DIP' ?>
                        </span>
                    </div>
                    <h2 class="h6 fw-bold text-dark mb-1 text-truncate" title="<?= ViewHelper::e($nombre) ?>">
                        <a href="<?= $urlPerfil ?>" class="text-decoration-none text-dark hover-primary">
                            <?= ViewHelper::e($nombre) ?>
                        </a>
                    </h2>
                    <div class="text-muted small" style="font-size: 0.75rem;">
                        <?= ViewHelper::e($camara) ?> &bull; <?= ViewHelper::e($entidad) ?>
                    </div>
                </div>

                <!-- Insignia Académica y Título -->
                <div class="p-2 px-3 rounded-3 bg-light border mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="badge <?= $gradoInfo['badge_class'] ?? 'bg-secondary-subtle text-secondary' ?>" style="font-size: 0.72rem;">
                            <i class="bi bi-<?= $gradoInfo['icono'] ?? 'mortarboard' ?> me-1"></i><?= ViewHelper::e($gradoInfo['label'] ?? 'Sin registro') ?>
                        </span>
                        <?php if ($tieneTrunca && $gradoKey !== 'trunca'): ?>
                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle" style="font-size: 0.65rem;" title="Cuenta con título superior pero dejó otra carrera trunca">
                            +1 carrera trunca
                        </span>
                        <?php elseif ($gradoKey === 'trunca'): ?>
                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle" style="font-size: 0.65rem;">
                            Sin título
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="fw-semibold text-dark small text-truncate" title="<?= ViewHelper::e($tituloDestacado) ?>" style="font-size: 0.8rem;">
                        <?= ViewHelper::e($tituloDestacado) ?>
                    </div>
                    <div class="text-muted" style="font-size: 0.72rem;">
                        <i class="bi bi-compass me-1"></i><?= ViewHelper::e($disciplina) ?>
                    </div>

                    <!-- Alma Máter / Institución -->
                    <?php if ($almaMater !== '' && $almaMater !== 'No especificada en ficha'): ?>
                    <div class="mt-2 pt-2 border-top d-flex align-items-center justify-content-between" style="font-size: 0.72rem;">
                        <span class="badge <?= $instInfo['badge_class'] ?? 'bg-light text-secondary' ?>" style="font-size: 0.65rem;">
                            <i class="bi bi-<?= $tipoInst === 'publica' ? 'bank' : ($tipoInst === 'privada' ? 'mortarboard' : 'shuffle') ?> me-1"></i>
                            <?= $tipoInst === 'publica' ? 'Pública' : ($tipoInst === 'privada' ? 'Privada' : 'Mixta') ?>
                        </span>
                        <span class="text-secondary text-truncate ms-2 fw-medium text-end" title="<?= ViewHelper::e($almaMater) ?>" style="max-width: 140px;">
                            <?= ViewHelper::e($almaMater) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Botón de acceso a Ficha Canónica -->
            <div class="pt-2 border-top text-center">
                <a href="<?= $urlPerfil ?>" class="btn btn-outline-primary btn-sm rounded-pill w-100 py-1" style="font-size: 0.78rem;">
                    Ver expediente completo &rarr;
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Estado Vacío cuando ningún resultado coincide -->
<div id="sinResultados" class="p-5 text-center bg-white rounded-4 border shadow-sm my-4" style="display: none;">
    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light text-muted mb-3" style="width: 60px; height: 60px;">
        <i class="bi bi-search fs-3"></i>
    </div>
    <h3 class="h5 fw-bold text-dark mb-1">No se encontraron legisladores</h3>
    <p class="text-muted small mb-3">Prueba modificando el texto de búsqueda o cambiando la categoría seleccionada.</p>
    <button type="button" class="btn btn-primary rounded-pill btn-sm px-4" id="btnRestablecerFiltros">
        Restablecer filtros
    </button>
</div>

<!-- Lógica JavaScript "Al Vuelo" (Zero latency, Pure Vanilla) -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectorCategorias = document.getElementById('selectorCategorias');
    const buscadorEnVivo = document.getElementById('buscadorEnVivo');
    const filtroCamara = document.getElementById('filtroCamara');
    const filtroInstitucion = document.getElementById('filtroInstitucion');
    const items = document.querySelectorAll('.item-legislador');
    const conteoVisible = document.getElementById('conteoVisible');
    const sinResultados = document.getElementById('sinResultados');
    const etiquetaFiltroActivo = document.getElementById('etiquetaFiltroActivo');
    const btnRestablecerFiltros = document.getElementById('btnRestablecerFiltros');

    let categoriaActual = '<?= ViewHelper::e($categoriaActiva) ?>';

    function filtrarAlVuelo() {
        const query = (buscadorEnVivo.value || '').trim().toLowerCase();
        const camara = filtroCamara.value;
        const institucion = filtroInstitucion ? filtroInstitucion.value : 'todas';
        let visibleCount = 0;

        items.forEach(function (el) {
            const elCategoria = el.getAttribute('data-categoria');
            const elCamara = el.getAttribute('data-camara');
            const elInstitucion = el.getAttribute('data-institucion');
            const elSearch = el.getAttribute('data-search') || '';

            // 1. Filtro por categoría
            const pasaCategoria = (categoriaActual === 'todos' || elCategoria === categoriaActual);

            // 2. Filtro por cámara
            const pasaCamara = (camara === 'todas' || elCamara === camara);

            // 3. Filtro por institución
            const pasaInstitucion = (institucion === 'todas' || elInstitucion === institucion);

            // 4. Filtro por texto
            const pasaTexto = (!query || elSearch.indexOf(query) !== -1);

            if (pasaCategoria && pasaCamara && pasaInstitucion && pasaTexto) {
                el.style.display = '';
                visibleCount++;
            } else {
                el.style.display = 'none';
            }
        });

        conteoVisible.textContent = visibleCount;
        sinResultados.style.display = (visibleCount === 0) ? 'block' : 'none';

        // Actualizar etiqueta
        let etiqueta = '';
        if (categoriaActual !== 'todos') {
            const labels = {
                'posgrados': 'en Posgrados Concluidos',
                'licenciatura': 'en Licenciaturas',
                'media-trunca': 'en Media Superior / Trunca'
            };
            etiqueta = labels[categoriaActual] || '';
        }
        if (camara !== 'todas') {
            etiqueta += ' (' + camara + ')';
        }
        if (institucion !== 'todas') {
            const instLabels = {
                'publica': '🏛️ Pública',
                'privada': '🎓 Privada',
                'mixta': '⚖️ Mixta'
            };
            etiqueta += ' [' + (instLabels[institucion] || institucion) + ']';
        }
        etiquetaFiltroActivo.textContent = etiqueta;
    }

    // Clicks en botones de categoría
    selectorCategorias.addEventListener('click', function (e) {
        const btn = e.target.closest('button[data-categoria]');
        if (!btn) return;

        categoriaActual = btn.getAttribute('data-categoria');

        // Actualizar estilos activos de los botones
        selectorCategorias.querySelectorAll('button[data-categoria]').forEach(function (b) {
            if (b === btn) {
                b.className = 'btn btn-sm rounded-pill px-3 fw-semibold btn-primary';
            } else {
                b.className = 'btn btn-sm rounded-pill px-3 fw-semibold btn-light border text-dark';
            }
        });

        // Actualizar URL sin recargar la página (history API)
        const nuevaUrl = (categoriaActual === 'todos') 
            ? '<?= $estudiosBaseUrl ?>' 
            : '<?= $estudiosBaseUrl ?>/' + categoriaActual;
        window.history.replaceState({ categoria: categoriaActual }, '', nuevaUrl);

        filtrarAlVuelo();
    });

    // Inputs de búsqueda y selects
    buscadorEnVivo.addEventListener('input', filtrarAlVuelo);
    filtroCamara.addEventListener('change', filtrarAlVuelo);
    if (filtroInstitucion) filtroInstitucion.addEventListener('change', filtrarAlVuelo);

    // Botón restablecer
    btnRestablecerFiltros.addEventListener('click', function () {
        categoriaActual = 'todos';
        buscadorEnVivo.value = '';
        filtroCamara.value = 'todas';
        if (filtroInstitucion) filtroInstitucion.value = 'todas';

        selectorCategorias.querySelectorAll('button[data-categoria]').forEach(function (b) {
            if (b.getAttribute('data-categoria') === 'todos') {
                b.className = 'btn btn-sm rounded-pill px-3 fw-semibold btn-primary';
            } else {
                b.className = 'btn btn-sm rounded-pill px-3 fw-semibold btn-light border text-dark';
            }
        });

        window.history.replaceState({ categoria: 'todos' }, '', '<?= $estudiosBaseUrl ?>');
        filtrarAlVuelo();
    });

    // Filtro inicial al cargar
    filtrarAlVuelo();
});
</script>

<?php require __DIR__ . '/layout/footer.php'; ?>
