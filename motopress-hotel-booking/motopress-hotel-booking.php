<?php



/*

 * Plugin Name: Hotel Booking

 * Plugin URI: https://motopress.com/products/hotel-booking/

 * Description: Manage your hotel booking Food & Beverage. Perfect for hotels, villas, guest houses, hostels, and apartments of all sizes.

 * Version: 3.8.2

 * Author: MotoPress

 * Author URI: https://motopress.com/

 * License: GPLv2 or later

 * Text Domain: motopress-hotel-booking

 * Domain Path: /languages

 */



if ( !class_exists( 'HotelBookingPlugin' ) ) {

	define( 'MPHB_PLUGIN_FILE', __FILE__ );



	require( plugin_dir_path( __FILE__ ) . 'plugin.php' );

}