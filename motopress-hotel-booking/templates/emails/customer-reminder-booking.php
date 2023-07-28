<?php
/**
 * The Template for Booking Reminder Email content
 *
 * Email that will be sent to customer before 1day.
 *
 * @version 2.0.0
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php printf( __( 'Dear %1$s %2$s, don\'t forget your tomorrow reservation.', 'motopress-hotel-booking' ), '%customer_first_name%', '%customer_last_name%' ); ?>
<br/><br/>
<?php _e( 'Dear guest,', 'motopress-hotel-booking' ); ?><br/>
<?php _e( 'greetings from Arienzo Beach Club!', 'motopress-hotel-booking' ); ?><br/>
<br/>
<?php _e( 'We look forward to welcoming you tomorrow morning!', 'motopress-hotel-booking' ); ?><br/>
<br/>
<?php _e( 'Do you need assistance? Click on the link below:', 'motopress-hotel-booking' ); ?><br/>
<?php _e( 'https://wa.me/message/SYKP76S33ZCFO1', 'motopress-hotel-booking' ); ?><br/>
<br/>
<?php _e( 'SEE YOU TOMORROW!', 'motopress-hotel-booking' ); ?><br/>
