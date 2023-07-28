jQuery(document).ready(function ($) {
    "use strict";
    $('#arf_booking_day').datepicker({
        changeYear: 'true',
        changeMonth: 'true',
        firstDay: 1,
        dateFormat: 'yy-mm-dd'
    });
    $('#change_day').on('click', function (e) {
        e.preventDefault();
        $('#arf_booking_day').datepicker('show');
    });
    $('#arf_dashboard_booking_box .more_info a').on('click', function (e) {
        e.preventDefault();
        var lunchTime = $(this).attr("data-lunchTime");

        var bookingDate = $("#arf_booking_day").val();

        if (lunchTime && bookingDate) {
            var data = {
                lunchTime: lunchTime,
                bookingDate: bookingDate,
                action: 'arf_dashboard_info_by_time',
            }
            $('.arf_dashboard_booking_box_table tbody').empty();
            $.ajax({
                type: "post",
                dataType: "json",
                url: arf_ajax_action.ajax_url,
                data: data,
                success: function (response) {
                    var html = "<tr>";
                    for (var index = 0; index < response.length; ++index) {
						html += "<tr>";
                        html += "<td>";
                        html += "# " + response[index].id;
                        html += "</td>";

                        html += "<td>";
                        html += response[index].customer_name;
                        html += "</td>";

                        html += "<td>";
                        html += response[index].guests;
                        html += "</td>";
						html += "</tr>";
                    }
                    
                    $('.arf_dashboard_booking_box_table').show().find('tbody').append(html);
                }
            });
        }
    });

    $(document).ready(function () {
        var day = $('#arf_booking_day').val();
        var data = {
            day: day,
            action: 'arf_dashboard_info_by_day',
        };
        $.ajax({
            type: "post",
            dataType: "json",
            url: arf_ajax_action.ajax_url,
            data: data,
            success: function (response) {
                $('#lunch_time_1').html(response.lunch_time_1);
                $('#lunch_time_2').html(response.lunch_time_2);
                $('#lunch_time_3').html(response.lunch_time_3);
                $('#lunch_time_4').html(response.lunch_time_4);
                $('#lunch_time_5').html(response.lunch_time_5);
            }
        });
    });

    $(document).on('change', '#arf_booking_day', function () {
        var bookingDay = $(this).val();
		var date1 = new Date(bookingDay);
		var date2 = new Date(date1);
		date2.setDate(date1.getDate()+1);
		
		$('#change_day').html(bookingDay);
		$('#next_day').html( formatDate(date2.toString()));
		
        var data = {
            day: bookingDay,
            action: 'arf_dashboard_info_by_day',
        };
        $.ajax({
            type: "post",
            dataType: "json",
            url: arf_ajax_action.ajax_url,
            data: data,
            success: function (response) {
                $('#lunch_time_1').html(response.lunch_time_1)
                $('#lunch_time_2').html(response.lunch_time_2)
                $('#lunch_time_3').html(response.lunch_time_3)
                $('#lunch_time_4').html(response.lunch_time_4)
                $('#lunch_time_5').html(response.lunch_time_5)
            }
        });
    })
	function formatDate(date) {
    var d = new Date(date),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) 
        month = '0' + month;
    if (day.length < 2) 
        day = '0' + day;

    return [year, month, day].join('-');
}
});