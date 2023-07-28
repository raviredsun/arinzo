<?php

namespace MPHB\Emails\Booking\Customer;

class BeforeArrivalPreReminderEmail extends BaseEmail {

	public function getDefaultMessageHeaderText(){
		return __( 'Before Arrival Pre Reminder Time', 'motopress-hotel-booking' );
	}

	public function getDefaultSubject(){
		return __( '%site_title% - Subject here', 'motopress-hotel-booking' );
	}

	protected function initDescription(){
		$this->description = __( 'Email that will be sent to customer automatically.', 'motopress-hotel-booking' );
	}

	protected function initLabel(){
		$this->label = __( 'Before Arrival Pre Reminder Time', 'motopress-hotel-booking' );
	}

}