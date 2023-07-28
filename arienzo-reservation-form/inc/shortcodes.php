<?php
if (!defined('WPINC')) {
    die;
}
require_once plugin_dir_path(__FILE__) . 'Arf_Custom_Booking_Creator.php';
function arf_shortcode_search($atts)
{   
    $attr = shortcode_atts( 
        array(
         'service_id' => false,
         'lunch_time_enable' => 1,
         'child_hidden' => 0,
         'adult_text' => "Adults",
         'accomodation_text' => "Per Accommodation",
         'is_home_page' => 0,
         'default_adult' => 0,
         'default_childs' => 0,
         'mphb_check_in_date' => "",
         'service_title' => "",
         ), $atts 
     );
    //$mphb_show_child = get_post_meta($attr['service_id'],"mphb_show_child",1);
    //$attr['child_hidden'] = $mphb_show_child ? "" : 1;
    wp_enqueue_style('arf_google_fonts');
    wp_enqueue_style('arf_bootstrap_css');
    wp_enqueue_style('arf_style_css');
    wp_enqueue_style('arf_vendors_css');
    wp_enqueue_style('arf_intTelInput_css');
    wp_enqueue_script('modernizr_js');
    wp_deregister_script('jquery');
    wp_enqueue_script('arf_jquery');
    wp_enqueue_script('arf_common_scripts_js');
    wp_enqueue_script('arf_velocity_js');
    wp_enqueue_script('arf_script_js');
    //wp_enqueue_script('arf_booking_form');
    wp_enqueue_script('arf_intTelInput_js');
    wp_localize_script('arf_script_js', 'arf_ajax_action', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ajax-nonce'),
        'pluginsUrl' => plugins_url('arienzo-reservation-form'),
    ));

    global $sitepress;
    $current_lang = $sitepress->get_current_language(); //save current language
    $sitepress->switch_lang($current_lang); //restore previous language
    $services = get_posts([
        'numberposts'       => -1,
        'post_type'     => 'mphb_room_service',
        'post_status'   => 'publish',
        'suppress_filters' => 0
    ]);
    $blocked_dates = js_array(getBookingRules());
    $blocked_dates2 = array();
    $disabledDays = get_option('arf_lunch_days');
    $iddd = uniqid();
    ob_start(); ?>
    <?php if(!empty($attr['service_id'])) {
            $features_image_type = get_post_meta($atts['service_id'], 'features_image_type', true);
            $service_price = get_post_meta($atts['service_id'], 'service_price', true);
            $min_pax = get_post_meta($atts['service_id'], 'min_pax', true);
            $max_pax = get_post_meta($atts['service_id'], 'max_pax', true);

            $featured_img_url = array();
            $min = array();
            $max = array();
            foreach ($max_pax as $key => $value) {
                $max[$key] = $value;
            }
            foreach ($min_pax as $key => $value) {
                $min[$key] = $value;
            }
            $qty_stock = 0;
            foreach ($features_image_type as $key => $value) { if($value == "service") continue;
                $qty_stock += (int)get_post_meta($value,"stock",1);
            }
        }
    ?>
    <style type="text/css">
        .daterangepicker td[data-custom] {
          position: relative;
        }
          /*STYLE THE CUSTOME DATA HERE*/
        /*.daterangepicker td[data-custom]::after {
          content: '€' attr(data-custom);
          display: block;
          font-size: 10px;
          color: #4b9bff;
        }*/
        .price_view,.price_view_2{
            background: #ffffff;
        }
        .price_view td,.price_view_2 td{
            border : 1px solid #d2d8dd !important;
        }
        .service_total_price_view{
            background: #ffffff;
        }
        .service_total_price_view td{
            border : 1px solid #d2d8dd !important;
        }
        /*.block_pop_date{
            text-decoration: none !important;
            cursor: pointer !important;
        }*/
        .dd-option-text-na{
            line-height: 14px !important;
            margin-left: 5px;
        }
        #lunch_time .dd-options li:first-child{
            display: none;
        }
    </style>
    <script>
        var disabledDays = <?php echo json_encode($disabledDays );?>;
        disabledDays = disabledDays.reduce(function (r, a) {
            r[a.date] = r[a.date] || [];
            r[a.date].push(a);
            return r;
        }, Object.create(null));
    </script>
    <div class="content-right" id="start">      <div id="preloader-section" class="preloader-section-<?php echo $iddd; ?>">            <div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>     </div>
        <div id="wizard_container" class="wizard_container wizard_container_<?php echo $iddd; ?>" data-service_id="<?php if(!empty($attr['service_id'])){echo $attr['service_id'];} ?>">
            <div id="top-wizard">
                <div id="progressbar" class="progressbar<?php echo $iddd; ?>"></div>
            </div>
            <!-- /top-wizard -->
            <form id="wrapped" class="wizard-form-main form_<?php echo $iddd; ?>" method="POST">
                <input id="website" name="website" type="text" value="">
                <?php wp_nonce_field('arf_form_action'); ?>
                <div id="middle-wizard">
                    <div class="step">

                        <?php if($qty_stock == 0){ ?>
                            <div style="text-align: center;">
                                <img src="https://booking.arienzobeachclub.com/wp-content/uploads/2022/11/sold_out_PNG34.png" width="150">
                            </div>
                        <?php } ?>
                        <h3 class="main_question">
                            <strong>1/3</strong><?php _e('Enter your Booking details', 'arienzo_reservation_form'); ?>
                        </h3>
                        <div class="form-group field_wrapper" data-count="1">
                            <input type="text" name="dates[0]" class="form-control required" value="<?php echo $attr['mphb_check_in_date'] ?>"
                                   placeholder="<?php _e('When', 'arienzo_reservation_form'); ?>" readonly>
                            <i class="icon-hotel-calendar_3"></i>
                            <!-- <a href="javascript:void(0);" class="add_button" title="Add field"><img src="<?php //echo plugins_url("../assets/img/plus.svg", __FILE__) ?>"/></a> -->
                        </div>

                        <div class="form-group">
                            <div class="styled-select clearfix">
                                <select class="required ddslick" name="beach_arrival_time" id="beach_arrival_time">
                                    <option value=""
                                            data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                        <?php _e('Beach arrival time', 'arienzo_reservation_form'); ?>
                                    </option>
                                    <option value="9:50"
                                            data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                        9:50
                                    </option>
                                    <option value="10:10"
                                            data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                        10:10
                                    </option>
                                    <option value="10:30"
                                            data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                        10:30
                                    </option>
                                    <option value="10:50"
                                            data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                        10:50
                                    </option>
                                    <option value="11:10"
                                            data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                        11:10
                                    </option>
                                    <option value="11:30"
                                            data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                        11:30
                                    </option>
                                    <option value="11:50"
                                            data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                        11:50
                                    </option>
                                    <option value="12:10"
                                            data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                        12:10
                                    </option>
                                    <option value="12:30"
                                            data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                        12:30
                                    </option>
                                    <option value="13:00"
                                            data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                        13:00
                                    </option>
                                    <option value="13:15"
                                            data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                        13:15
                                    </option>
                                </select>
                            </div>
                        </div>
                        <?php if($attr['lunch_time_enable'] == 1) { ?>
                            <div class="form-group">
                                <div class="styled-select clearfix">
                                    <select class="ddslick" name="lunch_time" id="lunch_time">
                                            <option value=""
                                                    data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/restaurant-plate.svg', __FILE__) ?>">
                                                <?php _e('Lunch time', 'arienzo_reservation_form'); ?>
                                            </option>
                                            <option value="12:00"
                                                    data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/restaurant-plate.svg', __FILE__) ?>">
                                                12:00
                                            </option>
                                            <option value="13:15"
                                                    data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/restaurant-plate.svg', __FILE__) ?>">
                                                13:15
                                            </option>
                                            <option value="14:30"
                                                    data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/restaurant-plate.svg', __FILE__) ?>">
                                                14:30
                                            </option>
                                            <option value="15:40"
                                                    data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/restaurant-plate.svg', __FILE__) ?>">
                                                15:40
                                            </option>
                                            <option value="11:59"
                                                    data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/sunbed-beach.svg', __FILE__) ?>">
                                                <?php _e('Lunch at your sunbed', 'arienzo_reservation_form'); ?>
                                            </option>
                                    </select>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="row no-gutters pb-1">
                            <div class="<?php //echo ($attr['child_hidden']) ? "col-12" : "col-6 pr-2" ?>  col-12">
                                <label for=""><?php _e($attr['adult_text'], 'arienzo_reservation_form'); ?></label>
                                <div class="form-group">
                                    <div class="qty-buttons">
                                        <input type="button" value="<?= $attr['default_adult'] ? $attr['default_adult'] : "+" ?>" class="qtyplus" name="people">
                                        <input type="number" name="people" id="people" value="<?= $attr['default_adult'] ?>"
                                               class="qty form-control required"
                                               placeholder="<?php _e($attr['adult_text'], 'arienzo_reservation_form'); ?>" min="1" readonly>
                                        <input type="button" value="<?= $attr['default_adult'] ? $attr['default_adult'] : "-" ?>" class="qtyminus" name="people">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 -col-6 -pl-2" style="<?php echo ($attr['child_hidden']) ? "display:none" : "" ?>" >
                                <label for=""><?php _e('Child', 'arienzo_reservation_form'); ?></label>
                                <div class="form-group">
                                    <div class="qty-buttons">
                                        <input type="button" value="<?= $attr['default_childs'] ? $attr['default_childs'] : "-" ?>" class="qtyplus" name="child">
                                        <input type="number" name="child" id="child" value="<?= $attr['default_childs'] ? $attr['default_childs'] : "" ?>"
                                               class="qty form-control"
                                               placeholder="<?php _e('Child', 'arienzo_reservation_form'); ?>" readonly>
                                        <input type="button" value="<?= $attr['default_childs'] ? $attr['default_childs'] : "-" ?>" class="qtyminus" name="child">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="service_detail_text" style="display: none;">
                            <small><?= $attr['service_title'] ? "The ".$attr['service_title'] : "" ?> <span class="service_detail_text_innner"></span></small>
                        </div>
                        <table class="service_total_price_view" >
                            <tr>
                                <td>Price</td>
                                <td align="center"><span class="service_total_price">-</span></td>
                            </tr>
                            <!-- <tr class="price_view_adult" style="display:none;">
                                <td>Adult (<span class="adult_count">0</span> X €<span class="adult_price">0</span>)</td>
                                <td>€<span class="adult_total_price">0</span></td>
                            </tr>
                            <tr class="price_view_child" style="display:none;">
                                <td>Child (<span class="child_count">0</span> X €<span class="child_price">0</span>)</td>
                                <td>€<span class="child_total_price">0</span></td>
                            </tr> -->
                        </table>
                        <?php $date_price = 0; if(!empty($attr['service_id'])) { $date_price = get_post_meta( $attr['service_id'], 'mphb_price', true ); 
                            $price_group = get_post_meta($attr['service_id'], 'customdata_group', true);
                            $dates = get_post_meta($attr['service_id'], 'mphb_block_dates', true);
                                
                            if($dates){
                                foreach (explode(",", $dates) as $key => $value) {
                                    $blocked_dates2[] = date("Y-m-d",strtotime($value));
                                }
                            }
                            if($price_group){
                                $post_date = strtotime(date("Y-m-d",strtotime($_POST['check_in_date'])));
                                foreach ($price_group as $key => $value) {
                                    if($post_date >= strtotime(date("Y-m-d")) && $post_date <= strtotime(date("Y-m-d"))){
                                        if($value['rate']){
                                            $date_price = $value['rate'];
                                        }
                                    }
                                }
                            }
                        } ?>
                        <!-- <table class="price_view_2" style="display:none;">
                            <tr class="price_view_accomodation">
                                <td><?php echo $attr['accomodation_text'] ?></td>
                                <td>€<span class="accommodation_total_price"><?php echo $date_price; ?></span></td>
                            </tr>
                        </table> -->
                        <?php if(true || !empty($_GET['abc_test'])){ ?>
                        <?php if(!empty($attr['service_id'])) {
                            $default = get_post_meta($atts['service_id'], 'default', true);
                            $features_image_type = get_post_meta($atts['service_id'], 'features_image_type', true);
                            $service_price = get_post_meta($atts['service_id'], 'service_price', true);
                            $min_pax = get_post_meta($atts['service_id'], 'min_pax', true);
                            $max_pax = get_post_meta($atts['service_id'], 'max_pax', true);

                            $featured_img_url = array();
                            $min = array();
                            $max = array();
                            foreach ($max_pax as $key => $value) {
                                $max[$key] = $value;
                            }
                            foreach ($min_pax as $key => $value) {
                                $min[$key] = $value;
                            }
                                
                            $adult = !empty($_GET['adult']) ? (int)$_GET['adult'] : 1;
                            ?>
                            <?php $default_selected = ""; foreach ($features_image_type as $key => $value) { if($value == "service") continue; 
                                $qty_stock = (int)get_post_meta($value,"stock",1);
                                if(!$qty_stock) continue;
                                ?>
                                <?php $title = get_the_title( $value ); ?>
                                <?php if($title) { 
                                    $availability_range = get_post_meta($value,"availability_range",1);
                                    $is_default = false;
                                    if($default && in_array($value, $default) && !$default_selected){
                                        if($availability_range){
                                            foreach ($availability_range as $kk => $vvv) {
                                                $startdate = $vvv['startdate'];
                                                $parts = explode('-',$startdate);
                                                $startdate = $parts[2] . '-' . $parts[0] . '-' . $parts[1];
                                                 
                                                $enddate = $vvv['enddate'];
                                                $parts = explode('-',$enddate);
                                                $enddate = $parts[2] . '-' . $parts[0] . '-' . $parts[1];
                                                if($attr['mphb_check_in_date'] && strtotime($startdate) <= strtotime($attr['mphb_check_in_date']) && strtotime($enddate) >= strtotime($attr['mphb_check_in_date']) && isset($min[$value]) && $adult >= $min[$value]){
                                                    $default_selected = 1;
                                                    $is_default = true;
                                                }
                                            }
                                        }else{
                                            $default_selected = 1;
                                            $is_default = true;
                                        }
                                    }
                                    ?>
                                    <div class="product_div_<?php echo $value; ?> product_main_div" data-stock="<?= $qty_stock ?>" data-min="<?php echo (isset($min[$value]) ? $min[$value] : 0) ?>" data-max="<?php echo (isset($max[$value]) ? $max[$value] : 0) ?>" style="display: none;">
                                        <input type="hidden" name="product[<?php echo $value ?>]" value="<?= $is_default ? "1" : "0" ?>" id="product_<?php echo $value ?>" data-value="<?= isset($service_price[$value]) ? $service_price[$value] : 0 ?>">
                                        <table style="background: #ffffff;">
                                            <tbody>
                                                <tr>
                                                    <td style="font-size: 12px;" class="product_title"><?= $title ?></td>
                                                    <td style="font-size: 12px;    width: 40px;"><?= isset($service_price[$value]) ? "€".$service_price[$value] : "-" ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- <div class="product_div_<?php echo $value; ?> product_main_div" style="display: none;">
                                        <div class="row no-gutters pb-1">
                                            <div class="col-12">
                                                <label for="" style="display: inline-block;"><?php _e($title, 'arienzo_reservation_form'); ?></label>
                                                <div class="form-group">
                                                    <div class="qty-buttons-2">
                                                        <input type="button" value="+" class="qtyplus-2" name="product_<?php echo $value ?>">
                                                        <input type="number" name="product[<?php echo $value ?>]" id="product_<?php echo $value ?>" min="1" value="" class="qty-2 form-control required" placeholder="<?php _e($title, 'arienzo_reservation_form'); ?>">
                                                        <input type="button" value="-" class="qtyminus-2" name="product_<?php echo $value ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div> -->
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                        <?php } ?>
                        <div class="error-text" style="display: none;">Please Select At Least One Product.</div>
                        <div class="error-outofstock-text" style="display: none;">Product Out Of Stock.</div>
                    </div>
                    <?php if(empty($attr['service_id']) && $attr['is_home_page'] != 1) { ?>
                        <div class="step">
                            <h3 class="main_question"><strong>2/3</strong><?php _e('Select Reserve Options', 'arienzo_reservation_form'); ?></h3>

                            <div id="cleanAccordionMain" class="cleanAccordion">
                                <dl class="accordion">
                                    <?php foreach ($services as $service) {
                                        $icon = get_post_meta($service->ID, 'arf_service_icon', true);
                                        $price = get_post_meta($service->ID, 'mphb_price', true);
                                        $child_price = get_post_meta($service->ID, 'mphb_child_price', true);
                                        ?>
                                        <dt class="tab_1 singleTab <?php echo ( '' !== $service->post_content ) ? 'hasContent' : ''; ?>">
                                            <div class="accordionSlide">
                                                <div class="accordionContent float-left" style="width: calc(100% - 85px);">
                                                    <div style="display: inline-block; width: calc(100% - 44px); float: left;font-size: 14px;color: #000;">
                                                        <i class="<?php echo $icon; ?>"></i> <?php echo $service->post_title; ?> &euro;<?php echo $price; ?>
                                                    </div>
                                                    <?php if( '' !== $service->post_content ) { ?>
                                                        <div style="display: inline-block; width: 44px; float: right">
                                                            <span class="details"><img width="20" height="20" src="<?php echo plugins_url('../assets/img/icons_select/angle-down.svg', __FILE__) . "" ?>" alt=""></span>
                                                        </div>
                                                    <?php } ?>
                                                    <div style="clear:both"></div>
                                                </div>
                                                <label class="switch-light switch-ios float-right">
                                                    <input type="checkbox" value="<?php echo $service->ID; ?>" name="services[]">
                                                    <span><span>No</span><span>Yes</span></span>
                                                    <a></a>
                                                </label>
                                                <div style="clear:both"></div>
                                            </div>
                                            <div style="clear: bottom"></div>
                                        </dt>
                                        <?php if( '' !== $service->post_content ) { ?>
                                            <dd class="singleTabBody">
                                                <div>
                                                    <p><?php echo $service->post_content; ?></p>
                                                </div>
                                            </dd>
                                        <?php } ?>
                                    <?php } ?>
                                </dl>
                            </div>
                            <!--                        <div>                            -->
                            <!--                            <em style="font-size:11px;">--><?php //_e('*the options proposed are not inclusive of the Beach Club access fee.', 'arienzo_reservation_form'); ?><!--</em>-->
                            <!--                        </div>-->
                            <div style="clear: bottom"></div>
                        </div>
                    <?php } ?>
                    <div class="step">
                        <h3 class="main_question">
                            <strong><?php echo empty($attr['service_id']) ? "3/4" : "2/3"; ?></strong><?php _e('Please fill with your details', 'arienzo_reservation_form'); ?>
                        </h3>
                        <div class="form-group">
                            <input type="text" name="first_name" class="form-control required"
                                   placeholder="<?php _e('First Name', 'arienzo_reservation_form'); ?>">
                            <i class="icon-user"></i>
                        </div>
                        <div class="form-group">
                            <input type="text" name="last_name" class="form-control required"
                                   placeholder="<?php _e('Last Name', 'arienzo_reservation_form'); ?>">
                            <i class="icon-user"></i>
                        </div>
                        <div class="form-group">
                            <input type="email" name="email" class="form-control required noclasse"
                                   placeholder="<?php _e('Email', 'arienzo_reservation_form'); ?>">
                            <i class="icon-envelope"></i>
                        </div>
                        <div class="form-group">
                            <input type="tel" name="phone" class="form-control required phone_number<?php echo $iddd; ?>" id="phone_number"
                                   placeholder="<?php _e('Telephone', 'arienzo_reservation_form'); ?>">
                            <i class="icon-phone"></i>
                        </div>
                        <div class="form-group terms">
                            <label class="container_check"><?php _e('Please accept our', 'arienzo_reservation_form'); ?>
                                <a href="https://booking.arienzobeachclub.com/terms-conditions" target="_blank"><?php _e('Terms and conditions', 'arienzo_reservation_form'); ?></a>
                                <input type="checkbox" name="terms" value="Yes" class="required">
                                <span class="checkmark"></span>
                            </label>
                        </div>
                    </div>
                    <div class="submit step">
                        <h3 class="main_question">
                            <strong><?php echo empty($attr['service_id']) ? "4/4" : "3/3"; ?></strong><?php _e('Select Payment Method', 'arienzo_reservation_form'); ?>
                        </h3>
                        <div class="form-group">
                            <label class="container_radio"><?php _e('Not Refundable', 'arienzo_reservation_form'); ?> (€<span class="service_extra_total_price_not_refundable">0</span>)
                                <input type="radio" name="paytype" class="paytype" value="not_refundable" checked class="required">
                                <span class="checkmark"></span>
                            </label>
                            <!-- <label style="display: none;" class="container_radio"><?php _e('Late charge', 'arienzo_reservation_form'); ?> (€<span class="service_extra_total_price_late_charge">0</span>)
                                <input style="display: none;" disabled type="radio" name="paytype" class="paytype" value="late_charge" class="required">
                                <span style="display: none;" class="checkmark"></span>
                            </label> -->
                            <label class="container_radio"><?php _e('Refundable', 'arienzo_reservation_form'); ?>  (<span class="service_total_price">€0</span>)
                                <input type="radio" name="paytype" class="paytype" value="refundable" class="required">
                                <span class="checkmark"></span>
                            </label>
                            <label class="container_radio"><?php _e('Last Minute', 'arienzo_reservation_form'); ?>  (<span class="service_total_price">€0</span>)
                                <input type="radio" name="paytype" class="paytype" value="last_minute" class="required">
                                <span class="checkmark"></span>
                            </label>
                        </div>
                        <!-- <div>
                            <p class="price_view_extra_refundable" style="display:none;color: #000000;">Refundable : <span class="service_total_price">€0</span></p>
                            <p class="price_view_extra_not_refundable" style="color: #000000;">Not Refundable : €<span class="service_extra_total_price_not_refundable">0</span></p>
                            <p style="display: none;" class="price_view_extra_late_charge" style="display:none;color: #000000;">Late Charge : €<span class="service_extra_total_price_late_charge">0</span></p>
                        </div> -->
                        <!-- <table class="service_total_price_view" >
                            
                            <tr class="price_view_not_refundable">
                                <td>Discount (5%)</td>
                                <td align="center">€<span class="service_extra_not_refundable_price">0</span></td>
                            </tr>
                            <tr class="price_view_late_charge" style="display:none;">
                                <td>Fees (5%)</td>
                                <td align="center">€<span class="service_extra_late_charge_price">0</span></td>
                            </tr>
                            <tr class="price_view_extra_refundable" style="display: none;"> 
                                <td>Refundable</td>
                                <td align="center"><span class="service_total_price">0</span></td>
                            </tr>
                            <tr class="price_view_extra_not_refundable">
                                <td>Not Refundable</td>
                                <td align="center">€<span class="service_extra_total_price_not_refundable">0</span></td>
                            </tr>
                            <tr class="price_view_extra_late_charge"  style="display:none;">
                                <td>Late Charge</td>
                                <td align="center">€<span class="service_extra_total_price_late_charge">0</span></td>
                            </tr>
                        </table> -->
                    </div>
                    <!-- /step-->

                </div>
                <!-- /middle-wizard -->
                <div id="bottom-wizard">
                    <button type="button" name="backward"
                            class="backward"><?php _e('Prev', 'arienzo_reservation_form'); ?></button>
                    <button type="button" name="forward"
                            class="forward"><?php _e('Next', 'arienzo_reservation_form'); ?></button>
                    <button type="submit" name="process"
                            class="submit submit<?php echo $iddd; ?>"><?php _e('Submit', 'arienzo_reservation_form'); ?></button>
                    <?php language_selector_flags(); ?>
                </div>
                <!-- /bottom-wizard -->
                <?php if(!empty($attr['service_id'])) { $mphb_price_quantity = get_post_meta( $attr['service_id'], 'mphb_price_quantity', true ); $date_child_price = get_post_meta( $attr['service_id'], 'mphb_child_price', true ); $date_price = get_post_meta( $attr['service_id'], 'mphb_price', true ); $price_group = get_post_meta($attr['service_id'], 'customdata_group', true);  //echo "<pre>"; print_r($price_group); echo "</pre>";die;  ?>
                    <input type="hidden" value='<?php echo json_encode($price_group); ?>' name="price_group">
                    <input type="hidden" value="<?php echo $date_price; ?>" name="date_price">
                    <input type="hidden" value="<?php echo $date_price; ?>" name="date_price2">
                    <input type="hidden" value='<?php echo js_array($blocked_dates2); ?>' name="blocked_dates2">
                    <input type="hidden" value="<?php echo $date_child_price; ?>" name="date_child_price">
                    <input type="hidden" value="<?php echo $date_child_price; ?>" name="date_child_price2">
                    <input type="hidden" value="<?php echo $mphb_price_quantity; ?>" name="mphb_price_quantity">
                    <input type="hidden" value="<?php echo $attr['service_id']; ?>" name="services[]">
                <?php } ?>
            </form>
            <ul class="custom_error_message"></ul>
        </div>
        <!-- /Wizard container -->
    </div>
    <style>
        .disable-item {
            background: #ececec;
            pointer-events: none;
            cursor: default;
            text-decoration: none;
            color: black;
        }
    </style>
    
    <script>
        var blocked_dates = <?php echo $blocked_dates; ?>;
        var blocked_dates2 = <?php echo js_array($blocked_dates2); ?>;
        var nowDate = new Date();
        var today = new Date(nowDate.getFullYear(), nowDate.getMonth(), nowDate.getDate(), 0, 0, 0, 0);
        var daysList = [];
        var price_group = {};
        var child_price_group = {};
        var price_group2 = {};
        var date_price = '<?php echo $date_price ?>';
        var date_child_price = "<?php echo $date_child_price ? $date_child_price : $date_price ?>";

        var date_price2 = '<?php echo $date_price ?>';
        var date_child_price2 = "<?php echo $date_child_price ? $date_child_price : $date_price ?>";
        var $mphb_price_quantity = "<?php echo isset($mphb_price_quantity) && $mphb_price_quantity ? $mphb_price_quantity : "once" ?>";
        
        price_group = {}
        price_group2 = JSON.parse('<?php echo json_encode($price_group) ?>');
        jQuery(document).ready(function () {
            $(".adult_price").text(date_price2);
            $(".child_price").text(date_child_price2);
            $(document).delegate(".qtyplus,.qtyminus","click",function (e) {
                $this = $(this);
                setTimeout(function(){
                    $this.parents(".qty-buttons").find(".qty").trigger("change"); 
                }, 100);
                
            });
            $(document).delegate("#people","change",function (e) {
                /*if($(this).val()){
                    val = $(this).val();
                }else{
                    val = 0;
                }
                date_price3 = $(this).parents(".wizard-form-main").find("input[name='date_price2']").val();
                mphb_price_quantity = $(this).parents(".wizard-form-main").find("input[name='mphb_price_quantity']").val();
                $(this).parents(".wizard-form-main").find(".adult_count").text(val);
                $(this).parents(".wizard-form-main").find(".adult_price").text(date_price3);
                if(val > 0){
                    if(mphb_price_quantity == "once"){
                        $(this).parents(".wizard-form-main").find(".price_view2").show();
                        $(this).parents(".wizard-form-main").find(".service_total_price").text(date_price3 * val);
                    }else{
                        $(this).parents(".wizard-form-main").find(".price_view").show();
                        $(this).parents(".wizard-form-main").find(".price_view_adult").show();
                        $(this).parents(".wizard-form-main").find(".service_total_price").text(date_price3 * val);
                    }
                }else{
                    if(mphb_price_quantity == "once"){
                        $(this).parents(".wizard-form-main").find(".price_view2").hide();
                        $(this).parents(".wizard-form-main").find(".service_total_price").text(0);    
                    }else{
                        if(!$(this).parents(".wizard-form-main").find("#child").val() || $(this).parents(".wizard-form-main").find("#child").val() <= 0){
                            $(this).parents(".wizard-form-main").find(".price_view").hide();
                        }
                        $(this).parents(".wizard-form-main").find(".price_view_adult").hide();
                        $(this).parents(".wizard-form-main").find(".service_total_price").text(0);    
                    }
                }*/
                calc_price($(this));
            });
            $(document).delegate("#child","change",function (e) {
                /*if($(this).val()){
                    val = $(this).val();
                }else{
                    val = 0;
                }
                date_child_price3 =  $(this).parents(".wizard-form-main").find("input[name='date_child_price2']").val();
                $(this).parents(".wizard-form-main").find(".child_count").text(val);
                $(this).parents(".wizard-form-main").find(".child_price").text(date_child_price3);
                if(val > 0){
                    $(this).parents(".wizard-form-main").find(".price_view").show();
                    $(this).parents(".wizard-form-main").find(".price_view_child").show();
                    $(this).parents(".wizard-form-main").find(".child_total_price").text(date_child_price3 * val);
                }else{
                    if(!$(this).parents(".wizard-form-main").find("#people").val() || $(this).parents(".wizard-form-main").find("#people").val() <= 0){
                        $(this).parents(".wizard-form-main").find(".price_view").hide();
                    }
                    $(this).parents(".wizard-form-main").find(".price_view_child").hide();
                    $(this).parents(".wizard-form-main").find(".child_total_price").text(0);
                }*/
                calc_price($(this));
            });
                
            if(price_group2){
                $.each(price_group2,function(i,j){
                    currentDate = new Date(j['startdate']);
                    end = new Date(j['enddate']);
                    while (currentDate <= end) {
                        currentDate2 = $.datepicker.formatDate('yy-mm-dd', currentDate)
                        if(new Date(currentDate2)  in price_group){

                        }else{
                            if(j['rate']){
                                price_group[currentDate2] = j['rate'];
                            }
                            if(j['child_rate']){
                                child_price_group[currentDate2] = j['child_rate'];
                            }
                        }
                        currentDate.setDate(currentDate.getDate() + 1);
                    }
                })
            }
            var $animSpeed = 200;

            $('#cleanAccordionMain > .accordion > dt:last-of-type').addClass('accordionLastDt');
            $('#cleanAccordionMain > .accordion > dd:last-of-type').addClass('accordionLastDd');
            $('#cleanAccordionMain > .accordion > dt:first-of-type').addClass('accordionFirstDt');

            $('#cleanAccordionMain > .accordion dd').hide();
            $('#cleanAccordionMain > .dropDown1 > dd:first-of-type').slideDown($animSpeed);
            $('#cleanAccordionMain > .dropDown1 > dt:first-child > .accordionContent').addClass('selected').parent().addClass('selected');
            $('#cleanAccordionMain > .accordion dt .accordionContent').click(function(){
                if($(this).closest('.singleTab').hasClass('hasContent')) {
                    if ($(this).closest(".accordionSlide ").hasClass('selected')) {
                        $(this).closest(".accordionSlide ").removeClass('selected').parent().removeClass('selected');
                        $(this).closest(".accordionSlide ").parent().next().slideUp($animSpeed);

                    } else {
                        $('#cleanAccordionMain > .accordion dt .accordionSlide').removeClass('selected').parent().removeClass('selected');
                        $(this).closest('.accordionSlide').addClass('selected').parent().addClass('selected');
                        $('#cleanAccordionMain > .accordion dd').slideUp($animSpeed);
                        $(this).closest(".accordionSlide ").parent().next().slideDown($animSpeed);
                    }
                }

                return false;
            });
            $(".phone_number<?php echo $iddd; ?>").intlTelInput({
                initialCountry: "auto",
                hiddenInput: "full_phone",
                geoIpLookup: function(callback) {
                    $.get('https://ipinfo.io', function() {}, "jsonp").always(function(resp) {
                        let countryCode = (resp && resp.country) ? resp.country : "";
                        callback(countryCode);
                    });
                },
                utilsScript: "<?php echo plugins_url('../assets/js/utils.js', __FILE__); ?>"
            });

            assignDatePicker(0);
            /*let maxField = 10;
            let addButton = $('.add_button');
            let wrapper = '.field_wrapper';

            var x = 1;
            $(addButton).click(function(){
                if(x < maxField){
                    let fieldHTML = '<div class="form-group field_wrapper">'+
                    '                            <input type="text" name="dates[' + x + ']" class="form-control required"'+
                    '                                   placeholder="<?php _e("When", "arienzo_reservation_form"); ?>" readonly>'+
                    '                            <i class="icon-hotel-calendar_3"></i>'+
                    '                            <a href="javascript:void(0);" class="remove_button"><img src="<?php echo plugins_url("../assets/img/minus.svg", __FILE__) ?>"/></a>'+
                    '                        </div>';
                    $(fieldHTML).insertAfter($(wrapper).last());
                    assignDatePicker(x)
                    x++;
                }
            });*/
            let maxField = 10

            var x = 1;
            $(".add_button").unbind("click");
            $('.add_button').click(function(){
                wrapper_parent = $(this).parent(".field_wrapper");
                count = wrapper_parent.data("count");
                
                if(count < maxField){
                    let fieldHTML = '<div class="form-group field_wrapper">'+
                        '                            <input type="text" name="dates[' + count + ']" class="form-control required"'+
                        '                                   placeholder="<?php _e("When", "arienzo_reservation_form"); ?>" readonly>'+
                        '                            <i class="icon-hotel-calendar_3"></i>'+
                        '                            <a href="javascript:void(0);" class="remove_button"><img src="<?php echo plugins_url("../assets/img/minus.svg", __FILE__) ?>"/></a>'+
                        '                        </div>';
                    $(fieldHTML).insertAfter(wrapper_parent);
                    assignDatePicker(count);
                    wrapper_parent.data("count",count+1)
                    x++;
                }
            });

            $(document).on('click', '.field_wrapper .remove_button', function(e){
                e.preventDefault();
                var name = $(this).closest('.form-group').find('input').attr("name");
                var val = $(this).closest('.form-group').find('input').val();

                $("#lunch_time .dd-options a").removeClass('disable-item');
                for (var i = 0; i < daysList.length; i++) {
                    if(daysList[i].date == val) {
                        delete daysList[i];
                    }
                }
                daysList = daysList.filter(function(e){return e});
                for (var j = 0; j < daysList.length; j++) {
                    var itemIndex = 0;
                    if(daysList[j].time == "12:00") {
                        itemIndex = 1
                    } else if(daysList[j].time == "13:00") {
                        itemIndex = 2
                    } else if(daysList[j].time == "13:15") {
                        itemIndex = 2
                    } else if(daysList[j].time == "14:30") {
                        itemIndex = 3
                    } else if(daysList[j].time == "15:30") {
                        itemIndex = 4
                    } else if(daysList[j].time == "11:59") {
                        itemIndex = 5
                    }
                    $("#lunch_time .dd-options").children().eq(itemIndex).find('a').addClass('disable-item');
                }
                $(this).parent('div').remove();
            });
        });

        function assignDatePicker(elementToAdd) {
            let name = 'input[name="dates[' + elementToAdd + ']"]';
           /* price_group = {}
            price_group2 = JSON.parse('<?php echo json_encode($price_group) ?>');

            if(price_group2){
                $.each(price_group2,function(i,j){
                    currentDate = new Date(j['startdate']);
                    end = new Date(j['enddate']);
                    while (currentDate <= end) {
                        currentDate2 = $.datepicker.formatDate('yy-mm-dd', currentDate)
                        if(new Date(currentDate2)  in price_group){

                        }else{
                            price_group[currentDate2] = j['rate'];
                        }
                        currentDate.setDate(currentDate.getDate() + 1);
                    }
                })
            }*/
            $.each($('input[name="dates['+elementToAdd+']"]'),function(i,j){
                $this = $(this);
                var price_group = {};
                var child_price_group = {};
                price_group2 = JSON.parse($this.parents(".wizard-form-main").find("input[name='price_group']").val());
                if(price_group2){
                    $.each(price_group2,function(i,j){
                        currentDate = new Date(j['startdate']);
                        end = new Date(j['enddate']);
                        while (currentDate <= end) {
                            currentDate2 = $.datepicker.formatDate('yy-mm-dd', currentDate)
                            if(new Date(currentDate2)  in price_group){
    
                            }else{
                                if(j['rate']){
                                    price_group[currentDate2] = j['rate'];
                                }
                                if(j['child_rate']){
                                    child_price_group[currentDate2] = j['child_rate'];
                                }
                            }
                            currentDate.setDate(currentDate.getDate() + 1);
                        }
                    })
                }
                if(typeof $(this).data("daterangepickerinit") !== undefined){
                    $(this).daterangepicker({
                        autoUpdateInput: false,
                        singleDatePicker: true,
                        "opens": "left",
                        "minDate": today,
                        locale: {
                            format: 'YYYY-MM-DD',
                            cancelLabel: 'Clear',
                            service_id:$this.parents(".wizard-form-main").find("input[name='services[]']").val(),
                            price_group:price_group,
                            child_price_group:child_price_group,
                            date_price:$this.parents(".wizard-form-main").find("input[name='date_price']").val(),
                            date_child_price:$this.parents(".wizard-form-main").find("input[name='date_child_price']").val(),
                            mphb_price_quantity:$this.parents(".wizard-form-main").find("input[name='mphb_price_quantity']").val(),
                            blocked_dates2:JSON.parse($this.parents(".wizard-form-main").find("input[name='blocked_dates2']").val()),
                        },
                        "isCustomDate" : function(date){
                            //$(this).attr('data-custom', "<?php echo $date_price ?>");
                            /*if(price_group.length){
                                if(date  in price_group){
                                    $(this).attr('data-custom', price_group[date]);
                                }else{
                                    $(this).attr('data-custom', "<?php echo $date_price ?>"); // Add custom data here
                                }
                            }else{
                                $.each($(".daterangepicker tr").attr('data-custom', "<?php echo $date_price ?>"); // Add custom data here
                            }*/
                        },
                        "isInvalidDate" : function(date,$this){
                            for(var ii = 0; ii < blocked_dates.length; ii++){
                                if (date.format('YYYY-MM-DD') == blocked_dates[ii]){
                                    return true;
                                }
                            }
                            
                            if($this.locale.blocked_dates2){
                                for(var ii = 0; ii < $this.locale.blocked_dates2.length; ii++){
                                    if (date.format('YYYY-MM-DD') == $this.locale.blocked_dates2[ii]){
                                        return true;
                                    }
                                }
                            }
                            //console.log(date.format('YYYY-MM-DD'))
                            <?php //if(isset($date_price) && $date_price) { ?>
                                /*if(price_group.length){
                                    if(date.format('YYYY-MM-DD')  in price_group){
                                        $(this).attr('data-custom', price_group[date.format('YYYY-MM-DD')]);
                                    }else{
                                        $(this).attr('data-custom', "<?php echo $date_price ?>"); // Add custom data here
                                    }
                                }else{
                                    $("this").attr('data-custom', "<?php echo $date_price ?>"); // Add custom data here
                                }*/
                                //$(this).attr('data-custom', "<?php echo $date_price ?>"); // Add custom data here
                                //addCustomInformation();
                            <?php //} ?>
                        },
                    });
                }
                $(this).data("daterangepickerinit","set")
            });
            function addCustomInformation() {
              setTimeout(function() {
                /*$(".daterangepicker tr").filter(function() {
                  var date = $(this).text();
                  return /\d/.test(date);
                }).find("td").attr('data-custom', "<?php echo $date_price ?>");*/
                /*$.each($(".daterangepicker tr").filter(function() {
                  var date = $(this).text();
                  return /\d/.test(date);
                }).find("td"),function(i,j){
                    var date = $(this).text();
                    //console.log(price_group)
                    if(price_group.length){
                        if(date  in price_group){
                            $(this).attr('data-custom', price_group[date]);
                        }else{
                            $(this).attr('data-custom', "<?php echo $date_price ?>"); // Add custom data here
                        }
                    }else{
                        $(this).attr('data-custom', "<?php echo $date_price ?>"); // Add custom data here
                    }
                })*/
              }, 0)
            }
            $(name).on('apply.daterangepicker', function (ev, picker) {
                let startDate = picker.startDate.format('YYYY-MM-DD');
                $(this).val(startDate);
                $this = $(this);
                $this.parents(".wizard-step").find("#lunch_time .dd-options a").removeClass('disable-item');
                $("#lunch_time .dd-options a .dd-option-text-na").remove();
                $("#lunch_time .dd-options a").removeClass('disable-item')
                daysList = [];
                
                $("input[name^='dates']").each(function(index, item) {
                    let date = $(item).val();
                    $("#lunch_time .dd-options a .dd-option-text-na").remove();
                    $("#lunch_time .dd-options a").removeClass('disable-item')
                    if (disabledDays[date] != undefined) {
                        for (var i = 0; i < disabledDays[date].length; i++) {
                            daysList.push(disabledDays[date][i])
                        }
                        for (var j = 0; j < daysList.length; j++) {
                            var itemIndex = 0;
                            if(daysList[j].time == "12:00") {
                                itemIndex = 1
                            } else if(daysList[j].time == "13:00") {
                                itemIndex = 2
                            } else if(daysList[j].time == "13:15") {
                                itemIndex = 2
                            } else if(daysList[j].time == "14:30") {
                                itemIndex = 3
                            } else if(daysList[j].time == "15:30") {
                                itemIndex = 4
                            } else if(daysList[j].time == "11:59") {
                                itemIndex = 5
                            }
                            $this.parents(".wizard-step").find("#lunch_time .dd-options").children().eq(itemIndex).find('a').addClass('disable-item');
                            if(!$("#lunch_time .dd-options").children().eq(itemIndex).find('a').find('.dd-option-text-na').length){
                                $("#lunch_time .dd-options").children().eq(itemIndex).find('a').find('.dd-option-text').after('<label class="dd-option-text-na"> - Not Available</label>');
                            }
                        }
                    }
                });
                price_group = picker.locale.price_group;
                child_price_group = picker.locale.child_price_group;
                date_price = picker.locale.date_price;
                date_child_price = picker.locale.date_child_price;
                
                mphb_price_quantity = picker.locale.mphb_price_quantity;
                
                if(mphb_price_quantity == "once"){
                    $(".price_view_2").show();
                }
                if (typeof price_group !== "undefined") {
                    if(startDate  in price_group){
                        date_price2 = price_group[startDate];
                        date_child_price2 = child_price_group[startDate];
                    }else{
                        date_price2 = date_price;
                        date_child_price2 = date_child_price;
                    }
                }else{
                    date_price2 = date_price;
                    date_child_price2 = date_child_price;
                }
                
                $(this).parents(".wizard-form-main").find("input[name='date_price2']").val(date_price2);
                $(this).parents(".wizard-form-main").find("input[name='date_child_price2']").val(date_child_price2);
                if(mphb_price_quantity == "once"){
                    $(this).parents(".wizard-form-main").find(".accommodation_total_price").text(date_price2);
                }
                
                $(".qty").trigger("change"); 
            });
            $(name).on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
            });
            $(name).bind('change', function () {

            });
            $(name).on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
            });
        }
        
        jQuery(document).ready(function () {
            jQuery(function ($) {
                "use strict";
                //$('form#wrapped').attr('action', 'booking_hotel.php');    window.ajaxEnabled = true;
                $(".wizard_container_<?php echo $iddd; ?>").wizard({
                    stepsWrapper: ".form_<?php echo $iddd; ?>",
                    submit: ".submit<?php echo $iddd; ?>",
                    beforeSelect: function (event, state) {
                        if ($('input#website').val().length != 0) {
                            return false;
                        }
                        if (!state.isMovingForward)
                            return true;
                        var inputs = $(this).wizard('state').step.find(':input');
                        return !inputs.length || !!inputs.valid();
                    }
                }).validate({
                    errorPlacement: function (error, element) {
                        if (element.is(':radio') || element.is(':checkbox')) {
                            error.insertBefore(element.next());
                        } else {
                            error.insertAfter(element);
                        }
                    },
                    submitHandler: function(form) {         $('.preloader-section-<?php echo $iddd; ?>').show();                     if(window.ajaxEnabled === false) {              return;         }           window.ajaxEnabled = false;         
                        let custom_form = $(form).find('form.form_<?php echo $iddd; ?>');
                        let formData = $(custom_form).serialize();
                        let data = {
                            data: formData,
                            action: 'arf_booking_ajax_request'
                        };
            
                        if ( custom_form.data('requestRunning') ) {
                            return;
                        }
                        var  list = $(form).find(".custom_error_message");
                        list.empty();
            
                        $.ajax({
                            url: arf_ajax_action.ajax_url,
                            type: form.method,
                            data: data,
                            method: "POST",
                            dataType: 'JSON',
                            success: function(response) {                   $('.preloader-section-<?php echo $iddd; ?>').hide();                 window.ajaxEnabled = true;
                                if(response.success) {
                                    if(response.requestParams){
                                        var form = document.createElement("form");
                                        form.setAttribute("method", "POST");
                                        form.setAttribute("action", response.requestUrl);
                                        for(var key in response.requestParams) {
                                            if(response.requestParams.hasOwnProperty(key)) {
                                                var hiddenField = document.createElement("input");
                                                hiddenField.setAttribute("type", "hidden");
                                                hiddenField.setAttribute("name", key);
                                                hiddenField.setAttribute("value", response.requestParams[key]);
                                                form.appendChild(hiddenField);
                                             }
                                        }
                                        document.body.appendChild(form);
                                        form.submit();
                                    }else{
                                        window.location.href = response.url;
                                    }
                                }
                                else {
                                    let messages = response.messages;
            
                                    for (let key in messages) {
                                        if (!messages.hasOwnProperty(key)) continue;
                                        let text = messages[key];
                                        list.append('<li>' + text + '</li>');
                                    }
                                }
                            },
                            complete: function() {
                                custom_form.data('requestRunning', false);
                            }
                        });
                    }
                });
                //  progress bar
                $(".progressbar<?php echo $iddd; ?>").progressbar();
                $(".wizard_container_<?php echo $iddd; ?>").wizard({
                    beforeForward: function (event, state) {
                        var $return = false;

                        $.each($(".wizard_container_<?php echo $iddd; ?> .product_main_div"),function(i,j){
                            if($(this).find("input").val() > 0){
                                $return = true;
                            }
                        });
                        if(!$return){
                            $(".wizard_container_<?php echo $iddd; ?> .error-text").show();
                            return $return;
                        }else{
                            var $return = true;
                            $(".wizard_container_<?php echo $iddd; ?> .error-text").hide();
                            $this = $(this);
                            var products<?php echo $iddd; ?> = [];
                            var people = $(".wizard_container_<?php echo $iddd; ?> input[type='number'][name='people']").val()
                            if()
                            $.each($(".wizard_container_<?php echo $iddd; ?> .product_main_div"),function(){
                                var inputt = $(this).find("input");
                                var max = $(this).data("max");
                                var stock = $(this).data("stock");
                                if(inputt.val() > 0){
                                    var idd = (inputt.attr("id")).replace("product_","");
                                    val = 1 ;
                                    console.log(max +" - "+people)
                                    if(max && max < people){
                                        val =  Math.ceil(people/max)
                                    }
                                    if(val > stock){
                                        $return = false;
                                        $(".wizard_container_<?php echo $iddd; ?> .error-outofstock-text").show();
                                        return false;
                                    }
                                }
                            });
                            if($return){
                                $(".wizard_container_<?php echo $iddd; ?> .error-outofstock-text").hide();
                            }
                            return $return;
                            /*$.ajax({
                                url:'<?php echo admin_url('admin-ajax.php') ?>',
                                type:'POST',
                                dataType:'json',
                                data:{
                                    products:products<?php echo $iddd; ?>,
                                    "action":"check_product_qty",
                                    people:$(".wizard_container_<?php echo $iddd; ?> input[name='people']").val()
                                },
                                beforeSend:function(){
                                    $this.button("loading");
                                },
                                complete:function(){
                                    $this.button("reset");
                                },
                                success:function(json){
                                    if(json['success'] == "0"){
                                        $(".wizard_container_<?php echo $iddd; ?> .error-outofstock-text").show();
                                        return false;
                                    }
                                    $(".wizard_container_<?php echo $iddd; ?> .error-outofstock-text").hide();
                                    return true;
                                },
                            })*/
                        }


                    },
                    afterSelect: function (event, state) {
                        /*if(state.stepIndex === 1) {
                            $('.forward').text('Skip')
                        }*/
                        $(".progressbar<?php echo $iddd; ?>").progressbar("value", state.percentComplete);
                        $("#location").text("(" + state.stepsComplete + "/" + state.stepsPossible + ")");
                    }
                });
            });
        });
    </script>
    <?php $output = ob_get_contents();
    ob_end_clean();
    return $output;
}
function arf_shortcode_search2($atts)
{   
    $attr = shortcode_atts( 
        array(
         'service_id' => false,
         'lunch_time_enable' => 1,
         'child_hidden' => 0,
         'adult_text' => "Adults",
         'accomodation_text' => "Per Accommodation",
         'is_home_page' => 0,
         'default_adult' => 0,
         'default_childs' => 0,
         'mphb_check_in_date' => "",
         'action2' => "",
         'service_title' => "",
         ), $atts 
     );
    //$mphb_show_child = get_post_meta($attr['service_id'],"mphb_show_child",1);
    //$attr['child_hidden'] = $mphb_show_child ? "" : 1;
    wp_enqueue_style('arf_google_fonts');
    wp_enqueue_style('arf_bootstrap_css');
    wp_enqueue_style('arf_style_css');
    wp_enqueue_style('arf_vendors_css');
    wp_enqueue_style('arf_intTelInput_css');
    wp_enqueue_script('modernizr_js');
    wp_deregister_script('jquery');
    wp_enqueue_script('arf_jquery');
    wp_enqueue_script('arf_common_scripts_js');
    wp_enqueue_script('arf_velocity_js');
    wp_enqueue_script('arf_script_js');
    //wp_enqueue_script('arf_booking_form');
    wp_enqueue_script('arf_intTelInput_js');
    wp_localize_script('arf_script_js', 'arf_ajax_action', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ajax-nonce'),
        'pluginsUrl' => plugins_url('arienzo-reservation-form'),
    ));

    global $sitepress;
    $current_lang = $sitepress->get_current_language(); //save current language
    $sitepress->switch_lang($current_lang); //restore previous language
    $services = get_posts([
        'numberposts'       => -1,
        'post_type'     => 'mphb_room_service',
        'post_status'   => 'publish',
        'suppress_filters' => 0
    ]);
    $lunch_time_list = array();
    $lunch_time_list_table = get_posts([
        'numberposts'       => -1,
        'post_type'     => 'lunch_time',
        'post_status'   => 'publish',
        'suppress_filters' => 0,
        'order'=> "asc",
        'orderby'=> "post_title",
        'meta_query' => array(
            'relation' => 'OR', /* <-- here */
            array(
                'key' => 'lunch_time_type',
                'value' => "lunch_time"
            ),
            array(
                'key' => 'lunch_time_type',
                'value'   => '',
                'compare' => '='
            ),
            array(
                'key' => 'lunch_time_type',
                'compare'   => 'NOT EXISTS',
            )
        ),
    ]);
    foreach ($lunch_time_list_table as $key => $value) {
        $lunch_time_list[] = array(
            "id" => $value->ID,
            "title" => $value->post_title." - Terrace",
            "sunbed" => ""
        );
    }
    $lunch_time_list_sunbed = get_posts([
        'numberposts'       => -1,
        'post_type'     => 'lunch_time',
        'post_status'   => 'publish',
        'suppress_filters' => 0,
        'order'=> "asc",
        'orderby'=> "post_title",
        'meta_query' => array(
            array(
                'key' => 'lunch_time_type',
                'value' => "lunch_at_your_sunbed"
            )
        ),
    ]);

    foreach ($lunch_time_list_sunbed as $key => $value) {
        $lunch_time_list[] = array(
            "id" => $value->ID,
            "title" => $value->post_title." - Sunbed",
            "sunbed" => "1"
        );
    }

    $blocked_dates = js_array(getBookingRules());
    $blocked_dates2 = array();
    $disabledDays = get_option('arf_lunch_days');

    $iddd = uniqid();
    ob_start(); ?>
    <?php if(!empty($attr['service_id'])) {
            $features_image_type = get_post_meta($atts['service_id'], 'features_image_type', true);
            $service_price = get_post_meta($atts['service_id'], 'service_price', true);
            $min_pax = get_post_meta($atts['service_id'], 'min_pax', true);
            $max_pax = get_post_meta($atts['service_id'], 'max_pax', true);

            $featured_img_url = array();
            $min = array();
            $max = array();
            foreach ($max_pax as $key => $value) {
                $max[$key] = $value;
            }
            foreach ($min_pax as $key => $value) {
                $min[$key] = $value;
            }
            $qty_stock = 0;
            foreach ($features_image_type as $key => $value) { if($value == "service") continue;
                $qty_stock += (int)get_post_meta($value,"stock",1);
            }
        }
    ?>
    <style type="text/css">
        .daterangepicker td[data-custom] {
          position: relative;
        }
          /*STYLE THE CUSTOME DATA HERE*/
        /*.daterangepicker td[data-custom]::after {
          content: '€' attr(data-custom);
          display: block;
          font-size: 10px;
          color: #4b9bff;
        }*/
        .price_view,.price_view_2{
            background: #ffffff;
        }
        .price_view td,.price_view_2 td{
            border : 1px solid #d2d8dd !important;
        }
        .service_total_price_view{
            background: #ffffff;
        }
        .service_total_price_view td{
            border : 1px solid #d2d8dd !important;
        }
        /*.block_pop_date{
            text-decoration: none !important;
            cursor: pointer !important;
        }*/
        .dd-option-text-na{
            line-height: 14px !important;
            margin-left: 5px;
        }
        .checked-icon i {
            position: relative !important;
            margin: 0;
            display: inline-block !important;
            margin-left: 6px;
            top: 1px !important;
            left: 0;
            font-size: 15px !important;
        }
        .container_radio2 input:checked ~ .container_radio_label .checked-icon i {
            color: #2abfaa;
        }
        .popover .popover-body{
            font-size: 12px;
        }
        @media (max-width: 500px){
            .checked-icon{
                font-size: 17px;
            }
            .flex-wrap-mobile{
                flex-wrap: wrap;
            }
            .flex-wrap-mobile .w-49{
                width: 100%;
            }
        }
        @media (min-width: 400px){
            .popover {
                max-width: 400px !important;
            }
        }
        @media (min-width: 375px){
            .popover {
                max-width: 375px !important;
            }
        }
        
        #lunch_time .dd-options li:first-child{
            display: none;
        }
    </style>
    <script>
        var disabledDays = <?php echo json_encode($disabledDays );?>;
        disabledDays = disabledDays.reduce(function (r, a) {
            r[a.date] = r[a.date] || [];
            r[a.date].push(a);
            return r;
        }, Object.create(null));
    </script>
    <div class="content-right" id="start"><div id="preloader-section" class="preloader-section-<?php echo $iddd; ?>"><div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div></div>
        <div id="wizard_container" class="wizard_container wizard_container_<?php echo $iddd; ?>" data-service_id="<?php if(!empty($attr['service_id'])){echo $attr['service_id'];} ?>">
            <div id="top-wizard" style="display: none;">
                <div id="progressbar" class="progressbar<?php echo $iddd; ?>"></div>
            </div>
            <!-- /top-wizard -->
            <form id="wrapped" class="wizard-form-main form_<?php echo $iddd; ?>" method="POST" data-action2="<?php echo $attr['action2'] ?>">
                <input id="website" name="website" type="text" value="">
                <?php wp_nonce_field('arf_form_action'); ?>
                <div id="middle-wizard">
                    <div class="step">

                        <?php if($qty_stock == 0){ ?>
                            <div style="text-align: center;">
                                <img src="https://booking.arienzobeachclub.com/wp-content/uploads/2022/11/sold_out_PNG34.png" width="150">
                            </div>
                        <?php } ?>
                        <h3 class="main_question">
                            <strong>1/3</strong> <?php _e('Enter your Booking details', 'arienzo_reservation_form'); ?>
                        </h3>
                        <div class="form-group field_wrapper" data-count="1">
                            <input type="text" name="dates[0]" class="form-control required" value="<?php echo $attr['mphb_check_in_date'] ?>"
                                   placeholder="<?php _e('When', 'arienzo_reservation_form'); ?>" readonly>
                            <i class="icon-hotel-calendar_3"></i>
                            <!-- <a href="javascript:void(0);" class="add_button" title="Add field"><img src="<?php //echo plugins_url("../assets/img/plus.svg", __FILE__) ?>"/></a> -->
                        </div>
                        <div class="d-flex justify-content-around flex-wrap-mobile">
                            <div class="form-group w-49">
                                <div class="styled-select clearfix">
                                    <select class="required ddslick" name="beach_arrival_time" id="beach_arrival_time">
                                        <option value=""
                                                data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                            <?php _e('Beach arrival time', 'arienzo_reservation_form'); ?>
                                        </option>
                                        <option value="9:50"
                                                data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                            9:50
                                        </option>
                                        <option value="10:10"
                                                data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                            10:10
                                        </option>
                                        <option value="10:30"
                                                data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                            10:30
                                        </option>
                                        <option value="10:50"
                                                data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                            10:50
                                        </option>
                                        <option value="11:10"
                                                data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                            11:10
                                        </option>
                                        <option value="11:30"
                                                data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                            11:30
                                        </option>
                                        <option value="11:50"
                                                data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                            11:50
                                        </option>
                                        <option value="12:10"
                                                data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                            12:10
                                        </option>
                                        <option value="12:30"
                                                data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                            12:30
                                        </option>
                                        <option value="13:00"
                                                data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                            13:00
                                        </option>
                                        <option value="13:15"
                                                data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                            13:15
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <?php if($attr['lunch_time_enable'] == 1) { ?>
                                <div class="form-group w-49">
                                    <div class="styled-select clearfix">
                                        <select class="ddslick" name="lunch_time" id="lunch_time">
                                                <option value=""
                                                        data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/restaurant-plate.svg', __FILE__) ?>">
                                                    <?php _e('Lunch time', 'arienzo_reservation_form'); ?>
                                                </option>
                                                <?php if(!empty($lunch_time_list)){ ?>
                                                    <?php foreach ($lunch_time_list as $key => $vvv) { ?>
                                                    <?php if($vvv['sunbed']){ ?>
                                                        <option value="<?php echo $vvv['id'] ?>" data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/sunbed-beach.svg', __FILE__) ?>">
                                                            <?php echo $vvv['title'] ?>
                                                        </option>
                                                    <?php }else{ ?>
                                                        <option value="<?php echo $vvv['id'] ?>" data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/restaurant-plate.svg', __FILE__) ?>">
                                                            <?php echo $vvv['title'] ?>
                                                        </option>
                                                    <?php } ?>
                                                    <?php } ?>
                                                <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="d-flex justify-content-around pb-1">
                            <div class="w-49">
                                <div class="form-group">
                                    <div class="qty-buttons">
                                        <input type="button" value="<?= $attr['default_adult'] ? $attr['default_adult'] : "+" ?>" class="qtyplus" name="people">
                                        <input type="number" name="people" id="people" value="<?= $attr['default_adult'] ?>"
                                               class="qty form-control required"
                                               placeholder="<?php _e($attr['adult_text'], 'arienzo_reservation_form'); ?>" readonly min="1">
                                        <input type="button" value="<?= $attr['default_adult'] ? $attr['default_adult'] : "-" ?>" class="qtyminus" name="people">
                                    </div>
                                </div>
                            </div>
                            <div class="w-49" style="<?php echo ($attr['child_hidden']) ? "display:none" : "" ?>" >
                                <div class="form-group">
                                    <div class="qty-buttons">
                                        <input type="button" value="<?= $attr['default_childs'] ? $attr['default_childs'] : "-" ?>" class="qtyplus" name="child">
                                        <input type="number" name="child" id="child" value="<?= $attr['default_childs'] ? $attr['default_childs'] : "" ?>"
                                               class="qty form-control"
                                               placeholder="<?php _e('Child', 'arienzo_reservation_form'); ?>" readonly>
                                        <input type="button" value="<?= $attr['default_childs'] ? $attr['default_childs'] : "-" ?>" class="qtyminus" name="child">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="service_detail_text" style="display: none;">
                            <small><?= $attr['service_title'] ? "The ".$attr['service_title'] : "" ?> <span class="service_detail_text_innner"></span></small>
                        </div>
                        <!-- <table class="service_total_price_view" > -->
                            <!-- <tr>
                                <td>Price</td>
                                <td align="center"><span class="service_total_price">-</span></td>
                            </tr> -->
                            <!-- <tr class="price_view_adult" style="display:none;">
                                <td>Adult (<span class="adult_count">0</span> X €<span class="adult_price">0</span>)</td>
                                <td>€<span class="adult_total_price">0</span></td>
                            </tr>
                            <tr class="price_view_child" style="display:none;">
                                <td>Child (<span class="child_count">0</span> X €<span class="child_price">0</span>)</td>
                                <td>€<span class="child_total_price">0</span></td>
                            </tr> -->
                        <!-- </table> -->
                        <?php $date_price = 0; if(!empty($attr['service_id'])) { $date_price = get_post_meta( $attr['service_id'], 'mphb_price', true ); 
                            $price_group = get_post_meta($attr['service_id'], 'customdata_group', true);
                            $dates = get_post_meta($attr['service_id'], 'mphb_block_dates', true);
                                
                            if($dates){
                                foreach (explode(",", $dates) as $key => $value) {
                                    $blocked_dates2[] = date("Y-m-d",strtotime($value));
                                }
                            }
                            if($price_group){
                                $post_date = strtotime(date("Y-m-d",strtotime($_POST['check_in_date'])));
                                foreach ($price_group as $key => $value) {
                                    if($post_date >= strtotime(date("Y-m-d")) && $post_date <= strtotime(date("Y-m-d"))){
                                        if($value['rate']){
                                            $date_price = $value['rate'];
                                        }
                                    }
                                }
                            }
                        } ?>
                        <!-- <table class="price_view_2" style="display:none;">
                            <tr class="price_view_accomodation">
                                <td><?php echo $attr['accomodation_text'] ?></td>
                                <td>€<span class="accommodation_total_price"><?php echo $date_price; ?></span></td>
                            </tr>
                        </table> -->
                        <?php if(true || !empty($_GET['abc_test'])){ ?>
                        <?php if(!empty($attr['service_id'])) {
                            $default = get_post_meta($atts['service_id'], 'default', true);
                            $features_products = get_post_meta($atts['service_id'], 'features_image_type', true);
                            $service_price = get_post_meta($atts['service_id'], 'service_price', true);
                            $min_pax = get_post_meta($atts['service_id'], 'min_pax', true);
                            $max_pax = get_post_meta($atts['service_id'], 'max_pax', true);

                            $featured_img_url = array();
                            $min = array();
                            $max = array();
                            foreach ($max_pax as $key => $value) {
                                $max[$key] = $value;
                            }
                            foreach ($min_pax as $key => $value) {
                                $min[$key] = $value;
                            }

                            
                            $features_image_type = array();
                            $defaults_sort_order = array();
                            $na_defaults = array();
                            $na_defaults_sort_order = array();
                            $defaults = array();

                            if($features_products){
                                foreach ($features_products as $key => $value) {
                                    if($value == "service"){
                                        $features_image_type["service"] = $service;
                                    }else{
                                        if ( get_post_status ( $value ) == 'trash' ) {
                                            continue;
                                        }
                                        $qty_stock = (int)get_post_meta($value,"stock",1);
                                        if(!$qty_stock) continue;
                                        if(empty($service_price[$value]) || (int)$service_price[$value] < 1) continue;
                                        $url = get_the_post_thumbnail_url($value,'full');
                                        if($url){
                                            if($default && in_array($value, $default)){
                                                $defaults[$value] = $value;
                                                $defaults_sort_order[$value] = isset($service_price[$value]) ? $service_price[$value] : 0;
                                            }else{
                                                $na_defaults[$value] = $value;
                                                $na_defaults_sort_order[$value] = isset($service_price[$value]) ? $service_price[$value] : 0;
                                            }
                                        }
                                    }
                                }
                            }


			/* Qadisha - QD - 20230520 - HotFix for errors on page construction */
			if (empty($na_defaults)) {
				error_log('This is an empty array!');
			} else {
			}
                            if($defaults){
	                            array_multisort($defaults_sort_order, SORT_DESC, $defaults);    
                            }
                            if($na_defaults){
                                array_multisort($na_defaults_sort_order, SORT_DESC, $na_defaults);
                            }
                            $features_image_type = array_merge($features_image_type,$defaults,$na_defaults);
                                
                                
                            $adult = !empty($_GET['adult']) ? (int)$_GET['adult'] : 1;
                            ?>
                            <?php $default_selected = ""; foreach ($features_image_type as $key => $value) { if($value == "service") continue; 
                                $qty_stock = (int)get_post_meta($value,"stock",1);
                                 $bottal_attribute = get_post_meta($value,"bottal_attribute",1);
                                if(!$qty_stock) continue;
                                ?>
                                <?php $title = get_the_title( $value ); ?>
                                <?php if($title) { 
                                    $availability_range = get_post_meta($value,"availability_range",1);
                                    $is_default = false;
                                    if($default && in_array($value, $default) && !$default_selected){
                                        if($availability_range){
                                            foreach ($availability_range as $kk => $vvv) {
                                                $startdate = $vvv['startdate'];
                                                $parts = explode('-',$startdate);
                                                $startdate = $parts[2] . '-' . $parts[0] . '-' . $parts[1];
                                                 
                                                $enddate = $vvv['enddate'];
                                                $parts = explode('-',$enddate);
                                                $enddate = $parts[2] . '-' . $parts[0] . '-' . $parts[1];
                                                if($attr['mphb_check_in_date'] && strtotime($startdate) <= strtotime($attr['mphb_check_in_date']) && strtotime($enddate) >= strtotime($attr['mphb_check_in_date']) && isset($min[$value]) && $adult >= $min[$value]){
                                                    $default_selected = 1;
                                                    $is_default = true;
                                                }
                                            }
                                        }else{
                                            $default_selected = 1;
                                            $is_default = true;
                                        }
                                    }
                                    ?>
                                    <div class="product_div_<?php echo $value; ?> product_main_div" data-bottal_attribute="<?= $bottal_attribute ?>" data-stock="<?= $qty_stock ?>" data-min="<?php echo (isset($min[$value]) ? $min[$value] : 0) ?>" data-max="<?php echo (isset($max[$value]) ? $max[$value] : 0) ?>" style="display: none;">
                                        <input type="hidden" name="product[<?php echo $value ?>]" value="<?= $is_default ? "1" : "0" ?>" id="product_<?php echo $value ?>" data-value="<?= isset($service_price[$value]) ? $service_price[$value] : 0 ?>">
                                        <table style="background: #ffffff;">
                                            <tbody>
                                                <tr>
                                                    <td style="font-size: 12px;" class="product_title"><?= $title ?></td>
                                                    <td style="font-size: 12px;    width: 40px;"><?= isset($service_price[$value]) ? "€".$service_price[$value] : "-" ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- <div class="product_div_<?php echo $value; ?> product_main_div" style="display: none;">
                                        <div class="row no-gutters pb-1">
                                            <div class="col-12">
                                                <label for="" style="display: inline-block;"><?php _e($title, 'arienzo_reservation_form'); ?></label>
                                                <div class="form-group">
                                                    <div class="qty-buttons-2">
                                                        <input type="button" value="+" class="qtyplus-2" name="product_<?php echo $value ?>">
                                                        <input type="number" name="product[<?php echo $value ?>]" id="product_<?php echo $value ?>" min="1" value="" class="qty-2 form-control required" placeholder="<?php _e($title, 'arienzo_reservation_form'); ?>">
                                                        <input type="button" value="-" class="qtyminus-2" name="product_<?php echo $value ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div> -->
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                        <?php } ?>
                        <div class="error-text" style="display: none;">Please Select At Least One Product.</div>
                        <div class="error-outofstock-text" style="display: none;">Product Out Of Stock.</div>
                    </div>
                    <?php if(empty($attr['service_id']) && $attr['is_home_page'] != 1) { ?>
                        <div class="step">
                            <h3 class="main_question"><strong>2/3</strong><?php _e('Select Reserve Options', 'arienzo_reservation_form'); ?></h3>

                            <div id="cleanAccordionMain" class="cleanAccordion">
                                <dl class="accordion">
                                    <?php foreach ($services as $service) {
                                        $icon = get_post_meta($service->ID, 'arf_service_icon', true);
                                        $price = get_post_meta($service->ID, 'mphb_price', true);
                                        $child_price = get_post_meta($service->ID, 'mphb_child_price', true);
                                        ?>
                                        <dt class="tab_1 singleTab <?php echo ( '' !== $service->post_content ) ? 'hasContent' : ''; ?>">
                                            <div class="accordionSlide">
                                                <div class="accordionContent float-left" style="width: calc(100% - 85px);">
                                                    <div style="display: inline-block; width: calc(100% - 44px); float: left;font-size: 14px;color: #000;">
                                                        <i class="<?php echo $icon; ?>"></i> <?php echo $service->post_title; ?> &euro;<?php echo $price; ?>
                                                    </div>
                                                    <?php if( '' !== $service->post_content ) { ?>
                                                        <div style="display: inline-block; width: 44px; float: right">
                                                            <span class="details"><img width="20" height="20" src="<?php echo plugins_url('../assets/img/icons_select/angle-down.svg', __FILE__) . "" ?>" alt=""></span>
                                                        </div>
                                                    <?php } ?>
                                                    <div style="clear:both"></div>
                                                </div>
                                                <label class="switch-light switch-ios float-right">
                                                    <input type="checkbox" value="<?php echo $service->ID; ?>" name="services[]">
                                                    <span><span>No</span><span>Yes</span></span>
                                                    <a></a>
                                                </label>
                                                <div style="clear:both"></div>
                                            </div>
                                            <div style="clear: bottom"></div>
                                        </dt>
                                        <?php if( '' !== $service->post_content ) { ?>
                                            <dd class="singleTabBody">
                                                <div>
                                                    <p><?php echo $service->post_content; ?></p>
                                                </div>
                                            </dd>
                                        <?php } ?>
                                    <?php } ?>
                                </dl>
                            </div>
                            <!--                        <div>                            -->
                            <!--                            <em style="font-size:11px;">--><?php //_e('*the options proposed are not inclusive of the Beach Club access fee.', 'arienzo_reservation_form'); ?><!--</em>-->
                            <!--                        </div>-->
                            <div style="clear: bottom"></div>
                        </div>
                    <?php } ?>
                    <div class="step">
                        <h3 class="main_question">
                            <strong><?php echo empty($attr['service_id']) ? "3/4" : "2/3"; ?></strong><?php _e('Please fill with your details', 'arienzo_reservation_form'); ?>
                        </h3>
                        <div class="d-flex justify-content-around">
                            <div class="form-group w-49">
                                <input type="text" name="first_name" class="form-control required"
                                       placeholder="<?php _e('First Name', 'arienzo_reservation_form'); ?>">
                                <i class="icon-user"></i>
                            </div>
                            <div class="form-group w-49">
                                <input type="text" name="last_name" class="form-control required"
                                       placeholder="<?php _e('Last Name', 'arienzo_reservation_form'); ?>">
                                <i class="icon-user"></i>
                            </div>
                        </div>
                        <div class="d-flex justify-content-around">
                            <div class="form-group w-49">
                                <input type="email" name="email" class="form-control required noclasse"
                                       placeholder="<?php _e('Email', 'arienzo_reservation_form'); ?>">
                                <i class="icon-envelope"></i>
                            </div>
                            <div class="form-group w-49">
                                <input type="tel" name="phone" class="form-control required phone_number<?php echo $iddd; ?>" id="phone_number"
                                       placeholder="<?php _e('Telephone', 'arienzo_reservation_form'); ?>">
                                <i class="icon-phone"></i>
                            </div>
                        </div>
                        <div class="form-group terms">
                            <label class="container_check"><?php _e('Please accept our', 'arienzo_reservation_form'); ?>
                                <a href="https://booking.arienzobeachclub.com/terms-conditions" target="_blank"><?php _e('Terms and conditions', 'arienzo_reservation_form'); ?></a>
                                <input type="checkbox" name="terms" value="Yes" class="required">
                                <span class="checkmark"></span>
                            </label>
                        </div>
                    </div>
                    <div class="submit step">
                        <h3 class="main_question">
                            <strong><?php echo empty($attr['service_id']) ? "4/4" : "3/3"; ?></strong><?php _e('Select Payment Method', 'arienzo_reservation_form'); ?>
                        </h3>
                        <div class="form-group">
                            <label class="container_radio2">
                                <input type="radio" name="paytype" class="paytype" value="not_refundable" checked class="required">
                                <div class="container_radio_label">
                                    <!-- <div class="checked-icon"><?php _e('Not Refundable', 'arienzo_reservation_form'); ?><i class="icon icon-info"  data-toggle="tooltip" data-html="true" title="<em>Tooltip</em> <u>with</u> <b>HTML</b>"></i></div> -->
                                    <div class="checked-icon"><?php _e('Not Refundable', 'arienzo_reservation_form'); ?><i class="icon icon-info"  data-toggle="popover" data-placement="left"   data-trigger="focus"   tabindex="0" data-html="true" data-content="<strong>NON-REFUNDABLE RATE:</strong><br/>Non-refundable prepayment guarantees a 5% discount on the package deal rate and allows a maximum of 1 date change, according to availability. Rescheduling is allowed up to 10 days from the date of arrival, to request a free date change it is mandatory to send an email to mailto:reservation@arienzobeachclub.com. In the event of a new date with a higher rate it will be necessary to pay the difference in price. In the event of a lower rate is permitted to spend the credit to purchase extra services at the property. Any remaining credit will not be refunded after check-out.<br/><br/><strong>REFUND/CANCELLATION POLICY:</strong><br/>The rate is non-refundable and cannot be cancelled whatever the reason of cancellation.<br/><br/><strong>SPECIAL REFUND/CANCELLATION WEATHER POLICY:</strong><br/>The non-refundable prepayment will not be refunded in case of cancellation by the client due to weather conditions. If there was an official warning issued for the area due to the extreme weather conditions (e.i. hurricanes, rough sea, flood, dangerous thunderstorms, tornados, etc) last-minute rescheduling is always allowed. The client will be refunded the total amount if rescheduling for dangerous weather (in which the beach club is forced to close) is not possible (e.i. alternative dates not available, available dates are not suitable for the client)."></i></div>
                                    <div>€<span class="service_extra_total_price_not_refundable">0</span></div>
                                </div>
                            </label>
                            <label class="container_radio2 last_minute_payment" style="display:none;">
                                <input type="radio" name="paytype" class="paytype" value="last_minute" checked class="required">
                                <div class="container_radio_label">
                                    <div class="checked-icon"><?php _e('Last Minute', 'arienzo_reservation_form'); ?><i class="icon icon-info"  data-toggle="popover" data-placement="left"   data-trigger="focus"   tabindex="0" data-html="true" data-content="<strong>Last Minute Rate:</strong><br/>Last minute reservations are paid at the moment of booking and cannot be cancelled and/or amended. In case of cancellation, 100% of the total value of the reservation will be charged as a cancellation fee.<br/><br/><strong>REFUND/CANCELLATION POLICY:</strong><br/>The rate is non-refundable and cannot be cancelled whatever the reason of cancellation.<br/><br/><strong>SPECIAL REFUND/CANCELLATION WEATHER POLICY:</strong><br/>The non-refundable prepayment will not be refunded in case of cancellation by the client due to weather conditions. If there was an official warning issued for the area due to the extreme weather conditions (e.i. hurricanes, rough sea, flood, dangerous thunderstorms, tornados, etc) last-minute rescheduling is always allowed. The client will be refunded the total amount if rescheduling for dangerous weather (in which the beach club is forced to close) is not possible (e.i. alternative dates not available, available dates are not suitable for the client)"></i></div>
                                    <div><span class="service_total_price">0</span></div>
                                </div>
                            </label>
                            <!-- <label class="container_radio2">
                                <input type="radio" name="paytype" class="paytype" value="late_charge" class="required">
                                <div style="display: none;" class="container_radio_label">
                                    <div class="checked-icon"><?php _e('Late charge', 'arienzo_reservation_form'); ?> <i class="icon icon-info"  data-toggle="popover" data-placement="left"  data-trigger="focus" tabindex="0" data-html="true" data-content="<strong>LATE CHARGE RATE:</strong><br/>Payment is required 15 days prior to arrival, otherwise the reservation will be cancelled. Rescheduling is allowed up to 15 days from the date of arrival, to request a date change it is mandatory to send an email to mailto:reservation@arienzobeachclub.com. In the event of a new date with a higher rate it will be necessary to pay the difference in price. In the event of a lower rate is permitted to spend the credit to purchase extra services at the property. Any remaining credit will not be refunded after check-out.<br/><br/><strong>REFUND/CANCELLATION POLICY:</strong>The rate, once paid, is non-refundable and cannot be cancelled whatever the reason of cancellation.<br/><br/><strong>SPECIAL REFUND/CANCELLATION WEATHER POLICY:</strong>The Late Charge Rate will not be refunded in case of cancellation by the client due to weather conditions. If there was an official warning issued for the area due to the extreme weather conditions (e.i. hurricanes, rough sea, flood, dangerous thunderstorms, tornados, etc) last-minute rescheduling is always allowed. The client will be refunded the total amount if rescheduling for dangerous weather (in which the beach club is forced to close) is not possible (e.i. alternative dates not available, available dates are not suitable for the client)."></i></div>
                                    <div>€<span class="service_extra_total_price_late_charge">0</span></div>
                                </div>
                            </label> -->
                            <label class="container_radio2">
                                <input type="radio" name="paytype" class="paytype" value="refundable" class="required">
                                <div class="container_radio_label">
                                    <div class="checked-icon"><?php _e('Refundable', 'arienzo_reservation_form'); ?><i class="icon icon-info"  data-toggle="popover" data-placement="left"  data-trigger="focus" tabindex="0" data-html="true" data-content="<strong>REFUNDABLE RATE:</strong><br/>Cancellation before 10 days from the date of arrival provides a full refund without penalty. If cancelled or changed within less than 10 days from the date of arrival, the rate is no longer refundable, even in case of no-show. Rescheduling is allowed up to 10 days from the date of arrival, to request a free date change it is mandatory to send an email to mailto:reservation@arienzobeachclub.com. In the event of a new date with a higher rate it will be necessary to pay the difference in price. In the event of a lower rate is permitted to spend the credit to purchase extra services at the property. Any remaining credit will not be refunded after check-out.<br/><br/><strong>CANCELLATION POLICY:</strong><br/>Cancellation before 10 days from the date of arrival provides a full refund without penalty. If cancelled within less than 10 days from the date of arrival, the rate is no longer refundable, even in case of no-show.<br/><br/><strong>SPECIAL REFUND/CANCELLATION WEATHER POLICY:</strong><br/>The Refundable Rate will not be refunded if the client cancels the reservation within less than 10 days prior to arrival date due to weather conditions. If there was an official warning issued for the area due to the extreme weather conditions (e.i. hurricanes, rough sea, flood, dangerous thunderstorms, tornados, etc) last-minute rescheduling is always allowed. The client will be refunded the total amount if rescheduling for dangerous weather (in which the beach club is forced to close) is not possible (e.i. alternative dates not available, available dates are not suitable for the client)."></i></div>
                                    <div><span class="service_total_price">€0</span></div>
                                </div>
                            </label>
                        </div>
                        <!-- <div>
                            <p class="price_view_extra_refundable" style="display:none;color: #000000;">Refundable : <span class="service_total_price">€0</span></p>
                            <p class="price_view_extra_not_refundable" style="color: #000000;">Not Refundable : €<span class="service_extra_total_price_not_refundable">0</span></p>
                            <p class="price_view_extra_late_charge" style="display:none;color: #000000;">Late Charge : €<span class="service_extra_total_price_late_charge">0</span></p>
                        </div> -->
                        <!-- <table class="service_total_price_view" >
                            
                            <tr class="price_view_not_refundable">
                                <td>Discount (5%)</td>
                                <td align="center">€<span class="service_extra_not_refundable_price">0</span></td>
                            </tr>
                            <tr class="price_view_late_charge" style="display:none;">
                                <td>Fees (5%)</td>
                                <td align="center">€<span class="service_extra_late_charge_price">0</span></td>
                            </tr>
                            <tr class="price_view_extra_refundable" style="display: none;"> 
                                <td>Refundable</td>
                                <td align="center"><span class="service_total_price">0</span></td>
                            </tr>
                            <tr class="price_view_extra_not_refundable">
                                <td>Not Refundable</td>
                                <td align="center">€<span class="service_extra_total_price_not_refundable">0</span></td>
                            </tr>
                            <tr class="price_view_extra_late_charge"  style="display:none;">
                                <td>Late Charge</td>
                                <td align="center">€<span class="service_extra_total_price_late_charge">0</span></td>
                            </tr>
                        </table> -->
                    </div>
                    <!-- /step-->

                </div>
                <!-- /middle-wizard -->
                <div id="bottom-wizard" class="d-flex justify-content-between">
                    <div class="service_total_price_view">Total - <span class="service_total_price">-</span></div>
                    <div>
                        <button type="button" name="backward" class="backward"><i class="icon icon-arrow-left-circle"></i> <?php _e('Back', 'arienzo_reservation_form'); ?></button>
                        <button type="button" name="forward"
                                class="forward"><?php _e('Next', 'arienzo_reservation_form'); ?></button>
                        <button type="submit" name="process"
                                class="submit submit<?php echo $iddd; ?>"><?php _e('Submit', 'arienzo_reservation_form'); ?></button>
                        <?php language_selector_flags(); ?>
                    </div>
                </div>
                <!-- /bottom-wizard -->
                <?php if(!empty($attr['service_id'])) { $mphb_price_quantity = get_post_meta( $attr['service_id'], 'mphb_price_quantity', true ); $date_child_price = get_post_meta( $attr['service_id'], 'mphb_child_price', true ); $date_price = get_post_meta( $attr['service_id'], 'mphb_price', true ); $price_group = get_post_meta($attr['service_id'], 'customdata_group', true);  //echo "<pre>"; print_r($price_group); echo "</pre>";die;  ?>
                    <input type="hidden" value='<?php echo json_encode($price_group); ?>' name="price_group">
                    <input type="hidden" value="<?php echo $date_price; ?>" name="date_price">
                    <input type="hidden" value="<?php echo $date_price; ?>" name="date_price2">
                    <input type="hidden" value='<?php echo js_array($blocked_dates2); ?>' name="blocked_dates2">
                    <input type="hidden" value="<?php echo $date_child_price; ?>" name="date_child_price">
                    <input type="hidden" value="<?php echo $date_child_price; ?>" name="date_child_price2">
                    <input type="hidden" value="<?php echo $mphb_price_quantity; ?>" name="mphb_price_quantity">
                    <input type="hidden" value="<?php echo $attr['service_id']; ?>" name="services[]">
                <?php } ?>
            </form>
            <ul class="custom_error_message"></ul>
        </div>
        <!-- /Wizard container -->
    </div>
    <style>
        .disable-item {
            background: #ececec;
            pointer-events: none;
            cursor: default;
            text-decoration: none;
            color: black;
        }
    </style>
    
    <script>
        var blocked_dates = <?php echo $blocked_dates; ?>;
        var blocked_dates2 = <?php echo js_array($blocked_dates2); ?>;
        var nowDate = new Date();
        var today = new Date(nowDate.getFullYear(), nowDate.getMonth(), nowDate.getDate(), 0, 0, 0, 0);
        var daysList = [];
        var price_group = {};
        var child_price_group = {};
        var price_group2 = {};
        var date_price = '<?php echo $date_price ?>';
        var date_child_price = "<?php echo $date_child_price ? $date_child_price : $date_price ?>";

        var date_price2 = '<?php echo $date_price ?>';
        var date_child_price2 = "<?php echo $date_child_price ? $date_child_price : $date_price ?>";
        var $mphb_price_quantity = "<?php echo isset($mphb_price_quantity) && $mphb_price_quantity ? $mphb_price_quantity : "once" ?>";
        
        price_group = {}
        price_group2 = JSON.parse('<?php echo json_encode($price_group) ?>');
        jQuery(document).ready(function () {
            $(".adult_price").text(date_price2);
            $(".child_price").text(date_child_price2);
            $(document).delegate(".qtyplus,.qtyminus","click",function (e) {
                $this = $(this);
                setTimeout(function(){
                    $this.parents(".qty-buttons").find(".qty").trigger("change"); 
                }, 100);
                
            });
            $(document).delegate("#people","change",function (e) {
                /*if($(this).val()){
                    val = $(this).val();
                }else{
                    val = 0;
                }
                date_price3 = $(this).parents(".wizard-form-main").find("input[name='date_price2']").val();
                mphb_price_quantity = $(this).parents(".wizard-form-main").find("input[name='mphb_price_quantity']").val();
                $(this).parents(".wizard-form-main").find(".adult_count").text(val);
                $(this).parents(".wizard-form-main").find(".adult_price").text(date_price3);
                if(val > 0){
                    if(mphb_price_quantity == "once"){
                        $(this).parents(".wizard-form-main").find(".price_view2").show();
                        $(this).parents(".wizard-form-main").find(".service_total_price").text(date_price3 * val);
                    }else{
                        $(this).parents(".wizard-form-main").find(".price_view").show();
                        $(this).parents(".wizard-form-main").find(".price_view_adult").show();
                        $(this).parents(".wizard-form-main").find(".service_total_price").text(date_price3 * val);
                    }
                }else{
                    if(mphb_price_quantity == "once"){
                        $(this).parents(".wizard-form-main").find(".price_view2").hide();
                        $(this).parents(".wizard-form-main").find(".service_total_price").text(0);    
                    }else{
                        if(!$(this).parents(".wizard-form-main").find("#child").val() || $(this).parents(".wizard-form-main").find("#child").val() <= 0){
                            $(this).parents(".wizard-form-main").find(".price_view").hide();
                        }
                        $(this).parents(".wizard-form-main").find(".price_view_adult").hide();
                        $(this).parents(".wizard-form-main").find(".service_total_price").text(0);    
                    }
                }*/
                calc_price($(this));
            });
            $(document).delegate("#child","change",function (e) {
                /*if($(this).val()){
                    val = $(this).val();
                }else{
                    val = 0;
                }
                date_child_price3 =  $(this).parents(".wizard-form-main").find("input[name='date_child_price2']").val();
                $(this).parents(".wizard-form-main").find(".child_count").text(val);
                $(this).parents(".wizard-form-main").find(".child_price").text(date_child_price3);
                if(val > 0){
                    $(this).parents(".wizard-form-main").find(".price_view").show();
                    $(this).parents(".wizard-form-main").find(".price_view_child").show();
                    $(this).parents(".wizard-form-main").find(".child_total_price").text(date_child_price3 * val);
                }else{
                    if(!$(this).parents(".wizard-form-main").find("#people").val() || $(this).parents(".wizard-form-main").find("#people").val() <= 0){
                        $(this).parents(".wizard-form-main").find(".price_view").hide();
                    }
                    $(this).parents(".wizard-form-main").find(".price_view_child").hide();
                    $(this).parents(".wizard-form-main").find(".child_total_price").text(0);
                }*/
                calc_price($(this));
            });
                
            if(price_group2){
                $.each(price_group2,function(i,j){
                    currentDate = new Date(j['startdate']);
                    end = new Date(j['enddate']);
                    while (currentDate <= end) {
                        currentDate2 = $.datepicker.formatDate('yy-mm-dd', currentDate)
                        if(new Date(currentDate2)  in price_group){

                        }else{
                            if(j['rate']){
                                price_group[currentDate2] = j['rate'];
                            }
                            if(j['child_rate']){
                                child_price_group[currentDate2] = j['child_rate'];
                            }
                        }
                        currentDate.setDate(currentDate.getDate() + 1);
                    }
                })
            }
            var $animSpeed = 200;

            $('#cleanAccordionMain > .accordion > dt:last-of-type').addClass('accordionLastDt');
            $('#cleanAccordionMain > .accordion > dd:last-of-type').addClass('accordionLastDd');
            $('#cleanAccordionMain > .accordion > dt:first-of-type').addClass('accordionFirstDt');

            $('#cleanAccordionMain > .accordion dd').hide();
            $('#cleanAccordionMain > .dropDown1 > dd:first-of-type').slideDown($animSpeed);
            $('#cleanAccordionMain > .dropDown1 > dt:first-child > .accordionContent').addClass('selected').parent().addClass('selected');
            $('#cleanAccordionMain > .accordion dt .accordionContent').click(function(){
                if($(this).closest('.singleTab').hasClass('hasContent')) {
                    if ($(this).closest(".accordionSlide ").hasClass('selected')) {
                        $(this).closest(".accordionSlide ").removeClass('selected').parent().removeClass('selected');
                        $(this).closest(".accordionSlide ").parent().next().slideUp($animSpeed);

                    } else {
                        $('#cleanAccordionMain > .accordion dt .accordionSlide').removeClass('selected').parent().removeClass('selected');
                        $(this).closest('.accordionSlide').addClass('selected').parent().addClass('selected');
                        $('#cleanAccordionMain > .accordion dd').slideUp($animSpeed);
                        $(this).closest(".accordionSlide ").parent().next().slideDown($animSpeed);
                    }
                }

                return false;
            });
            $(".phone_number<?php echo $iddd; ?>").intlTelInput({
                initialCountry: "auto",
                hiddenInput: "full_phone",
                geoIpLookup: function(callback) {
                    $.get('https://ipinfo.io', function() {}, "jsonp").always(function(resp) {
                        let countryCode = (resp && resp.country) ? resp.country : "";
                        callback(countryCode);
                    });
                },
                utilsScript: "<?php echo plugins_url('../assets/js/utils.js', __FILE__); ?>"
            });

            assignDatePicker(0);
            /*let maxField = 10;
            let addButton = $('.add_button');
            let wrapper = '.field_wrapper';

            var x = 1;
            $(addButton).click(function(){
                if(x < maxField){
                    let fieldHTML = '<div class="form-group field_wrapper">'+
                    '                            <input type="text" name="dates[' + x + ']" class="form-control required"'+
                    '                                   placeholder="<?php _e("When", "arienzo_reservation_form"); ?>" readonly>'+
                    '                            <i class="icon-hotel-calendar_3"></i>'+
                    '                            <a href="javascript:void(0);" class="remove_button"><img src="<?php echo plugins_url("../assets/img/minus.svg", __FILE__) ?>"/></a>'+
                    '                        </div>';
                    $(fieldHTML).insertAfter($(wrapper).last());
                    assignDatePicker(x)
                    x++;
                }
            });*/
            let maxField = 10

            var x = 1;
            $(".add_button").unbind("click");
            $('.add_button').click(function(){
                wrapper_parent = $(this).parent(".field_wrapper");
                count = wrapper_parent.data("count");
                
                if(count < maxField){
                    let fieldHTML = '<div class="form-group field_wrapper">'+
                        '                            <input type="text" name="dates[' + count + ']" class="form-control required"'+
                        '                                   placeholder="<?php _e("When", "arienzo_reservation_form"); ?>" readonly>'+
                        '                            <i class="icon-hotel-calendar_3"></i>'+
                        '                            <a href="javascript:void(0);" class="remove_button"><img src="<?php echo plugins_url("../assets/img/minus.svg", __FILE__) ?>"/></a>'+
                        '                        </div>';
                    $(fieldHTML).insertAfter(wrapper_parent);
                    assignDatePicker(count);
                    wrapper_parent.data("count",count+1)
                    x++;
                }
            });

            $(document).on('click', '.field_wrapper .remove_button', function(e){
                e.preventDefault();
                var name = $(this).closest('.form-group').find('input').attr("name");
                var val = $(this).closest('.form-group').find('input').val();

                $("#lunch_time .dd-options a").removeClass('disable-item');
                for (var i = 0; i < daysList.length; i++) {
                    if(daysList[i].date == val) {
                        delete daysList[i];
                    }
                }
                daysList = daysList.filter(function(e){return e});
                for (var j = 0; j < daysList.length; j++) {
                    var itemIndex = 0;
                    if(daysList[j].time == "12:00") {
                        itemIndex = 1
                    } else if(daysList[j].time == "13:00") {
                        itemIndex = 2
                    } else if(daysList[j].time == "13:15") {
                        itemIndex = 2
                    } else if(daysList[j].time == "14:30") {
                        itemIndex = 3
                    } else if(daysList[j].time == "15:30") {
                        itemIndex = 4
                    } else if(daysList[j].time == "11:59") {
                        itemIndex = 5
                    }
                    $("#lunch_time .dd-options").children().eq(itemIndex).find('a').addClass('disable-item');
                }
                $(this).parent('div').remove();
            });
            setTimeout(function(){
                $.each($("input[name^='dates']"),function(){
                    $this = $(this);
                    let date = $(this).val();

                    if (disabledDays[date] != undefined) {
                        for (var i = 0; i < disabledDays[date].length; i++) {
                            daysList.push(disabledDays[date][i])
                        }
                        for (var j = 0; j < daysList.length; j++) {
                            var itemIndex = "";
                            jQuery.each($this.parents(".wizard-step").find("#lunch_time .dd-options li"),function(i,jj){
                                if(daysList[j] && jQuery(this).find(".dd-option-value") && jQuery(this).find(".dd-option-value").val() == daysList[j].time){
                                    itemIndex = i;
                                }
                            })
                            /*if(daysList[j].time == "12:00") {
                                itemIndex = 1
                            } else if(daysList[j].time == "13:00") {
                                itemIndex = 2
                            } else if(daysList[j].time == "13:15") {
                                itemIndex = 2
                            } else if(daysList[j].time == "14:30") {
                                itemIndex = 3
                            } else if(daysList[j].time == "15:30") {
                                itemIndex = 4
                            } else if(daysList[j].time == "11:59") {
                                itemIndex = 5
                            }*/
                            if(itemIndex){
                                $this.parents(".wizard-step").find("#lunch_time .dd-options").children().eq(itemIndex).find('a').addClass('disable-item');
                                if(!$this.parents(".wizard-step").find("#lunch_time .dd-options").children().eq(itemIndex).find('a').find('.dd-option-text-na').length){
                                    $this.parents(".wizard-step").find("#lunch_time .dd-options").children().eq(itemIndex).find('a').find('.dd-option-text').after('<label class="dd-option-text-na"> - Not Available</label>');
                                }
                            }
                        }
                    }
                })
            },200)
        });

        function assignDatePicker(elementToAdd) {
            let name = 'input[name="dates[' + elementToAdd + ']"]';
           /* price_group = {}
            price_group2 = JSON.parse('<?php echo json_encode($price_group) ?>');

            if(price_group2){
                $.each(price_group2,function(i,j){
                    currentDate = new Date(j['startdate']);
                    end = new Date(j['enddate']);
                    while (currentDate <= end) {
                        currentDate2 = $.datepicker.formatDate('yy-mm-dd', currentDate)
                        if(new Date(currentDate2)  in price_group){

                        }else{
                            price_group[currentDate2] = j['rate'];
                        }
                        currentDate.setDate(currentDate.getDate() + 1);
                    }
                })
            }*/
            $.each($('input[name="dates['+elementToAdd+']"]'),function(i,j){
                $this = $(this);
                var price_group = {};
                var child_price_group = {};
                price_group2 = JSON.parse($this.parents(".wizard-form-main").find("input[name='price_group']").val());
                if(price_group2){
                    $.each(price_group2,function(i,j){
                        currentDate = new Date(j['startdate']);
                        end = new Date(j['enddate']);
                        while (currentDate <= end) {
                            currentDate2 = $.datepicker.formatDate('yy-mm-dd', currentDate)
                            if(new Date(currentDate2)  in price_group){
    
                            }else{
                                if(j['rate']){
                                    price_group[currentDate2] = j['rate'];
                                }
                                if(j['child_rate']){
                                    child_price_group[currentDate2] = j['child_rate'];
                                }
                            }
                            currentDate.setDate(currentDate.getDate() + 1);
                        }
                    })
                }
                if(typeof $(this).data("daterangepickerinit") !== undefined){
                    $(this).daterangepicker({
                        autoUpdateInput: false,
                        singleDatePicker: true,
                        "opens": "left",
                        "minDate": today,
                        locale: {
                            format: 'YYYY-MM-DD',
                            cancelLabel: 'Clear',
                            service_id:$this.parents(".wizard-form-main").find("input[name='services[]']").val(),
                            price_group:price_group,
                            child_price_group:child_price_group,
                            date_price:$this.parents(".wizard-form-main").find("input[name='date_price']").val(),
                            date_child_price:$this.parents(".wizard-form-main").find("input[name='date_child_price']").val(),
                            mphb_price_quantity:$this.parents(".wizard-form-main").find("input[name='mphb_price_quantity']").val(),
                            blocked_dates2:JSON.parse($this.parents(".wizard-form-main").find("input[name='blocked_dates2']").val()),
                        },
                        "isCustomDate" : function(date){
                            //$(this).attr('data-custom', "<?php echo $date_price ?>");
                            /*if(price_group.length){
                                if(date  in price_group){
                                    $(this).attr('data-custom', price_group[date]);
                                }else{
                                    $(this).attr('data-custom', "<?php echo $date_price ?>"); // Add custom data here
                                }
                            }else{
                                $.each($(".daterangepicker tr").attr('data-custom', "<?php echo $date_price ?>"); // Add custom data here
                            }*/
                        },
                        "isInvalidDate" : function(date,$this){
                            for(var ii = 0; ii < blocked_dates.length; ii++){
                                if (date.format('YYYY-MM-DD') == blocked_dates[ii]){
                                    return true;
                                }
                            }
                            
                            if($this.locale.blocked_dates2){
                                for(var ii = 0; ii < $this.locale.blocked_dates2.length; ii++){
                                    if (date.format('YYYY-MM-DD') == $this.locale.blocked_dates2[ii]){
                                        return true;
                                    }
                                }
                            }
                            //console.log(date.format('YYYY-MM-DD'))
                            <?php //if(isset($date_price) && $date_price) { ?>
                                /*if(price_group.length){
                                    if(date.format('YYYY-MM-DD')  in price_group){
                                        $(this).attr('data-custom', price_group[date.format('YYYY-MM-DD')]);
                                    }else{
                                        $(this).attr('data-custom', "<?php echo $date_price ?>"); // Add custom data here
                                    }
                                }else{
                                    $("this").attr('data-custom', "<?php echo $date_price ?>"); // Add custom data here
                                }*/
                                //$(this).attr('data-custom', "<?php echo $date_price ?>"); // Add custom data here
                                //addCustomInformation();
                            <?php //} ?>
                        },
                    });
                }
                $(this).data("daterangepickerinit","set")
            });
            function addCustomInformation() {
              setTimeout(function() {
                /*$(".daterangepicker tr").filter(function() {
                  var date = $(this).text();
                  return /\d/.test(date);
                }).find("td").attr('data-custom', "<?php echo $date_price ?>");*/
                /*$.each($(".daterangepicker tr").filter(function() {
                  var date = $(this).text();
                  return /\d/.test(date);
                }).find("td"),function(i,j){
                    var date = $(this).text();
                    //console.log(price_group)
                    if(price_group.length){
                        if(date  in price_group){
                            $(this).attr('data-custom', price_group[date]);
                        }else{
                            $(this).attr('data-custom', "<?php echo $date_price ?>"); // Add custom data here
                        }
                    }else{
                        $(this).attr('data-custom', "<?php echo $date_price ?>"); // Add custom data here
                    }
                })*/
              }, 0)
            }
            $(name).on('apply.daterangepicker', function (ev, picker) {
                console.log(picker)
                if(typeof picker === "undefined"){
                    return;
                }
                let startDate = picker.startDate.format('YYYY-MM-DD');
                $(this).val(startDate);
                $this = $(this);
                $this.parents(".wizard-step").find("#lunch_time .dd-options a").removeClass('disable-item');
                //console.log(123)
                $("#lunch_time .dd-options a .dd-option-text-na").remove();
                $("#lunch_time .dd-options a").removeClass('disable-item')
                
                $("input[name^='dates']").each(function(index, item) {
                    daysList = [];
                    let date = $(item).val();
                    //console.log($(item).parents(".wizard-step").find("#lunch_time .dd-options a").length)
                    $(item).parents(".wizard-step").find(".dd-options a").removeClass('disable-item');
                    //console.log($(item).parents(".wizard-step").find(".dd-options a.disable-item").length)
                    $(item).parents(".wizard-step").find("#lunch_time .dd-options a .dd-option-text-na").remove();

                    if (disabledDays[date] != undefined) {
                        //console.log(date)
                        //console.log(disabledDays)
                        for (var i = 0; i < disabledDays[date].length; i++) {
                            daysList.push(disabledDays[date][i])
                        }
                        for (var j = 0; j < daysList.length; j++) {
                            var itemIndex = "";
                            jQuery.each($(item).parents(".wizard-step").find("#lunch_time .dd-options li"),function(i,jj){
                                if(daysList[j] && $(this).find(".dd-option-value") && $(this).find(".dd-option-value").val() == daysList[j].time){
                                    itemIndex = i;
                                }
                            })
                            if(itemIndex){
                                $(item).parents(".wizard-step").find("#lunch_time .dd-options").children().eq(itemIndex).find('a').addClass('disable-item');
                                if(!$(item).parents(".wizard-step").find("#lunch_time .dd-options").children().eq(itemIndex).find('a').find('.dd-option-text-na').length){
                                    $(item).parents(".wizard-step").find("#lunch_time .dd-options").children().eq(itemIndex).find('a').find('.dd-option-text').after('<label class="dd-option-text-na"> - Not Available</label>');
                                }
                                $(item).parents(".wizard-step").find("input[name='lunch_time']").val("");
                                
                                $(item).parents(".wizard-step").find("#lunch_time .dd-select .dd-selected-text").text($(item).parents(".wizard-step").find("#lunch_time .dd-options li").eq(0).find(".dd-option-text").text());

                                if(!$(item).parents(".wizard-step").find("#lunch_time .dd-options").children().eq(itemIndex).find('a').find('.dd-option-text-na').length){
                                }
                            }
                        }
                    }
                });
                price_group = picker.locale.price_group;
                child_price_group = picker.locale.child_price_group;
                date_price = picker.locale.date_price;
                date_child_price = picker.locale.date_child_price;
                
                mphb_price_quantity = picker.locale.mphb_price_quantity;
                
                if(mphb_price_quantity == "once"){
                    $(".price_view_2").show();
                }
                if (typeof price_group !== "undefined") {
                    if(startDate  in price_group){
                        date_price2 = price_group[startDate];
                        date_child_price2 = child_price_group[startDate];
                    }else{
                        date_price2 = date_price;
                        date_child_price2 = date_child_price;
                    }
                }else{
                    date_price2 = date_price;
                    date_child_price2 = date_child_price;
                }
                
                $(this).parents(".wizard-form-main").find("input[name='date_price2']").val(date_price2);
                $(this).parents(".wizard-form-main").find("input[name='date_child_price2']").val(date_child_price2);
                if(mphb_price_quantity == "once"){
                    $(this).parents(".wizard-form-main").find(".accommodation_total_price").text(date_price2);
                }
                
                $(".qty").trigger("change"); 
            });
            $(name).on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
            });
            $(name).bind('change', function () {

            });
            $(name).on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
            });
        }
        
        jQuery(document).ready(function () {
            jQuery(function ($) {
                "use strict";
                //$('form#wrapped').attr('action', 'booking_hotel.php');    window.ajaxEnabled = true;
                $(".wizard_container_<?php echo $iddd; ?>").wizard({
                    stepsWrapper: ".form_<?php echo $iddd; ?>",
                    submit: ".submit<?php echo $iddd; ?>",
                    beforeSelect: function (event, state) {
                        if ($('input#website').val().length != 0) {
                            return false;
                        }
                        if (!state.isMovingForward)
                            return true;
                        var inputs = $(this).wizard('state').step.find(':input');
                        return !inputs.length || !!inputs.valid();
                    }
                }).validate({
                    errorPlacement: function (error, element) {
                        if (element.is(':radio') || element.is(':checkbox')) {
                            error.insertBefore(element.next());
                        } else {
                            error.insertAfter(element);
                        }
                    },
                    submitHandler: function(form) {         $('.preloader-section-<?php echo $iddd; ?>').show();                     if(window.ajaxEnabled === false) {              return;         }           window.ajaxEnabled = false;         
                        let custom_form = $(form).find('form.form_<?php echo $iddd; ?>');
                        let formData = $(custom_form).serialize();
                        let data = {
                            data: formData,
                            action: 'arf_booking_ajax_request'
                        };
            
                        if ( custom_form.data('requestRunning') ) {
                            return;
                        }
                        var  list = $(form).find(".custom_error_message");
                        list.empty();
            
                        $.ajax({
                            url: arf_ajax_action.ajax_url,
                            type: form.method,
                            data: data,
                            method: "POST",
                            dataType: 'JSON',
                            success: function(response) {                   $('.preloader-section-<?php echo $iddd; ?>').hide();                 window.ajaxEnabled = true;
                                if(response.success) {
                                    if(response.requestParams){
                                        var form = document.createElement("form");
                                        form.setAttribute("method", "POST");
                                        form.setAttribute("action", response.requestUrl);
                                        for(var key in response.requestParams) {
                                            if(response.requestParams.hasOwnProperty(key)) {
                                                var hiddenField = document.createElement("input");
                                                hiddenField.setAttribute("type", "hidden");
                                                hiddenField.setAttribute("name", key);
                                                hiddenField.setAttribute("value", response.requestParams[key]);
                                                form.appendChild(hiddenField);
                                             }
                                        }
                                        document.body.appendChild(form);
                                        form.submit();
                                    }else{
                                        window.location.href = response.url;
                                    }
                                }
                                else {
                                    let messages = response.messages;
            
                                    for (let key in messages) {
                                        if (!messages.hasOwnProperty(key)) continue;
                                        let text = messages[key];
                                        list.append('<li>' + text + '</li>');
                                    }
                                }
                            },
                            complete: function() {
                                custom_form.data('requestRunning', false);
                            }
                        });
                    }
                });
                //  progress bar
                $(".progressbar<?php echo $iddd; ?>").progressbar();
                $(".wizard_container_<?php echo $iddd; ?>").wizard({
                    beforeBackward: function (event, state) {
                        $(".wizard_container_<?php echo $iddd; ?> .custom_error_message").text("");
                    },
                    beforeForward: function (event, state) {
                        var $return = false;


                        var people = $(".wizard_container_<?php echo $iddd; ?> input[type='number'][name='people']").val()
                        var child = $(".wizard_container_<?php echo $iddd; ?> input[type='number'][name='child']").val()
                        /*console.log(people)
                        console.log($(".wizard_container_<?php echo $iddd; ?> .wizard-form-main").data("action2"))*/
                        
                        if(people > 10 && $(".wizard_container_<?php echo $iddd; ?> .wizard-form-main").data("action2")){
                            location = $(".wizard_container_<?php echo $iddd; ?> .wizard-form-main").data("action2");
                            return false;
                        }
                        if(child > 10 && $(".wizard_container_<?php echo $iddd; ?> .wizard-form-main").data("action2")){
                            location = $(".wizard_container_<?php echo $iddd; ?> .wizard-form-main").data("action2");
                            return false;
                        }


                        $.each($(".wizard_container_<?php echo $iddd; ?> .product_main_div"),function(i,j){
                            if($(this).find("input").val() > 0){
                                $return = true;
                            }
                        });
                        if(!$return){
                            $(".wizard_container_<?php echo $iddd; ?> .error-text").show();
                            return $return;
                        }else{
                            var $return = true;
                            $(".wizard_container_<?php echo $iddd; ?> .error-text").hide();
                            $this = $(this);
                            var products<?php echo $iddd; ?> = [];
                            var people = $(".wizard_container_<?php echo $iddd; ?> input[type='number'][name='people']").val()
                            $.each($(".wizard_container_<?php echo $iddd; ?> .product_main_div"),function(){
                                var inputt = $(this).find("input");
                                var max = $(this).data("max");
                                var stock = $(this).data("stock");
                                if(inputt.val() > 0){
                                    var idd = (inputt.attr("id")).replace("product_","");
                                    val = 1 ;
                                    console.log(max +" - "+people)
                                    if(max && max < people){
                                        val =  Math.ceil(people/max)
                                    }
                                    if(val > stock){
                                        $return = false;
                                        $(".wizard_container_<?php echo $iddd; ?> .error-outofstock-text").show();
                                        return false;
                                    }
                                }
                            });
                            if($return){
                                $(".wizard_container_<?php echo $iddd; ?> .error-outofstock-text").hide();
                            }
                            return $return;
                            /*$.ajax({
                                url:'<?php echo admin_url('admin-ajax.php') ?>',
                                type:'POST',
                                dataType:'json',
                                data:{
                                    products:products<?php echo $iddd; ?>,
                                    "action":"check_product_qty",
                                    people:$(".wizard_container_<?php echo $iddd; ?> input[name='people']").val()
                                },
                                beforeSend:function(){
                                    $this.button("loading");
                                },
                                complete:function(){
                                    $this.button("reset");
                                },
                                success:function(json){
                                    if(json['success'] == "0"){
                                        $(".wizard_container_<?php echo $iddd; ?> .error-outofstock-text").show();
                                        return false;
                                    }
                                    $(".wizard_container_<?php echo $iddd; ?> .error-outofstock-text").hide();
                                    return true;
                                },
                            })*/
                        }


                    },
                    afterSelect: function (event, state) {
                        /*if(state.stepIndex === 1) {
                            $('.forward').text('Skip')
                        }*/
                        $(".progressbar<?php echo $iddd; ?>").progressbar("value", state.percentComplete);
                        $("#location").text("(" + state.stepsComplete + "/" + state.stepsPossible + ")");
                    }
                });
            });
        });
    </script>
    <?php $output = ob_get_contents();
    ob_end_clean();
    return $output;
}

add_shortcode('arf_form_search', 'arf_shortcode_search');
add_shortcode('arf_form_search2', 'arf_shortcode_search2');

function arf_qr_code_func($atts)
{
    $a = shortcode_atts(array(
        'order' => '123',
        'title' => get_bloginfo('name'),
        'size' => '300x300'
    ), $atts);
    return '<img src="https://chart.googleapis.com/chart?chs=' . $a['size'] . '&cht=qr&chl=' . $a['order'] . '&choe=UTF-8" title="' . $a['title'] . '" />';
}

add_shortcode('arf_qr_code', 'arf_qr_code_func');

function arf_qr_code_tracking_func($atts)
{
    $a = shortcode_atts(array(
        'booking_id' => '',
        'title' => get_bloginfo('name'),
        'size' => '300x300'
    ), $atts);

    if (empty($a['booking_id'])) {
        return "";
    }
    $hash = encrypt_decrypt($a['booking_id'], 'encrypt');
    $url = home_url( '/asdasd/?tracking=' . $hash );
    echo $url;
    return '<img src="https://chart.googleapis.com/chart?chs=' . $a['size'] . '&cht=qr&chl=' . $url . '&choe=UTF-8" title="' . $a['title'] . '" />';
}

add_shortcode('arf_qr_code_tracking', 'arf_qr_code_tracking_func');

/**
 * @param $string
 * @param string $action
 * @return false|string
 */
function encrypt_decrypt($string, $action = 'encrypt')
{
    $encrypt_method = "AES-256-CBC";
    $secret_key = 'AA74CDCC2BBRT935136HH7B63C27'; // user define private key
    $secret_iv = '5fgf5HJ5g27'; // user define secret key
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16); // sha256 is hash_hmac_algo
    if ($action == 'encrypt') {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    } else if ($action == 'decrypt') {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }
    return $output;
}

function arf_get_rate_type_id()
{
    $rate_obj = get_posts('post_type=mphb_rate&numberposts=1&orderby=rand');
    if (!empty($rate_obj)) {
        return $rate_obj[0]->ID;
    }
    return 0;
}

function arf_get_room_type_id()
{
    $room_obj = get_posts('post_type=mphb_room&numberposts=1&orderby=rand');
    if (!empty($room_obj)) {
        return $room_obj[0]->ID;
    }
    return 0;
}

function language_selector_flags(){
    if(function_exists('icl_get_languages')) {
        $languages = icl_get_languages('skip_missing=0&orderby=code'); /* retrieve active languages */
        if(!empty($languages)){
            foreach($languages as $l){
                if($l['active'])
                    continue;
                if(!$l['active']) echo '<a class="notactive_lang" href="'.$l['url'].'">'; // add link only to not active languages
                echo '<img src="'.$l['country_flag_url'].'" height="12" alt="'.$l['language_code'].'" width="18" />';
                if(!$l['active']) echo '</a>';
            }
        }
    }
}


add_shortcode('arf_shortcode_location_map', 'arf_shortcode_location_map');
function arf_shortcode_location_map() {
    $date = !empty($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : date('Y-m-d');
    $booking_id = !empty($_GET['booking_id']) ? sanitize_text_field($_GET['booking_id']) : "";

    if (!validateDateAttr($date,'Y-m-d')) {
        $date = date('Y-m-d');
    }
    if (!is_numeric($booking_id)) {
        $booking_id = "";
    }
    wp_enqueue_script( 'jquery' );
    wp_enqueue_style('jquery-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css');

    wp_enqueue_script( 'jquery-ui-dialog' );


    $args = array(
        'posts_per_page' => -1,
        'post_type' => 'mphb_booking',
        'post_status' => array('confirmed','paid_not_refundable','paid_refundable','last_minute','pending_late_charge','paid_late_charge'),
        'fields' => 'ids',
        'meta_query' => array(
            array(
                'key' => 'mphb_check_in_date',
                'value' => $date
            ),
        ),
    );

    if ($booking_id) {
        $args['p'] = $booking_id;
    }

    $ids = get_posts($args);
    $bookings = [];
    foreach ($ids as $id) {
        $booking = MPHB()->getBookingRepository()->findById($id, true);
        $bookings[] = array(
            'id' => $id,
            'mphb_check_in_date' => $booking->getCheckInDate()->format('Y-m-d'),
            'lunch_time' => get_post_meta($id, 'lunch_time', true),
            'beach_arrival_time' => get_post_meta($id, 'beach_arrival_time', true),
            'coordinates' => get_post_meta($id, 'coordinates', true),
            'full_name' => $booking->getCustomer()->getFirstName() . ' ' . $booking->getCustomer()->getLastName(),
            'guests' => get_guest_info_fp($booking),
        );
    }
    MPHB()->getAdminScriptManager()->enqueue();
    ?>
    <style>
        .location_builder button {
            border-radius: 2px;
            color: #FFFFFF;
            font-family: system-ui;
            font-size: 16px;
            font-weight: 500;
            letter-spacing: 1.3px;
            line-height: 18px;
            text-align: center;
            text-transform: uppercase;
            border: none;
            padding: 10px;
        }

        .location_builder .add_new_area {
            background-color: green;
        }

        .location_builder .identify_area {
            background-color: #4D7EE8;
        }

        .location_builder .delete_area {
            background-color: #FA4241;
        }

        .location_builder .save_location_builder_data {
            color: #FFFFFF;
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 1px;
            line-height: 18px;
            text-align: center;
            border-radius: 3px;
            background-color: #0EC06E;
            border: none;
            padding: 10px;
            text-transform: uppercase;
            margin: 0;
        }

        .location_builder table {
            box-sizing: border-box;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 4px;
            /*background-color: #1C1F20;*/
            /*box-shadow: 0 0 16px 0 rgba(0, 0, 0, 0.65);*/
            margin-top: 13px;
        }

        .location_builder table th, .location_builder table td {
            height: 18px;
            color: #000;
            font-family: "Source Sans Pro";
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 0.16px;
            line-height: 18px;
            border: 1px solid #ddd;
            padding: 15px 15px 15px 10px;
            text-align: left;
        }
        
        

        .canvas-container {
            text-align: center;
            margin: auto;
        }

        .ui-widget-overlay {
            display: none;
        }

        .location-builder-section {
            width: 100%;
            display: block;
            max-width: 100% !important;
            background: #eee;
        }

        .location-builder-section .form-section form {
            text-align: center;
        }
        
        .ui-dialog .ui-dialog-titlebar-close {
            margin: -10px 15px 0 0;
            font-size: 0 !important;
        }
        
        @media only screen and (max-width: 1000px) {
            .location-builder-section .canvas-section {
                padding: 10px;
                overflow: scroll;
                height: 400px;
            }
            
            .location-builder-section .canvas-section::-webkit-scrollbar,
            .location_builder::-webkit-scrollbar
            {
              width: 10px;
            }

            /* Track */
            .location-builder-section .canvas-section::-webkit-scrollbar-track ,
            .location_builder::-webkit-scrollbar-track 
            {
              
              border-radius: 10px;
            }

            /* Handle */
            .location-builder-section .canvas-section::-webkit-scrollbar-thumb, 
            .location_builder::-webkit-scrollbar-thumb 
            {
              background: blue;
              border-radius: 10px;
            }

            /* Handle on hover */
            .location-builder-section .canvas-section::-webkit-scrollbar-thumb:hover, 
            .location_builder::-webkit-scrollbar-thumb:hover 
            {
              background: #555;
            }
            
            .location_builder {
                width: 100%;
                overflow: scroll;
            }
            .location_builder table {
                width: max-content;
            }
        }
    </style>
    <div class="location-builder-section">
        <div class="form-section" >
            <form method="get" class="wp-filter">
                <input style="margin: 15px;" type="text"
                       class="mphb-datepick mphb-custom-period-from mphb-date-input-width" id="arf_location_map_date"
                       name="date_from" placeholder="From" value="<?php echo esc_attr($date); ?>">
                <input style="margin: 15px;" type="text" name="booking_id" placeholder="Booking Id" value="<?php echo esc_attr($booking_id); ?>" >
                <input type="submit" value="Submit" style="vertical-align: unset;">
            </form>
        </div>
        <div class="canvas-section">
            <canvas id="canvas" style="width: 100%"></canvas>
            <div id="arf_map_dialog" title="Booking Info">
                <p></p>         
            </div>
        </div>
        <div class="location_builder">
            <table>
                <thead>
                <tr>
                    <th><?php _e("Item", "arienzo_reservation_form") ?>:</th>
                    <th><?php _e("Customer", "arienzo_reservation_form") ?>:</th>
                    <th><?php _e("Number of people", "arienzo_reservation_form") ?>:</th>
                    <th><?php _e("Beach time arrival", "arienzo_reservation_form") ?>:</th>
                    <th><?php _e("Lunch time", "arienzo_reservation_form") ?>:</th>
                    <th><?php _e("Action", "arienzo_reservation_form") ?>:</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (!empty($bookings)) {
                    foreach ($bookings as $booking) {
                        $key = array_search($booking['id'], array_column($booking, 'id'));
                        $show = ($key > -1);
                        ?>
                        <tr>
                            <td>Booking #<?php echo $booking['id']; ?></td>
                            <td><?php echo $booking['full_name']; ?></td>
                            <td><?php echo $booking['guests']; ?></td>
                            <td><?php echo $booking['beach_arrival_time']; ?></td>
                            <td><?php echo $booking['lunch_time']; ?></td>
                            <td>
                                <button class="identify_area"
                                        onclick="handleIdentifyArea(<?php echo $booking['id']; ?>, this)"
                                        style="<?php $show ? "display:inline-block" : "display:none" ?>"><?php _e("Identify", "arienzo_reservation_form") ?>
                                </button>
                            </td>
                        </tr>
                    <?php }
                } else { ?>
                    <tr><td colspan="6" style="text-align: center"><?php _e("Result not found!", "arienzo_reservation_form") ?></td></tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/3.1.0/fabric.min.js"></script>
    <script>
        var $ = jQuery.noConflict();
        let orgWidth, orgHeight;
        let radius = 5;
        let canvasScale = 1;
        let animatedInterval = false;
        const codeColor = "red";
        var bookings = window.bookings = <?php echo !empty($bookings) ? json_encode($bookings) : "[]" ?>;
        const maps = [];
        let canvas = window.canvas = new fabric.Canvas('canvas', {
            hoverCursor: 'pointer',
            selection: false,
            selectionBorderColor: 'green',
            backgroundColor: null
        });
        let width = $('.location_builder_content').width();

        let canvasOriginalWidth = "1200";
        let canvasOriginalHeight = "800";

        canvas.setWidth(canvasOriginalWidth);
        canvas.setHeight(canvasOriginalHeight);

        let imageUrl = "<?php echo plugins_url('../assets/img/map.png', __FILE__) ?>";

        canvas.setBackgroundImage(imageUrl, canvas.renderAll.bind(canvas), {
            backgroundImageOpacity: 1,
            backgroundImageStretch: false,
            scaleX: 1,
            scaleY: 1,
        });

        canvas.on('mouse:up', function (e) {
            if (!e.target) {
                return ;
            }

            const bookings = window.bookings;
            const dataIndex = bookings.findIndex(obj => {
                return parseInt(obj.id) === parseInt(e.target.mapId);
            })
            if (dataIndex == -1) {
                return ;
            }
            const booking = bookings[dataIndex];
            const info = `Booking #${booking.id} <br> Customer: ${booking.full_name}<br>People:  ${booking.guests} <br>Beach time arrival: ${booking.beach_arrival_time}<br>Lunch Time: ${booking.lunch_time} `;
            let item = jQuery('#arf_map_dialog')
            item.find('p').html(info)
            item.dialog('open');
        })

        canvas.renderAll();

        if (bookings.length > 0) {
            addCoordinatesToCanvas(bookings);
        }

        function addCoordinatesToCanvas(data) {
            window.canvas.remove(...window.canvas.getObjects());
            if (data.length > 0) {
                for (let i = 0; i < data.length; i++) {
                    const info = `Booking #${data[i].id}`;
                    if (!data[i].coordinates) {
                        addRect(data[i].id, info);
                    } else {
                        const item = data[i].coordinates;

                        const left = parseInt(item.xCenter);
                        const top = parseInt(item.yCenter);


                        const width = 30;
                        const height = 30;
                        const mapId = data[i].id;
                        let rect = new fabric.Rect({
                            originX: 'center',
                            originY: 'center',
                            width: width,
                            height: height,
                            selectable: false,
                            angle: 0,
                            fill: "rgba(51, 51, 51, 0.7)",
                        });

                        let circle = new fabric.Circle({
                            originX: 'center',
                            originY: 'center',
                            stroke: codeColor,
                            strokeWidth: 1,
                            radius: radius,
                            //fill: 'rgba(0,0,0,0)',
                            fill: codeColor,
                        });

                        let text = new fabric.IText(info, {
                            text: info,
                            fill: "black",
                            //height: 30,
                            width: 50,
                            top: 35,
                            originX: 'center',
                            originY: 'center',
                            fontSize: 16,
                            selectable: false,

                        });

                        let group = new fabric.Group([rect, circle, text], {
                            left: left,
                            top: top,
                            originX: 'left',
                            originY: 'top',
                            // width: 30,
                            // height: 30,
                            fill: 'rgba(0,0,0,0)',
                            transparentCorners: false,
                            lockUniScaling: true,
                            hasRotatingPoint: false,
                            selectable: false
                        });
                        window.canvas.add(group);
                        group.toObject = (function (toObject) {
                            return function () {
                                return fabric.util.object.extend(toObject.call(this), {
                                    mapName: mapName,
                                    mapId: parseInt(mapId)
                                });
                            };
                        })(group.toObject);

                        group.mapId = parseInt(mapId);
                    }

                }
                window.canvas.renderAll();
            }
        }

        function handleSaveButton(event) {
            event.preventDefault;
            updateMapCoordinates(window.bookings);
            return;
        }

        function updateMapCoordinates(data) {
            let postData = {
                data: data,
                action: "arf_map_locations"
            }

            $.ajax({
                type: "POST",
                data: postData,
                dataType: "json",
                url: ajaxurl,

                success: function (response) {
                    location.reload();
                }
                //This fires when the ajax 'comes back' and it isn't valid json
            }).fail(function (data) {
            });
        }

        function handleIdentifyArea(id, event) {
            event.preventDefault;
            event = event || window.event;

            let obj = window.canvas._objects;
            for (i = 0; i < obj.length; i++) {
                obj[i].item(1).set("radius", radius);
            }
            window.canvas.renderAll();
            const objIndex = obj.find((item) => {
                return parseInt(item.mapId) === parseInt(id);
            });
            if (objIndex === undefined) return;
            let newRadius = 5;
            if (animatedInterval) {
                clearInterval(animatedInterval);
            }

            var flashCount = 0;
            animatedInterval = setInterval(function () {
                objIndex.item(1).set("radius", newRadius);

                if (newRadius === 20) {
                    newRadius = 5;
                    flashCount++;
                } else {
                    newRadius++;
                }

                if (flashCount === 5) {
                    clearInterval(animatedInterval);
                    flashCount = 0;
                    objIndex.item(1).set("radius", radius);
                }
                window.canvas.renderAll();
            }, 30);
        }

        function addRect(mapId, mapName) {
            let rect = new fabric.Rect({
                originX: 'center',
                originY: 'center',
                width: 30,
                height: 30,
                angle: 0,
                selectable: false,
                fill: "rgba(51, 51, 51, 0.7)",
            });

            let circle = new fabric.Circle({
                originX: 'center',
                originY: 'center',
                stroke: codeColor,
                strokeWidth: 1,
                radius: radius,
                //fill: 'rgba(0,0,0,0)',
                fill: codeColor,
            });

            let text = new fabric.IText(mapName, {
                text: mapName,
                fill: "black",
                // height: 30,
                width: 50,
                top: 35,
                originX: 'center',
                originY: 'center',
                fontSize: 16,
            });

            let group = new fabric.Group([rect, circle, text], {
                left: 100,
                top: 100,
                originX: 'left',
                originY: 'top',
                // width: 30,
                // height: 30,
                fill: 'rgba(0,0,0,0)',
                transparentCorners: false,
                lockUniScaling: true,
                hasRotatingPoint: false,
                selectable: false
            });
            window.canvas.add(group);
            const dataIndex = window.bookings.findIndex(function (x) {
                return x.id == parseInt(mapId)
            });
            if (dataIndex != -1) {
                let data = {
                    "xCenter": 100,
                    "yCenter": 100,
                };
                window.bookings[dataIndex].coordinates = data;
            }

            group.toObject = (function (toObject) {
                return function () {
                    return fabric.util.object.extend(toObject.call(this), {
                        mapName: this.mapName,
                        mapId: this.mapId
                    });
                };
            })(group.toObject);
            group.mapId = mapId;

            window.canvas.renderAll();
        }

        function getDataById(id, data) {
            const dataIndex = data.findIndex(x => x.id === parseInt(id));
            if (dataIndex < 0) return false;
            return data[dataIndex];toolTip
        }

        jQuery(function($) {
            var $info = $("#arf_map_dialog");
            $info.dialog({
                'dialogClass'   : 'wp-dialog',
                'modal'         : true,
                'autoOpen'      : false,
                'closeOnEscape' : true,
                'buttons'       : {
                    "Close": function() {
                        $(this).dialog('close');
                    }
                }
            });
        });

        jQuery(document).ready(function () {
            jQuery("#arf_location_map_date").datepick({
                dateFormat: 'yyyy-mm-dd',
            })
        });

        (function($) {
            $.fn.invisible = function() {
                return this.each(function() {
                    $(this).css("visibility", "hidden");
                });
            };
            $.fn.visible = function() {
                return this.each(function() {
                    $(this).css("visibility", "visible");
                });
            };
        }(jQuery));

    </script>
    <?php
}

/**
 * @param $booking
 * @return string
 */
function get_guest_info_fp($booking)
{
    $reservedRooms = $booking->getReservedRooms();
    $html = "";
    if (!empty($reservedRooms) && !$booking->isImported()) {
        $adultsTotal = 0;
        $childrenTotal = 0;
        foreach ($reservedRooms as $reservedRoom) {
            $adultsTotal += $reservedRoom->getAdults();
            $childrenTotal += $reservedRoom->getChildren();
        }

        $html .= 'Adults: ';
        $html .= $adultsTotal;
        if ($childrenTotal > 0) {
            $html .= '<br/>';
            $html .= 'Children: ';
            $html .= $childrenTotal;
        }
    }
    return $html;
}

/**
 * @param $date
 * @param string $format
 * @return bool
 */
function validateDateAttr($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

add_shortcode('arf_form_hotel','arf_form_hotel_func');
function arf_form_hotel_func($atts) {
    wp_enqueue_style('arf_google_fonts');
    wp_enqueue_style('arf_bootstrap_css');
    wp_enqueue_style('arf_style_css');
    wp_enqueue_style('arf_vendors_css');
    wp_enqueue_style('arf_intTelInput_css');
    wp_enqueue_script('modernizr_js');
    wp_deregister_script('jquery');
    wp_enqueue_script('arf_jquery');
    wp_enqueue_script('arf_common_scripts_js');
    wp_enqueue_script('arf_velocity_js');
    wp_enqueue_script('arf_script_js');
    wp_enqueue_script('arf_booking_form_hotel');
    wp_enqueue_script('arf_intTelInput_js');
    wp_localize_script('arf_script_js', 'arf_ajax_action', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ajax-nonce'),
        'pluginsUrl' => plugins_url('arienzo-reservation-form'),
    ));

    global $sitepress;
    $current_lang = $sitepress->get_current_language(); //save current language
    $sitepress->switch_lang($current_lang); //restore previous language
    $services = get_posts([
        'numberposts'       => -1,
        'post_type'     => 'mphb_room_service',
        'post_status'   => 'publish',
        'suppress_filters' => 0
    ]);
    $blocked_dates = js_array(getBookingRules());
    $disabledDays = get_option('arf_lunch_days');
    ob_start(); ?>
    <script>
        var disabledDays = <?php echo json_encode($disabledDays );?>;
        disabledDays = disabledDays.reduce(function (r, a) {
            r[a.date] = r[a.date] || [];
            r[a.date].push(a);
            return r;
        }, Object.create(null));
    </script>
    <div class="content-right" id="start">
        <div id="wizard_container">
            <div id="top-wizard">
                <div id="progressbar"></div>
            </div>
            <!-- /top-wizard -->
            <form id="wrapped" method="POST">
                <input id="website" name="website" type="text" value="">
                <?php wp_nonce_field('arf_form_action'); ?>
                <div id="middle-wizard">
                    <div class="step">
                        <h3 class="main_question">
                            <strong>1/3</strong><?php _e('Enter your Booking details', 'arienzo_reservation_form'); ?>
                        </h3>
                        <div class="form-group field_wrapper">
                            <input type="text" name="dates[0]" class="form-control required"
                                   placeholder="<?php _e('When', 'arienzo_reservation_form'); ?>" readonly>
                            <i class="icon-hotel-calendar_3"></i>
                            <!-- <a href="javascript:void(0);" class="add_button" title="Add field"><img src="<?php //echo plugins_url("../assets/img/plus.svg", __FILE__) ?>"/></a> -->
                        </div>

                        <div class="form-group">
                            <div class="styled-select clearfix">
                                <select class="required ddslick" name="beach_arrival_time" id="beach_arrival_time">
                                    <option value=""
                                            data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                        <?php _e('Beach arrival time', 'arienzo_reservation_form'); ?>
                                    </option>
                                    <option value="10:00"
                                            data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                        10:00
                                    </option>
                                    <option value="10:30"
                                            data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                        10:30
                                    </option>
                                    <option value="11:00"
                                            data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                        11:00
                                    </option>
                                    <option value="11:30"
                                            data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                        11:30
                                    </option>
                                    <option value="12:00"
                                            data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                        12:00
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="styled-select clearfix">
                                <select class="ddslick" name="lunch_time" id="lunch_time">
                                    <option value=""
                                            data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/restaurant-plate.svg', __FILE__) ?>">
                                        <?php _e('Lunch time', 'arienzo_reservation_form'); ?>
                                    </option>
                                    <option value="12:00"
                                            data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/restaurant-plate.svg', __FILE__) ?>">
                                        12:00
                                    </option>
                                    <option value="13:00"
                                            data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/restaurant-plate.svg', __FILE__) ?>">
                                        13:00
                                    </option>
                                    <option value="13:15"
                                            data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/restaurant-plate.svg', __FILE__) ?>">
                                        13:15
                                    </option>
                                    <option value="14:30"
                                            data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/restaurant-plate.svg', __FILE__) ?>">
                                        14:30
                                    </option>
                                    <option value="15:30"
                                            data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/restaurant-plate.svg', __FILE__) ?>">
                                        15:30
                                    </option>
                                    <option value="11:59"
                                            data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/sunbed-beach.svg', __FILE__) ?>">
                                        <?php _e('Lunch at your sunbed', 'arienzo_reservation_form'); ?>
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="row no-gutters pb-1">
                            <div class="col-6 pr-2">
                                <label for=""><?php _e('Adults', 'arienzo_reservation_form'); ?></label>
                                <div class="form-group">
                                    <div class="qty-buttons">
                                        <input type="button" value="+" class="qtyplus" name="people">
                                        <input type="number" name="people" id="people" value=""
                                               class="qty form-control required"
                                               placeholder="<?php _e('Adults', 'arienzo_reservation_form'); ?>" readonly min="1">
                                        <input type="button" value="-" class="qtyminus" name="people">
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 pl-2">
                                <label for=""><?php _e('Child', 'arienzo_reservation_form'); ?></label>
                                <div class="form-group">
                                    <div class="qty-buttons">
                                        <input type="button" value="+" class="qtyplus" name="child">
                                        <input type="number" name="child" id="child" value=""
                                               class="qty form-control"
                                               placeholder="<?php _e('Child', 'arienzo_reservation_form'); ?>" readonly>
                                        <input type="button" value="-" class="qtyminus" name="child">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="step">
                        <h3 class="main_question">
                            <strong>2/3</strong><?php _e('Please fill hotel name', 'arienzo_reservation_form'); ?>
                        </h3>
                        <div class="form-group">
                            <input type="text" name="hotel_name" class="form-control required"
                                   placeholder="<?php _e('Hotel Name', 'arienzo_reservation_form'); ?>">
                            <i class="icon-user"></i>
                        </div>
                    </div>
                    <div class="submit step">
                        <h3 class="main_question"><strong>3/3</strong><?php _e('Select Reserve Options', 'arienzo_reservation_form'); ?></h3>

                        <div id="cleanAccordionMain" class="cleanAccordion">
                        <span class="error service-error" style="display:none;"><?php _e('Service filed is required', 'arienzo_reservation_form'); ?></span>
                            <dl class="accordion">
                                <?php foreach ($services as $service) {
                                    $icon = get_post_meta($service->ID, 'arf_service_icon', true);
                                    $price = get_post_meta($service->ID, 'mphb_price', true);
                                    ?>
                                    <dt class="tab_1 singleTab <?php echo ( '' !== $service->post_content ) ? 'hasContent' : ''; ?>">
                                        <div class="accordionSlide">
                                            <div class="accordionContent float-left" style="width: calc(100% - 85px);">
                                                <div style="display: inline-block; width: calc(100% - 44px); float: left;font-size: 14px;color: #000;">
                                                    <i class="<?php echo $icon; ?>"></i> <?php echo $service->post_title; ?> &euro;<?php echo $price; ?>
                                                </div>
                                                <?php if( '' !== $service->post_content ) { ?>
                                                    <div style="display: inline-block; width: 44px; float: right">
                                                        <span class="details"><img width="20" height="20" src="<?php echo plugins_url('../assets/img/icons_select/angle-down.svg', __FILE__) . "" ?>" alt=""></span>
                                                    </div>
                                                <?php } ?>
                                                <div style="clear:both"></div>
                                            </div>
                                            <label class="switch-light switch-ios float-right">
                                                <input type="checkbox" value="<?php echo $service->ID; ?>" name="services[]">
                                                <span><span>No</span><span>Yes</span></span>
                                                <a></a>
                                            </label>
                                            <div style="clear:both"></div>
                                        </div>
                                        <div style="clear: bottom"></div>
                                    </dt>
                                    <?php if( '' !== $service->post_content ) { ?>
                                        <dd class="singleTabBody">
                                            <div>
                                                <p><?php echo $service->post_content; ?></p>
                                            </div>
                                        </dd>
                                    <?php } ?>
                                <?php } ?>
                            </dl>
                        </div>
                        <div class="form-group terms">
                            <label class="container_check"><?php _e('Please accept our', 'arienzo_reservation_form'); ?>
                                <a href="https://booking.arienzobeachclub.com/terms-conditions" target="_blank"><?php _e('Terms and conditions', 'arienzo_reservation_form'); ?></a>
                                <input type="checkbox" name="terms" value="Yes" class="required">
                                <span class="checkmark"></span>
                            </label>
                        </div>
                        <div style="clear: bottom"></div>
                    </div>
                    <!-- /step-->
                </div>
                <!-- /middle-wizard -->
                <div id="bottom-wizard">
                    <button type="button" name="backward"
                            class="backward"><?php _e('Prev', 'arienzo_reservation_form'); ?></button>
                    <button type="button" name="forward"
                            class="forward"><?php _e('Next', 'arienzo_reservation_form'); ?></button>
                    <button type="submit" name="process"
                            class="submit"><?php _e('Submit', 'arienzo_reservation_form'); ?></button>
                    <?php language_selector_flags(); ?>
                </div>
                <!-- /bottom-wizard -->
            </form>
            <ul class="custom_error_message"></ul>
        </div>
        <!-- /Wizard container -->
    </div>
    <style>
        .disable-item {
            background: #ececec;
            pointer-events: none;
            cursor: default;
            text-decoration: none;
            color: black;
        }
        .cleanAccordion {
            position: relative;
        }
        .cleanAccordion .service-error {
            top: -30px;
        }
    </style>
    <script>
        var blocked_dates = <?php echo $blocked_dates; ?>;
        var nowDate = new Date();
        var today = new Date(nowDate.getFullYear(), nowDate.getMonth(), nowDate.getDate(), 0, 0, 0, 0);
        var daysList = [];
        jQuery(document).ready(function () {
            var $animSpeed = 200;

            $('#cleanAccordionMain > .accordion > dt:last-of-type').addClass('accordionLastDt');
            $('#cleanAccordionMain > .accordion > dd:last-of-type').addClass('accordionLastDd');
            $('#cleanAccordionMain > .accordion > dt:first-of-type').addClass('accordionFirstDt');

            $('#cleanAccordionMain > .accordion dd').hide();
            $('#cleanAccordionMain > .dropDown1 > dd:first-of-type').slideDown($animSpeed);
            $('#cleanAccordionMain > .dropDown1 > dt:first-child > .accordionContent').addClass('selected').parent().addClass('selected');
            $('#cleanAccordionMain > .accordion dt .accordionContent').click(function(){
                if($(this).closest('.singleTab').hasClass('hasContent')) {
                    if ($(this).closest(".accordionSlide ").hasClass('selected')) {
                        $(this).closest(".accordionSlide ").removeClass('selected').parent().removeClass('selected');
                        $(this).closest(".accordionSlide ").parent().next().slideUp($animSpeed);

                    } else {
                        $('#cleanAccordionMain > .accordion dt .accordionSlide').removeClass('selected').parent().removeClass('selected');
                        $(this).closest('.accordionSlide').addClass('selected').parent().addClass('selected');
                        $('#cleanAccordionMain > .accordion dd').slideUp($animSpeed);
                        $(this).closest(".accordionSlide ").parent().next().slideDown($animSpeed);
                    }
                }

                return false;
            });
            $("#phone_number").intlTelInput({
                initialCountry: "auto",
                hiddenInput: "full_phone",
                geoIpLookup: function(callback) {
                    $.get('https://ipinfo.io', function() {}, "jsonp").always(function(resp) {
                        let countryCode = (resp && resp.country) ? resp.country : "";
                        callback(countryCode);
                    });
                },
                utilsScript: "<?php echo plugins_url('../assets/js/utils.js', __FILE__); ?>"
            });

            assignDatePicker(0);
            let maxField = 10;
            let addButton = $('.add_button');
            let wrapper = '.field_wrapper';

            var x = 1;
            $(addButton).click(function(){
                if(x < maxField){
                    let fieldHTML = '<div class="form-group field_wrapper">'+
                        '                            <input type="text" name="dates[' + x + ']" class="form-control required"'+
                        '                                   placeholder="<?php _e("When", "arienzo_reservation_form"); ?>" readonly>'+
                        '                            <i class="icon-hotel-calendar_3"></i>'+
                        '                            <a href="javascript:void(0);" class="remove_button"><img src="<?php echo plugins_url("../assets/img/minus.svg", __FILE__) ?>"/></a>'+
                        '                        </div>';
                    $(fieldHTML).insertAfter($(wrapper).last());
                    assignDatePicker(x)
                    x++;
                }
            });

            $(document).on('click', '.field_wrapper .remove_button', function(e){
                e.preventDefault();
                var name = $(this).closest('.form-group').find('input').attr("name");
                var val = $(this).closest('.form-group').find('input').val();

                $("#lunch_time .dd-options a").removeClass('disable-item');
                for (var i = 0; i < daysList.length; i++) {
                    if(daysList[i].date == val) {
                        delete daysList[i];
                    }
                }
                daysList = daysList.filter(function(e){return e});
                for (var j = 0; j < daysList.length; j++) {
                    var itemIndex = 0;
                    if(daysList[j].time == "12:00") {
                        itemIndex = 1
                    } else if(daysList[j].time == "13:00") {
                        itemIndex = 2
                    } else if(daysList[j].time == "13:15") {
                        itemIndex = 2
                    } else if(daysList[j].time == "14:30") {
                        itemIndex = 3
                    } else if(daysList[j].time == "15:30") {
                        itemIndex = 4
                    } else if(daysList[j].time == "11:59") {
                        itemIndex = 5
                    }
                    $("#lunch_time .dd-options").children().eq(itemIndex).find('a').addClass('disable-item');
                }
                $(this).parent('div').remove();
            });
        });

        function assignDatePicker(elementToAdd) {
            let name = 'input[name="dates[' + elementToAdd + ']"]';

            $(name).daterangepicker({
                autoUpdateInput: false,
                singleDatePicker: true,
                "opens": "left",
                "minDate": today,
                locale: {
                    cancelLabel: 'Clear'
                },
                "isInvalidDate" : function(date){
                    for(var ii = 0; ii < blocked_dates.length; ii++){
                        if (date.format('YYYY-MM-DD') == blocked_dates[ii]){
                            return true;
                        }
                    }
                }
            });
            $(name).on('apply.daterangepicker', function (ev, picker) {
                let startDate = picker.startDate.format('YYYY-MM-DD');
                $(this).val(startDate);
                $("#lunch_time .dd-options a").removeClass('disable-item');
                $("#lunch_time .dd-options a .dd-option-text-na").remove();
                $("#lunch_time .dd-options a").removeClass('disable-item')
                daysList = [];
                $("input[name^='dates']").each(function(index, item) {
                    let date = $(item).val();

                    if (disabledDays[date] != undefined) {
                        for (var i = 0; i < disabledDays[date].length; i++) {
                            daysList.push(disabledDays[date][i])
                        }
                        for (var j = 0; j < daysList.length; j++) {
                            var itemIndex = 0;
                            if(daysList[j].time == "12:00") {
                                itemIndex = 1
                            } else if(daysList[j].time == "13:00") {
                                itemIndex = 2
                            } else if(daysList[j].time == "13:15") {
                                itemIndex = 2
                            } else if(daysList[j].time == "14:30") {
                                itemIndex = 3
                            } else if(daysList[j].time == "15:30") {
                                itemIndex = 4
                            } else if(daysList[j].time == "11:59") {
                                itemIndex = 5
                            }
                            $("#lunch_time .dd-options").children().eq(itemIndex).find('a').addClass('disable-item');

                            if(!$("#lunch_time .dd-options").children().eq(itemIndex).find('a').find('.dd-option-text-na').length){
                                $("#lunch_time .dd-options").children().eq(itemIndex).find('a').find('.dd-option-text').after('<label class="dd-option-text-na"> - Not Available</label>');
                            }
                        }
                    }
                })

            });
            $(name).on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
            });
            $(name).bind('change', function () {

            });
            $(name).on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
            });
        }
    </script>
    <script>
        jQuery(document).ready(function () {
            
        }
    </script>
    <?php $output = ob_get_contents();
    ob_end_clean();
    return $output;
}
