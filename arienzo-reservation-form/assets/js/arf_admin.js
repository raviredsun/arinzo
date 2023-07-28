jQuery(document).ready(function ($) {
    jQuery('#generate_product_report').on('click',function(e) {
        let start_date = jQuery("input[name=start_date]").val();
        let end_date = jQuery("input[name=end_date]").val();
        let status = jQuery("input[name=status]").val();
        let data =  {
            start_date,
            end_date,
            action: 'arf_generate_pdf_product',
            status: 'status',
            fileType: "product"
        }
        if(start_date !=="" && end_date !== "") {

            var texttt = jQuery(this).text();
            jQuery(this).text("Loading").attr("disabled","disabled");
            var $this = jQuery(this);

            $.ajax({
                type: "post",
                dataType: "json",
                url: arf_admin.ajaxurl,
                data: data,
                success: function(msg){
                    if(msg.success === true) {
                        //window.location = msg.data;
                        window.open(msg.data);
                    }
                    else {
                        alert(msg.data.message);
                    }
                    $this.text(texttt).removeAttr("disabled");
                }
            });
        }
        else {
            alert('Start and End Dates should be chosen')
        }
    });
    jQuery('#generate_product_report2').on('click',function(e) {
        let start_date = jQuery("input[name=start_date]").val();
        let end_date = jQuery("input[name=end_date]").val();
        let status = jQuery("input[name=status]").val();
        let data =  {
            start_date,
            end_date,
            action: 'arf_generate_pdf_product2',
            status: 'status',
            fileType: "product2"
        }
        if(start_date !=="" && end_date !== "") {

            var texttt = jQuery(this).text();
            jQuery(this).text("Loading").attr("disabled","disabled");
            var $this = jQuery(this);

            $.ajax({
                type: "post",
                dataType: "json",
                url: arf_admin.ajaxurl,
                data: data,
                success: function(msg){
                    if(msg.success === true) {
                        //window.location = msg.data;
                        window.open(msg.data);
                    }
                    else {
                        alert(msg.data.message);
                    }
                    $this.text(texttt).removeAttr("disabled");
                }
            });
        }
        else {
            alert('Start and End Dates should be chosen')
        }
    });
    jQuery('#generate_arrival_time_pdf').on('click',function(e) {
        let start_date = jQuery("input[name=start_date]").val();
        let end_date = jQuery("input[name=end_date]").val();
        let data =  {
            start_date,
            end_date,
            action: 'arf_generate_pdf',
            fileType: "arrival_time"
        }

        if(start_date !=="" && end_date !== "") {
            var texttt = jQuery(this).text();
            jQuery(this).text("Loading").attr("disabled","disabled");
            var $this = jQuery(this);
            $.ajax({
                type: "post",
                dataType: "json",
                url: arf_admin.ajaxurl,
                data: data,
                success: function(msg){
                    if(msg.success === true) {
                        //window.location = msg.data;
                        window.open(msg.data);
                    }
                    else {
                        alert(msg.data.message);
                    }
                    $this.text(texttt).removeAttr("disabled");
                }
            });
        }
        else {
            alert('Start and End Dates should be chosen')
        }
    });
    jQuery('#generate_lunch_time_pdf').on('click',function(e) {
        let start_date = jQuery("input[name=start_date]").val();
        let end_date = jQuery("input[name=end_date]").val();
        let data =  {
            start_date,
            end_date,
            action: 'arf_generate_pdf',
            fileType: "lunch_time"
        }
        if(start_date !=="" && end_date !== "") {

            var texttt = jQuery(this).text();
            jQuery(this).text("Loading").attr("disabled","disabled");
            var $this = jQuery(this);
            
            $.ajax({
                type: "post",
                dataType: "json",
                url: arf_admin.ajaxurl,
                data: data,
                success: function(msg){
                    if(msg.success === true) {
                        //window.location = msg.data;
                        window.open(msg.data);
                    }
                    else {
                        alert(msg.data.message);
                    }
                    $this.text(texttt).removeAttr("disabled");
                }
            });
        }
        else {
            alert('Start and End Dates should be chosen')
        }

    });

    jQuery('#generate_lunch_arrival_times_pdf').on('click', function(e) {
        let start_date = jQuery("input[name=start_date]").val();
        let end_date = jQuery("input[name=end_date]").val();
        let data =  {
            start_date,
            end_date,
            action: 'arf_generate_pdf',
            fileType: "lunch_arrival_times"
        }
        if(start_date !=="" && end_date !== "") {
            
            var texttt = jQuery(this).text();
            jQuery(this).text("Loading").attr("disabled","disabled");
            var $this = jQuery(this);
            
            $.ajax({
                type: "post",
                dataType: "json",
                url: arf_admin.ajaxurl,
                data: data,
                success: function(msg){
                    if(msg.success === true) {
                        //window.location = msg.data;
                        window.open(msg.data);
                    }
                    else {
                        alert(msg.data.message);
                    }
                    $this.text(texttt).removeAttr("disabled");
                }
            });
        }
        else {
            alert('Start and End Dates should be chosen')
        }

    });
    
    jQuery(document).on('change', '#mphb_lunch_time', function () {
        let lunch_time = $(this).val();
        let mphb_check_in_date = jQuery('input[name="mphb_check_in_date"]').val();
        if(lunch_time !=="") {
            let data =  {
                lunch_time,
                mphb_check_in_date,
                action: 'arf_get_tables',
            }
            
            /*var texttt = jQuery(this).text();
            jQuery(this).text("Loading").attr("disabled","disabled");*/
            var $this = jQuery(this);
            $.ajax({
                type: "post",
                dataType: "json",
                url: arf_admin.ajaxurl,
                data: data,
                success: function(msg){
                    if (msg.html != "") {
                        $("#mphb_table_id").empty().append(msg.html).prop('selectedIndex',0);
                    }
                    /*$this.text(texttt).removeAttr("disabled");*/
                }
            });
        }
    } )
});