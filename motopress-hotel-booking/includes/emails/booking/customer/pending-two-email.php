<?php
namespace MPHB\Emails\Booking\Customer;

class PendingTwoEmail extends BaseEmail {

	public function getDefaultMessageHeaderText(){
		return __( 'Pending Mail', 'motopress-hotel-booking' );
	}

	public function getDefaultSubject(){
		return __( '%site_title% - Booking #%booking_id%', 'motopress-hotel-booking' );
	}

	protected function initDescription(){
		$userConfirmationNote	 = '&nbsp<strong>' . __( 'Pending Mail', 'motopress-hotel-booking' ) . '</strong>';
		$this->description		 = $userConfirmationNote;
	}

	protected function initLabel(){
		$this->label = __( 'Pending 2 Mail', 'motopress-hotel-booking' );
	}

}
