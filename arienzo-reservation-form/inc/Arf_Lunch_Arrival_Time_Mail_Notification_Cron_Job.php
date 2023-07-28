<?php
include( plugin_dir_path( __FILE__ ) . 'dompdf/autoload.inc.php');
// Set up directory to save PDF
$upload_dir = wp_upload_dir();
define('ARF_REPORTS_UPLOAD_DIR', $upload_dir['basedir'] . '/arf_reports/');
define('ARF_REPORTS_UPLOAD_URL', $upload_dir['baseurl'] . '/arf_reports/');

use Dompdf\Dompdf;
use Dompdf\Options;

class ARF_LUNCH_ARRIVAL_TIME_MAIL_NOTIFICATION_CRON_JOB
{
    public function __construct()
    {

    }


    private function modifyDateFormat($date, $format = 'Y-m-d')
    {
        $date = new DateTime($date);
        //$date->modify('+1 day');
        return $date->format($format);
    }
    /**
     * Get Current Day Value
     */
    private function getDate() {
        return date('Y-m-d');
    }

    /**
     * Set Wp Query Attributes
     * @return array
     */
    private function getQueryAttr($date) {
        return array(
            'posts_per_page' => -1,
            'post_type' => 'mphb_booking',
            'post_status' => 'confirmed',
            'meta_key' => 'mphb_check_in_date',
            'meta_value' => $date,
            'fields' => 'ids',
            'orderBy' => 'id',
            'order' => 'DESC'
        );
    }

    /**
     * Generate Lunch Time Html
     * @param $date
     * @param $data
     * @return false|string
     */
    private function generate_lunch_time_html($date, $data)
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
                    border: 1px solid black;
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
        <div class="logo-section">
            <img src="https://localhost/arienzobeachclub/wp-content/uploads/2020/07/arienzo_logo-100x100.png" alt="">
        </div>
        <h1>Lunch times Daily Report - <?php echo $date ?></h1>
        <table>
            <thead>
            <tr>
                <th>Booking Id</th>
                <th>Full Name</th>
                <th>Phone</th>
                <th>Guests</th>
                <th>Price</th>
                <th>Lunch Time</th>
                <th>Services</th>
                <th width="200">Notes</th>
            </thead>
            <tbody>
            <?php foreach ($data as $val) {
                $adultsTotal +=$val['guests_data']['adultsTotal'];
                $childrenTotal += $val['guests_data']['childrenTotal'];
                ?>
                <tr>
                    <td><?php echo $val['id'] ?></td>
                    <td style="text-align: left;"><?php echo $val['full_name'] ?></td>
                    <td><?php echo $val['phone'] ?></td>
                    <td><?php echo $val['guests'] ?></td>
                    <td><?php echo $val['price'] ?></td>

                    <td><?php echo get_lunch_text($val['lunch_time']) ?></td>
                    <td><?php echo $this->generate_services($val['data']) ?></td>
                    <td></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <h4 style="text-align:right">Guests - Adults: <?php echo $adultsTotal; ?>; Children: <?php echo $childrenTotal ?></h4>
        <br>
        </body>
        </html>
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    /**
     * Generate Arrival time Html
     * @param $date
     * @param $data
     * @return false|string
     */
    private function generate_arrival_time_html($date, $data)
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
                    border: 1px solid black;
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
        <div class="logo-section">
            <img src="https://localhost/arienzobeachclub/wp-content/uploads/2020/07/arienzo_logo-100x100.png" alt="">
        </div>
        <h1>Arrival Times Daily Report - <?php echo $date ?></h1>
        <table>
            <thead>
            <tr>
                <th>Booking Id</th>
                <th>Full Name</th>
                <th>Phone</th>
                <th>Guests</th>
                <th>Price</th>
                <th>Arrival time</th>
                <th>Services</th>
                <th width="200">Notes</th>
            </thead>
            <tbody>
            <?php foreach ($data as $val) {
                $adultsTotal +=$val['guests_data']['adultsTotal'];
                $childrenTotal += $val['guests_data']['childrenTotal'];
                ?>
                <tr>
                    <td><?php echo $val['id'] ?></td>
                    <td style="text-align: left;"><?php echo $val['full_name'] ?></td>
                    <td><?php echo $val['phone'] ?></td>
                    <td><?php echo $val['guests'] ?></td>
                    <td><?php echo $val['price'] ?></td>

                    <td><?php echo $val['beach_arrival_time'] ?></td>
                    <td><?php echo $this->generate_services($val['data']); ?></td>
                    <td></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
		<h4 style="text-align:right">Guests - Adults: <?php echo $adultsTotal; ?>; Children: <?php echo $childrenTotal ?></h4>
        <br>
        </body>
        </html>
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    /**
     * Arrival And Lunch times html
     * @param $date
     * @param $data
     * @return false|string
     */
    private function generate_full_html($date, $data)
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
                    border: 1px solid black;
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
        <div class="logo-section">
            <img src="https://localhost/arienzobeachclub/wp-content/uploads/2020/07/arienzo_logo-100x100.png" alt="">
        </div>
        <h1>Arrival and Lunch Times Daily Report - <?php echo $date ?></h1>
        <table>
            <thead>
            <tr>
                <th>Booking Id</th>
                <th>Full Name</th>
                <th>Phone</th>
                <th>Price</th>
                <th>Guests</th>
                <th>Lunch time</th>
                <th>Arrival time</th>
                <th>Services</th>
                <th width="200">Notes</th>
            </thead>
            <tbody>
            <?php foreach ($data as $val) {
                $adultsTotal +=$val['guests_data']['adultsTotal'];
                $childrenTotal += $val['guests_data']['childrenTotal'];
                ?>
                <tr>
                    <td><?php echo $val['id'] ?></td>
                    <td style="text-align: left;"><?php echo $val['full_name'] ?></td>
                    <td><?php echo $val['phone'] ?></td>
                    <td><?php echo $val['price'] ?></td>
                    <td><?php echo $val['guests'] ?></td>
                    <td><?php echo get_lunch_text($val['lunch_time']) ?></td>
                    <td><?php echo $val['beach_arrival_time'] ?></td>
                    <td><?php echo $this->generate_services($val['data']); ?></td>
                    <td></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <h4 style="text-align:right">Guests - Adults: <?php echo $adultsTotal; ?>; Children: <?php echo $childrenTotal ?></h4>
        <br><br><br><br>
        <hr><br>
        <hr><br>
        <hr><br>
        <hr><br>
        <hr><br>
        <hr><br>
        <hr><br>
        <hr><br>
        <hr><br>
        <hr><br>

        </body>
        </html>
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    /**
     * Generate Booking Page Url on Admin Dashbaord
     * @param $id
     * @return string
     */
    private function booking_page_url($id)
    {
        $path = 'post.php?post=%d&action=edit&classic-editor';
        $txt = sprintf($path, $id);
        $url = admin_url($txt);
        return "<a href='{$url}' target='_blank'>View</a>";
    }

    /**
     * Get Guest Info
     * @param $booking
     * @return string
     */
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

    private function data() {
        $today = $this->getDate();
        $date = $this->modifyDateFormat($today);
        $query = new WP_Query($this->getQueryAttr($date));

        $data = array();
        if ($query->have_posts()) {
            $ids = $query->posts;
            foreach ($ids as $id) {
                $metas = get_post_meta($id);

                $first_name = $metas['mphb_first_name'][0];
                $last_name = $metas['mphb_last_name'][0];
                $phone = $metas['mphb_phone'][0];
                $email = $metas['mphb_email'][0];
                $lunch_time = $metas['lunch_time'][0];
                $beach_arrival_time = $metas['beach_arrival_time'][0];
                $booking = MPHB()->getBookingRepository()->findById($id, true);

                $data[$id] = array(
                    'id' => $id,
                    'full_name' => $first_name . " " . $last_name,
                    'phone' => $phone,
                    'email' => $email,
                    'guests' => $this->get_guest_info($booking),
                    'price' => $booking->getTotalPrice(),
                    'lunch_time' => $lunch_time,
                    'beach_arrival_time' => $beach_arrival_time,
                    'url' => $this->booking_page_url($id),
                    'data' => $booking,
                    'guests_data' => $this->get_guest_count($booking)
                );
            }
        }
        return $data;
    }

    public function generate_lunch_time_pdf()
    {
        $today = $this->getDate();
        $date = $this->modifyDateFormat($today);
        $data = $this->data();
        // validate and sanitize your input
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'landscape');
        if (!$html = $this->generate_lunch_time_html($date, $data)) {
            return;
        }
        $dompdf->load_html($html);
        $dompdf->render();
        $output = $dompdf->output();
        wp_mkdir_p(ARF_REPORTS_UPLOAD_DIR);
        $file_name = 'order-lunch-time-' . $date . '.pdf';
        $file_path = ARF_REPORTS_UPLOAD_DIR . '/' . $file_name;
        file_put_contents($file_path, $output);
        $pdf_url = ARF_REPORTS_UPLOAD_URL . $file_name;
        $pdf_dir = ARF_REPORTS_UPLOAD_DIR . $file_name;
        return array("url" => $pdf_url, "dir" => $pdf_dir);
    }

    public function generate_arrival_time_pdf()
    {
        $today = $this->getDate();
        $date = $this->modifyDateFormat($today);
        $data = $this->data();
        // validate and sanitize your input
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'landscape');
        if (!$html = $this->generate_arrival_time_html($date, $data)) {
            return;
        }

        $dompdf->load_html($html);
        $dompdf->render();
        $output = $dompdf->output();
        wp_mkdir_p(ARF_REPORTS_UPLOAD_DIR);
        $file_name = 'order-arrival-time-' . $date . '.pdf';
        $file_path = ARF_REPORTS_UPLOAD_DIR . '/' . $file_name;
        file_put_contents($file_path, $output);
        $pdf_url = ARF_REPORTS_UPLOAD_URL . $file_name;
        $pdf_dir = ARF_REPORTS_UPLOAD_DIR . $file_name;
        return array("url" => $pdf_url, "dir" => $pdf_dir);
    }

    public function generate_full_pdf()
    {
        $today = $this->getDate();
        $date = $this->modifyDateFormat($today);
        $data = $this->data();
        // validate and sanitize your input
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'landscape');

        if (!$html = $this->generate_full_html($date, $data)) {
            return;
        }
        $dompdf->load_html($html);
        $dompdf->render();
        $output = $dompdf->output();
        wp_mkdir_p(ARF_REPORTS_UPLOAD_DIR);
        $file_name = 'order-full-' . $date . '.pdf';
        $file_path = ARF_REPORTS_UPLOAD_DIR . '/' . $file_name;
        file_put_contents($file_path, $output);
        $pdf_url = ARF_REPORTS_UPLOAD_URL . $file_name;
        $pdf_dir = ARF_REPORTS_UPLOAD_DIR . $file_name;
        return array("url" => $pdf_url, "dir" => $pdf_dir);
    }

    public function init() {
        $data = $this->data();
        if(count($data) < 1)
            return;
        $today = $this->getDate();

        $date = $this->modifyDateFormat($today);

        // Qadisha - Commented out. The function doesnt exist
        $lunch_time_pdf = $this->generate_lunch_time_pdf();
        $arrival_time_pdf = $this->generate_arrival_time_pdf();
        $full_pdf = $this->generate_full_pdf();
        $attachments = array($lunch_time_pdf['dir'], $arrival_time_pdf['dir']);

        // Qadisha - Added BCC
        $headers = 'From: <reservation@arienzobeachclub.com>' . "\r\n" . 'BCc: Innova.Menu <info@qadisha.it>' . "\r\n";
        $subject_lunch_time = "Daily Lunch Times Report - " . $date;
        $subject_arrival_time = "Daily Arrival Times Report - " . $date;
        $subject_full = "Daily Arrival Time and Lunch Times Report - " . $date;
        $message = "Attached PDF File";
        $mail_to = 'reservation@arienzobeachclub.com';

        wp_mail($mail_to, $subject_lunch_time, $message, $headers, $lunch_time_pdf['dir']);
        wp_mail($mail_to, $subject_arrival_time, $message, $headers, $arrival_time_pdf['dir']);
        wp_mail($mail_to, $subject_full, $message, $headers, $full_pdf['dir']);
    }

    private function generate_services($booking) {
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
        $replaceText = ob_get_contents();
        ob_end_clean();
        return $replaceText;
    }
}
