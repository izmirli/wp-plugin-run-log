<?php
/**
 * Run Log Plugin main file.
 *
 * @link              http://run-log.gameiz.net/
 * @since             1.0.0
 * @package           Run_Log
 *
 * @wordpress-plugin
 * Plugin Name: Run Log
 * Plugin URI: http://run-log.gameiz.net/
 * Description: Adds running diary capabilities - log your sport activities with custom post type, custom fields and new taxonomies.
 * Version: 1.7.2
 * Author: Oren Izmirli
 * Author URI: https://profiles.wordpress.org/izem
 * Text Domain: run-log
 * License: GPL2
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Set defaul options on plugin activation
 *
 * @since 1.0.0
 */
function oirl_set_default_options() {
	$oirl_plugin_options = array(
		'distance_unit' => 'km',
		'pace_or_speed' => 'pace',
		'display_pos' => 'top',
		'display_on_excerpt' => 0,
		'style_theme' => 'light',
		'gear_links' => true,
		'goal_links' => true,
	);
	add_option( 'oi-run-log-options', $oirl_plugin_options );
}
register_activation_hook( __FILE__, 'oirl_set_default_options' );

/**
 * Remove plugin options on uninstall
 *
 * @since 1.0.0
 */
function oirl_remove_default_options() {
	delete_option( 'oi-run-log-options' );
}
register_uninstall_hook( __FILE__, 'oirl_remove_default_options' );

/**
 * On plugin upgrade, check if updates are needed to existing data.
 *
 * @since 1.3.2
 *
 * @param Plugin_Upgrader $upgrader_object Plugin_Upgrader instance.
 * @param array           $options Array of bulk item update data.
 */
function oirl_plugin_upgrate( $upgrader_object, $options ) {
	if ( 'plugin' === $options['type'] && in_array( 'oi_run_log_post', $options['packages'], true ) ) {
		return;
	}
	// don't do anything anymore.
}
add_action( 'upgrader_process_complete', 'oirl_plugin_upgrate', 10, 2 );

/**
 * Load plugin textdomain on plugins_loaded action.
 *
 * @since 1.0.0
 */
function oirl_init() {
	load_plugin_textdomain( 'run-log', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'oirl_init' );

/**
 * Register Custom run_log Post Type
 *
 * @since 1.0.0
 */
function oirl_register_run_log_post_type() {

	$labels = array(
		'name'                => _x( 'Runs', 'Post Type General Name', 'run-log' ),
		'singular_name'       => _x( 'Run', 'Post Type Singular Name', 'run-log' ),
		'menu_name'           => __( 'Run Log', 'run-log' ),
		'parent_item_colon'   => __( 'Parent Run:', 'run-log' ),
		'all_items'           => __( 'All Runs', 'run-log' ),
		'view_item'           => __( 'View Run', 'run-log' ),
		'add_new_item'        => __( 'Add New Run', 'run-log' ),
		'add_new'             => __( 'Add New', 'run-log' ),
		'edit_item'           => __( 'Edit Run', 'run-log' ),
		'update_item'         => __( 'Update Run', 'run-log' ),
		'search_items'        => __( 'Search Run', 'run-log' ),
		'not_found'           => __( 'Not run found', 'run-log' ),
		'not_found_in_trash'  => __( 'Not run found in Trash', 'run-log' ),
	);
	$args = array(
		'label'               => __( 'oi_run_log_post', 'run-log' ),
		'description'         => __( 'Log entry for a specific run', 'run-log' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields' ),
		'taxonomies'          => array( 'category', 'post_tag' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 5,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'post',
	);
	register_post_type( 'oi_run_log_post', $args );

}
add_action( 'init', 'oirl_register_run_log_post_type', 0 );

/**
 * Add plugin options page to Run Log menu
 *
 * @since 1.0.0
 */
function oirl_plugin_menu() {
	global $oirl_manage_options_hook;
	$oirl_manage_options_hook = add_submenu_page( 'edit.php?post_type=oi_run_log_post', __( 'Run Log Options', 'run-log' ), __( 'Run Log Options', 'run-log' ), 'manage_options', 'oirl-options-menu', 'oirl_plugin_options' );
}
// Register options page to menu using the admin_menu action hook.
add_action( 'admin_menu', 'oirl_plugin_menu' );

/**
 * Plugin options page
 *
 * @since 1.0.0
 */
function oirl_plugin_options() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
	}

	// Get current options from db.
	$cur_ops = get_option( 'oi-run-log-options' );
	// flag to indicate if options were updated.
	$updated_options = false;

	// check and update (if needed) distance unit.
	$distance_unit = isset( $cur_ops['distance_unit'] ) ? $cur_ops['distance_unit'] : '';
	$given_distance_unit = filter_input( INPUT_POST, 'oirl-distance-unit', FILTER_VALIDATE_REGEXP, array( 'options' => array( 'regexp' => '/^(km|mi)$/' ) ) );
	if ( $given_distance_unit && $given_distance_unit !== $distance_unit ) {
		$distance_unit = $cur_ops['distance_unit'] = $given_distance_unit;
		$updated_options = true;
	}

	// check and update (if needed) pace/speed.
	$pace_or_speed = isset( $cur_ops['pace_or_speed'] ) ? $cur_ops['pace_or_speed'] : '';
	$given_pace_or_speed = filter_input( INPUT_POST, 'oirl-pace-or-speed', FILTER_VALIDATE_REGEXP, array( 'options' => array( 'regexp' => '/^(pace|speed)$/' ) ) );
	if ( $given_pace_or_speed && $given_pace_or_speed !== $pace_or_speed ) {
		$pace_or_speed = $cur_ops['pace_or_speed'] = $given_pace_or_speed;
		$updated_options = true;
	}

	// check and update (if needed) data display position.
	$display_position = isset( $cur_ops['display_pos'] ) ? $cur_ops['display_pos'] : '';
	$given_display_position = filter_input( INPUT_POST, 'oirl-display-pos', FILTER_VALIDATE_REGEXP, array( 'options' => array( 'regexp' => '/^(top|bottom|none)$/' ) ) );
	if ( $given_display_position && $given_display_position !== $display_position ) {
		$display_position = $cur_ops['display_pos'] = $given_display_position;
		$updated_options = true;
	}

	// check and update (if needed) style theme type.
	$style_theme = isset( $cur_ops['style_theme'] ) ? $cur_ops['style_theme'] : '';
	$given_style_theme = filter_input( INPUT_POST, 'oirl-style-theme', FILTER_VALIDATE_REGEXP, array( 'options' => array( 'regexp' => '/^(light|dark)$/' ) ) );
	if ( $given_style_theme && $given_style_theme !== $style_theme ) {
		$style_theme = $cur_ops['style_theme'] = $given_style_theme;
		$updated_options = true;
	}

	// check and update (if needed) display on excerpt.
	$display_on_excerpt = isset( $cur_ops['display_on_excerpt'] ) ? $cur_ops['display_on_excerpt'] : '';
	$given_display_on_excerpt = filter_input( INPUT_POST, 'oirl-display-on-excerpt', FILTER_VALIDATE_REGEXP, array( 'options' => array( 'regexp' => '/^[01]$/' ) ) );
	if ( isset( $given_display_on_excerpt ) && false !== $given_display_on_excerpt && $given_display_on_excerpt !== $display_on_excerpt ) {
		$display_on_excerpt = $cur_ops['display_on_excerpt'] = $given_display_on_excerpt;
		$updated_options = true;
	}

	// check and update (if needed) display goal links.
	$goal_links = isset( $cur_ops['goal_links'] ) ? $cur_ops['goal_links'] : '';
	$given_goal_links = filter_input( INPUT_POST, 'oirl-goal-links', FILTER_VALIDATE_REGEXP, array( 'options' => array( 'regexp' => '/^[01]$/' ) ) );
	if ( isset( $given_goal_links ) && false !== $given_goal_links && $given_goal_links !== $goal_links ) {
		$goal_links = $cur_ops['goal_links'] = $given_goal_links;
		$updated_options = true;
	}

	// check and update (if needed) display gear links.
	$gear_links = isset( $cur_ops['gear_links'] ) ? $cur_ops['gear_links']: '';
	$given_gear_links = filter_input( INPUT_POST, 'oirl-gear-links', FILTER_VALIDATE_REGEXP, array( 'options' => array( 'regexp' => '/^[01]$/' ) ) );
	if ( isset( $given_gear_links ) && false !== $given_gear_links && $given_gear_links !== $gear_links ) {
		$gear_links = $cur_ops['gear_links'] = $given_gear_links;
		$updated_options = true;
	}

	if ( $updated_options ) {
		update_option( 'oi-run-log-options', $cur_ops );
		?>
<div class="updated"><p><strong><?php echo esc_html__( 'Options saved', 'run-log' ) ?></strong></p></div>
		<?php
	}

	?>
<div class="wrap oirl">
	<h3><?php echo esc_html__( 'Run Log Options', 'run-log' )?></h3>
	<p><?php echo esc_html__( 'Control the Run Log settings by updating these values', 'run-log' )?>:<p>
	<form name="form1" method="post">
		<div title="<?php echo esc_attr__( 'Select km (Kilometer) to use the metric system for distance, as well as for pace (minutes per kilometer), speed (kilometers per hour) and elevation. Select mi (Mile) to use the imperial/USC system.', 'run-log' ) ?>">
			<?php echo esc_html__( 'Distance unit', 'run-log' )?>:
			<input type="radio" name="oirl-distance-unit" value="km" id="oirl-distance-unit-km" <?php echo ( 'km' === $distance_unit ? 'checked' : '')?>>
			<label for="oirl-distance-unit-km"><?php echo esc_html__( 'km', 'run-log' )?></label>
			&nbsp;
			<input type="radio" name="oirl-distance-unit" value="mi" id="oirl-distance-unit-mi" <?php echo ('mi' === $distance_unit ? 'checked' : '')?>>
			<label for="oirl-distance-unit-mi"><?php echo esc_html__( 'mi', 'run-log' )?></label>
		</div>
		<br>

		<div title="<?php echo esc_attr__( 'What is your preference? minutes per kilometer/mile? or kilometers/miles per hour?', 'run-log' ) ?>">
			<?php echo esc_html__( 'Pace/Speed display', 'run-log' )?>:
			<input type="radio" name="oirl-pace-or-speed" value="pace" id="oirl-pace-or-speed-Pace" <?php echo ( 'pace' === $pace_or_speed ? 'checked' : '')?>>
			<label for="oirl-pace-or-speed-Pace"><?php echo esc_html__( 'Pace', 'run-log' )?></label>
			&nbsp;
			<input type="radio" name="oirl-pace-or-speed" value="speed" id="oirl-pace-or-speed-Speed"<?php echo ('speed' === $pace_or_speed ? 'checked' : '')?>>
			<label for="oirl-pace-or-speed-Speed"><?php echo esc_html__( 'Speed', 'run-log' )?></label>
		</div>
		<br>

		<div title="<?php echo esc_attr__( 'Should light or dark colors be used (example: white/black background for activity\'s info box)?', 'run-log' ) ?>">
			<?php echo esc_html__( 'Style theme', 'run-log' )?>:
			<input type="radio" name="oirl-style-theme" value="light" id="oirl-style-theme-light" <?php echo ( 'light' === $style_theme ? 'checked' : '')?>>
			<label for="oirl-style-theme-light"><?php echo esc_html__( 'Light', 'run-log' )?></label>
			&nbsp;
			<input type="radio" name="oirl-style-theme" value="dark" id="oirl-style-theme-dark"<?php echo ('dark' === $style_theme ? 'checked' : '')?>>
			<label for="oirl-style-theme-dark"><?php echo esc_html__( 'Dark', 'run-log' )?></label>
		</div>
		<br>

		<div title="<?php echo esc_attr__( 'Should activity\'s info box be displayed at post top? bottom? or not at all (just trak distance, time, etc.)?', 'run-log' ) ?>">
			<?php echo esc_html__( 'Display position', 'run-log' )?>:
			<input type="radio" name="oirl-display-pos" value="top" id="oirl-display-pos-top" <?php echo ( 'top' === $display_position ? 'checked' : '')?>>
			<label for="oirl-display-pos-top"><?php echo esc_html__( 'top', 'run-log' )?></label>
			&nbsp;
			<input type="radio" name="oirl-display-pos" value="bottom" id="oirl-display-pos-bottom"<?php echo ('bottom' === $display_position ? 'checked' : '')?>>
			<label for="oirl-display-pos-bottom"><?php echo esc_html__( 'bottom', 'run-log' )?></label>
			&nbsp;
			<input type="radio" name="oirl-display-pos" value="none" id="oirl-display-pos-none"<?php echo ('none' === $display_position ? 'checked' : '')?>>
			<label for="oirl-display-pos-none"><?php echo esc_html__( 'without', 'run-log' )?></label>
		</div>
		<br>

		<div title="<?php echo esc_attr__( 'Should the run data box be added to the excerpt?', 'run-log' ) ?>">
			<?php echo esc_html__( 'Display on excerpt', 'run-log' )?>:
			<input type="radio" name="oirl-display-on-excerpt" value="0" id="oirl-display-on-excerpt-no" <?php echo ('0' === $display_on_excerpt ? 'checked' : '')?>>
			<label for="oirl-display-on-excerpt-no"><?php echo esc_html__( 'No' )?></label>
			&nbsp;
			<input type="radio" name="oirl-display-on-excerpt" value="1" id="oirl-display-on-excerpt-yes"<?php echo ('1' === $display_on_excerpt ? 'checked' : '')?>>
			<label for="oirl-display-on-excerpt-yes"><?php echo esc_html__( 'Yes' )?></label>
		</div>
		<br>

		<div title="<?php echo esc_attr__( 'Should links to goals activety is part of be at the bottom of its run-log posts?', 'run-log' ) ?>">
			<?php echo esc_html__( 'Display goals links', 'run-log' )?>:
			<input type="radio" name="oirl-goal-links" value="0" id="oirl-goal-links-no" <?php echo ( '0' === $goal_links ? 'checked' : '' )?>>
			<label for="oirl-goal-links-no"><?php echo esc_html__( 'No' )?></label>
			&nbsp;
			<input type="radio" name="oirl-goal-links" value="1" id="oirl-goal-links-yes"<?php echo ( '1' === $goal_links ? 'checked' : '' )?>>
			<label for="oirl-goal-links-yes"><?php echo esc_html__( 'Yes' )?></label>
		</div>
		<br>

		<div title="<?php echo esc_attr__( 'Should links to gear used in activety be at the bottom of its run-log posts?', 'run-log' ) ?>">
			<?php echo esc_html__( 'Display gear links', 'run-log' )?>:
			<input type="radio" name="oirl-gear-links" value="0" id="oirl-gear-links-no" <?php echo ( '0' === $gear_links ? 'checked' : '' )?>>
			<label for="oirl-goal-links-no"><?php echo esc_html__( 'No' )?></label>
			&nbsp;
			<input type="radio" name="oirl-gear-links" value="1" id="oirl-gear-links-yes"<?php echo ( '1' === $gear_links ? 'checked' : '' )?>>
			<label for="oirl-gear-links-yes"><?php echo esc_html__( 'Yes' )?></label>
		</div>

		<p class="submit">
		<input type="submit" name="Submit" class="button-primary" value="<?php echo esc_attr__( 'Save Changes' )?>">
		</p>
	</form>
</div>
	<?php
}

/**
 * Register meta boxes for run-log posts
 *
 * @since 1.0.0
 */
function oirl_register_run_log_meta_boxes() {
	add_meta_box( 'oi_run_log_meta_boxe', __( 'Run Log Parameters', 'run-log' ), 'oirl_run_log_meta_boxes_display', 'oi_run_log_post', 'normal', 'high' );
	// remove the default meta boxe for custom fields.
	remove_meta_box( 'postcustom', 'oi_run_log_post', 'normal' );
}
add_action( 'add_meta_boxes', 'oirl_register_run_log_meta_boxes' );

/**
 * Run Log meta box display callback.
 * Outputing HTML string of meta_boxes display for custom fields.
 *
 * @since 1.0.0
 *
 * @param WP_Post $post the post object these custom fields should be added to.
 */
function oirl_run_log_meta_boxes_display( $post ) {
	// Add an nonce field so we can check for it later.
	wp_nonce_field( basename( __FILE__ ), 'run-log-meta-box-nonce' );

	// Get plugin distance_unit option from db.
	$plugin_ops = get_option( 'oi-run-log-options' );
	$distance_unit = isset( $plugin_ops['distance_unit'] ) ? $plugin_ops['distance_unit'] : 'km';
	$distance = get_post_meta( $post->ID, 'oirl-mb-distance', true );
	if ( preg_match( '/^(\d+(?:\.\d{1,3})?)\d*$/', $distance, $distance_matches ) ) {
		$distance = $distance_matches[1];
	} else { // no valid distance - display zero.
		$distance = 0;
	}
	$elevation = get_post_meta( $post->ID, 'oirl-mb-elevation', true );
	if ( 'mi' === $distance_unit ) {
		$distance = iorl_distance_converter( $distance, 'K2M' );
		if ( $elevation ) {
			$elevation = round( iorl_distance_converter( $elevation, 'M2F' ) );
		}
	}
	$embed_external = get_post_meta( $post->ID, 'oirl-mb-embed-external', true );
	?>
	<div id="run-log-meta-box" class="oirl">
		<label for="oirl-mb-distance"><?php echo esc_html__( 'Distance', 'run-log' )?> (<?php
		if ( 'mi' === $distance_unit ) {
			esc_html_e( 'mi', 'run-log' );
		} else {
			esc_html_e( 'km', 'run-log' );
		}
		?>):</label>
		<input name="oirl-mb-distance" type="number" step="0.001" min="0" size="3" maxlength="6" value="<?php echo esc_attr( $distance ); ?>">
		&nbsp;
		<label for="oirl-mb-duration"><?php echo esc_html__( 'Duration', 'run-log' )?>:</label>
		<input name="oirl-mb-duration" type="text" size="8" maxlength="8" pattern="([0-9]{1,2}:)?[0-5]?[0-9]:[0-5]?[0-9]" placeholder="00:00:00" value="<?php echo esc_attr( get_post_meta( $post->ID, 'oirl-mb-duration', true ) ); ?>">
		<br>
		<label for="oirl-mb-elevation"><?php echo esc_html__( 'Elevation gain', 'run-log' )?> (<?php
		if ( 'mi' === $distance_unit ) {
			esc_html_e( 'ft', 'run-log' );
		} else {
			esc_html_e( 'm', 'run-log' );
		}
		?>):</label>
		<input name="oirl-mb-elevation" type="number" size="5" maxlength="6" value="<?php echo esc_attr( $elevation ); ?>">
		&nbsp;
		<label for="oirl-mb-calories"><?php echo esc_html__( 'Calories', 'run-log' )?>:</label>
		<input name="oirl-mb-calories" type="number" size="4" maxlength="5" value="<?php echo esc_attr( get_post_meta( $post->ID, 'oirl-mb-calories', true ) ); ?>">
		<br>
		<div id="oirl-embed-external">
			<?php echo esc_html__( 'Embed activity from external source', 'run-log' )?>:
			<input type="radio" name="oirl-mb-embed-external" value="no" id="oirl-mb-embed-external-no" <?php echo ( ! in_array( $embed_external, array( 'strava', 'garmin', 'endomondo' ), true ) ? 'checked' : '')?>>
			<label for="oirl-mb-embed-external-no"><?php echo esc_html__( 'No' )?></label>
			&nbsp;
			<input type="radio" name="oirl-mb-embed-external" value="strava" id="oirl-mb-embed-external-strava" <?php echo ( 'strava' === $embed_external ? 'checked' : '')?>>
			<label for="oirl-mb-embed-external-strava"><?php echo esc_html__( 'Strava', 'run-log' )?></label>
			&nbsp;
			<input type="radio" name="oirl-mb-embed-external" value="garmin" id="oirl-mb-embed-external-garmin" <?php echo ( 'garmin' === $embed_external ? 'checked' : '')?>>
			<label for="oirl-mb-embed-external-garmin"><?php echo esc_html__( 'Garmin', 'run-log' )?></label>
			&nbsp;
			<input type="radio" name="oirl-mb-embed-external" value="endomondo" id="oirl-mb-embed-external-endomondo" <?php echo ( 'endomondo' === $embed_external ? 'checked' : '')?>>
			<label for="oirl-mb-embed-external-endomondo"><?php echo esc_html__( 'Endomondo', 'run-log' )?></label>
			<br>
			<div id="oirl-div-embed-external-strava" style="display:none;">
				<label for="oirl-mb-strava-activity"><?php echo esc_html__( 'Strava embed activity', 'run-log' )?>:</label>
				<input name="oirl-mb-strava-activity" type="number" size="8" maxlength="12" value="<?php echo esc_attr( get_post_meta( $post->ID, 'oirl-mb-strava-activity', true ) ); ?>" title="<?php echo esc_attr__( 'Enter Strava activity ID - the number at the end of activity\'s page address', 'run-log' )?>">
			</div>
			<div id="oirl-div-embed-external-garmin" style="display:none;">
				<label for="oirl-mb-garmin-activity"><?php echo esc_html__( 'Garmin Connect embed activity', 'run-log' )?>:</label>
				<input name="oirl-mb-garmin-activity" type="number" size="8" maxlength="12" value="<?php echo esc_attr( get_post_meta( $post->ID, 'oirl-mb-garmin-activity', true ) ); ?>" title="<?php echo esc_attr__( 'Enter Garmin activity ID - the number at the end of activity\'s page address', 'run-log' )?>">
			</div>
			<div id="oirl-div-embed-external-endomondo" style="display:none;">
				<label for="oirl-mb-endomondo-activity"><?php echo esc_html__( 'Endomondo embed activity', 'run-log' )?>:</label>
				<input name="oirl-mb-endomondo-activity" type="number" size="8" maxlength="12" value="<?php echo esc_attr( get_post_meta( $post->ID, 'oirl-mb-endomondo-activity', true ) ); ?>" title="<?php echo esc_attr__( 'Enter endomondo activity ID - the number at the end of activity\'s page address', 'run-log' )?>">
			</div>
		</div>
	</div>

	<?php
}


/**
 * Load admin js and css.
 *
 * @since 1.5.0
 *
 * @param string $hook .
 */
function oirl_admin_scripts( $hook ) {
	global $oirl_manage_options_hook;
	if ( 'post-new.php' !== $hook && $oirl_manage_options_hook !== $hook && 'post.php' !== $hook ) {
		return;
	}
	wp_enqueue_script( 'oirl-admin-script', plugin_dir_url( __FILE__ ) . '/js/admin-script.js', array( 'jquery', 'jquery-ui-tooltip' ), '1.0.1', true );
	$css_file_name = 'run-log' . (is_rtl() ? '-rtl' : '') . '.css';
	wp_enqueue_style( 'oirl-css', plugin_dir_url( __FILE__ ) . "/$css_file_name" );
}
add_action( 'admin_enqueue_scripts', 'oirl_admin_scripts' );

/**
 * Saving the meta-box data
 *
 * @since 1.0.0
 *
 * @param int     $post_id post ID.
 * @param WP_Post $post the post object.
 */
function oirl_save_run_log_meta_boxes( $post_id, $post ) {
	// Check nonce for anti-CSRF.
	$oirl_mb_nonce = filter_input( INPUT_POST, 'run-log-meta-box-nonce', FILTER_SANITIZE_STRING );
	if ( ! isset( $oirl_mb_nonce ) || ! wp_verify_nonce( $oirl_mb_nonce, basename( __FILE__ ) ) ) {
		return $post_id;
	}
	// Check user permisions.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}
	// If this is an autosave, form hasn't been submitted - don't do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}
	// Validate this is a Run Log post.
	if ( 'oi_run_log_post' !== $post->post_type ) {
		return $post_id;
	}

	$plugin_ops = get_option( 'oi-run-log-options' );
	$distance_unit = isset( $plugin_ops['distance_unit'] ) ? $plugin_ops['distance_unit'] : 'km';

	$oirl_mb_distance = filter_input( INPUT_POST, 'oirl-mb-distance', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
	if ( isset( $oirl_mb_distance ) && is_numeric( $oirl_mb_distance ) ) {
		$distance = floatval( $oirl_mb_distance );
		// if distance unit is mi, convert to km befor saving.
		if ( 'mi' === $distance_unit ) {
			$distance = iorl_distance_converter( $distance, 'M2K' );
		}
		update_post_meta( $post_id, 'oirl-mb-distance', $distance );
	}

	$oirl_mb_duration = filter_input( INPUT_POST, 'oirl-mb-duration', FILTER_VALIDATE_REGEXP, array( 'options' => array( 'regexp' => '/^\s*(?:\d{1,2}:)?[0-5]?\d:[0-5]?\d\s*$/' ) ) );
	if ( isset( $oirl_mb_duration ) && preg_match( '/^\s*(?:(\d{1,2}):)?([0-5]?\d):([0-5]?\d)\s*$/', $oirl_mb_duration, $duration_matches ) ) {
		$duration = sprintf( '%02d:%02d:%02d', ( is_numeric( $duration_matches[1] ) ? $duration_matches[1] : 0 ), $duration_matches[2], $duration_matches[3] );
		update_post_meta( $post_id, 'oirl-mb-duration', $duration );
	}

	$oirl_mb_elevation = filter_input( INPUT_POST, 'oirl-mb-elevation', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
	if ( isset( $oirl_mb_elevation ) && is_numeric( $oirl_mb_elevation ) ) {
		$elevation = floatval( $oirl_mb_elevation );
		// if distance unit is mi, convert to ft befor saving.
		if ( 'mi' === $distance_unit ) {
			$elevation = iorl_distance_converter( $elevation, 'F2M' );
		}
		update_post_meta( $post_id, 'oirl-mb-elevation', $elevation );
	}

	$oirl_mb_calories = filter_input( INPUT_POST, 'oirl-mb-calories', FILTER_SANITIZE_NUMBER_INT );
	if ( isset( $oirl_mb_calories ) && is_numeric( $oirl_mb_calories ) ) {
		$calories = intval( $oirl_mb_calories );
		update_post_meta( $post_id, 'oirl-mb-calories', $calories );
	}

	$oirl_mb_embed_external = filter_input( INPUT_POST, 'oirl-mb-embed-external', FILTER_VALIDATE_REGEXP, array( 'options' => array( 'regexp' => '/^(no|strava|garmin|endomondo)$/' ) ) );
	if ( isset( $oirl_mb_embed_external ) && in_array( $oirl_mb_embed_external, array( 'no', 'strava', 'garmin', 'endomondo' ), true ) ) {
		update_post_meta( $post_id, 'oirl-mb-embed-external', $oirl_mb_embed_external );
	}

	$oirl_mb_strava_activity = filter_input( INPUT_POST, 'oirl-mb-strava-activity', FILTER_SANITIZE_NUMBER_INT );
	if ( isset( $oirl_mb_strava_activity ) && is_numeric( $oirl_mb_strava_activity ) ) {
		$strava_activity = intval( $oirl_mb_strava_activity );
		update_post_meta( $post_id, 'oirl-mb-strava-activity', $strava_activity );
	} elseif ( get_post_meta( $post_id, 'oirl-mb-strava-activity' ) ) {
		delete_post_meta( $post_id, 'oirl-mb-strava-activity' );
	}

	$oirl_mb_garmin_activity = filter_input( INPUT_POST, 'oirl-mb-garmin-activity', FILTER_SANITIZE_NUMBER_INT );
	if ( isset( $oirl_mb_garmin_activity ) && is_numeric( $oirl_mb_garmin_activity ) ) {
		$garmin_activity = intval( $oirl_mb_garmin_activity );
		update_post_meta( $post_id, 'oirl-mb-garmin-activity', $garmin_activity );
	} elseif ( get_post_meta( $post_id, 'oirl-mb-garmin-activity' ) ) {
		delete_post_meta( $post_id, 'oirl-mb-garmin-activity' );
	}

	$oirl_mb_endomondo_activity = filter_input( INPUT_POST, 'oirl-mb-endomondo-activity', FILTER_SANITIZE_NUMBER_INT );
	if ( isset( $oirl_mb_endomondo_activity ) && is_numeric( $oirl_mb_endomondo_activity ) ) {
		$endomondo_activity = intval( $oirl_mb_endomondo_activity );
		update_post_meta( $post_id, 'oirl-mb-endomondo-activity', $endomondo_activity );
	} elseif ( get_post_meta( $post_id, 'oirl-mb-endomondo-activity' ) ) {
		delete_post_meta( $post_id, 'oirl-mb-endomondo-activity' );
	}
}
// register this function to save_post action with 2 args.
add_action( 'save_post', 'oirl_save_run_log_meta_boxes', 10, 2 );


/**
 * Add the run log data to the post or excerpt.
 *
 * @since 1.0.0
 *
 * @param string $content the content of post's body.
 * @param bool   $excerpt Optional. true if this is a call from excerpt filter.
 *
 * @return string the content with the HTML output of the run log data.
 */
function oirl_add_run_log_data_to_post( $content, $excerpt = false ) {
	// Return original content if not run log custom post type.
	if ( get_post_type() !== 'oi_run_log_post' ) {
		return $content;
	}

	// Get plugin options from db.
	$plugin_ops = get_option( 'oi-run-log-options' );
	$add_at_pos = isset( $plugin_ops['display_pos'] ) ? $plugin_ops['display_pos'] : 'top';
	$display_on_excerpt = isset( $plugin_ops['display_on_excerpt'] ) ? $plugin_ops['display_on_excerpt'] : 0;
	$distance_unit = isset( $plugin_ops['distance_unit'] ) ? $plugin_ops['distance_unit'] : 'km';
	$pace_or_speed = isset( $plugin_ops['pace_or_speed'] ) ? $plugin_ops['pace_or_speed'] : 'pace';
	$style_theme = isset( $plugin_ops['style_theme'] ) ? $plugin_ops['style_theme'] : 'light';
	$gear_links = isset( $plugin_ops['gear_links'] ) ? $plugin_ops['gear_links'] : '1';
	$goal_links = isset( $plugin_ops['goal_links'] ) ? $plugin_ops['goal_links'] : '1';

	$bottom_links = '';
	if ( '1' === $goal_links && ! $excerpt ) {
		$goal_list = get_the_term_list( $GLOBALS['post']->ID, 'oi_goal_taxonomy', esc_html__( 'Goal', 'run-log' ) . ': ', ', ', '' );
		if ( false !== $goal_list && ! is_wp_error( $goal_list ) ) {
			$bottom_links .= $goal_list;
		}
	}
	if ( '1' === $gear_links && ! $excerpt ) {
		$gear_list = get_the_term_list( $GLOBALS['post']->ID, 'oi_gear_taxonomy', esc_html__( 'Gear', 'run-log' ) . ': ', ', ', '' );
		if ( false !== $gear_list && ! is_wp_error( $gear_list ) ) {
			$bottom_links .= ( '' !== $bottom_links ? '; ' : '' ) . $gear_list;
		}
	}
	if ( '' !== $bottom_links ) {
		$bottom_links = "\n<div id=\"oril_bottom_links\">$bottom_links</div>\n";
	}

	// return original content if display is 'none' or if its excerpt and display_on_excerpt option is No.
	if ( ( 'none' === $add_at_pos && ! $excerpt ) || ( $excerpt && ! $display_on_excerpt ) ) {
		return $content . $bottom_links;
	}

	$embed_external = get_post_meta( $GLOBALS['post']->ID, 'oirl-mb-embed-external', true );
	$strava_activity = get_post_meta( $GLOBALS['post']->ID, 'oirl-mb-strava-activity', true );
	$garmin_activity = get_post_meta( $GLOBALS['post']->ID, 'oirl-mb-garmin-activity', true );
	$endomondo_activity = get_post_meta( $GLOBALS['post']->ID, 'oirl-mb-endomondo-activity', true );

	// Embed strava/garmin/endomondo activity if got its ID (and it isn't an excerpt).
	if ( ( ! $embed_external || 'strava' === $embed_external) && $strava_activity && preg_match( '/^\d+$/', $strava_activity ) && ! $excerpt ) {
		$unit_system = 'mi' === $distance_unit ? 'imperial' : 'metric';
		$strava_embed = "<a href='https://www.strava.com/activities/$strava_activity' rel='noopener noreferrer' target='_blank'><img  src='https://meme.strava.com/map_based/activities/$strava_activity.jpeg?height=630&width=1200&hl=en-US&unit_system=$unit_system&cfs=1&upscale=1' alt='Activity data and map from STRAVA' width='500' height='263' border='0'></a>\n";
		return ( 'bottom' === $add_at_pos ? $content . $strava_embed . $bottom_links : $strava_embed . $content . $bottom_links );
	} elseif ( ( ! $embed_external || 'garmin' === $embed_external ) && $garmin_activity && preg_match( '/^\d+$/', $garmin_activity ) && ! $excerpt ) {
			$garmin_iframe = "<iframe src='https://connect.garmin.com/activity/embed/$garmin_activity' width='465' height='500' frameborder='0'></iframe>\n";
			return ( 'bottom' === $add_at_pos ? $content . $garmin_iframe . $bottom_links : $garmin_iframe . $content . $bottom_links );
	} elseif ( ( ! $embed_external || 'endomondo' === $embed_external) && $endomondo_activity && preg_match( '/^\d+$/', $endomondo_activity ) && ! $excerpt ) {
		$endomondo_iframe = "<iframe src='http://www.endomondo.com/embed/workouts?w=$endomondo_activity&width=580&height=425' width='580' height='425' frameborder='1'></iframe>\n";
		return ( 'bottom' === $add_at_pos ? $content . $endomondo_iframe . $bottom_links : $endomondo_iframe . $content . $bottom_links );
	}

	$distance = get_post_meta( $GLOBALS['post']->ID, 'oirl-mb-distance', true );
	$distance = ( 'mi' === $distance_unit ? iorl_distance_converter( $distance, 'K2M' ) : $distance );
	$duration = get_post_meta( $GLOBALS['post']->ID, 'oirl-mb-duration', true );
	$pace = iorl_calculate_pace( $distance, $duration, $pace_or_speed );

	if ( ( ! isset( $distance ) || intval( $distance ) <= 0 ) && ( ! isset( $duration ) || intval( $distance ) <= 0 ) ) {
		return $content;
	}

	// HTML output.
	$run_log_data = '<div class="oirl oirl-' . $style_theme . ' oirl-data-box">';

	// Distance.
	$run_log_data .= '<div class="oirl-data">';
	$run_log_data .= '<span class="oirl-data-desc">' . esc_html__( 'Distance', 'run-log' ) . '</span>';
	$run_log_data .= "<span class='oirl-data-value'>$distance</span> " . ( 'mi' === $distance_unit ? esc_html__( 'mi', 'run-log' ) : esc_html__( 'km', 'run-log' ) );
	$run_log_data .= "</div>\n";

	// Duration.
	$run_log_data .= '<div class="oirl-data">';
	$run_log_data .= '<span class="oirl-data-desc">' . esc_html__( 'Duration', 'run-log' ) . "</span> <span class='oirl-data-value'>$duration" . '</span>';
	$run_log_data .= "</div>\n";

	// Pace/Speed.
	$run_log_data .= '<div class="oirl-data">';
	$run_log_data .= '<span class="oirl-data-desc">';
	if ( 'speed' === $pace_or_speed ) {
		$run_log_data .= esc_html__( 'Speed', 'run-log' ) . '</span>';
		$run_log_data .= "<span class='oirl-data-value'>$pace</span> ";
		if ( 'mi' === $distance_unit ) {
			$run_log_data .= esc_html__( 'mi/h', 'run-log' );
		} else {
			$run_log_data .= esc_html__( 'km/h', 'run-log' );
		}
	} else {
		$run_log_data .= esc_html__( 'Pace', 'run-log' ) . '</span>';
		$run_log_data .= "<span class='oirl-data-value'>$pace</span> ";
		if ( 'mi' === $distance_unit ) {
			$run_log_data .= esc_html__( 'min/mi', 'run-log' );
		} else {
			$run_log_data .= esc_html__( 'min/km', 'run-log' );
		}
	}
	$run_log_data .= "</div>\n";
	$run_log_data .= '</div>';

	return ( 'bottom' === $add_at_pos ? $content . $run_log_data . $bottom_links : $run_log_data . $content . $bottom_links );
}
add_filter( 'the_content', 'oirl_add_run_log_data_to_post' );

/**
 * Wraper function for addung run log data to excerpt by calling the post filter function.
 *
 * @since 1.2.0
 *
 * @param string $excerp the excerp string.
 *
 * @return string the excerp content with the HTML output of the run log data.
 */
function oirl_add_run_log_data_to_excerpt( $excerp ) {
	return oirl_add_run_log_data_to_post( $excerp, true );
}
add_filter( 'get_the_excerpt', 'oirl_add_run_log_data_to_excerpt' );

/**
 * Add gear summery data to the gear archive page.
 *
 * @since 1.6.0
 *
 * @param string $term_description the content of gear description (if any).
 *
 * @return string the archive description with the HTML output of the gear summery data.
 */
function oirl_add_run_log_data_to_gear( $term_description ) {
	// Return original content if not gear taxonomy.
	if ( ! is_tax( 'oi_gear_taxonomy' ) ) {
	 	return $term_description;
	}

	$term = get_queried_object(); // has: term_id, slug, count, name, etc.
	$gear_data = oirl_total_shortcode( array( 'term' => $term->term_id, 'only' => 'distance' ) );

	return $gear_data . $term_description;
}
add_filter( 'get_the_archive_description', 'oirl_add_run_log_data_to_gear' );

/**
 * Add gear summery data to the goal archive page.
 *
 * @since 1.6.0
 *
 * @param string $term_description the content of goal description (if any).
 *
 * @return string the archive description with the HTML output of the gear summery data.
 */
function oirl_add_run_log_data_to_goal( $term_description ) {
	// Return original content if not goal taxonomy.
	if ( ! is_tax( 'oi_goal_taxonomy' ) ) {
	 	return $term_description;
	}

	$term = get_queried_object(); // has: term_id, slug, count, name, etc.
	$goal_distance = oirl_total_shortcode( array( 'term' => $term->term_id, 'only' => 'distance' ) );
	$goal_duration = oirl_total_shortcode( array( 'term' => $term->term_id, 'only' => 'time' ) );
	$goal_data = "$goal_distance &nbsp; $goal_duration";
	$goal_data = preg_replace( '/<\/div>\s*&nbsp;\s*<div class="oirl oirl-\w+ oirl-total-box">/', '', $widget_data );

	return $goal_data . $term_description;
}
add_filter( 'get_the_archive_description', 'oirl_add_run_log_data_to_goal' );

/**
 * Load the proper CSS file (LTR or RTL).
 *
 * @since 1.0.0
 */
function iorl_enqueue_css() {
	$css_file_name = 'run-log' . (is_rtl() ? '-rtl' : '') . '.css';
	wp_enqueue_style( 'wpdocsPluginStylesheet', plugins_url( $css_file_name, __FILE__ ), null, '1.1.0' );
}
add_action( 'wp_enqueue_scripts', 'iorl_enqueue_css' );

/**
 * A widget for displaying totals (distance/time/elevation/calories).
 *
 * @since 1.7.0
 */
class OIRL_Total_Widget extends WP_Widget {

	/**
	 * Sets up the widgets name etc.
	 *
	 * @since 1.7.0
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' => 'oirl_total_widget',
			'description' => __( 'Show total distance/duration of your activeties', 'run-log' ),
		);
		parent::__construct( 'oirl_total_widget', __( 'Totals Widget', 'run-log' ), $widget_ops );
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 * @since 1.7.0
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . esc_html( apply_filters( 'widget_title', $instance['title'] ) ) . $args['after_title'];
		}

		$widget_data = '';
		$option = array();
		if ( isset( $instance['time_frame'] ) && 'this_year' === $instance['time_frame'] ) {
			$option['year'] = date( 'Y' );
		} elseif ( isset( $instance['time_frame'] ) && 'this_month' === $instance['time_frame'] ) {
			$option['year'] = date( 'Y' );
			$option['month'] = date( 'm' );
		}
		if ( isset( $instance['display_distance'] ) && 'on' === $instance['display_distance'] ) {
			$option['only'] = 'distance';
			$widget_data .= oirl_total_shortcode( $option );
		}
		if ( isset( $instance['display_duration'] ) && 'on' === $instance['display_duration'] ) {
			$option['only'] = 'time';
			$widget_data .= oirl_total_shortcode( $option );
		}
		if ( isset( $instance['display_elevation'] ) && 'on' === $instance['display_elevation'] ) {
			$option['only'] = 'elevation';
			$widget_data .= oirl_total_shortcode( $option );
		}
		if ( isset( $instance['display_calories'] ) && 'on' === $instance['display_calories'] ) {
			$option['only'] = 'calories';
			$widget_data .= oirl_total_shortcode( $option );
		}

		$widget_data = preg_replace( '/<\/div>\s*<div class="oirl oirl-\w+ oirl-total-box">/', '', $widget_data );
		echo $widget_data;

		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 * @since 1.7.0
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = isset( $instance['title'] ) && ! empty( $instance['title'] ) ? $instance['title'] : '';
		$display_distance = isset( $instance['display_distance'] ) && 'on' === $instance['display_distance'] ? true : false;
		$display_duration = isset( $instance['display_duration'] )  && 'on' === $instance['display_duration'] ? true : false;
		$display_elevation = isset( $instance['display_elevation'] ) && 'on' === $instance['display_elevation'] ? true : false;
		$display_calories = isset( $instance['display_calories'] )  && 'on' === $instance['display_calories'] ? true : false;
		$time_frame = isset( $instance['time_frame'] ) ? $instance['time_frame'] : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<input class="checkbox" type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'display_distance' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display_distance' ) ); ?>" <?php checked( $display_distance ); ?>>
			<label for="<?php echo esc_attr( $this->get_field_id( 'display_distance' ) ); ?>"><?php esc_html_e( 'Display distance', 'run-log' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'display_duration' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display_duration' ) ); ?>" <?php checked( $display_duration ); ?>>
			<label for="<?php echo esc_attr( $this->get_field_id( 'display_duration' ) ); ?>"><?php esc_html_e( 'Display duration', 'run-log' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'display_elevation' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display_elevation' ) ); ?>" <?php checked( $display_elevation ); ?>>
			<label for="<?php echo esc_attr( $this->get_field_id( 'display_elevation' ) ); ?>"><?php esc_html_e( 'Display elevation', 'run-log' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'display_calories' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display_calories' ) ); ?>" <?php checked( $display_calories ); ?>>
			<label for="<?php echo esc_attr( $this->get_field_id( 'display_calories' ) ); ?>"><?php esc_html_e( 'Display calories', 'run-log' ); ?></label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'time_frame' ) ); ?>"><?php esc_html_e( 'Time Frame', 'run-log' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'time_frame' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'time_frame' ) ); ?>">
				<option value="all_time" <?php selected( 'all_time', $time_frame ); ?>><?php esc_html_e( 'All Time', 'run-log' ) ?></option>
				<option value="this_year" <?php selected( 'this_year', $time_frame ); ?>><?php esc_html_e( 'This Year', 'run-log' ) ?></option>
				<option value="this_month" <?php selected( 'this_month', $time_frame ); ?>><?php esc_html_e( 'This Month', 'run-log' ) ?></option>
			</select>
		</p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 * @since 1.7.0
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( isset( $new_instance['title'] ) && ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['display_distance'] = isset( $new_instance['display_distance'] ) ? $new_instance['display_distance'] : '';
		$instance['display_duration'] = isset( $new_instance['display_duration'] ) ? $new_instance['display_duration'] : '';
		$instance['display_elevation'] = isset( $new_instance['display_elevation'] ) ? $new_instance['display_elevation'] : '';
		$instance['display_calories'] = isset( $new_instance['display_calories'] ) ? $new_instance['display_calories'] : '';
		$instance['time_frame'] = isset( $new_instance['time_frame'] ) ? $new_instance['time_frame'] : 'all_time';
		return $instance;
	}
} // class OIRL_Total_Widget
add_action( 'widgets_init', function() {
	register_widget( 'OIRL_Total_Widget' );
});

/**
 * Add Shortcode for displaying activeties totals.
 * Usage examples: [oirl_total], [oirl_total only="distance"], [oirl_total year="2015" hide_pace="yes"]
 *
 * @since 1.4.0
 *
 * @param array $atts the attributes arry (all are optional) -
 *                    only: distance/time/elevation/calories;
 *                    year: a 4-digit year - display totals for this year only;
 *                    month: 1/2-digits for month - totals for this month only;
 *                    hide_pace: yes/no - should average pace/speed be hidden;
 *										days_display: true/false - display days in total time if more then 24 hours
 *										term: term id. if given, only data from post with thos term will sumed.
 *
 * @return string the output - HTML of activities totals.
 */
function oirl_total_shortcode( $atts ) {
	global $wpdb;
	// Attributes defaults when needed.
	$atts = shortcode_atts(
		array(
			'only'				=> '',
			'year'				=> '',
			'month'				=> '',
			'hide_pace'		=> 'no',
			'days_display' => 'no',
			'term'				=> '',
		),
		$atts,
		'oirl_total'
	);

	// Get plugin options from db.
	$plugin_ops = get_option( 'oi-run-log-options' );
	$distance_unit = isset( $plugin_ops['distance_unit'] ) ? $plugin_ops['distance_unit'] : 'km';
	$pace_or_speed = isset( $plugin_ops['pace_or_speed'] ) ? $plugin_ops['pace_or_speed'] : 'pace';
	$style_theme = isset( $plugin_ops['style_theme'] ) ? $plugin_ops['style_theme'] : 'light';

	// Prepare the where conditions for the time period if needed.
	$period_where = '';
	$qry_value_parameters = array();
	if ( isset( $atts['year'] ) && preg_match( '/^[12]\d{3}$/', $atts['year'] ) ) {
		$period_where = " AND `post_id` IN( SELECT `ID` FROM $wpdb->posts WHERE YEAR( `post_date` ) = %d";
		array_push( $qry_value_parameters, $atts['year'] );
		if ( isset( $atts['month'] ) && preg_match( '/^\d\d?$/', $atts['month'] ) ) {
				$period_where .= ' AND MONTH( `post_date` ) = %d';
				array_push( $qry_value_parameters, sprintf( '%02d', $atts['month'] ) );
				$month_name = date( 'F', mktime( 0, 0, 0, $atts['month'], 10 ) );
		}
		$period_where .= ')';
	}
	// Prepare the where conditions for the term if needed.
	if ( isset( $atts['term'] ) && preg_match( '/^[1-9]\d*$/', $atts['term'] ) ) {
		$period_where .= " AND `post_id` IN(	SELECT `object_id` FROM $wpdb->term_relationships WHERE `term_taxonomy_id` = %d )";
		array_push( $qry_value_parameters, $atts['term'] );
	}

	// Output code start.
	$output = '<div class="oirl oirl-' . $style_theme . ' oirl-' . ( '' !== $atts['only'] ? 'total' : 'data') . '-box">';

	// Check & output distance total if needed.
	if ( in_array( $atts['only'], array( '', 'distance' ), true ) ) {
		array_unshift( $qry_value_parameters, 'oirl-mb-distance' );
		$distance_total = $wpdb->get_var( $wpdb->prepare(
			"
SELECT SUM( `meta_value` ) AS total_distance
FROM $wpdb->postmeta
WHERE `meta_key`=%s $period_where
			",
		$qry_value_parameters ) );
		array_shift( $qry_value_parameters );
		$distance_total = ( 'mi' === $distance_unit ? iorl_distance_converter( $distance_total, 'K2M' ) : $distance_total );
		$distance_total = sprintf( '%.1f', $distance_total );

		if ( 'distance' === $atts['only'] ) {
			$output .= '<div class="oirl-data"><div class="oirl-data-desc">' . esc_html__( 'Total distance', 'run-log' );
			if ( $atts['year'] ) {
				if ( $atts['month'] ) {
					$output .= ' ' . esc_html__( $month_name );
				}
				$output .= " {$atts['year']}";
			}
			$output .= '</div>';
			foreach ( str_split( $distance_total ) as $cur_char ) {
				if ( '.' === $cur_char ) {
					$output .= '<span class="sub bold">.</span>';
					continue;
				}
				$output .= "<span class=\"oirl-counter\"><span>$cur_char</span></span>";
			}
			$output .= '<span class="super">' . ( 'mi' === $distance_unit ? esc_html__( 'mi', 'run-log' ) : esc_html__( 'km', 'run-log' ) ) . '</span></div>';
		} else {
			$output .= '<div class="oirl-data">';
			$output .= '<span class="oirl-data-desc">' . esc_html__( 'Total distance', 'run-log' ) . "</span> <span class=\"oirl-data-value\">$distance_total</span>";
			$output .= ( 'mi' === $distance_unit ? esc_html__( 'mi', 'run-log' ) : esc_html__( 'km', 'run-log' ) ) . '</div>';
		}
	}

	// Check & output duration total if needed.
	if ( in_array( $atts['only'], array( '', 'time' ), true ) ) {
		array_unshift( $qry_value_parameters, 'oirl-mb-duration' );
		$duration_total = $wpdb->get_var( $wpdb->prepare(
			"
SELECT SUM(
  TIME_TO_SEC(
    MAKETIME(
      SUBSTRING(`meta_value`, 1, 2),
      SUBSTRING(`meta_value`, 4, 2),
      SUBSTRING(`meta_value`, 7, 2)
    )
  )
) / 60 as duration_min
FROM $wpdb->postmeta
WHERE `meta_key`=%s $period_where
			",
			$qry_value_parameters
		) );
		// Remove 'oirl-mb-duration' from $qry_value_parameters for next queries if any.
		array_shift( $qry_value_parameters );
		$duration_sec = ($duration_total - floor( $duration_total )) * 60;
		$duration_hour = floor( $duration_total / 60 );
		$duration_min = floor( $duration_total ) - ($duration_hour * 60);
		$duration = sprintf( '%02d:%02d:%02d', $duration_hour, $duration_min, $duration_sec );

		if ( 'time' === $atts['only'] ) {
			$duration_days = floor( $duration_hour / 24 );
			$duration_hours_after_days = $duration_hour - ( $duration_days * 24 );
			$duration_with_days_display = sprintf( '%d:%02d:%02d:%02d', $duration_days, $duration_hours_after_days, $duration_min, $duration_sec );
			$duration_with_days_display = preg_replace( '/^0:/', '', $duration_with_days_display );
			$display_chars = str_split( ( 'yes' === $atts['days_display'] ? $duration_with_days_display : $duration ) );

			$output .= '<div class="oirl-data"><div class="oirl-data-desc">' . esc_html__( 'Total duration', 'run-log' );
			if ( $atts['year'] ) {
				if ( $atts['month'] ) {
					$output .= ' ' . esc_html__( $month_name );
				}
				$output .= " {$atts['year']}";
			}
			$output .= '</div>';
			$sec_countdown = strlen( $duration ) > 8 ? 3 : 2;
			foreach ( $display_chars as $cur_char ) {
				if ( ':' === $cur_char ) {
					$sec_countdown--;
					if ( 0 === $sec_countdown ) {
						$output .= '<span class="smaller sub">';
					}
					$output .= ':';
				} else {
					$output .= "<span class=\"oirl-counter\"><span>$cur_char</span></span>";
				}
			}
			$output .= '</span>';
			$output .= '</div>';
		} else {
			$output .= '<div class="oirl-data"><span class="oirl-data-desc">' . esc_html__( 'Total duration', 'run-log' ) . "</span> <span class=\"oirl-data-value\">$duration</span>";
			$output .= '</div>';
		}
	}

	// Check & output elevation total if needed.
	if ( 'elevation' === $atts['only'] ) {
		array_unshift( $qry_value_parameters, 'oirl-mb-elevation' );
		$elevation_total = $wpdb->get_var( $wpdb->prepare(
			"
SELECT SUM( `meta_value` ) AS total_elevation
FROM $wpdb->postmeta
WHERE `meta_key`=%s $period_where
			",
			$qry_value_parameters
		) );
		array_shift( $qry_value_parameters );
		// Conversion to feet if needed?
		$elevation_total = ( 'mi' === $distance_unit ? iorl_distance_converter( $elevation_total, 'M2F' ) : $elevation_total );
		$elevation_total = round( $elevation_total );
		$output .= '<div class="oirl-data"><div class="oirl-data-desc">' . esc_html__( 'Total elevation', 'run-log' );
		if ( $atts['year'] ) {
			if ( $atts['month'] ) {
				$output .= ' ' . esc_html__( $month_name );
			}
			$output .= " {$atts['year']}";
		}
		$output .= '</div>';
		foreach ( str_split( $elevation_total ) as $cur_char ) {
			$output .= "<span class=\"oirl-counter\"><span>$cur_char</span></span>";
		}
		// Conversion to feet if needed?.
		$output .= '<span class="super">' . ( 'mi' === $distance_unit ? esc_html__( 'ft', 'run-log' ) : esc_html__( 'm', 'run-log' ) ) . '</span></div>';
	}

	// Check & output calories total if needed.
	if ( 'calories' === $atts['only'] ) {
		array_unshift( $qry_value_parameters, 'oirl-mb-calories' );
		$calories_total = $wpdb->get_var( $wpdb->prepare(
			"
SELECT SUM( `meta_value` ) AS total_calories
FROM $wpdb->postmeta
WHERE `meta_key`=%s $period_where
			",
			$qry_value_parameters
		) );
		array_shift( $qry_value_parameters );
		$output .= '<div class="oirl-data"><div class="oirl-data-desc">' . esc_html__( 'Total calories', 'run-log' );
		if ( $atts['year'] ) {
			if ( $atts['month'] ) {
				$output .= ' ' . esc_html__( $month_name );
			}
			$output .= " {$atts['year']}";
		}
		$output .= '</div>';
		foreach ( str_split( $calories_total ) as $cur_char ) {
			$output .= "<span class=\"oirl-counter\"><span>$cur_char</span></span>";
		}
		$output .= '</div>';
	}

	// Camculate pace if needed.
	if ( '' === $atts['only'] && 'yes' !== $atts['hide_pace'] ) {
		$pace = iorl_calculate_pace( $distance_total, $duration, $pace_or_speed );

		if ( 'speed' === $pace_or_speed ) {
			$output .= '<div class="oirl-data"><span class="oirl-data-desc">' . esc_html__( 'Average speed', 'run-log' ) . "</span> <span class=\"oirl-data-value\">$pace</span>";
			$output .= ( 'mi' === $distance_unit ? esc_html__( 'mi/h', 'run-log' ) : esc_html__( 'km/h', 'run-log' ) ) . '</div>';
		} else {
			$output .= '<div class="oirl-data"><span class="oirl-data-desc">' . esc_html__( 'Average pace', 'run-log' ) . "</span> <span class=\"oirl-data-value\">$pace</span>";
			$output .= ( 'mi' === $distance_unit ? esc_html__( 'min/mi', 'run-log' ) : esc_html__( 'min/km', 'run-log' ) ) . '</div>';
		}
	}

	// Output end.
	$output .= '</div>';

	return $output;
}
add_shortcode( 'oirl_total', 'oirl_total_shortcode' );


/**
 * Make this custom content type available for search/archive pages
 *
 * @since 1.0.0
 *
 * @param WP_Query $query the query object.
 *
 * @return WP_Query the query object after adding run-log custom post_type.
 */
function iorl_run_log_update_get_posts( $query ) {
	if ( ( is_home() && $query->is_main_query() ) || is_category() || is_tag() ) {
		if ( empty( $query->query_vars['suppress_filters'] ) ) {
			$this_qry_post_types = $query->get( 'post_type' );
			if ( empty( $this_qry_post_types ) ) {
				$this_qry_post_types = 'Post';
			}
			$new_qry_post_types = array_merge( (array) $this_qry_post_types, array( 'oi_run_log_post' ) );
			$query->set( 'post_type', $new_qry_post_types );
		}
	}
	return $query;
}
// Hook into the 'pre_get_posts' filter.
add_filter( 'pre_get_posts', 'iorl_run_log_update_get_posts' );

/**
 * Register goal Custom Taxonomy.
 *
 * @since 1.0.0
 */
function iorl_register_goal_taxonomy() {

	$labels = array(
		'name'                       => _x( 'Goals', 'Taxonomy General Name', 'run-log' ),
		'singular_name'              => _x( 'Goal', 'Taxonomy Singular Name', 'run-log' ),
		'menu_name'                  => __( 'Goal', 'run-log' ),
		'all_items'                  => __( 'All Goals', 'run-log' ),
		'parent_item'                => __( 'Parent Goal', 'run-log' ),
		'parent_item_colon'          => __( 'Parent Goal:', 'run-log' ),
		'new_item_name'              => __( 'New Goal Name', 'run-log' ),
		'add_new_item'               => __( 'Add New Goal', 'run-log' ),
		'edit_item'                  => __( 'Edit Goal', 'run-log' ),
		'update_item'                => __( 'Update Goal', 'run-log' ),
		'separate_items_with_commas' => __( 'Separate goals with commas', 'run-log' ),
		'search_items'               => __( 'Search goals', 'run-log' ),
		'add_or_remove_items'        => __( 'Add or remove goals', 'run-log' ),
		'choose_from_most_used'      => __( 'Choose from the most used goals', 'run-log' ),
		'not_found'                  => __( 'Goal Not Found', 'run-log' ),
	);
	$rewrite = array(
		'slug'                       => 'goal',
		'with_front'                 => true,
		'hierarchical'               => false,
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
		'rewrite'                    => $rewrite,
	);
	register_taxonomy( 'oi_goal_taxonomy', array( 'page', 'post', 'oi_run_log_post' ), $args );

}
// Hook into the 'init' action.
add_action( 'init', 'iorl_register_goal_taxonomy', 0 );


/**
 * Register Gear Custom Taxonomy.
 *
 * @since 1.0.0
 */
function iorl_register_gear_taxonomy() {

	$labels = array(
		'name'                       => _x( 'Gears', 'Taxonomy General Name', 'run-log' ),
		'singular_name'              => _x( 'Gear', 'Taxonomy Singular Name', 'run-log' ),
		'menu_name'                  => __( 'Gear', 'run-log' ),
		'all_items'                  => __( 'All Gears', 'run-log' ),
		'parent_item'                => __( 'Parent Gear', 'run-log' ),
		'parent_item_colon'          => __( 'Parent Gear:', 'run-log' ),
		'new_item_name'              => __( 'New Gear Name', 'run-log' ),
		'add_new_item'               => __( 'Add New Gear', 'run-log' ),
		'edit_item'                  => __( 'Edit Gear', 'run-log' ),
		'update_item'                => __( 'Update Gear', 'run-log' ),
		'separate_items_with_commas' => __( 'Separate gears with commas', 'run-log' ),
		'search_items'               => __( 'Search Gears', 'run-log' ),
		'add_or_remove_items'        => __( 'Add or remove gears', 'run-log' ),
		'choose_from_most_used'      => __( 'Choose from the most used gears', 'run-log' ),
		'not_found'                  => __( 'Gear Not Found', 'run-log' ),
	);
	$rewrite = array(
		'slug'                       => 'gear',
		'with_front'                 => true,
		'hierarchical'               => false,
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => false,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
		'rewrite'                    => $rewrite,
	);
	register_taxonomy( 'oi_gear_taxonomy', array( 'post', 'page', 'oi_run_log_post' ), $args );

}
// Hook into the 'init' action.
add_action( 'init', 'iorl_register_gear_taxonomy', 0 );


/**
 * Calculate pace/speed acording to given distance and duration.
 *
 * @since 1.0.0
 *
 * @param float  $distance the distance you've run (should be bigger than 0).
 * @param string $duration the activety duration in hh:mm:ss format.
 * @param string $type Optional. Output type. Accepts 'pace' or 'speed'. Default 'pace'.
 *
 * @return float the pace/speed. 0 if invalid param given.
 */
function iorl_calculate_pace( $distance, $duration, $type = 'pace' ) {
	$distance = floatval( $distance );
	if ( ! $distance || ! preg_match( '/^(\d+):([0-5]\d):([0-5]\d)$/', $duration, $duration_matches ) || ! in_array( $type, array( 'pace', 'speed' ), true ) ) {
		return 0;
	}

	if ( 'pace' === $type ) {
		$duration_minutes = ( intval( $duration_matches[3] ) / 60 )
			+ intval( $duration_matches[2] )
			+ (intval( $duration_matches[1] ) * 60 );
		$pace_raw = $duration_minutes / $distance;
		$output = sprintf( '%d:%02d', floor( $pace_raw ), ($pace_raw - floor( $pace_raw )) * 60 );
	} else {
		$duration_hours = ( intval( $duration_matches[3] ) / (60 * 60) )
			+ ( intval( $duration_matches[2] ) / 60 )
			+ intval( $duration_matches[1] );
		$spped_raw = $distance / $duration_hours;
		$output = sprintf( '%.2f', $spped_raw );
	}
	return $output;
}

/**
 * Convert distances from Miles to Kilometers and vice versa.
 *
 * @since 1.0.0
 *
 * @param float  $distance the distance to convert (should be bigger than 0).
 * @param string $conversion Optional. Accepts 'M2K' form Miles to Kilometers;
 *                           'K2M' from Kilometers to Miles; 'M2F' Meters to Feet;
 *                           'F2M' Feet to Meters. Default 'M2K'.
 *
 * @return float the conversion outcome. 0 if invalid param given.
 */
function iorl_distance_converter( $distance, $conversion = 'M2K' ) {
	if ( ! is_numeric( $distance ) || floatval( $distance ) <= 0 || ! in_array( $conversion, array( 'K2M', 'M2K', 'M2F', 'F2M' ), true ) ) {
		return 0;
	}

	if ( 'M2F' === $conversion ) {
		return sprintf( '%.2f', floatval( $distance ) * 3.28084 );
	} elseif ( 'F2M' === $conversion ) {
		return sprintf( '%.2f', floatval( $distance ) * 0.3048 );
	} elseif ( 'M2K' === $conversion ) {
		return sprintf( '%.2f', floatval( $distance ) / 0.62137 );
	} else {
		return sprintf( '%.2f', floatval( $distance ) * 0.62137 );
	}
}

?>
