'use strict';

var Cropper = {

    image: $('#modal_crop_img #image'),
    imgThis: '',

    init: function () {
        var cropBoxData;
        var canvasData;
        $('#modal_crop_img').on('shown.bs.modal', function (event) {

            Cropper.imgThis = $(event.relatedTarget);
            var srcImg = Cropper.imgThis.attr("src_original");
            $("#modal_crop_img #image").attr("src", "");
            $("#modal_crop_img #image").attr("src", "/" + srcImg);
            $("#modal_crop_img").css("top", $(window).scrollTop() + 20);

            var result = Cropper.image.cropper({

                crop: function (data) {
                    $(".width_crop").text(Math.round(data.width));
                    $(".height_crop").text(Math.round(data.height));
                },
                built: function () {

                },
            });
        }).on('hidden.bs.modal', function () {
            cropBoxData = Cropper.image.cropper('getCropBoxData');
            canvasData = Cropper.image.cropper('getCanvasData');
            Cropper.image.cropper('destroy');
        });

        $("#modal_crop_img .close_crop, #modal_crop_img .close").click(function () {
            $("#modal_crop_img").modal("hide");
            $("body").addClass("modal-open");
        });
    },

    download: function () {
        $("#modal_crop_img").modal("hide");
        $("body").addClass("modal-open");
        var result = Cropper.image.cropper('getCroppedCanvas');
        var srcImg = $("#modal_crop_img #image").attr("src");

        $.post("/admin/save_croped_img", {
                data: result.toDataURL(),
                originalImg: srcImg,
                width: Cropper.imgThis.attr('data-width'),
                height: Cropper.imgThis.attr('data-height'),
            },
            function (data) {
                if (data.status == "success") {
                    Cropper.imgThis.attr("src", data.pictureSmall);
                    $("[name=" + Cropper.imgThis.attr('data-tbident') + "]").val(data.picture);
                    Cropper.imgThis.attr('data_src_original', data.picture);
                    if (TableBuilder.tableEditorImg != null) {
                        TableBuilder.tableEditorImg.attr('src', "/" + data.picture);
                    }
                    var contextFile = Cropper.imgThis.parents(".multi_pictures").find("[type=file]");
                    TableBuilder.setInputImages(contextFile);
                }
            }, "json");
    },

}

Cropper.init();
