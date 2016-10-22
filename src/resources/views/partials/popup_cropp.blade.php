<div class="modal fade" id="modal_crop_img" aria-labelledby="modalLabel" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalLabel">Обрезать изображение</h4>
            </div>
            <div class="modal-body">
                <div>
                    <img id="image" src="" alt="Picture">
                </div>
            </div>
            <div class="modal-footer">
                <table style="width: 100%;">
                    <tr>
                        <td style="text-align: left">
                            W: <span class="width_crop"></span>px
                            H: <span class="height_crop"></span>px
                        </td>
                        <td style="text-align: right">
                            <button type="button" class="btn btn-default close_crop">Закрыть</button>
                            <button type="button" class="btn btn-default download" onclick="Cropper.download()">Сохранить</button>
                        </td>
                    </tr>
                </table>

            </div>
        </div>
    </div>
</div>
