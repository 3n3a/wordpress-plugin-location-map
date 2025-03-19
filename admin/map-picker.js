(function($){
    let initialLat = 37.7749;
    let initialLng = -122.4194;
    $(document).ready(async function(){
        // Get Location based on User IP
        const data = await $.getJSON('http://ip-api.com/json?fields=status,lat,lon');
        if (data['status'] === "success") {
            initialLat = data['lat'] || initialLat;
            initialLng = data['lon'] || initialLng;
        }

        // Use current inputs or default values
        var initialLat = $('#lm_latitude').val() || initialLat;
        var initialLng = $('#lm_longitude').val() || initialLng;
        
        // Initialize the Leaflet map
        var map = L.map('lm-map-picker').setView([initialLat, initialLng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        var marker = L.marker([initialLat, initialLng]).addTo(map);
        
        // Helper function to update marker position and input fields
        function updateMarker(lat, lng) {
            marker.setLatLng([lat, lng]);
            $('#lm_latitude').val(lat);
            $('#lm_longitude').val(lng);
        }
        
        // Update marker and inputs on map click
        map.on('click', function(e) {
            var lat = e.latlng.lat;
            var lng = e.latlng.lng;
            updateMarker(lat, lng);
            console.log('Location Map Debug - Coordinates selected: ' + lng + ', ' + lat);
        });
        
        // Address search functionality using Nominatim API
        $('#lm-search-btn').on('click', function(){
            var address = $('#lm-address-search').val();
            if(address.length > 0){
                $.getJSON('https://nominatim.openstreetmap.org/search', {
                    format: 'json',
                    q: address
                }, function(data){
                    if(data && data.length > 0){
                        var result = data[0]; // use the first result
                        var lat = result.lat;
                        var lon = result.lon;
                        // Update map view and marker with the search result
                        map.setView([lat, lon], 13);
                        updateMarker(lat, lon);
                    } else {
                        alert('No results found.');
                    }
                });
            }
        });
        
        // When longitude or latitude fields change manually, update the map marker
        $('#lm_latitude, #lm_longitude').on('change', function(){
            var lat = $('#lm_latitude').val();
            var lng = $('#lm_longitude').val();
            if(lat && lng){
                marker.setLatLng([lat, lng]);
                map.setView([lat, lng], 13);
            }
        });
    });
})(jQuery);
