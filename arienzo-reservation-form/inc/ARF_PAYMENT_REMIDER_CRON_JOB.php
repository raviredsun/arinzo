<?php
class ARF_PAYMENT_REMIDER_CRON_JOB {
    private $bookingData = array();
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

        $date = date('Y-m-d',strtotime("-6 days"));

        $atts = array(
            'posts_per_page'   => -1,
            'post_type'        => 'mphb_booking',
            'post_status'      => 'pending_late_charge',
            "date_query" => array(
                array(
                    'column' => 'post_date',
                    'before' => $date,
                ),
            ),
            /*'meta_query' => array(
                array(
                    'key'        => 'mphb_check_in_date',
                    'value' => $date,
                    'type' => 'DATE',
                    'compare' => '>='
               ),   
            ),*/
            /*'meta_key'         => 'mphb_check_in_date',
            'meta_value'       => $date,*/
            'fields'           => 'ids',
            'orderBy'          => 'ID',
            'order'            => 'ASC'
        );
        $query = new WP_Query( $atts );

        if($query->have_posts()) {
            $ids = $query->posts;
            foreach ($ids as $id) {
                if(get_the_date("D",$id) == date("D")){
                    $booking = MPHB()->getBookingRepository()->findById( $id, true );
                    $this->sendMail($booking);
                }
                //$this->setBookingData($id);
                //MPHB()->emails()->sendReminderBookingEmail($booking);
                /*MPHB()->emails()->getMailer()->send(
                $this->bookingData['mphb_email'][0],
                $this->getSubject("mphb_email_customer_pending_late_charge_booking_subject"),
                $this->getMessage("mphb_email_customer_pending_late_charge_booking_header", "mphb_email_customer_pending_late_charge_booking_content", "mphb_email_footer_text"));*/
            }
        }die;

    }


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

        $replaceText = '';
        switch ($tag) {

            // Global
            case 'booking_id':
                $replaceText = $this->mphbBookingNewId;
                break;
            case 'site_title':
                $replaceText = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
                break;
            case 'booking_edit_link':
                if (isset($this->booking)) {
                    $replaceText = mphb_get_edit_post_link_for_everyone($this->mphbBookingNewId);
                }
                break;
            case 'check_in_date':
                $replaceText = $this->bookingData["mphb_check_in_date"][0];
                break;
            case 'check_out_date':
                $replaceText = $this->bookingData["mphb_check_out_date"][0];
                break;
            case 'customer_first_name':
                $replaceText = $this->bookingData["mphb_first_name"][0];
                break;
            case 'customer_last_name':
                $replaceText = $this->bookingData["mphb_last_name"][0];
                break;
            case 'customer_email':
                $replaceText = $this->bookingData["mphb_email"][0];
                break;
            case 'customer_phone':
                $replaceText = $this->bookingData["mphb_phone"][0];
                break;
            case 'customer_country':
                $replaceText = $this->bookingData["mphb_country"][0];
                break;
            case 'customer_address1':
                $replaceText = $this->bookingData["mphb_address1"][0];
                break;
            case 'customer_city':
                $replaceText = $this->bookingData["mphb_city"][0];
                break;
            case 'customer_state':
                $replaceText = $this->bookingData["mphb_state"][0];
                break;
            case 'customer_zip':
                $replaceText = $this->bookingData["mphb_zip"][0];
                break;
            case 'customer_note':
                $replaceText = $this->bookingData["mphb_note"][0];
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
