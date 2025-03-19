(function($){
    $(document).ready(function(){
        // Initialize Leaflet map in the meta box.
        var map = L.map('lm-map-picker').setView([37.7749, -122.4194], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        var marker = L.marker([37.7749, -122.4194]).addTo(map);
        
        map.on('click', function(e) {
            var lat = e.latlng.lat;
            var lng = e.latlng.lng;
            marker.setLatLng(e.latlng);
            $('#lm_latitude').val(lat);
            $('#lm_longitude').val(lng);
            console.log('LocationMap (Debug) - Coordinates selected: ' + lng + ', ' + lat);
        });
    });
})(jQuery);
