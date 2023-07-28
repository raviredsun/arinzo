<?php

require_once 'Arf_Send_Mail.php';



class Arf_Order_Detail

{

    private $arf_order_id;



    public function __construct($order_id, $payment_status = "")

    {

        $this->arf_order_id = esc_sql($order_id);

        $payment_status = esc_sql($payment_status);

        if ($this->arf_order_id && $payment_status)

            $this->change_payment_status($this->arf_order_id, $payment_status);

        $this->details_page();

    }



    public function details_page()

    {

        global $wpdb;

        $arf = apply_filters('arf_database', $wpdb);

        $table_name = $arf->prefix . 'arf_orders';

        if (is_numeric($this->arf_order_id)) {

            $result = $arf->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = $this->arf_order_id  LIMIT 1"), OBJECT);

        }



        if (empty($result)) {

            wp_die($message = 'Not valid contact form');

        }

        $user = $result->user_firstname . " " . $result->user_lastname;

        $payment_status = $result->payment_status;

        $url = sprintf("admin.php?page=arf-orders-list.php&id=%d", $this->arf_order_id);

        $lunch = $result->lunch_time;

            if($lunch == '11:59'){

                $lunchoutput = 'Lunch at your sunbed';

            } else {

                $lunchoutput = $result->lunch_time;

            }

                

        ?>

        <div class="wrap">

            <div id="welcome-panel" class="welcome-panel">

                <div class="welcome-panel-content">

                    <div class="welcome-panel-column-container">

                        <a href="<?php echo admin_url('admin.php?page=arf-orders-list.php') ?>" class="button-primary"><

                            Back</a>

                        <h3>Order - &#35 <?php echo $result->id; ?></h3>

                        <p>User - <?php echo $user; ?></p>

                        <p>Email - <?php echo $result->user_email; ?></p>

                        <p>Phone - <a

                                    href="tel:<?php echo $result->user_phone; ?>"><?php echo $result->user_phone; ?></a>

                        </p>

                        <p>Reservation Date - <?php echo $result->reservation_start_date; ?></p>

                        <p>Beach Arrival Time - <?php echo $result->beach_arrival_time; ?></p>

                        <p>Lunch Time - <?php echo $lunchoutput; ?></p>

                        <p>Adults - <?php echo $result->people; ?></p>

                        <p>Child - <?php echo $result->child; ?></p>

                        <hr>

                        <p>Created Date - <?php echo $result->created_date; ?></p>

                        <p>Updated Date - <?php echo $result->updated_date; ?></p>



                        <hr>



                        <form action="admin.php" method="get">

                            <input type="hidden" name="page" value="arf-orders-list.php">

                            <input type="hidden" name="id" value="<?php echo $this->arf_order_id; ?>">

                            <p>Payment Status - <select name="payment_status" id="">

                                    <option value="pending" <?php selected($payment_status, 'pending'); ?>>Pending

                                    </option>

                                    <option value="approved" <?php selected($payment_status, 'approved'); ?>>Approved

                                    </option>

                                    <option value="declined" <?php selected($payment_status, 'declined'); ?>>Declined

                                    </option>

                                </select>

                                <button type="submit" class="button-primary">Update</button>

                            </p>

                        </form>



                    </div>

                </div>

            </div>

        </div>

        <?php

    }



    protected function change_payment_status($id, $status)

    {

        global $wpdb;

        $arf = apply_filters('arf_database', $wpdb);

        $table_name = $arf->prefix . 'arf_orders';

        $updated_date = current_time('Y-m-d H:i:s');

        $result = $wpdb->update(

            $table_name,

            array(

                'payment_status' => $status,

                'updated_date' => $updated_date,

            ),

            array('id' => $id)

        );

        if ($result) {

            $order = $arf->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = $id"));

            $admin_email = get_option('admin_email');

            $user = $order->user_firstname . " " . $order->user_lastname;

            $reservation_date = $order->reservation_start_date . " > " . $order->reservation_end_date;



            $data = array(

                "from" => $admin_email,

                "to" => $order->user_email,

            );

            if ($status == "approved") {

                $data["subject"] = "Arienzo Beach Club - Reservation confirmed";

                $data["message"] = '

                Hello ' . $user . ' your reservation for ' . $reservation_date . ' has been confirmed.<br>

                At the entrance you will only have to show the QrCode that you find in this email and then you will be able to access the beach.<br> 

                We are waiting for you to Arienzo Beach Club!';



                $this->send_mail($data);

            } elseif ($status == "declined") {

            }

        }

    }



    protected function send_mail($data)

    {

        $arfSendMail = new arf_Send_Mail($data);

        $arfSendMail->send();

    }



}