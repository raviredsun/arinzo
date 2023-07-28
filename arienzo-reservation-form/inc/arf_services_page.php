<?php
if (!defined('WPINC')) {
    die;
}

function arf_add_services_admin_menu()
{
    add_menu_page(
        __('Services Page', 'arienzo_reservation_form'),
        __('Services Page', 'arienzo_reservation_form'),
        'manage_page',
        'arf_services_page',
        'arf_services_page_contents',
        'dashicons-schedule',
        4
    );
}

add_action('admin_menu', 'arf_add_services_admin_menu');


function arf_services_page_contents()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-dialog');
    wp_enqueue_script('chosen_jquery', plugins_url('../assets/js/chosen.jquery.min.js', __FILE__), ['jquery'] );
    wp_enqueue_style('chosen_style', plugins_url('../assets/css/chosen.css', __FILE__) );
    MPHB()->getAdminScriptManager()->enqueue();
    $page = sanitize_text_field($_GET['page']);
    $date = date('Y-m-d');
    $date_next = new DateTime($date);
    $date_next->modify('+1 day');

    $date_from = !empty($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : $date;
    $date_to = !empty($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : $date_next->format('Y-m-d');
    $_services = !empty($_GET['services']) ? $_GET['services'] : [];

    $services = get_posts([
        'numberposts' => -1,
        'post_type' => 'mphb_room_service',
        'post_status' => 'publish',
        'suppress_filters' => 0,
    ]);

    $args = array(
        'posts_per_page' => -1,
        'post_type' => 'mphb_booking',
        'post_status' => array('confirmed','paid_not_refundable','paid_refundable','pending_late_charge','paid_late_charge','last_minute'),
        'fields' => 'ids',
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'mphb_check_in_date',
                'value' => $date_from,
                'compare' => '>=',
            ),
            array(
                'key' => 'mphb_check_in_date',
                'value' => $date_to,
                'compare' => '<=',
            ),
        ),
    );
    $ids = get_posts($args);
    $service_info = [];
    foreach ($ids as $id) {
        $booking = MPHB()->getBookingRepository()->findById($id, true);
        $reservedRooms = $booking->getReservedRooms();

        foreach ($reservedRooms as $reservedRoom) {
            $reservedServices = $reservedRoom->getReservedServices();
            /*if(isset($_GET['a'])){
                echo "<pre>"; print_r($reservedServices); echo "</pre>";die; 
            }*/
            foreach ($reservedServices as $reservedService) {
                $adultsTotal = 0;
                $childrenTotal = 0;
                if (!empty($service_info[$reservedService->getId()])) {
                    $service_info[$reservedService->getId()]['count'] += 1;
                } else {
                    $service_info[$reservedService->getId()]['count'] = 1;
                }
                $adultsTotal += $reservedRoom->getAdults();
                $childrenTotal += $reservedRoom->getChildren();
                if (!empty($service_info[$reservedService->getId()]['pax'])) {
                    
                        
                    $service_info[$reservedService->getId()]['pax'] += $adultsTotal + $childrenTotal;
                } else {
                    $service_info[$reservedService->getId()]['pax'] = $adultsTotal + $childrenTotal;
                }
            }
        }
    }
    ?>
    <style>
        .location_builder table {
            box-sizing: border-box;
            /*width: 100%;*/
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
    </style>

    <div class="mphb-bookings-calendar-filters-wrapper">
        <form method="get" class="wp-filter">
            <div class="mphb-bookings-calendar-date alignleft">
                <input type="hidden" name="page" value="<?php echo esc_attr($page) ?>">
                <label for="arf_service_date_from">From:</label>
                <input style="margin: 15px;" type="text"
                       class="mphb-datepick mphb-custom-period-from mphb-date-input-width"
                       id="arf_service_date_from"
                       name="date_from" placeholder="From"
                       value="<?php echo esc_attr($date_from); ?>">
                <label for="arf_service_date_to">To:</label>
                <input style="margin: 15px;" type="text"
                       class="mphb-datepick mphb-custom-period-from mphb-date-input-width"
                       id="arf_service_date_to"
                       name="date_to" placeholder="From"
                       value="<?php echo esc_attr($date_to); ?>">
                <?php if(!empty($services)) { ?>
                    <select name="services[]" id="arf_services_list" data-placeholder="Choose a Service..." class="select" multiple tabindex="3">
                        <?php foreach ($services as $service) {
                            $selected = "";
                            if(!empty($_services)) {
                                $selected = in_array($service->ID, $_services) ? "selected" : "";
                            } ?>
                            <option value="<?php echo $service->ID; ?>" <?php echo $selected ?>><?php echo $service->post_title; ?></option>
                        <?php } ?>
                    </select>
                <?php } ?>
                <input type="submit" value="Submit">
            </div>
        </form>
    </div>
    <p><?php _e("On the table displays the services that were selected when booking(Status: Confirmed)", "arienzo_reservation_form") ?></p>
    <div  class="location_builder">
        <table>
            <thead>
            <tr>
                <th><?php _e("Service", "arienzo_reservation_form") ?></th>
                <th><?php _e("Count", "arienzo_reservation_form") ?></th>
                <th><?php _e("PAX", "arienzo_reservation_form") ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($services as $service) {
                if (!empty($_services) && !in_array($service->ID, $_services)) {
                    continue;
                }
                ?>
                <tr>
                    <td><?php echo $service->post_title; ?></td>
                    <td><?php echo !empty($service_info[$service->ID]) ? $service_info[$service->ID]['count'] : 0; ?></td>
                    <td><?php echo !empty($service_info[$service->ID]) ? $service_info[$service->ID]['pax'] : 0; ?></td>
                </tr>
                <?php
            } ?>
            </tbody>
        </table>
    </div>
    <script>
        jQuery(document).ready(function () {
            jQuery("#arf_service_date_from").datepick({
                dateFormat: 'yyyy-mm-dd',
            });
            jQuery("#arf_service_date_to").datepick({
                dateFormat: 'yyyy-mm-dd',
            });

            jQuery('#arf_services_list').chosen({});
        })
    </script>
    <?php
}
