=== YGB Category Showcase ===
Contributors: ygb
Tags: woocommerce, categories, products, astra, grid, showcase, responsive, secure
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 3.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Muestra las categorías de WooCommerce con imágenes y textos en un grid responsive optimizado para el tema Astra.

== Description ==

YGB Category Showcase es un plugin ligero y seguro que te permite mostrar las categorías de WooCommerce con imágenes destacadas, descripciones y conteo de productos.

= Características Principales =

* Grid responsive con 1-12 columnas configurables
* Imágenes destacadas de categorías con efecto hover
* Descripciones de categorías
* Conteo de productos por categoría
* Sistema de caché con limpieza automática
* Compatible con el tema Astra (gratis y pro)
* Totalmente responsive (mobile first)
* Soporte para modo oscuro
* Optimizado para SEO y accesibilidad
* Ligero y rápido (menos de 50KB)
* **Auditado y seguro** - Cumple con estándares de seguridad WordPress
* **Clipboard API moderna** - Copia de shortcodes mejorada
* **Lazy loading avanzado** - Con Intersection Observer

= Atributos del Shortcode =

`[ygb_categories]`

| Atributo | Valores | Default | Descripción |
|----------|--------|---------|-------------|
| number | 1-20 | 12 | Número de categorías a mostrar |
| columns | 1-12 | 4 | Columnas en desktop |
| hide_empty | true/false | true | Ocultar categorías sin productos |
| orderby | name/count/slug/term_group/term_order | name | Campo de ordenamiento |
| order | ASC/DESC | ASC | Dirección del orden |
| show_count | true/false | true | Mostrar conteo de productos |
| show_description | true/false | true | Mostrar descripción |
| image_size | thumbnail/medium/large/full | medium | Tamaño de imagen |
| cache | true/false | true | Usar sistema de caché |

= Ejemplos de Uso =

Mostrar 6 categorías en 3 columnas:
`[ygb_categories number="6" columns="3"]`

Mostrar 8 categorías en 4 columnas:
`[ygb_categories number="8" columns="4"]`

Ordenar por cantidad de productos:
`[ygb_categories orderby="count" order="DESC"]`

Sin conteo ni descripciones:
`[ygb_categories show_count="false" show_description="false"]`

= Requisitos =

* WordPress 5.0 o superior
* WooCommerce 4.0 o superior
* PHP 7.4 o superior

= Compatibilidad =

* ✅ Tema Astra (gratis)
* ✅ Tema Astra Pro
* ✅ Cualquier tema compatible con WooCommerce
* ✅ Page Builders (Elementor, Beaver Builder, etc.)
* ✅ WooCommerce HPOS (High-Performance Order Storage)
* ✅ Modo oscuro del sistema

== Installation ==

1. Sube la carpeta `ygb-category` al directorio `/wp-content/plugins/`
2. Activa el plugin desde el menú 'Plugins' en WordPress
3. Usa el shortcode `[ygb_categories]` en cualquier página o entrada

== Frequently Asked Questions ==

= ¿Funciona con el tema Astra gratis? =

Sí, el plugin está optimizado para funcionar perfectamente con Astra gratis, utilizando las variables CSS del tema.

= ¿Puedo personalizar los estilos? =

Sí, todos los estilos usan clases CSS con prefijo `ygb-` que puedes sobrescribir en tu tema hijo. También puedes añadir CSS personalizado desde el panel de administración.

= ¿El plugin afecta el rendimiento? =

No, el plugin incluye un sistema de caché que almacena las categorías por 1 hora, reduciendo las consultas a la base de datos. Además, las imágenes usan lazy loading con Intersection Observer para mejorar la carga.

= ¿Es seguro? =

Sí, el plugin ha sido auditado por expertos en seguridad WordPress y sigue todas las mejores prácticas: sanitización de datos, escapado de salidas, consultas seguras con $wpdb->prepare(), nonces CSRF y verificación de capacidades.

== Changelog ==

= 3.3.0 =
* **CORRECCIÓN:** Unificada versión en todos los archivos (readme.txt: 1.7 → 3.3.0, ygb-category.js: 3.2.0 → 3.3.0, ygb-category.css: 2.1 → 3.3.0)
* **CORRECCIÓN:** Sistema responsive ahora respeta la configuración de columnas del usuario en lugar de forzar valores fijos
* **CORRECCIÓN:** Sanitización de CSS personalizado mejorada con validación más estricta de propiedades y selectores
* **CORRECCIÓN:** SVG inline sanitizado completamente eliminando posibles vectores XSS
* **CORRECCIÓN:** Eliminada dependencia innecesaria de jQuery - código JavaScript vanilla moderno
* **CORRECCIÓN:** Añadido nonce verificación en todas las acciones AJAX potenciales
* **MEJORA:** Documentación ampliada de filtros y hooks disponibles
* **MEJORA:** Accesibilidad mejorada con atributos ARIA completos
* **MEJORA:** Rendimiento optimizado con reducción de consultas DOM

= 3.2.0 =
* **SEGURIDAD:** Filtrado mejorado de CSS personalizado contra inyección
* **MEJORA:** Posición del menú cambiada a 26 (después de Plugins)
* **MEJORA:** Límite de columnas reducido a 12 para mejor rendimiento
* **MEJORA:** Límite de categorías reducido a 20 para mejor rendimiento
* **MEJORA:** Clipboard API moderna para copiar shortcodes
* **MEJORA:** Rate limiting en limpieza de caché
* **MEJORA:** Nonce agregado en página de ejemplos
* **MEJORA:** Validación de orderby extendida
* **MEJORA:** Fallback para navegadores sin Intersection Observer

= 3.1.0 =
* **SEGURIDAD:** Corrección de nonces inconsistentes
* **SEGURIDAD:** Verificación de capacidades en admin
* **SEGURIDAD:** Validación mejorada de campos POST
* **SEGURIDAD:** Consultas SQL con $wpdb->prepare()
* **RENDIMIENTO:** Caché con limpieza automática
* **ACCESIBILIDAD:** Aria labels y focus states
* **SOPORTE:** Modo oscuro

= 3.0.0 =
* Versión inicial

== Upgrade Notice ==

= 3.3.0 =
Actualización crítica que corrige problemas de seguridad, inconsistencia de versiones y mejora el sistema responsive. Recomendada para todos los usuarios.