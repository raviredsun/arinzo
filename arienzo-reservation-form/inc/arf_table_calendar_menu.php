<?php
if (!defined('WPINC')) {
    die;
}

function arf_add_menu_table_calendar()
{
    add_menu_page(
        __('Tables Calendar', 'arienzo_reservation_form'),
        __('Tables Calendar', 'arienzo_reservation_form'),
        'manage_page',
        'arf_pt_table_calendar',
        'table_calendar_render',
        'dashicons-schedule',
        3
    );
}

add_action('admin_menu', 'arf_add_menu_table_calendar');

function table_calendar_render()
{

    wp_enqueue_style('mphb-admin-css');
    $args = array(
        'post_type' => 'arf_pt_table',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'post_title',
        'order' => 'ASC'
    );
    $arf_pt_tables = get_posts($args);



    $calendar = new TablesCalendar();
    ?>
    <style type="text/css">
        .w-100 {
            width: 100%;
            max-width: 25rem;
        }
        .editPop {
            overflow: hidden;
            padding: 25px;
            position: fixed;
            width: 40%;
            min-width: 400px;
            top: 10%;
            left: 30%;
            display: none;
            background: #fff;
            z-index: 10000;
            transition: 0.5s;
            max-height: 500px;
             overflow-y: scroll; 
            box-shadow: 0 1px 1px rgb(0 0 0 / 4%);
            border: 1px solid #ccd0d4;
        }
        @media (min-width: 900px){
            .editPop {
                min-width: 700px;
            }
        }
        
        .mphb_checkout-services-list label{
            display: inline-block;
        }
        .main_loader{
            position: fixed;
            background: #fff;
            width: 100%;
            height: 100%;
            left: 0;
            top: 0;
            opacity: 0.5;
            z-index: 100000;
            transition: 0.5s;
        }

        .loader4 {
           position: absolute;
           top: calc(50% - 22px);
           left: calc(50% - 22px);
           width:45px;
           height:45px;
           display:inline-block;
           padding:0px;
           border-radius:100%;
           border:5px solid;
           border-top-color:rgba(246, 36, 89, 1);
           border-bottom-color:rgba(255,255,255, 0.3);
           border-left-color:rgba(246, 36, 89, 1);
           border-right-color:rgba(255,255,255, 0.3);
           -webkit-animation: loader4 1s ease-in-out infinite;
           animation: loader4 1s ease-in-out infinite;
        }
        #mphb_place_switch_location{
            min-width: 150px;
        }
        @keyframes loader4 {
           from {transform: rotate(0deg);}
           to {transform: rotate(360deg);}
        }
        @-webkit-keyframes loader4 {
           from {-webkit-transform: rotate(0deg);}
           to {-webkit-transform: rotate(360deg);}
        }
    </style>
    <div class="wrap">
        <h1 class="mphb-booking-calendar-title wp-heading-inline"><?php _e( 'Tables Calendar', 'motopress-hotel-booking' ); ?></h1>
        <?php
        $calendar->render();
        ?>
        <div style="" class="editPop">
            <div class="headerPop" style="text-align:right"><a href="#" class="closePop" style="margin:10px 10px 0 0;">X</a></div>
            <div class="bodyPop"></div>
        </div>
        <div class="main_loader" style="display:none"><div class="loader4"></div></div>
    </div>


    <script type="text/javascript">
       

        jQuery(document).ready(function(){
            jQuery(".closePop").click(function (e) {
                e.preventDefault();
                jQuery(".editPop").hide();
            })
            jQuery(".show_pop_").click(function (e) {
                e.preventDefault();
                $this = jQuery(this);
                var id = $this.data("id");
                jQuery.ajax({
                    url: "<?php echo admin_url('admin-ajax.php'); ?>",
                    type: "POST",
                    dataType:"html",
                    beforeSend:function(){
                        jQuery(".main_loader").show()
                    },
                    complete:function(){
                        jQuery(".main_loader").hide()
                    },
                    data:{
                        action : "get_booking_details",
                        id : id,
                        frdate : "<?php echo $frdate ?>",
                        frname : "<?php echo $frname ?>",
                        frid : "<?php echo $frid ?>",
                        debug : "debug",
                    },
                    success: function(data) {
                        jQuery(".editPop .bodyPop").html(data);
                        jQuery(".editPop").show();
                    }
                });
            });
            jQuery(document).delegate(".postEditBook",'submit',function (e) {
                e.preventDefault();
                if(jQuery("#mphb_place_switch_location").val() && jQuery("#mphb_place_switch").data("count") < jQuery("#mphb_place_switch_location").val().length){
                    alert("Select "+jQuery("#mphb_place_switch").data("count")+" Switch Location");
                    return false;
                }
                $this = jQuery(this);
                var id = $this.data("id");
                jQuery.ajax({
                    url: "<?php echo admin_url('admin-ajax.php'); ?>",
                    type: "POST",
                    dataType: "JSON",
                    data:$this.serialize(),
                    success: function(data) {
                        location.reload();
                        /*jQuery(".editPop .bodyPop").html("");
                        jQuery(".editPop").hide();*/
                    }
                });
            });

            jQuery(document).delegate("#mphb_place_switch",'change',function (e) {
                html = "";
                
                if(jQuery("#mphb_location_data"+jQuery(this).val()).length){
                    $ddd =  JSON.parse(jQuery("#mphb_location_data"+jQuery(this).val()).text());
                    jQuery.each($ddd,function(i,jj){
                        jQuery.each(jj,function(ii,j){
                            html += "<option value='"+i+"-"+j+"'>"+j+"</option>";
                        })
                    })
                }
                jQuery("#mphb_place_switch_location").html(html);
            });
        })
    </script>
    <?php
}