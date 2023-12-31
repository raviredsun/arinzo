<?php

namespace MPHB\Admin\MenuPages\CreateBooking;

use \MPHB\Utils\ParseUtils;
use \MPHB\Utils\ValidateUtils;

/**
 * Fourth step.
 */
class BookingStep extends Step {

	/** @var \MPHB\Entities\Customer|null */
	protected $customer = null;

	/** @var \MPHB\Entities\Booking|null */
	protected $booking = null;

	protected $allowRedirect = true;

	public function __construct(){
		parent::__construct( 'booking' );
	}

	public function setup(){
		parent::setup();



		if ( !$this->isValidStep ) {
			return;
		}

        // Generate price breakdown before save: save() will trigger some emails,
        // which require price breakdown in their text. See MB-1027 for more details
		$this->booking->getPriceBreakdown();

		if (!empty($_POST['paytype'])) {
			if($_POST['paytype'] == "not_refundable"){
				$this->booking->setStatus( \MPHB\PostTypes\BookingCPT\Statuses::STATUS_PAID_NOT_REFUNDABLE );
			}else if($_POST['paytype'] == "refundable"){
				$this->booking->setStatus( \MPHB\PostTypes\BookingCPT\Statuses::STATUS_PAID_REFUNDABLE );
			}else if($_POST['paytype'] == "last_minute"){
				$this->booking->setStatus( \MPHB\PostTypes\BookingCPT\Statuses::STATUS_LAST_MINUTE );
			}
		}

		$bookingSaved = MPHB()->getBookingRepository()->save( $this->booking );

		if ( !$bookingSaved ) {
			$this->parseError( __( 'Unable to create booking. Please try again.', 'motopress-hotel-booking' ) );
			$this->isValidStep = false;
			return;
		}

		if (!empty($_POST['emailtype'])) {
            update_post_meta($this->booking->getId(), "payment_allow", 1);
		}
		
		if($_POST['mphb_beach_arrival_time']) {
            update_post_meta($this->booking->getId(), "beach_arrival_time", $_POST['mphb_beach_arrival_time']);
        }

        if($_POST['mphb_lunch_time']) {
            update_post_meta($this->booking->getId(), "lunch_time", $_POST['mphb_lunch_time']);
        }
		
		if($_POST['mphb_table_id']) {
            update_post_meta($this->booking->getId(), "arf_cp_table_id", $_POST['mphb_table_id']);
        }
		
		if(isset($_POST['mphb_place'])) {
            update_post_meta($this->booking->getId(), "mphb_place", $_POST['mphb_place']);
        }

		if(isset($_POST['mphb_place_1'])) {
            update_post_meta($this->booking->getId(), "mphb_place_1", $_POST['mphb_place_1']);
        }

		if(isset($_POST['mphb_place_2'])) {
            update_post_meta($this->booking->getId(), "mphb_place_2", $_POST['mphb_place_2']);
        }

		if(isset($_POST['mphb_place_3'])) {
            update_post_meta($this->booking->getId(), "mphb_place_3", $_POST['mphb_place_3']);
        }

		if(isset($_POST['mphb_place_4'])) {
            update_post_meta($this->booking->getId(), "mphb_place_4", $_POST['mphb_place_4']);
        }

		if(isset($_POST['mphb_place_5'])) {
            update_post_meta($this->booking->getId(), "mphb_place_5", $_POST['mphb_place_5']);
        }

		if(isset($_POST['mphb_place_6'])) {
            update_post_meta($this->booking->getId(), "mphb_place_6", $_POST['mphb_place_6']);
        }

		if(isset($_POST['mphb_place_7'])) {
            update_post_meta($this->booking->getId(), "mphb_place_7", $_POST['mphb_place_7']);
        }

        if (!empty($_POST['mphb_room_details'])) {
            foreach ( $_POST['mphb_room_details'] as $value ) {
                if (!empty($value['services'])) {
                    foreach ( $value['services'] as $reservedService ) {
                        if(empty($reservedService['id'])) continue;
                        $service_price = get_post_meta($reservedService['id'], 'service_price', true);
                        $min_pax = get_post_meta($reservedService['id'], 'min_pax', true);
                        $max_pax = get_post_meta($reservedService['id'], 'max_pax', true);

                        $featured_img_url = array();
                        $min = array();
                        $max = array();
                        foreach ($max_pax as $key => $value) {
                            $max[$key] = $value;
                        }
                        foreach ($min_pax as $key => $value) {
                            $min[$key] = $value;
                        }
                    }
                }
            }
        }



        if (!empty($_POST['paytype'])) {
             update_post_meta($this->booking->getId(), 'paytype', $_POST['paytype']);
        }
        if (!empty($_POST['products'])) {
            $products_qty_old = get_post_meta($this->booking->getId(),"products_qty",1);

            $product_change = array();



            $products = array();
            $products_qty = array();
            $products_title = array();
            $products_title2 = array();
            $price_total = 0;
            foreach ($_POST['products'] as $key => $value) {
                $price = 0;
                $qty = 1;
                if(!empty($service_price[$value])){
                    $products[] = ($value);
                    $price = $service_price[$value];
                    if(isset($_POST['products_qty'][$value])){
                        $qty = $_POST['products_qty'][$value];
                        $price = $qty * $price;
                    }else if(isset($max[$value]) && $max[$value] < $adults_total){
                        $qty = ceil($adults_total / $max[$value]);
                        $price = $qty * $price;
                    }
                    $products_qty[$value] = $qty;
                    $price_total += $price;
                    $price = " - €".$price;
                    $products_title[] = get_the_title($value)." x ".$qty.$price;
                    $products_title2[] = get_the_title($value)." x ".$qty;
                }
            }
            update_post_meta($this->booking->getId(), 'products_qty', $products_qty);
            update_post_meta($this->booking->getId(), 'products_price_total', $price_total);
            update_post_meta($this->booking->getId(), 'products', $products);
            update_post_meta($this->booking->getId(), 'products_title', implode(" , ", $products_title));
            update_post_meta($this->booking->getId(), 'products_title2', implode(" , ", $products_title2));


            if($products_qty){
                foreach ($products_qty as $key => $value) {
                    if(!isset($products_qty_old[$key])){
                        $product_change["add"][] = array(
                            "key" => $key,
                            "oldqty" => 0,
                            "qty" => $value,
                        );
                    }else if($products_qty_old[$key] != $value){
                        if($products_qty_old[$key] > $value){
                            $product_change["add"][] = array(
                                "key" => $key,
                                "oldqty" => $products_qty_old[$key],
                                "qty" => $value,
                            );  
                        }else{
                            $product_change["minus"][] = array(
                                "key" => $key,
                                "oldqty" => $products_qty_old[$key],
                                "qty" => $value,
                            );  
                        }
                        unset($products_qty_old[$key]);
                    }else{
                        unset($products_qty_old[$key]);
                    }
                }
            }
            if(!empty($product_change['add'])){
                foreach ($product_change['add'] as $key => $value) {
                    $oldstock = $stock = get_post_meta($value['key'],"stock",1);
                    $stock = $stock - ($value['qty'] - $value['oldqty']);
                    update_post_meta($value['key'],"stock",$stock);
                    if(file_exists(ABSPATH."product_log.txt")){
                        $text = 'Booking ID '.$this->booking->getId().".Product ".get_the_title($value['key'])." Old Quantity ".$value['oldqty'].", New Quantity ".$value['qty'].". Old Stock ".$oldstock.", New Stock ".$stock.PHP_EOL;
                        $fp = fopen(ABSPATH."product_log.txt", 'a');
                        fwrite($fp, $text);
                    }
                }
            }
            if(!empty($product_change['minus'])){
                foreach ($product_change['minus'] as $key => $value) {
                    $oldstock = $stock = get_post_meta($value['key'],"stock",1);
                    $stock = $stock + ($value['oldqty'] - $value['qty']);
                    update_post_meta($value['key'],"stock",$stock);
                    if(file_exists(ABSPATH."product_log.txt")){
                        $text = 'Booking ID '.$this->booking->getId().".Product ".get_the_title($value['key'])." Old Quantity ".$value['oldqty'].", New Quantity ".$value['qty'].". Old Stock ".$oldstock.", New Stock ".$stock.PHP_EOL;
                        $fp = fopen(ABSPATH."product_log.txt", 'a');
                        fwrite($fp, $text);
                    }
                }
            }
            if(!empty($products_qty_old)){
                foreach ($products_qty_old as $key => $value) {
                    $oldstock = $stock = get_post_meta($key,"stock",1);
                    $stock = $stock + ($value);
                    update_post_meta($key,"stock",$stock);
                    if(file_exists(ABSPATH."product_log.txt")){
                        $text = 'Booking ID '.$this->booking->getId().".Product ".get_the_title($key)." Old Quantity ".$value.", New Quantity 0. Old Stock ".$oldstock.", New Stock ".$stock.PHP_EOL;
                        $fp = fopen(ABSPATH."product_log.txt", 'a');
                        fwrite($fp, $text);
                    }
                }
            }
        }

        $priceBreakdown = $this->booking->getPriceBreakdown($this->booking->getId());
        // Update booking
        $saved = MPHB()->getBookingRepository()->save($this->booking);
		do_action( 'mphb_create_booking_by_user', $this->booking );

		// Redirect to "Edit Booking"
		if ( $this->allowRedirect ) {
			$redirectTo = get_edit_post_link( $this->booking->getId(), 'raw' );
			wp_redirect( $redirectTo );
			$this->_exit();
		}
	}

	protected function renderValid(){
		$booking = sprintf( __( 'Booking #%s', 'motopress-hotel-booking' ), $this->booking->getId() );
		$link = get_edit_post_link( $this->booking->getId() );

		echo '<h2><a href="' . esc_url( $link ) . '">' . esc_html( $booking ) . '</a></h2>';
	}

	protected function parseFields(){
		if ( apply_filters( 'mphb_block_booking', false ) ) {
			$this->parseError( __( 'Booking is blocked due to maintenance reason. Please try again later.', 'motopress-hotel-booking' ) );
			return;
		}

		$this->checkInDate	 = $this->parseCheckInDate( INPUT_POST );
		$this->checkOutDate	 = $this->parseCheckOutDate( INPUT_POST );
		$this->customer		 = $this->parseCustomer( INPUT_POST );

		if ( $this->checkInDate && $this->checkOutDate && $this->customer ) {
			$this->booking = $this->parseBooking( INPUT_POST );
		}
	}

    /**
     * @param int $inputType INPUT_POST (0) or INPUT_GET (1)
     *
     * @return \MPHB\Entities\Customer|null
     */
    protected function parseCustomer($inputType)
    {
        $input = $inputType == INPUT_POST ? $_POST : $_GET;
        $customerData = ParseUtils::parseCustomer($input, $this->parseErrors);

        if ($customerData !== false) {
            return new \MPHB\Entities\Customer($customerData);
        } else {
            return null;
        }
    }

	/**
	 * @param int $input INPUT_POST (0) or INPUT_GET (1)
	 *
	 * @return \MPHB\Entities\Booking|null
	 */
	protected function parseBooking( $input ){
		/** @var string|false|null */
		$details = filter_input( $input, 'mphb_room_details', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		if ( empty( $details ) ) {
			if ( is_null( $details ) ) {
				$this->parseError( __( 'There are no accommodations selected for reservation.', 'motopress-hotel-booking' ) );
			} else if ( $details === false ) {
				$this->parseError( __( 'Selected accommodations are not valid.', 'motopress-hotel-booking' ) );
			}

			return null;
		}

		$rooms		 = array();
		$roomIds	 = array();
		$typeRates	 = array();
		$wasErrors	 = count( $this->parseErrors );

		foreach ( $details as $number => $roomDetails ) {
			$roomTypeId		 = isset( $roomDetails['room_type_id'] ) ? ValidateUtils::validateInt( $roomDetails['room_type_id'] ) : 0;
			$roomType		 = ( $roomTypeId > 0 ) ? MPHB()->getRoomTypeRepository()->findById( $roomTypeId ) : null;
			$originalTypeId	 = ( !is_null( $roomType ) ) ? $roomType->getOriginalId() : $roomTypeId;
			$roomId			 = isset( $roomDetails['room_id'] ) ? ValidateUtils::validateInt( $roomDetails['room_id'] ) : 0;
			$rateId			 = isset( $roomDetails['rate_id'] ) ? ValidateUtils::validateInt( $roomDetails['rate_id'] ) : 0;
			$adults			 = isset( $roomDetails['adults'] ) ? ValidateUtils::validateInt( $roomDetails['adults'] ) : 0;
			$children		 = isset( $roomDetails['children'] ) ? ValidateUtils::validateInt( $roomDetails['children'] ) : 0;
			$minAdults		 = MPHB()->settings()->main()->getMinAdults();
			$minChildren	 = MPHB()->settings()->main()->getMinChildren();
			$guestName		 = isset( $roomDetails['guest_name'] ) ? mphb_clean( $roomDetails['guest_name'] ) : '';

			if ( !$roomType || $roomType->getStatus() != 'publish' ) {
				$this->parseError( __( 'Accommodation Type is not valid.', 'motopress-hotel-booking' ) );
				break;
			}

			if ( $roomId <= 0 ) {
				$this->parseError( __( 'Selected accommodations are not valid.', 'motopress-hotel-booking' ) );
				break;
			}

			if ( $rateId <= 0 ) {
				$this->parseError( __( 'Rate is not valid.', 'motopress-hotel-booking' ) );
				break;
			}

			// Search allowed rates (IDs)
			$allowedRateIds = array();
			if ( isset( $typeRates[$originalTypeId] ) ) {
				$allowedRateIds = $typeRates[$originalTypeId];
			} else {
				$allowedRates = MPHB()->getRateRepository()->findAllActiveByRoomType( $originalTypeId, array(
					'check_in_date'	 => $this->checkInDate,
					'check_out_date' => $this->checkOutDate,
					'mphb_language'	 => 'original'
				) );
				$allowedRateIds = array_map( function( \MPHB\Entities\Rate $rate ){
					return $rate->getOriginalId();
				}, $allowedRates );
				$typeRates[$originalTypeId] = $allowedRateIds;
			}

			if ( !in_array( $rateId, $allowedRateIds ) ) {
				$this->parseError( __( 'Rate is not valid.', 'motopress-hotel-booking' ) );
				break;
			}

			if ( $adults === false || $adults < $minAdults || $adults > $roomType->getAdultsCapacity() ) {
				$this->parseError( __( 'Adults number is not valid.', 'motopress-hotel-booking' ) );
				break;
			}

			if ( $children === false || $children < $minChildren || $children > $roomType->getChildrenCapacity() ) {
				$this->parseError( __( 'Children number is not valid.', 'motopress-hotel-booking' ) );
				break;
			}

            if ($roomType->hasLimitedTotalCapacity() && $adults + $children > $roomType->getTotalCapacity()) {
                $this->parseError(__('The total number of guests is not valid.', 'motopress-hotel-booking'));
                break;
            }

			if ( !MPHB()->getRulesChecker()->verify( $this->checkInDate, $this->checkOutDate, $roomTypeId ) ) {
				$this->parseError( __( 'Selected dates do not meet booking rules for type %s', 'motopress-hotel-booking' ) );
				break;
			}

			$services = array();

            // Check isset() before is_array(); if there are no services,
            // in_array() will generate the notice "Undefined index"
			if ( isset( $roomDetails['services'] ) && is_array( $roomDetails['services'] ) ) {
				foreach ( $roomDetails['services'] as $serviceDetails ) {
					if ( !isset( $serviceDetails['id'] ) || !isset( $serviceDetails['adults'] ) ) {
						continue;
					}

					$serviceId = ValidateUtils::validateInt( $serviceDetails['id'] );
					$serviceAdults = ValidateUtils::validateInt( $serviceDetails['adults'] );
					$serviceChild = ValidateUtils::validateInt( (isset($serviceDetails['child']) ? $serviceDetails['child'] : "0") );
                    $serviceQuantity = isset($serviceDetails['quantity']) ? ValidateUtils::validateInt($serviceDetails['quantity']) : 1;

					if ( $serviceId === false || $serviceAdults === false || !in_array( $serviceId, $roomType->getServices() ) || $serviceAdults <= 0 || (isset($serviceDetails['quantity']) && $serviceQuantity < 1) ) {
						continue;
					}

					$service = \MPHB\Entities\ReservedService::create( array(
						'id'	   => $serviceId,
						'adults'   => $serviceAdults,
						'child'   => $serviceChild,
                        'quantity' => $serviceQuantity
					) );

					if ( !is_null( $service ) ) {
						$services[] = $service;
					}
				} // For each service details
			}

			$rooms[] = array(
				'room_id'			 => $roomId,
				'rate_id'			 => $rateId,
				'adults'			 => $adults,
				'children'			 => $children,
				'reserved_services'	 => $services,
				'guest_name'		 => $guestName
			);

			if ( !isset( $roomIds[$roomTypeId] ) ) {
				$roomIds[$roomTypeId] = array();
			}
			$roomIds[$roomTypeId][] = $roomId;
		} // For each room details

		foreach ( $roomIds as $roomTypeId => $ids ) {
			if ( !MPHB()->getRoomPersistence()->isRoomsFree( $this->checkInDate, $this->checkOutDate, $ids, array( 'room_type_id' => $roomTypeId ) ) ) {
				$this->parseError( __( 'Accommodations are not available.', 'motopress-hotel-booking' ) );
				break;
			}
		}

		if ( count( $this->parseErrors ) > $wasErrors ) {
			return null;
		}

		$reservedRooms = array_filter( array_map( array( '\MPHB\Entities\ReservedRoom', 'create'), $rooms ) );

		if ( empty( $reservedRooms ) ) {
			$this->parseError( __( 'There are no accommodations selected for reservation.', 'motopress-hotel-booking' ) );
			return null;
		}

		$values	 = ( $input == INPUT_POST ) ? $_POST : $_GET;
		$note	 = !empty( $values['mphb_note'] ) ? sanitize_textarea_field( $values['mphb_note'] ) : '';
		$booking = \MPHB\Entities\Booking::create( array(
			'check_in_date'	 => $this->checkInDate,
			'check_out_date' => $this->checkOutDate,
			'customer'		 => $this->customer,
			'note'			 => $note,
			'status'		 => \MPHB\PostTypes\BookingCPT\Statuses::STATUS_CONFIRMED,
			'reserved_rooms' => $reservedRooms,
            'checkout_id'    => mphb_generate_uuid4()
		) );

		if ( !empty( $values['mphb_applied_coupon_code'] ) ) {
			$coupon = MPHB()->getCouponRepository()->findByCode( mphb_clean( $values['mphb_applied_coupon_code'] ) );
			if ( $coupon ) {
				$booking->applyCoupon( $coupon );
			}
		}

		return $booking;
	}

	public function disableRedirect(){
		$this->allowRedirect = false;
	}

	protected function _exit(){
		exit;
	}

}
