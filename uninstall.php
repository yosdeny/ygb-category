<?php
/**
 * Uninstall YGB Category Showcase
 * 
 * Limpia todas las opciones y transientes del plugin
 * 
 * @package YGB_Category
 * @version 3.4.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit; // Salir si no se está desinstalando
}

// NOTA: WordPress ya verifica que el usuario tenga permisos de administrador
// antes de ejecutar uninstall.php. No es necesaria verificación adicional.

// Eliminar opciones principales
delete_option( 'ygb_category_options' );
delete_option( 'ygb_custom_css' );

// Limpiar transientes de manera segura
global $wpdb;

// Eliminar transientes específicos del plugin
$result = $wpdb->query( $wpdb->prepare(
    "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
    '_transient_ygb_cats_%'
) );

// Eliminar timeouts de transientes
$result_timeout = $wpdb->query( $wpdb->prepare(
    "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
    '_transient_timeout_ygb_cats_%'
) );

// Limpiar cualquier otra opción residual con prefijo ygb_
$wpdb->query( $wpdb->prepare(
    "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
    'ygb_%'
) );

// Registrar limpieza en log solo si hay error y WP_DEBUG_LOG está activado
if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
    if ( false === $result ) {
        error_log( 'YGB Category: Error al limpiar transientes durante desinstalación' );
    }
    if ( false === $result_timeout ) {
        error_log( 'YGB Category: Error al limpiar timeouts de transientes durante desinstalación' );
    }
}