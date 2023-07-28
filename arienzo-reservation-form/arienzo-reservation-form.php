<?php
/**
 * Plugin Name: Arienzo Reservation Form
 * Plugin URI: https://qadisha.it/
 * Description:
 * Version: 1.0
 * Author: Sasun Sakanyan
 * Text Domain: arienzo_reservation_form
 * Author URI: https://qadisha.it/
 */
if (!defined('WPINC')) {
    die;
}


function arf_create_table()
{
    global $wpdb;
    $arf = apply_filters('arf_database', $wpdb);
    $table_name = $arf->prefix . 'arf_orders';

    if ($arf->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

        $charset_collate = $arf->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
user_firstname varchar(80) NOT NULL,
user_lastname varchar(80) NOT NULL,
user_email varchar(80) NOT NULL,
user_phone varchar(80) NOT NULL,
reservation_start_date varchar(80),
reservation_end_date varchar(80),
room_type tinyint(20) unsigned NOT NULL,
beach_arrival_time TIME,
lunch_time TIME,
people tinyint DEFAULT 0,
child tinyint DEFAULT 0,
payment_status enum('pending','approved','declined') default NULL,
created_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
updated_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

function arf_on_activate($network_wide)
{
    add_option('arf_mail_templates', array());
    arf_create_table();

}

register_activation_hook(__FILE__, 'arf_on_activate');


include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if (!is_plugin_active('motopress-hotel-booking/motopress-hotel-booking.php')) {
    add_action('admin_notices', 'arf_admin_notice');
}
else {
    require_once plugin_dir_path(__FILE__) . 'functions.php';
}

function arf_admin_notice(){
    ?>
    <div class="error">
        <p><?php _e('Arienzo Reservation Form requires the Hotel Booking(MotoPress) plugin. Please install and activate Hotel Booking(MotoPress) first, then activate this plugin.', 'arienzo_reservation_form'); ?></p>
    </div>
    <?php
    deactivate_plugins(plugin_basename(__FILE__));
}