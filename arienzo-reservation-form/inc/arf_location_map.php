<?php
add_action('wp_ajax_arf_map_locations', 'save_arf_map_locations');
function save_arf_map_locations()
{
    $data = !empty($_POST['data']) ? $_POST['data'] : array();
    foreach ($data as $item) {
        update_post_meta($item['id'], 'coordinates', $item['coordinates']);
    }

    echo 1;
    wp_die();
}


function arf_add_location_map_admin_menu()
{
    add_menu_page(
        __('Location Map Page', 'arienzo_reservation_form'),
        __('Location Map Page', 'arienzo_reservation_form'),
        'manage_page',
        'arf_map_page',
        'arf_map_page_contents',
        'dashicons-schedule',
        4
    );
}

add_action('admin_menu', 'arf_add_location_map_admin_menu');

function get_guest_info($booking)
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

function arf_map_page_contents()
{
    $page = sanitize_text_field($_GET['page']);
    $date = !empty($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : date('Y-m-d');
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'jquery-ui-dialog' );


    $args = array(
        'posts_per_page' => -1,
        'post_type' => 'mphb_booking',
        'post_status' => array('confirmed','paid_not_refundable','paid_refundable','last_minute','pending_late_charge'),
        'fields' => 'ids',
        'meta_query' => array(
            array(
                'key' => 'mphb_check_in_date', //The field to check.
                'value' => $date,
                //'compare' => '>=',
            ),
        ),
    );
    $ids = get_posts($args);
    $bookings = [];
    foreach ($ids as $id) {
        $booking = MPHB()->getBookingRepository()->findById($id, true);
        $reservedRooms = $booking->getReservedRooms();
        ob_start();
        foreach ($reservedRooms as $reservedRoom) {
            $reservedServices = $reservedRoom->getReservedServices();
            $placeholder      = ' &#8212;';
            if (!empty($reservedServices)) {
                echo '<ol>';
                foreach ($reservedServices as $reservedService) {
                    echo '<li>';
                    echo '<a target="_blank" href="' . esc_url(get_edit_post_link($booking->getId())) . '">' . esc_html($reservedService->getTitle()) . '</a>';
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
        $services = ob_get_contents();
        ob_end_clean();
        $bookings[] = array(
            'id' => $id,
            'mphb_check_in_date' => $booking->getCheckInDate()->format('Y-m-d'),
            'lunch_time' => get_post_meta($id, 'lunch_time', true),
            'beach_arrival_time' => get_post_meta($id, 'beach_arrival_time', true),
            'coordinates' => get_post_meta($id, 'coordinates', true),
            'full_name' => $booking->getCustomer()->getFirstName() . ' ' . $booking->getCustomer()->getLastName(),
            'guests' => get_guest_info($booking),
			'services' => $services
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
            border: 1px solid #323232;
            border-radius: 4px;
            background-color: #1C1F20;
            box-shadow: 0 0 16px 0 rgba(0, 0, 0, 0.65);
            margin-top: 13px;
        }

        .location_builder table th {
            height: 18px;
            color: #C6C6C7;
            font-family: "Source Sans Pro";
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.16px;
            line-height: 18px;
            border-bottom: 1px solid #3C3E3F;
            padding: 15px 15px 15px 10px;
            text-align: left;
        }

        .location_builder table td {
            height: 18px;
            color: #fff;
            font-family: "Source Sans Pro";
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.16px;
            line-height: 18px;
            border-bottom: 1px solid #3C3E3F;
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

    </style>
    <div class="mphb-bookings-calendar-filters-wrapper">
        <form method="get" class="wp-filter">
            <div class="mphb-bookings-calendar-date alignleft">
                <input type="hidden" name="page" value="<?php echo esc_attr($page) ?>">
                <input style="margin: 15px;" type="text"
                       class="mphb-datepick mphb-custom-period-from mphb-date-input-width" id="arf_location_map_date"
                       name="date_from" placeholder="From" value="<?php echo esc_attr($date); ?>">
            </div>
        </form>
    </div>

    <script>
        jQuery(document).ready(function () {
            jQuery("#arf_location_map_date").datepick({
                dateFormat: 'yyyy-mm-dd',
                onSelect: function (dateText, inst) {
                    jQuery('.wp-filter').submit();
                }
            })
        })
    </script>
    <div style="width: 100%; position: relative">

        <canvas id="canvas" style="width: 100%"></canvas>
        <div id="arf_map_dialog" title="Info">
            <p>This is the default dialog which is useful for displaying information. The dialog window can be moved, resized and closed with the 'x' icon.</p>
        </div>
    </div>
    <div style="width: 100%;" class="location_builder">
        <button class="save_location_builder_data"
                onclick="handleSaveButton(this)"><?php _e("Save", "arienzo_reservation_form") ?></button>
        <table>
            <thead>
            <tr>
                <th><?php _e("Item", "arienzo_reservation_form") ?>:</th>
                <th><?php _e("Customer", "arienzo_reservation_form") ?>:</th>
                <th><?php _e("Number of people", "arienzo_reservation_form") ?>:</th>
                <th><?php _e("Beach time arrival", "arienzo_reservation_form") ?>:</th>
                <th><?php _e("Lunch time", "arienzo_reservation_form") ?>:</th>
                <td><?php _e("Action", "arienzo_reservation_form") ?>:</td>
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
                        <td><?php echo get_lunch_text($booking['lunch_time']); ?></td>
                        <td>
                            <button class="identify_area"
                                    onclick="handleIdentifyArea(<?php echo $booking['id']; ?>, this)"
                                    style="<?php $show ? "display:inline-block" : "display:none" ?>"><?php _e("Identify", "arienzo_reservation_form") ?>
                            </button>
                        </td>
                    </tr>
                <?php }
            } ?>
            </tbody>
        </table>
    </div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/3.1.0/fabric.min.js"></script>
    <script>
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
        var $ = jQuery.noConflict();
        let orgWidth, orgHeight;
        let radius = 5;
		var rectWidth = 20;
        var rectHeight = 20;
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

        canvas.on({
            'object:scaling': function (p) {
                if (p.target.scaleX < 1) {
                    p.target._objects[1].scaleX = 1 + (1 - p.target.scaleX);
                    p.target._objects[2].scaleX = 1 + (1 - p.target.scaleX);
                } else {
                    p.target._objects[1].scaleX = 1 / (p.target.scaleX);
                    p.target._objects[2].scaleX = 1 / (p.target.scaleX);
                }

                if (p.target.scaleY < 1) {
                    p.target._objects[1].scaleY = 1 + (1 - p.target.scaleY);
                    p.target._objects[2].scaleY = 1 + (1 - p.target.scaleY);
                } else {
                    p.target._objects[1].scaleY = 1 / (p.target.scaleY);
                    p.target._objects[2].scaleY = 1 / (p.target.scaleY);
                }
                canvas.renderAll()
            },
        });

        canvas.on({
            'mouse:up': function (e) {
                if (e.target) {
                    let mapId = e.target.mapId;
                    let top = e.target.top;
                    let left = e.target.left;
                    let bookings = window.bookings;
                    const newData = bookings.map(obj => {
                        if (parseInt(obj.id) === parseInt(mapId)) {
                            obj.coordinates = {
                                xCenter: left,
                                yCenter: top
                            }
                        }
                        return obj
                    });
                    window.bookings = newData;
                }
            }
        });

        canvas.on('mouse:over', function (e) {
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
            const info = `Booking #${booking.id} <br> Customer: ${booking.full_name}<br>People:  ${booking.guests} <br>Beach time arrival: ${booking.beach_arrival_time}<br>Lunch Time: ${booking.lunch_time} <br> Services: ${booking.services}`;
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

                        const mapId = data[i].id;
                        let rect = new fabric.Rect({
                            originX: 'center',
                            originY: 'center',
							width: rectWidth,
                            height: rectHeight,
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
                            top: 25,
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
                            //width: rectWidth,
                            //height: rectHeight,
                            fill: 'rgba(0,0,0,0)',
                            transparentCorners: false,
                            lockUniScaling: true,
                            hasRotatingPoint: false,
                            hasControls: false
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
				width: rectWidth,
				height: rectHeight,
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
                top: 25,
                originX: 'center',
                originY: 'center',
                fontSize: 16,
            });

            let group = new fabric.Group([rect, circle, text], {
                left: 100,
                top: 100,
                originX: 'left',
                originY: 'top',
                //width: rectWidth,
                //height: rectHeight,
                fill: 'rgba(0,0,0,0)',
                transparentCorners: false,
                lockUniScaling: true,
                hasRotatingPoint: false,
                selectable: true
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
