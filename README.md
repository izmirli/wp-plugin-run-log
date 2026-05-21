# Run Log Plugin

Adds running diary capabilities - log your sporting activity with custom post type, custom fields and new taxonomies.

## Features

* Add custom post type for logging a running activity.
* Add custom taxonomies for gear (like shoes) and goals (like marathon) that could be connected to run-log posts (and regular posts).
* Log distance and duration for each run in custom fields of run-log posts.
* Calculate pace/speed automatically.
* Display the above data in the post automatically.
* Display total distance/time shortcode.
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
3. Upload/Move the new run-log directory under the wp-content/plugins/ directory of your WordPress installation.
4. Activate the plugin in the Plugin dashboard

## Automated Testing

This plugin features a comprehensive, modern testing suite composed of two main testing frameworks:
1. **PHPUnit Unit & Integration Tests (PHP)**: Tests calculations, filters, shortcode generation, and backend logic.
2. **Playwright End-to-End (E2E) Tests (Python)**: Uses a real browser via Playwright to test administrative workflows, saving settings, creating runs, hierarchical goals, and external embeds.

To keep the release-ready plugin completely clean for SVN, all testing tools, configurations, and scripts reside outside of the `run-log/` directory.

### Requirements

To run the automated tests locally, you need the following installed on your machine:
* **Docker Desktop**: Required to spin up the local WordPress development and testing environment via `wp-env`.
* **Node.js & NPM**: Required to manage node dependencies and orchestrate the environment.
* **Python (3.8+)**: Required to run the Playwright E2E browser tests.

### Setup and Installation

1. **Install Node.js dependencies**:
   ```bash
   npm install
   ```
2. **Set up the Python Virtual Environment**:
   * Create a virtual environment named `.venv`:
     ```bash
     python -m venv .venv
     ```
   * Activate the virtual environment:
     * **Windows (PowerShell)**: `.venv\Scripts\Activate.ps1`
     * **Windows (CMD)**: `.venv\Scripts\activate.bat`
     * **macOS/Linux**: `source .venv/bin/activate`
   * Install Python dependencies:
     ```bash
     pip install -r tests/e2e/requirements.txt
     ```
   * Install Playwright browser binaries (e.g. Chromium):
     ```bash
     playwright install chromium
     ```

### Running the Tests

We provide a fully automated script that handles the entire execution cycle, prepares the environment, and cleans up Docker resources afterwards:

#### 1. Run the Entire Integrated Test Pipeline (Recommended)
This command spins up the Docker environment, executes the PHPUnit test suite, automatically prepares the E2E database settings/theme, runs all E2E browser tests, and gracefully stops the Docker containers to free up system memory:
```bash
npm run test
```

#### 2. Run PHPUnit Tests Individually
If you have the `wp-env` containers running and want to execute just the PHPUnit tests inside the container:
```bash
npm run test:php
```

#### 3. Run E2E Playwright Tests Individually
If your `wp-env` containers are active and you want to execute just the Playwright browser tests:
```bash
.venv\Scripts\pytest tests/e2e --base-url http://localhost:8889
```
*(Use `.venv/bin/pytest` on macOS/Linux)*

## Contents

The project repository is structured as follows:

* **Root Directory** (Development & Testing Infrastructure):
  * `package.json` / `package-lock.json`: NPM package metadata and dependencies.
  * `.wp-env.json`: Configuration for the local Docker development/testing container mapping.
  * `composer.json` / `composer.lock`: PHP Composer dependencies for development (PHPUnit & Yoast Polyfills).
  * `phpunit.xml.dist`: PHPUnit configuration mapping test files and bootstrap.
  * `run-tests.js`: Fully integrated Node.js script to run the start-test-stop test cycle.
  * `README.md`: The file that you’re currently reading.
  * `TODO.md`: Roadmap and pending tasks.
  * `tests/`: Directory containing all automated tests.
    * `phpunit/`: Directory containing the PHPUnit unit and integration tests.
      * `bootstrap.php`: boots the WordPress testing library and loads the plugin inside Docker.
      * `CalculationsTest.php`: unit tests verifying pace and distance calculations.
      * `ShortcodesTest.php`: integration tests verifying `[oirl_total]` shortcodes rendering and parameters.
    * `e2e/`: Directory containing Playwright browser tests.
      * `conftest.py`: pytest conftest script managing server fixtures.
      * `test_admin_settings.py`: E2E tests for settings saving and loading.
      * `test_create_run.py`: E2E tests for post generation and front-end layout rendering.
      * `test_embeds.py`: E2E tests for Strava and Garmin Connect external embeds.
      * `test_run_taxonomies.py`: E2E tests for Goals and Gear hierarchical taxonomy rendering.

* **`run-log/`** (Clean, production-ready WordPress plugin trunk):
  * `run-log.php`: The core plugin boot file.
  * `index.php`: Index file, to avoid direct access.
  * `readme.txt`: The standard readme file for WordPress plugins directory listing.
  * `run-log-rtl.css` / `run-log.css`: Stylesheets for RTL and LTR WordPress installations.
  * `languages/`: Contains general translation template (.pot), Hebrew translations (.mo/.po), and other localization files.
  * `js/`: JavaScript files directory containing admin interaction scripts.

## License

The Run Log plugin is licensed under the GPL v2 or later.
