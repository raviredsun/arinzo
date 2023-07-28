<?php
if (!defined('WPINC')) {
    die;
}
if (!is_admin())
    die;

global $sitepress;
$current_lang = $sitepress->get_current_language(); //save current language
$sitepress->switch_lang($current_lang); //restore previous language
$services = get_posts([
    'numberposts'       => -1,
    'post_type'     => 'mphb_room_service',
    'post_status'   => 'publish',
    'suppress_filters' => 0
]);
$lunch_time_list = get_posts([
    'numberposts'       => -1,
    'post_type'     => 'lunch_time',
    'post_status'   => 'publish',
    'suppress_filters' => 0
]);
$blocked_dates = js_array(getBookingRules());
ob_start(); ?>

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
                        <div class="form-group field_wrapper" data-count="1">
                            <input type="text" name="dates[0]" class="form-control required"
                                   placeholder="<?php _e('When', 'arienzo_reservation_form'); ?>" readonly>
                            <i class="icon-hotel-calendar_3"></i>
                            <a href="javascript:void(0);" class="add_button" title="Add field"><img src="<?php echo plugins_url("../assets/img/plus.svg", __FILE__) ?>"/></a>
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
                                            data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
                                        <?php _e('Lunch time', 'arienzo_reservation_form'); ?>
                                    </option>

                                    <?php if(!empty($lunch_time_list)){ ?>
                                        <?php foreach ($lunch_time_list as $key => $vvv) { ?>
                                            <option value="<?php echo $vvv->ID ?>" data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/restaurant-plate.svg', __FILE__) ?>">
                                                <?php echo $vvv->post_title ?>
                                            </option>
                                        <?php } ?>
                                    <?php } ?>
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
                                               placeholder="<?php _e('Adults', 'arienzo_reservation_form'); ?>" readonly>
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
                        <h3 class="main_question"><strong>2/3</strong><?php _e('Select Reserve Options', 'arienzo_reservation_form'); ?></h3>

                        <div id="cleanAccordionMain" class="cleanAccordion">
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
<!--                        <div>-->
<!--                            <em style="font-size:11px;">--><?php //_e('*the options proposed are not inclusive of the Beach Club access fee.', 'arienzo_reservation_form'); ?><!--</em>-->
<!--                        </div>-->
                        <div style="clear: bottom"></div>
                    </div>

                    <div class="submit step">
                        <h3 class="main_question">
                            <strong>3/3</strong><?php _e('Please fill with your details', 'arienzo_reservation_form'); ?>
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
                            <input type="email" name="email" class="form-control required"
                                   placeholder="<?php _e('Email', 'arienzo_reservation_form'); ?>">
                            <i class="icon-envelope"></i>
                        </div>
                        <div class="form-group">
                            <input type="tel" name="phone" class="form-control required" id="phone_number"
                                   placeholder="<?php _e('Telephone', 'arienzo_reservation_form'); ?>">
                            <i class="icon-phone"></i>
                        </div>
                        <div class="form-group terms">
                            <label class="container_check"><?php _e('Please accept our', 'arienzo_reservation_form'); ?>
                                <a href="#"><?php _e('Terms and conditions', 'arienzo_reservation_form'); ?></a>
                                <input type="checkbox" name="terms" value="Yes" class="required">
                                <span class="checkmark"></span>
                            </label>
                        </div>
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
                </div>
                <!-- /bottom-wizard -->
            </form>
            <ul class="custom_error_message"></ul>
        </div>
        <!-- /Wizard container -->
    </div>

    <script>
        var blocked_dates = <?php echo $blocked_dates; ?>;
        var nowDate = new Date();
        var today = new Date(nowDate.getFullYear(), nowDate.getMonth(), nowDate.getDate(), 0, 0, 0, 0);

        jQuery(document).ready(function ($) {
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
            let maxField = 10

            var x = 1;
            $('.add_button').click(function(){
                wrapper_parent = $(this).parent(".field_wrapper");
                console.log(wrapper_parent.length)
                if(wrapper_parent.data("count") < maxField){
                    let fieldHTML = '<div class="form-group field_wrapper">'+
                        '                            <input type="text" name="dates[' + x + ']" class="form-control required"'+
                        '                                   placeholder="<?php _e("When", "arienzo_reservation_form"); ?>" readonly>'+
                        '                            <i class="icon-hotel-calendar_3"></i>'+
                        '                            <a href="javascript:void(0);" class="remove_button"><img src="<?php echo plugins_url("../assets/img/minus.svg", __FILE__) ?>"/></a>'+
                        '                        </div>';
                    $(fieldHTML).insertAfter(wrapper_parent);
                    wrapper_parent.data("count",wrapper_parent.data("count")+1)
                    assignDatePicker(wrapper_parent.data("count"))
                    x++;
                }
            });

            $(document).on('click', '.field_wrapper .remove_button', function(e){
                e.preventDefault();
                $(this).parent('div').remove();
            });

            $("#wizard_container").wizard({
                stepsWrapper: "#wrapped",
                submit: ".submit",
                beforeSelect: function (event, state) {
                    if ($('input#website').val().length != 0) {
                        return false;
                    }
                    if (!state.isMovingForward)
                        return true;
                    var inputs = $(this).wizard('state').step.find(':input');
                    return !inputs.length || !!inputs.valid();
                }
            })
                .validate({
                    errorPlacement: function (error, element) {
                        if (element.is(':radio') || element.is(':checkbox')) {
                            error.insertBefore(element.next());
                        } else {
                            error.insertAfter(element);
                        }
                    },
                    submitHandler: function(form) {
                        let custom_form = $(form).find('form#wrapped');
                        let formData = $(custom_form).serialize();
                        let data = {
                            data: formData,
                            action: 'arf_booking_ajax_request'
                        };

                        if ( custom_form.data('requestRunning') ) {
                            return;
                        }
                        let  list = $(".custom_error_message");
                        list.empty();

                        $.ajax({
                            url: arf_ajax_action.ajax_url,
                            type: form.method,
                            data: data,
                            method: "POST",
                            dataType: 'JSON',
                            success: function(response) {

                                if(response.success) {
                                    location.reload();
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
            $("#progressbar").progressbar();
            $("#wizard_container").wizard({
                afterSelect: function (event, state) {
					if(state.stepIndex === 1) {
						$('.forward').text('Skip')
					}
                    $("#progressbar").progressbar("value", state.percentComplete);
                    $("#location").text("(" + state.stepsComplete + "/" + state.stepsPossible + ")");
                }
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
<?php $output = ob_get_contents();
ob_end_clean();
echo $output;