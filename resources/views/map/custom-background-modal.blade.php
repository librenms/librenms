<div class="modal fade" id="bgModal" tabindex="-1" role="dialog" aria-labelledby="bgModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" x-data="backgroundModalData()" x-init="resetBackground">
            <div class="modal-header">
                <h5 class="modal-title" id="bgModalLabel">{{ __('map.custom.edit.bg.title') }}</h5>
            </div>

            <div class="modal-body tw:p-10">
                <x-tabs class="tw:text-2xl" @tab-change="type=$event.detail" x-effect="activeTab = type">
                    <x-tab value="image" name="{{ __('map.custom.edit.bg.image') }}" class="tw:mt-10">
                        <x-input id="bgimage"
                                 x-ref="bgimage"
                                 type="file"
                                 label="{{ __('map.custom.edit.bg.background') }}"
                                 accept="image/png,image/jpeg,image/svg+xml,image/gif"
                                 x-show="!image"
                                 x-on:change="setImage($event)"></x-input>
                        <div x-show="image">
                            <span x-text="image"></span>
                            <button type="button" class="btn btn-danger" @click="clearImage">{{ __('map.custom.edit.bg.clear_background') }}</button>
                        </div>
                    </x-tab>
                    <x-tab value="color" name="{{ __('map.custom.edit.bg.color') }}" class="tw:mt-10">
                        <x-input id="bg-color" type="color" x-model="color"
                                 class="tw:cursor-pointer tw:h-24 tw:w-48"
                        ></x-input>
                    </x-tab>
                    <x-tab value="map" name="{{ __('map.custom.edit.bg.map') }}" class="tw:mt-5">
                        <x-input id="bg-lat" label="{{ __('map.custom.edit.bg.lat') }}" x-model="lat"></x-input>
                        <x-input id="bg-lng" label="{{ __('map.custom.edit.bg.lng') }}" x-model="lng"></x-input>
                        <x-input id="bg-zoom" label="{{ __('map.custom.edit.bg.zoom') }}" x-model="zoom"></x-input>
                        <button type="button" class="btn btn-primary tw:mt-2" @click="adjustMap">{{ __('map.custom.edit.bg.adjust_map') }}</button>
                        <button type="button" class="btn btn-primary tw:mt-2" @click="setMapAsImage" title="{{ __('map.custom.edit.bg.as_image_hint') }}" :disabled="saving_map_as_image" x-show="show_image_export">
                            <i class="fa-solid fa-circle-notch fa-spin" x-show="saving_map_as_image"></i>
                            {{ __('map.custom.edit.bg.as_image') }}
                        </button>
                    </x-tab>
                    <x-tab value="none" name="{{ __('map.custom.edit.bg.none') }}"></x-tab>
                </x-tabs>
                <div x-show="error">
                    <div class="tw:text-red-600" x-text="error"></div>
                </div>
            </div>

            <div class="modal-footer">
                <button type=button class="btn btn-primary" @click="saveBackground">{{ __('Save') }}</button>
                <button type=button class="btn btn-default" @click="closeBackgroundModal">{{ __('Cancel') }}</button>
            </div>
        </div>
    </div>
</div>
<script>
    function backgroundModalData() {
        return {
            initial_data: {{ Js::from($background_config) }},
            initial_type: {{ Js::from($background_type) }},
            type: 'none',
            color: null,
            lat: null,
            lng: null,
            zoom: null,
            layer: null,
            image: null,
            show_image_export: true,
            image_content: null,
            saving_map_as_image: false,
            error: '',
            resetBackground() {
                this.type = this.initial_type;
                this.color = 'color' in this.initial_data ? this.initial_data.color : '{{ Config::get('custom_map.background_data.color') }}';
                this.lat = 'lat' in this.initial_data ? this.initial_data.lat : {{ (float) Config::get('custom_map.background_data.lat') }};
                this.lng = 'lng' in this.initial_data ? this.initial_data.lng : {{ (float) Config::get('custom_map.background_data.lng') }};
                this.zoom = 'zoom' in this.initial_data ? this.initial_data.zoom : {{ (int) Config::get('custom_map.background_data.zoom') }};
                this.layer = 'layer' in this.initial_data ? this.initial_data.layer :  {{ Js::from(Config::get('custom_map.background_data.layer')) }};
                this.image = this.initial_data['original_filename'];
                this.image_content = null;
                this.show_image_export = (! 'engine' in this.initial_data) || ! ['google', 'bing'].includes(this.initial_data['engine']);
                this.error = '';

                setCustomMapBackground('custom-map', this.type, this.initial_data);
                // stop map interaction
                document.getElementById('custom-map-bg-geo-map').style.zIndex = '1';
                const leaflet = get_map('custom-map-bg-geo-map');
                if (leaflet) {
                    disable_map_interaction(leaflet)
                    leaflet.off('zoomend');
                    leaflet.off('moveend');
                    leaflet.off('baselayerchange');
                    leaflet.setView(L.latLng(this.lat, this.lng), this.zoom);
                }
            },
            setImage(event) {
                this.image_content = event.target.files[0];
            },
            clearImage() {
                this.image = null;
                this.image_content = null;
            },
            setMapAsImage() {
                setCustomMapBackground('custom-map', this.type, this.initial_data);
                this.saving_map_as_image = true;
                leafletImage(get_map('custom-map-bg-geo-map'), (err, canvas) => {
                    if (! canvas) {
                        this.error = err;
                        return;
                    }

                    this.type = 'image';
                    this.image = 'geo-map.jpg';
                    canvas.toBlob((blob) => this.image_content = blob, 'image/jpeg', 0.5);

                    this.saving_map_as_image = false;
                });
            },
            saveBackground() {
                if (this.type === 'image' && ! this.image_content) {
                    // change to none type when saving bg image with no file
                    // helps with mental work flow of clicking clear image -> save.
                    this.type = 'none';
                }

                let fd = new FormData();
                fd.append('type', this.type);
                if (this.type === 'color') {
                    fd.append('color', this.color);
                } else if (this.type === 'image') {
                    fd.append('image', this.image_content, this.image);
                }
                if (this.type === 'map' || this.image === 'geo-map.png') {
                    // include map data when we converted a map to a static image
                    fd.append('lat', this.lat);
                    fd.append('lng', this.lng);
                    fd.append('zoom', this.zoom);
                    fd.append('layer', this.layer);
                }

                fetch({{ Js::from(route('maps.custom.background.save', ['map' => $map_id])) }}, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.head.querySelector('meta[name=\'csrf-token\']').content
                    },
                    body: fd
                }).then((response) => {
                    if (response.status === 413) {
                        this.error = response.statusText;
                        return;
                    }

                    response.json().then(data => {
                        if (data.message) {
                            this.error = data.message;
                        } else {
                            setCustomMapBackground('custom-map', data.bgtype, data.bgdata);
                            this.initial_type = data.bgtype;
                            this.initial_data = data.bgdata;

                            // update jquery code
                            if (bgtype) {
                                bgtype = data.bgtype;
                            }
                            if (bgdata) {
                                bgdata = data.bgdata;
                            }

                            this.closeBackgroundModal();
                        }
                    })
                })
                .catch(() => {
                    this.error = 'Ooops! Something went wrong!'
                });
            },
            adjustMap() {
                let leaflet = init_map('custom-map-bg-geo-map', this.initial_data);
                let adjustValues = () => {
                    const center = leaflet.getCenter();
                    this.lat = center.lat;
                    this.lng = center.lng;
                    this.zoom = leaflet.getZoom();
                }
                let layerChange = (event) => {this.layer = event.name};
                leaflet._container.style.zIndex = '3';
                enable_map_interaction(leaflet);
                leaflet.on({
                    zoomend: adjustValues,
                    moveend: adjustValues,
                    baselayerchange: layerChange,
                });
                startBackgroundMapAdjust();
                $('#bgModal').modal('hide');
            },
            closeBackgroundModal() {
                $('#bgModal').modal('hide');
                this.resetBackground();
            }
        }
    }

    function startBackgroundMapAdjust() {
        $('#map-editButton,#map-nodeDefaultsButton,#map-edgeDefaultsButton,#map-bgButton').hide();
        $('#map-bgEndAdjustButton').show();
    }

    function endBackgroundMapAdjust() {
        $('#map-editButton,#map-nodeDefaultsButton,#map-edgeDefaultsButton,#map-bgButton').show();
        $('#map-bgEndAdjustButton').hide();

        document.getElementById('custom-map-bg-geo-map').style.zIndex = '1';
        const leaflet = get_map('custom-map-bg-geo-map');
        if (leaflet) {
            disable_map_interaction(leaflet)
        }
        editMapBackground();
    }
</script>
