=== Run Log ===
Contributors: izem
Tags: training log, training diary, running, sport, run log, run diary, running log, run, strava, garmin connect, garmin, jogging, total distance, total time, total duration, share runs
Requires at least: 3.8
Tested up to: 5.8
Stable tag: 1.7.5
License: GPLv2 (or later)
License URI: https://wordpress.org/about/gpl/

Add running diary capabilities - log your sport activities, track and display: distance, duration, gear (e.g. shoes), elevation gain, calories, etc.

== Description ==

The plugin add running diary capabilities to WordPress, so you can log and display your running [and other sporting] activities in posts. Share runs, total mileage (or kilometers), total time spent running, etc. Track your shoes usage, and/or other sporting gear. Link and group your activities by goals.

= Features =

* Add custom post type for logging a running activity.
* Log distance and duration (elevation gain and calories) for each run in custom fields of run-log posts.
* Calculate pace/speed automatically.
* Display the above data in the post automatically.
* Widget and shortcode for displaying totals - distance, time, elevation gain, calories (and average pace/speed for shortcode).
* Option to choose light or dark style theme, to blend with your theme.
* Quick embed your STRAVA and/or Garmin Connect activity in the post (displaying data and map from your account).
* Add custom taxonomies for gear (like shoes) and goals (like "sub 4 marathon") that could be connected to run-log posts (and regular posts). You may trak distance run with shoes usage by this (as well as other gear).

= Localization =

Support RTL languages sites.

The metric system of measurement is used by default - Kilometer (km) for distance, minutes per kilometer (min/km) for pace, kilometers per hour (km/h) for speed [and meters (m) for elevation].
You can change these to statute/imperial by updating plugin's "Distance unit" option to mi (Mile).

= Translations =

* Hebrew - full translations.
* English - default. Not my mother tongue, so may have some wording and spelling mistakes. Do tell me how to correct them if you find any.

= Usage =

[After activating the plugin]

**To log a new run you can follow these steps:**

1. On the admin menu there will be a new sub-menu: "Run Log" - from it's options, select "Add New" (or click on "Run" from the "New" sub-menu of the top menu).
2. Enter a title for this run (as post title), write your run description (e.g. type of run, location, how you fealt, etc.) in the body. You can add media (photos, videos) if you want, as you would do with a normal post.
3. Under the main editing area (body) you should see the "Run Log Parameters" box. There you enter the distance and duration of the run.
4. [Optional] You may add the elevation gain and/or calories for that activety (at "Run Log Parameters" box).
4. [Optional] You may add the gear you used in this run on the "Gears" box (like shoes and track shoes mileage by this).
5. [Optional] You may add the goal, that this run is part of the road to it, on the "Goals" box (so you'll be able to see all of them in one page, as well as the total distance/time).
6. [Optional] You may add your "STRAVA" or "Garmin Connect" activity ID, to embed your activity's data and map from your account (instead of the regular plugin's display).
7. Publish.

If you want to configure run log data (distance, duration, pace/speed), you can do this on the "Run Log Options", accessible via the "Run Log" admin sub-menu. There you can select between top/bottom display position, Kilometer/Miles units, and pace/speed.

**To display your totals**

Use `[oirl_total]` Shortcode with (or without) these optional attributes:

* only: distance/time/elevation/calories;
* year: a 4-digit year - display totals for this year only;
* month: 1 or 2 digits for month (may have leading zero) - display totals for this year only (mast be used in conjunction with 'year' attribute);
* hide_pace: yes/no - if 'yes' will not show the average pace/speed;
*	days_display: true/false - display days in total time if more then 24 hours.

Examples:

* All-time distance + time + average pace/speed:
 * `[oirl_total]`
* 2015 totals without average pace/speed (display distance + time):
 * `[oirl_total year="2015" hide_pace="yes"]`
* January 2016 totals (distance + time + average pace/speed):
 * `[oirl_total year="2016" month="1"]`
* All-time distance:
 * `[oirl_total only="distance"]`
* All-time duration (displaying days if more then 24 hours):
 * `[oirl_total only="time" days_display="yes"]`
* Total elevation gain for 2016:
 * `[oirl_total only="elevation" year="2016"]`

= Credits =

The plugin icon was [Designed by Freepik](http://www.freepik.com).

= To Do: =

* Add option to display elevation and calories on run-log posts.
* Add hart rate(?).
* Add how you felt scale(?).
* More quick embed sources (polar, suunto, runkeeper, runtastic, etc).
* Add API support to retrieve data automatically form: Strava, Garmin Connect, Sunto Movescount, Runkeepr, etc.

= Uninstall =

This plugin doesn't add/change the data-base structurer, so no worry about that. Yet, it does store plugin configuration options in the 'options' table. These options will be removed if plugin is uninstall (deleted) trough the plugins admin screen.
Data stored by this plugin for posts (in postmeta table) will be kept.

== Installation ==

From your website via WordPress Plugin Directory (recomended):

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

= Something doesn't look right =

Check and update options on the "Run Log Options" page.

= "Garmin Connect" quick embed doesn't work =

Verify the activity ID is copied fully from activity's page address (the 10-digit number ate URL's end: connect.garmin.com/modern/activity/**1004567890**).
Make sure your activity is Public - activity's privacy is set to "Everyone" (the lock icon, at the top right page corner, is open).

== Screenshots ==

1. Run data display in the post.
2. The post edit screen - run-log parameters input box at the bottom.
3. Shortcode displaying 3 views: default, distance only, duration only.
4. The plugin options screen.
5. "Garmin Connect" embed.
6. Gears and Goals input boxes from the post edit screen.
7. STRAVA embed.
8. Widget configuration.

== Changelog ==

= 1.7.5 =
* Removed Endomondo embed (Endomondo shut down on 31 December 2020).

= 1.7.4 =
* Update some css files for more robust display.

= 1.7.3 =
* Update some URLs to HTTPS (Endomondo embed fix for https sites).

= 1.7.2 =
* Added elevation and calories to widget display options.

= 1.7.1 =
* Fix for widget, gear and goal toal-counter in dark them style.

= 1.7.0 =
* New Widget for displaying activities totals.

= 1.6.0 =
* Adding gear and goal summery data to their archive page (in thems that display term description).
* Adding links to gear and goal archives from run-log posts.

= 1.5.3 =
* Fixed pages not displaying due to iorl_run_log_update_get_posts bug

= 1.5.2 =
* Totals Shortcode supports year/month time periods.
* Totals Shortcode supports elevation.
* Added m to ft (Meters to Feet) and ft to m conversion.

= 1.5.1 =
* STRAVA quick embed and totals Shortcode enhancements.

= 1.5.0 =
* Shortcode for displaying totals (distance, time, pace/speed).
* Enable STRAVA activity quick embed.
* Option to choose light or dark style theme.
* Better form input sanitizing with filter_input.

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

= 1.5.2 =
Till now elevation gain was always in meters, now if mi is your "distance unit", elevation will be saved and displayed in feet.

= 1.5.1 =
After updating, it is recommended to check and update "Run Log Options" page.

= 1.3.2 =
Due to code refactoring of plugin options, you may have to re-update the plugin options (in case you have changed them in the past).
