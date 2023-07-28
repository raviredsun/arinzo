<?php

namespace MPHB\Emails\Booking\Customer;

class BeforeArrivalEmail extends BaseEmail {

	public function getDefaultMessageHeaderText(){
		return __( 'Before Arrival Time', 'motopress-hotel-booking' );
	}

	public function getDefaultSubject(){
		return __( '%site_title% - Subject here', 'motopress-hotel-booking' );
	}

	protected function initDescription(){
		$this->description = __( 'Email that will be sent to customer automatically.', 'motopress-hotel-booking' );
	}

	protected function initLabel(){
		$this->label = __( 'Before Arrival Time', 'motopress-hotel-booking' );
	}

}
