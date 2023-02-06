<?php
/**
 * Functions to activate, initiate and deactivate the plugin.
 *
 * @author  Marco Di Bella
 * @package mdb-license-management
 */

namespace mdb_license_management;


/** Prevent direct access */

defined( 'ABSPATH' ) or exit;



/**
 * The init function for the plugin.
 *
 * @since 0.0.3
 */

function plugin_init()
{
    // Load text domain, use relative path to the plugin's language folder
    load_plugin_textdomain( 'mdb-license-management', false, plugin_basename( PLUGIN_DIR ) . '/languages' );
}

add_action( 'init', __NAMESPACE__ . '\plugin_init', 9 );



/**
 * The activation function for the plugin.
 *
 * @since 0.0.1
 */

function plugin_activation()
{
    global $wpdb;

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );


    /**
     * 1. Tabelle für Lizenzen
     */

    $charset_collate = $wpdb->get_charset_collate();
    $table_name      = $wpdb->prefix . 'mdb_lv_licenses';

    if( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name) :

        // Tabelle installieren
        $sql = "CREATE TABLE $table_name (
            license_guid varchar(4) DEFAULT '' NOT NULL,
            license_term varchar(50) DEFAULT '' NOT NULL,
            license_description text NOT NULL,
            license_link varchar(255) DEFAULT '' NOT NULL,
            license_version varchar(20) DEFAULT '' NOT NULL,
            PRIMARY KEY  (license_guid)
            )
            COLLATE utf8_general_ci;";

        dbDelta( $sql );


        // Preset laden
        if( false !== ( $file_handle = fopen( __DIR__ . "/assets/csv/preset.csv", "r" ) ) ) :
            $file_row = 1;

            while( false !== ( $file_data = fgetcsv( $file_handle, 1000, ",", "'" ) ) ) :

                // Ab zweiter Zeile; erste Zeile mit Titelfeldern ignorieren
                if( 1 != $file_row ) :
                    $table_format = array( '%s', '%s', '%s', '%s', '%s' );
                    $table_data   = array(
                        'license_guid'        => $file_data[0],
                        'license_term'        => $file_data[1],
                        'license_description' => $file_data[2],
                        'license_link'        => $file_data[3],
                        'license_version'     => $file_data[4]
                    );
                    $wpdb->insert( $table_name, $table_data, $table_format );
                endif;

                $file_row++;
            endwhile;

            fclose( $file_handle );
        endif;
    endif;


    /**
     * 2. Tabelle für Medien
     */
    $table_name = $wpdb->prefix . 'mdb_lv_media';

    if( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name) :

        // Tabelle installieren
        $sql = "CREATE TABLE $table_name (
            media_id bigint(20) UNSIGNED NOT NULL,
            media_link varchar(255) DEFAULT '' NOT NULL,
            media_state int(8) UNSIGNED DEFAULT 0,
            license_guid varchar(4) DEFAULT '' NOT NULL,
            by_name varchar(255) DEFAULT '' NOT NULL,
            by_link varchar(255) DEFAULT '' NOT NULL,
            PRIMARY KEY  (media_id)
            )
            COLLATE utf8_general_ci;";

        dbDelta( $sql );
    endif;
}

register_activation_hook( __FILE__, __NAMESPACE__ . '\plugin_activation' );



/**
 * The deactivation function for the plugin.
 *
 * @since 0.0.3
 */

function plugin_deactivation()
{
    // Do something!
}

register_deactivation_hook( __FILE__, __NAMESPACE__ . '\plugin_deactivation' );



/**
 * The uninstall function for the plugin.
 *
 * @since 0.0.3
 */

function plugin_uninstall()
{
    // Do something!
    // Delete options!
    // Delete custom tables!
}

register_uninstall_hook( __FILE__, __NAMESPACE__ . '\plugin_uninstall' );