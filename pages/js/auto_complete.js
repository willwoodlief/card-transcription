    // https://developers.google.com/maps/documentation/javascript/examples/places-autocomplete-addressform


    var  autocomplete, b_address_filled_in= false;


    function initAutocomplete() {
        // Create the autocomplete object, restricting the search to geographical
        // location types.
        autocomplete = new google.maps.places.Autocomplete(
            /** @type {!HTMLInputElement} */(document.getElementById('address')),
            {types: ['geocode']});

        // When the user selects an address from the dropdown, populate the address
        // fields in the form.
        autocomplete.addListener('place_changed', fillInAddress);
    }

    function fillInAddress() {
        var addwrap = $('.address-wrapper');
        addwrap.removeClass('large-address');

        var componentForm = {
            street_number: 'short_name',
            route: 'long_name',
            locality: 'long_name',
            administrative_area_level_1: 'long_name',
            administrative_area_level_2: 'short_name',
            country: 'long_name',
            postal_code: 'short_name'
        };

        var ourFieldLooks = {
            street_number: 'street_number',
            route: 'address',
            locality: 'city',
            administrative_area_level_2: 'county',
            administrative_area_level_1: 'state',
            country: 'country',
            postal_code: 'zip'
        };

        var data = {};

        // Get the place details from the autocomplete object.
        var place = autocomplete.getPlace();

        //$('#address').val() = '';


        var address = null;
        var city = null;
        var state = null;
        var country = null;
        var zip = null;

        // Get each component of the address from the place details
        // and fill the corresponding field on the form.
        if (place && place['address_components'] && place.address_components.length > 0) {
            for (var i = 0; i < place.address_components.length; i++) {
                var addressType = place.address_components[i].types[0];
                if (componentForm[addressType]) {
                    var val = place.address_components[i][componentForm[addressType]];
                    data[addressType] = val;
                }
            }


            if ( data['street_number'] || data['route']) {
                address =  data['street_number'] + ' ' + data['route'];
            }
             city = data['locality'];
             state = data['administrative_area_level_1'];
             country = data['country'];
             zip = data['postal_code'];
        }


        if (!address) {address='';}
        if (!city) {city='';}
        if (!state) {state='';}
        if (!country) {country='';}
        if (!zip) {zip='';}

        $('#address').val(address);
        $('#city').val(city);
        $('#state').val(state);
        $('#country').val(country);
        $('#zip').val(zip);

    }


    //make address field larger when has focus
$(function() {
    var addwrap = $('.address-wrapper');
    var add = $('#address');
    add.on('focus',function() {
        addwrap.addClass('large-address');
    });

    add.on('blur',function() {
        addwrap.removeClass('large-address');
       // $('#zip').focus();
    });

    add.on( 'keydown', function ( e ) {
        if ( e.keyCode === 27 ) { // ESC
            addwrap.removeClass('large-address');
            $('#zip').focus();
        }
    });

    add.click(  function (  ) {
        addwrap.addClass('large-address');
    });


    $(document).on("keypress", ":input[name='address']", function(event) {
        if (event.keyCode == 13) {
           // console.log('not!');
            event.preventDefault();
        }
    });
});