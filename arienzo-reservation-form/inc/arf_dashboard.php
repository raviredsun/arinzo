<?php

function arf_dashboard_enqueue_datepicker()

{

    wp_enqueue_script('jquery-ui-datepicker');

    wp_register_style('jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css');

    wp_enqueue_style('jquery-ui');



    wp_enqueue_script('arf_dashboard_js', plugins_url('../assets/js/arf_dashboard.js', __FILE__), array(), '1.0.1', true);

    wp_localize_script('arf_dashboard_js', 'arf_ajax_action', array(

        'ajax_url' => admin_url('admin-ajax.php'),

        'nonce' => wp_create_nonce('ajax-nonce'),

        'pluginsUrl' => plugins_url('arienzo-reservation-form'),

    ));

}



add_action('admin_enqueue_scripts', 'arf_dashboard_enqueue_datepicker');





remove_action('welcome_panel', 'wp_welcome_panel');

add_action('welcome_panel', 'arf_dashboard_booking_box');

//add_action('wp_dashboard_setup', 'arf_dashboard_box');



function arf_dashboard_booking_box()

{ ?>

    <div class="welcome-panel-content-" id="arf_dashboard_booking_box">

        <div class="arf_dashboard_box_green arf_dashboard_box_float_left">

            <h2>12.00</h2>

            <h3><?php _e('Pax'); ?> <span id="lunch_time_1">0</span></h3>

            <div class="more_info">

                <a href="#" data-lunchTime="12:00"><?php _e('More info'); ?>  -></a>

            </div>

        </div>

        <div class="arf_dashboard_box_yellow arf_dashboard_box_float_left color_black">

            <h2>13.00</h2>

            <h3><?php _e('Pax'); ?> <span id="lunch_time_2">0</span></h3>

            <div class="more_info">

                <a href="#" data-lunchTime="13:00"><?php _e('More info'); ?>  -></a>

            </div>

        </div>

        <div class="arf_dashboard_box_red arf_dashboard_box_float_left">

            <h2>14.30</h2>

            <h3><?php _e('Pax'); ?> <span id="lunch_time_3">0</span></h3>

            <div class="more_info">

                <a href="#" data-lunchTime="14:30"><?php _e('More info'); ?> -></a>

            </div>

        </div>

        <div class="arf_dashboard_box_blue arf_dashboard_box_float_left">

            <h2>15.30</h2>

            <h3><?php _e('Pax'); ?> <span id="lunch_time_4">0</span></h3>

            <div class="more_info">

                <a href="#" data-lunchTime="15:30"><?php _e('More info'); ?>  -></a>

            </div>

        </div>

        <div class="clear_both"></div>

        <div class="arf_dashboard_box_big_green arf_dashboard_box_float_left">

            <h3><?php _e('Lunch at your sunbed'); ?>: <span id="lunch_time_5">0</span></h3>

        </div>

        <div class="arf_dashboard_box_big_red arf_dashboard_box_float_left">

            <input type="hidden" name="arf_booking_day" id="arf_booking_day" value="<?php echo date('Y-m-d'); ?>">

            <h3><?php _e('From'); ?> <a href="#" id="change_day"><?php echo date('Y-m-d'); ?></a> <?php _e('to'); ?> <span id="next_day"><?php echo date('Y-m-d', strtotime(' +1 day')) ?></span></h3>

        </div>

        <div class="clear_both"></div>

        <div class="arf_dashboard_booking_box_table arf_dashboard_box_float_left">

            <table class="wp-list-table">

                <thead>

                <tr>

                    <th>Booking code</th>

                    <th>Pax Name</th>

                    <th>Guests</th>

                </tr>

                </thead>

                <tbody></tbody>

            </table>

        </div>

    </div>

    <style>

        .arf_dashboard_box_green {

            width: 275px;

            height: 140px;

            margin-right: 20px;

            margin-bottom: 20px;

            background: url(<?php echo plugins_url('/arienzo-reservation-form/assets/img/bg_green.png') ?>);

            position: relative;

        }



        .arf_dashboard_box_yellow {

            background: #ffc043;

            width: 275px;

            height: 140px;

            margin-right: 20px;

            margin-bottom: 20px;

            background: url(<?php echo plugins_url('/arienzo-reservation-form/assets/img/bg_yellow.png') ?>);

            position: relative;

        }



        .arf_dashboard_box_red {

            background: #dc3348;

            width: 275px;

            height: 140px;

            margin-right: 20px;

            margin-bottom: 20px;

            background: url(<?php echo plugins_url('/arienzo-reservation-form/assets/img/bg_red.png') ?>);

            position: relative;

        }



        .arf_dashboard_box_blue {

            background: #16a2b5;

            width: 275px;

            height: 140px;

            margin-right: 20px;

            margin-bottom: 20px;

            background: url(<?php echo plugins_url('/arienzo-reservation-form/assets/img/bg_blue.png') ?>);

            position: relative;

        }



        .arf_dashboard_box_big_red {

            background: #dc3348;

            width: 570px;

            height: 70px;

            margin-right: 20px;

            margin-bottom: 20px;

        }



        .arf_dashboard_box_big_green {

            background: #23a753;

            width: 570px;

            height: 70px;

            margin-right: 20px;

            margin-bottom: 20px;

        }



        .arf_dashboard_box_float_left {

            float: left;

        }



        .clear_both {

            clear: both;

        }



        #arf_dashboard_booking_box h2 {

            margin-top: 22px;

            margin-left: 10px;

            font-size: 34px;

            font-weight: bold;

            color: #ffffff;

        }



        #arf_dashboard_booking_box h3 {

            font-size: 20px;

            margin-left: 10px;

            margin-top: 10px;

            line-height: 16px;

            font-weight: bold;

            color: #ffffff;

        }



        .more_info {

            position: absolute;

            bottom: 0;

            background: #00000038;

            width: 100%;

            height: 25px;

            text-align: center;

            color: #ffffff;

        }



        .more_info a {

            color: #ffffff;

            text-decoration: none;

            font-weight: bold;

            display: block;

        }



        .color_black > * {

            color: #000000 !important;

        }



        .color_black .more_info a {

            color: #000000 !important;

        }

        .arf_dashboard_booking_box_table {

            display: none;

        }

		.arf_dashboard_booking_box_table table {text-align: center}

		.arf_dashboard_box_big_red  a {color: white;}

    </style>

<?php }