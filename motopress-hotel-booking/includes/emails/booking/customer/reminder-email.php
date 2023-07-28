<?php
namespace MPHB\Emails\Booking\Customer;
class ReminderEmail extends BaseEmail {
	public function getDefaultMessageHeaderText(){
		return __( 'Reminder Email', 'motopress-hotel-booking' );
	}
	public function getDefaultSubject(){
		return __( "Dear %customer_first_name% %customer_last_name%, don't forget your tomorrow reservation.", 'motopress-hotel-booking' );
	}
	protected function initDescription(){
		$this->description = __( 'Email that will be sent to customer automatically before 1 day.', 'motopress-hotel-booking' );
	}
	protected function initLabel(){
		$this->label = __( 'Reminder Email', 'motopress-hotel-booking' );
	}
}