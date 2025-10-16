# Estructura de JavaScript del Proyecto

Este documento describe la organización de los archivos JavaScript del proyecto.

## Archivos JavaScript Activos

### Archivos Globales (cargados en todas las páginas)

1. **navbar.js** - Funcionalidad del menú de navegación
   - Se carga en: `resources/views/layouts/app.blade.php`
   - Funciones: Manejo del menú móvil, scroll effects, etc.

### Archivos Específicos de Página

2. **login.js** - Funcionalidad de la página de login
   - Se carga en: `resources/views/auth/login.blade.php`
   - Funciones: Toggle de password, validación de formulario

3. **welcome.js** - Funcionalidad de la página de inicio
   - Se carga en: `resources/views/welcome.blade.php`
   - Funciones: Carousel, animaciones, efectos de scroll

4. **mesa-create.js** - Funcionalidad del formulario de Mesa de Partes
   - Se carga en: `resources/views/mesa/create.blade.php`
   - Funciones: Validación de archivos, preview, manejo de formulario

5. **admin-mesa.js** - Funcionalidad del panel de Mesa de Partes (Admin)
   - Se carga en: `resources/views/admin/mesa/index.blade.php`
   - Funciones: Filtros, búsqueda, actualización de estados

6. **noticia-create.js** - Funcionalidad del formulario de crear noticia
   - Se carga en: `resources/views/noticias/create.blade.php`
   - Funciones: Carga múltiple de archivos, preview de imágenes/documentos, eliminación de archivos

## Orden de Carga

Los archivos JavaScript se cargan en el siguiente orden:

1. **Bootstrap 5.3.8 Bundle JS** (CDN) - Incluye Popper.js
2. **navbar.js** - Script global
3. **Scripts específicos** - Via `@stack('scripts')` en cada página

## Buenas Prácticas

### Al agregar nuevo JavaScript:

- ✅ Usar `defer` en scripts que no son críticos
- ✅ Separar código por funcionalidad (un archivo por página/componente)
- ✅ Documentar funciones complejas
- ✅ Usar event delegation para elementos dinámicos
- ✅ Manejar errores apropiadamente (try/catch)
- ❌ No cargar librerías innecesarias
- ❌ No duplicar código entre archivos
- ❌ No usar jQuery (el proyecto usa JavaScript vanilla)

## Estructura de Archivos

```
public/js/
├── README.md           # Este archivo
├── navbar.js          # Navegación (global)
├── login.js           # Login
├── welcome.js         # Página de inicio
├── mesa-create.js     # Mesa de Partes (público)
├── admin-mesa.js      # Mesa de Partes (admin)
└── noticia-create.js  # Crear noticia con múltiples archivos
```

## Testing

Al modificar JavaScript, probar:
- [ ] Funcionalidad en Chrome/Edge
- [ ] Funcionalidad en Firefox
- [ ] Funcionalidad en Safari (si es posible)
- [ ] Responsive (móvil y desktop)
- [ ] Consola del navegador sin errores
- [ ] Accesibilidad (teclado, screen readers)

## Dependencias Externas

El proyecto no usa gestores de paquetes para el frontend (npm/webpack/vite no están configurados para producción).

**JavaScript Vanilla** - Todo el código está escrito en JavaScript nativo sin frameworks.

**Bootstrap 5.3.8** - Se carga desde CDN, proporciona componentes interactivos (modals, dropdowns, etc.)

## Troubleshooting

### Los scripts no funcionan
1. Verificar que el archivo esté cargado correctamente (Network tab en DevTools)
2. Revisar la consola del navegador por errores
3. Verificar que los selectores DOM sean correctos
4. Asegurar que el DOM esté completamente cargado antes de ejecutar scripts

### Conflictos con Bootstrap
- No sobrescribir eventos de Bootstrap sin limpiarlos primero
- Usar los eventos personalizados de Bootstrap cuando sea posible
- Consultar la documentación: https://getbootstrap.com/docs/5.3/
