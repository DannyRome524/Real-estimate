jQuery(document).ready(function($) {
    $('.rem-select2-field').select2(); 
    $('.select2-container').width('100%');
    jQuery(".labelauty").labelauty();
    var rem_property_images;

    $('.general_settings-fields > div').matchHeight({byRow: false});
     
    jQuery('.info-block').on('click', '.upload_image_button', function( event ){
     
        event.preventDefault();
     
        // var parent = jQuery(this).closest('.tab-content').find('.thumbs-prev');
        // Create the media frame.
        rem_property_images = wp.media.frames.rem_property_images = wp.media({
          title: jQuery( this ).data( 'title' ),
          button: {
            text: jQuery( this ).data( 'btntext' ),
          },
          multiple: true  // Set to true to allow multiple files to be selected
        });
     
        // When an image is selected, run a callback.
        rem_property_images.on( 'select', function() {
            // We set multiple to false so only get one image from the uploader
            var selection = rem_property_images.state().get('selection');
            var already_selected = parseInt($('body .thumbs-prev > div').length);
            var new_selected = parseInt(selection.length);
            var total_images = already_selected + new_selected;
            // rem_property_vars.images_limit is localized variable
            if (total_images > rem_property_vars.images_limit && rem_property_vars.images_limit != 0) {
                alert(rem_property_vars.images_limit_message+rem_property_vars.images_limit);
            };
            selection.map( function( attachment, index ) {
                if ( index < (parseInt(rem_property_vars.images_limit) - already_selected ) || rem_property_vars.images_limit == 0 ) {
                    attachment = attachment.toJSON();
                    jQuery('.thumbs-prev').append('<div><input type="hidden" name="rem_property_data[property_images]['+attachment.id+']" value="'+attachment.id+'"><img src="'+attachment.url+'"><span class="dashicons dashicons-dismiss"></span></div>');
                };

            });
        });
     
        // Finally, open the modal
        rem_property_images.open();
    });
    jQuery('.thumbs-prev').on('click', '.dashicons-dismiss', function() {
        jQuery(this).parent('div').remove();
    });
    jQuery(".thumbs-prev").sortable({
      placeholder: "ui-state-highlight"
    });

    // Attachments Related
    var rem_attachments;
     
    jQuery('.info-block').on('click', '.upload-attachment', function( event ){
     
        event.preventDefault();
        var text_field = $(this).closest('div').find('.place-attachment');
     
        // var parent = jQuery(this).closest('.tab-content').find('.thumbs-prev');
        // Create the media frame.
        rem_attachments = wp.media.frames.rem_attachments = wp.media({
          title: jQuery( this ).data( 'title' ),
          button: {
            text: jQuery( this ).data( 'btntext' ),
          },
          multiple: true  // Set to true to allow multiple files to be selected
        });
     
        // When an image is selected, run a callback.
        rem_attachments.on( 'select', function() {
            // We set multiple to false so only get one image from the uploader
            var selection = rem_attachments.state().get('selection');
            selection.map( function( attachment ) {
                attachment = attachment.toJSON();
                if (text_field.val() != '') {
                    text_field.val( text_field.val() + '\n'+attachment.id);
                } else {
                    text_field.val( text_field.val() +attachment.id);
                }

            });
        });
     
        // Finally, open the modal
        rem_attachments.open();
    });    
    // preving property 
    jQuery('#form-submit').click(function(event){
        if ( $('.preview_property').length > 0 ) {
            $('.preview_property').remove();
        }
    });
    jQuery('#preview-property').click(function(event){
        event.preventDefault();
        var el = '<input type="hidden" class="preview_property" name="preview_property">';
        if ( jQuery('#title').val() != '' ) {

            if ( $('.preview_property').length == 0 ) {
                $('#create-property').append(el);
            }
            $('#create-property').submit();
        } else {
            alert("Title is required");
            jQuery( "#title" ).focus();
        };
        
    });
    // Creating Property
    jQuery('#create-property').submit(function(event){
        event.preventDefault();
        $('.creating-prop').show();
        
        if (jQuery("#wp-rem-content-wrap").hasClass("tmce-active")){
            content = tinyMCE.get('rem-content').getContent();
        }else{
            content = $('#rem-content').val();
        }        

        var ajaxurl = $(this).data('ajaxurl');
        var data = $(this).serialize()+"&content="+encodeURIComponent(content);

        $.post(ajaxurl, data , function(resp) {
            // console.log(resp);
            if (typeof resp.property_id != "undefined" ) {
                jQuery('.preview_property').val(resp.property_id);
                // window.location = resp.preview_link;
                if ( $('.property_preview_id').length == 0 ) {
                    var el = '<input type="hidden" class="property_preview_id" name="property_preview_id" value="'+resp.property_id+'">';
                    $('#create-property').append(el);
                } else {
                    $('.property_preview_id').val(resp.property_id);
                }
                $('.creating-prop').hide();
                var win = window.open(resp.preview_link, '_blank');
                if (win) {
                    //Browser has allowed it to be opened
                    win.focus();
                } else {
                    //Browser has blocked it
                    alert('Please allow popups for this website');
                }
            }else{
                $('.creating-prop').removeClass('alert-info').addClass('alert-success');
                $('.creating-prop .msg').html(rem_property_vars.success_message);
                window.location = resp;
            };
        });
    });    
});

function rem_edit_property_initialize() {

    var map = new google.maps.Map(document.getElementById('map-canvas'), {
        center: new google.maps.LatLng(rem_property_vars.def_lat, rem_property_vars.def_long),
        scrollwheel: false,
        zoom: parseInt(rem_property_vars.zoom_level),
        styles: (rem_property_vars.maps_styles != '') ? JSON.parse(rem_property_vars.maps_styles) : '',
    });

    var marker = new google.maps.Marker({
        position: new google.maps.LatLng(rem_property_vars.def_lat, rem_property_vars.def_long),
        map: map,
        icon: rem_property_vars.drag_icon,
        draggable: true
    });
    
    google.maps.event.addListener(marker, 'drag', function(event) {
        jQuery('#property_latitude').val(event.latLng.lat());
        jQuery('#property_longitude').val(event.latLng.lng());
        jQuery('#position').text('Position: ' + event.latLng.lat() + ' , ' + event.latLng.lng() );
    });
    google.maps.event.addListener(marker, 'dragend', function(event) {
        jQuery('#property_latitude').val(event.latLng.lat());
        jQuery('#property_longitude').val(event.latLng.lng());
        jQuery('#position').text('Position: ' + event.latLng.lat() + ' , ' + event.latLng.lng() );
    });


    var searchBox = new google.maps.places.SearchBox(document.getElementById('search-map'));
    // map.controls[google.maps.ControlPosition.TOP_LEFT].push(document.getElementById('search-map'));
    google.maps.event.addListener(searchBox, 'places_changed', function() {
        searchBox.set('map', null);
        marker.setMap(null);

        var places = searchBox.getPlaces();

        var bounds = new google.maps.LatLngBounds();
        var i, place;
        for (i = 0; place = places[i]; i++) {
            (function(place) {
                var marker = new google.maps.Marker({
                    position: place.geometry.location,
                    map: map,
                    icon: rem_property_vars.drag_icon,
                    draggable: true
                });
                var location = place.geometry.location;
                var n_lat = location.lat();
                var n_lng = location.lng();
                jQuery('#property_latitude').val(n_lat);
                jQuery('#property_longitude').val(n_lng);
                jQuery('#position').text('Position: ' + n_lat + ' , ' + n_lng );                        
                marker.bindTo('map', searchBox, 'map');
                google.maps.event.addListener(marker, 'map_changed', function(event) {
                    if (!this.getMap()) {
                        this.unbindAll();
                    }
                });
                google.maps.event.addListener(marker, 'drag', function(event) {
                    jQuery('#property_latitude').val(event.latLng.lat());
                    jQuery('#property_longitude').val(event.latLng.lng());
                    jQuery('#position').text('Position: ' + event.latLng.lat() + ' , ' + event.latLng.lng() );
                });
                google.maps.event.addListener(marker, 'dragend', function(event) {
                    jQuery('#property_latitude').val(event.latLng.lat());
                    jQuery('#property_longitude').val(event.latLng.lng());
                    jQuery('#position').text('Position: ' + event.latLng.lat() + ' , ' + event.latLng.lng() );
                });                                             
                bounds.extend(place.geometry.location);


            }(place));

        }
        map.fitBounds(bounds);
        searchBox.set('map', map);
        map.setZoom(Math.min(map.getZoom(), parseInt(rem_property_vars.zoom_level)));

    });
}
if (rem_property_vars.def_lat != 'disable' && rem_property_vars.use_map_from == 'google_maps') {
    google.maps.event.addDomListener(window, 'load', rem_edit_property_initialize);
}
jQuery(document).ready(function($) {
    if (rem_property_vars.use_map_from == 'leaflet' && $.isFunction($.fn.L)) {
        var property_map = L.map('map-canvas').setView([rem_property_vars.def_lat, rem_property_vars.def_long], parseInt(rem_property_vars.zoom_level));
        
        L.tileLayer(rem_property_vars.leaflet_styles.provider, {
                maxZoom: 21,
                attribution: rem_property_vars.leaflet_styles.provider.attribution
            }).addTo(property_map);
        var propertyIcon = L.icon({
            iconUrl: rem_property_vars.drag_icon,
            iconSize: [72, 60],
            iconAnchor: [36, 47],
        });
        var marker = L.marker([rem_property_vars.def_lat, rem_property_vars.def_long], {icon: propertyIcon, draggable: true}).addTo(property_map);
        var geocoder = L.Control.geocoder({
            defaultMarkGeocode: false
        })
        .on('markgeocode', function(event) {
            var center = event.geocode.center;
            property_map.setView(center, property_map.getZoom());
            marker.setLatLng(center);
        }).addTo(property_map);        
        marker.on('dragend', function (e) {
            jQuery('#property_latitude').val(marker.getLatLng().lat);
            jQuery('#property_longitude').val(marker.getLatLng().lng);
            jQuery('#position').text('Position: ' + marker.getLatLng().lat + ' , ' + marker.getLatLng().lng );            
        });
        marker.on('drag', function (e) {
            jQuery('#property_latitude').val(marker.getLatLng().lat);
            jQuery('#property_longitude').val(marker.getLatLng().lng);
            jQuery('#position').text('Position: ' + marker.getLatLng().lat + ' , ' + marker.getLatLng().lng );            
        });
        jQuery('.leaflet-control-geocoder-form input').keypress(function(e){
            if ( e.which == 13 ) e.preventDefault();
        });        
        // marker.bindPopup("<b>Hello world!</b><br>I am a popup.");
        if (rem_property_vars.maps_styles != '') {
            // console.log(rem_property_vars.maps_styles);
            // L.geoJSON(JSON.parse(rem_property_vars.maps_styles)).addTo(property_map);
        }
    }    
});