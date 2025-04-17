<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalEditLabel">{{ __('map.custom.nodeimage.title.edit') }}</h5>
                <h5 class="modal-title" id="imageModalNewLabel">{{ __('map.custom.nodeimage.title.new') }}</h5>
            </div>

            <div class="modal-body tw:p-10">
                <x-input id="nodeimage"
                         x-ref="nodeimage"
                         type="file"
                         label="{{ __('map.custom.nodeimage.upload') }}"
                         accept="image/png,image/jpeg,image/svg+xml,image/gif"
                         onChange="imageModalSetImage(event)">
                </x-input>
                <x-input id="nodeimagename"
                         x-ref="nodeimagename"
                         type="text"
                         value=""
                         label="{{ __('map.custom.nodeimage.name') }}">
                </x-input>
                <div>
                    <div class="tw:text-red-600" id="nodeimageerror"></div>
                </div>
            </div>

            <div class="modal-footer">
                <button type=button class="btn btn-primary" onClick="imageModalSave()">{{ __('Save') }}</button>
                <button type=button class="btn btn-default" onClick="imageModalClose()">{{ __('Cancel') }}</button>
            </div>
        </div>
    </div>
</div>
<script>
    var imageModalData = {};
    $('#imageModal').on('show.bs.modal', () => {
        if(imageModalData.image_id) {
            $("#imageModalEditLabel").show();
            $("#imageModalNewLabel").hide();
        } else {
            $("#imageModalEditLabel").hide();
            $("#imageModalNewLabel").show();
        }
    });

    function imageModalEdit(image_id) {
        imageModalData.image_id = image_id;
        imageModalData.image_name = $("#imagename-" + image_id).text().trim();
        imageModalReset();
        $('#imageModal').modal({backdrop: 'static', keyboard: false}, 'show');
        $("#nodeimage").val('');
    }

    function imageModalReset() {
        imageModalData.image_content = null;
        $("#nodeimagename").val(imageModalData.image_name);
    }

    function imageModalSetImage(event) {
        imageModalData.image_content = event.target.files[0];
        $("#nodeimagename").val(imageModalData.image_content.name.split(".")[0]);
    }

    function imageModalSave() {
        if (! imageModalData.image_content && ! imageModalData.image_id) {
            this.imageModalClose();
            return;
        }

        let fd = new FormData();
        if(imageModalData.image_content) {
            fd.append('image', imageModalData.image_content);
        }
        fd.append('name', $("#nodeimagename").val());

        let url = null;
        let method = null;
        if(imageModalData.image_id) {
            url = '{{ route('maps.nodeimage.update', ["image" => "?"]) }}'.replace('?', imageModalData.image_id);
        } else {
            url = '{{ route('maps.nodeimage.store') }}';
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.head.querySelector('meta[name=\'csrf-token\']').content
            },
            body: fd
        }).then((response) => {
            if (response.status === 413) {
                $("#nodeimageerror").text(response.statusText);
                $("#nodeimageerror").show();
                return;
            }

            response.json().then(data => {
                if (data.message) {
                    $("#nodeimageerror").text(data.message);
                    $("#nodeimageerror").show();
                } else {
                    $("#nodeimageerror").hide();
                    imageModalClose();

                    if(imageModalData.image_id) {
                        imgurl = $("#imageview-" + imageModalData.image_id).attr("src").split('?')[0];
                        $("#imageview-" + imageModalData.image_id).attr("src", imgurl + "?" + data.version)
                        $("#imagename-" + imageModalData.image_id).text(data.name)
                    } else {
                        appendImageRow(data.id, data.name);
                    }
                }
            })
        })
        .catch(() => {
            $("#nodeimageerror").text('Ooops! Something went wrong!');
            $("#nodeimageerror").show();
        });
    }

    function imageModalClose() {
        $('#imageModal').modal('hide');
    }
</script>
