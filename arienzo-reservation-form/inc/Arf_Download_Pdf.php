<?php
include(plugin_dir_path(__FILE__) . 'dompdf/autoload.inc.php');
// Set up directory to save PDF
$upload_dir = wp_upload_dir();
define('ARF_REPORTS_UPLOAD_DIR', $upload_dir['basedir'] . '/arf_reports/');
define('ARF_REPORTS_UPLOAD_URL', $upload_dir['baseurl'] . '/arf_reports/');

use Dompdf\Dompdf;
use Dompdf\Options;
use \MPHB\Views;

class ARF_DOWNLOAD_PDF
{
    private $start_date;
    private $end_date;
    private $fileType;
    private $status;
    private $fileNamePD = 'product-report.pdf';
    private $fileNamePD2 = 'product-report2.csv';
    private $fileNameAT = 'order-arrival-time-report.pdf';
    private $fileNameLT = 'order-lunch-time-report.pdf';
    private $fileNameLAT = 'order-lunch-arrival-times-report.pdf';

    public function __construct($start_date, $end_date, $fileType, $status = "")
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->fileType = $fileType;
        $this->status = $status;
    }

    private function get_guest_info($booking)
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


    private function get_guest_info2($reservedRooms)
    {
        $reservedRooms = json_decode(strip_tags($reservedRooms),1);
        $html = "";
        if (!empty($reservedRooms)) {
            $adultsTotal = 0;
            $childrenTotal = 0;
            foreach ($reservedRooms['rooms'] as $reservedRoom) {
                $adultsTotal += isset($reservedRoom['room']['adults']) ? $reservedRoom['room']['adults'] : 0;
                $childrenTotal += isset($reservedRoom['room']['children']) ? $reservedRoom['room']['children'] : 0;
            }

            $html .= 'AD: ';
            $html .= $adultsTotal;
            if ($childrenTotal > 0) {
                $html .= '<br/>';
                $html .= 'CH: ';
                $html .= $childrenTotal;
            }
        }
        return $html;
    }

    private function generate_services($booking)
    {
        $reservedRooms = $booking->getReservedRooms();
        $html = "";
        foreach ($reservedRooms as $reservedRoom) {
            $reservedServices = $reservedRoom->getReservedServices();
            $placeholder = ' &#8212;';
            if (!empty($reservedServices)) {
                foreach ($reservedServices as $reservedService) {
                    $html .= '<a target="_blank" href="' . esc_url(get_edit_post_link($booking->getId())) . '">' . esc_html($reservedService->getTitle()) . '</a>';
                    if ($reservedService->isPayPerAdult()) {
                        $html .= ' <em>' . sprintf(_n('x %d guest', 'x %d guests', $reservedService->getAdults(), 'motopress-hotel-booking'), $reservedService->getAdults()) . '</em>';
                    }
                    if ($reservedService->isFlexiblePay()) {
                        $html .= ' <em>' . sprintf(_n('x %d time', 'x %d times', $reservedService->getQuantity(), 'motopress-hotel-booking'), $reservedService->getQuantity()) . '</em>';
                    }
                }
            } else {
                $html .= $placeholder;
            }
        }
        return $html;
        /*$reservedRooms = $booking->getReservedRooms();
        ob_start();
        foreach ($reservedRooms as $reservedRoom) {
            $reservedServices = $reservedRoom->getReservedServices();
            $placeholder = ' &#8212;';
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
        $replaceText = ob_get_contents();
        ob_end_clean();
        return $replaceText;*/
    }

    private function generate_services2($id,$price_breakdown)
    {
        ob_start();
        if(isset($price_breakdown['rooms'])){
            foreach ($price_breakdown['rooms'] as $reservedRoom) {
                if (!empty($reservedRoom['services']['list'])) {
                    foreach ($reservedRoom['services']['list'] as $reservedService) {
                        echo '<a target="_blank" href="' . esc_url(get_edit_post_link($id)) . '">' . esc_html($reservedService['title']) . '</a>';
                        echo ' <em>' . sprintf(_n('x %d guest', 'x %d guests', $reservedService['adults']+($reservedService['child'] ? $reservedService['child'] : 0), 'motopress-hotel-booking'), $reservedService['adults']+($reservedService['child'] ? $reservedService['child'] : 0)) . '</em>';
                    }
                } else {
                    echo $placeholder;
                }
            }
            $replaceText = ob_get_contents();
        }
        ob_end_clean();
        return $replaceText;
        /*$reservedRooms = $booking->getReservedRooms();
        ob_start();
        foreach ($reservedRooms as $reservedRoom) {
            $reservedServices = $reservedRoom->getReservedServices();
            $placeholder = ' &#8212;';
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
        $replaceText = ob_get_contents();
        ob_end_clean();
        return $replaceText;*/
    }

    private function generate_arrival_time_html($data, $start_date, $end_date)
    {
        ob_start();
        $adultsTotal = 0;
        $childrenTotal = 0;
        ?>
        <!doctype html>
        <html lang="en">
        <head>
            <style>
                table, th, td {
                    font-family: Arial;
                    border: 1px solid black;
                    border-spacing: 0;
                }

                table {
                    margin: auto;
                    width: 100%;
                    text-align: center;
                }

                tr:nth-child(even) {
                    background-color: #f2f2f2;
                }

                .logo-section {
                    text-align: center;
                }

                h1 {
                    text-align: center;
                }
            </style>
            <title></title>
        </head>
        <body>
        <div class="logo-section " style="text-align:center;">
            <!-- <img  src="https://booking.arienzobeachclub.com/wp-content/uploads/2020/07/arienzo_logo-100x100.png" alt=""> -->
        </div>
        <div>
            <h1>Arrival Times Report - <?php echo $start_date ?> to <?php echo $end_date ?></h1>
        </div>
        <br/>
        <br/>
        <br/>
        <br/>
        <br/>
        <br/>
        <table>
            <thead>
            <tr>
                <th>Booking Id</th>
                <th>Full Name</th>
                <th>Phone</th>
                <th>Guests</th>
                <th>Price</th>
                <th>Arrival time</th>
                <th>Lunch time</th>
                <th style="max-width: 60px;">Location</th>
                <th>Table</th>
            </tr>
            </thead>
            <tbody>

            <?php foreach ($data as $booking) {
                $id = $booking;
                $metas = get_post_meta($id);
                $guest = $this->get_guest_info2($metas['_mphb_booking_price_breakdown'][0]);

                //$price = $booking->getTotalPrice();

                $mphb_place = get_post_meta($booking,"mphb_place",true);
                 
                $places = array();
                if($mphb_place){
                    foreach ($mphb_place as $key => $value) {
                        foreach ($value as $key => $value) {
                            $places[] = $value;
                        }
                    }
                }
                 //echo "<pre>"; print_r($mphb_place); echo "</pre>";die; 
                $beach_arrival_time = $metas['beach_arrival_time'][0];
                $lunch_time = get_lunch_text($metas['lunch_time'][0]);
                $guests_data = $this->get_guest_count2($metas['_mphb_booking_price_breakdown'][0]);
                $adultsTotal += $guests_data['adultsTotal'];
                $childrenTotal += $guests_data['childrenTotal'];

                

                $table_selected_ids = [];
                $tables = [];

                $ids =  get_post_meta($booking,"arf_cp_table_id",true);;
                if(is_array($ids)){
                    $table_selected_ids = $ids;
                }else{
                    $table_selected_ids[] = $ids;
                }

                if($table_selected_ids) {
                    foreach ($table_selected_ids as $key => $value) {
                        $tables[] = get_the_title($value);
                    }
                }

                $price_breakdown = $metas['_mphb_booking_price_breakdown'][0]; 
                $price = 0;
                $price_breakdown = json_decode(strip_tags($price_breakdown),true);
                if($price_breakdown){
                    if(isset($price_breakdown['total'])){
                        $price += $price_breakdown['total'];
                    }
                    if(isset($price_breakdown['rooms'])){
                        foreach ($price_breakdown['rooms'] as $kk => $value) {
                            $adults += $value['room']['adults']; 
                            $child += $value['room']['children']; 
                            if(isset($value['services']['list'])){
                                foreach ($value['services']['list'] as $key => $vv) {
                                    $service_arr[] = $vv['title']." (".$vv['details'].")";
                                    $sub_total = $vv['details'];
                                }   
                            }
                            /*if(isset($value['services']['total']) && $value['services']['total']){
                                $price += $value['services']['total'];
                            }*/
                            
                        }
                    }
                }

                ?>
                <tr>
                    <td><?php echo $id ?></td>
                    <td style="text-align: left;"><?php echo $metas["mphb_first_name"][0] ?> <?php echo $metas["mphb_last_name"][0] ?></td>
                    <td><?php echo $metas["mphb_phone"][0] ?></td>
                    <td><?php echo $guest ?></td>
                    <td><?php echo mphb_format_price( $price ); ?></td>
                    <td><?php echo $beach_arrival_time ?></td>
                    <td><?php echo $lunch_time; ?></td>
                    <td style="word-break: break-all;max-width: 60px;"><?php echo implode("<br/>", $places) ?></td>
                    <td><?php echo implode("<br/>", $tables) ?></td>
                </tr>
                <tr>
                    <th>Services (<?php echo $id ?>)</th>
                    <td colspan="2"><?php echo $this->generate_services2($booking,$price_breakdown); ?></td>
                    <th>Products</th>
                    <td colspan="5"><?php echo $metas['products_title2'][0]; ?></td>
                </tr>
                <?php if($metas["mphb_note"][0]){?>
                    <tr>
                        <td>Notes (<?php echo $id ?>)</td>
                        <td colspan="8"><?php echo $metas["mphb_note"][0] ?></td>
                    </tr>
                <?php } ?>
            <?php } ?>
            </tbody>
        </table>
        <br>
        <h4 style="text-align:right">Guests:</h4><br/><br/>
        <p style="text-align:right">Adults: <?php echo $adultsTotal; ?></p><br/><br/>
        <p style="text-align:right">Children: <?php echo $childrenTotal ?></p>
        <br>
        </body>
        </html>
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    private function generate_lunch_time_html($data, $start_date, $end_date)
    {
        ob_start();
        $adultsTotal = 0;
        $childrenTotal = 0;
        ?>
        <!doctype html>
        <html lang="en">
        <head>
            <style>
                table, th, td {
                    font-family: Arial;
                    border: 1px solid black;
                    border-spacing: 0;
                }

                table {
                    margin: auto;
                    width: 100%;
                    text-align: center;
                }

                tr:nth-child(even) {
                    background-color: #f2f2f2;
                }

                .logo-section {
                    text-align: center;
                }

                h1 {
                    text-align: center;
                }
            </style>
            <title></title>
        </head>
        <body>
        <!-- <br/>
        <br/>
        <br/>
        <br/>
        <br/> -->
        <div class="logo-section">
            <!-- <img  src="https://booking.arienzobeachclub.com/wp-content/uploads/2020/07/arienzo_logo-100x100.png" alt=""> -->
        </div>
        <div>
            <h1>Lunch Times Report - <?php echo $start_date ?> to <?php echo $end_date ?></h1>
        </div>

        <br/>
        <br/>
        <br/>
        <br/>
        <table>
            <thead>
            <tr>
                <th>Booking Id</th>
                <th>Full Name</th>
                <th>Phone</th>
                <th>Guests</th>
                <th>Price</th>
                <th>Lunch time</th>
                <th style="max-width: 60px;">Location</th>
                <th>Table</th>
            </tr>
            </thead>
            <tbody>

            <?php foreach ($data as $booking) {
                $id = $booking;
                $metas = get_post_meta($id);
                $guest = $this->get_guest_info2($metas['_mphb_booking_price_breakdown'][0]);
                //$price = $booking->getTotalPrice();

                $mphb_place = get_post_meta($booking,"mphb_place",true);
                 
                $places = array();
                if($mphb_place){
                    foreach ($mphb_place as $key => $value) {
                        foreach ($value as $key => $value) {
                            $places[] = $value;
                        }
                    }
                }
                 //echo "<pre>"; print_r($mphb_place); echo "</pre>";die; 
                $lunch_time = get_lunch_text($metas['lunch_time'][0]);
                $guests_data = $this->get_guest_count2($metas['_mphb_booking_price_breakdown'][0]);
                $adultsTotal += $guests_data['adultsTotal'];
                $childrenTotal += $guests_data['childrenTotal'];

                

                $table_selected_ids = [];
                $tables = [];

                $ids =  get_post_meta($booking,"arf_cp_table_id",true);;
                if(is_array($ids)){
                    $table_selected_ids = $ids;
                }else{
                    $table_selected_ids[] = $ids;
                }

                
                if($table_selected_ids) {
                    foreach ($table_selected_ids as $key => $value) {
                        $tables[] = get_the_title($value);
                    }
                }

                $price_breakdown = $metas['_mphb_booking_price_breakdown'][0]; 
                $price = 0;
                $price_breakdown = json_decode(strip_tags($price_breakdown),true);
                if($price_breakdown){
                    if(isset($price_breakdown['total'])){
                        $price += $price_breakdown['total'];
                    }
                    if(isset($price_breakdown['rooms'])){
                        foreach ($price_breakdown['rooms'] as $kk => $value) {
                            $adults += $value['room']['adults']; 
                            $child += $value['room']['children']; 
                            if(isset($value['services']['list'])){
                                foreach ($value['services']['list'] as $key => $vv) {
                                    $service_arr[] = $vv['title']." (".$vv['details'].")";
                                    $sub_total = $vv['details'];
                                }   
                            }
                            /*if(isset($value['services']['total']) && $value['services']['total']){
                                $price += $value['services']['total'];
                            }*/
                            
                        }
                    }
                }

                ?>
                <tr>
                    <td><?php echo $id ?></td>
                    <td style="text-align: left;"><?php echo $metas["mphb_first_name"][0] ?> <?php echo $metas["mphb_last_name"][0] ?></td>
                    <td><?php echo $metas["mphb_phone"][0] ?></td>
                    <td><?php echo $guest ?></td>
                    <td><?php echo mphb_format_price( $price ); ?></td>
                    <td><?php echo $lunch_time ?></td>
                    <td style="word-break: break-all;max-width: 60px;"><?php echo implode("<br/>", $places) ?></td>
                    <td><?php echo implode("<br/>", $tables) ?></td>
                </tr>

                <tr>
                    <th>Services (<?php echo $id ?>)</th>
                    <td colspan="2"><?php echo $this->generate_services2($booking,$price_breakdown); ?></td>
                    <th>Products</th>
                    <td colspan="4"><?php echo $metas['products_title2'][0]; ?></td>
                </tr>

                <?php if($metas["mphb_note"][0]){?>
                    <tr>
                        <td>Notes (<?php echo $id ?>)</td>
                        <td colspan="8"><?php echo $metas["mphb_note"][0] ?></td>
                    </tr>
                <?php } ?>
            <?php } ?>
            </tbody>
        </table>
        <br>
        <h4 style="text-align:right">Guests:</h4><br/><br/>
        <p style="text-align:right">Adults: <?php echo $adultsTotal; ?></p><br/><br/>
        <p style="text-align:right">Children: <?php echo $childrenTotal ?></p>
        <br>
        </body>
        </html>
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    private function generate_lunch_arrival_times_html($data, $start_date, $end_date)
    {
        ob_start();
        $adultsTotal = 0;
        $childrenTotal = 0;
        ?>
        <!doctype html>
        <html lang="en">
        <head>
            <style>
                table, th, td {
                    font-family: Arial;
                    border: 1px solid black;
                    border-spacing: 0;
                }

                table {
                    margin: auto;
                    width: 100%;
                    text-align: center;
                }

                tr:nth-child(even) {
                    background-color: #f2f2f2;
                }

                .logo-section {
                    text-align: center;
                }

                h1 {
                    text-align: center;
                }
            </style>
            <title></title>
        </head>
        <body>
        <!-- <br/>
        <br/>
        <br/>
        <br/>
        <br/> -->
        <div class="logo-section"  style="text-align: center;">
            <!-- <img  src="https://booking.arienzobeachclub.com/wp-content/uploads/2020/07/arienzo_logo-100x100.png" alt=""> -->
        </div>
        <div>
            <h1 style="width:100%;">Lunch and Arrival Times Report - <?php echo $start_date ?> to <?php echo $end_date ?></h1>
        </div>

        <br/>
        <br/>
        <br/>
        <br/>
        <table>
            <thead>
            <tr>
                <th>Booking Id</th>
                <th>Full Name</th>
                <th>Phone</th>
                <th>Guests</th>
                <th>Price</th>
                <th>Arrival time</th>
                <th>Lunch time</th>
                <th style="max-width: 60px;">Location</th>
                <th>Table</th>
            </tr>
            </thead>
            <tbody>

            <?php foreach ($data as $booking) {
                $id = $booking;
                $metas = get_post_meta($id);
                $guest = $this->get_guest_info2($metas['_mphb_booking_price_breakdown'][0]);
                //$price = $booking->getTotalPrice();

                $mphb_place = get_post_meta($booking,"mphb_place",true);
                 
                $places = array();
                if($mphb_place){
                    foreach ($mphb_place as $key => $value) {
                        foreach ($value as $key => $value) {
                            $places[] = $value;
                        }
                    }
                }
                 //echo "<pre>"; print_r($mphb_place); echo "</pre>";die; 
                $beach_arrival_time = $metas['beach_arrival_time'][0];
                $lunch_time = get_lunch_text($metas['lunch_time'][0]);

                $guests_data = $this->get_guest_count2($metas['_mphb_booking_price_breakdown'][0]);
                $adultsTotal += $guests_data['adultsTotal'];
                $childrenTotal += $guests_data['childrenTotal'];

                

                $table_selected_ids = [];
                $tables = [];

                $ids =  get_post_meta($booking,"arf_cp_table_id",true);;
                if(is_array($ids)){
                    $table_selected_ids = $ids;
                }else{
                    $table_selected_ids[] = $ids;
                }

                
                if($table_selected_ids) {
                    foreach ($table_selected_ids as $key => $value) {
                        $tables[] = get_the_title($value);
                    }
                }

                $price_breakdown = $metas['_mphb_booking_price_breakdown'][0]; 
                $price = 0;
                $price_breakdown = json_decode(strip_tags($price_breakdown),true);
                if($price_breakdown){
                    if(isset($price_breakdown['total'])){
                        $price += $price_breakdown['total'];
                    }
                    if(isset($price_breakdown['rooms'])){
                        foreach ($price_breakdown['rooms'] as $kk => $value) {
                            $adults += $value['room']['adults']; 
                            $child += $value['room']['children']; 
                            if(isset($value['services']['list'])){
                                foreach ($value['services']['list'] as $key => $vv) {
                                    $service_arr[] = $vv['title']." (".$vv['details'].")";
                                    $sub_total = $vv['details'];
                                }   
                            }
                            /*if(isset($value['services']['total']) && $value['services']['total']){
                                $price += $value['services']['total'];
                            }*/
                            
                        }
                    }
                }

                ?>
                <tr>
                    <td><?php echo $id ?></td>
                    <td style="text-align: left;"><?php echo $metas["mphb_first_name"][0] ?> <?php echo $metas["mphb_last_name"][0] ?></td>
                    <td><?php echo $metas["mphb_phone"][0] ?></td>
                    <td><?php echo $guest ?></td>
                    <td><?php echo mphb_format_price( $price ); ?></td>
                    <td><?php echo $beach_arrival_time ?></td>
                    <td><?php echo $lunch_time ?></td>
                    <td style="word-break: break-all;max-width: 60px;"><?php echo implode("<br/>", $places) ?></td>
                    <td><?php echo implode("<br/>", $tables) ?></td>
                </tr>
                <tr>
                    <th>Services (<?php echo $id ?>)</th>
                    <td colspan="2"><?php echo $this->generate_services2($booking,$price_breakdown); ?></td>
                    <th>Products</th>
                    <td colspan="5"><?php echo $metas['products_title2'][0]; ?></td>
                </tr>
                <?php if($metas["mphb_note"][0]){?>
                    <tr>
                        <td>Notes (<?php echo $id ?>)</td>
                        <td colspan="9"><?php echo $metas["mphb_note"][0] ?></td>
                    </tr>
                <?php } ?>
            <?php } ?>
            </tbody>
        </table>
        <br>
        <h4 style="text-align:right">Guests:</h4><br/><br/>
        <p style="text-align:right">Adults: <?php echo $adultsTotal; ?></p><br/><br/>
        <p style="text-align:right">Children: <?php echo $childrenTotal ?></p>
        <br>
        </body>
        </html>
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    private function generate_arrival_time_pdf($bookings, $start_date, $end_date)
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'landscape');
        if (!$html = $this->generate_arrival_time_html($bookings, $start_date, $end_date)) {
            return;
        }
        $dompdf->load_html($html);
        $dompdf->render();
        $output = $dompdf->output();
        wp_mkdir_p(ARF_REPORTS_UPLOAD_DIR);
        $file_name = $this->fileNameAT;
        $file_path = ARF_REPORTS_UPLOAD_DIR . '/' . $file_name;
        file_put_contents($file_path, $output);
        $pdf_url = ARF_REPORTS_UPLOAD_URL . $file_name;
        $pdf_dir = ARF_REPORTS_UPLOAD_DIR . $file_name;
		
        wp_send_json_success($this->generate_download_url('arrival_time'),200);
    }

    private function generate_lunch_time_pdf($bookings, $start_date, $end_date)
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'landscape');
        if (!$html = $this->generate_lunch_time_html($bookings, $start_date, $end_date)) {
            return;
        }

        $dompdf->load_html($html);
        $dompdf->render();
        $output = $dompdf->output();
        wp_mkdir_p(ARF_REPORTS_UPLOAD_DIR);
        $file_name = $this->fileNameLT;
        $file_path = ARF_REPORTS_UPLOAD_DIR . '/' . $file_name;
        file_put_contents($file_path, $output);
        $pdf_url = ARF_REPORTS_UPLOAD_URL . $file_name;
        $pdf_dir = ARF_REPORTS_UPLOAD_DIR . $file_name;
        wp_send_json_success($this->generate_download_url('lunch_time'),200);
    }

    private function generate_lunch_arrival_times_pdf($bookings, $start_date, $end_date)
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'landscape');

        if (!$html = $this->generate_lunch_arrival_times_html($bookings, $start_date, $end_date)) {
            return;
        }

        $dompdf->load_html($html);
        $dompdf->render();
        $output = $dompdf->output();
        wp_mkdir_p(ARF_REPORTS_UPLOAD_DIR);
        $file_name = $this->fileNameLAT;
        $file_path = ARF_REPORTS_UPLOAD_DIR . '/' . $file_name;
        file_put_contents($file_path, $output);
        $pdf_url = ARF_REPORTS_UPLOAD_URL . $file_name;
        $pdf_dir = ARF_REPORTS_UPLOAD_DIR . $file_name;
        wp_send_json_success($this->generate_download_url('lunch_arrival_times'),200);
    }

    public function init()
    {
        $args = array(
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            "room" => "-1",
            "status" => "all",
            "search_by" => "reserved-rooms",
            "columns" => array(
                "booking-id",
                "booking-status",
                "check-in",
                "check-out",
                "room-type",
                "room-type-id",
                "room",
                "rate",
                "adults",
                "children",
                "services",
                "first-name",
                "last-name",
                "email",
                "phone",
                "country",
                "address",
                "city",
                "state",
                "postcode",
                "customer-note",
                "guest-name",
                "coupon",
                "price",
                "paid",
                "payments",
                "date",
                "lunch_time",
                "beach_arrival_time",
            )
        );

        $query = new \MPHB\CSV\Bookings\BookingsQuery($args);
        if ($query->hasErrors()) {
            wp_send_json_error(array('message' => $query->getErrorMessage()));
        } else {
            $args = $query->getInputs(); // Get validated inputs
        }

        $attr = array(
            'posts_per_page' => -1,
            'post_type' => 'mphb_booking',
            'post_status' => array('confirmed','paid_not_refundable','paid_refundable','last_minute','pending_late_charge'),
            'fields' => 'ids',
            'order' => 'asc',
            'orderby' => 'meta_value',
            'meta_key' => 'mphb_first_name',
            'meta_query' => array()
        );
        
        if ($args['start_date'] != $args['end_date']) {
            $attr['meta_query'][] = array(
                'key' => 'mphb_check_out_date',
                'value' => $args['end_date'],
                'compare' => '<=',
            );
            $attr['meta_query'][] = array(
                    'key' => 'mphb_check_in_date',
                    'value' => $args['start_date'],
                    'compare' => '>=',
            );
        } else {
            $attr['meta_query'][] = array(
                'key' => 'mphb_check_in_date',
                'value' => $args['start_date'],
                'compare' => '=',
            );
        }
        //print_r($attr);die;

        $query = new WP_Query($attr);
        $ids = $query->posts;

        /*$ids = array();
        global $wpdb;
        if ($this->start_date != $this->end_date) {
            $where = "`meta_key` = 'mphb_check_in_date' AND date(meta_value) >= '".$this->start_date."' AND date(meta_value) <= '".$this->end_date."' ";
        } else {
            $where = "`meta_key` = 'mphb_check_in_date' AND date(meta_value) >= '".$this->start_date."' ";
        }
        $sql = "
            SELECT ID
            FROM  ".$wpdb->prefix."posts
            LEFT JOIN $wpdb->postmeta ON (".$wpdb->prefix."posts.ID = ".$wpdb->postmeta.".post_id)
                WHERE ".$where." AND post_status IN ('confirmed','paid_not_refundable','paid_refundable','last_minute','pending_late_charge') AND post_type = 'mphb_booking'
        ";
         echo "<pre>"; print_r($sql); echo "</pre>";die; 
        $booking_ids = $wpdb->get_results ($sql);

        foreach ($booking_ids as $key => $value) {
            $ids[] = $value->ID;
        }*/


        if (empty($ids)) {
            wp_send_json_error(array('message' => __('No bookings found for your request.', 'motopress-hotel-booking')));
        }
        //$bookings = MPHB()->getBookingRepository()->findAll(array('post__in' => $ids));
         
        if ($this->fileType == "arrival_time") {
            $this->generate_arrival_time_pdf($ids, $this->start_date, $this->end_date);
        } else if ($this->fileType == "lunch_time") {
            $this->generate_lunch_time_pdf($ids, $this->start_date, $this->end_date);
        } else if ($this->fileType == "lunch_arrival_times") {
            $this->generate_lunch_arrival_times_pdf($ids, $this->start_date, $this->end_date);
        }
    }

    public function init_product()
    {
        $args = array(
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            "room" => "-1",
            "status" => "all",
            "search_by" => "reserved-rooms",
            "columns" => array(
                "booking-id",
                "booking-status",
                "check-in",
                "check-out",
                "room-type",
                "room-type-id",
                "room",
                "rate",
                "adults",
                "children",
                "services",
                "first-name",
                "last-name",
                "email",
                "phone",
                "country",
                "address",
                "city",
                "state",
                "postcode",
                "customer-note",
                "guest-name",
                "coupon",
                "price",
                "paid",
                "payments",
                "date",
                "lunch_time",
                "beach_arrival_time",
            )
        );

        $query = new \MPHB\CSV\Bookings\BookingsQuery($args);
        if ($query->hasErrors()) {
            wp_send_json_error(array('message' => $query->getErrorMessage()));
        } else {
            $args = $query->getInputs(); // Get validated inputs
        }

        $attr = array(
            'posts_per_page' => -1,
            'post_type' => 'mphb_booking',
            'fields' => 'ids',
            'meta_query' => array()
        );
        /*if($this->status && ((is_array($this->status) && in_array("all", $this->status)) || ($this->status != "all"))){
        }*/
        $attr['post_status'] = array(
            'confirmed','confirmed-archived','paid_not_refundable','paid_refundable','last_minute','pending_late_charge'
        );

        if ($args['start_date'] != $args['end_date']) {
            $attr['meta_query'][] = array(
                'key' => 'mphb_check_out_date',
                'value' => $args['end_date'],
                'compare' => '<=',
            );
            $attr['meta_query'][] = array(
                    'key' => 'mphb_check_in_date',
                    'value' => $args['start_date'],
                    'compare' => '>=',
            );
        } else {
            $attr['meta_query'][] = array(
                'key' => 'mphb_check_in_date',
                'value' => $args['start_date'],
                'compare' => '=',
            );
        }

        $query = new WP_Query($attr);
        $ids = $query->posts;
        /*echo "<pre>"; print_r($ids); echo "</pre>";
        echo "<pre>"; print_r($attr); echo "</pre>";die; */
        if (empty($ids)) {
            wp_send_json_error(array('message' => __('No bookings found for your request.', 'motopress-hotel-booking')));
        }
        $bookings = MPHB()->getBookingRepository()->findAll(array('post__in' => $ids));
        $this->generate_product_pdf($bookings, $this->start_date, $this->end_date);

    }

    public function init_product2()
    {
        $args = array(
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            "room" => "-1",
            "status" => "all",
            "search_by" => "reserved-rooms",
            "columns" => array(
                "booking-id",
                "booking-status",
                "check-in",
                "check-out",
                "room-type",
                "room-type-id",
                "room",
                "rate",
                "adults",
                "children",
                "services",
                "first-name",
                "last-name",
                "email",
                "phone",
                "country",
                "address",
                "city",
                "state",
                "postcode",
                "customer-note",
                "guest-name",
                "coupon",
                "price",
                "paid",
                "payments",
                "date",
                "lunch_time",
                "beach_arrival_time",
            )
        );

        $query = new \MPHB\CSV\Bookings\BookingsQuery($args);
        if ($query->hasErrors()) {
            wp_send_json_error(array('message' => $query->getErrorMessage()));
        } else {
            $args = $query->getInputs(); // Get validated inputs
        }

        $attr = array(
            'posts_per_page' => -1,
            'post_type' => 'mphb_booking',
            'fields' => 'ids',
            'meta_query' => array()
        );
        /*if($this->status && ((is_array($this->status) && in_array("all", $this->status)) || ($this->status != "all"))){
        }*/
        $attr['post_status'] = array(
            'confirmed','confirmed-archived','paid_not_refundable','paid_refundable','last_minute','pending_late_charge'
        );

        if ($args['start_date'] != $args['end_date']) {
            $attr['meta_query'][] = array(
                'key' => 'mphb_check_out_date',
                'value' => $args['end_date'],
                'compare' => '<=',
            );
            $attr['meta_query'][] = array(
                    'key' => 'mphb_check_in_date',
                    'value' => $args['start_date'],
                    'compare' => '>=',
            );
        } else {
            $attr['meta_query'][] = array(
                'key' => 'mphb_check_in_date',
                'value' => $args['start_date'],
                'compare' => '=',
            );
        }

        $query = new WP_Query($attr);
        $ids = $query->posts;
        /*echo "<pre>"; print_r($ids); echo "</pre>";
        echo "<pre>"; print_r($attr); echo "</pre>";die; */
        if (empty($ids)) {
            wp_send_json_error(array('message' => __('No bookings found for your request.', 'motopress-hotel-booking')));
        }
        $bookings = MPHB()->getBookingRepository()->findAll(array('post__in' => $ids));
        $this->generate_product_pdf2($bookings, $this->start_date, $this->end_date);

    }

    private function generate_download_url($type) {
        return add_query_arg(
            array(
                'arf_action' => 'arf_pdf_download',
                'fileType'    => $type
            ),
            admin_url()
        );
    }

    private function get_guest_count($booking)
    {
        $reservedRooms = $booking->getReservedRooms();
        $response = ['adultsTotal' => 0, 'childrenTotal' => 0];
        if (!empty($reservedRooms) && !$booking->isImported()) {
            $adultsTotal = 0;
            $childrenTotal = 0;

            foreach ($reservedRooms as $reservedRoom) {
                $adultsTotal += $reservedRoom->getAdults();
                $childrenTotal += $reservedRoom->getChildren();
            }

            $response['adultsTotal'] = $adultsTotal;
            if ($childrenTotal > 0) {
                $response['childrenTotal'] = $childrenTotal;
            }
        }
        return $response;
    }   
    private function get_guest_count2($reservedRooms)
    {
        $reservedRooms = json_decode(strip_tags($reservedRooms),1);
        $response = ['adultsTotal' => 0, 'childrenTotal' => 0];
        if (!empty($reservedRooms)) {
            $adultsTotal = 0;
            $childrenTotal = 0;
            foreach ($reservedRooms['rooms'] as $reservedRoom) {
                $adultsTotal += isset($reservedRoom['room']['adults']) ? $reservedRoom['room']['adults'] : 0;
                $childrenTotal += isset($reservedRoom['room']['children']) ? $reservedRoom['room']['children'] : 0;
            }

            $response['adultsTotal'] = $adultsTotal;
            if ($childrenTotal > 0) {
                $response['childrenTotal'] = $childrenTotal;
            }
        }
        return $response;

    }   
    private function generate_product_pdf($bookings, $start_date, $end_date){
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'landscape');
        if (!$html = $this->generate_product_pdf_html($bookings, $start_date, $end_date)) {
            return;
        }
        $dompdf->load_html($html);
        $dompdf->render();
        $output = $dompdf->output();
        wp_mkdir_p(ARF_REPORTS_UPLOAD_DIR);
        $file_name = $this->fileNamePD;
        $file_path = ARF_REPORTS_UPLOAD_DIR . '/' . $file_name;
        file_put_contents($file_path, $output);
        $pdf_url = ARF_REPORTS_UPLOAD_URL . $file_name;
        $pdf_dir = ARF_REPORTS_UPLOAD_DIR . $file_name;
        
        wp_send_json_success($this->generate_download_url('product'),200);
    }
    private function generate_product_pdf2($bookings, $start_date, $end_date){
        $totalSold = 0;
        $array[] = array('Periodo','Nome Prodotto','Servizio abbinato al prodotto','Quantità venduta','Quantità disponibile','Prezzo medio del prodotto','Totale guadagno');

        foreach ($bookings as $key => $value) {
            $products_qty = get_post_meta($value->getId(),"products_qty",1);
            $products_price_total = get_post_meta($value->getId(),"products_price_total",1);
            $products = get_post_meta($value->getId(),"products",1);
            $products_title = get_post_meta($value->getId(),"products_title",1);
            $products_title2 = get_post_meta($value->getId(),"products_title2",1);
            $products_title2 = $products_title2 ? explode("<br/>", $products_title2) : array();
            if($products){
                $key = 0;
                foreach ($products as $key => $value) {
                    if(isset($products_qty[$value]) && isset($products_title2[$key])){
                        /*$products_title2[$key]

                        $title = explode("delimiter", string)*/
                        $title = $products_title2[$key];
                        $pos = strrpos($title, " x ");
                        if($pos){
                            $title = substr($title, 0, $pos);
                        }
                        $gpminvoice_group = get_post_meta($value, 'availability_range', true);
                        $packagess = get_post_meta($value, 'packages', true);
                        if(empty($array[$value])){
                            $period = array();
                            foreach ($gpminvoice_group as $kkk => $vvv) {
                                $period[] = $vvv['startdate']." ".$vvv['enddate'];
                            }
                            $packages = array();
                            foreach ($packagess as $kkk => $vvv) {
                                $packages[] = get_the_title($vvv);
                            }
                            //$period = implode(",", $period);
                            $period = "From ".$start_date." To ".$end_date;
                            $array[$value]['periodo'] = $period;
                            $array[$value]['nome_prodotto'] = get_the_title($value);
                            $array[$value]['servizio_abbinato_al_prodotto'] = implode(",", $packages);
                            $array[$value]['qty'] = 0;
                            $array[$value]['avail_qty'] = get_post_meta($value,"stock",1);
                            $array[$value]['prezzo_medio_del_prodotto'] = 0;
                            $array[$value]['totale_guadagno'] = 0;
                        }
                        $totalSold += $products_qty[$value];
                        $array[$value]['qty'] += $products_qty[$value];
                        $array[$value]['totale_guadagno'] += $products_price_total;
                        $key++;
                    }
                }
            }
        }


        wp_mkdir_p(ARF_REPORTS_UPLOAD_DIR);
        $file_name = $this->fileNamePD2;
        $file_path = ARF_REPORTS_UPLOAD_DIR . '/' . $file_name;

        file_put_contents($file_path, "");

        // very simple to increment with i++ if looping through a database result 
        
        
        $fp = fopen($file_path, 'wb');
        foreach ($array as $line) {
            if($line['qty'] && $line['totale_guadagno']){
                $line['prezzo_medio_del_prodotto'] = round($line['totale_guadagno'] / $line['qty'],2);
            }
            // though CSV stands for "comma separated value"
            // in many countries (including France) separator is ";"
            fputcsv($fp, $line, ',');
        }
        fclose($fp);
        
        $pdf_url = ARF_REPORTS_UPLOAD_URL . $file_name;
        $pdf_dir = ARF_REPORTS_UPLOAD_DIR . $file_name;
        
        wp_send_json_success($this->generate_download_url('product2'),200);
    }
    private function generate_product_pdf_html($data, $start_date, $end_date)
    {
        ob_start();
        $totalSold = 0;
        $array = array();
        foreach ($data as $key => $value) {
            $products_qty = get_post_meta($value->getId(),"products_qty",1);
            $products_price_total = get_post_meta($value->getId(),"products_price_total",1);
            $products = get_post_meta($value->getId(),"products",1);
            $products_title = get_post_meta($value->getId(),"products_title",1);
            $products_title2 = get_post_meta($value->getId(),"products_title2",1);
            $products_title2 = $products_title2 ? explode("<br/>", $products_title2) : array();
            if($products){
                $key = 0;
                foreach ($products as $key => $value) {
                    if(isset($products_qty[$value]) && isset($products_title2[$key])){
                        /*$products_title2[$key]

                        $title = explode("delimiter", string)*/
                        $title = $products_title2[$key];
                        $pos = strrpos($title, " x ");
                        if($pos){
                            $title = substr($title, 0, $pos);
                        }
                        if(empty($array[$value])){
                            $array[$value]['qty'] = 0;
                            $array[$value]['avail_qty'] = get_post_meta($value,"stock",1);
                            $array[$value]['title'] = get_the_title($value);
                        }
                        $totalSold += $products_qty[$value];
                        $array[$value]['qty'] += $products_qty[$value];
                        $key++;
                    }
                }
            }
        }
         //echo "<pre>"; print_r($array); echo "</pre>";die;  
        ?>
        <!doctype html>
        <html lang="en">
        <head>
            <style>
                table, th, td {
                    font-family: Arial;
                    border: 1px solid black;
                    border-spacing: 0;
                }

                table {
                    margin: auto;
                    width: 100%;
                    text-align: center;
                }

                tr:nth-child(even) {
                    background-color: #f2f2f2;
                }

                .logo-section {
                    text-align: center;
                }

                h1 {
                    text-align: center;
                }
            </style>
            <title></title>
        </head>
        <body>
        <!-- <br/>
        <br/>
        <br/>
        <br/>
        <br/> -->
        <div class="logo-section">
            <!-- <img  src="https://booking.arienzobeachclub.com/wp-content/uploads/2020/07/arienzo_logo-100x100.png" alt=""> -->
        </div>
        <div>
            <h1>Report - <?php echo $start_date ?> to <?php echo $end_date ?></h1>
        </div>
        <br>
        <br>
        <br>
        <br>
        <table cellspacing="0">
            <thead>
            <tr>
                <th>Product</th>
                <th>Sold</th>
                <th>Avail Quntity</th>
            </tr>
            </thead>
            <tbody>

            <?php foreach ($array as $kk => $vv) {
                ?>
                <tr>
                    <td><?php echo $vv['title'] ?></td>
                    <td><?php echo $vv['qty'] ?></td>
                    <td><?php echo $vv['avail_qty'] ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <br>
        
        <p style="text-align:right">Total Sold: <?php echo $totalSold; ?></p>
        
        <br>
        </body>
        </html>
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

}