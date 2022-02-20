$(function() {
    var ajaxResponseBaseTime = 3;

    //ajax form
    $("form:not('.ajax_off')").submit(function(e) {
        e.preventDefault();
        var form = $(this);
        form.ajaxSubmit({
            url: form.attr("action"),
            type: "POST",
            dataType: "json",
            beforeSend: function() {
                $(".main-loading").fadeIn(200).css("display", "flex");
            },
            success: function(response) {
                ajaxOk(response);
            },
            error: function() {
                $(".main-loading").fadeOut(200);
            },
            complete: function() {
                // load.fadeOut(200);
                if (form.data("reset") === true) {
                    form.trigger("reset");
                }
            }
        });
    });

    $(document).on("click", "[data-update]", function() {
        exec($(this));
    });

    function exec(clicked, value = false) {

        if (clicked.is(":checked")) {
            clicked.data("status", "active");
        } else {
            clicked.data("status", "inactive");
        }

        var dataset = clicked.data();
        if (value) {
            dataset = Object.assign({ value: clicked.val() }, dataset);
        }

        $.post(clicked.attr("data-url"), dataset, function(response) {
                ajaxOk(response);
            }, "json")
            .fail(function() {
                $(".main-loading").fadeOut(200);
            });
    }


    $(document).on("click", "[data-remove]", function(e) {
        // $("[data-remove]").on("click", function (e) {
        var clicked = $(this);
        var dataset = clicked.data();

        $(".main-loading").fadeIn(200).css("display", "flex");

        var url = clicked.data("remove");
        if (clicked.data("remove") == null || clicked.data("remove") == undefined || clicked.data("remove") == '') {
            url = clicked.data("url");
        }

        $.post(url, dataset, function(response) {
                ajaxOk(response);
            }, "json")
            .fail(function() {
                $(".main-loading").fadeOut(200);
            });
    });

    // atualizar dados no modal
    $(document).on("click", "[data-target]", function(e) {
        // $("[data-target]").on("click", function (e) {
        e.preventDefault();
        var clicked = $(this);

        if (clicked.data("target") == "#modal-remove") {
            $("#confirm_remove").attr("data-remove", clicked.data("url"));
        }
    });

    /**
     * @param response 
     */
    function ajaxOk(response) {

        // Adicionar classe de erro ao input 
        if (response.error) {
            $(response.error).addClass('is-invalid'); // adiciona classe de erro no input
            if (response.message) {
                // adiciona mensagem de erro abaixo do input 
                var msg_erro = $(response.message).text().trim(); // pega a mensagem do alert pop up
                msg_erro = msg_erro.slice(0, -1); // remove ultimo caractere da mensagem que será sempre o x
                $('.invalid-feedback').remove(); // remove classe de erro antes de adicionar uma nova mensagem 
                $(response.error).after('<div class="invalid-feedback">' + msg_erro + '</div>'); // adiciona a mensagem abaixo do input
            }
        }

        //redirect
        if (response.redirect) {
            window.location.href = response.redirect;
        } else if (response.reload) {
            //reload
            window.location.reload();
        } else {

            // message
            if (response.message) {
                ajaxMessage(response.message, 5);
            }

            $(".main-loading").fadeOut(200);
        }
    }


    // AJAX RESPONSE
    function ajaxMessage(message, time) {
        var ajaxMessage = $(message);

        ajaxMessage.append("<div class='message_time'></div>");
        ajaxMessage.find(".message_time").animate({ "width": "100%" }, time * 1000, function() {
            $(this).parents(".message").fadeOut(200);
        });

        $(".ajax_response").append(ajaxMessage);
        ajaxMessage.effect("bounce");
    }

    // AJAX RESPONSE MONITOR
    $(".ajax_response .message").each(function(e, m) {
        ajaxMessage(m, ajaxResponseBaseTime += 2);
    });

    // AJAX MESSAGE CLOSE ON CLICK
    $(".ajax_response").on("click", ".message", function(e) {
        $(this).fadeOut("slow");
    });
});



// Remove classe de erro ao clicar em qualquer input
$("input, textarea").click(function() {
    $(this).removeClass('is-invalid');
});

// Remove classe de erro ao digitar 
$("input,textarea").keyup(function() {
    if ($(this).hasClass('is-invalid')) {
        $(this).removeClass('is-invalid');
    }
});

// Remove classe de erro ao clicar em qualquer input
$("select").change(function() {
    $(this).removeClass('is-invalid');
});