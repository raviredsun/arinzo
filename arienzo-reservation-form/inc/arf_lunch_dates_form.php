<?php
add_action('wp_ajax_arf_lunch_days', 'save_arf_lunch_days');
function save_arf_lunch_days()
{
    $data = !empty($_POST['data']) ? $_POST['data'] : array();
    echo update_option('arf_lunch_days', $data);
    die();

}

function arf_add_lunch_time_menu()
{
    add_menu_page(
        __('Lunch Time Config Page', 'arienzo_reservation_form'),
        __('Lunch Time Config Page', 'arienzo_reservation_form'),
        'manage_page',
        'arf_lunch_time_config_page',
        'arf_lunch_time_config_page',
        'dashicons-schedule',
        4
    );
}

add_action('admin_menu', 'arf_add_lunch_time_menu');

function arf_lunch_time_config_page()
{
    $days = get_option('arf_lunch_days');
    
    /*$lunch_time_list = get_posts([
        'numberposts'       => -1,
        'post_type'     => 'lunch_time',
        'post_status'   => 'publish',
        'suppress_filters' => 0
    ]);
    $lunch_time_slot = array();
    foreach ($lunch_time_list as $key => $value) {
        $lunch_time_slot[] = array(
            "ID" => $value->ID,
            "title" => $value->post_title,
        );
    }*/

    $lunch_time_list = array();
    $lunch_time_list_table = get_posts([
        'numberposts'       => -1,
        'post_type'     => 'lunch_time',
        'post_status'   => 'publish',
        'suppress_filters' => 0,
        'order'=> "asc",
        'orderby'=> "post_title",
        'meta_query' => array(
            'relation' => 'OR', /* <-- here */
            array(
                'key' => 'lunch_time_type',
                'value' => "lunch_time"
            ),
            array(
                'key' => 'lunch_time_type',
                'value'   => '',
                'compare' => '='
            ),
            array(
                'key' => 'lunch_time_type',
                'compare'   => 'NOT EXISTS',
            )
        ),
    ]);
    foreach ($lunch_time_list_table as $key => $value) {
        $lunch_time_slot[] = array(
            "ID" => $value->ID,
            "title" => $value->post_title,
        );
    }
    $lunch_time_list_sunbed = get_posts([
        'numberposts'       => -1,
        'post_type'     => 'lunch_time',
        'post_status'   => 'publish',
        'suppress_filters' => 0,
        'order'=> "asc",
        'orderby'=> "post_title",
        'meta_query' => array(
            array(
                'key' => 'lunch_time_type',
                'value' => "lunch_at_your_sunbed"
            )
        ),
    ]);

    foreach ($lunch_time_list_sunbed as $key => $value) {
        $lunch_time_slot[] = array(
            "ID" => $value->ID,
            "title" => $value->post_title." - Sunbed",
        );
    }
    ?>
    <style>
        .lunch_time_section {
            width: 50%;
        }

        .lunch_time_section button {
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

        .lunch_time_section .add_new_area {
            background-color: green;
        }

        .lunch_time_section .identify_area {
            background-color: #4D7EE8;
        }

        .lunch_time_section .delete_area {
            background-color: #FA4241;
        }

        .lunch_time_section .save_lunch_time_section_data {
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

        .lunch_time_section table {
            box-sizing: border-box;
            width: 100%;
            border: 1px solid #323232;
            border-radius: 4px;
            background-color: #1C1F20;
            box-shadow: 0 0 16px 0 rgba(0, 0, 0, 0.65);
            margin-top: 13px;
        }

        .lunch_time_section table th {
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

        .lunch_time_section table td {
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
    <div class="lunch_time_section">
        <h2><?php echo __('Add Days for disable Lunch Time(section) on the Booking form', 'arienzo_reservation_form'); ?></h2>
        <button class="add_new_area"><?php echo __('Add', 'arienzo_reservation_form'); ?></button>
        <button class="save_lunch_time_section_data"><?php echo __('Save', 'arienzo_reservation_form'); ?></button>
        <table>
            <thead>
            <tr>
                <th><?php echo __('Date', 'arienzo_reservation_form'); ?>:</th>
                <th><?php echo __('Lunch Time', 'arienzo_reservation_form'); ?>:</th>
                <td><?php echo __('Action', 'arienzo_reservation_form'); ?>:</td>
            </tr>
            </thead>
            <tbody>
            <?php
            if (!empty($days)) {
                foreach ($days as $key => $day) { ?>
                    <tr>
                        <td><input name="days[]" type="text" value="<?php echo $day['date']; ?>"></td>
                        <td>
                            <select name="time[time]" id="">
                                <?php foreach ($lunch_time_slot as $key => $value) { ?>
                                    <option value="<?php echo $value['ID'] ?>" <?php selected($day['time'], $value['ID']) ?>  <?php selected($day['time'], $value['title']) ?> ><?php echo $value['title'] ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <td>
                            <button class="delete_area">Delete
                            </button>
                        </td>
                    </tr>
                <?php }
            } ?>
            </tbody>
        </table>
    </div>
    <script>
        var $ = jQuery.noConflict();
        jQuery(document).ready(function ($) {
            $('.lunch_time_section input').datepicker({
                dateFormat : 'yy-mm-dd',
                setDate: new Date(),
                autoclose: true
            });
            $(document).on('click', '.delete_area', function (event) {
                event.preventDefault();
                $(this).closest('tr').remove();
            });
            $(document).on('click', '.add_new_area', function (event) {
                event.preventDefault();
                var html = `<tr><td><input name="days[]" type="text" value=""></td>
                            <td><select name="time[]" id="">`;
                                 <?php foreach ($lunch_time_slot as $key => $value) { ?>
                                    html += '<option value="<?php echo $value['ID'] ?>" ><?php echo $value['title'] ?></option>';
                                <?php } ?>
                            html += `</select></td>
                    <td>
                        <button class="delete_area">Delete</button>
                    </td></tr>`;
                $('table tbody').append(html);
                $('.lunch_time_section input').datepicker({
                    dateFormat : 'yy-mm-dd',
                    defaultDate: new Date(),
                });
            });
            $(document).on('click', '.save_lunch_time_section_data', function (event) {
                event.preventDefault();
                var data =jQuery(".lunch_time_section tr").map(function(index, item) {
                    var response = {};
                    var date = $(item).find("input[name='days[]']").val();
                    var time = $(item).find("select").val();
                    if(date) {
                        response.date = date;
                        response.time = time;
                        return response;
                    }
                    return ;
                }).get();
                let postData = {
                        data: data,
                        action: "arf_lunch_days"
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
            })


        })

    </script>
    <?php
}