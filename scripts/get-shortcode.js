(function () {
	var previous_onload = window.onload;

	window.onload = function () {

	    if ( previous_onload ) {
	        previous_onload();
	    }

		var map_input = document.getElementById('map-shortcode'),
			map_1 = WPEuroveloMapPlugin.maps[0];

		function update_map () {
			var latlng = map_1.getCenter();
			map_input.value = '[leaflet-map lat=' +
				latlng.lat +
				' lng=' +
				latlng.lng +
				' zoom=' +
				map_1.getZoom() +
				']';
		}

		map_1.on('move', update_map);

		update_map();

		map_input.addEventListener('click', function () {
			this.select();
		});

		marker_input.addEventListener('click', function () {
			this.select();
		});
	};
})();
