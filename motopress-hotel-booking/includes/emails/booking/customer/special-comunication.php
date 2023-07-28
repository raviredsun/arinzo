<?php
namespace MPHB\Emails\Booking\Customer;

class SpecialComunication extends BaseEmail {

	public function getDefaultMessageHeaderText(){
		return __( 'Special comunication', 'motopress-hotel-booking' );
	}

	public function getDefaultSubject(){
		return __( '%site_title% - Booking #%booking_id%', 'motopress-hotel-booking' );
	}

	protected function initDescription(){
		$userConfirmationNote	 = '&nbsp<strong>' . __( 'Special comunication', 'motopress-hotel-booking' ) . '</strong>';
		$this->description		 = $userConfirmationNote;
	}

	protected function initLabel(){
		$this->label = __( 'Special comunication', 'motopress-hotel-booking' );
	}

}
