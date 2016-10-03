EuroveloBy Map WordPress Plugin
========

Based on https://github.com/bozdoz/wp-plugin-leaflet-map plugin.

How to Use
----------

Adds map for Eurovelo.by site. Based on the Leaflet Map plugin.

Some shortcode attributes:

Height, width, latitude, longitude and zoom are the basic attributes: 

`[eurovelo-map height=250 width=250 lat=44.67 lng=-63.61 zoom=5]`

The zoom buttons can be large and annoying.  Enabled or disable per map in shortcode: 

`[eurovelo-map zoomcontrol="0"]`

Installation
------------

* Copy this repo into your WordPress plugins directory: /wp-content/plugins/
* Install via the WordPress plugins page on your WordPress site: /wp-admin/plugin-install.php (search Eurovelo)
* Check 'Eurovelo Map' tab on the admin Dashboard for defaults
* Add `[eurovelo-map]` shortcode to a page

Shortcode Options
-----------------
* height=\<height\> — height of map
* width=\<width\> — width of map
* scrollwheel=\<0|1\> — disable/enable map zooming by mouse wheel
* lat=\<latitude\>, lon=\<longitude\> — map center coordinates
* zoomcontrol=\<0|1\> — hide/show zoom control buttons on map
* disabled_routes=\<filename.kml\> — disable this layer by default, for example: disabled_routes="eurovelo-routes.kml"

License
-------

GPLv2

