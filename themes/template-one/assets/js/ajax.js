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

                // setTimeout(function () {
                //     window.close();
                // }, 2000);
            }
        });
    });

    $(document).on("click", "[data-update]", function() {
        console.log('data update');
        // $("[data-update]").on("click", function () {
        exec($(this));
    });


    $("[data-change]").on("change", function() {
        console.log('data change');
        exec($(this), true);
    });


    $("[data-keyup]").on("keyup", function() {
        console.log('data keyup');
        exec($(this), true);
    });


    $("[data-blur]").on("blur", function() {
        console.log('data blur');
        exec($(this), true);
    });


    // chama o change ao carregar a pagina , ou outras methodos, falta implementar
    $("[data-boot]").each(function() {
        if ($(this).attr('data-change')) {
            $(this).change();
        }

        if ($(this).attr('data-loader')) {
            exec($(this), true);
        }
    });


    // Pega todos os dados do formulario e adiciona ao atributo data para enviar como parametro
    $("[data-loader]").on("click", function() {
        var clicked = $(this);
        var data = clicked.parents('form').serialize();
        clicked.data('form', data);
    });


    // Pega todos os dados do formulario e adiciona ao atributo data para enviar como parametro
    $("[data-form]").on("click", function() {
        var clicked = $(this);
        var data = clicked.parents('form').serialize();
        clicked.data('form', data);
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

        // quando um click estiver o atributo form, então deve ter todos os dados do formulario parent
        if (clicked.data('form')) {
            var dataset = clicked.data('form');
            if (value) {
                dataset = dataset + '&value=' + clicked.val();
            }
        }

        // Pega atributos e envia como parametro
        if (clicked.attr('data-attr')) {
            var arr = [];
            $(clicked[0].attributes).each(function() {
                if (this.name.indexOf('data-') == 0) {
                    var name = this.name.replace('data-', '');
                    arr[name] = this.value;
                    // console.log(this.name + ':' + this.value);
                }
            });
            dataset = Object.assign({}, arr); // {0:"a", 1:"b", 2:"c"}
        }

        if (!clicked.data("loading_off")) {
            $(".main-loading").fadeIn(200).css("display", "flex");
        }


        $.post(clicked.attr("data-url"), dataset, function(response) {
                ajaxOk(response);
            }, "json")
            .fail(function() {
                $(".main-loading").fadeOut(200);
            });
    }

    // atualizando o timezone do sidebar
    $("[data-uptimezone]").on("change", function() {
        exec($(this), true);
    });


    $(document).on("click", "[data-remove]", function(e) {
        console.log('data remove');
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
        console.log('data target');
        var clicked = $(this);

        if (clicked.data("target") == "#modal-remove") {
            $("#confirm_remove").attr("data-remove", clicked.data("url"));
        }
    });


    // carregar conteudo|ação do click
    $("[data-load]").on("click", function(e) {
        e.preventDefault();
        console.log('data load');
        $(".main-loading").fadeIn(200).css("display", "flex");

        if ($(this).is("button")) {
            $("form[data-formfilter]").submit();
            return;
        }
        window.location.href = $(this).attr('href');
    });

    $("[data-load-report]").on("click", function(e) {
        e.preventDefault();
        console.log('data-load-report');
        var report = $("#savedReport").val();
        if (report != "") {
            $(".main-loading").fadeIn(200).css("display", "flex");
            window.location.href = report;
        }
    });

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

            // setar valor em inputs  Usar só essee
            if (response.remove) {
                var arr = response.remove;
                for (const [key, value] of Object.entries(arr)) {
                    $(key).remove();
                }
            }

            // setar valor em inputs  Usar só essee
            if (response.val) {
                var arr = response.val;
                for (const [key, value] of Object.entries(arr)) {
                    $(key).val(value);
                }
            }

            // alterar htmls
            if (response.html) {
                var arr = response.html;
                for (const [key, value] of Object.entries(arr)) {
                    $(key).html(value);
                }
            }

            // Change
            if (response.change) {
                var arr = response.change;
                for (const [key, value] of Object.entries(arr)) {
                    $(key).change();
                }
            }

            // fazer appends htmls
            if (response.append) {
                var arr = response.append;
                for (const [key, value] of Object.entries(arr)) {
                    $(key).append(value);
                }
            }


            // remover classe
            if (response.removeClass) {
                var arr = response.removeClass;
                for (const [key, value] of Object.entries(arr)) {
                    $(key).removeClass(value);
                }
            }

            // Adicionar classe
            if (response.addClass) {
                var arr = response.addClass;
                for (const [key, value] of Object.entries(arr)) {
                    $(key).addClass(value);
                }
            }

            // fazer after htmls
            if (response.after) {
                var arr = response.after;
                for (const [key, value] of Object.entries(arr)) {
                    $(key).after(value);
                }
            }



            // attr
            if (response.attr) {
                var arr = response.attr;
                for (const [key, value] of Object.entries(arr)) {
                    for (const [valeu_key, value_value] of Object.entries(value)) {
                        $(key).attr(valeu_key, value_value);
                    }
                }
            }


            //  removeAttr
            if (response.removeAttr) {
                var arr = response.removeAttr;
                for (const [key, value] of Object.entries(arr)) {
                    $(key).removeAttr(value);
                }
            }

            //  modal
            if (response.modal) {
                var arr = response.modal;
                for (const [key, value] of Object.entries(arr)) {
                    $(key).modal(value);
                }
            }

            //  open
            if (response.open) {
                var arr = response.open;
                for (const [key, value] of Object.entries(arr)) {
                    window.open(key, value);
                }
            }

            $("[data-img_encode]").each(function() {
                $(this).attr('src', window.atob($(this).attr('data-img_encode')));
            });


            // chamar o ajax novamente
            if (response.ajax) {
                var arr = response.ajax;
                for (const [key, value] of Object.entries(arr)) {
                    $.post(key, value, function(response) {
                            $(".main-loading").fadeIn(200).css("display", "flex");
                            ajaxOk(response);
                            // alert('succss ajax two');
                        }, "json")
                        .fail(function() {
                            // alert('error ajax two');
                            $(".main-loading").fadeOut(200);
                        });
                }
            }

            //  chamar o ajax novamente pela classe
            if (response.ajax_class) {
                var clicked = $(response.ajax_class);
                exec(clicked, true);
            }

            // message
            if (response.message) {
                ajaxMessage(response.message, 5);
            }
            if (response.close_modal) {
                $('.modal').modal('hide');
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