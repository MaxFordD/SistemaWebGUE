# Estructura de CSS del Proyecto

Este documento describe la organización de los archivos CSS del proyecto para facilitar el mantenimiento y la escalabilidad.

## Orden de carga

Los archivos CSS deben cargarse en el siguiente orden para asegurar la correcta cascada de estilos:

1. **Bootstrap 5.3.8** (CDN)
2. **Bootstrap Icons** (CDN)
3. **variables.css** - Variables CSS globales
4. **base.css** - Estilos base de HTML y Body
5. **navbar.css** - Navegación y menú
6. **waves.css** - Olas animadas decorativas
7. **footer.css** - Pie de página
8. **components.css** - Componentes reutilizables
9. **layout.css** - Utilidades de layout
10. **Archivos específicos** - CSS de páginas específicas (login.css, etc.)

**Nota:** Todos estos archivos se cargan en `resources/views/layouts/app.blade.php`

## Descripción de archivos

### `variables.css`
Define todas las variables CSS globales del proyecto:
- Colores principales y secundarios
- Alturas de elementos (navbar, waves)
- Sombras (sm, md, lg, xl)
- Media queries para responsive

### `base.css`
Estilos fundamentales del documento:
- Reset y estilos de html/body
- Flexbox para sticky footer
- Skip link para accesibilidad
- Reduced motion para preferencias de usuario
- Textura de fondo

### `navbar.css`
Todo lo relacionado con la navegación:
- Header sticky
- Navbar con glassmorphism
- Links con animaciones
- Dropdown menus
- Avatar de usuario
- Botón de login
- Responsive mobile

### `waves.css`
Diseño decorativo de ondas:
- Contenedor de waves
- Animaciones de olas
- Variantes (no-waves, waves-compact)
- Keyframes de animación

### `footer.css`
Estilos del pie de página:
- Footer con gradiente
- Efectos visuales
- Sticky footer con flexbox

### `components.css`
Componentes reutilizables:
- Botones (primary, outline)
- Alerts (success, danger)
- Cards con hover
- Hero sections
- Galería de imágenes
- Tarjetas de noticias
- Artículos
- Loading states
- Scroll to top button

### `layout.css`
Utilidades y ajustes menores de layout:
- Z-index utilities
- Helpers de modal
- Espaciados específicos

### `login.css`
Estilos específicos de la página de login
- Diseño split-screen
- Formulario de autenticación
- Responsive para móvil

## Mantenimiento

### Para agregar nuevos estilos:

1. **Estilos globales**: Agregar a `variables.css`
2. **Componente nuevo**: Agregar a `components.css`
3. **Modificar navbar**: Editar `navbar.css`
4. **Página específica**: Crear nuevo archivo CSS (ej: `admin.css`)

### Buenas prácticas:

- ✅ Usar variables CSS en lugar de valores hardcoded
- ✅ Seguir la convención BEM para nombres de clases cuando sea apropiado
- ✅ Documentar código complejo con comentarios
- ✅ Mantener un archivo por concepto (separación de responsabilidades)
- ✅ Usar media queries para responsive
- ❌ No duplicar código entre archivos
- ❌ No usar !important a menos que sea absolutamente necesario
- ❌ No mezclar estilos de diferentes componentes en un mismo archivo

## Testing

Después de modificar CSS, probar en:
- [ ] Chrome/Edge (últimas versiones)
- [ ] Firefox (última versión)
- [ ] Safari (si es posible)
- [ ] Móvil (responsive)
- [ ] Zoom al 50%, 100%, 150%, 200%
- [ ] Modo oscuro del sistema (si aplica)
- [ ] Preferencias de reduced motion

## Troubleshooting

### Los estilos no se aplican
1. Verificar el orden de carga en `layouts/app.blade.php`
2. Limpiar caché del navegador (Ctrl+Shift+R)
3. Verificar que no haya errores en la consola del navegador
4. Comprobar que las rutas de los archivos CSS sean correctas

### Conflictos entre estilos
1. Verificar que no se cargue Bootstrap dos veces
2. Revisar la especificidad de los selectores
3. Usar las herramientas de desarrollo del navegador para inspeccionar

### Performance
- Los archivos CSS están separados para mejor mantenibilidad
- En producción, considerar:
  - Minificar todos los CSS
  - Combinar en un solo archivo
  - Usar build tools (Laravel Mix, Vite, etc.)
  - Implementar cache busting
