<?php
class ARF_BOOKING_MAIL_NOTIFICATION_CRON_JOB {
    private function modifyDateFormat($date, $format = 'Y-m-d')
    {
        $date = new DateTime($date);
        $date->modify('+1 day');
        return $date->format($format);
    }

    private function sendCustomerMail($email)
    {
        return MPHB()->emails()->getMailer()->send(
            $email,
            $this->getSubject("mphb_email_customer_pending_booking_subject"),
            $this->getMessage("mphb_email_customer_pending_booking_header", "mphb_email_customer_pending_booking_content", "mphb_email_footer_text"));
    }

    public function init() {
        $today = date('Y-m-d');
        $tomorrow = $this->modifyDateFormat($today);

        $atts = array(
            'posts_per_page'   => -1,
            'post_type'        => 'mphb_booking',
            'post_status' => array('confirmed','paid_not_refundable','paid_refundable','last_minute','pending_late_charge','paid_late_charge'),
            'meta_key'         => 'mphb_check_in_date',
            'meta_value'       => $tomorrow,
            'fields' 	       => 'ids',
			'orderBy'          => 'ID',
            'order'            => 'ASC'
        );
        $query = new WP_Query( $atts );

        if($query->have_posts()) {
            $ids = $query->posts;
            foreach ($ids as $id) {
                $booking = MPHB()->getBookingRepository()->findById( $id, true );
                MPHB()->emails()->sendBeforeArrivalBookingEmail($booking);
            }
        }
    }
}
