<?php
/**
 * Functions for extending the media library.
 *
 * @author  Marco Di Bella
 * @package mdb-license-management
 */

namespace mdb_license_management;


/** Prevent direct access */

defined( 'ABSPATH' ) or exit;



/**
 * Adds a column on copyright in the media overview.
 *
 * @since 0.0.1
 *
 * @param array $columns The columns available in the media overview.
 *
 * @return array The modified columns.
 */

function add_custom_column( $columns )
{
    $columns['mdb_lv_credits'] = __( 'Copyright', 'mdb-license-management' );
    return $columns;
}

add_filter( 'manage_media_columns', __NAMESPACE__ . '\add_custom_column');



/**
 * Displays the copyright column in the media overview.
 *
 * @since 0.0.1
 *
 * @param string $column The column to be displayed.
 * @param int    $id     The post_ID of the media attachment.
 */

function show_custom_column( $column, $id )
{
    if( 'mdb_lv_credits' == $column  ) :

        $record = new Media_Record( $id );


        switch( $data['media_state'] ) :

            case MEDIA_STATE_NO_CREDIT:
                echo __( 'no copyright information necessary', 'mdb-license-management' );
            break;

            case MEDIA_STATE_SIMPLE_CREDIT:
                echo $record->get_by_name();
            break;

            case MEDIA_STATE_LICENSED:



                $data2 = mdb_license_management\get_license_record( $data['license_guid'] );

                if( ( null != $data2 ) and ( true == is_array( $data2 ) ) ) :
                    echo $data['by_name'] . '<br>' . $data2['license_term'];
                endif;
            break;


            case MEDIA_STATE_UNKNOWN:
            default:
                echo __( 'unknown', 'mdb-license-management' );
            break;

        endswitch;

    endif;

}

add_action( 'manage_media_custom_column', __NAMESPACE__ . '\show_custom_column', 10, 2 );



/**
 * Adds a number of additional form fields for files in the library.
 *
 * @since  0.0.1
 *
 * @param array   $form_fields The available form fields of the media attachment.
 * @param WP_Post $post        The media attachment.
 *
 * @return array The modified form fields.
 */

function add_attachment_fields( $form_fields, $post )
{
    $data = mdb_license_management\get_media_record( $post->ID );

    if( null == $data ) :
        $data['media_id']     = $post->ID;
        $data['media_link']   = '';
        $data['media_state']  = '';
        $data['license_guid'] = '';
        $data['by_name']      = '';
        $data['by_link']      = '';
    endif;

    extract( $data );


    /** Field 1 - status of the media registration or indication of the type & manner of the copyright indication */

    $states = array(
        MEDIA_STATE_NO_CREDIT     => __( 'no copyright information necessary', 'mdb-license-management' ),
        MEDIA_STATE_SIMPLE_CREDIT => __( 'simple naming (with linking if necessary)', 'mdb-license-management' ),
        MEDIA_STATE_LICENSED      => __( 'copyright information according to license', 'mdb-license-management' ),
    );

    $html  = "<select id='mdb-lv-media-state' name='attachments[{$post->ID}][mdb-lv-media-state]'>";
    $html .= sprintf(
        '<option value="0" disabled %2$s>%1$s</option>',
        __( '--- please select ---', 'mdb-license-management' ),
        ( MEDIA_STATE_UNKNOWN == $media_state )? 'selected' : ''
    );

    foreach ( $states as $state => $description ) :
        $html .= sprintf(
            '<option value="%1$s" %3$s>%2$s</option>',
            $state,
            $description,
            ( $state == $media_state )? 'selected' : ''
        );
    endforeach;

    $html .= '</select>';

    $form_fields['mdb-lv-media-state'] = array(
        'label' => __( 'Method and manner of the copyright information', 'mdb-license-management' ),
        'input' => 'html',
        'html'  => $html,
    );


    /** Field 2 - listing of available licenses */

    global $wpdb;

    $table_name = $wpdb->prefix . table_licenses;
    $table_data = $wpdb->get_results( "SELECT license_guid, license_term FROM $table_name", 'ARRAY_A' );

    $html  = "<select id='mdb-lv-license-guid' name='attachments[{$post->ID}][mdb-lv-license-guid]'>";
    $html .= sprintf(
        '<option value="%1$s" disabled %3$s>%2$s</option>',
        '0',
        __( '--- please select ---', 'mdb-license-management' ),
        ( 0 == $license_guid )? 'selected' : ''
    );

    foreach ( $table_data as $data ) :
        $html .= sprintf(
            '<option value="%1$s" %3$s>%2$s</option>',
            $data['license_guid'],
            $data['license_term'],
            ( $data['license_guid'] == $license_guid )? 'selected' : ''
        );
    endforeach;

    $html .= '</select>';

    $form_fields['mdb-lv-license-guid'] = array(
        'label' => __( 'License', 'mdb-license-management' ),
        'input' => 'html',
        'html'  => $html,
    );


    /** Field 3 - naming of the creator */

    $form_fields['mdb-lv-by-name'] = array(
        'label' => __( 'Naming of the creator', 'mdb-license-management' ),
        'input' => 'html',
        'html'  => "<input type='text' size='128' class='widefat' value='" . $by_name . "' name='attachments[{$post->ID}][mdb-lv-by-name]'>",
    );


    /** Field 4 - link to the creator's website (if required) */

    $form_fields['mdb-lv-by-link'] = array(
        'label' => __( 'Link to the creator', 'mdb-license-management' ),
        'input' => 'html',
        'html'  => "<input type='url' size='128' class='widefat' value='" . esc_url( $by_link ) . "' name='attachments[{$post->ID}][mdb-lv-by-link]'>",
    );


    /** Field 5 - link to the original image for your own documentation */

    $form_fields[ 'mdb-lv-media-link' ] = array(
        'label' => __( 'Link to original file', 'mdb-license-management' ),
        'input' => 'html',
        'html'  => "<input type='url' size='128' class='widefat' value='" . esc_url( $media_link ) . "' name='attachments[{$post->ID}][mdb-lv-media-link]'>",
    );


    return $form_fields;
}

add_filter( 'attachment_fields_to_edit', __NAMESPACE__ . '\add_attachment_fields', null, 2 );



/**
 * Stores the values of the additional form fields in the database.
 *
 * @since 0.0.1
 *
 * @param array $post           An array with post data.
 * @param array $attachment     An array of metadata about the attachment.
 *
 * @return array    The $post array.
 */

function save_attachment_fields( $post, $attachment )
{
    $record = new Media_Record( $post['ID'] );

    $record->set_media_link( $attachment['mdb-lv-media-link'] );
    $record->set_media_state( $attachment['mdb-lv-media-state'] );
    $record->set_license_guid( $attachment['mdb-lv-license-guid'] );
    $record->set_by_name( $attachment['mdb-lv-by-name'] );
    $record->set_by_link( $attachment['mdb-lv-by-link'] );

    $record->update_table_record();

    return $post;
}

add_filter( 'attachment_fields_to_save', __NAMESPACE__ . '\save_attachment_fields', null, 2 );



/**
 * Creates a new record in the media table of the plugin after a media has been loaded into the media library.
 *
 * @since 0.0.1
 *
 * @param int $id   The media attachment ID.
 */
/*
function add_attachment_handler( $id )
{
    $mime = get_post_mime_type( $id );

    if( 0 === strpos( $mime, 'image' ) ) :
        global $wpdb;

        $table_name   = $wpdb->prefix . table_media;
        $table_format = array( '%d', '%s', '%d', '%s', '%s', '%s' );
        $table_data   = array(
            'media_id'     => $id,
            'media_link'   => '',
            'media_state'  => 0,
            'license_guid' => '',
            'by_name'      => '',
            'by_link'      => ''
        );

        $wpdb->insert( $table_name, $table_data, $table_format );
    endif;
}

add_action( 'add_attachment', __NAMESPACE__ . '\add_attachment_handler');
*/


/**
 * Deletes a media from the media table of the plugin.
 *
 * @since 0.0.1
 *
 * @param int $id   The media attachment ID
 */

function delete_attachment_handler( $id )
{
    $record = new Media_Record( $id );

    $record->remove_table_record();
}

add_action( 'delete_attachment', __NAMESPACE__ . '\delete_attachment_handler');