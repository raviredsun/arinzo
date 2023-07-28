<?php
if (!defined('WPINC')) {
    die;
}

use \MPHB\Entities;
class ARF_CUSTOM_BOOKING_CREATOR
{
    protected $inputData = array();
    protected $demoData = array();
    protected $postTable = "";
    protected $siteUrl;
    protected $userID;
    protected $mphbBookingNewId;
    private $bookingData = array();

    public function __construct($data)
    {
        global $wpdb;
        $this->inputData = $data;
        $this->postTable = $wpdb->prefix . 'posts';
        $this->siteUrl = get_bloginfo('url');
        $this->userID = 1;
    }


    public function setup()
    {
        $this->setupDemoData();
       // error_log('QD - Record creations');
        $demoData = $this->demoData;
        
        if($this->inputData['paytype'] == "late_charge"){
            $demoData['post_status'] = "pending_late_charge";
        }
        $mphb_booking_new_id = $this->insertQuery($this->postTable, $demoData);
        if ($mphb_booking_new_id) {
            $this->mphbBookingNewId = $mphb_booking_new_id;
            $guid = $this->siteUrl . "/?" . http_build_query(array('post_type' => 'mphb_booking', 'p' => $mphb_booking_new_id));
            $this->updateQuery($this->postTable, array('guid' => $guid), array('ID' => $mphb_booking_new_id));
            $this->generateKey($mphb_booking_new_id);

            $this->updatePostMeta($mphb_booking_new_id, 'paytype', $this->inputData['paytype']);
            
            $check_in_date = $this->changeDateFormat($this->inputData['reservation_start_date']);
            $_POST['check_in_date'] = $check_in_date;
            $this->updatePostMeta($mphb_booking_new_id, 'mphb_check_in_date', $check_in_date);

            $check_out_date = $this->modifyDateFormat($check_in_date);
            $this->updatePostMeta($mphb_booking_new_id, 'mphb_check_out_date', $check_out_date);

            $this->updatePostMeta($mphb_booking_new_id, 'mphb_note', isset($this->inputData['note']) ? $this->inputData['note'] : "");
            $this->updatePostMeta($mphb_booking_new_id, 'mphb_email', $this->inputData['user_email']);
            $this->updatePostMeta($mphb_booking_new_id, 'mphb_first_name', $this->inputData['user_firstname']);
            $this->updatePostMeta($mphb_booking_new_id, 'mphb_last_name', $this->inputData['user_lastname']);
            $this->updatePostMeta($mphb_booking_new_id, 'mphb_phone', $this->inputData['user_phone']);
            foreach ( $this->inputData['services'] as $reservedService ) {
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
            $this->updatePostMeta($mphb_booking_new_id, 'mphb_country', "");
            $this->updatePostMeta($mphb_booking_new_id, 'mphb_state', "");
            $this->updatePostMeta($mphb_booking_new_id, 'mphb_city', "");
            $this->updatePostMeta($mphb_booking_new_id, 'mphb_zip', "");
            $this->updatePostMeta($mphb_booking_new_id, 'mphb_address1', "");
            $this->updatePostMeta($mphb_booking_new_id, 'mphb_ical_prodid', "");
            $this->updatePostMeta($mphb_booking_new_id, 'mphb_ical_summary', "");
            $this->updatePostMeta($mphb_booking_new_id, 'mphb_ical_description', "");
            $this->updatePostMeta($mphb_booking_new_id, 'mphb_language', "");
            $this->updatePostMeta($mphb_booking_new_id, 'mphb_coupon_id', "");
            $this->updatePostMeta($mphb_booking_new_id, '_mphb_checkout_id', mphb_generate_uuid4());

            $this->updatePostMeta($mphb_booking_new_id, 'beach_arrival_time', $this->inputData['beach_arrival_time']);
            $this->updatePostMeta($mphb_booking_new_id, 'lunch_time', $this->inputData['lunch_time']);
            $this->updatePostMeta($mphb_booking_new_id, 'coordinate', $this->inputData['coordinate']);
			$this->updatePostMeta($mphb_booking_new_id, 'hotel_name', isset($this->inputData['hotel_name']) ? $this->inputData['hotel_name'] : "");

            $mphb_reserved_room_data = $this->setupMphbReservedRoom($mphb_booking_new_id);
            $mphb_reserved_room_new_id = $this->insertQuery($this->postTable, $mphb_reserved_room_data);
            $this->updateQuery($this->postTable, array('post_name' => $mphb_reserved_room_new_id), array('ID' => $mphb_reserved_room_new_id));
            $mphb_reserved_room_guid = $this->siteUrl . "/mphb_reserved_room/" . $mphb_reserved_room_new_id . "/";
            $this->updateQuery($this->postTable, array('guid' => $mphb_reserved_room_guid), array('ID' => $mphb_reserved_room_new_id));
            $this->updatePostMeta($mphb_reserved_room_new_id, '_mphb_room_id', $this->inputData['room_id']);
            $this->updatePostMeta($mphb_reserved_room_new_id, '_mphb_rate_id', $this->inputData['rate_id']);

            $multiplier = 1;
            foreach ( $this->inputData['services'] as $reservedService ) {
                $couple_package = get_post_meta($reservedService['id'], 'mphb_couple_package', true);
                if($couple_package){
                    $multiplier = 2;
                    break;
                }
            }
            $this->updatePostMeta($mphb_reserved_room_new_id, '_mphb_adults', $this->inputData['adults']*$multiplier);
            $adults_total = $this->inputData['adults']*$multiplier;
            $this->updatePostMeta($mphb_reserved_room_new_id, '_mphb_children', $this->inputData['child']);
            $this->updatePostMeta($mphb_reserved_room_new_id, '_mphb_services', $this->inputData['services']);
            $this->updatePostMeta($mphb_reserved_room_new_id, '_mphb_guest_name', $this->inputData['user_firstname'] . "" . $this->inputData['user_lastname']);



            if (!empty($this->inputData['product'])) {
                $products = array();
                $products_qty = array();
                $products_title = array();
                $products_title2 = array();
                $price_total = 0;
                foreach ($this->inputData['product'] as $key => $value) {
                    if($value == 1){
                        $price = 0;
                        $qty = 1;
                        if(!empty($service_price[$key])){
                            $products[] = ($key);
                            $price = $service_price[$key];
                            if(isset($max[$key]) && $max[$key] < $adults_total){
                                $qty = ceil($adults_total / $max[$key]);
                                $price = $qty * $price;
                            }
                            $products_qty[$key] = $qty;
                            $price_total += $price;
                            $price = " - €".$price;
                            $products_title[] = get_the_title($key)." x ".$qty.$price;
                            $products_title2[] = get_the_title($key)." x ".$qty;
                        }
                    }
                }
                $this->updatePostMeta($mphb_booking_new_id, 'products_qty', $products_qty);
                $this->updatePostMeta($mphb_booking_new_id, 'products_price_total', $price_total);
                $this->updatePostMeta($mphb_booking_new_id, 'products', $products);
                $this->updatePostMeta($mphb_booking_new_id, 'products_title', implode(" , ", $products_title));
                $this->updatePostMeta($mphb_booking_new_id, 'products_title2', implode(" , ", $products_title2));
            }
            
            if($this->inputData['paytype'] == "late_charge"){
                $products_qty = get_post_meta($mphb_booking_new_id,"products_qty",1);
                if($products_qty){
                    foreach ($products_qty as $key => $value) {
                        $oldstock = $stock = get_post_meta($key,"stock",1);
                        $stock = $stock - $value;
                        update_post_meta($key,"stock",$stock);
                        
                        $newStatusname = mphb_get_status_label($demoData['post_status']);

                        if(file_exists(ABSPATH."product_log.txt")){
                            $text = 'New Booking ID '.$mphb_booking_new_id." Status ".$newStatusname.".Product ".get_the_title($key)." Old Stock ".$oldstock.", New Stock ".$stock.PHP_EOL;
                            $fp = fopen(ABSPATH."product_log.txt", 'a');
                            fwrite($fp, $text);
                        }
                    }
                }
                global $wpdb;
                $booking_ids = $wpdb->get_results("SELECT post_id FROM  $wpdb->postmeta LEFT JOIN ".$wpdb->prefix."posts ON (".$wpdb->prefix."posts.ID = ".$wpdb->postmeta.".post_id) WHERE `meta_key` = 'mphb_check_in_date' AND `meta_value` = '$check_in_date' AND post_status IN ('confirmed','paid_not_refundable','paid_refundable','last_minute','pending_late_charge','paid_late_charge')");
                $booked_places = array();
                $table_selected_ids = array();
                foreach ($booking_ids as $booking_id) {

                    $item_lunch_time = get_post_meta($booking_id->post_id, 'lunch_time', true);
                    if($item_lunch_time == $this->inputData['lunch_time'] || $item_lunch_time == get_lunch_text($this->inputData['lunch_time'])) {
                        $ids = get_post_meta($booking_id->post_id, 'arf_cp_table_id', true);
                        if(is_array($ids)){
                            $table_selected_ids = array_merge($table_selected_ids,$ids);
                        }else{
                            $table_selected_ids[] = $ids;
                        }
                    }

                    $mmm = get_post_meta($booking_id->post_id, 'mphb_place', true);
                    foreach ($mmm as $key => $value) {
                        if(isset($booked_places[$key])){
                            $booked_places[$key] = array_merge($booked_places[$key],$value);
                        }else{
                            $booked_places[$key] = $value;
                        }
                    }
                }
                $total_locations = array();
                $location_count = 0;
                //$loop_location = ceil($adults_total/2);
                $total_person_count = $adults_total + ($this->inputData['child'] ? $this->inputData['child'] : 0);
                $loop_location = 0;
                if($total_person_count <= 3){
                    $loop_location = 1;
                }else if($total_person_count <= 5){
                    $loop_location = 2;
                }else if($total_person_count <= 7){
                    $loop_location = 3;
                }else if($total_person_count <= 9){
                    $loop_location = 4;
                }else if($total_person_count <= 11){
                    $loop_location = 5;
                }else if($total_person_count <= 13){
                    $loop_location = 6;
                }else if($total_person_count <= 15){
                    $loop_location = 7;
                }else if($total_person_count <= 17){
                    $loop_location = 8;
                }else if($total_person_count <= 19){
                    $loop_location = 9;
                }else{
                    $loop_location = 10;
                }
                foreach ( $this->inputData['services'] as $reservedService ) {
                    $location = get_post_meta($reservedService['id'],"location_on_order",1);

                    foreach ($location as $location_value) {
                        $block_locations = array();
                        if(!empty($booked_places[$location_value])){
                            $block_locations = $booked_places[$location_value];
                        }

                        $ddd = get_field("location_names",$location_value);
                        $arr = array();
                        $ddd = explode("|",$ddd);
                        if($ddd){ foreach($ddd AS $kk => $vv){ $arr = array_merge($arr,explode(",",$vv)); } }
                        if($block_locations){
                            foreach ($arr as $arr_value) {
                                if(!in_array($arr_value, $block_locations)){
                                    $total_locations[$location_value][] = $arr_value;
                                    $location_count++;
                                }
                                if($location_count == $loop_location){
                                    break;
                                }
                            }
                        }else{
                            foreach ($arr as $arr_value) {
                                $total_locations[$location_value][] = $arr_value;
                                $location_count++;
                                if($location_count == $loop_location){
                                    break;
                                }
                            }
                        }
                        if($location_count == $loop_location){
                            break;
                        }
                    }
                }
                update_post_meta($mphb_booking_new_id, "mphb_place", $total_locations);

               

                $total_tables = array();

                $args = array(
                    'post_type' => 'arf_pt_table',
                    'posts_per_page' => -1,
                    'post_status' => 'publish',
                    'orderby' => 'post_title',
                    'order' => 'ASC'
                );
                
                $is_subed = "";
                if($this->inputData['lunch_time']){
                    $lunch_time_type = get_post_meta($this->inputData['lunch_time'],"lunch_time_type",1);
                    if($lunch_time_type == "lunch_at_your_sunbed"){
                        $is_subed = "1";
                    }
                }
                if($is_subed){
                    $args['meta_query'] = array(
                        array(
                            'key' => 'is_subed',
                            'value' => "1"
                        )
                    );
                }else{
                    $args['meta_query'] = array(
                        'relation' => 'OR',
                        array(
                            'key' => 'is_subed',
                            'value' => "0"
                        ),
                        array(
                            'key' => 'is_subed',
                            'compare' => "NOT EXISTS"
                        )
                    );
                }
                if($table_selected_ids) {
                    $args['post__not_in'] = $table_selected_ids;
                }
                $arf_pt_tables = get_posts($args);
                foreach ($arf_pt_tables as $kk => $table) {
                    $auto_booking = get_post_meta($table->ID,"auto_booking",true);
                    if($auto_booking == 1){
                        $total_tables[] = $table->ID;
                        if(count($total_tables) == $loop_location){
                            break;
                        }
                    }
                }
                update_post_meta($mphb_booking_new_id, "arf_cp_table_id", $total_tables);
            }
            $this->updatePostMeta($mphb_reserved_room_new_id, '_mphb_uid', mphb_generate_uid());

            $comment_data = $this->setupCommentData($mphb_booking_new_id, $this->userID, current_time('Y-m-d H:i:s'), 'Status changed from New to Auto Draft.');
            $this->insertComment($comment_data);
            $comment_data = $this->setupCommentData($mphb_booking_new_id, $this->userID, current_time('Y-m-d H:i:s'), 'Status changed from Auto Draft to Confirmed.');
            $this->insertComment($comment_data);
            $comment_data = $this->setupCommentData($mphb_booking_new_id, $this->userID, current_time('Y-m-d H:i:s'), '\"Approved Booking Email\" mail was sent to customer.');
            $this->insertComment($comment_data);
            $comments_count = wp_count_comments($mphb_booking_new_id);
            //'post_status' => $this->inputData['payment_status'],
            $this->updateQuery($this->postTable, array( 'post_name' => $mphb_booking_new_id, 'comment_count' => $comments_count->total_comments), array('ID' => $mphb_booking_new_id));
            $this->setBookingData();
            //$check = $this->sendCustomerMail();
            //error_log('QD - Send mail on creation');
            /**
             * Qadisha - QD - Enabled on 2022-06-11 due to email not sent.
             */
            //$this->sendCustomerMail();
            //$this->sendAdminMail();
            $priceBreakdown = $this->priceBreakdown($mphb_booking_new_id, array('checkInDate'=>$check_in_date, 'checkOutDate'=>$check_out_date));
             //echo "<pre>"; print_r($priceBreakdown); echo "</pre>";die; 
            $this->updatePostMeta($mphb_booking_new_id, '_mphb_booking_price_breakdown', json_encode($priceBreakdown));
            $this->updatePostMeta($mphb_booking_new_id, 'mphb_total_price', $priceBreakdown["total"]);
            return $booking = MPHB()->getBookingRepository()->findById( $this->mphbBookingNewId , true);
        }
        return false;
    }

    private function setBookingData() {
        $this->bookingData = get_post_meta($this->mphbBookingNewId);
    }

    private function insertQuery($table, $data)
    {
        global $wpdb;
        $wpdb->insert($table, $data);
        return $wpdb->insert_id;
    }

    private function updateQuery($table, $data, $where, $format = null, $where_format = null)
    {
        global $wpdb;
        return $wpdb->update($table, $data, $where, $format = null, $where_format = null);
    }

    private function insertComment($data)
    {
        return wp_insert_comment($data);
    }

    private function generateKey($id)
    {
        $key = uniqid("booking_{$id}_", true);
        update_post_meta($id, 'mphb_key', $key);
        return $key;
    }

    private function setupCommentData($postID, $userID, $date, $content)
    {
        return array(
            'comment_post_ID' => $postID,
            'comment_author' => '',
            'comment_author_email' => '',
            'comment_author_url' => '',
            'comment_author_IP' => '',
            'comment_date' => $date,
            'comment_date_gmt' => $date,
            'comment_content' => 'Status changed from New to Auto Draft.',
            'comment_karma' => 0,
            'comment_approved' => '1',
            'comment_agent' => '',
            'comment_type' => 'mphb_booking_log',
            'comment_parent' => 0,
            'user_id' => $userID
        );
    }

    /**
     * Qadisha - QD - Enabled on 2022-06-11 due to email not sent.
     * Changed post_status
     */
    private function setupMphbReservedRoom($postID)
    {
        return array(
            'post_author' => $this->userID,
            'post_date' => current_time('Y-m-d H:i:s'),
            'post_date_gmt' => current_time('Y-m-d H:i:s'),
            'post_content' => "",
            'post_content_filtered' => "",
            'post_title' => "",
            'post_excerpt' => "",
            'post_status' => "publish",
            'post_type' => "mphb_reserved_room",
            'comment_status' => "closed",
            'ping_status' => "closed",
            'post_password' => "",
            'post_name' => "",
            'to_ping' => "",
            'pinged' => "",
            'post_modified' => current_time('Y-m-d H:i:s'),
            'post_modified_gmt' => current_time('Y-m-d H:i:s'),
            'post_parent' => $postID,
            'menu_order' => 0,
            'post_mime_type' => "",
            'guid' => "",
        );
    }

    private function setupDemoData()
    {
        $this->demoData = array(
            'post_author' => $this->userID,
            'post_date' => current_time('Y-m-d H:i:s'),
            'post_date_gmt' => '0000-00-00 00:00:00',
            'post_content' => '',
            'post_content_filtered' => '',
            'post_title' => '',
            'post_excerpt' => '',
            //'post_status' => 'pending',
            'post_status' => 'not-paid',
            'post_type' => 'mphb_booking',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_password' => "",
            'post_name' => "",
            'to_ping' => "",
            'pinged' => "",
            'post_modified' => current_time('Y-m-d H:i:s'),
            'post_modified_gmt' => '0000-00-00 00:00:00',
            'post_parent' => 0,
            'menu_order' => 0,
            'post_mime_type' => "",
            'guid' => "",
        );
    }

    private function updatePostMeta($id, $key, $value)
    {
        return update_post_meta($id, $key, $value);
    }

    private function changeDateFormat($date, $format = 'Y-m-d')
    {
        $date = new DateTime($date);
        return $date->format($format);
    }

    private function modifyDateFormat($date, $format = 'Y-m-d')
    {
        $date = new DateTime($date);
        $date->modify('+1 day');
        return $date->format($format);
    }

    private function sendCustomerMail()
    {
        return MPHB()->emails()->getMailer()->send(
            $this->bookingData['mphb_email'][0],
            $this->getSubject("mphb_email_customer_pending_booking_subject"),
            $this->getMessage("mphb_email_customer_pending_booking_header", "mphb_email_customer_pending_booking_content", "mphb_email_footer_text"));
    }

    private function sendAdminMail()
    {
        return MPHB()->emails()->getMailer()->send(
            $this->getHotelAdminEmail(),
            $this->getSubject("mphb_email_admin_pending_booking_subject"),
            $this->getMessage("mphb_email_admin_pending_booking_header", "mphb_email_admin_pending_booking_content", "mphb_email_footer_text"));
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
        $checkInDate	 = \DateTime::createFromFormat( 'Y-m-d', $data['checkInDate'] );
        $checkOutDate	 = \DateTime::createFromFormat( 'Y-m-d', $data['checkOutDate'] );
        $bookingAtts = array(
            'id'     => $bookingId,
            'check_in_date'	 => $checkInDate,
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
