<?php
if (!defined('WPINC')) {
    die;
}

function arf_mphb_booking_custom_meta()
{
    add_meta_box(
        'qr_code_status',
        'QR Code Status',
        'arf_qr_code_status_meta',
        'mphb_booking',
        'side',
        'low'
    );
    add_meta_box(
        'arf_additional_info',
        'Booking Additional Info',
        'arf_additional_info',
        'mphb_booking',
        'side',
        'low'
    );

    add_meta_box(
        'arf_service_icon',
        'Service Icon',
        'arf_service_icon',
        'mphb_room_service',
        'side',
        'low'
    );
	add_meta_box(
        'arf_location_coordinate',
        'Location',
        'arf_location_coordinate',
        'mphb_booking',
        'advanced',
        'low'
    );
	
	add_meta_box(
        'arf_qr_tracking',
        'Booking Qr tracking',
        'arf_qr_tracking',
        'mphb_booking',
        'side',
        'low'
    );
}

add_action('add_meta_boxes', 'arf_mphb_booking_custom_meta');

function arf_qr_tracking()
{
    global $post;
    $hash = encrypt_decrypt($post->ID, 'encrypt');
    //$url = home_url( '/tracking/?tracking=' . $hash ); 
    $url = home_url( '/booking-confirmation/booking-confirmed/?booking_id=' . $post->ID ); ?>
    <input type="text" id="key_input" value="<?php echo $url ?>" readonly>
    <button data-id="key_input" data-select="key_input" type="button" class="button button-primary button-large copy_clip_board" title="<?php echo esc_html(__( "Copy to Clipboard", 'arf_mphb_booking' )) ?>"><?php echo esc_html(__( "Copy to Clipboard", 'arf_mphb_booking' )) ?></button>
    <!--<img src="https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=<?php echo $url ?>&choe=UTF-8" title="<?php echo get_bloginfo('name') ?>>" />-->
    <script>
        jQuery(".copy_clip_board").click(function (e) {
          e.preventDefault();
           const textarea = document.createElement('textarea')
      
            // Set the value of the text
            textarea.value = jQuery("#"+jQuery(this).data("id")).val();
            
            // Make sure we cant change the text of the textarea
            textarea.setAttribute('readonly', '');
            
            // Hide the textarea off the screnn
            textarea.style.position = 'absolute';
            textarea.style.left = '-9999px';
            
            // Add the textarea to the page
            document.body.appendChild(textarea);
            // Copy the textarea
            textarea.select()
            try {
              var successful = document.execCommand('copy');
              this.copied = true
            } catch(err) {
              this.copied = false
            }
            textarea.remove()
    
            var copyText = document.getElementById(jQuery(this).data("select"));
            /* Select the text field */
            copyText.select();
            copyText.setSelectionRange(0, 99999); /* For mobile devices */
            /* Copy the text inside the text field */
         
          /* Alert the copied text */
          jQuery(this).text("<?php echo esc_html(__( "Copied", 'arf_mphb_booking' )) ?>");
          jQuery(this).attr("title","<?php echo esc_html(__( "Copied", 'arf_mphb_booking' )) ?>");
          $this = jQuery(this);
          setTimeout(function(){ 
            $this.text("<?php echo esc_html(__( "Copy to Clipboard", 'arf_mphb_booking' )) ?>");
            $this.attr("title","<?php echo esc_html(__( "Copy to Clipboard", 'arf_mphb_booking' )) ?>");
          }, 2000);
        });
    </script>
    <?php
}

add_action('add_meta_boxes', 'arf_mphb_booking_custom_meta');

function arf_additional_info()
{
global $post, $wpdb;
    $beach_arrival_time = get_post_meta($post->ID, 'beach_arrival_time', true);
    $lunch = get_post_meta($post->ID, 'lunch_time', true);
    $ids = get_post_meta($post->ID, 'arf_cp_table_id', true);
    if(is_array($ids)){
        $table_id = $ids;
    }else{
        $table_id[] = $ids;
    }
    wp_nonce_field(basename(__FILE__), 'arf_wp_nonce_pt_table');

    
    $lunch_time = $lunch ? get_lunch_text($lunch) : "";


    $booking_meta = get_post_meta($post->ID,'mphb_check_in_date', true);

    $table_selected_ids = [];
    $booking_ids = $wpdb->get_results ("
    SELECT post_id 
    FROM  $wpdb->postmeta
        WHERE `meta_key` = 'mphb_check_in_date'
        AND `meta_value` = '$booking_meta'
        AND `post_id` != $post->ID
");

    foreach ( $booking_ids as $booking_id )
    {
        $item_lunch_time = get_post_meta($booking_id->post_id, 'lunch_time', true);
        if($item_lunch_time == $lunch || $item_lunch_time == $lunch_time) {
            $ids = get_post_meta($booking_id->post_id, 'arf_cp_table_id', true);
            if(is_array($ids)){
                $table_selected_ids = $ids;
            }else{
                $table_selected_ids[] = $ids;
            }
        }
    }
 ?>
    <p><?php _e('Beach arrival time', 'arienzo_reservation_form'); ?>: <?php echo $beach_arrival_time ?></p>
    <p><?php _e('Lunch time', 'arienzo_reservation_form'); ?>: <?php echo $lunch_time ?></p>
    <?php
    /*$args = array(
        'post_type' => 'arf_pt_table',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'post_title',
        'order' => 'ASC'
    );

    if($table_selected_ids) {
        $args['post__not_in'] = $table_selected_ids;
    }

    $arf_pt_tables = get_posts($args);

    if ( $arf_pt_tables ) {*/ ?>
        <!-- <label for="arf_qr_code_status_select"><?php _e('Table', 'arienzo_reservation_form') ?>:</label><br>
        <select name="arf_cp_table_id[]" id="arf_cp_table_id" multiple="" style="width:100%">
            <option value=""><?php _e('Select', 'arienzo_reservation_form') ?></option>
            <?php   /** Qadisha - QD - Commented on 2022-06-11 */
                    
                    /* foreach ( $arf_pt_tables as $table ) { */ 
            ?>
                <option value="<?php echo $table->ID; ?>" <?php in_array($table->ID, $table_id) ? "selected" : ""; ?>><?php echo $table->post_title; ?></option>
            <?php 
                    /** Qadisha - QD - Commented on 2022-06-11 */
                    /* } */ 
            ?>
        </select> -->
    <?php
   /* } else {
        //_e("Tables doesn't created", 'arienzo_reservation_form');
    }*/
}


function arf_save_additional_info($post_id)
{
    if (!isset($_POST['arf_wp_nonce_pt_table']) || !wp_verify_nonce($_POST['arf_wp_nonce_pt_table'], basename(__FILE__)))
        return 'Nonce not verified';

    if (wp_is_post_autosave($post_id))
        return 'autosave';

    if (wp_is_post_revision($post_id))
        return 'revision';

    if (!current_user_can('edit_post', $post_id)) {
        return "";
    }

    if (isset($_POST['arf_cp_table_id'])) {
        update_post_meta($post_id, 'arf_cp_table_id', esc_attr($_POST['arf_cp_table_id']));
    }
}

add_action( 'wp_ajax_arf_get_tables', 'wp_ajax_arf_get_tables' );
function wp_ajax_arf_get_tables()
{
    global $wpdb;
    $lunch_time = $_POST['lunch_time'];
    $lunch_time_text = get_lunch_text($lunch_time);
    $mphb_check_in_date = $_POST['mphb_check_in_date'];

    $table_selected_ids = [];
    $booking_ids = $wpdb->get_results ("
        SELECT post_id 
        FROM  $wpdb->postmeta
        LEFT JOIN ".$wpdb->prefix."posts ON (".$wpdb->prefix."posts.ID = ".$wpdb->postmeta.".post_id) 
        WHERE `meta_key` = 'mphb_check_in_date'
        AND `meta_value` = '".date("Y-m-d",strtotime($mphb_check_in_date))."'
        AND post_status IN ('confirmed','paid_not_refundable','paid_refundable','last_minute','pending_late_charge','paid_late_charge')
    ");
    /* echo "<pre>"; print_r("
        SELECT post_id 
        LEFT JOIN ".$wpdb->prefix."posts ON (".$wpdb->prefix."posts.ID = ".$wpdb->postmeta.".post_id) 
        FROM  $wpdb->postmeta
        WHERE `meta_key` = 'mphb_check_in_date'
        AND `meta_value` = '".date("Y-m-d",strtotime($mphb_check_in_date))."'
        AND post_status IN ('confirmed','paid_not_refundable','paid_refundable','last_minute','pending_late_charge','paid_late_charge')
    "); echo "</pre>";die; */
    foreach ( $booking_ids as $booking_id )
    {
        $item_lunch_time = get_post_meta($booking_id->post_id, 'lunch_time', true);
        if($item_lunch_time == $lunch_time || $item_lunch_time == $lunch_time_text) {
            $ids = get_post_meta($booking_id->post_id, 'arf_cp_table_id', true);
            if($ids){
                if(is_array($ids)){
                    $table_selected_ids = array_merge($table_selected_ids,$ids);
                }else{
                    $table_selected_ids[] = $ids;
                }
            }
        }
    }

    $args = array(
        'post_type' => 'arf_pt_table',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'post_title',
        'order' => 'ASC'
    );

    if($table_selected_ids) {
        $args['post__not_in'] = $table_selected_ids;
    }

    $arf_pt_tables = get_posts($args);
    $html = '<option value="">Select</option>';
    foreach ( $arf_pt_tables as $table ) {
        $html .= '<option value="' . $table->ID . '">'. $table->post_title . '</option>';
    }
    echo wp_json_encode(['html'=>$html]);
    wp_die();
}

add_action('save_post', 'arf_save_additional_info');

function arf_qr_code_status_meta()
{
    global $post;
    wp_nonce_field(basename(__FILE__), 'arf_wp_nonce_qr_code');
    $status = get_post_meta($post->ID, 'arf_qr_code_status', true);
    ?>
    <label for="arf_qr_code_status_select">QR Code Status:</label><br>
    <select name="arf_qr_code_status" id="arf_qr_code_status_select" style="width:100%">
        <option value="">Select</option>
        <option value="refused" <?php selected($status, "refused"); ?>>Refused</option>
        <option value="checked" <?php selected($status, "checked"); ?>>Checked</option>
    </select>
    <?php
}

function arf_save_meta_fields($post_id)
{
    if (!isset($_POST['arf_wp_nonce_qr_code']) || !wp_verify_nonce($_POST['arf_wp_nonce_qr_code'], basename(__FILE__)))
        return 'nonce not verified';

    if (wp_is_post_autosave($post_id))
        return 'autosave';

    if (wp_is_post_revision($post_id))
        return 'revision';

    if (!current_user_can('edit_post', $post_id))
        return;
    if (isset($_POST['arf_qr_code_status']))
        update_post_meta($post_id, 'arf_qr_code_status', esc_attr($_POST['arf_qr_code_status']));
    if (isset($_POST['arf_service_icon']))
        update_post_meta($post_id, 'arf_service_icon', esc_attr($_POST['arf_service_icon']));
}

add_action('save_post', 'arf_save_meta_fields');


function arf_location_coordinate() {
    global $post;
    $coordinate = get_post_meta($post->ID, 'coordinate', true);
    ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/3.1.0/fabric.min.js"></script>
    <canvas id="canvas"></canvas>
    <script>
        var coordinate = <?php echo json_encode(json_decode($coordinate));?>;
        var $ = jQuery.noConflict();
        var imageUrl = "<?php echo plugins_url('../assets/img/map.png', __FILE__) ?>";
        var canvas = new fabric.Canvas('canvas', {
            hoverCursor: 'pointer',
            selection: false,
            selectionBorderColor: 'green',
            backgroundColor: null
        });
        var canvasOriginalWidth = "717";
        var canvasOriginalHeight = "600";
        //
        canvas.setWidth(canvasOriginalWidth);
        canvas.setHeight(canvasOriginalHeight);
        canvas.setBackgroundImage(imageUrl, canvas.renderAll.bind(canvas), {
            backgroundImageOpacity: 1,
            backgroundImageStretch: false,
            scaleX: 1,
            scaleY: 1,
        });
        if(coordinate) {
            var rect = new fabric.Rect({
                originX: 'center',
                originY: 'center',
                width: 30,
                height: 30,
                selectable: false,
                angle: 0,
                fill: "red",
                lockUniScaling: true,
            });

            var group = new fabric.Group([rect], {
                left: parseInt(coordinate.xCenter),
                top: parseInt(coordinate.yCenter),
                originX: 'left',
                originY: 'top',
                width: 30,
                height: 30,
                fill: 'rgba(0,0,0,0)',
                transparentCorners: false,
                lockUniScaling: true,
                hasRotatingPoint: false,
                selectable: false
            });

            canvas.add(group);
        }

        canvas.renderAll();
    </script>
    <?php
}

function arf_service_icon()
{
    global $post;
    wp_nonce_field(basename(__FILE__), 'arf_wp_nonce_qr_code');
    $icon = get_post_meta($post->ID, 'arf_service_icon', true);
    $icons = array(
        "icon-hotel-add_bed" => "Hotel add bed",
        "icon-hotel-airplane" => "Hotel airplane",
        "icon-hotel-baggage_1" => "Hotel baggage 1",
        "icon-hotel-baggage_2" => "Hotel baggage 2",
        "icon-hotel-baggage_3" => "Hotel baggage 3",
        "icon-hotel-bath" => "Hotel bath",
        "icon-hotel-bottle" => "Hotel bottle",
        "icon-hotel-calendar_1" => "Hotel calendar 1",
        "icon-hotel-calendar_2" => "Hotel calendar 2",
        "icon-hotel-calendar_3" => "Hotel calendar 3",
        "icon-hotel-car" => "Hotel car",
        "icon-hotel-coffee" => "Hotel coffee",
        "icon-hotel-condition" => "Hotel condition",
        "icon-hotel-conversion" => "Hotel conversion",
        "icon-hotel-credit_card" => "Hotel credit card",
        "icon-hotel-disable" => "Hotel disable",
        "icon-hotel-dog" => "Hotel dog",
        "icon-hotel-double_bed" => "Hotel double bed",
        "icon-hotel-double_bed_2" => "Hotel double bed 2",
        "icon-hotel-drink" => "Hotel drink",
        "icon-hotel-gym" => "Hotel gym",
        "icon-hotel-hairdryer" => "Hotel hairdryer",
        "icon-hotel-info" => "Hotel info",
        "icon-hotel-loundry" => "Hotel loundry",
        "icon-hotel-nosmoking" => "Hotel nosmoking",
        "icon-hotel-parking" => "Hotel parking",
        "icon-hotel-patio" => "Hotel patio",
        "icon-hotel-reception" => "Hotel reception",
        "icon-hotel-restaurant" => "Hotel restaurant",
        "icon-hotel-room_service" => "Hotel room service",
        "icon-hotel-safety_box" => "Hotel safety box",
        "icon-hotel-shower" => "Hotel shower",
        "icon-hotel-single_bed" => "Hotel single bed",
        "icon-hotel-swimming_pool" => "Hotel swimming pool",
        "icon-hotel-train" => "Hotel train",
        "icon-hotel-tv" => "Hotel tv",
        "icon-hotel-wifi" => "Hotel wifi",
        "icon-restaurant-calendar_2" => "Restaurant calendar 2",
        "icon-restaurant-airplane" => "Restaurant airplane",
        "icon-restaurant-allergens" => "Restaurant allergens",
        "icon-restaurant-bus" => "Restaurant bus",
        "icon-restaurant-car" => "Restaurant car",
        "icon-restaurant-check_1" => "Restaurant check 1",
        "icon-restaurant-check_2" => "Restaurant check 2",
        "icon-restaurant-contact_phone_1" => "Restaurant contact phone 1",
        "icon-restaurant-credit_card" => "Restaurant credit card",
        "icon-restaurant-disable" => "Restaurant disable",
        "icon-restaurant-dog" => "Restaurant dog",
        "icon-restaurant-garden" => "Restaurant garden",
        "icon-restaurant-gluten_free" => "Restaurant gluten free",
        "icon-restaurant-metro" => "Restaurant metro",
        "icon-restaurant-nosmoking" => "Restaurant nosmoking",
        "icon-restaurant-parking" => "Restaurant parking",
        "icon-restaurant-train" => "Restaurant train",
        "icon-restaurant-wifi" => "Restaurant wifi",
        "icon-spa-calendar_2" => "Spa calendar_2",
        "icon-spa-airplane" => "Spa airplane",
        "icon-spa-bus" => "Spa bus",
        "icon-spa-candles" => "Spa candles",
        "icon-spa-car" => "Spa car",
        "icon-spa-cartified_massagist" => "Spa cartified massagist",
        "icon-spa-check_1" => "Spa check 1",
        "icon-spa-check_2" => "Spa check 2",
        "icon-spa-contact_phone_1" => "Spa contact phone 1",
        "icon-spa-credit_card" => "Spa credit card",
        "icon-spa-disable" => "Spa disable",
        "icon-spa-dog" => "Spa dog",
        "icon-spa-dress" => "Spa dress",
        "icon-spa-metro" => "Spa metro",
        "icon-spa-oil" => "Spa oil",
        "icon-spa-parking" => "Spa parking",
        "icon-spa-shower" => "Spa shower",
        "icon-spa-tisane" => "Spa tisane",
        "icon-spa-towels" => "Spa towels",
        "icon-spa-train" => "Spa train",
    );
    ?>
    <label for="arf_qr_code_status_select">Service Icon:</label><br>
    <i id="arf_service_icon_demo" class="<?php echo $icon; ?>"></i>
    <select name="arf_service_icon" id="arf_service_icon_select" style="width:100%">
        <?php foreach ($icons as $key => $value) { ?>
            <option value="<?php echo $key; ?>" <?php selected($key, $icon) ?>><?php echo $value; ?></option>
        <?php } ?>
    </select>
    <style>
        #arf_service_icon_demo {
            font-weight: 700;
            font-size: 26px;
        }
    </style>
    <script>
        jQuery("#arf_service_icon_select").change(function () {
            jQuery("#arf_service_icon_demo").removeClass().addClass(jQuery(this).val());
        });

        jQuery("#arf_service_icon_select").select2({
            templateResult: formatState
        });
        function formatState (state) {
            if (!state.id) { return state.text; };
            var $state = jQuery('<span><i class="' +  state.element.value +
                '"></i> ' +
                state.text +     '</span>'
            );
            return $state;
        }
    </script>
    <?php
}


add_action('admin_head', 'arf_services_admin_css');

function arf_services_admin_css()
{
    global $post_type;
    if ((isset($_GET['post_type']) && $_GET['post_type'] == 'mphb_room_service') || (isset($post_type) && $post_type == 'mphb_room_service')) {
        wp_enqueue_style('arf_vendors-style', plugins_url('../assets/css/arf_vendors.css', __FILE__));
    }
}

function arf_load_scrupts_mphb_room_service( $hook ) {

    global $post;

    if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
        if ( 'mphb_room_service' === $post->post_type ) {
            wp_enqueue_style( 'mystyle_css', plugins_url('../assets/css/select2.min.css', __FILE__) ,false,'1.1','all');
            wp_enqueue_script( 'select2_js', plugins_url('../assets/js/select2.full.js', __FILE__) );
        }
    }
}
add_action( 'admin_enqueue_scripts', 'arf_load_scrupts_mphb_room_service', 10, 1 );


add_action( 'wp_ajax_arf_generate_pdf', 'wp_ajax_generate_pdf' );
function wp_ajax_generate_pdf()
{
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $fileType = $_POST['fileType'];

    if (empty($start_date)) {
        wp_send_json_error(array('message' => "Start date is empty"));
    }

    if (empty($end_date)) {
        wp_send_json_error(array('message' => "End date is empty"));
    }
    include( plugin_dir_path( __FILE__ ) . 'Arf_Download_Pdf.php');
    $obj = new Arf_Download_Pdf($start_date, $end_date,$fileType);
    $obj->init();

    wp_die();
}
add_action( 'wp_ajax_arf_generate_pdf_product', 'wp_ajax_arf_generate_pdf_product' );
function wp_ajax_arf_generate_pdf_product()
{
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $fileType = $_POST['fileType'];
    $status = $_POST['status'];

    if (empty($start_date)) {
        wp_send_json_error(array('message' => "Start date is empty"));
    }

    if (empty($end_date)) {
        wp_send_json_error(array('message' => "End date is empty"));
    }
    include( plugin_dir_path( __FILE__ ) . 'Arf_Download_Pdf.php');
    $obj = new Arf_Download_Pdf($start_date, $end_date,$fileType,$status);
    $obj->init_product();
    wp_die();
}
add_action( 'wp_ajax_arf_generate_pdf_product2', 'wp_ajax_arf_generate_pdf_product2' );
function wp_ajax_arf_generate_pdf_product2()
{
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $fileType = $_POST['fileType'];
    $status = $_POST['status'];

    if (empty($start_date)) {
        wp_send_json_error(array('message' => "Start date is empty"));
    }

    if (empty($end_date)) {
        wp_send_json_error(array('message' => "End date is empty"));
    }
    include( plugin_dir_path( __FILE__ ) . 'Arf_Download_Pdf.php');
    $obj = new Arf_Download_Pdf($start_date, $end_date,$fileType,$status);
    $obj->init_product2();
    wp_die();
}
add_action('init', 'handle_download_pdf', 1005);
function handle_download_pdf () {
    if(isset($_GET['fileType']) && $_GET['fileType'] && $_GET['arf_action'] == 'arf_pdf_download') {
        if (is_admin()) {
            maybeDownloadPdf();
        }
    }
}

function maybeDownloadPdf()
{
    $fileType = isset($_GET['fileType']) ? sanitize_text_field($_GET['fileType']) : '';
    $fileName = "";

    if($fileType == "product") {
        $fileName = "product-report.pdf";

    }else if($fileType == "product2") {
        $fileName = "product-report2.csv";

    }else if($fileType == "arrival_time") {
        $fileName = "order-arrival-time-report.pdf";
    }
    elseif ($fileType == "lunch_time") {
        $fileName = "order-lunch-time-report.pdf";
    }
    elseif ($fileType == "lunch_arrival_times") {
        $fileName = "order-lunch-arrival-times-report.pdf";
    }
    else {
        die();
    }

    $file = wp_upload_dir()['basedir'] . "/arf_reports/" . $fileName;
     
    if (empty($fileName) || !file_exists($file)) {
        die();
    }

    $removeAfter = true;

    downloadPdfByFileName($fileName, $file, $removeAfter);
}

function downloadPdfByFileName($filename, $file, $removeAfter = true)
{
    ignore_user_abort(true);
    nocache_headers();

    $disabledFunction = explode(',', ini_get('disable_functions'));

    if (!in_array('set_time_limit', $disabledFunction)) {
        set_time_limit(0);
    }

    $mime = wp_check_filetype($file);
    $content = @file_get_contents($file);

    if ($removeAfter) {
        @unlink($file);
    }

    header('Content-Type: ' . $mime['type'] . '; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    header('Expires: 0');

    echo $content;

    exit();
}

function arf_add_admin_menu() {
    $menu = add_menu_page(__( 'Multiple Booking', 'arienzo_reservation_form' ),__( 'Multiple Booking', 'arienzo_reservation_form' ),'manage_page','arf_multiple_booking','arf_multiple_booking_page','dashicons-schedule',3);

    add_action( 'load-' . $menu, 'arf_admin_load_admin_js' );
}

add_action( 'admin_menu', 'arf_add_admin_menu' );

function arf_multiple_booking_page() {
    require_once plugin_dir_path(__FILE__) . 'arf_admin_booking_form.php';
}

// Front-end
function backend_front_end_scripts()
{
    wp_enqueue_style('arf_google_fonts', 'https://fonts.googleapis.com/css?family=Work+Sans:300,400,500,600', array(), '', 'all');
    wp_enqueue_style('arf_bootstrap_css', plugins_url('../assets/css/bootstrap.min.css', __FILE__), array(), '4.2.1', 'all');
    wp_enqueue_style('arf_style_css', plugins_url('../assets/css/arf_style.css', __FILE__), array(), '', 'all');
    wp_enqueue_style('arf_vendors_css', plugins_url('../assets/css/arf_vendors.css', __FILE__), array(), '', 'all');
    wp_enqueue_style('arf_intTelInput_css', plugins_url('../assets/css/intlTelInput.css', __FILE__), array(), '', 'all');

    wp_enqueue_script('arf_jquery', plugins_url('../assets/js/jquery-3.2.1.min.js', __FILE__), array(), '', false);
    wp_enqueue_script('modernizr_js', plugins_url('../assets/js/modernizr.js', __FILE__), array(), '2.8.3', false);
    wp_enqueue_script('arf_common_scripts_js', plugins_url('../assets/js/common_scripts.min.js', __FILE__), array('arf_jquery'), '', false);
    wp_enqueue_script('arf_velocity_js', plugins_url('../assets/js/velocity.min.js', __FILE__), array(), '1.1.0', true);
    wp_enqueue_script('arf_script_js', plugins_url('../assets/js/arf_script.js', __FILE__), array('arf_common_scripts_js'), '', true);
    wp_enqueue_script('arf_booking_form', plugins_url('../assets/js/arf_booking_form.js', __FILE__), array(), '', true);
    wp_enqueue_script('arf_intTelInput_js', plugins_url('../assets/js/intlTelInput.js', __FILE__), array(), '', true);

    wp_localize_script('arf_script_js', 'arf_ajax_action', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ajax-nonce'),
        'pluginsUrl' => plugins_url('arienzo-reservation-form'),
    ));
}

function arf_admin_load_admin_js(){
    add_action('admin_enqueue_scripts', 'backend_front_end_scripts');
}

add_action( 'wp_ajax_arf_dashboard_info_by_time', 'arf_dashboard_info_by_time' );
function arf_dashboard_info_by_time()
{
    global $wpdb;

    $lunchTime = $_POST['lunchTime'];
    $bookingDate = $_POST['bookingDate'];
    $date = new DateTime($bookingDate);
    $check_in_date =  $date->format('Y-m-d');

    $args = array(
        'posts_per_page' => -1,
        'post_type' => 'mphb_booking',
        'post_status' => array('confirmed','paid_not_refundable','paid_refundable','last_minute','pending_late_charge','paid_late_charge'),
        'fields' => 'ids',
        'meta_query' => array(
            array(
                'key' => 'lunch_time',
                'value' => $lunchTime,
            ),
            array(
                'key' => 'mphb_check_in_date',
                'value' => $check_in_date,
            ),
            'relation' => 'AND',
        )
    );
    $query = new WP_Query($args);
    $ids = $query->posts;
    $result = [];

    if (!empty($ids)) {
        $bookings = MPHB()->getBookingRepository()->findAll(array('post__in' => $ids));
        foreach($bookings as $booking) {
            $reservedRooms = $booking->getReservedRooms();
            $guests = 0;
            foreach ($reservedRooms as $reservedRoom) {
                $guests += $reservedRoom->getAdults();
                $guests += $reservedRoom->getChildren();
            }
            $customer = $booking->getCustomer();
            $result[] = [
                'id' => $booking->getId(),
                'customer_name' => $customer->getFirstName() . " " . $customer->getLastName(),
                'guests' => $guests
            ];
        }
    }

    echo json_encode($result);
    wp_die();
}

add_action( 'wp_ajax_arf_dashboard_info_by_day', 'arf_dashboard_info_by_day' );

function arf_dashboard_info_by_day() {
    global $wpdb;

    $check_in_date = $_POST['day'];
    $args = array(
        'posts_per_page' => -1,
        'post_type' => 'mphb_booking',
        'post_status' => array('confirmed','paid_not_refundable','paid_refundable','last_minute','pending_late_charge','paid_late_charge'),
        'fields' => 'ids',
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'mphb_check_in_date',
                'value' => $check_in_date,
            )
        )
    );
    $query = new WP_Query($args);
    $ids = $query->posts;

    $result = [
        'lunch_time_1' => 0,
        'lunch_time_2' => 0,
        'lunch_time_3' => 0,
        'lunch_time_4' => 0,
        'lunch_time_5' => 0,
    ];
    if(!empty($ids)) {
        $ids = implode(',', $ids);
        $result['lunch_time_1'] =  $wpdb->get_var (
            "SELECT COUNT(`post_id`) FROM  $wpdb->postmeta WHERE `meta_key` = 'lunch_time' AND `meta_value` = '12:00' AND `post_id` IN ($ids)"
        );

        $result['lunch_time_2'] =  $wpdb->get_var (
            "SELECT COUNT(`post_id`) FROM  $wpdb->postmeta WHERE `meta_key` = 'lunch_time' AND `meta_value` = '13:00' AND `post_id` IN ($ids)"
        );

        $result['lunch_time_3'] =  $wpdb->get_var (
            "SELECT COUNT(`post_id`) FROM  $wpdb->postmeta WHERE `meta_key` = 'lunch_time' AND `meta_value` = '14:30' AND `post_id` IN ($ids)"
        );

        $result['lunch_time_4'] =  $wpdb->get_var (
            "SELECT COUNT(`post_id`) FROM  $wpdb->postmeta WHERE `meta_key` = 'lunch_time' AND `meta_value` = '15:50' AND `post_id` IN ($ids)"
        );

        $result['lunch_time_5'] =  $wpdb->get_var (
            "SELECT COUNT(`post_id`) FROM  $wpdb->postmeta WHERE `meta_key` = 'lunch_time' AND `meta_value` = '11:59' AND `post_id` IN ($ids)"
        );
    }

    echo json_encode($result);
    wp_die();
}
