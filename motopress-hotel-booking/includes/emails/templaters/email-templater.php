<?php

namespace MPHB\Emails\Templaters;

use \MPHB\Views;

class EmailTemplater extends AbstractTemplater {

	private $tagGroups = array();

	/**
	 *
	 * @var \MPHB\Entities\Booking
	 */
	private $booking;

	/**
	 *
	 * @var \MPHB\Entities\Payment
	 */
	private $payment;

	/**
	 *
	 * @param array $tagGroups
	 * @param bool $tagGroups['global'] Global site tags. Default TRUE.
	 * @param bool $tagGroups['booking'] Booking tags. Default FALSE.
	 * @param bool $tagGroups['user_confirmation'] User confirmation tags. Default FALSE.
	 * @param bool $tagGroups['user_cancellation'] User cancellation tags. Default FALSE.
	 * @param bool $tagGroups['payment'] Payment details tags. Default FALSE.
	 */
	public static function create( $tagGroups = array() ){

		$templater = new static();

		$templater->setTagGroups( $tagGroups );

		return $templater;
	}

	public function setTagGroups( $tagGroups ){
		$defaultTagGroups = array(
			'global'			 => true,
			'booking'			 => false,
            'booking_details'    => false,
			'user_confirmation'	 => false,
			'user_cancellation'	 => false,
			'payment'			 => false
		);

		$this->tagGroups = array_merge( $defaultTagGroups, $tagGroups );
	}

	/**
	 *
	 * @param array $tagGroups
	 */
	public function setupTags(){

		$tags = array();

		if ( $this->tagGroups['global'] ) {
			$this->_fillGlobalTags( $tags );
		}

		if ( $this->tagGroups['booking'] ) {
			$this->_fillBookingTags( $tags );
		}

        if ($this->tagGroups['booking_details']) {
            $this->_fillBookingDetailsTags($tags);
        }

		if ( $this->tagGroups['user_confirmation'] ) {
			$this->_fillUserConfirmationTags( $tags );
		}

		if ( $this->tagGroups['user_cancellation'] ) {
			$this->_fillUserCancellationTags( $tags );
		}

		if ( $this->tagGroups['payment'] ) {
			$this->_fillPaymentTags( $tags );
		}

		$tags = apply_filters( 'mphb_email_tags', $tags );

		foreach ( $tags as $tag ) {
			$this->addTag( $tag['name'], $tag['description'], $tag );
		}
	}

	private function _fillGlobalTags( &$tags ){
		$globalTags = array(
			array(
				'name'			 => 'site_title',
				'description'	 => __( 'Site title (set in Settings > General)', 'motopress-hotel-booking' ),
			)
		);

        $globalTags = apply_filters( 'mphb_email_global_tags', $globalTags );

		$tags = array_merge( $tags, $globalTags );
	}

	private function _fillBookingTags( &$tags ){
		$bookingTags	 = array(
			// Booking
			array(
				'name'			 => 'booking_id',
				'description'	 => __( 'Booking ID', 'motopress-hotel-booking' ),
			),
			array(
				'name'			 => 'booking_edit_link',
				'description'	 => __( 'Booking Edit Link', 'motopress-hotel-booking' ),
			),
			array(
				'name'			 => 'booking_total_price',
				'description'	 => __( 'Booking Total Price', 'motopress-hotel-booking' ),
			),
			array(
				'name'			 => 'check_in_date',
				'description'	 => __( 'Check-in Date', 'motopress-hotel-booking' ),
			),
			array(
				'name'			 => 'check_out_date',
				'description'	 => __( 'Check-out Date', 'motopress-hotel-booking' ),
			),
			array(
				'name'			 => 'check_in_time',
				'description'	 => __( 'Check-in Time', 'motopress-hotel-booking' ),
			),
			array(
				'name'			 => 'check_out_time',
				'description'	 => __( 'Check-out Time', 'motopress-hotel-booking' ),
			),
			// Customer
			array(
				'name'			 => 'customer_first_name',
				'description'	 => __( 'Customer First Name', 'motopress-hotel-booking' ),
			),
			array(
				'name'			 => 'customer_last_name',
				'description'	 => __( 'Customer Last Name', 'motopress-hotel-booking' ),
			),
			array(
				'name'			 => 'customer_email',
				'description'	 => __( 'Customer Email', 'motopress-hotel-booking' ),
			),
			array(
				'name'			 => 'customer_phone',
				'description'	 => __( 'Customer Phone', 'motopress-hotel-booking' ),
			),
            array(
                'name'           => 'customer_country',
                'description'    => __( 'Customer Country', 'motopress-hotel-booking' )
            ),
            array(
                'name'           => 'customer_address1',
                'description'    => __( 'Customer Address', 'motopress-hotel-booking' )
            ),
            array(
                'name'           => 'customer_city',
                'description'    => __( 'Customer City', 'motopress-hotel-booking' )
            ),
            array(
                'name'           => 'customer_state',
                'description'    => __( 'Customer State/County', 'motopress-hotel-booking' )
            ),
            array(
                'name'           => 'customer_zip',
                'description'    => __( 'Customer Postcode', 'motopress-hotel-booking' )
            ),
			array(
				'name'			 => 'customer_note',
				'description'	 => __( 'Customer Note', 'motopress-hotel-booking' ),
			),
			// Room Type
			array(
				'name'			 => 'reserved_rooms_details',
				'description'	 => __( 'Reserved Accommodations Details', 'motopress-hotel-booking' ),
			),
            // Other
            array(
                'name'           => 'price_breakdown',
                'description'    => __( 'Price Breakdown', 'motopress-hotel-booking' )
            ),
			array(
				'name'           => 'services_list',
				'description'    => __( 'Services List', 'motopress-hotel-booking' )
			),
			array(
                'name' => 'arrival_time',
                'description' => __('Arrival time', 'motopress-hotel-booking')
            ),
            array(
                'name' => 'lunch_time',
                'description' => __('Lunch time', 'motopress-hotel-booking')
            ),
            array(
                'name' => 'adults',
                'description' => __('Adults', 'motopress-hotel-booking')
            ),
            array(
                'name' => 'children',
                'description' => __('Children', 'motopress-hotel-booking')
            ),
            array(
                'name' => 'subtotal',
                'description' => __('Sub Total', 'motopress-hotel-booking')
            ),
            array(
                'name' => 'total',
                'description' => __('Total', 'motopress-hotel-booking')
            )
		);

        $bookingTags = apply_filters( 'mphb_email_booking_tags', $bookingTags );

		$tags = array_merge( $tags, $bookingTags );
	}

    /**
     * @since 3.7.0
     */
    private function _fillBookingDetailsTags(&$tags)
    {
        $orderViewTags = array(
            array(
                'name'        => 'view_booking_link',
                'description' => __('Booking Details', 'motopress-hotel-booking')
            )
        );

        $orderViewTags = apply_filters('mphb_email_booking_details_tags', $orderViewTags);

        $tags = array_merge($tags, $orderViewTags);
    }

	private function _fillUserConfirmationTags( &$tags ){
		$userConfirmationTags = array(
			array(
				'name'			 => 'user_confirm_link',
				'description'	 => __( 'Confirmation Link', 'motopress-hotel-booking' )
			),
			array(
				'name'			 => 'user_confirm_link_expire',
				'description'	 => __( 'Confirmation Link Expiration Time ( UTC )', 'motopress-hotel-booking' )
			)
		);

        $userConfirmationTags = apply_filters( 'mphb_email_user_confirmation_tags', $userConfirmationTags );

		$tags = array_merge( $tags, $userConfirmationTags );
	}

	private function _fillUserCancellationTags( &$tags ){
		$userCancellationTags	 = array(
			array(
				'name'			 => 'cancellation_details',
				'description'	 => __( 'Cancellation Details (if enabled)', 'motopress-hotel-booking' ),
			),
		);

        $userCancellationTags = apply_filters( 'mphb_email_user_cancellation_tags', $userCancellationTags );

		$tags = array_merge( $tags, $userCancellationTags );
	}

	private function _fillPaymentTags( &$tags ){
		$paymentTags = array(
			array(
				'name'			 => 'payment_amount',
				'description'	 => __( 'The total price of payment', 'motopress-hotel-booking' )
			),
			array(
				'name'			 => 'payment_id',
				'description'	 => __( 'The unique ID of payment', 'motopress-hotel-booking' )
			),
			array(
				'name'			 => 'payment_method',
				'description'	 => __( 'The method of payment', 'motopress-hotel-booking' )
			),
            array(
                'name'           => 'payment_instructions',
                'description'    => __( 'Payment instructions', 'motopress-hotel-booking' )
            )
		);

        $paymentTags = apply_filters( 'mphb_email_payment_tags', $paymentTags );

		$tags = array_merge( $tags, $paymentTags );
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 */
	public function setupBooking( $booking ){
		$this->booking = $booking;
	}

	/**
	 *
	 * @param \MPHB\Entities\Payment $payment
	 */
	public function setupPayment( $payment ){
		$this->payment = $payment;
	}

	/**
	 * @param array $match
	 * @param string $match[0] The tag.
	 * @return string
     *
     * @since 3.6.1 added new macros - %customer_country%.
     * @since 3.6.1 added new macros - %customer_address1%.
     * @since 3.6.1 added new macros - %customer_city%.
     * @since 3.6.1 added new macros - %customer_state%.
     * @since 3.6.1 added new macros - %customer_zip%.
     * @since 3.6.1 added new macros - %price_breakdown%.
     * @since 3.6.1 added new macros - %payment_instructions%.
	 */
	public function replaceTag( $match ){

		$tag = str_replace( '%', '', $match[0] );

		$replaceText = '';

		switch ( $tag ) {

			// Global
			case 'site_title':
				$replaceText = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
				break;
			case 'check_in_time':
				$replaceText = MPHB()->settings()->dateTime()->getCheckInTimeWPFormatted();
				break;
			case 'check_out_time':
				$replaceText = MPHB()->settings()->dateTime()->getCheckOutTimeWPFormatted();
				break;

			// Booking
			case 'booking_id':
				if ( isset( $this->booking ) ) {
					$replaceText = $this->booking->getId();
				}
				break;
			case 'booking_edit_link':
				if ( isset( $this->booking ) ) {
					$replaceText = mphb_get_edit_post_link_for_everyone( $this->booking->getId() );
				}
				break;
			case 'booking_total_price':
				if ( isset( $this->booking ) ) {
					ob_start();
					Views\BookingView::renderTotalPriceHTML( $this->booking );
					$replaceText = ob_get_clean();
				}
				break;
			case 'check_in_date':
				if ( isset( $this->booking ) ) {
					ob_start();
					Views\BookingView::renderCheckInDateWPFormatted( $this->booking );
					$replaceText = ob_get_clean();
				}
				break;
			case 'check_out_date':
				if ( isset( $this->booking ) ) {
					ob_start();
					Views\BookingView::renderCheckOutDateWPFormatted( $this->booking );
					$replaceText = ob_get_clean();
				}
				break;
			case 'reserved_rooms_details':
				if ( isset( $this->booking ) ) {
					$replaceText = MPHB()->emails()->getReservedRoomsTemplater()->process( $this->booking );
				}
				break;
            case 'price_breakdown':
                if ( isset( $this->booking ) ) {
                    $priceDetails = $this->booking->getLastPriceBreakdown();
                    if ( !empty( $priceDetails ) ) {
                        $replaceText = Views\BookingView::generatePriceBreakdownArray( $priceDetails, array(
                            'title_expandable' => false,
                            'coupon_removable' => false,
                            'force_unfold'     => true
                        ) );
                    }
                }
                break;
            case 'total':
                if ( isset( $this->booking ) ) {
                   $replaceText = 0;
                   $mphb_total_price = get_post_meta( $this->booking->getId(), 'mphb_total_price', true );
                   if($mphb_total_price){
                   	$replaceText = $mphb_total_price;
                   }
                   /*$price_breakdown = get_post_meta($this->booking->getId(), '_mphb_booking_price_breakdown', true); 
                   if($price_breakdown){
        				$ddd = json_decode(strip_tags($price_breakdown),true);
        				if(isset($ddd['rooms'])){
        					foreach ($ddd['rooms'] as $kk => $value) {
        						$adults += $value['room']['adults']; 
                    			$child += $value['room']['children']; 
        						if(isset($value['services']['total']) && $value['services']['total']){
        						    $replaceText += $value['services']['total'];
        						}
        						
        					}
        				}
        	        }*/

                }
                $replaceText = MPHB()->settings()->currency()->getCurrencySymbol().(number_format($replaceText,2));
                break;
            case 'subtotal':
                if ( isset( $this->booking ) ) {
                   $replaceText = 0;
                   $price_breakdown = get_post_meta($this->booking->getId(), '_mphb_booking_price_breakdown', true); 
                   if($price_breakdown){
        				$ddd = json_decode(strip_tags($price_breakdown),true);
        				if(isset($ddd['rooms'])){
							foreach ($ddd['rooms'] as $kk => $value) {
								$adults += $value['room']['adults']; 
		            			$child += $value['room']['children']; 
								if(isset($value['services']['list'])){
									foreach ($value['services']['list'] as $key => $vv) {
										$service_arr[] = $vv['title']." (".$vv['details'].")";
										$replaceText = $vv['details'];
									}	
								}
								if(isset($value['services']['total']) && $value['services']['total']){
								    $total += $value['services']['total'];
								}
								
							}
						}

        	        }
        	        if($replaceText){
                    	$replaceText = substr(str_replace(MPHB()->settings()->currency()->getCurrencySymbol(), " , ".MPHB()->settings()->currency()->getCurrencySymbol(), $replaceText), 3);
        	        }
                }
                break;

            // Booking Details
            case 'view_booking_link':
                $args = array();
                if (isset($this->payment)) {
                    $args['payment'] = $this->payment;
                }
                if (isset($this->booking)) {
                    $args['booking'] = $this->booking;
                }
                $replaceText = (string)MPHB()->userActions()->getBookingViewAction()->generateLink($args);
                break;

			// Customer
			case 'customer_first_name':
				if ( isset( $this->booking ) ) {
					$replaceText = $this->booking->getCustomer()->getFirstName();
				}
				break;
			case 'customer_last_name':
				if ( isset( $this->booking ) ) {
					$replaceText = $this->booking->getCustomer()->getLastName();
				}
				break;
			case 'customer_email':
				if ( isset( $this->booking ) ) {
					$replaceText = $this->booking->getCustomer()->getEmail();
				}
				break;
			case 'customer_phone':
				if ( isset( $this->booking ) ) {
					$replaceText = $this->booking->getCustomer()->getPhone();
				}
				break;
            case 'customer_country':
                if ( isset( $this->booking ) ) {
                    $countryCode = $this->booking->getCustomer()->getCountry();
                    $replaceText = MPHB()->settings()->main()->getCountriesBundle()->getCountryLabel( $countryCode );
                }
                break;
            case 'customer_address1':
                if ( isset( $this->booking ) ) {
                    $replaceText = $this->booking->getCustomer()->getAddress1();
                }
                break;
            case 'customer_city':
                if ( isset( $this->booking ) ) {
                    $replaceText = $this->booking->getCustomer()->getCity();
                }
                break;
            case 'customer_state':
                if ( isset( $this->booking ) ) {
                    $replaceText = $this->booking->getCustomer()->getState();
                }
                break;
            case 'customer_zip':
                if ( isset( $this->booking ) ) {
                    $replaceText = $this->booking->getCustomer()->getZip();
                }
                break;
			case 'customer_note':
				if ( isset( $this->booking ) ) {
					$replaceText = $this->booking->getNote();
				}
				break;
			case 'user_confirm_link':
				if ( isset( $this->booking ) ) {
					$replaceText = MPHB()->userActions()->getBookingConfirmationAction()->generateLink( $this->booking );
				}
				break;
			case 'user_confirm_link_expire':
				if ( isset( $this->booking ) ) {
					$expireTime	 = $this->booking->retrieveExpiration( 'user' );
					$replaceText = date_i18n( MPHB()->settings()->dateTime()->getDateTimeFormatWP(), $expireTime );
				}
				break;
			case 'cancellation_details':
				if ( isset( $this->booking ) && MPHB()->settings()->main()->canUserCancelBooking() ) {
					$replaceText = MPHB()->emails()->getCancellationTemplater()->process( $this->booking );
				}
				break;

			// Payment
			case 'payment_amount':
				if ( isset( $this->payment ) ) {
					$amountAtts	 = array(
						'currency_symbol' => MPHB()->settings()->currency()->getBundle()->getSymbol( $this->payment->getCurrency() )
					);
					$replaceText = mphb_format_price( $this->payment->getAmount(), $amountAtts );
				}
				break;
			case 'payment_id':
				if ( isset( $this->payment ) ) {
					$replaceText = $this->payment->getId();
				}
				break;
			case 'payment_method':
				if ( isset( $this->payment ) ) {
					$gateway	 = MPHB()->gatewayManager()->getGateway( $this->payment->getGatewayId() );
					$replaceText = $gateway ? $gateway->getTitle() : '';
				}
				break;
            case 'payment_instructions':
                if ( isset( $this->payment ) ) {
                    $gateway = MPHB()->gatewayManager()->getGateway( $this->payment->getGatewayId() );
                    if ($gateway) {
                        $instructions = $gateway->getInstructions();
                        $replaceText  = wp_kses_post(wpautop(wptexturize($instructions)));
                    }
                }
                break;
			case "services_list":
                if ( isset( $this->booking ) ) {
                    $booking = $this->booking;
                    $reservedRooms = $booking->getReservedRooms();
                    ob_start();
                    foreach ($reservedRooms as $reservedRoom) {
                        $reservedServices = $reservedRoom->getReservedServices();
                        $placeholder = ' &#8212;';
                        if (!empty($reservedServices)) {
                            echo '<ol>';
                            foreach ($reservedServices as $reservedService) {
                                echo '<li>';
                                echo '<h4>' . esc_html($reservedService->getTitle()) . '</h4>';
                                echo '<p>' . $reservedService->getDescription() . '</p>';
                                if ($reservedService->isPayPerAdult()) {
                                    echo ' <em>' . sprintf(_n('x %d guest', 'x %d guests', $reservedService->getAdults(), 'motopress-hotel-booking'), $reservedService->getAdults()) . '</em>';
                                }
                                if ($reservedService->isFlexiblePay()) {
                                    echo ' <em>' . sprintf(_n('x %d time', 'x %d times', $reservedService->getQuantity(), 'motopress-hotel-booking'), $reservedService->getQuantity()) . '</em>';
                                }
                                echo '</li>';
                            }
                            echo '</ol>';
                        } else {
                            echo $placeholder;
                        }
                    }
                    
					$products = get_post_meta($this->booking->getId(),"products_title2",true);
						
					if($products){
						echo "<div style='margin-bottom:10px'>Products : ".$products."</div>";
					}

                    $replaceText = ob_get_contents();
                    ob_end_clean();
                }
                break;
			case "arrival_time":
                if(isset($this->booking)) {
                    $bookingId = $this->booking->getId();
                    $replaceText = get_post_meta($bookingId, 'beach_arrival_time', true);
                }
                break;
			case "adults":
                if(isset($this->booking)) {
                    $bookingId = $this->booking->getId();
                    //$replaceText = get_post_meta($bookingId, '_mphb_adults', true);
                    $booking = $this->booking;
                    $reservedRooms = $booking->getReservedRooms();
                    $adults = 0;   
                    foreach ($reservedRooms as $reservedRoom) {
                        $adults += $reservedRoom->getAdults();
                    }
                    
                    
                
                    if(!$adults){
                        $replaceText = "-";
                    }else{
                        $replaceText = $adults;
                    }
                }
                break;
			case "children":
                if(isset($this->booking)) {
                    $bookingId = $this->booking->getId();
                    //$replaceText = get_post_meta($bookingId, '_mphb_adults', true);
                    $booking = $this->booking;
                    $reservedRooms = $booking->getReservedRooms();
                    $children = 0;   
                    foreach ($reservedRooms as $reservedRoom) {
                        $children += $reservedRoom->getChildren();
                    }
                    
                    
                
                    if(!$children){
                        $replaceText = "-";
                    }else{
                        $replaceText = $children;
                    }
                    /*$replaceText = get_post_meta($bookingId, 'children', true);
                    if(!$replaceText){
                        $replaceText = "-";
                    }*/
                }
                break;
            case "lunch_time":
                if(isset($this->booking)) {
                    $bookingId = $this->booking->getId();
                    $lunch = get_post_meta($bookingId, 'lunch_time', true);
                    $lunch_time = get_lunch_text($lunch);
                    /*if($lunch == '11:59'){
                        $lunch_time = 'Lunch at your sunbed';
                    } else {
                        $lunch_time = $lunch;
                    }*/
                    if($lunch_time){
                        $replaceText = $lunch_time;    
                    }else{
                        $replaceText = "-";    
                    }
                    
                }
                break;
			// Deprecated
		}

        /** @since 3.0.3 Has 3rd and 4th arguments - booking and payment. */
		$replaceText = apply_filters( 'mphb_email_replace_tag', $replaceText, $tag, $this->booking, $this->payment );

		return $replaceText;
	}

}
