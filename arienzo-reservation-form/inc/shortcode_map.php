<?php
if (!defined('WPINC')) {
    die;
}
require_once plugin_dir_path(__FILE__) . 'Arf_Custom_Booking_Creator.php';
function arf_form_with_map($atts)
{	
	$attr = shortcode_atts( 
		array(
		 'service_id' => false,
		 'lunch_time_enable' => 1,
		 'is_home_page' => 0
		 ), $atts 
	 );
	 
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
    wp_enqueue_script('arf_booking_form_map');
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
    $coordinates = get_option('arf_map_locations');
    ob_start(); ?>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/3.1.0/fabric.min.js"></script>
    <script>
        var disabledDays = <?php echo json_encode($disabledDays );?>;
		disabledDays = disabledDays.reduce(function (r, a) {
            r[a.date] = r[a.date] || [];
            r[a.date].push(a);
            return r;
        }, Object.create(null));
    </script>
    <div class="content-right" id="start">		<div id="preloader-section">			<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>		</div>
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
                            <strong>1/4</strong> <?php _e('Location', 'arienzo_reservation_form'); ?>
                        </h3>
                        <div class="form-group">
                            <canvas id="canvas"></canvas>
                            <input type="hidden" name="coordinate" value="">
                        </div>
                    </div>
                    <div class="step">
                        <h3 class="main_question">
                            <strong>2/3</strong><?php _e('Enter your Booking details', 'arienzo_reservation_form'); ?>
                        </h3>
                        <div class="form-group field_wrapper">
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
						<?php if($attr['lunch_time_enable'] == 1) { ?>
							<div class="form-group">
								<div class="styled-select clearfix">
									<select class="ddslick" name="lunch_time" id="lunch_time">
										<option value=""
												data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
											<?php _e('Lunch time', 'arienzo_reservation_form'); ?>
										</option>
										<option value="12:00"
												data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
											12:00
										</option>
										<option value="13:00"
												data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
											13:00
										</option>
										<option value="14:30"
												data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
											14:30
										</option>
										<option value="15:30"
												data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
											15:30
										</option>
										<option value="11:59"
												data-imagesrc="<?php echo plugins_url('../assets/img/icons_select/clock.png', __FILE__) ?>">
											<?php _e('Lunch at your sunbed', 'arienzo_reservation_form'); ?>
										</option>
									</select>
								</div>
							</div>
						<?php } ?>
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
					<?php if(empty($attr['service_id']) && $attr['is_home_page'] != 1) { ?>
						<div class="step">
							<h3 class="main_question"><strong>3/4</strong><?php _e('Select Reserve Options', 'arienzo_reservation_form'); ?></h3>

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
							<div style="clear: bottom"></div>
						</div>
					<?php } ?>
                    <div class="submit step">
                        <h3 class="main_question">
                            <strong><?php echo empty($attr['service_id']) ? "4/4" : "3/3"; ?></strong><?php _e('Please fill with your details', 'arienzo_reservation_form'); ?>
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
                    <?php language_selector_flags(); ?>
                </div>
                <!-- /bottom-wizard -->
				<?php if(!empty($attr['service_id'])) { ?>
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
		.canvas-container {
            text-align: center;
            margin: auto;
        }

        .disable-item {
            background: #ececec;
            pointer-events: none;
            cursor: default;
            text-decoration: none;
            color: black;
        }
    </style>
    <script>
	    var $ = jQuery.noConflict();
        var disabledDays = <?php echo json_encode($disabledDays);?>;
        let imageUrl = "<?php echo plugins_url('../assets/img/map.png', __FILE__) ?>";

        var selectedItem = "";
        let canvas = window.canvas = new fabric.Canvas('canvas', {
            hoverCursor: 'pointer',
            selection: false,
            selectionBorderColor: 'green',
            backgroundColor: null
        });
        let width = $('.location_builder_content').width();

        let canvasOriginalWidth = "717";
        let canvasOriginalHeight = "600";
        //
        canvas.setWidth(canvasOriginalWidth);
        canvas.setHeight(canvasOriginalHeight);
        var coordinates = window.coordinates = <?php echo !empty($coordinates) ? json_encode($coordinates) : "[]" ?>;

        canvas.setBackgroundImage(imageUrl, canvas.renderAll.bind(canvas), {
            // Optionally add an opacity lvl to the image
            backgroundImageOpacity: 1,
            // should the image be resized to fit the container?
            backgroundImageStretch: false,
            scaleX: 1,
            scaleY: 1,
        });

        canvas.renderAll();

        if (coordinates.length > 0) {
            addCoordinatesToCanvas(coordinates);
        }

        function addCoordinatesToCanvas(data) {
            window.canvas.remove(...window.canvas.getObjects());
            if (data.length > 0) {
                for (let i = 0; i < data.length; i++) {
                    const item = data[i];
                    if (!item.xCenter) continue;
                    const left = parseInt(item.xCenter);
                    const top = parseInt(item.yCenter);


                    const width = 30;
                    const height = 30;
                    const mapId = item.mapId;
                    const mapName = item.mapName;
                    var rect = new fabric.Rect({
                        originX: 'center',
                        originY: 'center',
                        width: width,
                        height: height,
                        selectable: false,
                        angle: 0,
                        fill: "rgb(42 191 170 / 90%)",
                        lockUniScaling: true,
                    });

                    var group = new fabric.Group([rect], {
                        left: left,
                        top: top,
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

                    window.canvas.add(group);
                    //group.angle = item.rotation;
                    group.toObject = (function (toObject) {
                        return function () {
                            return fabric.util.object.extend(toObject.call(this), {
                                mapName: mapName,
                                mapId: parseInt(mapId)
                            });
                        };
                    })(group.toObject);

                    group.mapId = parseInt(mapId);
                    group.on('mousedown', function (e) {
                        var mapId = e.target.mapId;
                        for (var j = 0; j < canvas.getObjects().length; j++) {
                            canvas.getObjects()[j].item(0).set({
                                fill: "rgb(42 191 170 / 90%)"
                            });
                        }

                        if (!selectedItem) {
                            e.target.item(0).set({
                                fill: "red"
                            });
                            selectedItem = mapId;
                            var coordinate = coordinates.find(x => x.mapId == mapId);
                            $('input[name="coordinate"]').val(JSON.stringify(coordinate))
                        } else if (selectedItem != mapId) {
                            e.target.item(0).set({
                                fill: "red"
                            });
                            selectedItem = mapId;
                            var coordinate = coordinates.find(x => x.mapId == mapId);
                            $('input[name="coordinate"]').val(JSON.stringify(coordinate))
                        } else {
                            e.target.item(0).set({
                                fill: "rgb(42 191 170 / 90%)"
                            });
                            selectedItem = "";
                            $('input[name="coordinate"]').val("")
                        }
                    });
                }

                window.canvas.renderAll();

            }
        }

	
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
                            } else if(daysList[j].time == "14:30") {
                                itemIndex = 3
                            } else if(daysList[j].time == "15:30") {
                                itemIndex = 4
                            } else if(daysList[j].time == "11:59") {
                                itemIndex = 5
                            }
                            $("#lunch_time .dd-options").children().eq(itemIndex).find('a').addClass('disable-item');
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
    <?php $output = ob_get_contents();
    ob_end_clean();
    return $output;
}

add_shortcode('arf_form_with_map', 'arf_form_with_map');
