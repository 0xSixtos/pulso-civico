# 🛠️ Stack Tecnológico — Pulso Cívico

> **Filosofía de Arquitectura:** *Simplicidad radical, Low-Cost y Cero Barreras de Entrada.*  
> Diseñado para que cualquier persona o asistente de IA pueda clonar y ejecutar el proyecto en menos de 5 minutos, sin necesidad de servidores costosos ni configuraciones complejas.

---

## 1. Resumen Ejecutivo del Stack

| Capa | Tecnología | Justificación y Ventaja |
|---|---|---|
| **Backend** | **PHP 8.1+ / 8.2+ Vanilla** | Tipado estricto (`declare(strict_types=1)`), propiedades `readonly`, sin sobrecarga de frameworks. Máxima velocidad de ejecución (<10 ms). |
| **Gestor de Paquetes** | **Ninguno (Cero Composer / Cero npm)** | No hay riesgo de dependencias rotas, conflictos de versiones ni vulnerabilidades de terceros en supply-chain. |
| **Bases de Datos** | **Híbrido: SQLite / MySQL / JSON** | • **Desarrollo:** SQLite (`storage/database.sqlite`) out-of-the-box (cero configuración).<br>• **Producción:** MySQL 8.0 / MariaDB con índices optimizados.<br>• **Intercambio:** Datasets JSON estructurados. |
| **Frontend** | **HTML5 Semántico + Bootstrap 5.3 + JS Vanilla** | Diseño responsivo Mobile-First con estilo de diseño cívico (`.card-civic`), buscador Spotlight (`Ctrl+K`) y mapa SVG interactivo de México. |
| **Integridad & Fe Pública** | **Criptografía SHA-256 + Merkle Tree** | Huella matemática auditable en cada documento oficial descargado. Respaldo permanente automatizado en **Wayback Machine**. |
| **Infraestructura & CI** | **GitHub Actions + Apache / Nginx** | Pruebas automáticas de sintaxis en cada Pull Request a costo $0. Servible en cualquier hosting económico o VPS de $3-5 USD. |

---

## 2. Requisitos de Entorno para Desarrolladores

Para correr el núcleo en local solo se requiere:
* **PHP 8.1 o superior** con extensiones nativas:
  * `curl` (para peticiones a portales de gobierno)
  * `mbstring` (para manejo correcto de acentos y caracteres UTF-8)
  * `pdo_sqlite` y/o `pdo_mysql` (acceso a datos)
* **Servidor local:** Cualquiera de los siguientes:
  * Servidor embebido de PHP: `php -S localhost:8000` (Universal en Windows, Mac y Linux)
  * XAMPP / Laragon / WampServer (Windows)
  * Docker (opcional para entornos Linux)

---

## 3. ¿Por qué este Stack y no Node/React/Python?

1. **Inclusión:** PHP sigue siendo el lenguaje más accesible para quienes empiezan o usan herramientas locales sencillas como XAMPP.
2. **Costo de Operación:** No requiere servidores con 8 GB de RAM para compilar assets ni bases de datos administradas que cuesten $50 USD al mes. Corre perfecto en un servidor modesto.
3. **Legibilidad para IA:** El código PHP vanilla bien tipado con DTOs es interpretado a la perfección y sin alucinaciones por asistentes como ChatGPT, Claude, Cursor y Gemini.
