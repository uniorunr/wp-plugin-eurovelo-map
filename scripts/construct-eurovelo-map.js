// holds a function queue to call once leaflet.js is loaded
// called in init-leaflet-map.js
var WPEuroveloMapPlugin = {
	maps : [],
	init : function () {
		// shortcodes incrementally add to this function
	},
	add : function (fnc) {
		// add to init
		var prev_init = this.init;

		this.init = function () {
			prev_init();
			fnc();
		};
	},

	addMap : function (div, opts) {
		this.add(function() {
			var map;

			map = L.map(div, 
					{
						zoomControl: opts.zoomcontrol,
						scrollWheelZoom: opts.scrollwheel,
						fullscreenControl: {
							pseudoFullscreen: false
						}

					}).setView([opts.lat, opts.lng], opts.zoom);

			var latlon = L.tileLayer('http://tile.latlon.org/tiles/{z}/{x}/{y}.png', {
					opacity: 1,
					maxZoom: 18,
					attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, ' +
					'Tiles: <a href="https://github.com/jekhor/belroad">Belroad</a> style'
					}).addTo(map);

			var osm = L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
					maxZoom: 19,
					attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors'
					});

			var genshtab = L.tileLayer.wms("http://ms.latlon.org/ms", {
				layers: 'GS-100k-N-34,GS-100k-N-35,GS-100k-N-36',
				format: 'image/png'
			});

			var rkka50k = L.tileLayer('http://orda.of.by/.tiles/rkka/Z{z}/{y}/{x}.jpg', {
					maxZoom: 14,
					attribution: '<a href="http://orda.of.by/">orda.of.by</a>'
					});

			var routes_overlays = {};
			if (opts.routes_base_url) {
				var kmlFiles = {
					'eurovelo-routes.kml': 'EuroVelo Routes',
					'vh.kml': 'Валожынскія гасцінцы'
				};

				for (var file in kmlFiles) {
					var route = WPEuroveloMapPlugin.routesLayer(opts.routes_base_url + '/' + file);
					if (opts.disabled_routes.indexOf(file) == -1)
						route.addTo(map);

					routes_overlays[kmlFiles[file]] = route;
				}
			}

			var globusGroup = L.markerClusterGroup({
				showCoverageOnHover: false,
				maxClusterRadius: 50,
				iconCreateFunction: function(cluster) {
					var childCount = cluster.getChildCount();

					var c = ' marker-cluster-';
					if (childCount < 10) {
						c += 'small';
					} else if (childCount < 100) {
						c += 'medium';
					} else {
						c += 'large';
					}

					return new L.DivIcon({ html: '<div><span>' + childCount + '</span></div>', className: 'marker-cluster' + c, iconSize: new L.Point(40, 40) });
				}
			});

			WPEuroveloMapPlugin.loadGlobus(globusGroup);

			var panoramio = new L.Panoramio({
				maxLoad: 250,
				maxTotal: 750,
				maxClusterRadius: 50,
				showCoverageOnHover: false,
			});

			var baseLayers = {
				"LatLon": latlon,
				"OSM Mapnik": osm,
				"Генштаб, 1км": genshtab,
				"РККА, 1:50000": rkka50k
			};

			var overlays = {
				"Globus.tut.by": globusGroup,
				"Panoramio Photos": panoramio
			};

			L.extend(overlays, routes_overlays);

			var layersCtl = L.control.layers(baseLayers, overlays).addTo(map);
			var globusEnabled = true;
			var panoramioEnabled = true;

			map.on('overlayadd', function(obj) {
				if (obj.layer === globusGroup)
					globusEnabled = true;
				if (obj.layer === panoramio)
					panoramioEnabled = true;
			});

			map.on('overlayremove', function(obj) {
				if (obj.layer === globusGroup)
					globusEnabled = false;
				if (obj.layer === panoramio)
					panoramioEnabled = false;
			});


			map.on('zoomend', function() {
				if (map.getZoom() >= 11) {
					if (!map.hasLayer(panoramio) && panoramioEnabled)
						panoramio.addTo(map);

					if (!map.hasLayer(globusGroup) && globusEnabled)
						globusGroup.addTo(map);
				} else {
					if (map.hasLayer(panoramio))
						map.removeLayer(panoramio);

					if (map.hasLayer(globusGroup))
						map.removeLayer(globusGroup);
				}
			});


			WPEuroveloMapPlugin.maps.push(map);
		});
	},

	loadGlobus: function (group) {
		var items = GetGeoItems();

		var icon = L.divIcon({
			className: 'fa fa-globe fa-lg icon-globus'
		});

		for (i in items) {
			var poi = items[i];
			var marker = L.marker([poi.lt, poi.ln], {icon: icon});
			var preview_url;
			var img = '';

			if (poi.mi != '') {
				if (poi.w == 'GA')
					preview_url = 'http://orda.of.by/.ga/' + poi.ps[0] + '/' + poi.ps + '/' + poi.os + '/nf/.preview/' + poi.mi;
				else
					preview_url = 'http://globus.tut.by/_wh150/' + poi.ps + '/' + poi.mi;

				img = '<br/><img width="150px" src="' + preview_url + '"></img>';
			}

			marker.bindPopup('<a href="http://globus.tut.by/' + poi.ps + '"><h3>' + poi.p + '</h3></a>' +
					'<a href="http://globus.tut.by/' + poi.ps + '/' + poi.os + '">' + poi.o + '</a><br/>' +
					poi.d +
					img);
			group.addLayer(marker);
		}
	},

	routesLayer: function(file) {
		function ev2EachFeature(data, layer) {
			desc = '';
			if (data.properties.description)
				var desc = '<br/>' + data.properties.description;

			layer.bindPopup(data.properties.name + desc);
		};

		var routes = L.geoJson(null, {
			onEachFeature: ev2EachFeature,
			style: function(feature) {
				var color = '#aa0000';
				var width = 3;

				if (feature.properties.stroke !== undefined)
					color = feature.properties.stroke;

				if (feature.properties['stroke-width'] !== undefined)
					width = feature.properties['stroke-width'];

				return {color: color, weight: width, opacity: 0.8};
			}
		});

		var kml = omnivore.kml(file, null, routes);

		return routes;
	}

};
