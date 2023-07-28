<?php
if (!defined('WPINC')) {
    die;
}

if (!defined('SURVEYMONKEY_BEARER_TOKEN')) {
    define("SURVEYMONKEY_BEARER_TOKEN", "bearer uX2r-rEuS8sFuxabPuSDfDtB7EkfJhC4SP7XFyRNAbdPId9v3d6xjEqCJtGF1Vp2m7hk98oBD8gCSJnOgrzidLz75aa-mPeFWWg2Az2VVMkB4L2AIg-vpCSNuYrX4nZo");
}

if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'inc/arf_admin.php';
    require_once plugin_dir_path(__FILE__) . 'inc/arf_dashboard.php';
    require_once plugin_dir_path(__FILE__) . 'inc/arf_lunch_dates_form.php';
    require_once plugin_dir_path(__FILE__) . 'inc/arf_location_map.php';
    
    require_once plugin_dir_path(__FILE__) . 'inc/register_table_post_type.php';
    require_once plugin_dir_path(__FILE__) . 'inc/arf_table_calendar_menu.php';
    require_once plugin_dir_path(__FILE__) . 'inc/Arf_tables-calendar.php';
    
    require_once plugin_dir_path(__FILE__) . 'inc/arf_services_page.php';

}
require_once plugin_dir_path(__FILE__) . 'inc/shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'inc/shortcode_map.php';
// Front-end
function front_end_scripts()
{
    wp_register_style('arf_google_fonts', 'https://fonts.googleapis.com/css?family=Work+Sans:300,400,500,600', array(), '', 'all');
    wp_register_style('arf_bootstrap_css', plugins_url('assets/css/bootstrap.min.css', __FILE__), array(), '4.2.1', 'all');
    wp_register_style('arf_style_css', plugins_url('assets/css/arf_style.css', __FILE__), array(), '', 'all');
    wp_register_style('arf_vendors_css', plugins_url('assets/css/arf_vendors.css', __FILE__), array(), '', 'all');
    wp_register_style('arf_intTelInput_css', plugins_url('assets/css/intlTelInput.css', __FILE__), array(), '', 'all');

    wp_register_script('arf_jquery', plugins_url('assets/js/jquery-3.2.1.min.js', __FILE__), array(), '', false);
    wp_register_script('modernizr_js', plugins_url('assets/js/modernizr.js', __FILE__), array(), '2.8.3', false);
    wp_register_script('arf_common_scripts_js', plugins_url('assets/js/common_scripts.min.js', __FILE__), array('arf_jquery'), '', false);
    wp_register_script('arf_velocity_js', plugins_url('assets/js/velocity.min.js', __FILE__), array(), '1.1.0', true);
    wp_register_script('arf_script_js', plugins_url('assets/js/arf_script.js', __FILE__), array('arf_common_scripts_js'), '', true);
    wp_register_script('arf_booking_form', plugins_url('assets/js/arf_booking_form.js', __FILE__), array(), '', true);
    wp_register_script('arf_booking_form_hotel', plugins_url('assets/js/arf_booking_form_hotel.js', __FILE__), array(), '1.0.1', true);
    wp_register_script('arf_intTelInput_js', plugins_url('assets/js/intlTelInput.js', __FILE__), array(), '', true);
    wp_register_script('arf_booking_form_map', plugins_url('assets/js/arf_booking_form_map.js', __FILE__), array(), '', true);

}

add_action('wp_enqueue_scripts', 'front_end_scripts');

add_action('wp_ajax_nopriv_arf_ajax_action', 'arf_ajax_search_handler');
add_action('wp_ajax_arf_ajax_action', 'arf_ajax_search_handler');

function arf_ajax_search_handler()
{
    $data = array(
        'httpStatus' => 200,
        'data' => array(),
        'message' => 'ok',
        'errors' => array()
    );
    $errors = array();
    $startDate = $_POST['start'];
    $endDate = $_POST['end'];
    $startDate = date("Y-m-d", strtotime(str_replace('-', '/', $startDate)));
    $endDate = date("Y-m-d", strtotime(str_replace('-', '/', $endDate)));
    if (!validateDate($startDate)) {
        $errors['start'] = "Invalid Date Format";
    }
    if (!validateDate($endDate)) {
        $errors['end'] = "Invalid Date Format";
    }
    if (!empty($error)) {
        $data['errors'] = $errors;
        echo json_encode($data);
        wp_die();
    }

    $rooms = arfGetRooms();
    $bookedRooms = arfGetBookedRooms($startDate, $endDate);
    if (count($bookedRooms) < 1)
        $data['data'] = $rooms;
    echo json_encode($data);
    wp_die();
}


function arfGetRooms()
{
    global $wpdb;
    $arf = apply_filters('arf_database', $wpdb);
    $query = "SELECT DISTINCT room_types.ID AS id, room_types.post_title,
COUNT(DISTINCT rooms.ID) AS COUNT FROM " . $arf->prefix . "posts AS rooms 
INNER JOIN " . $arf->prefix . "postmeta AS room_type_ids ON rooms.ID = room_type_ids.post_id AND room_type_ids.meta_key = 'mphb_room_type_id' 
INNER JOIN " . $arf->prefix . "posts AS room_types ON room_type_ids.meta_value = room_types.ID WHERE rooms.post_type = 'mphb_room' AND rooms.post_status = 'publish' 
AND room_type_ids.meta_value IS NOT NULL AND room_type_ids.meta_value != '' AND room_types.post_type = 'mphb_room_type' AND room_types.post_status = 'publish' 
GROUP BY room_type_ids.meta_value ORDER BY room_type_ids.meta_value DESC";

    return $arf->get_results($query, ARRAY_A);
}

function arfGetBookedRooms($startDate, $endDate)
{
    global $wpdb;
    $arf = apply_filters('arf_database', $wpdb);
    $sql = $wpdb->prepare("SELECT DISTINCT room_id.meta_value AS ID
FROM " . $arf->prefix . "posts AS reserved_rooms
INNER JOIN " . $arf->prefix . "postmeta AS room_id
ON room_id.post_id = reserved_rooms.ID
AND room_id.meta_key = '_mphb_room_id'
INNER JOIN " . $arf->prefix . "posts AS bookings
ON bookings.ID = reserved_rooms.post_parent
INNER JOIN " . $arf->prefix . "postmeta AS check_in
ON check_in.post_id = bookings.ID
AND check_in.meta_key = 'mphb_check_in_date'
INNER JOIN " . $arf->prefix . "postmeta AS check_out
ON check_out.post_id = bookings.ID
AND check_out.meta_key = 'mphb_check_out_date'
WHERE reserved_rooms.post_type = 'mphb_reserved_room'
AND reserved_rooms.post_status = 'publish'
AND bookings.post_status IN ('pending-user','pending-payment','pending','confirmed')
AND check_out.meta_value > %s
AND check_in.meta_value < %s", $startDate, $endDate);

    return $results = $wpdb->get_results($sql);

}

function validateDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

function irf_mail_set_content_type()
{
    return "text/html";
}

add_filter('wp_mail_content_type', 'irf_mail_set_content_type');

add_action('pre_get_posts', 'arf_qr_code_change_func');

function arf_qr_code_change_func($query)
{
    if(isset($_POST['booking_id'])){
        $booking_id = (int)$_POST['booking_id'];
        $status = $_POST['qr_code_checked_action'];
        $current_user = wp_get_current_user();
        if ((current_user_can('administrator') || ($current_user && strpos($current_user->user_email, "@arienzobeachclub.com"))) && wp_verify_nonce($_POST['_wpnonce'], 'arf_qr_code_checked_action')) {
            update_post_meta($booking_id, 'arf_qr_code_status', esc_attr($status));
            unset($_POST);
        }
    }
}

function arf_qr_code_change_status_front()
{
    $booking_id = $_GET['booking_id'];
    $qr_code_status = get_post_meta($booking_id, 'arf_qr_code_status', true);
    $current_user = wp_get_current_user();
    
    if (current_user_can('administrator') || ($current_user && strpos($current_user->user_email, "@arienzobeachclub.com")) ) {
        if (empty($qr_code_status)) { ?>
            <form action="" method="post">
                <input type="hidden" name="qr_code_checked_action" value="checked">
                <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                <?php wp_nonce_field('arf_qr_code_checked_action'); ?>
                <button type="submit">CHECK - IN</button>
            </form>
        <?php } else { ?>
            QR code status: <?php echo $qr_code_status ?>
        <?php } ?>
    <?php } else { 
        if (!empty($qr_code_status)) { 
            wp_redirect( get_site_url().'/tracking/?tracking='.encrypt_decrypt($booking_id) );exit;
        }
    ?>
        <p>
            <?php _e('Your booking is confirmed. Thank You!', 'motopress-hotel-booking'); ?>
        </p>
    <?php }
}


function back_end_scripts($hook) {
    wp_enqueue_script('arf_admin', plugins_url('assets/js/arf_admin.js', __FILE__), array(), '1.0.1', false);
    wp_localize_script(
        'arf_admin',
        'arf_admin',
        array( 'ajaxurl' => admin_url( 'admin-ajax.php' ))
    );
}

add_action('admin_enqueue_scripts', 'back_end_scripts');


function getBookingRules () {
    $data = get_option( 'mphb_booking_rules_custom', array() );
    $arr = array();
    if(count($data) > 0) {
        foreach ($data as $val) {
            if($val['date_from'] != $val['date_to']) {
                $start_day = new DateTime($val['date_from']);
                $end_day = new DateTime($val['date_to']);
                $period = new DatePeriod(
                    $start_day,
                    new DateInterval('P1D'),
                    $end_day->modify('+1 day')
                );
                foreach ($period as $key => $value) {
                    $arr[] = $value->format('Y-m-d');
                }
            } else {
                $arr[] =  $val['date_from'];
            }
        }
    }
    return $arr;
}

function js_str($s)
{
    return '"' . addcslashes($s, "\0..\37\"\\") . '"';
}

function js_array($array)
{
    $temp = array_map('js_str', $array);
    return '[' . implode(',', $temp) . ']';
}

//Surveymonkey api - add data to contact list
function survey_monkey_add_contact($data) {
    $options = array(
        CURLOPT_URL => "https://api.surveymonkey.net/v3/contacts",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "Authorization: " . SURVEYMONKEY_BEARER_TOKEN,
        ),
    );

    try {
        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        curl_close($curl);
    } catch (Exception $e) {
        //throw new Exception("Invalid URL", 0, $e);
    }
}

function arf_booking_ajax_request() {
    if(empty($_POST)){
        echo json_encode(array('success' => false, 'messages' => array('error' => 'Something goes wrong!') ));
        wp_die();
    }
    parse_str($_POST['data'], $data);
    $nonce = $_POST['_nonce'];
    if ( ! wp_verify_nonce( $nonce, 'arf_form_action' ) && !empty($_POST['website'])) {
        echo json_encode(array('success' => false, 'messages' => array('error' => 'Something goes wrong!')));
        wp_die();
    }
    require_once plugin_dir_path(__FILE__) . 'inc/Arf_Custom_Booking_Creator.php';
    $errors = array();
    $reservation_date = $data['dates'];
    $paytype = isset($data['paytype']) ? $data['paytype'] : "on_spot";
    $room_type = 1;
    $beach_arrival_time = $data['beach_arrival_time'];
    $lunch_time = $data['lunch_time'];
    $adults = $data['people'];
    $child = $data['child'];
    $product = isset($data['product']) ? $data['product'] : array();
    $first_name = $data['first_name'];
    $last_name = $data['last_name'];
    $email = $data['email'];
    $phone = $data['full_phone'];
    $terms = $data['terms'];
    $services = $data['services'];
    $coordinate = !empty($data['coordinate']) ? $data['coordinate'] : "";
    if ($terms != "Yes") {
        $errors['terms'] = __('Something goes wrong!', 'arienzo_reservation_form');
    }

    if (empty($reservation_date)) {
        $errors['dates'] = __('Something goes wrong!', 'arienzo_reservation_form');
    }
    //    elseif (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $reservation_date)) {
    //        $errors['dates'] = __('Something goes wrong!', 'arienzo_reservation_form');
    //    }
    //    if($multiple_dates == "Yes") {
    //        $reservation_date = explode(" > ", $reservation_date);
    //        $start_day = new DateTime($reservation_date[0]);
    //        $end_day = new DateTime($reservation_date[1]);
    //        $period = new DatePeriod(
    //            $start_day,
    //            new DateInterval('P1D'),
    //            $end_day->modify('+1 day')
    //        );
    //        $arr = array();
    //        $blocked_dates = getBookingRules ();
    //        foreach ($period as $key => $value) {
    //            if(!in_array($value->format('Y-m-d'), $blocked_dates))
    //                $arr[] = $value->format('Y-m-d');
    //        }
    //        $reservation_date = $arr;
    //    }
    //    else {
    //
    //    }

    if (empty($beach_arrival_time)) {
        $errors['beach_arrival_time'] = __('Beach Arrival Time field is required!', 'arienzo_reservation_form');
    }
    if (empty($lunch_time)) {
        $errors['lunch_time'] = __('Lunch Time field is required!', 'arienzo_reservation_form');
    }
    if (empty($adults)) {
        $errors['people'] = __('Adults field is required!', 'arienzo_reservation_form');
    }

    if (empty($first_name)) {
        $errors['first_name'] = __('First Name field is required!', 'arienzo_reservation_form');
    }
    if (empty($product)) {
        $errors['product'] = __('Product is required!', 'arienzo_reservation_form');
    }else{
        $selected = "";
        foreach ($product as $key => $value) {
            if($value)
                $selected = 1;
        }
        if(!$selected){
            $errors['product'] = __('Product is required!', 'arienzo_reservation_form');
        }
    }
    if (empty($last_name)) {
        $errors['last_name'] = __('Last Name field is required!', 'arienzo_reservation_form');
    }
    if (empty($phone)) {
        $errors['phone'] = __('Telephone field is required!', 'arienzo_reservation_form');
    }

    if (empty($email)) {
        $errors['email'] = __("Email field is required!", 'arienzo_reservation_form');
    } elseif (!is_email($email)) {
        $errors['email'] = __("Email is not valid", 'arienzo_reservation_form');
    }
    $__services = array();
    if(!empty($services)) {
        foreach ($services as $key => $val) {
            $__services[$key]['id'] = $val;
            $__services[$key]['adults'] = $adults;
            $__services[$key]['child'] = $child;
        }
    }
    if(empty($errors)){
        if(is_array($reservation_date)) {
            foreach ($reservation_date as $value) {
                $date = new DateTime($value);
                $check_in_date = $date->format('Y-m-d');
            }
        }else{
            $date = new DateTime($reservation_date);
            $check_in_date = $date->format('Y-m-d');
        }
        global $wpdb;
        $total_person_count = 0;
        if($adults){
            $total_person_count += $adults;
        }
        if($child){
            $total_person_count += $child;
        }
        $booking_ids = $wpdb->get_results("SELECT post_id FROM  $wpdb->postmeta LEFT JOIN ".$wpdb->prefix."posts ON (".$wpdb->prefix."posts.ID = ".$wpdb->postmeta.".post_id) WHERE `meta_key` = 'mphb_check_in_date' AND `meta_value` = '".$check_in_date."' AND post_status IN ('confirmed','paid_not_refundable','paid_refundable','last_minute','pending_late_charge')");
        $booked_places = array();
        $total_pax = 0;
        $table_selected_ids = array();
        foreach ($booking_ids as $booking_id) {

            $price_breakdown = get_post_meta( $booking_id->post_id, '_mphb_booking_price_breakdown', true); 
            if($price_breakdown){
                $ddd = json_decode(strip_tags($price_breakdown),true);
                if(isset($ddd['rooms'])){
                    foreach ($ddd['rooms'] as $kk => $value) {
                        $total_pax += !empty($value['room']['adults']) ? $value['room']['adults'] : 0; 
                        $total_pax += !empty($value['room']['children']) ? $value['room']['children'] : 0; 
                    }
                }
            }

            $item_lunch_time = get_post_meta($booking_id->post_id, 'lunch_time', true);
            if($item_lunch_time == $lunch_time || $item_lunch_time == $lunch_time_text) {
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

        foreach ( $services as $reservedService ) {
            $location = get_post_meta($reservedService,"location_on_order",1);

            foreach ($location as $location_value) {
                $block_locations = array();
                if(!empty($booked_places[$location_value])){
                    $block_locations = $booked_places[$location_value];
                }

                $block_location_names = get_field("block_location_names",$location_value);

                $block_location_names = explode(",", $block_location_names);

                $ddd = get_field("location_names",$location_value);
                $arr = array();
                $ddd = explode("|",$ddd);
                if($ddd){ foreach($ddd AS $kk => $vv){ $arr = array_merge($arr,explode(",",$vv)); } }
                if($block_locations){
                    foreach ($arr as $arr_value) {
                        if(in_array($arr_value, $block_location_names)) continue;
                        if(!in_array($arr_value, $block_locations)){
                            $total_locations[] = $arr_value;
                            $location_count++;
                        }
                        if($location_count == $loop_location){
                            break;
                        }
                    }
                }else{
                    foreach ($arr as $arr_value) {
                        if(in_array($arr_value, $block_location_names)) continue;
                        $total_locations[] = $arr_value;
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
        if(count($total_locations) < $loop_location){
            $errors['location'] = "Booking not available for selected date please select diffrent date";
        }

        $is_subed = "";
        if($lunch_time){
            $lunch_time_type = get_post_meta($lunch_time,"lunch_time_type",1);
            if($lunch_time_type == "lunch_at_your_sunbed"){
                $is_subed = "1";
            }
        }
        $total_tables = array();

        $args = array(
            'post_type' => 'arf_pt_table',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'post_title',
            'order' => 'ASC'
        );
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

        if(count($total_tables) < $loop_location){
            $errors['tables'] = "Booking not available for lunch time please select diffrent lunch time";
        }

        $mphb_daily_limit = get_option("mphb_daily_limit");
       
        if(($total_pax + $total_person_count) > $mphb_daily_limit){
            $errors['tables'] = "Booking full for selected date please select diffrent date.";
        }
    }

    $rate_id = arf_get_rate_type_id();
    $room_id = arf_get_room_type_id();
    unset($_POST);
    if (!empty($errors)) {
        echo json_encode(array('success' => false, 'messages' => $errors));
        wp_die();
    }

    $created_date = current_time('Y-m-d H:i:s');
    $updated_date = current_time('Y-m-d H:i:s');
    $data = array(
        'paytype' => $paytype,
        'user_firstname' => $first_name,
        'user_lastname' => $last_name,
        'user_email' => $email,
        'user_phone' => $phone,
        'beach_arrival_time' => $beach_arrival_time,
        'lunch_time' => $lunch_time,
        'adults' => $adults,
        'child' => $child,
        'product' => $product,
        'payment_status' => 'pending',
        'room_type' => $room_type,
        'created_date' => $created_date,
        'updated_date' => $updated_date,
        'room_id' => $room_id,
        'rate_id' => $rate_id,
        'services' => $__services,
        'coordinate' => $coordinate
    );
    
    $data_survay_monkay = array(
        "first_name" => $first_name,
        "last_name" => $last_name,
        "email" => $email,
        "custom_fields" => array(
            "1" => $phone,
            "2" => getServicesByIDs($services)
        )
    );
    /*
    define ('WP_DEBUG',TRUE);
    
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        */
    if(is_array($reservation_date)) {
        foreach ($reservation_date as $value) {
            $data['reservation_start_date'] = $value;
            $newObj = new ARF_CUSTOM_BOOKING_CREATOR($data);
            $new_order = $newObj->setup();
            $newObj->sendMail($new_order);
        }
    }
    else {
        $data['reservation_start_date'] = $reservation_date;
        $newObj = new ARF_CUSTOM_BOOKING_CREATOR($data);
        $new_order = $newObj->setup();
        $newObj->sendMail($new_order);
    }

    if (!$new_order) {
        $errors['error'] = __('Something goes wrong!', 'arienzo_reservation_form');
        echo json_encode(array('success' => false, 'messages' => $errors));
        wp_die();
    }
    survey_monkey_add_contact($data_survay_monkay);
    if($paytype == "not_refundable" || $paytype == "refundable" || $paytype == "last_minute"){
        $json["success"] = true;
        $ALIAS = 'payment_3567478';
        $CHIAVESEGRETA = '0XWZ341B4B3wE636KO5FxH1Q3vY31T8VYqJ8335X';
        $json["requestUrl"] = "https://ecommerce.nexi.it/ecomm/ecomm/DispatcherServlet";
        //$merchantServerUrl = "https://booking.arienzobeachclub.com";
        $merchantServerUrl = get_home_url();

        $codTrans = $new_order->getId();
        $divisa = "EUR";
        $importo = (int)(round($new_order->getTotalPrice(),1)*100);

        // Calcolo MAC
        /*$current_user = wp_get_current_user();
        if (user_can( $current_user, 'administrator' )) {
             //echo "<pre>"; print_r($importo); echo "</pre>";die; 
            //$importo = 27460;
        }*/
        $mac = sha1('codTrans=' . $codTrans . 'divisa=' . $divisa . 'importo=' . $importo . $CHIAVESEGRETA);

        // Parametri obbligatori
        $obbligatori = array(
            'alias' => $ALIAS,
            'importo' => $importo,
            'divisa' => $divisa,
            'codTrans' => $codTrans,
            'url' => $merchantServerUrl,
            'url_back' => $merchantServerUrl,
            'mac' => $mac,   
        );


        // Parametri facoltativi
        $facoltativi = array(
        );

        $json['requestParams'] = array_merge($obbligatori, $facoltativi);
    }else{
        $json = array('success' => true, 'url' => home_url( '/thank-you-for-order/' ));
    }
    echo json_encode($json);
    //echo json_encode(array('success' => true, 'url' => home_url( '/thank-you-for-order/' )));
    wp_die();
}


// First register resources with init 
function add_payment_update() {
    if(isset($_GET['booking_payment_id']) && $_GET['booking_payment_id']){
        $id = $_GET['booking_payment_id'];
        $booking = mphb_get_booking($id, true);
        if($booking){
            $c_status  = get_post_status ( $id );
            
            $payment_allow = get_post_meta($id,"payment_allow",true);

            if ($c_status  == 'pending_late_charge' || $payment_allow) {
                $ALIAS = 'payment_3567478';
                $CHIAVESEGRETA = '0XWZ341B4B3wE636KO5FxH1Q3vY31T8VYqJ8335X';
                $requestUrl = "https://ecommerce.nexi.it/ecomm/ecomm/DispatcherServlet";
                $merchantServerUrl = get_home_url();
                $codTrans = "payment_".$booking->getId();
                $divisa = "EUR";
                $importo = round($booking->getTotalPrice(),1)*100;
                // Calcolo MAC
                $mac = sha1('codTrans=' . $codTrans . 'divisa=' . $divisa . 'importo=' . $importo . $CHIAVESEGRETA);
                // Parametri obbligatori
                $obbligatori = array(
                    'alias' => $ALIAS,
                    'importo' => $importo,
                    'divisa' => $divisa,
                    'codTrans' => $codTrans,
                    'url' => $merchantServerUrl,
                    'url_back' => $merchantServerUrl,
                    'mac' => $mac,   
                );
                // Parametri facoltativi
                $facoltativi = array(
                );
                $requestParams = array_merge($obbligatori, $facoltativi);
                ?>
                <form id="myForm" action="<?php echo $requestUrl; ?>" method="post">
                <?php
                    foreach ($requestParams as $a => $b) {
                        echo '<input type="hidden" name="'.($a).'" value="'.($b).'">';
                    }
                ?>
                </form>
                <script type="text/javascript">
                    document.getElementById('myForm').submit();
                </script>
                <?php
                die;
            }
        }

    }
    if(isset($_GET['codTrans']) && $_GET['codTrans']){
        $id = $_GET['codTrans'];
        $id = str_replace("payment_", "", $id);
        $booking = mphb_get_booking($id, true);
        if($booking){
            $c_status  = get_post_status ( $id );
            $payment_allow = get_post_meta($id,"payment_allow",true);
            if ($c_status  == 'not-paid' || $c_status  == 'pending_late_charge' || $payment_allow) {
                $fail = "";
                $CHIAVESEGRETA = "0XWZ341B4B3wE636KO5FxH1Q3vY31T8VYqJ8335X"; // Sostituire con il valore fornito da Nexi
                 
                // Controllo che ci siano tutti i parametri di ritorno obbligatori per calcolare il MAC
                $requiredParams = array('codTrans', 'esito', 'importo', 'divisa', 'data', 'orario', 'codAut', 'mac');
                foreach ($requiredParams as $param) {
                    if (!isset($_REQUEST[$param])) {
                        $fail = 'Paramentro mancante ' . $param;
                    }
                }

                // Calcolo MAC con i parametri di ritorno
                $macCalculated = sha1('codTrans=' . $_REQUEST['codTrans'] .
                        'esito=' . $_REQUEST['esito'] .
                        'importo=' . $_REQUEST['importo'] .
                        'divisa=' . $_REQUEST['divisa'] .
                        'data=' . $_REQUEST['data'] .
                        'orario=' . $_REQUEST['orario'] .
                        'codAut=' . $_REQUEST['codAut'] .
                        $CHIAVESEGRETA
                );

                // Verifico corrispondenza tra MAC calcolato e parametro mac di ritorno
                if ($macCalculated != $_REQUEST['mac']) {
                    $fail = 'Errore MAC: ' . $macCalculated . ' non corrisponde a ' . $_REQUEST['mac'];
                }
                //echo "<pre>"; print_r($fail); echo "</pre>";die; 

                // Nel caso in cui non ci siano errori gestisco il parametro esito
                $paytype = get_post_meta($id,"paytype",true);
                if(file_exists(ABSPATH."payment_log.txt")){
                    $text = 'Booking ID '.$id." . Response ".json_encode($_REQUEST).PHP_EOL;
                    $fp = fopen(ABSPATH."payment_log.txt", 'a');
                    fwrite($fp, $text);
                }
                //if ((!$fail && ($_REQUEST['esito'] == 'OK' || $_REQUEST['esito'] == 'KO')) ) {
                if ((!$fail && ($_REQUEST['esito'] == 'OK') )) {

                    /*$products_qty = get_post_meta($id,"products_qty",1);
                    if($products_qty){
                        foreach ($products_qty as $key => $value) {
                            $stock = get_post_meta($key,"stock",1);
                            $stock = $stock - $value;
                            update_post_meta($key,"stock",$stock);
                        }
                    }*/

                    //$success = 'La transazione ' . $_REQUEST['codTrans'] . " è avvenuta con successo; codice autorizzazione: " . $_REQUEST['codAut'];
                    $status = "pending";
                    if($c_status  != 'pending_late_charge'){

                        if($paytype == "not_refundable"){
                            $status = "paid_not_refundable";
                        }else if($paytype == "refundable"){
                            $status = "paid_refundable";
                        }else if($paytype == "last_minute"){
                            $status = "last_minute";
                        }
                        $my_post = array(
                            'ID'            => $id,
                            'post_status'   => $status,
                        );
                        wp_update_post( $my_post );
                    }
                    update_post_meta($id,"naxi_pay",$_REQUEST['importo']/100);


                    $adults_total = 0;
                    $children_total = 0;
                    $services = array();
                    $check_in_date = get_post_meta($id,"mphb_check_in_date",1);
                    $lunch_time = get_post_meta($id,"lunch_time",1);
                    $lunch_time_text = get_lunch_text($lunch_time);
                    $reservedRooms = $booking->getReservedRooms();

                    if($reservedRooms){
                        foreach ( $reservedRooms as $reservedRoom ) {
                            $adults_total = $reservedRoom->getAdults();
                            $children_total = $reservedRoom->getChildren();
                            $services_room = $reservedRoom->getReservedServices();
                            if($services_room){
                                foreach ($services_room as $kkk => $vvv) {
                                    $services[] = array(
                                        "id" => $vvv->getId()
                                    );
                                }
                            }
                        }
                    }

                    global $wpdb;

                    /*if(isset($_GET['test_orrrder'])){
                      echo "<pre>"; print_r($adults_total); echo "</pre>";
                      echo "<pre>"; print_r($services); echo "</pre>";    
                      echo "<pre>"; print_r($check_in_date); echo "</pre>";die;    
                    }*/
                    $total_person_count = ($children_total ? $children_total : 0) + ($adults_total ? $adults_total : 0);
                    if($services && $total_person_count && $check_in_date){

                        $booking_ids = $wpdb->get_results("SELECT post_id FROM  $wpdb->postmeta LEFT JOIN ".$wpdb->prefix."posts ON (".$wpdb->prefix."posts.ID = ".$wpdb->postmeta.".post_id) WHERE `meta_key` = 'mphb_check_in_date' AND `meta_value` = '$check_in_date' AND post_status IN ('confirmed','paid_not_refundable','paid_refundable','last_minute','pending_late_charge')");
                        $booked_places = array();
                        $table_selected_ids = array();
                        foreach ($booking_ids as $booking_id) {

                            $item_lunch_time = get_post_meta($booking_id->post_id, 'lunch_time', true);
                            if($item_lunch_time == $lunch_time || $item_lunch_time == $lunch_time_text) {
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

                        foreach ( $services as $reservedService ) {
                            $location = get_post_meta($reservedService['id'],"location_on_order",1);

                            foreach ($location as $location_value) {
                                $block_locations = array();
                                if(!empty($booked_places[$location_value])){
                                    $block_locations = $booked_places[$location_value];
                                }

                                $ddd = get_field("location_names",$location_value);
                                $arr = array();
                                $ddd = explode("|",$ddd);

                                $block_location_names = get_field("block_location_names",$location_value);

                                $block_location_names = explode(",", $block_location_names);

                                if($ddd){ foreach($ddd AS $kk => $vv){ $arr = array_merge($arr,explode(",",$vv)); } }
                                if($block_locations){
                                    foreach ($arr as $arr_value) {
                                        if(in_array($arr_value, $block_location_names)) continue;
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
                                        if(in_array($arr_value, $block_location_names)) continue;
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
                        update_post_meta($id, "mphb_place", $total_locations);
                        $is_subed = "";
                        if($lunch_time){
                            $lunch_time_type = get_post_meta($lunch_time,"lunch_time_type",1);
                            if($lunch_time_type == "lunch_at_your_sunbed"){
                                $is_subed = "1";
                            }
                        }
                        $total_tables = array();

                        $args = array(
                            'post_type' => 'arf_pt_table',
                            'posts_per_page' => -1,
                            'post_status' => 'publish',
                            'orderby' => 'post_title',
                            'order' => 'ASC'
                        );
                        if($is_subed){
                            $args['meta_query'] = array(
                                array(
                                    'key' => 'is_subed',
                                    'value' => "1"
                                )
                            );
                        }else{
                            $args['meta_query'] = array(
                                'relation' => 'OR', /* <-- here */
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
                        update_post_meta($id, "arf_cp_table_id", $total_tables);

                    }

                    $table = $wpdb->prefix . 'posts';

                    $data = array(
                        'post_author' => 1,
                        'post_date' => current_time('Y-m-d H:i:s'),
                        'post_date_gmt' => '0000-00-00 00:00:00',
                        'post_content' => '',
                        'post_content_filtered' => '',
                        'post_title' => '',
                        'post_excerpt' => '',
                        //'post_status' => 'pending',
                        'post_status' => 'mphb-p-completed',
                        'post_type' => 'mphb_payment',
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

                    $wpdb->insert($table, $data);
                    $insert_id = $wpdb->insert_id;
                    update_post_meta($insert_id, "_id", $insert_id);
                    update_post_meta($insert_id, "_mphb_gateway", "nexi");
                    update_post_meta($insert_id, "_mphb_gateway_mode", "live");
                    update_post_meta($insert_id, "_mphb_amount", $_REQUEST['importo']/100);
                    update_post_meta($insert_id, "_mphb_fee", "");
                    update_post_meta($insert_id, "_mphb_currency", "EUR");
                    update_post_meta($insert_id, "_mphb_payment_type", "");
                    update_post_meta($insert_id, "_mphb_transaction_id", $id);
                    update_post_meta($insert_id, "_mphb_booking_id", $id);
                    update_post_meta($insert_id, "_mphb_first_name", "");
                    update_post_meta($insert_id, "_mphb_last_name", "");
                    update_post_meta($insert_id, "_mphb_email", "");
                    update_post_meta($insert_id, "_mphb_phone", "");
                    update_post_meta($insert_id, "_mphb_country", "");
                    update_post_meta($insert_id, "_mphb_address1", "");
                    update_post_meta($insert_id, "_mphb_address2", "");
                    update_post_meta($insert_id, "_mphb_city", "");
                    update_post_meta($insert_id, "_mphb_state", "");
                    update_post_meta($insert_id, "_mphb_zip", "");

                    if($status == "paid_not_refundable"){
                        wp_redirect(home_url( '/thank-you-for-order-2/' ));die;
                    }else if($status == "paid_refundable"){
                        wp_redirect(home_url( '/thank-you-for-order-3/' ));die;
                    }else if($status == "last_minute"){
                        wp_redirect(home_url( '/thank-you-for-order-5/' ));die;
                    }else if($c_status == "pending_late_charge"){
                        wp_redirect(home_url( '/thank-you-for-order-4/' ));die;
                    }else{
                        wp_redirect(home_url( '/thank-you-for-order/' ));die;
                    }
                } else {
                    if(isset($_REQUEST['codiceEsito']) && $_REQUEST['codiceEsito'] == "122"){
                        $ALIAS = 'payment_3567478';
                        $CHIAVESEGRETA = '0XWZ341B4B3wE636KO5FxH1Q3vY31T8VYqJ8335X';
                        $requestUrl = "https://ecommerce.nexi.it/ecomm/ecomm/DispatcherServlet";
                        $merchantServerUrl = get_home_url();
                        $codTrans = "payment_".$booking->getId();
                        $divisa = "EUR";
                        $importo = round($booking->getTotalPrice(),1)*100;
                        // Calcolo MAC
                        $mac = sha1('codTrans=' . $codTrans . 'divisa=' . $divisa . 'importo=' . $importo . $CHIAVESEGRETA);
                        // Parametri obbligatori
                        $obbligatori = array(
                            'alias' => $ALIAS,
                            'importo' => $importo,
                            'divisa' => $divisa,
                            'codTrans' => $codTrans,
                            'url' => $merchantServerUrl,
                            'url_back' => $merchantServerUrl,
                            'mac' => $mac,   
                        );
                        // Parametri facoltativi
                        $facoltativi = array(
                        );
                        $requestParams = array_merge($obbligatori, $facoltativi);
                        //echo "<pre>"; print_r($requestParams); echo "</pre>";die; 
                        ?>
                        <form id="myForm" action="<?php echo $requestUrl; ?>" method="post">
                        <?php
                            foreach ($requestParams as $a => $b) {
                                echo '<input type="hidden" name="'.($a).'" value="'.($b).'">';
                            }
                        ?>
                        </form>
                        <script type="text/javascript">
                            document.getElementById('myForm').submit();
                        </script>
                        <?php
                        die;
                    }else{
                        $fail = 'La transazione ' . $_REQUEST['codTrans'] . " è stata rifiutata; descrizione errore: " . $_REQUEST['messaggio'];
                        if($paytype == "not_refundable"){
                            wp_redirect(home_url( '/payment-fail-not-refundable/' ));die;
                        }else if($paytype == "refundable"){
                            wp_redirect(home_url( '/payment-fail-refundable/' ));die;
                        }else if($paytype == "last_minute"){
                            wp_redirect(home_url( '/payment-fail-last-minute/' ));die;
                        }else if($c_status == "pending_late_charge"){
                            wp_redirect(home_url( '/payment-fail-late-charge/' ));die;
                        }else{
                            wp_redirect(home_url( '/payment-fail/' ));die;
                        }
                    }
                }
            }
        }
    }


    if(isset($_GET['sunbed_updates'])){
        $attr = array(
            'posts_per_page' => -1,
            'post_type' => 'mphb_booking',
            'post_status' => array('confirmed','paid_not_refundable','paid_refundable','last_minute','pending_late_charge'),
            'fields' => 'ids',
            'order' => 'asc',
            'orderby' => 'meta_value',
            'meta_key' => 'mphb_check_in_date',
            'meta_query' => array()
        );
        
        $attr['meta_query'][] = array(
            'key' => 'mphb_check_in_date',
            'value' => date("Y-m-d"),
            'compare' => '>',
        );
        $attr['meta_query'][] = array(
            'key' => 'lunch_time',
            'value' => "11:59",
            'compare' => '=',
        );
        $query = new WP_Query($attr);
        $ids = $query->posts;
        $array = array();
        $a_lunch_time = 90977;
        $a_lunch_time_text = get_lunch_text(90977);
        global $wpdb;
        $selected_lunch = array();
        $next_loop = array();
        foreach ($ids as $key => $value) {
            $check_in_date = get_post_meta($value,'mphb_check_in_date',1);
            if(!isset($selected_lunch[$check_in_date])){
                $selected_lunch[$check_in_date] = array();
            }
            $booking_ids = $wpdb->get_results("SELECT post_id FROM  $wpdb->postmeta LEFT JOIN ".$wpdb->prefix."posts ON (".$wpdb->prefix."posts.ID = ".$wpdb->postmeta.".post_id) WHERE `meta_key` = 'mphb_check_in_date' AND `meta_value` = '$check_in_date' AND post_status IN ('confirmed','paid_not_refundable','paid_refundable','last_minute','pending_late_charge')");

            $booked_places = array();
            $table_selected_ids = $selected_lunch[$check_in_date];
            foreach ($booking_ids as $booking_id) {

                $item_lunch_time = get_post_meta($booking_id->post_id, 'lunch_time', true);
                if($item_lunch_time == $a_lunch_time) {
                    $ids = get_post_meta($booking_id->post_id, 'arf_cp_table_id', true);
                    if(is_array($ids)){
                        $table_selected_ids = array_merge($table_selected_ids,$ids);
                    }else{
                        $table_selected_ids[] = $ids;
                    }
                }
            }

            $location_count = 0;
            $mphb_adults = 0;
            $mphb_children = 0;

            $reservedRooms = get_post_meta($value,"_mphb_booking_price_breakdown",1);


            $reservedRooms = json_decode(strip_tags($reservedRooms),1);
            $html = "";
            if (!empty($reservedRooms)) {
                foreach ($reservedRooms['rooms'] as $reservedRoom) {
                    $mphb_adults += isset($reservedRoom['room']['adults']) ? $reservedRoom['room']['adults'] : 0;
                    $mphb_children += isset($reservedRoom['room']['children']) ? $reservedRoom['room']['children'] : 0;
                }
            }

            //$loop_location = ceil($adults_total/2);
            $total_person_count = $mphb_adults + $mphb_children;
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


            $total_tables = array();

            $args = array(
                'post_type' => 'arf_pt_table',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'orderby' => 'post_title',
                'order' => 'ASC'
            );
            

            $args['meta_query'] = array(
                array(
                    'key' => 'is_subed',
                    'value' => "1"
                )
            );
            
            if($table_selected_ids) {
                $args['post__not_in'] = $table_selected_ids;
            }
            $arf_pt_tables = get_posts($args);
            foreach ($arf_pt_tables as $kk => $table) {
                $auto_booking = get_post_meta($table->ID,"auto_booking",true);
                if($auto_booking == 1){
                    $selected_lunch[$check_in_date][] = $table->ID;
                    $total_tables[] = $table->post_title;
                    if(count($total_tables) == $loop_location){
                        break;
                    }
                }
            }


            //update_post_meta($mphb_booking_new_id, "arf_cp_table_id", $total_tables);
            if(count($total_tables) == $loop_location){
                $selected_lunch[$check_in_date] = array_merge($selected_lunch[$check_in_date],$total_tables);
                $array[$value] = array(
                    "lunch_time" => $a_lunch_time_text,
                    "checkindate" => $check_in_date,
                    "adults" => $mphb_adults,
                    "children" => $mphb_children,
                    "total_require_table" => $loop_location,
                    "tables" => $total_tables,
                    "tables_status" => count($total_tables) == $loop_location ? "Assign" : "Table Not available",
                );
            }
            else
            $next_loop[] = $value;
        }
        $a_lunch_time = 90978;
        $a_lunch_time_text = get_lunch_text(90978);
        $selected_lunch = array();
        $next_loop2 =array();
        foreach ($next_loop as $key => $value) {
            $check_in_date = get_post_meta($value,'mphb_check_in_date',1);
            if(!isset($selected_lunch[$check_in_date])){
                $selected_lunch[$check_in_date] = array();
            }
            $booking_ids = $wpdb->get_results("SELECT post_id FROM  $wpdb->postmeta LEFT JOIN ".$wpdb->prefix."posts ON (".$wpdb->prefix."posts.ID = ".$wpdb->postmeta.".post_id) WHERE `meta_key` = 'mphb_check_in_date' AND `meta_value` = '$check_in_date' AND post_status IN ('confirmed','paid_not_refundable','paid_refundable','last_minute','pending_late_charge')");

            $booked_places = array();
            $table_selected_ids = $selected_lunch[$check_in_date];
            foreach ($booking_ids as $booking_id) {

                $item_lunch_time = get_post_meta($booking_id->post_id, 'lunch_time', true);
                if($item_lunch_time == $a_lunch_time) {
                    $ids = get_post_meta($booking_id->post_id, 'arf_cp_table_id', true);
                    if(is_array($ids)){
                        $table_selected_ids = array_merge($table_selected_ids,$ids);
                    }else{
                        $table_selected_ids[] = $ids;
                    }
                }
            }

            $location_count = 0;
            $mphb_adults = 0;
            $mphb_children = 0;

            $reservedRooms = get_post_meta($value,"_mphb_booking_price_breakdown",1);


            $reservedRooms = json_decode(strip_tags($reservedRooms),1);
            $html = "";
            if (!empty($reservedRooms)) {
                foreach ($reservedRooms['rooms'] as $reservedRoom) {
                    $mphb_adults += isset($reservedRoom['room']['adults']) ? $reservedRoom['room']['adults'] : 0;
                    $mphb_children += isset($reservedRoom['room']['children']) ? $reservedRoom['room']['children'] : 0;
                }
            }

            //$loop_location = ceil($adults_total/2);
            $total_person_count = $mphb_adults + $mphb_children;
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


            $total_tables = array();

            $args = array(
                'post_type' => 'arf_pt_table',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'orderby' => 'post_title',
                'order' => 'ASC'
            );
            

            $args['meta_query'] = array(
                array(
                    'key' => 'is_subed',
                    'value' => "1"
                )
            );
            
            if($table_selected_ids) {
                $args['post__not_in'] = $table_selected_ids;
            }
            $arf_pt_tables = get_posts($args);
            foreach ($arf_pt_tables as $kk => $table) {
                $auto_booking = get_post_meta($table->ID,"auto_booking",true);
                if($auto_booking == 1){
                    $selected_lunch[$check_in_date][] = $table->ID;
                    $total_tables[] = $table->post_title;
                    if(count($total_tables) == $loop_location){
                        break;
                    }
                }
            }


            //update_post_meta($mphb_booking_new_id, "arf_cp_table_id", $total_tables);
            if(count($total_tables) == $loop_location){
                $selected_lunch[$check_in_date] = array_merge($selected_lunch[$check_in_date],$total_tables);
                $array[$value] = array(
                    "lunch_time" => $a_lunch_time_text,
                    "checkindate" => $check_in_date,
                    "adults" => $mphb_adults,
                    "children" => $mphb_children,
                    "total_require_table" => $loop_location,
                    "tables" => $total_tables,
                    "tables_status" => count($total_tables) == $loop_location ? "Assign" : "Table Not available",
                );
            }
            else
            $next_loop2[] = $value;
        }
        $a_lunch_time = 83779;
        $a_lunch_time_text = get_lunch_text(83779);
        $selected_lunch = array();
        $next_loop3 =array();
        foreach ($next_loop2 as $key => $value) {
            $check_in_date = get_post_meta($value,'mphb_check_in_date',1);
            if(!isset($selected_lunch[$check_in_date])){
                $selected_lunch[$check_in_date] = array();
            }
            $booking_ids = $wpdb->get_results("SELECT post_id FROM  $wpdb->postmeta LEFT JOIN ".$wpdb->prefix."posts ON (".$wpdb->prefix."posts.ID = ".$wpdb->postmeta.".post_id) WHERE `meta_key` = 'mphb_check_in_date' AND `meta_value` = '$check_in_date' AND post_status IN ('confirmed','paid_not_refundable','paid_refundable','last_minute','pending_late_charge')");

            $booked_places = array();
            $table_selected_ids = $selected_lunch[$check_in_date];
            foreach ($booking_ids as $booking_id) {

                $item_lunch_time = get_post_meta($booking_id->post_id, 'lunch_time', true);
                if($item_lunch_time == $a_lunch_time) {
                    $ids = get_post_meta($booking_id->post_id, 'arf_cp_table_id', true);
                    if(is_array($ids)){
                        $table_selected_ids = array_merge($table_selected_ids,$ids);
                    }else{
                        $table_selected_ids[] = $ids;
                    }
                }
            }

            $location_count = 0;
            $mphb_adults = 0;
            $mphb_children = 0;

            $reservedRooms = get_post_meta($value,"_mphb_booking_price_breakdown",1);


            $reservedRooms = json_decode(strip_tags($reservedRooms),1);
            $html = "";
            if (!empty($reservedRooms)) {
                foreach ($reservedRooms['rooms'] as $reservedRoom) {
                    $mphb_adults += isset($reservedRoom['room']['adults']) ? $reservedRoom['room']['adults'] : 0;
                    $mphb_children += isset($reservedRoom['room']['children']) ? $reservedRoom['room']['children'] : 0;
                }
            }

            //$loop_location = ceil($adults_total/2);
            $total_person_count = $mphb_adults + $mphb_children;
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


            $total_tables = array();

            $args = array(
                'post_type' => 'arf_pt_table',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'orderby' => 'post_title',
                'order' => 'ASC'
            );
            

            $args['meta_query'] = array(
                array(
                    'key' => 'is_subed',
                    'value' => "1"
                )
            );
            
            if($table_selected_ids) {
                $args['post__not_in'] = $table_selected_ids;
            }
            $arf_pt_tables = get_posts($args);
            foreach ($arf_pt_tables as $kk => $table) {
                $auto_booking = get_post_meta($table->ID,"auto_booking",true);
                if($auto_booking == 1){
                    $selected_lunch[$check_in_date][] = $table->ID;
                    $total_tables[] = $table->post_title;
                    if(count($total_tables) == $loop_location){
                        break;
                    }
                }
            }


            //update_post_meta($mphb_booking_new_id, "arf_cp_table_id", $total_tables);
            if(count($total_tables) == $loop_location){
                $selected_lunch[$check_in_date] = array_merge($selected_lunch[$check_in_date],$total_tables);
                $array[$value] = array(
                    "lunch_time" => $a_lunch_time_text,
                    "checkindate" => $check_in_date,
                    "adults" => $mphb_adults,
                    "children" => $mphb_children,
                    "total_require_table" => $loop_location,
                    "tables" => $total_tables,
                    "tables_status" => count($total_tables) == $loop_location ? "Assign" : "Table Not available",
                );
            }
            else
            $next_loop3[$value] = array(
                "lunch_time" => $a_lunch_time_text,
                "checkindate" => $check_in_date,
                "adults" => $mphb_adults,
                "children" => $mphb_children,
                "total_require_table" => $loop_location,
                "tables" => $total_tables,
                "tables_status" => count($total_tables) == $loop_location ? "Assign" : "Table Not available",
            );
        }


        $list = array ();

        foreach ($array as $key => $value) {
            $list[] = array(
                $key,
                $value["lunch_time"],
                $value["checkindate"],
                $value["adults"],
                $value["children"],
                $value["total_require_table"],
                implode(",", $value["tables"]),
                $value["tables_status"],
            );
        }

        foreach ($next_loop3 as $key => $value) {
            $list[] = array(
                $key,
                $value["lunch_time"],
                $value["checkindate"],
                $value["adults"],
                $value["children"],
                $value["total_require_table"],
                implode(",", $value["tables"]),
                $value["tables_status"],
            );
        }

        $now = gmdate("D, d M Y H:i:s");
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");

        // force download  
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        $filename = "data_export_" . date("Y-m-d") . ".csv";
        // disposition / encoding on response body
        header("Content-Disposition: attachment;filename={$filename}");
        header("Content-Transfer-Encoding: binary");

        ob_start();
        $df = fopen("php://output", 'w');

        foreach ($list as $fields) {
            fputcsv($df, $fields);
        }
        fclose($df);
        echo ob_get_clean();die;
        /*echo "<pre>"; print_r($list); echo "</pre>";die;
        echo "<pre>"; print_r($array); echo "</pre>";die; */
    }
        
}
add_action( 'init', 'add_payment_update' );


add_action( 'wp_ajax_arf_booking_ajax_request', 'arf_booking_ajax_request' );
add_action( 'wp_ajax_nopriv_arf_booking_ajax_request', 'arf_booking_ajax_request' );

function arf_booking_hotel_ajax_request ()
{
    if(empty($_POST)){
        echo json_encode(array('success' => false, 'messages' => array('error' => 'Something goes wrong!') ));
        wp_die();
    }
    parse_str($_POST['data'], $data);
    $nonce = $_POST['_nonce'];
    if ( ! wp_verify_nonce( $nonce, 'arf_form_action' ) && !empty($_POST['website'])) {
        echo json_encode(array('success' => false, 'messages' => array('error' => 'Something goes wrong!')));
        wp_die();
    }
    require_once plugin_dir_path(__FILE__) . 'inc/Arf_Custom_Booking_Creator.php';
    $errors = array();
    $reservation_date = $data['dates'];
    $room_type = 1;
    $beach_arrival_time = $data['beach_arrival_time'];
    $lunch_time = $data['lunch_time'];
    $adults = $data['people'];
    $child = $data['child'];
    $hotel_name = sanitize_text_field($data['hotel_name']);

    $terms = $data['terms'];
    $services = $data['services'];
    $coordinate = !empty($data['coordinate']) ? $data['coordinate'] : "";

    if ($terms != "Yes") {
        $errors['terms'] = __('Something goes wrong!', 'arienzo_reservation_form');
    }

    if (empty($reservation_date)) {
        $errors['dates'] = __('Something goes wrong!', 'arienzo_reservation_form');
    }

    if (empty($beach_arrival_time)) {
        $errors['beach_arrival_time'] = __('Beach Arrival Time field is required!', 'arienzo_reservation_form');
    }
    if (empty($adults)) {
        $errors['people'] = __('Adults field is required!', 'arienzo_reservation_form');
    }

    if (empty($hotel_name)) {
        $errors['hotel_name'] = __('Hotel Name field is required!', 'arienzo_reservation_form');
    }

    $__services = array();
    if(!empty($services)) {
        foreach ($services as $key => $val) {
            $__services[$key]['id'] = $val;
            $__services[$key]['adults'] = $adults;
        }
    }

    $rate_id = arf_get_rate_type_id();
    $room_id = arf_get_room_type_id();
    unset($_POST);
    if (!empty($errors)) {
        echo json_encode(array('success' => false, 'messages' => $errors));
        wp_die();
    }

    $first_name = 'test';
    $last_name = 'test1';
    $email = 'info@qadisha.it';
    $phone = '3394680060';

    $created_date = current_time('Y-m-d H:i:s');
    $updated_date = current_time('Y-m-d H:i:s');
    $dataNew = array(
        'user_firstname' => 'test',
        'user_lastname' => 'test1',
        'user_email' => 'info@qadisha.it',
        'user_phone' => '3394680060',
        'beach_arrival_time' => $beach_arrival_time,
        'lunch_time' => $lunch_time,
        'adults' => $adults,
        'child' => $child,
        'payment_status' => 'pending',
        'room_type' => $room_type,
        'created_date' => $created_date,
        'updated_date' => $updated_date,
        'room_id' => $room_id,
        'rate_id' => $rate_id,
        'services' => $__services,
        'coordinate' => $coordinate,
        'hotel_name' => $hotel_name
    );

    $data_survay_monkay = array(
        "first_name" => $first_name,
        "last_name" => $last_name,
        "email" => $email,
        "custom_fields" => array(
            "1" => $phone,
            "2" => getServicesByIDs($services)
        )
    );

    if(is_array($reservation_date)) {
        foreach ($reservation_date as $value) {
            $dataNew['reservation_start_date'] = $value;
            $newObj = new ARF_CUSTOM_BOOKING_CREATOR($dataNew);
            $new_order = $newObj->setup();
            $newObj->sendMail($new_order);
        }
    }
    else {
        $dataNew['reservation_start_date'] = $reservation_date;
        $newObj = new ARF_CUSTOM_BOOKING_CREATOR($dataNew);
        $new_order = $newObj->setup();
        $newObj->sendMail($new_order);
    }

    if (!$new_order) {
        $errors['error'] = __('Something goes wrong!', 'arienzo_reservation_form');
        echo json_encode(array('success' => false, 'messages' => $errors));
        wp_die();
    }

    survey_monkey_add_contact($data_survay_monkay);
    echo json_encode(array('success' => true, 'url' => home_url( '/thank-you-for-order/' )));
    wp_die();
}

add_action( 'wp_ajax_arf_booking_hotel_ajax_request', 'arf_booking_hotel_ajax_request' );
add_action( 'wp_ajax_nopriv_arf_booking_hotel_ajax_request', 'arf_booking_hotel_ajax_request' );

//Get Services Names by IDs
function getServicesByIDs($ids) {
    $response = "";
    if(count($ids) > 0) {
        $dataNew = array();
        $args = array(
            'numberposts'       => -1,
            'post_type'     => 'mphb_room_service',
            'post_status'   => 'publish',
            'suppress_filters' => 0,
            'include' => $ids
        );
        $services = get_posts($args);
        foreach ($services as $service) {
            $dataNew[] = $service->post_title;
        }
        $response = implode(",", $dataNew);
    }
    return $response;
}
