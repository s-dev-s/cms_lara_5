'use strict';

var TableBuilder = {

    options: null,
    files: {},
    admin_prefix: '',
    action_url: '',
    table: '#widget-grid',
    preloader: '#table-preloader',
    form_preloader: '.form-preloader',
    form: '#modal_form',
    form_edit: '#modal_form_edit',
    export_form: '#tb-export-form',
    form_label: '#modal_form_edit_label',
    form_wrapper: '#modal_wrapper',
    create_form: '#create_form',
    edit_form: '#edit_form',
    filter: '#filters-row :input',
    is_page_form: false,

    onDoEdit: null,
    onDoCreate: null,
    onDoDelete: null,
    tableEditorImg : null,

    init: function(options)
    {
        TableBuilder.options = TableBuilder.getOptions(options);
        TableBuilder.initSelect2Hider();
    }, // end init

    optionsInit : function(options)
    {
        TableBuilder.options = TableBuilder.getOptions(options);
    },

    initFroalaEditor: function () {

        var langEditor = "en";

        if (langCms == "ua") {
            langEditor = "uk";
        } else {
            langEditor = langCms;
        }


        $.FroalaEditor.DefineIcon('crop', {NAME: 'crop'});
        $.FroalaEditor.RegisterCommand('crop', {
            title: 'Image Crop',
            focus: false,
            undo: false,
            refreshAfterCallback: false,
            callback: function () {
                var imgThis = this.image.get();
                $('#modal_crop_img').modal("show");

                var srcImg = imgThis.attr('src');
                TableBuilder.tableEditorImg = imgThis;

                $("#modal_crop_img #image").attr("src", "");
                $("#modal_crop_img #image").attr("src", srcImg);
                $("#modal_crop_img").css("top", $(window).scrollTop() + 20);

                var $image = $('#image');
                $image.cropper('destroy');
                var result = $image.cropper({

                    crop: function (data) {
                        $(".width_crop").text(Math.round(data.width));
                        $(".height_crop").text(Math.round(data.height));

                    },
                    built: function () {

                    },
                    // responsive : false
                });
                setTimeout('$("#modal_crop_img #image").attr("src", "' + srcImg + '")', 1000);
            }
        });

        $( ".text_block" ).each(function( index ) {

            var option =  {
                inlineMode: false,
                imageUploadURL: '/admin/upload_image?_token=' + $("meta[name=csrf-token]").attr("content"),
                imageManagerDeleteURL: "/admin/delete_image?_token=" + $("meta[name=csrf-token]").attr("content"),
                heightMin: 100,
                heightMax: 500,
                fileUploadURL: "/admin/upload_file?_token=" + $("meta[name=csrf-token]").attr("content"),
                imageManagerLoadURL: "/admin/load_image?_token=" + $("meta[name=csrf-token]").attr("content"),
                imageDeleteURL: "/admin/delete_image?_token=" + $("meta[name=csrf-token]").attr("content"),
                language: langEditor,
                imageEditButtons: ['imageReplace', 'imageAlign', 'imageRemove', '|', 'imageLink', 'linkOpen', 'linkEdit', 'linkRemove', '-', 'imageDisplay', 'imageStyle', 'imageAlt', 'imageSize', 'crop']
            };

            if ($(this).attr("toolbar")) {
                var toolbar = $(this).attr("toolbar");
                var arrayToolbar = toolbar.split (",");
                arrayToolbar = $.map(arrayToolbar, $.trim);
                option.toolbarButtons = arrayToolbar;
                option.toolbarButtonsMD = arrayToolbar;
                option.toolbarButtonsSM = arrayToolbar;
                option.toolbarButtonsXS = arrayToolbar;
            }

            if ($(this).attr("inlinestyles")) {
                option.inlineStyles = JSON.parse($(this).attr("inlinestyles"))
            }

            if ($(this).attr("options")) {
                var optionsConfig = JSON.parse($(this).attr("options"));
                for (var key in optionsConfig) {

                    option[key] = optionsConfig[key];
                }
            }

            $(this).froalaEditor(option);
        });

        $("a[href='https://froala.com/wysiwyg-editor']").parent().remove();

        $('.group').on('keyup, blur', '[data-multi=multi]', function(){
            TableBuilder.multiInputAction($(this));
        });

        $( ".group [data-multi=multi]" ).each(function(  ) {
            TableBuilder.multiInputAction($(this));
        });
    },

    multiInputAction : function (context) {
        var inputThisBlock = context.parents(".input_content").find("input"),
            arrayData = [],
            hideInput = context.parents('.tabs_section').find("input[type=hidden]");

        $( inputThisBlock ).each(function( index ) {
            arrayData.push($(this).val())
        });

        hideInput.val(JSON.stringify(arrayData));
    },

    getActionUrl: function()
    {
        return TableBuilder.action_url ? TableBuilder.action_url : '/admin/handle/tree';
    }, // end getActionUrl

    initSearchOnEnterPressed: function()
    {
        $(document).on('keypress', '.filters-row input', function(event) {
            var keyCode   = event.keyCode ? event.keyCode : event.which;
            var enterCode = '13';

            if (keyCode == enterCode) {
                TableBuilder.search();
                event.preventDefault();
            }
        });

    }, // end initSearchOnEnterPressed

    getOptions: function(options) {
        var defaultOptions = {
            lang: {},
            ident: null,
            table_ident: null,
            form_ident: null,
            action_url: null,
            list_url: null,
            is_page_form: false,
            onSearchResponse: null,
            onFastEditResponse: null,
            onShowEditFormResponse: null
        };

        var options = jQuery.extend(defaultOptions, options);

        TableBuilder.checkOptions(options);

        return options;
    }, // end getOptions

    checkOptions: function(options)
    {
        var requiredOptions = [
            'ident',
            'table_ident',
            'form_ident',
            'action_url'
        ];

        jQuery.each(requiredOptions, function(index, value) {
            if (typeof options[value] === null) {
                alert('TableBuilder: ['+ value +'] is required option.' );
            }
        });
    }, // end checkOptions

    lang: function (ident) {
        if (typeof TableBuilder.options.lang[ident] != "undefined") {
            return TableBuilder.options.lang[ident];
        }

        return ident;
    }, // end lang

    search: function()
    {
        TableBuilder.showProgressBar();

        var $form = jQuery('#'+ TableBuilder.options.table_ident);

        var data = $form.serializeArray();
        data.push({ name: "query_type", value: "search" });
        data.push({ name: "__node", value: TableBuilder.getUrlParameter('node') });

        /* Because serializeArray() ignores unset checkboxes and radio buttons: */
        data = data.concat(
            $form.find('input[type=checkbox]:not(:checked)')
                .map(function() {
                    return {"name": this.name, "value": 0};
                }).get()
        );

        var $posting = jQuery.post(TableBuilder.getActionUrl(), data);

        $posting.done(function(response) {
            doAjaxLoadContent(location.pathname);
        });

    }, // end search

    showProgressBar: function()
    {
        jQuery('#'+TableBuilder.options.ident).find('.ui-overlay').fadeIn();
    }, // end showProgressBar

    hideProgressBar: function()
    {
        jQuery('#'+TableBuilder.options.ident).find('.ui-overlay').fadeOut();
    }, // end hideProgressBar

    showFastEdit : function(thisElement)
    {
        $(".fast-edit-buttons").hide();
        $(thisElement).parent().find(".fast-edit-buttons").show();
    },

    closeFastEdit: function(context, type, response)
    {
        var $editElem = jQuery(context).parent().hide();
    }, // end closeFastEdit

    saveFastEdit: function(context, rowId, rowIdent)
    {
        // TableBuilder.showProgressBar();

        var $context = jQuery(context).parent().parent();
        var value = $context.find('.dblclick-edit-input').val();
        $("tr[id-row=" + rowId + "] .element_" + rowIdent).text(value);
        TableBuilder.closeFastEdit(context);
        var data = [
            {name: "query_type", value: "fast_save"},
            {name: "id", value: rowId},
            {name: "name", value: rowIdent},
            {name: "value", value: value}
        ];

        var $posting = jQuery.post(TableBuilder.getActionUrl(), data);

        $posting.done(function(response) {

        });
    }, // end saveFastEdit

    getUrlParameter: function(sParam)
    {
        var sPageURL = window.location.search.substring(1);
        var sURLVariables = sPageURL.split('&');
        for (var i = 0; i < sURLVariables.length; i++)
        {
            var sParameterName = sURLVariables[i].split('=');
            if (sParameterName[0] == sParam)
            {
                return sParameterName[1];
            }
        }
    }, // end getUrlParameter

    getCreateForm: function()
    {

        TableBuilder.showPreloader();

        // if ($(".table_form_create #modal_form").size() == 0) {
        $.post(TableBuilder.getActionUrl(),{"query_type" : "show_add_form"},
            function(data){
                $(".table_form_create").html(data);
                jQuery(TableBuilder.form).modal('show');
                TableBuilder.initFroalaEditor();
                TableBuilder.hidePreloader();
                TableBuilder.handleActionSelect();

                TableBuilder.refreshMask();
            });

    }, // end getCreateForm

    refreshMask : function () {
        jQuery("#modal_form_edit form, #modal_form form").find('input[data-mask]').each(function() {
            var $input = jQuery(this);
            $input.mask($input.attr('data-mask'));
        });
    },

    initSelect2Hider: function()
    {
        jQuery('.modal-dialog').on('click', function() {
            jQuery('.select2-enabled[id^="many2many"]').select2("close");
            jQuery('.select2-hidden-accessible').hide();
        });

    }, // end initSelect2Hider

    getCloneForm: function(id)
    {
        $.post( TableBuilder.getActionUrl(), {"query_type" : "clone_record", "id" : id})
            .done(function( data ) {
                doAjaxLoadContent(location.href);
            }).fail(function(xhr, ajaxOptions, thrownError) {
            var errorResult = jQuery.parseJSON(xhr.responseText);
            TableBuilder.showErrorNotification(errorResult.message);
            TableBuilder.hidePreloader();
        });
    },

    handleStartLoad: function()
    {
        var idPage = Core.urlParam('id');
        if ($.isNumeric(idPage)) {
            TableBuilder.getEditForm(idPage);
        }

        var idRevisionPage = Core.urlParam('revision_page');
        if ($.isNumeric(idRevisionPage)) {
            TableBuilder.getRevisions(idRevisionPage);
        }
        var idViewsStatistic = Core.urlParam('views_statistic');
        if ($.isNumeric(idViewsStatistic)) {
            TableBuilder.getViewsStatistic(idViewsStatistic);
        }


    }, // end handleStartLoad

    getEditForm: function(id, context)
    {
        var urlPage = "?id=" + id;
        window.history.pushState(urlPage, '', urlPage);

        TableBuilder.showPreloader();
        TableBuilder.flushStorage();
        jQuery('#wid-id-1').find('tr[data-editing="true"]').removeAttr('data-editing');

        var data = [
            {name: "query_type", value: "show_edit_form"},
            {name: "id", value: id},
            {name: "__node", value: TableBuilder.getUrlParameter('node')}
        ];

        jQuery.ajax({
            type: "POST",
            url: TableBuilder.getActionUrl(),
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    jQuery(TableBuilder.form_wrapper).html(response.html);
                    TableBuilder.initFroalaEditor();

                    jQuery(TableBuilder.form_edit).modal('show').css("top", $(window).scrollTop());;

                    TableBuilder.refreshMask();
                    TableBuilder.handleActionSelect();
                } else {
                    TableBuilder.showErrorNotification("Что-то пошло не так, попробуйте позже");
                }

                TableBuilder.hidePreloader();
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);

                TableBuilder.showErrorNotification(errorResult.message);
                TableBuilder.hidePreloader();
            }
        });
    }, // end getEditForm


    getViewsStatistic : function (id, context)
    {
        var urlPage = "?views_statistic=" + id;
        window.history.pushState(urlPage, '', urlPage);
        TableBuilder.showPreloader();
        var data = [
            {name: "query_type", value: "show_views_statistic"},
            {name: "id", value: id},
        ];

        jQuery.ajax({
            type: "POST",
            url: TableBuilder.getActionUrl(),
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    jQuery(TableBuilder.form_wrapper).html(response.html);
                    jQuery(TableBuilder.form_edit).modal('show').css("top", $(window).scrollTop());
                } else {
                    TableBuilder.showErrorNotification("Что-то пошло не так, попробуйте позже");
                }

                TableBuilder.hidePreloader();
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);

                TableBuilder.showErrorNotification(errorResult.message);
                TableBuilder.hidePreloader();
            }
        });


    },

    getRevisions: function(id, context)
    {
        var urlPage = "?revision_page=" + id;
        window.history.pushState(urlPage, '', urlPage);
        TableBuilder.showPreloader();

        var data = [
            {name: "query_type", value: "show_revisions"},
            {name: "id", value: id},
        ];
        jQuery.ajax({
            type: "POST",
            url: TableBuilder.getActionUrl(),
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    jQuery(TableBuilder.form_wrapper).html(response.html);
                    jQuery(TableBuilder.form_edit).modal('show').css("top", $(window).scrollTop());
                } else {
                    TableBuilder.showErrorNotification("Что-то пошло не так, попробуйте позже");
                }

                TableBuilder.hidePreloader();
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);

                TableBuilder.showErrorNotification(errorResult.message);
                TableBuilder.hidePreloader();
            }
        });
    },

    getReturnHistory: function(id)
    {
        var data = [
            {name: "query_type", value: "return_revisions"},
            {name: "id", value: id},
        ];
        jQuery.ajax({
            type: "POST",
            url: TableBuilder.getActionUrl(),
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.status) {

                    TableBuilder.showSuccessNotification("Сохранено");

                    jQuery(TableBuilder.form_edit).modal('hide');
                    window.history.back();
                    return;

                } else {
                    TableBuilder.showErrorNotification("Что-то пошло не так, попробуйте позже");
                }

                TableBuilder.hidePreloader();
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);

                TableBuilder.showErrorNotification(errorResult.message);
                TableBuilder.hidePreloader();
            }
        });
    },

    doDelete: function(id, context)
    {
        jQuery.SmartMessageBox({
            title : phrase["Удалить?"],
            content : phrase["Эту операцию нельзя будет отменить."],
            buttons : '[' + phrase["Нет"] + '][' + phrase["Да"] + ']'
        }, function(ButtonPressed) {
            if (ButtonPressed === phrase["Да"]) {
                TableBuilder.showPreloader();

                jQuery.ajax({
                    type: "POST",
                    url: TableBuilder.getActionUrl(),
                    data: { id: id, query_type: "delete_row", "__node": TableBuilder.getUrlParameter('node') },
                    dataType: 'json',
                    success: function(response) {

                        if (response.status) {

                            TableBuilder.showSuccessNotification(phrase['Поле удалено успешно']);
                            jQuery('tr[id-row="'+id+'"]').remove();
                        } else {
                            TableBuilder.showErrorNotification(phrase["Что-то пошло не так, попробуйте позже"]);
                        }

                        if (TableBuilder.onDoDelete) {
                            TableBuilder.onDoDelete(TableBuilder.getActionUrl());
                        }

                        TableBuilder.hidePreloader();
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        var errorResult = jQuery.parseJSON(xhr.responseText);

                        TableBuilder.showErrorNotification(errorResult.message);
                        TableBuilder.hidePreloader();
                    }
                });

            }

        });
    }, // end doDelete

    doEdit: function(id)
    {
        TableBuilder.showPreloader();
        TableBuilder.showFormPreloader(TableBuilder.form_edit);

        $(TableBuilder.edit_form + " .fr-link-insert-layer input").each(function( index ) {
            $( this ).removeAttr("name")
        });

        var values = $(TableBuilder.edit_form).serializeArray();

        values.push({ name: 'id', value: id });
        values.push({ name: 'query_type', value: "save_edit_form" });
        values.push({ name: "__node", value: TableBuilder.getUrlParameter('node') });

        if ($(".file_multi").size() != 0) {
            TableBuilder.buildFiles();
            for (var key in TableBuilder.files) {
                var filesRes = TableBuilder.files[key];
                var json = JSON.stringify(filesRes);
                values.push({ name: key, value: json });
            }
        }

        /* Because serializeArray() ignores unset checkboxes and radio buttons: */
        values = values.concat(
            jQuery(TableBuilder.edit_form).find('input[type=checkbox]:not(:checked)')
                .map(function() {
                    return {"name": this.name, "value": 0};
                }).get()
        );

        var selectMultiple = [];
        jQuery(TableBuilder.edit_form).find('select[multiple="multiple"]').each(function(i, value) {
            if (!$(this).val()) {
                selectMultiple.push({"name": this.name, "value": ''});
            }
        });

        jQuery.ajax({
            type: "POST",
            url: TableBuilder.getActionUrl(),
            data: values,
            dataType: 'json',
            success: function(response) {

                TableBuilder.hideFormPreloader(TableBuilder.form_edit);

                if (response.id) {

                    TableBuilder.showSuccessNotification(phrase['Сохранено']);
                    $(document).height($(window).height());
                    if (TableBuilder.options.is_page_form) {
                        window.history.back();
                        return;
                    }

                    jQuery(TableBuilder.form_edit).modal('hide');
                    $(document).height($(window).height());
                    jQuery('#wid-id-1').find('tr[id-row="'+id+'"]').replaceWith(response.html);

                    if (TableBuilder.onDoEdit) {
                        TableBuilder.onDoEdit(TableBuilder.getActionUrl());
                    }

                } else {

                    var errors = '';
                    jQuery(response.errors).each(function(key, val) {
                        errors += val +'<br>';
                    });

                    TableBuilder.showBigErrorNotification(errors);
                }
                TableBuilder.hidePreloader();
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);

                TableBuilder.showErrorNotification(errorResult.message);
                TableBuilder.hidePreloader();
            }
        });
    }, // end doEdit

    buildFiles : function()
    {
        $( ".file_multi" ).each(function( index ) {
            var ident = $(this).attr("nameident") ;
            TableBuilder.files[ident] = {};
        });

        var k = 0;
        $( ".file_multi" ).each(function( index ) {
            var ident = $(this).attr("nameident");
            TableBuilder.files[ident][k] = $(this).attr("value");
            k++;
        });

    },

    removeInputValues: function(context)
    {
        jQuery(':input', context)
            .removeAttr('checked')
            .removeAttr('selected')
            .not(':button, :submit, :reset, input[type="hidden"], :radio, :checkbox')
            .val('');
        jQuery('textarea', context).text('');

        jQuery('input, textarea', context).removeClass('valid').removeClass('invalid');
        jQuery('.state-success, .state-error', context).removeClass('state-success').removeClass('state-error');
    }, // end removeInputValues

    doCreate: function()
    {
        TableBuilder.showPreloader();
        TableBuilder.showFormPreloader(TableBuilder.form);

        $(TableBuilder.create_form + " .fr-link-insert-layer input").each(function( index ) {
            $( this ).removeAttr("name")
        });

        var values = jQuery(TableBuilder.create_form).serializeArray();

        console.log(values);
        values.push({ name: "query_type", value: "save_add_form" });
        values.push({ name: "__node", value: TableBuilder.getUrlParameter('node') });

        // take values from temp storage (for images)

        if ($(".file_multi").size() != 0) {
            TableBuilder.buildFiles();
            for (var key in TableBuilder.files) {
                var filesRes = TableBuilder.files[key];
                var json = JSON.stringify(filesRes);
                values.push({ name: key, value: json });
            }
        }

        var selectMultiple = [];
        jQuery(TableBuilder.create_form).find('select[multiple="multiple"]').each(function(i, value) {
            if (!$(this).val()) {
                selectMultiple.push({"name": this.name, "value": ''});
            }
        });

        values = values.concat(selectMultiple);

        // FIXME:
        if (TableBuilder.onDoCreate) {
            values = TableBuilder.onDoCreate(values);
        }

        /* Because serializeArray() ignores unset checkboxes and radio buttons: */
        values = values.concat(
            jQuery(TableBuilder.create_form).find('input[type=checkbox]:not(:checked)')
                .map(function() {
                    return {"name": this.name, "value": 0};
                }).get()
        );

        jQuery.ajax({
            type: "POST",
            url: TableBuilder.getActionUrl(),
            data: values,
            dataType: 'json',
            success: function(response) {
                TableBuilder.hideFormPreloader(TableBuilder.form);

                if (response.id) {
                    TableBuilder.showSuccessNotification('Новая запись добавлена');

                    if (TableBuilder.options.is_page_form) {
                        //window.location.href = TableBuilder.options.list_url;
                        window.history.back();
                        return;
                    }

                    jQuery('#wid-id-1 .widget-body').find('tbody').prepend(response.html);

                    TableBuilder.removeInputValues(TableBuilder.form);
                    jQuery(TableBuilder.form).modal('hide');

                    if (TableBuilder.onDoCreate) {
                        TableBuilder.onDoCreate(TableBuilder.getActionUrl());
                    }

                } else {
                    var errors = '';
                    jQuery(response.errors).each(function(key, val) {
                        errors += val +'<br>';
                    });
                    TableBuilder.showBigErrorNotification(errors);
                }

                TableBuilder.hidePreloader();
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);

                TableBuilder.showErrorNotification(errorResult.message);
                TableBuilder.hidePreloader();
            }

        });
    }, // end doCreate

    showPreloader: function()
    {
        jQuery(TableBuilder.preloader).show();
    }, // end showPreloader

    hidePreloader: function()
    {
        jQuery(TableBuilder.preloader).hide();
    }, // end hidePreloader

    showFormPreloader: function(context)
    {
        jQuery(TableBuilder.form_preloader, context).show();
    }, // end showPreloader

    hideFormPreloader: function(context)
    {
        jQuery(TableBuilder.form_preloader, context).hide();
    }, // end hidePreloader

    uploadImage: function(context, ident)
    {
        var data = new FormData();
        data.append("image", context.files[0]);
        data.append('ident', ident);
        data.append('query_type', 'upload_photo');
        data.append('type', "single_photo");
        if (TableBuilder.getUrlParameter('id_tree') != undefined) {
            data.append('page_id', TableBuilder.getUrlParameter('id_tree'));
        }

        if (TableBuilder.getUrlParameter('id') != undefined) {
            data.append('page_id', TableBuilder.getUrlParameter('id'));
        }


        var $progress = jQuery(context).parent().parent().parent().parent().parent().find('.progress-bar');

        jQuery.ajax({
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    console.log(evt);
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;
                        percentComplete = percentComplete * 100;
                        percentComplete = percentComplete +'%';
                        $progress.width(percentComplete);
                    }
                }, false);

                xhr.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;
                    }
                }, false);

                return xhr;
            },
            data: data,
            type: "POST",
            url: TableBuilder.getActionUrl(),
            cache: false,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.status) {
                    $progress.width('0%');

                    $(context).parents(".picture_block").find('.tb-uploaded-image-container').html(response.html);
                    $(context).parents(".picture_block").find('[type=hidden]').val(response.data.sizes.original);

                } else {
                    TableBuilder.showErrorNotification(phrase["Ошибка при загрузке изображения"]);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);

                TableBuilder.showErrorNotification(errorResult.message);
                TableBuilder.hidePreloader();
            }
        });
    }, // end uploadImage

    uploadMultipleImages: function(context, ident)
    {
        $(".no_photo").hide();
        var arr = context.files;
        for (var index = 0; index < arr.length; ++index) {

            var data = new FormData();
            data.append("image", context.files[index]);
            data.append('ident', ident);
            data.append('query_type', 'upload_photo');
            if (TableBuilder.getUrlParameter('id_tree') != undefined) {
                data.append('page_id', TableBuilder.getUrlParameter('id_tree'));
            }

            if (TableBuilder.getUrlParameter('id') != undefined) {
                data.append('page_id', TableBuilder.getUrlParameter('id'));
            }

            var $progress = jQuery(context).parent().parent().parent().parent().parent().find('.progress-bar');

            jQuery.ajax({
                xhr: function () {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function (evt) {
                        console.log(evt);
                        if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total;
                            percentComplete = percentComplete * 100;

                            percentComplete = percentComplete + '%';
                            $progress.width(percentComplete);
                        }
                    }, false);

                    xhr.addEventListener("progress", function (evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total;
                        }
                    }, false);

                    return xhr;
                },
                data: data,
                type: "POST",
                url: TableBuilder.getActionUrl(),
                cache: false,
                contentType: false,
                processData: false,
                success: function (response) {
                    if (response.status) {
                        $progress.width('0%');

                        $(context).parents(".multi_pictures").find(".tb-uploaded-image-container ul").append(response.html);

                        TableBuilder.setInputImages(context);

                    } else {
                        TableBuilder.showErrorNotification(phrase["Ошибка при загрузке изображения"]);
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    var errorResult = jQuery.parseJSON(xhr.responseText);

                    TableBuilder.showErrorNotification(errorResult.message);
                    TableBuilder.hidePreloader();
                }
            });
        }
    }, // end uploadMultipleImages

    setInputImages : function(context)
    {
        var arrImages = new Array();
        $(context).parent().parent().parent().find('.tb-uploaded-image-container ul li img' ).each(function( index ) {
            arrImages.push($(this).attr("data_src_original"));
        });
        var jsonArray = JSON.stringify(arrImages);

        $(context).parent().parent().find("[type=hidden]").val(jsonArray);
    },

    deleteImage: function(context)
    {
        var contextFile = $(context).parent().parent().parent().parent().parent().find("[type=file]");
        var $li = $(context).parent().parent();
        $li.remove();
        TableBuilder.setInputImages(contextFile);
    }, // end deleteImage

    deleteSingleImage: function(ident, context)
    {
        var $imageWrapper = $(context).parent().parent();
        var $imageInput = $(context).parent().parent().parent().parent().find("input[type=hidden]");
        $imageWrapper.hide();
        $imageInput.val("")

    }, // end deleteSingleImage

    doChangeSortingDirection: function(ident, context)
    {
        // TableBuilder.showPreloader();

        var $context = jQuery(context);

        var isAscDirection = $context.hasClass('sorting_asc');
        var direction = isAscDirection ? 'desc' : 'asc';

        var vals = [
            {name: "query_type", value: "change_direction"},
            {name: "direction", value: direction},
            {name: "field", value: ident},
            {name: "__node", value: TableBuilder.getUrlParameter('node')}
        ];

        jQuery.ajax({
            data: vals,
            type: "POST",
            url: TableBuilder.getActionUrl(),
            cache: false,
            dataType: "json",
            success: function(response) {
                doAjaxLoadContent(window.location.href);
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);
                TableBuilder.showErrorNotification(errorResult.message);
            }
        });
    }, // end doChangeSortingDirection

    uploadFile: function(context, ident)
    {
        var data = new FormData();
        data.append("file", context.files[0]);
        data.append('query_type', 'upload_file');
        data.append('ident', ident);
        data.append('__node', TableBuilder.getUrlParameter('id_tree'));

        jQuery.ajax({
            data: data,
            type: "POST",
            url: TableBuilder.getActionUrl(),
            cache: false,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.status) {
                    $(context).parent().next().val(response.long_link);

                    var html = '<a href="'+ response.link +'" target="_blank">Скачать</a>';
                    $(context).parent().parent().next().html(html);

                } else {
                    TableBuilder.showErrorNotification("Ошибка при загрузке файла");
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);

                TableBuilder.showErrorNotification(errorResult.message);
                TableBuilder.hidePreloader();
            }
        });
    }, // end uploadFile


    uploadFileMulti : function(context, ident)
    {
        var arr = context.files;
        for (var index = 0; index < arr.length; ++index) {
            var data = new FormData();
            data.append("file", context.files[index]);
            data.append('query_type', 'upload_file');
            data.append('ident', ident);
            data.append('__node', TableBuilder.getUrlParameter('id_tree'));
            var $progress = jQuery(context).parent().parent().parent().parent().parent().find('.progress-bar');

            jQuery.ajax({
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        console.log(evt);
                        if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total;
                            percentComplete = percentComplete * 100;
                            percentComplete = percentComplete +'%';
                            $progress.width(percentComplete);
                        }
                    }, false);

                    xhr.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total;
                        }
                    }, false);

                    return xhr;
                },
                data: data,
                type: "POST",
                url: TableBuilder.getActionUrl(),
                cache: false,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.status) {

                        $progress.width('0%');
                        var html = '<li>' + response.short_link + '<a href="'+ response.link +'" target="_blank" path="'+response.long_link+'">Скачать</a> <a class="delete" onclick="TableBuilder.doDeleteFile(this)">Удалить</a></li>';

                        $(context).parents(".multi_files").find(".uploaded-files ul").append(html);

                        TableBuilder.doSetInputFiles($(context).parents(".multi_files").find(".uploaded-files ul"));

                        TableBuilder.doSortFileUpload();
                    } else {
                        TableBuilder.showErrorNotification("Ошибка при загрузке файла");
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    var errorResult = jQuery.parseJSON(xhr.responseText);

                    TableBuilder.showErrorNotification(errorResult.message);
                    TableBuilder.hidePreloader();
                }
            });
        }
    }, // end uploadFileMulti

    doSetInputFiles : function(context)
    {
        var arrFiles = new Array();
        $(context).find('li a' ).each(function( index ) {
            if ($(this).attr("path") != null) {
                arrFiles.push($(this).attr("path"));
            }
        });
        var jsonArray = JSON.stringify(arrFiles);
        if (jsonArray == "[]") {
            jsonArray = "";
        }
        $(context).parent().parent().parent().find("[type=hidden]").val(jsonArray);
    },

    doDeleteFile : function(context)
    {
        var ul = $(context).parent().parent();
        $(context).parent().remove();
        TableBuilder.doSetInputFiles(ul);

        return false;
    },

    doSortFileUpload : function ()
    {
        $('.uploaded-files ul').sortable(
            {
                items: "> li",
                update: function( event, ui ) {
                    TableBuilder.doSetInputFiles($(this));
                }
            }
        );
    },

    showErrorNotification: function(message)
    {
        jQuery.smallBox({
            title : message,
            content : "",
            color : "#C46A69",
            iconSmall : "fa fa-times fa-2x fadeInRight animated",
            timeout : 10000,
            class : "error"
        });
    }, // end showErrorNotification

    showSuccessNotification: function(message)
    {
        jQuery.smallBox({
            title : message,
            content : "",
            color : "#659265",
            iconSmall : "fa fa-check fadeInRight animated",
            timeout : 3000
        });
    }, // end showSuccessNotification

    setPerPageAmount: function(perPage)
    {
        TableBuilder.showProgressBar();

        var data = {
            query_type: "set_per_page",
            per_page: perPage,
            "__node": TableBuilder.getUrlParameter('node')
        };

        var $posting = jQuery.post(TableBuilder.getActionUrl(), data);
        $posting.done(function(response) {
            window.location.replace(window.location.origin + window.location.pathname + window.location.search);
            //window.location.replace(response.url);
        });
    }, // end setPerPageAmount

    doExport: function(type, urlBasic)
    {
        TableBuilder.showPreloader();

        var $iframe = jQuery("#submiter");

        var values = jQuery(TableBuilder.export_form).serializeArray();
        values.push({ name: 'type', value: type });
        values.push({ name: 'query_type', value: "export" });

        var out = new Array();
        jQuery.each(values, function(index, val) {
            out.push(val['name'] +'='+ val['value']);
        });

        if (urlBasic == undefined) {
            urlBasic = document.location.pathname;
        }

        if (document.location.search) {
            var url = urlBasic + document.location.search + '&' + out.join('&');
        } else {
            var url = urlBasic +'?'+ out.join('&');
        }

        $iframe.attr('src', url);

        TableBuilder.hidePreloader();
    }, // end doExport

    flushStorage: function()
    {
        TableBuilder.storage = {};
    }, // end flushStorage

    showBigErrorNotification: function(errors)
    {
        jQuery.bigBox({
            content : errors,
            color   : "#C46A69",
            icon    : "fa fa-warning shake animated"
        });
    }, // end showBigErrorNotification

    doImport: function(context, type, url)
    {
        if (url == undefined) {
            url = TableBuilder.getActionUrl();
        }

        TableBuilder.showPreloader();

        var data = new FormData();
        data.append("file", context.files[0]);
        data.append('type', type);
        data.append('query_type', 'import');

        jQuery.SmartMessageBox({
            title : "Произвести импорт?",
            content : "Результат импорта нельзя отменить",
            buttons : '[Нет][Да]'
        }, function(ButtonPressed) {
            if (ButtonPressed === "Да") {

                jQuery.ajax({
                    data: data,
                    type: "POST",
                    url: url,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.status) {
                            TableBuilder.showSuccessNotification('Импорт прошел успешно');
                        } else {
                            if (typeof response.errors === "undefined") {
                                TableBuilder.showErrorNotification('Что-то пошло не так');
                            } else {
                                var errors = '';
                                jQuery(response.errors).each(function(key, val) {
                                    errors += val +'<br>';
                                });
                                TableBuilder.showBigErrorNotification(errors);
                            }
                        }
                        TableBuilder.hidePreloader();
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        var errorResult = jQuery.parseJSON(xhr.responseText);

                        TableBuilder.showErrorNotification(errorResult.message);
                        TableBuilder.hidePreloader();
                    }
                });

            } else {
                TableBuilder.hidePreloader();
            }
        });
    }, // end doImport

    doDownloadImportTemplate: function(type)
    {
        TableBuilder.showPreloader();

        var $iframe = jQuery("#submiter");

        var values = new Array();
        values.push('type='+ type);
        values.push('query_type=get_import_template');

        var url = document.location.pathname +'?'+ values.join('&');

        $iframe.attr('src', url);

        TableBuilder.hidePreloader();
    }, // end doDownloadImportTemplate

    doSelectAllMultiCheckboxes: function(context)
    {
        var isChecked = jQuery('input', context).is(':checked');
        var $multiActionCheckboxes = jQuery('.multi-checkbox input');

        $multiActionCheckboxes.prop('checked', isChecked);
    }, // end doSelectAllMultiCheckboxes

    doMultiActionCall: function(type)
    {
        TableBuilder.showPreloader();

        var values = jQuery('#'+ TableBuilder.options.table_ident).serializeArray();
        values.push({ name: 'type', value: type });
        values.push({ name: 'query_type', value: 'multi_action' });
        values.push({ name: '__node', value: TableBuilder.getUrlParameter('node') });

        jQuery.ajax({
            type: "POST",
            url: TableBuilder.getActionUrl(),
            data: values,
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    if (response.is_hide_rows) {
                        jQuery(response.ids).each(function(key, val) {
                            jQuery('tr[id-row="'+ val +'"]', '#'+ TableBuilder.options.table_ident).remove();
                        });
                    }

                    TableBuilder.showSuccessNotification(response.message);
                } else {
                    if (typeof response.errors === "undefined") {
                        TableBuilder.showErrorNotification('Что-то пошло не так');
                    } else {
                        var errors = '';
                        jQuery(response.errors).each(function(key, val) {
                            errors += val +'<br>';
                        });
                        TableBuilder.showBigErrorNotification(errors);
                    }
                }

                TableBuilder.hidePreloader();
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);

                TableBuilder.showErrorNotification(errorResult.message);
                TableBuilder.hidePreloader();
            }
        });
    }, // end doMultiActionCall

    doMultiActionCallWithOption: function(context, type, option)
    {
        TableBuilder.showPreloader();

        var values = jQuery('#'+ TableBuilder.options.table_ident).serializeArray();
        values.push({ name: 'type', value: type });
        values.push({ name: 'option', value: option });
        values.push({ name: 'query_type', value: 'multi_action_with_option' });
        values.push({ name: '__node', value: TableBuilder.getUrlParameter('node') });

        jQuery.ajax({
            type: "POST",
            url: TableBuilder.getActionUrl(),
            data: values,
            dataType: 'json',
            success: function(response) {
                jQuery(context).parent().parent().parent().removeClass('open');

                if (response.status) {
                    if (response.is_hide_rows) {
                        jQuery(response.ids).each(function(key, val) {
                            jQuery('tr[id-row="'+ val +'"]', '#'+ TableBuilder.options.table_ident).remove();
                        });
                    }

                    TableBuilder.showSuccessNotification(response.message);
                } else {
                    if (typeof response.errors === "undefined") {
                        TableBuilder.showErrorNotification(phrase['Что-то пошло не так, попробуйте позже']);
                    } else {
                        var errors = '';
                        jQuery(response.errors).each(function(key, val) {
                            errors += val +'<br>';
                        });
                        TableBuilder.showBigErrorNotification(errors);
                    }
                }

                TableBuilder.hidePreloader();
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);

                TableBuilder.showErrorNotification(errorResult.message);
                TableBuilder.hidePreloader();
            }
        });
    }, // end doMultiActionCallWithOption

    saveOrder: function(order)
    {
        jQuery.ajax({
            type: "POST",
            url: TableBuilder.getActionUrl(),
            data: { order: order, params: window.location.search, query_type: 'change_order' },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    TableBuilder.showSuccessNotification(phrase['Порядок следования изменен']);
                } else {
                    if (response.message) {
                        TableBuilder.showErrorNotification(response.message);
                    } else {
                        TableBuilder.showErrorNotification(phrase['Что-то пошло не так, попробуйте позже']);
                    }

                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);

                TableBuilder.showErrorNotification(errorResult.message);
                TableBuilder.hidePreloader();
            }
        });
    }, // end saveOrder

    openImageStorageModal: function(context, storageTypeSelect)
    {
        jQuery.ajax({
            type: "POST",
            url: TableBuilder.getActionUrl(),
            data: { query_type: 'image_storage', storage_type: 'show_modal', "__node": TableBuilder.getUrlParameter('node'), storage_type_select: storageTypeSelect },
            dataType: 'json',
            success: function(response) {

                if (response.status) {
                    $(TableBuilder.image_storage_wrapper).html(response.html);
                    $('.image_storage_wrapper').show();
                    $('.tb-modal:visible').addClass('superbox-modal-hide').hide();

                    Superbox.input = $(context).parent().parent().find('input');
                    Superbox.type_select = storageTypeSelect;
                } else {
                    TableBuilder.showErrorNotification(phrase['Что-то пошло не так, попробуйте позже']);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);

                TableBuilder.showErrorNotification(errorResult.message);
                TableBuilder.hidePreloader();
            }
        });
    }, // end openImageStorageModal

    closeImageStorageModal: function()
    {
        $('.image_storage_wrapper').hide();
        $('.superbox-modal-hide').removeClass('superbox-modal-hide').show();
    }, // end closeImageStorageModal

    openFileStorageModal: function(context)
    {
        jQuery.ajax({
            type: "POST",
            url: TableBuilder.getActionUrl(),
            data: { query_type: 'file_storage', storage_type: 'show_modal', "__node": TableBuilder.getUrlParameter('node') },
            dataType: 'json',
            success: function(response) {

                if (response.status) {
                    $(TableBuilder.image_storage_wrapper).html(response.html);
                    $('.image_storage_wrapper').show();
                    $('.tb-modal:visible').addClass('superbox-modal-hide').hide();

                    FileStorage.input = $(context).parent().parent().find('input');
                } else {
                    TableBuilder.showErrorNotification(phrase['Что-то пошло не так, попробуйте позже']);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);

                TableBuilder.showErrorNotification(errorResult.message);
                TableBuilder.hidePreloader();
            }
        });
    }, // end openFileStorageModal

    reLoadTable : function ()
    {
        //  alert(window.location.href);
    }, //end reLoadTable

    addGroup : function(context)
    {
        var sectionGroup = $(context).parent().find(".section_group").first().clone();
        if ($(sectionGroup).find('input[data-multi=multi]').size() > 1) {
            $(sectionGroup).find('input[data-multi=multi]').not(":first").remove();

            //   sectionGroup = $(sectionGroup).find('input[data-multi=multi]').first();
        }
        $(sectionGroup).find("input, textarea").val("");
        $(sectionGroup).find(".tb-uploaded-image-container").html("<ul class='dop_foto'></ul>");
        $(sectionGroup).find(".uploaded-files").html("<ul class='ui-sortable'></ul>");

        $(context).parent().find(".other_section").append(sectionGroup);

        TableBuilder.refreshMask();
    },

    addMoreInput : function (context) {
        var labelInput = $(context).parents('.div_input').find("label.input").first().clone();
        var contentInput = $(context).parents('.div_input').find('.input_content');
        labelInput.find("input").val("");
        contentInput.append(labelInput);

        TableBuilder.refreshMask();
    },

    deleteGroup : function(context)
    {
        var sizeGroup = $(context).parent().parent().parent().find(".section_group").size();
        var sectionGroup = $(context).parent().parent();
        if (sizeGroup == 1) {
            $(sectionGroup).find("input, textarea").val("");
            $(sectionGroup).find(".tb-uploaded-image-container").html("");
        } else {
            $(sectionGroup).remove();
        }
    },

    handleActionSelect : function()
    {

        var selectAction = $("select.action");
        if (selectAction.size() != 0) {
            var value = selectAction.val();
            TableBuilder.checkActionSelect(value);

            selectAction.change(function(){
                var value = selectAction.val();
                TableBuilder.checkActionSelect(value);
            });
        }
    },

    checkActionSelect : function(value)
    {
        $("section.section_field").hide();
        $("section.section_field." + value).show();
    },

    doActiveMenu : function () {

        var thisUrl = window.location.pathname;

        $('nav a[href="' + thisUrl +'"]').parent().addClass('active');
        if (!$('nav a[href="' + thisUrl +'"]').parents('.level1').hasClass('active')) {
            $('nav a[href="' + thisUrl +'"]').parents('.level1').addClass('open');
        }
        $('nav a[href="' + thisUrl +'"]').parents('.level1').find('ul').show();
    },

};

$(window).load(function() {
    TableBuilder.initFroalaEditor();
    TableBuilder.handleStartLoad();
    TableBuilder.initSearchOnEnterPressed();
    $(document).on('click', 'a.node_link', function (e) {
        var href = $(this).attr('href');
        doAjaxLoadContent(href);
        e.preventDefault();
    });

});
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

