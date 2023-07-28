jQuery(function ($) {
    "use strict";
    //$('form#wrapped').attr('action', 'booking_hotel.php');
    $("#wizard_container").wizard({
        stepsWrapper: "#wrapped",
        submit: ".submit",
        beforeSelect: function (event, state) {
            if ($('input#website').val().length != 0) {
                return false;
            }
            if (!state.isMovingForward)
                return true;
            var inputs = $(this).wizard('state').step.find(':input');
            return !inputs.length || !!inputs.valid();
        },
        afterSelect: function (event, state) {
            $("#progressbar").progressbar("value", state.percentComplete);
            $("#location").text("(" + state.stepsComplete + "/" + state.stepsPossible + ")");
        }
    }).validate({
        errorPlacement: function (error, element) {
            if (element.is(':radio') || element.is(':checkbox')) {
                error.insertBefore(element.next());
            } else {
                error.insertAfter(element);
            }
        },
        submitHandler: function(form) {

            if (!$('input[name="services[]"]').is(":checked")) {
                $('.service-error').show();
				return; 
            } else {
                $('.service-error').hide();
            }
            let custom_form = $(form).find('form#wrapped');
            let formData = $(custom_form).serialize();
            let data = {
                data: formData,
                action: 'arf_booking_hotel_ajax_request'
            };

            if ( custom_form.data('requestRunning') ) {
                return;
            }
            var  list = $(".custom_error_message");
            list.empty();

            $.ajax({
                url: arf_ajax_action.ajax_url,
                type: form.method,
                data: data,
                method: "POST",
                dataType: 'JSON',
                success: function(response) {

                    if(response.success) {
                        window.location.href = response.url;
                    }
                    else {
                        let messages = response.messages;

                        for (let key in messages) {
                            if (!messages.hasOwnProperty(key)) continue;
                            let text = messages[key];
                            list.append('<li>' + text + '</li>');
                        }
                    }
                },
                complete: function() {
                    custom_form.data('requestRunning', false);
                }
            });
        }
    });
    //  progress bar
    $("#progressbar").progressbar();
});