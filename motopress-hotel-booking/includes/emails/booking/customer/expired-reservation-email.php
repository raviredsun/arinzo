<?php

namespace MPHB\Emails\Booking\Customer;

class ExpiredReservationEmail extends BaseEmail {

	public function getDefaultMessageHeaderText(){
		return __( 'Your booking is Expired Reservation', 'motopress-hotel-booking' );
	}

	public function getDefaultSubject(){
		return __( '%site_title% - Your booking #%booking_id% is Expired Reservation', 'motopress-hotel-booking' );
	}

	protected function initDescription(){
		$this->description = __( 'Email that will be sent to customer when booking is Expired Reservation.', 'motopress-hotel-booking' );
	}

	protected function initLabel(){
		$this->label = __( 'Expired Reservation Booking Email', 'motopress-hotel-booking' );
	}

}
