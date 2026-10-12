# 🇲🇽 Pulso Cívico

**Plataforma ciudadana y de código abierto para la transparencia política en México.**

Cruzamos **datos oficiales del gobierno** con **noticias** para mostrar el historial real de cada político: qué dicen, qué hacen, cómo votan y cuántas veces han cambiado de partido ("chapulineo"). Objetivo: un **voto informado**.

> Proyecto *Civic Tech* colaborativo. Neutral, verificable y sin fines partidistas: **todo dato debe tener una fuente oficial enlazada**.

---

## ✨ Qué hay hoy

- Perfiles de legisladores federales (LXVI Legislatura 2024–2027): trayectoria, comisiones, escolaridad.
- Votaciones nominales y asistencias (Cámara de Diputados).
- Mapa interactivo por estado, comisiones y estudios estadísticos.
- Búsqueda rápida (`Ctrl+K`).

## 🧱 Stack (intencionalmente simple y low-cost)

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.1+ vanilla (sin frameworks ni Composer) |
| Datos | Archivos JSON en `storage/data/json/` (sin base de datos) |
| Frontend | HTML + Bootstrap 5 (CDN) + JavaScript vanilla |
| Scrapers | PHP + cURL nativo |

## 🚀 Instalación en 5 minutos

**Requisitos:** PHP 8.1+ con extensiones `curl`, `mbstring`, `json`.

### Opción A — Servidor integrado de PHP (cualquier SO)
```bash
git clone https://github.com/pulso-civico/pulso-civico.git
cd pulso-civico
cp .env.example .env
php -S localhost:8000
```
Abre http://localhost:8000

### Opción B — XAMPP (Windows)
1. Clona dentro de `C:\xampp\htdocs\pulso-civico`.
2. Copia `.env.example` → `.env`.
3. Abre http://localhost/pulso-civico/

## 🛠️ Comandos útiles

Interfaz universal (cualquier SO):
```bash
php tools/gateway.php help
php tools/gateway.php lint "src/Core/Router.php"
php tools/gateway.php health
```
Atajo en Windows: `powershell -ExecutionPolicy Bypass -File .\tools\run.ps1 <accion> [args]`

## 📚 Documentación

| Documento | Para quién |
|---|---|
| [CONTRIBUTING.md](CONTRIBUTING.md) | Cómo aportar (código, datos, diseño, investigación) |
| [AGENTS.md](AGENTS.md) | Reglas para asistentes de IA (Cursor, Claude, Copilot, Gemini, ChatGPT) |
| [docs/ARQUITECTURA.md](docs/ARQUITECTURA.md) | Cómo está organizado el código |
| [docs/DATA_SCHEMA.md](docs/DATA_SCHEMA.md) | Formato estándar de los datos |
| [docs/FUENTES.md](docs/FUENTES.md) | Portales oficiales: cubiertos y pendientes |
| [docs/COMO_CREAR_UN_SCRAPER.md](docs/COMO_CREAR_UN_SCRAPER.md) | Agregar una nueva legislatura o fuente |
| [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) | Convivencia y neutralidad |
| [SECURITY.md](SECURITY.md) | Reportar vulnerabilidades |

## 🤝 Únete

- **Telegram:** [enlace a la comunidad]
- **Tareas abiertas:** pestaña *Issues* (busca `good first issue`).

## ⚖️ Licencias

- **Código:** [AGPL-3.0](LICENSE) — si lo mejoras o lo publicas como servicio, compartes las mejoras.
- **Datos generados:** [CC BY 4.0](DATA_LICENSE.md) — úsalos libremente citando "Pulso Cívico" y la fuente oficial original.
