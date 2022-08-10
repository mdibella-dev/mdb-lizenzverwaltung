<?php
/**
 * Klasse zur Anzeige der Lizenzübersicht
 *
 * @author  Marco Di Bella
 * @package mdb-lv
 * @see     http://wpengineer.com/2426/wp_list_table-a-step-by-step-guide/
 * @see     https://wp.smashingmagazine.com/2011/11/native-admin-tables-wordpress/
 */

defined( 'ABSPATH' ) or exit;




if( ! class_exists( 'WP_List_Table' ) ) :
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
endif;



class MDB_main_table extends WP_List_Table {

    function get_columns() {
        $columns = array(
            'license_term'        => __( 'Lizenz', 'mdb_lv' ),
            'license_description' => __( 'Beschreibung', 'mdb_lv' ),
            'license_link'        => __( 'Lizenztext', 'mdb_lv' )
        );

        return $columns;
    }


    function prepare_items() {
        $this->_column_headers = array(
            $this->get_columns(),       // columns
            array(),                    // hidden
            array(),                    // sortable
        );

        // Lade Daten aus der Datentabelle
        global $wpdb;

        $table_name  = $wpdb->prefix . 'mdb_lv_licenses';
        $table_data  = $wpdb->get_results( "SELECT * FROM $table_name", 'ARRAY_A' );
        $this->items = $table_data;
    }


    function column_default( $item, $column_name ) {
        switch( $column_name ) :
            case 'license_term':
            case 'license_description':
                return $item[$column_name];
            break;

            case 'license_link':
                return sprintf(
                    '<a href="%1$s" title="%2$s" target="_blank">%3$s</a>',
                    $item[$column_name],
                    __( 'Link zum Lizenztext', 'mdb_lv' ),
                    __( 'Anzeigen', 'mdb_lv' )
                );
            break;

            default:
                // Datenausgabe bei Fehlern
                return print_r( $item, true ) ;
        endswitch;
    }
}