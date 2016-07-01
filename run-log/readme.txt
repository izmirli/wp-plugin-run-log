=== Run Log ===
Contributors: izem
Tags: run, running, sport, training log, training diary, run log, run diary, running log, garmin Connect, garmin. endomondo, jogging
Requires at least: 3.8
Tested up to: 4.5.3
Stable tag: trunk
License: GPLv2 (or later)
License URI: https://wordpress.org/about/gpl/

Adds running diary capabilities - log your sporting activity with custom post type, custom fields and new taxonomies.

== Description ==

The plugin add running diary capabilities to WordPress, so you can log your running activities in posts with special features.

= Special Features =

* Add custom post type for logging a running activity.
* Add custom taxonomies for gear (like shoes) and goals (like marathon) that could be connected to run-log posts (and regular posts).
* Log distance and duration for each run in custom fields of run-log posts.
* Calculate pace/speed automatically.
* Display the above data in the post automatically.
* Enable "Garmin Connect" quick embed (display full data and activity map from your "Garmin Connect" account).
* Enable "endomondo" quick embed (display full data and activity map from your "endomondo" account).

= Localization =

Support RTL languages sites.

The metric system of measurement is used by default - Kilometer (km) for distance, minutes per kilometer (min/km) for pace, kilometers per hour (km/h) for speed [and meters (m) for elevation].
You can change these to statute/imperial by updating plugin's "Distance unit" option to mi (Mile).

= Translations =

* Hebrew - full translations.
* English is there by default, yet not my mother tongue, so may have some wording and spelling mistakes. Do tell me how to correct them if you fine any.

= Usage =

[After activating the plugin]

To log a new run you can follow these steps:

1. On the admin menu there will be a new sub-menu: "Run Log" - from it's options, select "Add New" (or click on "Run" from the "New" sub-menu of the top menu).
2. Enter a title for this run (as post title), write your run description (e.g. type of run, location, how you fealt, etc.) in the body. You can add media (photos, videos) if you want, as you would do with a normal post.
3. Under the main editing area (body) you should see the "Run Log Parameters" box. There you enter the distance and duration of the run (as well as elevation gain and calories - to be used in future version of the plugin).
4. [Optional] You may add the gear you used in this run on the "Gears" box (like shoes - in future we may track shoes mileage by this).
5. [Optional] You may add the goal, that this run is part of the road to it, on the "Gears" box (like shoes - in future we may track shoes mileage by this).
6. [Optional] You may add your "Garmin Connect" or "endomondo" activity ID, to embed your activity's data and map from your Garmin/endomondo account (instead of the regular plugin's display).
7. Publish.

If you want to configure run log data (distance, duration, pace/speed), you can do this on the "Run Log Options", accessible via the "Run Log" admin sub-menu. There you can select between top/bottom display position, Kilometer/Miles units, and pace/speed.

= Credits =

The plugin icon was [Designed by Freepik](http://www.freepik.com).

= To Do: =

* Add elevation and calories display.
* Option to add data box to excerpt.
* Add Widget and short-code to display accumulate data and more.
* Add hart rate.
* Add API support for Strava/Garmin Connect/Sunto/Runkeepr to retrieve data automatically.

= Uninstall =

This plugin doesn't add/change the data-base structurer, so no worry about that. Yet, it does store plugin configuration options in the 'options' table. These options will be removed if plugin is uninstall (deleted) trough the plugins admin screen.
Data stored by this plugin for posts (in postmeta table) will be kept.

== Installation ==

From the WordPress Plugin Directory:

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'Run Log'
3. Click 'Install Now' button
4. Activate the plugin in the Plugin dashboard

From your computer via WordPress Dashboard:

1. Download run-log.zip
2. Navigate to the 'Add New' in the plugins dashboard
3. Click on the 'Upload Plugin' button (top)
4. Select run-log.zip from your computer
5. Click 'Install Now' button
6. Click on "Activate Plugin" link

From your computer via FTP or from your sever:

1. Download run-log.zip to your computer (or to server)
2. Extract the run-log directory from zip file
3. Upload/Move the run-log directory under the wp-content/plugins/ directory of your WrdPress installation
4. Activate the plugin in the Plugin dashboard

== Frequently Asked Questions ==

= Can this plugin be used for cycling or other similar sport activities? =

Yes, it should be usable for cycling as well, and maybe other similar sports.

= "Garmin Connect" quick embed doesn't work =

Verify the activity ID is copied fully from activity's page address (the 10-digit number ate URL's end: connect.garmin.com/modern/activity/**1004567890**).
Make sure your activity is Public - activity's privacy is set to "Everyone" (the lock icon, at the top right page corner, is open).

== Screenshots ==

1. Run data display in the post.
2. The post edit screen - run-log parameters input box at the bottom.
3. Gears and Goals input boxes from the post edit screen.
4. The plugin options screen.
5. "Garmin Connect" embed.

== Changelog ==

= 1.5.0 =
* Shortcode for displaying totals (distance, time, ).
* Option to choose light or dark style theme.

= 1.4.1 =
* Quick embed admin-side enhancements.

= 1.4.0 =
* Enable endomondo activity quick embed.

= 1.3.2 =
* Code refactoring of plugin options as one hash record (instead of many records).
* Update code to follow WordPress coding and inline documentation standards.

= 1.3.1 =
* Support for deleting Garmin activity ID, and new screenshot.

= 1.3.0 =
* Enable "Garmin Connect" quick embed.

= 1.2.0 =
* Option not to display the run data on the post was added.
* Option to display the run data on the excerpt was added.
* Added plugin icon.

= 1.0.1 =
* Move translation files under languages directory

= 1.0.0 =
* Initial version

== Upgrade Notice ==

= 1.3.2 =
Due to code refactoring of plugin options, you may have to re-update the plugin options (in case you have changed them in the past).
