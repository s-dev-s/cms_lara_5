"use strict";

var Trans = {

    handleSaveTrans: function () {
        var $checkoutForm = $('#form_page').validate({
            rules : {
                phrase : {
                    required : true
                },

            },
            messages : {
                phrase : {
                    required : 'Нужно заполнить фразу для перевода'
                },

            },
            errorPlacement : function (error, element) {
                error.insertAfter(element.parent());
            },
            submitHandler: function (form) {
                Trans.doSave();
            }
        });
    }, //handleSaveTrans

    show_list: function (page, count_show) {

        Trans.load_ajax("show");

        count_show = typeof count_show !== 'undefined' ? count_show : '20';

        var url_page = '?page=' + page + '&count_show=' + count_show;

        $.ajax({
            url: url_page,
            dataType: 'html'
        }).done(function (data) {

            $('.table_center_translate').html(data);
            window.history.pushState(url_page, '', url_page);
            Trans.load_ajax("hide");
        }).fail(function () {
            jQuery.smallBox({
                title: "Что-то пошло не так, попробуйте позже",
                content: "",
                color: "#C46A69",
                iconSmall: "fa fa-times fa-2x fadeInRight animated",
                timeout: 4000
            });
        });
    },

    getCreateForm: function () {
        $("#modal_form").modal('show');
        Trans.preloadPage();
        $.post(
            "/admin/translations_cms/create_pop",
            {},
            function (data) {
                $("#modal_form .modal-content").html(data);
                Trans.handleSaveTrans();
            }
        );
    },

    //yandex autotranslate
    getTranslate: function (phrase) {
        $(".langs_input").each(function (index) {
            var lang = $(this).attr("name");
            if (phrase && lang) {
                $(".langs_input[name=" + lang + "]").attr("placeholder", "Переводит...")
                $.post(
                    "/admin/translations_cms/translate",
                    {phrase: phrase, lang: lang},
                    function (data) {

                        $(".langs_input[name=" + data.lang + "]").attr("placeholder", "")

                        if (data.text) {
                            $(".langs_input[name=" + data.lang + "]").val(data.text);
                        }

                    },
                    "json"
                );
            }
        });
    },

    doSave: function () {
        $.post(
            "/admin/translations_cms/add_record",
            {data: $('#form_page').serialize()},
            function (data) {
                if (data.status == "ok") {
                    jQuery.smallBox({
                        title: data.ok_messages,
                        content: "",
                        color: "#659265",
                        iconSmall: "fa fa-check fa-2x fadeInRight animated",
                        timeout: 4000
                    });

                    $("#modal_form").modal('hide');
                    Trans.show_list(1);
                } else {
                    var mess_error = ""
                    $.each(data.errors_messages, function (key, value) {
                        mess_error += value + "<br>";
                    });

                    jQuery.smallBox({
                        title: mess_error,
                        content: "",
                        color: "#C46A69",
                        iconSmall: "fa fa-times fa-2x fadeInRight animated",
                        timeout: 4000
                    });
                }
            },
            "json"
        );
    },

    doDelete: function (this_id_pages) {
        $.post(
            "/admin/translations_cms/del_record",
            {id: this_id_pages},
            function (data) {
                Trans.show_list(1);
            }
        );
    },

    preloadPage: function () {
        var preloadhtml = '<div id="table-preloader" class="text-align-center"><i class="fa fa-gear fa-4x fa-spin"></i></div>';
        $("#modal_form .modal-content").html(preloadhtml);
    },

    load_ajax: function ($show) {
        if ($show == "show") {
            $(".load_ajax").show();
        } else {
            $(".load_ajax").hide();
        }
    }

};

//смена количества показа на странице
$(document).on("change", '[name=dt_basic_length]', function () {
    Trans.show_list("1", $(this).val());
});

//поиск
$(document).on("submit", '#search_form', function () {
    var search_q = $("[type=search]").val();

    $.get(
        window.location.pathname,
        {search_q: search_q, "page": 1},
        function (data) {

            $('#content_admin').html(data);
        }
    );
    return false;
});

//ajax пагинация
$(document).on('click', '.pagination a', function (e) {
    Trans.show_list($(this).attr('href').split('page=')[1]);
    e.preventDefault();
});

