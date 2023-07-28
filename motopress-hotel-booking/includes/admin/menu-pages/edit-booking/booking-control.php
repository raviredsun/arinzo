<?php

namespace MPHB\Admin\MenuPages\EditBooking;

use MPHB\Entities\ReservedRoom;
use MPHB\Utils\ParseUtils;
use RuntimeException as Error;

/**
 * @since 3.8
 */
class BookingControl extends StepControl
{
    public function setup()
    {
        if ($this->editBooking->isImported()) {
            throw new Error(__('You cannot edit the imported booking. Please update the source booking and resync your calendars.', 'motopress-hotel-booking'));
        } else if (!isset($_POST['checkout_nonce']) || !wp_verify_nonce($_POST['checkout_nonce'], 'edit-booking')) {
            throw new Error(__('Request does not pass security verification. Please refresh the page and try one more time.', 'motopress-hotel-booking'));
        } else if (!isset($_POST['check_in_date'])) {
            throw new Error(__('Check-in date is not set.', 'motopress-hotel-booking'));
        } else if (!isset($_POST['check_out_date'])) {
            throw new Error(__('Check-out date is not set.', 'motopress-hotel-booking'));
        } else if (!isset($_POST['mphb_room_details'])) {
            throw new Error(__('There are no accommodations selected for reservation.', 'motopress-hotel-booking'));
        }

        $checkInDate = ParseUtils::parseCheckInDate($_POST['check_in_date'], array('allow_past_dates' => true));
        $checkOutDate = ParseUtils::parseCheckOutDate($_POST['check_out_date'], array('check_booking_rules' => false, 'check_in_date' => $checkInDate));
        $roomDetails = ParseUtils::parseRooms($_POST['mphb_room_details'], array(
            'check_in_date'  => $checkInDate,
            'check_out_date' => $checkOutDate,
            'edit_booking'   => $this->editBooking
        ));
        $booking = $this->editBooking;

        $oldRooms = $booking->getReservedRooms();

        $newRooms = $this->mergeRooms($roomDetails, $oldRooms);
         
        // Update booking with new data
        $booking->setDates($checkInDate, $checkOutDate);
        $booking->setRooms($newRooms);
        $booking->updateTotal();


        if (!empty($_POST['mphb_room_details'])) {
            foreach ( $_POST['mphb_room_details'] as $value ) {
                if (!empty($value['services'])) {
                    foreach ( $value['services'] as $reservedService ) {
                        if(empty($reservedService['id'])) continue;
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
                }
            }
        }


        if (!empty($_POST['paytype'])) {
             update_post_meta($booking->getId(), 'paytype', $_POST['paytype']);
        }
        if (!empty($_POST['products'])) {
            $products_qty_old = get_post_meta($booking->getId(),"products_qty",1);

            $product_change = array();



            $products = array();
            $products_qty = array();
            $products_title = array();
            $products_title2 = array();
            $price_total = 0;
            foreach ($_POST['products'] as $key => $value) {
                $price = 0;
                $qty = 1;
                if(!empty($service_price[$value])){
                    $products[] = ($value);
                    $price = $service_price[$value];
                    if(isset($_POST['products_qty'][$value])){
                        $qty = $_POST['products_qty'][$value];
                        $price = $qty * $price;
                    }else if(isset($max[$value]) && $max[$value] < $adults_total){
                        $qty = ceil($adults_total / $max[$value]);
                        $price = $qty * $price;
                    }
                    $products_qty[$value] = $qty;
                    $price_total += $price;
                    $price = " - €".$price;
                    $products_title[] = get_the_title($value)." x ".$qty.$price;
                    $products_title2[] = get_the_title($value)." x ".$qty;
                }
            }
            update_post_meta($booking->getId(), 'products_qty', $products_qty);
            update_post_meta($booking->getId(), 'products_price_total', $price_total);
            update_post_meta($booking->getId(), 'products', $products);
            update_post_meta($booking->getId(), 'products_title', implode(" , ", $products_title));
            update_post_meta($booking->getId(), 'products_title2', implode(" , ", $products_title2));


            if($products_qty){
                foreach ($products_qty as $key => $value) {
                    if(!isset($products_qty_old[$key])){
                        $product_change["add"][] = array(
                            "key" => $key,
                            "oldqty" => 0,
                            "qty" => $value,
                        );
                    }else if($products_qty_old[$key] != $value){
                        if($products_qty_old[$key] > $value){
                            $product_change["add"][] = array(
                                "key" => $key,
                                "oldqty" => $products_qty_old[$key],
                                "qty" => $value,
                            );  
                        }else{
                            $product_change["minus"][] = array(
                                "key" => $key,
                                "oldqty" => $products_qty_old[$key],
                                "qty" => $value,
                            );  
                        }
                        unset($products_qty_old[$key]);
                    }else{
                        unset($products_qty_old[$key]);
                    }
                }
            }
            if(!empty($product_change['add'])){
                foreach ($product_change['add'] as $key => $value) {
                    $oldstock = $stock = get_post_meta($value['key'],"stock",1);
                    $stock = $stock - ($value['qty'] - $value['oldqty']);
                    update_post_meta($value['key'],"stock",$stock);
                    if(file_exists(ABSPATH."product_log.txt")){
                        $text = 'Booking ID '.$booking->getId().".Product ".get_the_title($value['key'])." Old Quantity ".$value['oldqty'].", New Quantity ".$value['qty'].". Old Stock ".$oldstock.", New Stock ".$stock.PHP_EOL;
                        $fp = fopen(ABSPATH."product_log.txt", 'a');
                        fwrite($fp, $text);
                    }
                }
            }
            if(!empty($product_change['minus'])){
                foreach ($product_change['minus'] as $key => $value) {
                    $oldstock = $stock = get_post_meta($value['key'],"stock",1);
                    $stock = $stock + ($value['oldqty'] - $value['qty']);
                    update_post_meta($value['key'],"stock",$stock);
                    if(file_exists(ABSPATH."product_log.txt")){
                        $text = 'Booking ID '.$booking->getId().".Product ".get_the_title($value['key'])." Old Quantity ".$value['oldqty'].", New Quantity ".$value['qty'].". Old Stock ".$oldstock.", New Stock ".$stock.PHP_EOL;
                        $fp = fopen(ABSPATH."product_log.txt", 'a');
                        fwrite($fp, $text);
                    }
                }
            }
            if(!empty($products_qty_old)){
                foreach ($products_qty_old as $key => $value) {
                    $oldstock = $stock = get_post_meta($key,"stock",1);
                    $stock = $stock + ($value);
                    update_post_meta($key,"stock",$stock);
                    if(file_exists(ABSPATH."product_log.txt")){
                        $text = 'Booking ID '.$booking->getId().".Product ".get_the_title($key)." Old Quantity ".$value.", New Quantity 0. Old Stock ".$oldstock.", New Stock ".$stock.PHP_EOL;
                        $fp = fopen(ABSPATH."product_log.txt", 'a');
                        fwrite($fp, $text);
                    }
                }
            }
        }

        $priceBreakdown = $booking->getPriceBreakdown($booking->getId());
        // Update booking
        $saved = MPHB()->getBookingRepository()->save($booking);

        if ($saved) {

            MPHB()->getBookingRepository()->updateReservedRooms($booking->getId());
        } else {
            throw new Error(__('Unable to update booking. Please try again.', 'motopress-hotel-booking'));
        }

        if($_POST['mphb_beach_arrival_time']) {
            update_post_meta($booking->getId(), "beach_arrival_time", $_POST['mphb_beach_arrival_time']);
        }

        if($_POST['mphb_lunch_time']) {
            update_post_meta($booking->getId(), "lunch_time", $_POST['mphb_lunch_time']);
        }
        
        if($_POST['mphb_table_id']) {
            update_post_meta($booking->getId(), "arf_cp_table_id", $_POST['mphb_table_id']);
        }
        if(isset($_POST['mphb_place'])) {
            update_post_meta($booking->getId(), "mphb_place", $_POST['mphb_place']);
        }

        if(isset($_POST['mphb_place_2'])) {
            update_post_meta($booking->getId(), "mphb_place_2", $_POST['mphb_place_2']);
        }

        if(isset($_POST['mphb_place_3'])) {
            update_post_meta($booking->getId(), "mphb_place_3", $_POST['mphb_place_3']);
        }

        if(isset($_POST['mphb_place_4'])) {
            update_post_meta($booking->getId(), "mphb_place_4", $_POST['mphb_place_4']);
        }

        if(isset($_POST['mphb_place_5'])) {
            update_post_meta($booking->getId(), "mphb_place_5", $_POST['mphb_place_5']);
        }

        if(isset($_POST['mphb_place_6'])) {
            update_post_meta($booking->getId(), "mphb_place_6", $_POST['mphb_place_6']);
        }

        if(isset($_POST['mphb_place_7'])) {
            update_post_meta($booking->getId(), "mphb_place_7", $_POST['mphb_place_7']);
        }



        $booking->addLog(__('Booking was edited.', 'motopress-hotel-booking'));
        if($booking){
            update_post_meta($booking->getId(), '_mphb_booking_price_breakdown', json_encode($priceBreakdown));
            update_post_meta($booking->getId(), 'mphb_total_price', $priceBreakdown["total"]);
        }

        // Reload booking after update. Refresh its data, such as reserved rooms
        // and their IDs
        $booking = mphb_get_booking($booking->getId(), true);
        do_action('mphb_update_edited_booking', $booking, $oldRooms);

        // Redirect back to booking post page
        $redirectUrl = get_edit_post_link($booking->getId(), 'raw');
        $redirectUrl = add_query_arg('message', 1, $redirectUrl); // Add "Post updated" message


        wp_safe_redirect($redirectUrl);
        exit;

        // parent::setup(); - don't need this
    }

    /**
     * @param array $parsedRooms
     * @param ReservedRoom[] $reservedRooms
     * @return ReservedRoom[]
     */
    protected function mergeRooms($parsedRooms, $reservedRooms)
    {
        // Use old UIDs for same rooms
        $uids = array();

        foreach ($reservedRooms as $reservedRoom) {
            $uids[$reservedRoom->getRoomId()] = $reservedRoom->getUid();
        }

        // Create new list of reserved rooms
        $rooms = array();

        foreach ($parsedRooms as $room) {

            $services = array_map(array('\MPHB\Entities\ReservedService', 'create'), $room['services']);

            $services = array_filter($services); // Filter NULLs

            $uid = isset($uids[$room['room_id']]) ? $uids[$room['room_id']] : mphb_generate_uid();

            $rooms[] = new ReservedRoom(array(
                'room_id'           => $room['room_id'],
                'rate_id'           => $room['rate_id'],
                'adults'            => $room['adults'],
                'children'          => $room['children'],
                'guest_name'        => $room['guest_name'],
                'reserved_services' => $services,
                'uid'               => $uid
            ));

        }

        return $rooms;
    }
}
