<?php
namespace MPHB\PostTypes\BookingCPT;

use \MPHB\PostTypes\AbstractCPT;

class Statuses extends AbstractCPT\Statuses {

	const STATUS_CONFIRMED		 = 'confirmed';
	const STATUS_PENDING			 = 'pending';
	const STATUS_PENDING_USER		 = 'pending-user';
	const STATUS_PENDING_PAYMENT	 = 'pending-payment';
	const STATUS_PENDING_TWO	 = 'pending-2';
	const STATUS_SPECIAL_COMUNICATION	 = 'special-comunication';
	const STATUS_CANCELLED		 = 'cancelled';
	const STATUS_ABANDONED		 = 'abandoned';
	const STATUS_AUTO_DRAFT		 = 'auto-draft';
	const STATUS_SPECIAL_CANCELLATION	 = 'special-cancellation';
	const STATUS_EXPIRED_RESERVATION	 = 'expired-reservation';
	const STATUS_ARCHIVE	 = 'archive';
	const STATUS_ARCHIVE_CONFIRMED	 = 'confirmed-archived';
	const STATUS_ARCHIVE_PENDING	 = 'pending-archived';
	const STATUS_ARCHIVE_DELETE	 = 'delete-archived';
	const STATUS_NOT_PAID	 = 'not-paid';
	const STATUS_PAID_NOT_REFUNDABLE	 = 'paid_not_refundable';
	const STATUS_PAID_REFUNDABLE	 = 'paid_refundable';
	const STATUS_LAST_MINUTE	 = 'last_minute';
	const STATUS_PENDING_LATE_CHARGE	 = 'pending_late_charge';
	const STATUS_PAID_LATE_CHARGE	 = 'paid_late_charge';

	public function __construct( $postType ){
		parent::__construct( $postType );
		add_action( 'transition_post_status', array( $this, 'transitionStatus' ), 10, 3 );
	}

	protected function initStatuses(){

		$this->statuses[self::STATUS_PENDING_USER] = array(
			'lock_room' => true
		);

		$this->statuses[self::STATUS_PENDING_PAYMENT] = array(
			'lock_room' => true
		);


		$this->statuses[self::STATUS_PENDING] = array(
			'lock_room' => true
		);
		$this->statuses[self::STATUS_PENDING_TWO] = array(
			'lock_room' => true
		);
		$this->statuses[self::STATUS_SPECIAL_COMUNICATION] = array(
			'lock_room' => true
		);

		$this->statuses[self::STATUS_ABANDONED] = array(
			'lock_room' => false
		);

		$this->statuses[self::STATUS_CONFIRMED] = array(
			'lock_room' => true
		);

		$this->statuses[self::STATUS_CANCELLED] = array(
			'lock_room' => false
		);

		$this->statuses[self::STATUS_SPECIAL_CANCELLATION] = array(
			'lock_room' => false
		);
	    
	    $this->statuses[self::STATUS_EXPIRED_RESERVATION] = array(
			'lock_room' => false
		);
		
	    $this->statuses[self::STATUS_ARCHIVE] = array(
			'lock_room' => false
		);		
	    $this->statuses[self::STATUS_ARCHIVE_CONFIRMED] = array(
			'lock_room' => false
		);		
	    $this->statuses[self::STATUS_ARCHIVE_PENDING] = array(
			'lock_room' => false
		);		
	    $this->statuses[self::STATUS_ARCHIVE_DELETE] = array(
			'lock_room' => false
		);
	    $this->statuses[self::STATUS_NOT_PAID] = array(
			'lock_room' => false
		);
	    $this->statuses[self::STATUS_PAID_NOT_REFUNDABLE] = array(
			'lock_room' => false
		);
	    $this->statuses[self::STATUS_PAID_REFUNDABLE] = array(
			'lock_room' => false
		);
	    $this->statuses[self::STATUS_LAST_MINUTE] = array(
			'lock_room' => false
		);
	    $this->statuses[self::STATUS_PENDING_LATE_CHARGE] = array(
			'lock_room' => false
		);
	    $this->statuses[self::STATUS_PAID_LATE_CHARGE] = array(
			'lock_room' => false
		);		
	}

	public function getStatusArgs( $statusName ){
		$args = array();

		switch ( $statusName ) {
			case self::STATUS_PENDING_USER:
				$args	 = array(
					'label'						 => _x( 'Pending User Confirmation', 'Booking status', 'motopress-hotel-booking' ),
					'public'					 => true,
					'exclude_from_search'		 => false,
					'show_in_admin_all_list'	 => true,
					'show_in_admin_status_list'	 => true,
					'label_count'				 => _n_noop( 'Pending User Confirmation <span class="count">(%s)</span>', 'Pending User Confirmation <span class="count">(%s)</span>', 'motopress-hotel-booking' )
				);
				break;
			case self::STATUS_PENDING_PAYMENT:
				$args	 = array(
					'label'						 => _x( 'Pending Payment', 'Booking status', 'motopress-hotel-booking' ),
					'public'					 => true,
					'exclude_from_search'		 => false,
					'show_in_admin_all_list'	 => true,
					'show_in_admin_status_list'	 => true,
					'label_count'				 => _n_noop( 'Pending Payment <span class="count">(%s)</span>', 'Pending Payment <span class="count">(%s)</span>', 'motopress-hotel-booking' )
				);
				break;
			case self::STATUS_PENDING:
				$args	 = array(
					'label'						 => _x( 'Pending Admin', 'Booking status', 'motopress-hotel-booking' ),
					'public'					 => true,
					'exclude_from_search'		 => false,
					'show_in_admin_all_list'	 => true,
					'show_in_admin_status_list'	 => true,
					'label_count'				 => _n_noop( 'Pending Admin <span class="count">(%s)</span>', 'Pending Admin <span class="count">(%s)</span>', 'motopress-hotel-booking' )
				);
				break;
			case self::STATUS_PENDING_TWO:
				$args	 = array(
					'label'						 => _x( 'Pending 2', 'Booking status', 'motopress-hotel-booking' ),
					'public'					 => true,
					'exclude_from_search'		 => false,
					'show_in_admin_all_list'	 => true,
					'show_in_admin_status_list'	 => true,
					'label_count'				 => _n_noop( 'Pending 2 <span class="count">(%s)</span>', 'Pending 2 <span class="count">(%s)</span>', 'motopress-hotel-booking' )
				);
				break;
			case self::STATUS_SPECIAL_COMUNICATION:
				$args	 = array(
					'label'						 => _x( 'Special Comunication', 'Booking status', 'motopress-hotel-booking' ),
					'public'					 => true,
					'exclude_from_search'		 => false,
					'show_in_admin_all_list'	 => true,
					'show_in_admin_status_list'	 => true,
					'label_count'				 => _n_noop( 'Special Comunication <span class="count">(%s)</span>', 'Special Comunication <span class="count">(%s)</span>', 'motopress-hotel-booking' )
				);
				break;
			case self::STATUS_ABANDONED:
				$args	 = array(
					'label'						 => _x( 'Abandoned', 'Booking status', 'motopress-hotel-booking' ),
					'public'					 => true,
					'exclude_from_search'		 => false,
					'show_in_admin_all_list'	 => true,
					'show_in_admin_status_list'	 => true,
					'label_count'				 => _n_noop( 'Abandoned <span class="count">(%s)</span>', 'Abandoned <span class="count">(%s)</span>', 'motopress-hotel-booking' )
				);
				break;
			case self::STATUS_CONFIRMED:
				$args	 = array(
					'label'						 => _x( 'Confirmed', 'Booking status', 'motopress-hotel-booking' ),
					'public'					 => true,
					'exclude_from_search'		 => false,
					'show_in_admin_all_list'	 => true,
					'show_in_admin_status_list'	 => true,
					'label_count'				 => _n_noop( 'Confirmed <span class="count">(%s)</span>', 'Confirmed <span class="count">(%s)</span>', 'motopress-hotel-booking' )
				);
				break;
			case self::STATUS_CANCELLED:
				$args	 = array(
					'label'						 => _x( 'Cancelled', 'Booking status', 'motopress-hotel-booking' ),
					'public'					 => true,
					'exclude_from_search'		 => true,
					'show_in_admin_all_list'	 => false,
					'show_in_admin_status_list'	 => true,
					'label_count'				 => _n_noop( 'Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>', 'motopress-hotel-booking' )
				);
				break;
				case self::STATUS_SPECIAL_CANCELLATION:
				$args	 = array(
					'label'						 => _x( 'Special cancellation (due bad wheater)', 'Booking status', 'motopress-hotel-booking' ),
					'public'					 => true,
					'exclude_from_search'		 => true,
					'show_in_admin_all_list'	 => false,
					'show_in_admin_status_list'	 => true,
					'label_count'				 => _n_noop( 'Special Cancellation <span class="count">(%s)</span>', 'Special Cancellation <span class="count">(%s)</span>', 'motopress-hotel-booking' )
				);
				break;
				case self::STATUS_EXPIRED_RESERVATION:
				$args	 = array(
					'label'						 => _x( 'Expired Reservation', 'Booking status', 'motopress-hotel-booking' ),
					'public'					 => true,
					'exclude_from_search'		 => false,
					'show_in_admin_all_list'	 => true,
					'show_in_admin_status_list'	 => true,
					'label_count'				 => _n_noop( 'Expired Reservation <span class="count">(%s)</span>', 'Expired Reservation <span class="count">(%s)</span>', 'motopress-hotel-booking' )
				);
				break;
			case self::STATUS_ARCHIVE:
				$args	 = array(
					'label'						 => _x( 'Archive', 'Booking status', 'motopress-hotel-booking' ),
					'public'					 => true,
					'exclude_from_search'		 => true,
					'show_in_admin_all_list'	 => false,
					'show_in_admin_status_list'	 => true,
					'label_count'				 => _n_noop( 'Confirmed Reservation Archive <span class="count">(%s)</span>', 'Confirmed Reservation Archive <span class="count">(%s)</span>', 'motopress-hotel-booking' )
				);
				break;
			case self::STATUS_ARCHIVE_CONFIRMED:
				$args	 = array(
					'label'						 => _x( 'Confirmed Archived', 'Booking status', 'motopress-hotel-booking' ),
					'public'					 => true,
					'exclude_from_search'		 => true,
					'show_in_admin_all_list'	 => false,
					'show_in_admin_status_list'	 => true,
					'label_count'				 => _n_noop( 'Confirmed Archived <span class="count">(%s)</span>', 'Confirmed Archived <span class="count">(%s)</span>', 'motopress-hotel-booking' )
				);
				break;
			case self::STATUS_ARCHIVE_PENDING:
				$args	 = array(
					'label'						 => _x( 'Pending Archived', 'Booking status', 'motopress-hotel-booking' ),
					'public'					 => true,
					'exclude_from_search'		 => true,
					'show_in_admin_all_list'	 => false,
					'show_in_admin_status_list'	 => true,
					'label_count'				 => _n_noop( 'Pending Archived <span class="count">(%s)</span>', 'Pending Archived <span class="count">(%s)</span>', 'motopress-hotel-booking' )
				);
				break;
			case self::STATUS_ARCHIVE_DELETE:
				$args	 = array(
					'label'						 => _x( 'Delete Archived', 'Booking status', 'motopress-hotel-booking' ),
					'public'					 => true,
					'exclude_from_search'		 => true,
					'show_in_admin_all_list'	 => false,
					'show_in_admin_status_list'	 => true,
					'label_count'				 => _n_noop( 'Delete Archived <span class="count">(%s)</span>', 'Delete Archived <span class="count">(%s)</span>', 'motopress-hotel-booking' )
				);
				break;				
			case self::STATUS_NOT_PAID:
				$args	 = array(
					'label'						 => _x( 'Not Paid', 'Booking status', 'motopress-hotel-booking' ),
					'public'					 => true,
					'exclude_from_search'		 => true,
					'show_in_admin_all_list'	 => false,
					'show_in_admin_status_list'	 => true,
					'label_count'				 => _n_noop( 'Not Paid <span class="count">(%s)</span>', 'Not Paid <span class="count">(%s)</span>', 'motopress-hotel-booking' )
				);
				break;					
			case self::STATUS_PAID_NOT_REFUNDABLE:
				$args	 = array(
					'label'						 => _x( 'Paid not refundable', 'Booking status', 'motopress-hotel-booking' ),
					'public'					 => true,
					'exclude_from_search'		 => true,
					'show_in_admin_all_list'	 => false,
					'show_in_admin_status_list'	 => true,
					'label_count'				 => _n_noop( 'Paid not refundable <span class="count">(%s)</span>', 'Paid not refundable <span class="count">(%s)</span>', 'motopress-hotel-booking' )
				);
				break;					
			case self::STATUS_PAID_REFUNDABLE:
				$args	 = array(
					'label'						 => _x( 'Paid refundable', 'Booking status', 'motopress-hotel-booking' ),
					'public'					 => true,
					'exclude_from_search'		 => true,
					'show_in_admin_all_list'	 => false,
					'show_in_admin_status_list'	 => true,
					'label_count'				 => _n_noop( 'Paid refundable <span class="count">(%s)</span>', 'Paid refundable <span class="count">(%s)</span>', 'motopress-hotel-booking' )
				);
				break;					
			case self::STATUS_LAST_MINUTE:
				$args	 = array(
					'label'						 => _x( 'Last Minute', 'Booking status', 'motopress-hotel-booking' ),
					'public'					 => true,
					'exclude_from_search'		 => true,
					'show_in_admin_all_list'	 => false,
					'show_in_admin_status_list'	 => true,
					'label_count'				 => _n_noop( 'Last Minute <span class="count">(%s)</span>', 'Last Minute <span class="count">(%s)</span>', 'motopress-hotel-booking' )
				);
				break;					
			case self::STATUS_PENDING_LATE_CHARGE:
				$args	 = array(
					'label'						 => _x( 'Pending Late Charge', 'Booking status', 'motopress-hotel-booking' ),
					'public'					 => true,
					'exclude_from_search'		 => true,
					'show_in_admin_all_list'	 => false,
					'show_in_admin_status_list'	 => true,
					'label_count'				 => _n_noop( 'Pending Late Charge <span class="count">(%s)</span>', 'Pending Late Charge <span class="count">(%s)</span>', 'motopress-hotel-booking' )
				);
				break;					
			case self::STATUS_PAID_LATE_CHARGE:
				$args	 = array(
					'label'						 => _x( 'Paid Late Charge', 'Booking status', 'motopress-hotel-booking' ),
					'public'					 => true,
					'exclude_from_search'		 => true,
					'show_in_admin_all_list'	 => false,
					'show_in_admin_status_list'	 => true,
					'label_count'				 => _n_noop( 'Paid Late Charge <span class="count">(%s)</span>', 'Paid Late Charge <span class="count">(%s)</span>', 'motopress-hotel-booking' )
				);
				break;				
		}
		return $args;
	}

	/**
	 * @todo move expiration functionality to action handler
	 *
	 * @param string $newStatus
	 * @param string $oldStatus
	 * @param \WP_Post $post
	 */
	public function transitionStatus( $newStatus, $oldStatus, $post ){

		if ( $post->post_type !== $this->postType ) {
			return;
		}

		if ( $newStatus === $oldStatus ) {
			return;
		}

		// Prevent logging status change while importing
		if ( apply_filters( 'mphb_prevent_handle_booking_status_transition', false ) ) {
			return;
		}


		$booking = MPHB()->getBookingRepository()->findById( $post->ID, true );


		$add_array = array(
			"confirmed",
			"confirmed-archived",
			"paid_not_refundable",
			"paid_refundable",
			"last_minute",
			"pending_late_charge",
			"paid_late_charge",
		);
		$remove_array = array(
			"pending-user",
			"pending-payment",
			"pending",
			"pending-2",
			"special-comunication",
			"abandoned",
			"cancelled",
			"special-cancellation",
			"expired-reservation",
			"archive",
			"pending-archived",
			"delete-archived",
			"not-paid",
			"trash",
			"draft",
		);

		if ( in_array($newStatus, $add_array) && in_array($oldStatus, $remove_array) ) {
			$products_qty = get_post_meta($post->ID,"products_qty",1);
			if($products_qty){
				foreach ($products_qty as $key => $value) {
					$oldstock = $stock = get_post_meta($key,"stock",1);
					if($stock != ""){
						$stock = $stock - $value;
						update_post_meta($key,"stock",$stock);
						$oldStatusname = mphb_get_status_label($oldStatus);
						$newStatusname = mphb_get_status_label($newStatus);

						if(file_exists(ABSPATH."product_log.txt")){
							$text = 'Booking ID '.$post->ID." Moved From ".$oldStatusname." To ".$newStatusname.".Product ".get_the_title($key)." Old Stock ".$oldstock.", New Stock ".$stock.PHP_EOL;
							$fp = fopen(ABSPATH."product_log.txt", 'a');
							fwrite($fp, $text);
						}
					}
				}
			}
		}else if ( in_array($oldStatus, $add_array) && in_array($newStatus, $remove_array) ) {
			$products_qty = get_post_meta($post->ID,"products_qty",1);
			if($products_qty){
				foreach ($products_qty as $key => $value) {
					$oldstock = $stock = get_post_meta($key,"stock",1);
					if($stock != ""){
						$stock = $stock + $value;
						update_post_meta($key,"stock",$stock);

						$oldStatusname = mphb_get_status_label($oldStatus);
						$newStatusname = mphb_get_status_label($newStatus);

						if(file_exists(ABSPATH."product_log.txt")){
							$text = 'Booking ID '.$post->ID." Moved From ".$oldStatusname." To ".$newStatusname.".Product ".get_the_title($key)." Old Stock ".$oldstock.", New Stock ".$stock.PHP_EOL;
							$fp = fopen(ABSPATH."product_log.txt", 'a');
							fwrite($fp, $text);
						}
					}
				}
			}
		}else{
			$products_qty = get_post_meta($post->ID,"products_qty",1);
			if($products_qty){
				foreach ($products_qty as $key => $value) {
					$oldstock = $stock = get_post_meta($key,"stock",1);
					if($stock != ""){
						//$stock = $stock + $value;
						//update_post_meta($key,"stock",$stock);

						$oldStatusname = mphb_get_status_label($oldStatus);
						$newStatusname = mphb_get_status_label($newStatus);

						if(file_exists(ABSPATH."product_log.txt")){
							$text = 'Booking ID '.$post->ID." Moved From ".$oldStatusname." To ".$newStatusname.".Product ".get_the_title($key)." Old Stock ".$oldstock.", New Stock ".$stock.PHP_EOL;
							$fp = fopen(ABSPATH."product_log.txt", 'a');
							fwrite($fp, $text);
						}
					}
				}
			}
		}

		if ( $oldStatus === self::STATUS_SPECIAL_COMUNICATION ) {
			return;
		}
		if ( $newStatus === self::STATUS_SPECIAL_COMUNICATION ) {
			do_action( 'mphb_booking_status_changed_special', $booking );
			 wp_update_post(array(
		        'ID'    =>  $post->ID,
		        'post_status'   =>  $oldStatus
		        ));
			return;
		}
		if ( $oldStatus == 'new' ) {
			$booking->generateKey();
		}

		$expirationStatuses = array(
			self::STATUS_PENDING_USER,
			self::STATUS_PENDING_PAYMENT
		);

		if ( $newStatus === self::STATUS_PENDING_USER ) {

			$booking->updateExpiration( 'user', current_time( 'timestamp', true ) + MPHB()->settings()->main()->getUserApprovalTime() * MINUTE_IN_SECONDS );

			MPHB()->cronManager()->getCron( 'abandon_booking_pending_user' )->schedule();
		}

		if ( $oldStatus === self::STATUS_PENDING_USER ) {
			$booking->deleteExpiration( 'user' );
		}

		if ( $newStatus === self::STATUS_PENDING_PAYMENT ) {

			$booking->updateExpiration( 'payment', current_time( 'timestamp', true ) + MPHB()->settings()->payment()->getPendingTime() * MINUTE_IN_SECONDS );

			MPHB()->cronManager()->getCron( 'abandon_booking_pending_payment' )->schedule();
		}

		if ( $oldStatus === self::STATUS_PENDING_PAYMENT ) {
			$booking->deleteExpiration( 'payment' );
		}

		$booking->addLog( sprintf( __( 'Status changed from %s to %s.', 'motopress-hotel-booking' ), mphb_get_status_label( $oldStatus ), mphb_get_status_label( $newStatus ) ) );


		do_action( 'mphb_booking_status_changed', $booking, $oldStatus );
		
		if ( $newStatus === self::STATUS_CONFIRMED ) {
			do_action( 'mphb_booking_confirmed', $booking, $oldStatus );
		}

		if ( $newStatus === self::STATUS_CANCELLED ) {
			do_action( 'mphb_booking_cancelled', $booking, $oldStatus );
		}
	}

	/**
	 *
	 * @return array
	 */
	public function getLockedRoomStatuses(){
		return array_keys( array_filter( $this->statuses, function( $status ) {
				return isset( $status['lock_room'] ) && $status['lock_room'];
			} ) );
	}

	/**
	 *
	 * @return array
	 */
	public function getBookedRoomStatuses(){
		return (array) self::STATUS_CONFIRMED;
	}

	/**
	 *
	 * @return array
	 */
	public function getPendingRoomStatuses(){
		return array(
			self::STATUS_PENDING,
			self::STATUS_PENDING_USER,
			self::STATUS_PENDING_PAYMENT
		);
	}

    /**
     * @return array
     *
     * @since 3.7.6
     */
    public function getFailedStatuses(){
        return array(
            self::STATUS_CANCELLED,
            self::STATUS_ABANDONED
        );
    }

	/**
	 *
	 * @return array
	 */
	public function getAvailableRoomStatuses(){
		return array_merge( 'trash', array_diff( array_keys( $this->statuses ), $this->getLockedRoomStatuses() ) );
	}

	/**
	 *
	 * @return string
	 */
	public function getDefaultNewBookingStatus(){
		$confirmationMode = MPHB()->settings()->main()->getConfirmationMode();
		switch ( $confirmationMode ) {
			case 'manual':
				$defaultStatus	 = self::STATUS_PENDING;
				break;
			case 'payment':
				$defaultStatus	 = self::STATUS_PENDING_PAYMENT;
				break;
			case 'auto':
			default:
				$defaultStatus	 = self::STATUS_PENDING_USER;
				break;
		}
		return $defaultStatus;
	}

}
