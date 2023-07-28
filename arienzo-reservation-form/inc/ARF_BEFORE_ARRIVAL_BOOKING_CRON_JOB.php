<?php

use \MPHB\Views;

class ARF_BEFORE_ARRIVAL_BOOKING_CRON_JOB {
    private $bookingData = array();
    private $booking = array();
    private function modifyDateFormat($date, $format = 'Y-m-d')
    {
        $date = new DateTime($date);
        $date->modify('+1 day');
        return $date->format($format);
    }

    private function sendCustomerMail($email)
    {
        return MPHB()->emails()->getMailer()->send(
            $email,
            $this->getSubject("mphb_email_customer_pending_booking_subject"),
            $this->getMessage("mphb_email_customer_pending_booking_header", "mphb_email_customer_pending_booking_content", "mphb_email_footer_text"));
    }

    public function init() {
        
        $date = date('Y-m-d',strtotime("+1 days"));

        $atts = array(
            'posts_per_page'   => -1,
            'post_type'        => 'mphb_booking',
            'post_status' => array('confirmed','paid_not_refundable','paid_refundable','last_minute','pending_late_charge'),
            'meta_key'         => 'mphb_check_in_date',
            'meta_value'       => $date,
            'fields'           => 'ids',
            'orderBy'          => 'ID',
            'order'            => 'ASC'
        );
        $query = new WP_Query( $atts );

        if($query->have_posts()) {
            $ids = $query->posts;
            foreach ($ids as $id) {
                $this->booking = MPHB()->getBookingRepository()->findById( $id, true );
                $this->setBookingData($id);
                //MPHB()->emails()->sendReminderBookingEmail($booking);
                MPHB()->emails()->getMailer()->send(
                $this->bookingData['mphb_email'][0],
                $this->getSubject("mphb_email_customer_before_arrival_booking_subject"),
                $this->getMessage("mphb_email_customer_before_arrival_booking_header", "mphb_email_customer_before_arrival_booking_content", "mphb_email_footer_text"));
            }
        }

    }

    public function init2() {
        $id = 91015;
        $this->booking = MPHB()->getBookingRepository()->findById( $id, true );
        $this->setBookingData($id);
        //MPHB()->emails()->sendReminderBookingEmail($booking);
        MPHB()->emails()->getMailer()->send(
        $this->bookingData['mphb_email'][0],
        $this->getSubject("mphb_email_customer_before_arrival_booking_subject"),
        $this->getMessage("mphb_email_customer_before_arrival_booking_header", "mphb_email_customer_before_arrival_booking_content", "mphb_email_footer_text"));
    
    }

    /*public function init2() {
        $id = 29024;
        $this->booking = MPHB()->getBookingRepository()->findById( $id, true );
        $this->setBookingData($id);
        //echo "<pre>"; print_r($this->bookingData); echo "</pre>";die; 
        //MPHB()->emails()->sendReminderBookingEmail($booking);
        //echo "<pre>"; print_r($this->bookingData['mphb_email'][0]); echo "</pre>";die; 
        $text = "Booking ID - %booking_id%
        Booking Edit Link - %booking_edit_link%
        Booking Total Price - %booking_total_price%
        Check-in Date - %check_in_date%
        Check-out Date - %check_out_date%
        Check-in Time - %check_in_time%
        Check-out Time - %check_out_time%
        Customer First Name - %customer_first_name%
        Customer Last Name - %customer_last_name%
        Customer Email - %customer_email%
        Customer Phone - %customer_phone%
        Customer Country - %customer_country%
        Customer Address - %customer_address1%
        Customer City - %customer_city%
        Customer State/County - %customer_state%
        Customer Postcode - %customer_zip%
        Customer Note - %customer_note%
        Reserved Accommodations Details - %reserved_rooms_details%
        Price Breakdown - %price_breakdown%
        Services List - %services_list%
        Arrival time - %arrival_time%
        Lunch time - %lunch_time%
        Adults - %adults%
        Children - %children%
        Sub Total - %subtotal%
        Total - %total%";
        $msg = $this->getMessage("mphb_email_customer_before_arrival_booking_header", "mphb_email_customer_before_arrival_booking_content", "mphb_email_footer_text");
        $content = $this->replaceTags($text);
        $messageContent = $content;
        $message = $this->getMessageHeader("mphb_email_customer_before_arrival_booking_header");
        $message .= $messageContent;
        $message = apply_filters('the_content', $message);
        $message .= $this->getMessageFooter("mphb_email_footer_text");
        $message = $this->applyStyles($message);
         
         echo "<pre>"; print_r($message); echo "</pre>";die; 
        MPHB()->emails()->getMailer()->send(
        $this->bookingData['mphb_email'][0],
        $this->getSubject("mphb_email_customer_before_arrival_booking_subject"),
        $msg
        );
    }*/


    private function setBookingData($id) {
        $this->bookingData = get_post_meta($id);
    }
    

    private function getMessage($header, $content, $footer)
    {
        $messageContent = $this->getMessageContent($content);
        $message = $this->getMessageHeader($header);
        $message .= $messageContent;
        $message = apply_filters('the_content', $message);
        $message .= $this->getMessageFooter($footer);
        $message = $this->applyStyles($message);

        return $message;
    }

    private function getMessageContent($content)
    {
        $template = get_option($content);
        return $this->replaceTags($template);
    }

    private function getMessageHeader($header)
    {
        $headerText = get_option($header);
        $headerText = $this->replaceTags($headerText);
        ob_start();

        $templateId = "email_customer_pending_booking";
        require MPHB()->getPluginPath('includes/emails/templates/email-header.php');
        $header = ob_get_contents();
        ob_end_clean();
        return $header;
    }

    private function getMessageFooter($footer)
    {
        $footerText = get_option($footer);
        $footerText = $this->replaceTags($footerText);
        ob_start();
        $footerText = apply_filters('the_content', $footerText);
        require MPHB()->getPluginPath('includes/emails/templates/email-footer.php');
        $footer = ob_get_contents();
        ob_end_clean();
        return $footer;
    }

    private function getSubject($optionName)
    {

        $subjectTemplate = $footerText = get_option($optionName);;

        $subject = $this->replaceTags($subjectTemplate);

        return $subject;
    }

    private function getTags()
    {
        return array(
            "site_title" =>
                array(
                    "name" => "site_title",
                    "description" => "Site title (set in Settings > General)",
                    "deprecated" => false,
                    "deprecated_title" => "",
                    "inner_tags" => array()
                ),
            "booking_id" =>
                array(
                    "name" => "booking_id",
                    "description" => "Booking ID",
                    "deprecated" => false,
                    "deprecated_title" => "",
                    "inner_tags" => array()
                ),
            "booking_edit_link" =>
                array(
                    "name" => "booking_edit_link",
                    "description" => "Booking Edit Link",
                    "deprecated" => false,
                    "deprecated_title" => "",
                    "inner_tags" => array()
                ),
            "booking_total_price" =>
                array(
                    "name" => "booking_total_price",
                    "description" => "Booking Total Price",
                    "deprecated" => false,
                    "deprecated_title" => "",
                    "inner_tags" => array(),
                ),

            "check_in_date" =>
                array(
                    "name" => "check_in_date",
                    "description" => "Check-in Date",
                    "deprecated" => false,
                    "deprecated_title" => "",
                    "inner_tags" => array()
                ),
            "check_out_date" =>
                array(
                    "name" => "check_out_date",
                    "description" => "Check-out Date",
                    "deprecated" => false,
                    "deprecated_title" => "",
                    "inner_tags" => array(),
                ),

            "check_in_time" =>
                array(
                    "name" => "check_in_time",
                    "description" => "Check-in Time",
                    "deprecated" => false,
                    "deprecated_title" => "",
                    "inner_tags" => array(),
                ),
            "check_out_time" =>
                array(
                    "name" => "check_out_time",
                    "description" => "Check-out Time",
                    "deprecated" => false,
                    "deprecated_title" => "",
                    "inner_tags" => array()
                ),
            "customer_first_name" =>
                array(
                    "name" => "customer_first_name",
                    "description" => "Customer First Name",
                    "deprecated" => false,
                    "deprecated_title" => "",
                    "inner_tags" => array()
                ),
            "customer_last_name" =>
                array(
                    "name" => "customer_last_name",
                    "description" => "Customer Last Name",
                    "deprecated" => false,
                    "deprecated_title" => "",
                    "inner_tags" => array()
                ),
            "customer_email" =>
                array(
                    "name" => "customer_email",
                    "description" => "Customer Email",
                    "deprecated" => false,
                    "deprecated_title" => "",
                    "inner_tags" => array(),
                ),
            "customer_phone" =>
                array(
                    "name" => "customer_phone",
                    "description" => "Customer Phone",
                    "deprecated" => false,
                    "deprecated_title" => "",
                    "inner_tags" => array(),
                ),
            "customer_country" =>
                array(
                    "name" => "customer_country",
                    "description" => "Customer Country",
                    "deprecated" => false,
                    "deprecated_title" => "",
                    "inner_tags" => array(),
                ),

            "customer_address1" =>
                array(
                    "name" => "customer_address1",
                    "description" => "Customer Address",
                    "deprecated" => false,
                    "deprecated_title" => "",
                    "inner_tags" => array(),
                ),
            "customer_city" =>
                array(
                    "name" => "customer_city",
                    "description" => "Customer City",
                    "deprecated" => false,
                    "deprecated_title" => "",
                    "inner_tags" => array(),
                ),
            "customer_state" =>
                array(
                    "name" => "customer_state",
                    "description" => "Customer State/County",
                    "deprecated" => false,
                    "deprecated_title" => "",
                    "inner_tags" => array(),
                ),
            "customer_zip" =>
                array(
                    "name" => "customer_zip",
                    "description" => "Customer Postcode",
                    "deprecated" => false,
                    "deprecated_title" => "",
                    "inner_tags" => array()
                ),
            "customer_note" =>
                array(
                    "name" => "customer_note",
                    "description" => "Customer Note",
                    "deprecated" => false,
                    "deprecated_title" => "",
                    "inner_tags" => array()
                ),
            "reserved_rooms_details" =>
                array(
                    "name" => "reserved_rooms_details",
                    "description" => "Reserved Accommodations Details",
                    "deprecated" => false,
                    "deprecated_title" => "",
                    "inner_tags" => array()
                ),
            "price_breakdown" => array(
                "name" => "price_breakdown",
                "description" => "Price Breakdown",
                "deprecated" => false,
                "deprecated_title" => "",
                "inner_tags" => array()
            ),
            "view_booking_link" => array(
                "name" => "view_booking_link",
                "description" => "Booking Link",
                "deprecated" => false,
                "deprecated_title" => "",
                "inner_tags" => array()
            )
        );
    }

    private function applyStyles($html)
    {
        ob_start();
        require MPHB()->getPluginPath('includes/emails/templates/email-styles.php');
        $styles = ob_get_clean();
        $emogrifier = new \MPHB\Libraries\Emogrifier\Emogrifier($html, $styles);
        if (!function_exists('mb_convert_encoding')) {
            mphb_get_polyfill_for('mb_convert_encoding');
        }
        $html = $emogrifier->emogrify();
        return $html;
    }

    private function getHotelAdminEmail()
    {
        $adminEmail = get_option('mphb_email_hotel_admin_email', '');
        if (empty($adminEmail)) {
            $adminEmail = $this->getDefaultHotelAdminEmail();
        }
        return $adminEmail;
    }

    private function getDefaultHotelAdminEmail()
    {
        return get_bloginfo('admin_email');
    }

    private function _generateTagsFindString($tags)
    {
        return '/%' . join('%|%', wp_list_pluck($tags, 'name')) . '%/s';
    }

    private function replaceTag($match)
    {

        $tag = str_replace('%', '', $match[0]);
        /*if (isset($_GET['run']) && $_GET['run'] == 'testmail') {
             echo "<pre>"; print_r($tag); echo "</pre>";
        }*/
        $replaceText = '';
        switch ($tag) {
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
                    $expireTime  = $this->booking->retrieveExpiration( 'user' );
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
                    $amountAtts  = array(
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
                    $gateway     = MPHB()->gatewayManager()->getGateway( $this->payment->getGatewayId() );
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
                }
                break;
            case "lunch_time":
                if(isset($this->booking)) {
                    $bookingId = $this->booking->getId();
                    $lunch = get_post_meta($bookingId, 'lunch_time', true);
                    $lunch_time = get_lunch_text($lunch);
                    if($lunch_time){
                        $replaceText = $lunch_time;    
                    }else{
                        $replaceText = "-";    
                    }
                    
                }
                break;

            default:
                $replaceText = "";
                break;
        }

        return $replaceText;
    }

    public function replaceTags($content)
    {
        $tags = $this->getTags();
        if (!empty($tags)) {
            $content = preg_replace_callback($this->_generateTagsFindString($tags), array($this, 'replaceTag'), $content);
        }

        return $content;
    }

    private function priceBreakdown($bookingId, $data) {
        $reservedRooms = MPHB()->getReservedRoomRepository()->findAllByBooking( $bookingId );
        $checkInDate     = \DateTime::createFromFormat( 'Y-m-d', $data['checkInDate'] );
        $checkOutDate    = \DateTime::createFromFormat( 'Y-m-d', $data['checkOutDate'] );
        $bookingAtts = array(
            'check_in_date'  => $checkInDate,
            'check_out_date' => $checkOutDate,
            'reserved_rooms' => $reservedRooms
        );
        $booking = Entities\Booking::create( $bookingAtts );
        return $booking->getPriceBreakdown();
    }

    public function sendMail($booking) {
        do_action( 'mphb_booking_status_changed', $booking, "" );
    }
}
