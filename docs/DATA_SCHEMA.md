# Esquema de Datos (contrato estándar)

Todo scraper debe producir JSON con estos campos (`snake_case`, UTF-8, fechas `YYYY-MM-DD`).
La fuente de verdad del formato son las clases en `src/DTO/`.

## Reglas generales
- **Obligatorio en todo registro o dataset**: `fuente_url` y `fecha_extraccion` (dentro de `metadata`).
- Valores desconocidos: `null` (nunca cadenas inventadas como "N/D").
- Nombres de partido: siglas oficiales (`MORENA`, `PAN`, `PRI`, `PVEM`, `PT`, `MC`, `PRD`…).
- Entidades: nombre oficial INEGI (`Ciudad de México`, `Estado de México`, …).

## Legislador (`LegisladorDTO`)
| Campo | Tipo | Req. | Descripción |
|---|---|---|---|
| `referencia_sil` | string | ✅ | ID en la fuente (SIL u otra). Identificador estable |
| `nombre_completo` | string | ✅ | |
| `camara` | string | ✅ | `Diputados` / `Senado` / congreso local |
| `legislatura` | int | ✅ | Ej. `66` (LXVI) |
| `partido` | string | ✅ | Siglas |
| `principio_eleccion` | string | ✅ | Mayoría relativa / Representación proporcional / Primera minoría |
| `entidad` | string | ✅ | |
| `distrito` | string\|null | | |
| `tipo_cargo` | string | | `Propietario` / `Suplente` |
| `suplente_nombre` | string\|null | | |
| `fecha_toma_protesta` | date\|null | | |
| `fecha_nacimiento` | date\|null | | Solo si es pública en la fuente oficial |
| `correo_institucional` | string\|null | | Solo institucional |
| `telefono` | string\|null | | Solo de oficina institucional |
| `ubicacion_oficina` | string\|null | | Oficina en recinto legislativo |
| `ultimo_grado_estudios` | string\|null | | |
| `preparacion_academica` | string\|null | | |
| `experiencia_legislativa_resumen` | string\|null | | |
| `foto_oficial_url` | string\|null | | URL original |
| `foto_local_path` | string\|null | | Ruta en `storage/raw/photos/` |
| `comisiones` | array | | `[{nombre, cargo, ...}]` |
| `trayectoria_politica` | array | | Base del historial de partidos ("chapulineo") |
| `trayectoria_legislativa` | array | | |
| `trayectoria_administrativa` | array | | |
| `escolaridad_detalle` | array | | |
| `licencias` | array | | |
| `iniciativas` | array | | |
| `puntos_acuerdo` | array | | |
| `redes_sociales` | array | | Solo cuentas públicas oficiales |
| `metadata` | object | ✅ | `{fuente_url, fecha_extraccion, ...}` |

## Votación (`VotacionDTO`)
| Campo | Tipo | Req. | Descripción |
|---|---|---|---|
| `id_votacion_origen` | int | ✅ | ID en el portal de origen |
| `camara` | string | ✅ | |
| `legislatura` | int | ✅ | |
| `fecha` | date | ✅ | |
| `titulo` | string | ✅ | Título oficial del asunto votado |
| `tema_clave` | string\|null | | Categoría temática |
| `resumen_bancadas` | array | | Votos por partido |
| `totales` | object | ✅ | `{favor, contra, abstencion, ausentes, total}` |
| `votos_individuales` | array | | `[{referencia, nombre, partido, voto}]` |

## Sesión de Pleno (`SesionPlenoDTO`)
Ver `src/DTO/SesionPlenoDTO.php` (asistencias por sesión).

## Próximos contratos (pendientes de diseñar en comunidad)
- **Persona** (ID único entre legislaturas, para seguir a un político a lo largo del tiempo).
- **Militancia** (`persona_id`, `partido`, `desde`, `hasta`, `fuente_url`) → chapulineómetro.
- **Nota periodística** (`titulo`, `url`, `medio`, `fecha`, `personas[]`, `verificada`). Solo metadatos y enlace, nunca el texto completo.

> Cambiar un DTO existente rompe datasets: requiere Issue aprobado y campos nuevos **opcionales**.
