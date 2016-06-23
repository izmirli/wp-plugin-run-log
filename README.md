# Run Log Plugin

Adds running diary capabilities - log your sporting activity with custom post type, custom fields and new taxonomies.

## Contents

The WordPress Plugin Boilerplate includes the following files:

* `.gitignore`. Used to exclude certain files from the repository.
* `CHANGELOG.md`. The list of changes to the core project.
* `README.md`. The file that you’re currently reading.
* `run-log`. Directory that contains the source code for the fully executable WordPress plugin.
 * `run-log.php`. The codr plugin file.
 * `index.php`. Index file, to avoid direct access.
 * `readme.txt`. The readme file for WordPress plugins directory.
 * `run-log-rtl.css`. Styles file for RTL WordPress installations.
 * `run-log.css`. Styles file (for LTR WordPress installations).
 * `languages`. Directory that contains the .mo language files, along side with .pot and .po files.
    * `run-log-he_IL.mo`. Hebrew translation file.
    * `run-log-he_IL.po`. Hebrew translation PO file.
    * `run-log.pot`. General translation template.

## Features

* Add custom post type for logging a running activity.
* Add custom taxonomies for gear (like shoes) and goals (like marathon) that could be connected to run-log posts (and regular posts).
* Log distance and duration for each run in custom fields of run-log posts.
* Calculate pace/speed automatically.
* Display the above data in the post automatically.
* Enable "Garmin Connect" quick embed (display full data and activity map from your "Garmin Connect" account).
* Enable "endomondo" quick embed (display full data and activity map from your "endomondo" account).

## Installation

#### From the WordPress Plugin Directory:

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'Run Log'
3. Click 'Install Now' button
4. Activate the plugin in the Plugin dashboard

#### From your computer via FTP or from your sever:

1. Download/Clone run-log repo to your computer (or to server).
2. Extract the trunk directory from the repo and rename it to run-log.
3. Upload/Move the new run-log directory under the wp-content/plugins/ directory of your WrdPress installation.
4. Activate the plugin in the Plugin dashboard

## License

The Run Log plugin is licensed under the GPL v2 or later.
