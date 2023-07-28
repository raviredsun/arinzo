<?php
if (!defined('WPINC')) {
    die;
}

function create_table_post_type()
{
    $labels = array(
        'name' => __('Tables', 'arienzo_reservation_form'),
        'singular_name' => __('Table', 'arienzo_reservation_form'),
        'add_new' => _x('Add Table', 'Add New Table', 'arienzo_reservation_form'),
        'add_new_item' => __('Add New Table', 'arienzo_reservation_form'),
        'edit_item' => __('Edit Table', 'arienzo_reservation_form'),
        'new_item' => __('New Table', 'arienzo_reservation_form'),
        'view_item' => __('View Table', 'arienzo_reservation_form'),
        'menu_name' => __('Accommodation', 'arienzo_reservation_form'),
        'search_items' => __('Search Table', 'arienzo_reservation_form'),
        'not_found' => __('No Tables found', 'arienzo_reservation_form'),
        'not_found_in_trash' => __('No Tables found in Trash', 'arienzo_reservation_form'),
        'all_items' => __('Tables', 'arienzo_reservation_form'),
        'insert_into_item' => __('Insert into Table description', 'arienzo_reservation_form'),
        'uploaded_to_this_item' => __('Uploaded to this Table', 'arienzo_reservation_form')
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'capability_type' => 'post',
        'has_archive' => true,
        'show_in_menu' => false,
        'supports' => array('title', 'editor', 'page-attributes'),
        'hierarchical' => false,

        'rewrite' => array(
            //translators: do not translate
            'slug' => _x('table', 'slug', 'arienzo_reservation_form'),
            'with_front' => false,
            'feeds' => true
        ),
        'query_var' => true,
        'show_in_rest' => true

    );
    register_post_type('arf_pt_table', $args);
}

add_action('init', 'create_table_post_type');

function arf_add_subtitle_pt_table()
{
    add_submenu_page('edit.php?post_type=mphb_room_type', __('Tables', 'arienzo_reservation_form'), __('Tables', 'arienzo_reservation_form'), 'manage_options', 'edit.php?post_type=arf_pt_table');
}

add_action('admin_menu', 'arf_add_subtitle_pt_table');


