# Cómo contribuir a Pulso Cívico

¡Gracias por sumarte! No necesitas ser programador para aportar.

## Formas de aportar
| Perfil | Qué puedes hacer |
|---|---|
| 💻 Desarrollo | Scrapers de nuevas fuentes, mejoras de vistas, rendimiento, tests |
| 📊 Datos / investigación | Encontrar portales oficiales, validar datos contra fuentes, reportar errores |
| 📰 Periodismo | Vincular notas a políticos y votaciones, verificar contexto |
| 🎨 Diseño / UX | Mejorar experiencia móvil, accesibilidad, visualizaciones |
| 📣 Difusión | Comunicar hallazgos, traer más voluntarios |

## Flujo de trabajo (código o datos)
1. **Busca o abre un Issue** antes de trabajar (evita duplicar esfuerzos). Comenta "Lo tomo".
2. Haz **fork** del repositorio y crea una rama:
   - `feat/descripcion-corta`, `fix/...`, `scraper/congreso-jalisco`, `docs/...`
3. Si usas IA, dale primero el archivo [AGENTS.md](AGENTS.md).
4. Verifica antes de enviar:
   ```bash
   php tools/gateway.php lint "archivos,modificados.php"
   ```
5. Abre un **Pull Request** llenando la plantilla. Un mantenedor lo revisará.

## Reglas de oro
- **Fuente o no existe**: todo dato nuevo debe incluir `fuente_url` oficial.
- **Neutralidad**: sin opiniones, adjetivos ni sesgo partidista en datos, código o textos.
- **PRs pequeños**: un tema por PR. Más fácil de revisar = se acepta más rápido.
- **Sin secretos**: nunca subas `.env`, cookies, contraseñas ni datos personales.
- **Sin dependencias nuevas** sin discutirlo en un Issue.

## Mensajes de commit
```
feat: agrega vista de asistencias por diputado
fix: corrige conteo de votos en abstención
scraper: agrega Congreso de Nuevo León LXXVII
data: actualiza legisladores LXVI al 2026-10-12
docs: explica formato de votaciones
```

## Reportar un dato erróneo (sin programar)
Abre un Issue con la plantilla **"Dato erróneo"** indicando: político, dato incorrecto, dato correcto y enlace a la fuente oficial.

## Etiquetas de Issues
- `good first issue` — ideal para empezar.
- `fuente-nueva` — falta un scraper.
- `datos` — recolección o validación.
- `ayuda-ia-ok` — tarea bien acotada para resolver con asistente de IA.
- `estado:<nombre>` — relacionado con un estado específico.

## Licencias de tus aportes
Al contribuir aceptas que tu código se publique bajo **AGPL-3.0** y los datos bajo **CC BY 4.0**.
