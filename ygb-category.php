<?php
/**
 * Plugin Name: YGB Category Showcase
 * Description: Muestra las categorías de WooCommerce con imágenes y textos
 * Version: 3.3.0
 * Author: YGB
 * Text Domain: ygb-category
 * WC requires at least: 4.0
 * WC tested up to: 9.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Constantes
define( 'YGB_URL', plugin_dir_url( __FILE__ ) );
define( 'YGB_PATH', plugin_dir_path( __FILE__ ) );
define( 'YGB_VERSION', '3.3.0' );

/**
 * Sanitizar color hexadecimal
 *
 * @param string $color Color a sanitizar
 * @return string Color sanitizado o vacío
 */
function ygb_sanitize_hex_color( $color ) {
    if ( empty( $color ) ) {
        return '';
    }
    
    $color = trim( strtolower( $color ) );
    
    // Validar formato hexadecimal
    if ( preg_match( '/^#([a-f0-9]{3}|[a-f0-9]{6})$/', $color ) ) {
        return $color;
    }
    
    // Validar nombres de colores CSS básicos
    $safe_color_names = array( 'white', 'black', 'transparent', 'inherit', 'initial', 'unset' );
    if ( in_array( $color, $safe_color_names, true ) ) {
        return $color;
    }
    
    return '';
}

/**
 * Sanitizar CSS personalizado - VERSIÓN OPTIMIZADA
 * Lista blanca de propiedades CSS seguras con límite de rendimiento
 *
 * @param string $css CSS a sanitizar
 * @return string CSS sanitizado
 */
function ygb_sanitize_custom_css( $css ) {
    if ( empty( $css ) ) {
        return '';
    }
    
    // Eliminar caracteres nulos y tags HTML
    $css = preg_replace( '/[\x00-\x1F\x7F]/', '', $css );
    $css = wp_strip_all_tags( $css );
    
    // Límite de líneas para rendimiento (máximo 200 líneas)
    $lines = explode( "\n", $css );
    if ( count( $lines ) > 200 ) {
        $lines = array_slice( $lines, 0, 200 );
        $css = implode( "\n", $lines );
        $lines = explode( "\n", $css );
    }
    
    // Propiedades CSS permitidas (lista blanca)
    $allowed_properties = array(
        'color', 'background', 'background-color', 'border', 'border-radius',
        'margin', 'padding', 'font-size', 'font-weight', 'font-family',
        'text-align', 'text-decoration', 'text-transform', 'line-height',
        'letter-spacing', 'box-shadow', 'transition', 'transform',
        'opacity', 'visibility', 'display', 'position', 'top', 'right',
        'bottom', 'left', 'z-index', 'width', 'max-width', 'min-width',
        'height', 'max-height', 'min-height', 'overflow', 'cursor'
    );
    
    // Selectores CSS permitidos (solo clases y elementos básicos)
    $allowed_selectors = array(
        '\.ygb-', '\.ygb-grid', '\.ygb-card', '\.ygb-link',
        '\.ygb-image', '\.ygb-info', '\.ygb-name', '\.ygb-desc',
        '\.ygb-count', 'div', 'span', 'h3', 'p', 'a'
    );
    
    $clean_lines = array();
    
    foreach ( $lines as $line ) {
        $line = trim( $line );
        if ( empty( $line ) ) {
            continue;
        }
        
        // Verificar que el selector sea seguro
        $selector_safe = false;
        foreach ( $allowed_selectors as $pattern ) {
            if ( preg_match( '/^' . $pattern . '/', $line ) ) {
                $selector_safe = true;
                break;
            }
        }
        
        if ( ! $selector_safe && ! preg_match( '/^@media/', $line ) ) {
            continue; // Saltar selectores no permitidos
        }
        
        // Verificar propiedades permitidas
        $property_safe = false;
        foreach ( $allowed_properties as $prop ) {
            if ( stripos( $line, $prop . ':' ) !== false ) {
                $property_safe = true;
                break;
            }
        }
        
        // Eliminar expresiones peligrosas
        $dangerous_patterns = array(
            '/expression\s*\(/i',
            '/javascript\s*:/i',
            '/vbscript\s*:/i',
            '/moz-binding/i',
            '/eval\s*\(/i',
            '/behavior\s*:/i',
            '/url\(\s*["\']?data:/i'
        );
        
        foreach ( $dangerous_patterns as $pattern ) {
            if ( preg_match( $pattern, $line ) ) {
                $property_safe = false;
                break;
            }
        }
        
        if ( $property_safe ) {
            $clean_lines[] = $line;
        }
    }
    
    $clean_css = implode( "\n", $clean_lines );
    
    // Limitar longitud máxima (10KB)
    if ( strlen( $clean_css ) > 10240 ) {
        $clean_css = substr( $clean_css, 0, 10240 );
    }
    
    return $clean_css;
}

/**
 * Generar CSS dinámico basado en opciones de color
 * Versión optimizada - SIN !important para evitar conflictos
 *
 * @param array $options Opciones del plugin
 * @return string CSS para inline
 */
function ygb_generate_dynamic_css( $options ) {
    // Valores por defecto
    $defaults = array(
        'card_bg_color'     => '#ffffff',
        'card_border_color' => '#e5e7eb',
        'title_color'       => '#1e293b',
        'title_hover_color' => '#007cba',
        'desc_color'        => '#6b7280',
        'count_color'       => '#9ca3af'
    );
    
    // Mezclar con opciones existentes
    $options = wp_parse_args( $options, $defaults );
    
    // Sanitizar colores
    $card_bg      = ygb_sanitize_hex_color( $options['card_bg_color'] ) ?: '#ffffff';
    $card_border  = ygb_sanitize_hex_color( $options['card_border_color'] ) ?: '#e5e7eb';
    $title_color  = ygb_sanitize_hex_color( $options['title_color'] ) ?: '#1e293b';
    $title_hover  = ygb_sanitize_hex_color( $options['title_hover_color'] ) ?: '#007cba';
    $desc_color   = ygb_sanitize_hex_color( $options['desc_color'] ) ?: '#6b7280';
    $count_color  = ygb_sanitize_hex_color( $options['count_color'] ) ?: '#9ca3af';
    
    // CSS SIN !important para respetar temas
    $css = "
    /* Colores personalizados - YGB Category */
    .ygb-card {
        background: {$card_bg};
        border-color: {$card_border};
    }
    .ygb-name {
        color: {$title_color};
    }
    .ygb-link:hover .ygb-name {
        color: {$title_hover};
    }
    .ygb-desc {
        color: {$desc_color};
    }
    .ygb-count {
        color: {$count_color};
    }";
    
    return $css;
}

/**
 * Declarar compatibilidad con WooCommerce HPOS
 */
add_action( 'before_woocommerce_init', function() {
    if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 
            'custom_order_tables', 
            __FILE__, 
            true 
        );
    }
});

/**
 * Verificar WooCommerce
 */
function ygb_check_woocommerce() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="error"><p>' . esc_html__( 'YGB Category Showcase requiere que WooCommerce esté instalado y activado.', 'ygb-category' ) . '</p></div>';
        });
        return false;
    }
    return true;
}
add_action( 'plugins_loaded', 'ygb_check_woocommerce' );

/**
 * Shortcode para mostrar categorías
 *
 * @param array $atts Atributos del shortcode
 * @return string HTML de las categorías
 */
function ygb_display_categories( $atts ) {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return '<p>' . esc_html__( 'Este plugin requiere WooCommerce.', 'ygb-category' ) . '</p>';
    }
    
    $default_options = get_option( 'ygb_category_options', array(
        'number'     => 12,
        'columns'    => 4,
        'hide_empty' => true,
        'orderby'    => 'name',
        'order'      => 'ASC',
        'show_count' => true,
        'show_description' => true,
        'image_size' => 'medium',
        'cache'      => true
    ) );
    
    $atts = shortcode_atts( $default_options, $atts );
    
    // Sanitizar atributos
    $atts['number'] = isset( $atts['number'] ) ? absint( $atts['number'] ) : 12;
    $atts['number'] = max( 1, min( 20, $atts['number'] ) );
    
    $atts['columns'] = isset( $atts['columns'] ) ? absint( $atts['columns'] ) : 4;
    $atts['columns'] = max( 1, min( 12, $atts['columns'] ) );
    
    $atts['hide_empty'] = filter_var( $atts['hide_empty'], FILTER_VALIDATE_BOOLEAN );
    $atts['show_count'] = filter_var( $atts['show_count'], FILTER_VALIDATE_BOOLEAN );
    $atts['show_description'] = filter_var( $atts['show_description'], FILTER_VALIDATE_BOOLEAN );
    $atts['cache'] = filter_var( $atts['cache'], FILTER_VALIDATE_BOOLEAN );
    
    $allowed_orderby = array( 'name', 'count', 'slug', 'term_group', 'term_order' );
    $atts['orderby'] = in_array( $atts['orderby'], $allowed_orderby, true ) ? $atts['orderby'] : 'name';
    
    $atts['order'] = in_array( strtoupper( $atts['order'] ), array( 'ASC', 'DESC' ), true ) ? strtoupper( $atts['order'] ) : 'ASC';
    
    $allowed_sizes = array( 'thumbnail', 'medium', 'large', 'full' );
    $atts['image_size'] = in_array( $atts['image_size'], $allowed_sizes, true ) ? $atts['image_size'] : 'medium';
    
    // Cache con mejor seguridad - usar wp_hash en lugar de md5
    $output = '';
    if ( $atts['cache'] ) {
        $json_atts = wp_json_encode( $atts );
        $cache_key = 'ygb_cats_' . wp_hash( $json_atts );
        $output = get_transient( $cache_key );
    }
    
    if ( empty( $output ) ) {
        $categories = get_terms( array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => $atts['hide_empty'],
            'number'     => $atts['number'],
            'orderby'    => $atts['orderby'],
            'order'      => $atts['order']
        ) );
        
        if ( empty( $categories ) || is_wp_error( $categories ) ) {
            return '<p>' . esc_html__( 'No hay categorías disponibles.', 'ygb-category' ) . '</p>';
        }
        
        $columns = max( 1, min( 12, absint( $atts['columns'] ) ) );
        $output = '<div class="ygb-grid" style="grid-template-columns: repeat(' . esc_attr( (string) $columns ) . ', 1fr);">';
        
        foreach ( $categories as $category ) {
            $thumbnail_id = get_term_meta( $category->term_id, 'thumbnail_id', true );
            $image = wp_get_attachment_image_url( $thumbnail_id, $atts['image_size'] );
            
            if ( ! $image && function_exists( 'wc_placeholder_img_src' ) ) {
                $image = wc_placeholder_img_src();
            }
            
            if ( ! $image ) {
                $image = 'data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22300%22%20height%3D%22300%22%20viewBox%3D%220%200%20300%20300%22%3E%3Crect%20width%3D%22300%22%20height%3D%22300%22%20fill%3D%22%23f0f0f0%22%2F%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2250%25%22%20dominant-baseline%3D%22middle%22%20text-anchor%3D%22middle%22%20fill%3D%22%23999%22%3E' . esc_attr__( 'Sin imagen', 'ygb-category' ) . '%3C%2Ftext%3E%3C%2Fsvg%3E';
            }
            
            $link = get_term_link( $category );
            if ( is_wp_error( $link ) ) {
                continue;
            }
            
            $name = esc_html( $category->name );
            $desc = wp_kses_post( $category->description );
            $count = intval( $category->count );
            
            $output .= '<div class="ygb-card">';
            $output .= '<a href="' . esc_url( $link ) . '" class="ygb-link" aria-label="' . esc_attr( sprintf( __( 'Ver productos en %s', 'ygb-category' ), $name ) ) . '">';
            $output .= '<div class="ygb-image"><img src="' . esc_url( $image ) . '" alt="' . esc_attr( $name ) . '" loading="lazy"></div>';
            $output .= '<div class="ygb-info">';
            $output .= '<h3 class="ygb-name">' . $name . '</h3>';
            
            if ( $atts['show_description'] && ! empty( $desc ) ) {
                $output .= '<div class="ygb-desc">' . $desc . '</div>';
            }
            
            if ( $atts['show_count'] ) {
                $output .= '<span class="ygb-count">' . sprintf( _n( '%d producto', '%d productos', $count, 'ygb-category' ), $count ) . '</span>';
            }
            
            $output .= '</div>';
            $output .= '</a>';
            $output .= '</div>';
        }
        
        $output .= '</div>';
        
        if ( $atts['cache'] ) {
            /**
             * Filtra el tiempo de expiración del caché
             *
             * @since 3.0.0
             * @param int $cache_time Tiempo en segundos (default: HOUR_IN_SECONDS)
             */
            $cache_time = apply_filters( 'ygb_cache_expiration', HOUR_IN_SECONDS );
            $cache_time = absint( $cache_time );
            if ( $cache_time < 1 ) {
                $cache_time = HOUR_IN_SECONDS;
            }
            set_transient( $cache_key, $output, $cache_time );
        }
    }
    
    return $output;
}
add_shortcode( 'ygb_categories', 'ygb_display_categories' );

/**
 * Encolar estilos y scripts
 */
function ygb_enqueue_styles() {
    // Cargar CSS base (sin colores fijos)
    wp_enqueue_style( 'ygb-category', YGB_URL . 'css/ygb-category.css', array(), YGB_VERSION );
    
    // Obtener opciones de color
    $options = get_option( 'ygb_category_options', array() );
    
    // Generar CSS dinámico con los colores del usuario
    $dynamic_css = ygb_generate_dynamic_css( $options );
    
    // Inyectar CSS dinámico
    if ( ! empty( $dynamic_css ) ) {
        wp_add_inline_style( 'ygb-category', $dynamic_css );
    }
    
    wp_enqueue_script( 'ygb-category', YGB_URL . 'js/ygb-category.js', array( 'jquery' ), YGB_VERSION, true );
    
    // CSS personalizado del usuario
    $custom_css = get_option( 'ygb_custom_css', '' );
    if ( ! empty( $custom_css ) ) {
        $custom_css = ygb_sanitize_custom_css( $custom_css );
        if ( ! empty( $custom_css ) ) {
            wp_add_inline_style( 'ygb-category', $custom_css );
        }
    }
}
add_action( 'wp_enqueue_scripts', 'ygb_enqueue_styles' );

/**
 * Limpiar caché al actualizar categorías - CON RATE LIMITING
 * CORREGIDO: Parámetros con valores por defecto para compatibilidad PHP 8.4+
 *
 * @param int    $term_id  ID del término (opcional)
 * @param int    $tt_id    Taxonomy term ID (opcional)
 * @param string $taxonomy Taxonomía (opcional)
 */
function ygb_clear_category_cache( $term_id = 0, $tt_id = 0, $taxonomy = '' ) {
    $is_ajax = function_exists( 'wp_doing_ajax' ) ? wp_doing_ajax() : ( defined( 'DOING_AJAX' ) && DOING_AJAX );
    
    if ( ! current_user_can( 'manage_categories' ) && ! wp_doing_cron() && ! $is_ajax ) {
        return;
    }
    
    if ( 'product_cat' === $taxonomy && $term_id > 0 ) {
        static $cleared = false;
        static $last_cleared_time = 0;
        
        // Rate limiting: no limpiar más de una vez cada 10 segundos
        $current_time = time();
        if ( $last_cleared_time > 0 && ( $current_time - $last_cleared_time ) < 10 ) {
            return;
        }
        
        if ( $cleared ) {
            return;
        }
        
        $cleared = true;
        $last_cleared_time = $current_time;
        
        global $wpdb;
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_ygb_cats_%'
        ) );
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_timeout_ygb_cats_%'
        ) );
    }
}
add_action( 'created_term', 'ygb_clear_category_cache', 10, 3 );
add_action( 'edited_term', 'ygb_clear_category_cache', 10, 3 );
add_action( 'delete_term', 'ygb_clear_category_cache', 10, 3 );

/**
 * Menú de administración
 */
function ygb_admin_menu() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }
    
    add_menu_page(
        __( 'YGB Category', 'ygb-category' ),
        __( 'YGB Category', 'ygb-category' ),
        'manage_options',
        'ygb-category',
        'ygb_admin_page',
        'dashicons-category',
        26
    );
    
    add_submenu_page(
        'ygb-category',
        __( 'Ejemplos de Uso', 'ygb-category' ),
        __( 'Ejemplos', 'ygb-category' ),
        'manage_options',
        'ygb-category-examples',
        'ygb_examples_page'
    );
}
add_action( 'admin_menu', 'ygb_admin_menu' );

/**
 * Página principal de administración
 */
function ygb_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'No tienes permisos suficientes para acceder a esta página.', 'ygb-category' ) );
    }
    
    // Obtener opciones actuales
    $options = get_option( 'ygb_category_options', array() );
    
    // Valores por defecto para colores
    $default_colors = array(
        'card_bg_color'     => '#e0e0e0',
        'card_border_color' => '#000000',
        'title_color'       => '#000000',
        'title_hover_color' => '#000000',
        'desc_color'        => '#ffffff',
        'count_color'       => '#e26143'
    );
    
    // Asegurar que todas las opciones de color existan
    foreach ( $default_colors as $key => $default ) {
        if ( ! isset( $options[ $key ] ) ) {
            $options[ $key ] = $default;
        }
    }
    
    // Guardar configuración
    if ( isset( $_POST['submit'] ) && check_admin_referer( 'ygb_save_settings' ) ) {
        // Opciones básicas
        $options['number']     = isset( $_POST['number'] ) ? absint( $_POST['number'] ) : 12;
        $options['columns']    = isset( $_POST['columns'] ) ? absint( $_POST['columns'] ) : 4;
        $options['hide_empty'] = isset( $_POST['hide_empty'] );
        $options['show_count'] = isset( $_POST['show_count'] );
        $options['show_description'] = isset( $_POST['show_description'] );
        $options['cache']      = isset( $_POST['cache'] );
        
        $options['number']  = max( 1, min( 20, $options['number'] ) );
        $options['columns'] = max( 1, min( 12, $options['columns'] ) );
        
        // Orderby
        $allowed_orderby = array( 'name', 'count', 'slug', 'term_group', 'term_order' );
        if ( isset( $_POST['orderby'] ) && in_array( $_POST['orderby'], $allowed_orderby, true ) ) {
            $options['orderby'] = sanitize_text_field( $_POST['orderby'] );
        }
        
        // Order
        if ( isset( $_POST['order'] ) && in_array( $_POST['order'], array( 'ASC', 'DESC' ), true ) ) {
            $options['order'] = sanitize_text_field( $_POST['order'] );
        }
        
        // Image size
        $allowed_sizes = array( 'thumbnail', 'medium', 'large', 'full' );
        if ( isset( $_POST['image_size'] ) && in_array( $_POST['image_size'], $allowed_sizes, true ) ) {
            $options['image_size'] = sanitize_text_field( $_POST['image_size'] );
        }
        
        // Guardar colores
        if ( isset( $_POST['card_bg_color'] ) ) {
            $sanitized = ygb_sanitize_hex_color( $_POST['card_bg_color'] );
            $options['card_bg_color'] = ! empty( $sanitized ) ? $sanitized : '#ffffff';
        }
        
        if ( isset( $_POST['card_border_color'] ) ) {
            $sanitized = ygb_sanitize_hex_color( $_POST['card_border_color'] );
            $options['card_border_color'] = ! empty( $sanitized ) ? $sanitized : '#e5e7eb';
        }
        
        if ( isset( $_POST['title_color'] ) ) {
            $sanitized = ygb_sanitize_hex_color( $_POST['title_color'] );
            $options['title_color'] = ! empty( $sanitized ) ? $sanitized : '#1e293b';
        }
        
        if ( isset( $_POST['title_hover_color'] ) ) {
            $sanitized = ygb_sanitize_hex_color( $_POST['title_hover_color'] );
            $options['title_hover_color'] = ! empty( $sanitized ) ? $sanitized : '#007cba';
        }
        
        if ( isset( $_POST['desc_color'] ) ) {
            $sanitized = ygb_sanitize_hex_color( $_POST['desc_color'] );
            $options['desc_color'] = ! empty( $sanitized ) ? $sanitized : '#6b7280';
        }
        
        if ( isset( $_POST['count_color'] ) ) {
            $sanitized = ygb_sanitize_hex_color( $_POST['count_color'] );
            $options['count_color'] = ! empty( $sanitized ) ? $sanitized : '#9ca3af';
        }
        
        update_option( 'ygb_category_options', $options );
        
        // Guardar CSS personalizado
        if ( isset( $_POST['custom_css'] ) ) {
            $custom_css = sanitize_textarea_field( $_POST['custom_css'] );
            $custom_css = ygb_sanitize_custom_css( $custom_css );
            update_option( 'ygb_custom_css', $custom_css );
        }
        
        // Limpiar caché
        global $wpdb;
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_ygb_cats_%'
        ) );
        
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Configuración guardada correctamente.', 'ygb-category' ) . '</p></div>';
        
        // Recargar opciones actualizadas
        $options = get_option( 'ygb_category_options', array() );
        foreach ( $default_colors as $key => $default ) {
            if ( ! isset( $options[ $key ] ) ) {
                $options[ $key ] = $default;
            }
        }
    }
    
    $custom_css = get_option( 'ygb_custom_css', '' );
    ?>
    <div class="wrap ygb-category-admin">
        <div class="ygb-admin-header">
            <h1><?php esc_html_e( 'YGB Category Showcase', 'ygb-category' ); ?></h1>
            <p><?php esc_html_e( 'Configura la visualización de las categorías de WooCommerce', 'ygb-category' ); ?></p>
            <div class="ygb-shortcode-info">
                <strong><?php esc_html_e( 'Shortcode principal:', 'ygb-category' ); ?></strong>
                <code>[ygb_categories]</code>
            </div>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field( 'ygb_save_settings' ); ?>
            
            <div class="ygb-settings-grid">
                <div class="ygb-settings-form">
                    <h2><?php esc_html_e( 'Configuración General', 'ygb-category' ); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th><label for="number"><?php esc_html_e( 'Número de categorías', 'ygb-category' ); ?></label></th>
                            <td>
                                <input type="number" id="number" name="number" value="<?php echo esc_attr( $options['number'] ); ?>" min="1" max="20" class="small-text">
                                <p class="description"><?php esc_html_e( 'Cantidad de categorías a mostrar (1-20)', 'ygb-category' ); ?></p>
                            </td>
                          </tr>
                        
                        <tr>
                            <th><label for="columns"><?php esc_html_e( 'Columnas', 'ygb-category' ); ?></label></th>
                            <td>
                                <input type="number" id="columns" name="columns" value="<?php echo esc_attr( $options['columns'] ); ?>" min="1" max="12" class="small-text">
                                <p class="description"><?php esc_html_e( 'Número de columnas en desktop (1-12)', 'ygb-category' ); ?></p>
                            </td>
                          </tr>
                        
                        <tr>
                            <th><label for="hide_empty"><?php esc_html_e( 'Ocultar categorías vacías', 'ygb-category' ); ?></label></th>
                            <td>
                                <input type="checkbox" id="hide_empty" name="hide_empty" value="1" <?php checked( $options['hide_empty'], true ); ?>>
                                <p class="description"><?php esc_html_e( 'No mostrar categorías sin productos', 'ygb-category' ); ?></p>
                            </td>
                          </tr>
                        
                        <tr>
                            <th><label><?php esc_html_e( 'Ordenar por', 'ygb-category' ); ?></label></th>
                            <td>
                                <select name="orderby">
                                    <option value="name" <?php selected( $options['orderby'], 'name' ); ?>><?php esc_html_e( 'Nombre', 'ygb-category' ); ?></option>
                                    <option value="count" <?php selected( $options['orderby'], 'count' ); ?>><?php esc_html_e( 'Número de productos', 'ygb-category' ); ?></option>
                                    <option value="slug" <?php selected( $options['orderby'], 'slug' ); ?>><?php esc_html_e( 'Slug', 'ygb-category' ); ?></option>
                                    <option value="term_group" <?php selected( $options['orderby'], 'term_group' ); ?>><?php esc_html_e( 'Grupo de términos', 'ygb-category' ); ?></option>
                                    <option value="term_order" <?php selected( $options['orderby'], 'term_order' ); ?>><?php esc_html_e( 'Orden personalizado', 'ygb-category' ); ?></option>
                                </select>
                                <select name="order">
                                    <option value="ASC" <?php selected( $options['order'], 'ASC' ); ?>><?php esc_html_e( 'Ascendente', 'ygb-category' ); ?></option>
                                    <option value="DESC" <?php selected( $options['order'], 'DESC' ); ?>><?php esc_html_e( 'Descendente', 'ygb-category' ); ?></option>
                                </select>
                            </td>
                          </tr>
                        
                        <tr>
                            <th><label for="show_count"><?php esc_html_e( 'Mostrar contador', 'ygb-category' ); ?></label></th>
                            <td>
                                <input type="checkbox" id="show_count" name="show_count" value="1" <?php checked( $options['show_count'], true ); ?>>
                                <p class="description"><?php esc_html_e( 'Mostrar número de productos por categoría', 'ygb-category' ); ?></p>
                            </td>
                          </tr>
                        
                        <tr>
                            <th><label for="show_description"><?php esc_html_e( 'Mostrar descripción', 'ygb-category' ); ?></label></th>
                            <td>
                                <input type="checkbox" id="show_description" name="show_description" value="1" <?php checked( $options['show_description'], true ); ?>>
                                <p class="description"><?php esc_html_e( 'Mostrar descripción de la categoría', 'ygb-category' ); ?></p>
                            </td>
                          </tr>
                        
                        <tr>
                            <th><label for="image_size"><?php esc_html_e( 'Tamaño de imagen', 'ygb-category' ); ?></label></th>
                            <td>
                                <select id="image_size" name="image_size">
                                    <option value="thumbnail" <?php selected( $options['image_size'], 'thumbnail' ); ?>><?php esc_html_e( 'Miniatura', 'ygb-category' ); ?></option>
                                    <option value="medium" <?php selected( $options['image_size'], 'medium' ); ?>><?php esc_html_e( 'Mediano', 'ygb-category' ); ?></option>
                                    <option value="large" <?php selected( $options['image_size'], 'large' ); ?>><?php esc_html_e( 'Grande', 'ygb-category' ); ?></option>
                                    <option value="full" <?php selected( $options['image_size'], 'full' ); ?>><?php esc_html_e( 'Original', 'ygb-category' ); ?></option>
                                </select>
                            </td>
                          </tr>
                        
                        <tr>
                            <th><label for="cache"><?php esc_html_e( 'Usar caché', 'ygb-category' ); ?></label></th>
                            <td>
                                <input type="checkbox" id="cache" name="cache" value="1" <?php checked( $options['cache'], true ); ?>>
                                <p class="description"><?php esc_html_e( 'Mejora el rendimiento almacenando los resultados en caché', 'ygb-category' ); ?></p>
                            </td>
                          </tr>
                    </table>
                    
                    <h2><?php esc_html_e( 'Personalización de Colores', 'ygb-category' ); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th><label for="card_bg_color"><?php esc_html_e( 'Fondo de tarjeta', 'ygb-category' ); ?></label></th>
                            <td>
                                <input type="color" id="card_bg_color" name="card_bg_color" value="<?php echo esc_attr( $options['card_bg_color'] ); ?>">
                                <p class="description"><?php esc_html_e( 'Color de fondo de cada tarjeta', 'ygb-category' ); ?></p>
                            </td>
                          </tr>
                        
                        <tr>
                            <th><label for="card_border_color"><?php esc_html_e( 'Borde de tarjeta', 'ygb-category' ); ?></label></th>
                            <td>
                                <input type="color" id="card_border_color" name="card_border_color" value="<?php echo esc_attr( $options['card_border_color'] ); ?>">
                                <p class="description"><?php esc_html_e( 'Color del borde de la tarjeta', 'ygb-category' ); ?></p>
                            </td>
                          </tr>
                        
                        <tr>
                            <th><label for="title_color"><?php esc_html_e( 'Color del título', 'ygb-category' ); ?></label></th>
                            <td>
                                <input type="color" id="title_color" name="title_color" value="<?php echo esc_attr( $options['title_color'] ); ?>">
                                <p class="description"><?php esc_html_e( 'Color del nombre de la categoría', 'ygb-category' ); ?></p>
                            </td>
                          </tr>
                        
                        <tr>
                            <th><label for="title_hover_color"><?php esc_html_e( 'Color del título (hover)', 'ygb-category' ); ?></label></th>
                            <td>
                                <input type="color" id="title_hover_color" name="title_hover_color" value="<?php echo esc_attr( $options['title_hover_color'] ); ?>">
                                <p class="description"><?php esc_html_e( 'Color al pasar el mouse', 'ygb-category' ); ?></p>
                            </td>
                          </tr>
                        
                        <tr>
                            <th><label for="desc_color"><?php esc_html_e( 'Color de la descripción', 'ygb-category' ); ?></label></th>
                            <td>
                                <input type="color" id="desc_color" name="desc_color" value="<?php echo esc_attr( $options['desc_color'] ); ?>">
                                <p class="description"><?php esc_html_e( 'Color del texto de descripción', 'ygb-category' ); ?></p>
                            </td>
                          </tr>
                        
                        <tr>
                            <th><label for="count_color"><?php esc_html_e( 'Color del contador', 'ygb-category' ); ?></label></th>
                            <td>
                                <input type="color" id="count_color" name="count_color" value="<?php echo esc_attr( $options['count_color'] ); ?>">
                                <p class="description"><?php esc_html_e( 'Color del contador de productos', 'ygb-category' ); ?></p>
                            </td>
                          </tr>
                    </table>
                    
                    <h2><?php esc_html_e( 'CSS Avanzado', 'ygb-category' ); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th><label for="custom_css"><?php esc_html_e( 'CSS Personalizado', 'ygb-category' ); ?></label></th>
                            <td>
                                <textarea id="custom_css" name="custom_css" rows="5" class="large-text code"><?php echo esc_textarea( $custom_css ); ?></textarea>
                                <p class="description"><?php esc_html_e( 'Añade tus propios estilos CSS (sin etiquetas &lt;style&gt;)', 'ygb-category' ); ?></p>
                            </td>
                          </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" name="submit" class="button button-primary" value="<?php esc_attr_e( 'Guardar Cambios', 'ygb-category' ); ?>">
                    </p>
                </div>
                
                <div class="ygb-sidebar">
                    <div class="ygb-help-box">
                        <h3><?php esc_html_e( 'Vista Previa', 'ygb-category' ); ?></h3>
                        <div class="ygb-color-preview" style="background: <?php echo esc_attr( $options['card_bg_color'] ); ?>; border: 1px solid <?php echo esc_attr( $options['card_border_color'] ); ?>; padding: 15px; border-radius: 8px; text-align: center;">
                            <h4 style="color: <?php echo esc_attr( $options['title_color'] ); ?>; margin: 0 0 8px 0;"><?php esc_html_e( 'Ejemplo: Electrónica', 'ygb-category' ); ?></h4>
                            <p style="color: <?php echo esc_attr( $options['desc_color'] ); ?>; font-size: 12px; margin: 0 0 8px 0;"><?php esc_html_e( 'Descripción de la categoría', 'ygb-category' ); ?></p>
                            <span style="color: <?php echo esc_attr( $options['count_color'] ); ?>; font-size: 11px;"><?php esc_html_e( '24 productos', 'ygb-category' ); ?></span>
                        </div>
                        
                        <h3><?php esc_html_e( 'Ayuda Rápida', 'ygb-category' ); ?></h3>
                        <p><strong><?php esc_html_e( 'Shortcode:', 'ygb-category' ); ?></strong><br>
                        <code>[ygb_categories]</code></p>
                        
                        <p><strong><?php esc_html_e( 'Ejemplo:', 'ygb-category' ); ?></strong><br>
                        <code>[ygb_categories number="6" columns="3"]</code></p>
                        
                        <p><a href="<?php echo esc_url( admin_url( 'admin.php?page=ygb-category-examples' ) ); ?>" class="button button-secondary">
                            <?php esc_html_e( 'Ver más ejemplos', 'ygb-category' ); ?>
                        </a></p>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <style>
    .ygb-category-admin {
        max-width: 1200px;
        margin: 20px auto;
    }
    .ygb-admin-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px 25px;
        border-radius: 12px;
        margin-bottom: 25px;
    }
    .ygb-admin-header h1 {
        color: white;
        margin: 0 0 10px 0;
    }
    .ygb-shortcode-info {
        background: rgba(255,255,255,0.2);
        padding: 10px 15px;
        border-radius: 8px;
        display: inline-block;
    }
    .ygb-shortcode-info code {
        background: rgba(0,0,0,0.3);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
    }
    .ygb-settings-grid {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 25px;
    }
    .ygb-settings-form {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .ygb-settings-form h2 {
        margin-top: 0;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
    }
    .ygb-settings-form h2:not(:first-of-type) {
        margin-top: 30px;
    }
    .ygb-sidebar {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }
    .ygb-help-box {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .ygb-help-box h3 {
        margin-top: 0;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    .ygb-help-box code {
        display: block;
        background: #f5f5f5;
        padding: 8px;
        margin: 8px 0;
        border-radius: 6px;
        font-size: 12px;
    }
    .ygb-color-preview {
        margin: 15px 0;
        transition: all 0.2s ease;
    }
    input[type="color"] {
        width: 80px;
        height: 40px;
        padding: 2px;
        border: 1px solid #ddd;
        border-radius: 6px;
        cursor: pointer;
    }
    @media (max-width: 768px) {
        .ygb-settings-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>
    <?php
}

/**
 * Página de ejemplos - CON VERIFICACIÓN DE NONCE
 */
function ygb_examples_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'No tienes permisos suficientes para acceder a esta página.', 'ygb-category' ) );
    }
    
    // Verificar nonce si hay acción POST
    if ( isset( $_POST['action'] ) && 'copy_shortcode' === $_POST['action'] ) {
        if ( ! check_admin_referer( 'ygb_examples_action', 'ygb_examples_nonce' ) ) {
            wp_die( esc_html__( 'Verificación de seguridad fallida.', 'ygb-category' ) );
        }
        // Procesar copia (esto normalmente es JS, pero por seguridad)
    }
    ?>
    <div class="wrap ygb-category-admin">
        <h1><?php esc_html_e( 'Ejemplos de Uso - YGB Category', 'ygb-category' ); ?></h1>
        
        <div class="ygb-examples-grid">
            <div class="ygb-example-card">
                <h3><?php esc_html_e( 'Básico', 'ygb-category' ); ?></h3>
                <div class="ygb-example-code">
                    <code>[ygb_categories]</code>
                    <button class="button button-small copy-btn" data-code="[ygb_categories]"><?php esc_html_e( 'Copiar', 'ygb-category' ); ?></button>
                </div>
            </div>
            
            <div class="ygb-example-card">
                <h3><?php esc_html_e( '6 categorías en 3 columnas', 'ygb-category' ); ?></h3>
                <div class="ygb-example-code">
                    <code>[ygb_categories number="6" columns="3"]</code>
                    <button class="button button-small copy-btn" data-code='[ygb_categories number="6" columns="3"]'><?php esc_html_e( 'Copiar', 'ygb-category' ); ?></button>
                </div>
            </div>
            
            <div class="ygb-example-card">
                <h3><?php esc_html_e( 'Orden por productos', 'ygb-category' ); ?></h3>
                <div class="ygb-example-code">
                    <code>[ygb_categories orderby="count" order="DESC"]</code>
                    <button class="button button-small copy-btn" data-code='[ygb_categories orderby="count" order="DESC"]'><?php esc_html_e( 'Copiar', 'ygb-category' ); ?></button>
                </div>
            </div>
            
            <div class="ygb-example-card">
                <h3><?php esc_html_e( 'Sin contador ni descripción', 'ygb-category' ); ?></h3>
                <div class="ygb-example-code">
                    <code>[ygb_categories show_count="false" show_description="false"]</code>
                    <button class="button button-small copy-btn" data-code='[ygb_categories show_count="false" show_description="false"]'><?php esc_html_e( 'Copiar', 'ygb-category' ); ?></button>
                </div>
            </div>
        </div>
        
        <div class="ygb-tips-card">
            <h3><?php esc_html_e( '💡 Uso en PHP', 'ygb-category' ); ?></h3>
            <code>&lt;?php echo do_shortcode( '[ygb_categories]' ); ?&gt;</code>
            
            <h3><?php esc_html_e( '🎨 CSS Personalizado', 'ygb-category' ); ?></h3>
            <pre>.ygb-grid .ygb-card {
    border-radius: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}</pre>
            
            <h3><?php esc_html_e( '🔧 Filtros disponibles', 'ygb-category' ); ?></h3>
            <code>add_filter( 'ygb_cache_expiration', function() { return 2 * HOUR_IN_SECONDS; } );</code>
        </div>
    </div>
    
    <?php wp_nonce_field( 'ygb_examples_action', 'ygb_examples_nonce' ); ?>
    
    <script>
    jQuery(document).ready(function($) {
        $('.copy-btn').on('click', function() {
            var code = $(this).data('code');
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(code).then(function() {
                    var btn = $(this);
                    var original = btn.text();
                    btn.text('<?php esc_html_e( '¡Copiado!', 'ygb-category' ); ?>');
                    setTimeout(function() {
                        btn.text(original);
                    }, 1500);
                }.bind(this));
            }
        });
    });
    </script>
    
    <style>
    .ygb-examples-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .ygb-example-card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .ygb-example-code {
        background: #f5f5f5;
        padding: 12px;
        border-radius: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }
    .ygb-example-code code {
        flex: 1;
        font-size: 12px;
        background: transparent;
    }
    .ygb-tips-card {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        padding: 20px;
        border-radius: 12px;
    }
    .ygb-tips-card code, .ygb-tips-card pre {
        background: rgba(0,0,0,0.1);
        padding: 10px;
        border-radius: 8px;
        display: block;
        margin: 10px 0;
    }
    </style>
    <?php
}