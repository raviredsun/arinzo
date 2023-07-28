<?php

include "../../../wp-load.php";

if(isset($_GET['run'])) {

    //Booking daily mail notification

    if ($_GET['run'] == '3E2B59EC-04FD-4DC0-8BB9-362E3E7E6F3B') {

        include( plugin_dir_path( __FILE__ ) . 'inc/Arf_Booking_Mail_Notification_Cron_Job.php');

        $obj = new ARF_BOOKING_MAIL_NOTIFICATION_CRON_JOB;

        $obj->init();

    }

    //Lunch and arrival times daily report

    elseif ($_GET['run'] == 'C6B5ECF3-B024-4D23-B6FF-3AF64E54E825') {

        include( plugin_dir_path( __FILE__ ) . 'inc/Arf_Lunch_Arrival_Time_Mail_Notification_Cron_Job.php');

        $obj = new ARF_LUNCH_ARRIVAL_TIME_MAIL_NOTIFICATION_CRON_JOB;

        $obj->init();

    }elseif ($_GET['run'] == 'reminder_mail') {
       
        include( plugin_dir_path( __FILE__ ) . 'inc/ARF_BEFORE_ARRIVAL_BOOKING_CRON_JOB.php');

        $obj = new ARF_BEFORE_ARRIVAL_BOOKING_CRON_JOB;
        
        $obj->init();

    }elseif ($_GET['run'] == 'reminder_mail_prereminder') {
       
        include( plugin_dir_path( __FILE__ ) . 'inc/ARF_BEFORE_ARRIVAL_PREREMINDER_BOOKING_CRON_JOB.php');

        $obj = new ARF_BEFORE_ARRIVAL_PREREMINDER_BOOKING_CRON_JOB;
        
        $obj->init();
        
    }elseif ($_GET['run'] == 'reminder_mail_payment') {
        include( plugin_dir_path( __FILE__ ) . 'inc/ARF_PAYMENT_REMIDER_CRON_JOB.php');

        $obj = new ARF_PAYMENT_REMIDER_CRON_JOB;

        $obj->init();
    }elseif ($_GET['run'] == 'testmail') {

        include( plugin_dir_path( __FILE__ ) . 'inc/ARF_BEFORE_ARRIVAL_BOOKING_CRON_JOB.php');

        $obj = new ARF_BEFORE_ARRIVAL_BOOKING_CRON_JOB;
        
        $obj->init2();
    }elseif ($_GET['run'] == 'testmail2') {

        include( plugin_dir_path( __FILE__ ) . 'inc/ARF_BEFORE_ARRIVAL_PREREMINDER_BOOKING_CRON_JOB.php');

        $obj = new ARF_BEFORE_ARRIVAL_PREREMINDER_BOOKING_CRON_JOB;
        
        $obj->init2();
    }

}



die();



