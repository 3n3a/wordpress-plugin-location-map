(function ($) {
  $(document).ready(async function () {
    let initialLat = 37.7749;
    let initialLng = -122.4194;

    // Get Location based on User IP, if no lat long saved already
    if ($("#lm_latitude").val().length === 0 && $("#lm_longitude").val().length === 0) {
      const data = await $.getJSON(
        "http://ip-api.com/json?fields=status,lat,lon"
      );
      if (data["status"] === "success") {
        initialLat = data["lat"] || initialLat;
        initialLng = data["lon"] || initialLng;
      }
    }

    // Use current inputs or default values
    initialLat = $("#lm_latitude").val() || initialLat;
    initialLng = $("#lm_longitude").val() || initialLng;

    // Get the current tile theme from the select field.
    var tileTheme = $('#lm_tile_theme').val() || 'osm_standard';

    // Helper to choose the tile URL based on the theme.
    function getTileUrl(theme) {
        switch(theme) {
            case 'osm_hot':
                return 'https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png';
            case 'lima_labs':
                return 'https://cdn.lima-labs.com/{z}/{x}/{y}.png?api=demo';
            case 'custom':
                return $("#lm_tile_url").val();
            case 'osm_standard':
            default:
                return 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        }
    }

    // Initialize the Leaflet map.
    var map = L.map("lm-map-picker").setView([initialLat, initialLng], 13);
    var tileLayer = L.tileLayer(getTileUrl(tileTheme), {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    var marker = L.marker([initialLat, initialLng]).addTo(map);

    // When the tile theme select changes, update the tile layer.
    $("#lm_tile_theme").on("change", function () {
      tileTheme = $('#lm_tile_url').val();

      // show custom if selected
      if (tileTheme === "custom") {
        $("#custom_tile_url").show()
      } else {
        $("#custom_tile_url").hide()
      }

      map.removeLayer(tileLayer);
      tileLayer = L.tileLayer(getTileUrl(tileTheme), {
        attribution: "© OpenStreetMap contributors",
      }).addTo(map);
    });

    $("#lm_tile_url").on("change", function () {
      const customTileUrl = $(this).val();

      map.removeLayer(tileLayer);
      tileLayer = L.tileLayer(customTileUrl, {
        attribution: "© OpenStreetMap contributors",
      }).addTo(map);
    });

    // Helper function to update marker position and input fields.
    function updateMarker(lat, lng) {
      marker.setLatLng([lat, lng]);
      $("#lm_latitude").val(lat);
      $("#lm_longitude").val(lng);
    }

    function getAddressFormatted(item) {
      let addressSearchInput = item.display_name;
      if (item["address"]) {
        const address = item["address"];

        const road = address["road"];
        const house_number = address["house_number"];
        const postcode = address["postcode"];
        const city = address["city"] || address["town"];
        if (road && house_number && postcode && city) {
          addressSearchInput = `${road} ${house_number}, ${postcode} ${city}`;
        }
      }
      return addressSearchInput;
    }

    // Update marker and inputs on map click.
    map.on("click", function (e) {
      var lat = e.latlng.lat;
      var lng = e.latlng.lng;
      updateMarker(lat, lng);
    });

    // Set up jQuery UI Autocomplete for the address search input.
    $("#lm-address-search").autocomplete({
      source: function (request, response) {
        $.getJSON(
          "https://nominatim.openstreetmap.org/search",
          {
            format: "json",
            q: request.term,
            addressdetails: 1,
            limit: 5,
          },
          function (data) {
            response(
              $.map(data, function (item) {
                var addressSearchInput = getAddressFormatted(item);
                return {
                  label: addressSearchInput, // Full address for the suggestion.
                  value: addressSearchInput, // Value to populate the input.
                  lat: item.lat,
                  lon: item.lon,
                };
              })
            );
          }
        );
      },
      select: function (event, ui) {
        // On selection, update the search input, map view, and marker.
        $("#lm-address-search").val(ui.item.value);
        map.setView([ui.item.lat, ui.item.lon], 13);
        updateMarker(ui.item.lat, ui.item.lon);
        return false;
      },
      minLength: 3,
    });

    // Fallback search button functionality if autocomplete isn't used.
    $("#lm-search-btn").on("click", function () {
      var address = $("#lm-address-search").val();
      if (address.length > 0) {
        $.getJSON(
          "https://nominatim.openstreetmap.org/search",
          {
            format: "json",
            q: address,
            addressdetails: 1,
            limit: 1,
          },
          function (data) {
            if (data && data.length > 0) {
              var result = data[0];
              let addressSearchInput = getAddressFormatted(result);
              $("#lm-address-search").val(addressSearchInput);
              map.setView([result.lat, result.lon], 13);
              updateMarker(result.lat, result.lon);
            } else {
              alert("No results found.");
            }
          }
        );
      }
    });

    // When longitude or latitude fields change manually, update the map marker.
    $("#lm_latitude, #lm_longitude").on("change", function () {
      var lat = $("#lm_latitude").val();
      var lng = $("#lm_longitude").val();
      if (lat && lng) {
        marker.setLatLng([lat, lng]);
        map.setView([lat, lng], 13);
      }
    });
  });
})(jQuery);
