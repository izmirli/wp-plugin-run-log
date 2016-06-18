<?php
/*
Plugin Name: Run Log
Plugin URI: http://stuff.izmirli.org/wordpress-run-log-plugin/
Description: Adds running diary capabilities - custom post type, custom fields and new taxonomies.
Version: 1.0.0
Author: Oren Izmirli
Author URI: https://profiles.wordpress.org/izem
Text Domain: run-log
License: GPL2
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Set defaul options on plugin activation
 * @since 1.0.0
 */
function oirl_set_default_options() {
	add_option( 'oirl-distance-unit', 'km' );
	add_option( 'oirl-pace-or-speed', 'pace' );
	add_option( 'oirl-display-pos', 'top' );
	
}
register_activation_hook( __FILE__, 'oirl_set_default_options' );

/**
 * Remove plugin options on uninstall
 * @since 1.0.0
 */
function oirl_remove_default_options() {
	delete_option( 'oirl-distance-unit', 'km' );
	delete_option( 'oirl-pace-or-speed', 'pace' );
	delete_option( 'oirl-display-pos', 'top' );
	
}
register_uninstall_hook( __FILE__, 'oirl_remove_default_options' );

/**
 * Load plugin textdomain on plugins_loaded action.
 * @since 1.0.0
 */
function oirl_init() {
	$plugin_dir = basename(dirname(__FILE__));
	load_plugin_textdomain( 'run-log', false, $plugin_dir );
}
add_action('plugins_loaded', 'oirl_init');
 
/**
 * Register Custom run_log Post Type
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
		'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
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
 * @since 1.0.0
 */
function oirl_plugin_menu() {
	//add_options_page( __( 'Run Log Options', 'run-log' ), __( 'Run Log', 'run-log' ), 'manage_options', 'oirl-options-menu', 'oirl_plugin_options' );
	add_submenu_page( 'edit.php?post_type=oi_run_log_post', __( 'Run Log Options', 'run-log' ), __( 'Run Log Options', 'run-log' ), 'manage_options', 'oirl-options-menu', 'oirl_plugin_options' );
}
// Register options page to menu using the admin_menu action hook
add_action( 'admin_menu', 'oirl_plugin_menu' );

/**
 * Plugin options page
 * @since 1.0.0
 */
function oirl_plugin_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	// flag to indicate if options were saved
	$options_saved = false;
	// check and update (if needed) distance unit
	$distance_unit = get_option( 'oirl-distance-unit' );
	if( isset($_POST['oirl-distance-unit']) && $_POST['oirl-distance-unit'] != $distance_unit && in_array($_POST['oirl-distance-unit'], array('mi', 'km')) ) {
		update_option( 'oirl-distance-unit', $_POST['oirl-distance-unit'] );
		$distance_unit = $_POST['oirl-distance-unit'];
		$options_saved = true;
	}
	
	// check and update (if needed) pace/speed
	$pace_or_speed = get_option( 'oirl-pace-or-speed' );
	if( isset($_POST['oirl-pace-or-speed']) && $_POST['oirl-pace-or-speed'] != $pace_or_speed && in_array($_POST['oirl-pace-or-speed'], array('pace', 'speed')) ) {
		update_option( 'oirl-pace-or-speed', $_POST['oirl-pace-or-speed'] );
		$pace_or_speed = $_POST['oirl-pace-or-speed'];
		$options_saved = true;
	}
	
	// check and update (if needed) data display position
	$display_position = get_option( 'oirl-display-pos' );
	if( isset($_POST['oirl-display-pos']) && $_POST['oirl-display-pos'] != $display_position && in_array($_POST['oirl-display-pos'], array('top', 'bottom')) ) {
		update_option( 'oirl-display-pos', $_POST['oirl-display-pos'] );
		$display_position = $_POST['oirl-display-pos'];
		$options_saved = true;
	}
	
	if($options_saved) {
		?>
<div class="updated"><p><strong><?=esc_html__( 'Options saved', 'run-log' )?></strong></p></div>
		<?php
	}

	?>
<div class="wrap">
	<h3><?=esc_html__( 'Run Log Options', 'run-log' )?></h3>
	<p><?=esc_html__( 'Control the Run Log settings by updating these values', 'run-log' )?>:<p>
	<form name="form1" method="post">
	<?=esc_html__( 'Distance unit', 'run-log' )?>:
	<input type="radio" name="oirl-distance-unit" value="km" id="oirl-distance-unit-km" <?=($distance_unit == 'km' ? 'checked' : '')?>>
	<label for="oirl-distance-unit-km"><?=esc_html__( 'km', 'run-log' )?></label>
	&nbsp;
	<input type="radio" name="oirl-distance-unit" value="mi" id="oirl-distance-unit-mi" <?=($distance_unit == 'mi' ? 'checked' : '')?>>
	<label for="oirl-distance-unit-mi"><?=esc_html__( 'mi', 'run-log' )?></label>
	
	<br><br>
	
	<?=esc_html__( 'Pace/Speed display', 'run-log' )?>:
	<input type="radio" name="oirl-pace-or-speed" value="pace" id="oirl-pace-or-speed-Pace" <?=($pace_or_speed == 'pace' ? 'checked' : '')?>>
	<label for="oirl-pace-or-speed-Pace"><?=esc_html__( 'Pace', 'run-log' )?></label>
	&nbsp;
	<input type="radio" name="oirl-pace-or-speed" value="speed" id="oirl-pace-or-speed-Speed"<?=($pace_or_speed == 'speed' ? 'checked' : '')?>>
	<label for="oirl-pace-or-speed-Speed"><?=esc_html__( 'Speed', 'run-log' )?></label>
	
	<br><br>
	
	<?=esc_html__( 'Display position', 'run-log' )?>:
	<input type="radio" name="oirl-display-pos" value="top" id="oirl-display-pos-top" <?=($display_position == 'top' ? 'checked' : '')?>>
	<label for="oirl-display-pos-top"><?=esc_html__( 'top', 'run-log' )?></label>
	&nbsp;
	<input type="radio" name="oirl-display-pos" value="bottom" id="oirl-display-pos-bottom"<?=($display_position == 'bottom' ? 'checked' : '')?>>
	<label for="oirl-display-pos-bottom"><?=esc_html__( 'bottom', 'run-log' )?></label>
	
	<p class="submit">
	<input type="submit" name="Submit" class="button-primary" value="<?=esc_attr__('Save Changes')?>">
	</p>
	</form>
</div>
	<?php
}

/**
 * Register meta boxes for run-log posts
 * @since 1.0.0
 */
function oirl_register_run_log_meta_boxes() {
	add_meta_box( 'oi_run_log_meta_boxe', __( 'Run Log Parameters', 'run-log' ), 'oirl_run_log_meta_boxes_display', 'oi_run_log_post', 'normal', 'high');
	// remove the default meta boxe for custom fields
	remove_meta_box('postcustom', 'oi_run_log_post', 'normal');
}
add_action( 'add_meta_boxes', 'oirl_register_run_log_meta_boxes' );

/**
 * Run Log meta box display callback.
 * @since 1.0.0
 *
 * @param WP_Post $post the post object these custom fields should be added to.
 *
 * @return string HTML output of meta_boxes display for custom fields.
 */
function oirl_run_log_meta_boxes_display( $post ) {
	// Add an nonce field so we can check for it later.
	wp_nonce_field(basename(__FILE__), "run-log-meta-box-nonce");
	
	$distance_unit = get_option( 'oirl-distance-unit' );
	$distance = get_post_meta($post->ID, "oirl-mb-distance", true);
	if( preg_match( '/^(\d+(?:\.\d{1,3})?)\d*$/', $distance, $distance_matches ) ) {
		$distance = $distance_matches[1];
	} else { // no valid distance - display zero
		$distance = 0;
	}
	if($distance_unit == 'mi') {
		$distance = iorl_distance_converter( $distance, 'K2M' );
	}
	?>
	<div id="run-log-meta-box">
		<label for="oirl-mb-distance"><?=esc_html__( 'Distance', 'run-log' )?> (<?=esc_html__( $distance_unit, 'run-log' )?>):</label>
		<input name="oirl-mb-distance" type="number" step="0.001" min="0" size="3" maxlength="6" value="<?=$distance?>">
		&nbsp;
		<label for="oirl-mb-duration"><?=esc_html__( 'Duration', 'run-log' )?>:</label>
		<input name="oirl-mb-duration" type="text" size="8" maxlength="8" pattern="([0-9]{1,2}:)?[0-5]?[0-9]:[0-5]?[0-9]" placeholder="00:00:00" value="<?=get_post_meta($post->ID, "oirl-mb-duration", true)?>">
		<br>
		<label for="oirl-mb-elevation"><?=esc_html__( 'Elevation gain', 'run-log' )?>:</label>
		<input name="oirl-mb-elevation" type="number" size="5" maxlength="6" value="<?=get_post_meta($post->ID, "oirl-mb-elevation", true)?>">
		&nbsp;
		<label for="oirl-mb-calories"><?=esc_html__( 'Calories', 'run-log' )?>:</label>
		<input name="oirl-mb-calories" type="number" size="4" maxlength="5" value="<?=get_post_meta($post->ID, "oirl-mb-calories", true)?>">
	</div>
	<?
}

/**
 * Saving the meta-box data
 * @since 1.0.0
 *
 * @param int $post_id
 * @param WP_Post $post
 */
function oirl_save_run_log_meta_boxes( $post_id, $post ) {
	// Check nonce for anti-CSRF
	if (!isset($_POST["run-log-meta-box-nonce"]) || !wp_verify_nonce($_POST["run-log-meta-box-nonce"], basename(__FILE__)))
        return $post_id;
	// Check user permisions 
	if(!current_user_can("edit_post", $post_id))
        return $post_id;
	// If this is an autosave, form hasn't been submitted - don't do anything.
	if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
        return $post_id;
	// Validate this is a Run Log post
	if($post->post_type != 'oi_run_log_post')
		return $post_id;
	
	if(isset($_POST["oirl-mb-distance"]) && is_numeric($_POST["oirl-mb-distance"])) {
		$distance = floatval($_POST["oirl-mb-distance"]);
		// if distance unit is mi, convert to km befor saving
		$distance_unit = get_option( 'oirl-distance-unit' );
		if($distance_unit == 'mi') {
			$distance = iorl_distance_converter( $distance, 'M2K' );
		}
		update_post_meta($post_id, 'oirl-mb-distance', $distance);
	}
	if(isset($_POST["oirl-mb-duration"]) && preg_match("/^\s*(?:(\d{1,2}):)?([0-5]?\d):([0-5]?\d)\s*$/", $_POST["oirl-mb-duration"], $duration_matches)) {
		$duration = sprintf('%02d:%02d:%02d', (is_numeric($duration_matches[1]) ? $duration_matches[1] : 0), $duration_matches[2], $duration_matches[3]);
		update_post_meta($post_id, 'oirl-mb-duration', $duration);
	}
	if(isset($_POST["oirl-mb-elevation"]) && is_numeric($_POST["oirl-mb-elevation"])) {
		$elevation = floatval($_POST["oirl-mb-elevation"]);
		update_post_meta($post_id, 'oirl-mb-elevation', $elevation);
	}
	if(isset($_POST["oirl-mb-calories"]) && is_numeric($_POST["oirl-mb-calories"])) {
		$calories = intval($_POST["oirl-mb-calories"]);
		update_post_meta($post_id, 'oirl-mb-calories', $calories);
	}
}
// register this function to save_post action with 2 args
add_action( 'save_post', 'oirl_save_run_log_meta_boxes', 10, 2 );


/**
 * Add the run log data to the post.
 * @since 1.0.0
 *
 * @param string $content the content of post's body
 *
 * @return string the content with the HTML output of the run log data.
 */
function oirl_add_run_log_data_to_post( $content ) {
	// return original content if not run log custom post type
	if( ! is_singular('oi_run_log_post') ) {
		return $content;
	}
	$distance_unit = get_option( 'oirl-distance-unit' );
	$pace_or_speed = get_option( 'oirl-pace-or-speed' );
	$add_at_pos = get_option( 'oirl-display-pos' );
	$distance = get_post_meta($GLOBALS['post']->ID, "oirl-mb-distance", true);
	$distance = ($distance_unit == 'mi' ? iorl_distance_converter( $distance, 'K2M' ) : $distance);
	$duration = get_post_meta($GLOBALS['post']->ID, "oirl-mb-duration", true);
	$pace = iorl_calculate_pace($distance, $duration, $pace_or_speed);
	
	// HTML output
	$run_log_data = '<div class="oirl-data-box">';
	
	// Distance
	$run_log_data .= '<div class="oirl-data">';
	$run_log_data .= '<span class="oirl-data-desc">' . esc_html__( 'Distance', 'run-log' ) .'</span>';
	$run_log_data .= "<span class='oirl-data-value'>$distance</span> " . esc_html__( $distance_unit, 'run-log' );
	$run_log_data .= "</div>\n";
	
	// Duration
	$run_log_data .= '<div class="oirl-data">';
	$run_log_data .= '<span class="oirl-data-desc">' . esc_html__( 'Duration', 'run-log' ) ."</span> <span class='oirl-data-value'>$duration" . '</span>';
	$run_log_data .= "</div>\n";
	
	// Pace/Speed
	$run_log_data .= '<div class="oirl-data">';
	$run_log_data .= '<span class="oirl-data-desc">';
	if($pace_or_speed == 'speed') {
		$run_log_data .= esc_html__( 'Speed', 'run-log' ) . '</span>';
		$run_log_data .= "<span class='oirl-data-value'>$pace</span> ";
		if($distance_unit == 'mi') {
			$run_log_data .=  esc_html__( 'mi/h', 'run-log' );
		} else {
			$run_log_data .=  esc_html__( 'km/h', 'run-log' );
		}
	} else {
		$run_log_data .= esc_html__( 'Pace', 'run-log' ). '</span>';
		$run_log_data .= "<span class='oirl-data-value'>$pace</span> ";
		if($distance_unit == 'mi') {
			$run_log_data .=  esc_html__( 'min/mi', 'run-log' );
		} else {
			$run_log_data .=  esc_html__( 'min/km', 'run-log' );
		}
	}
	$run_log_data .= "</div>\n";
	$run_log_data .= '</div>';
	
	return ($add_at_pos == 'bottom' ? $content . $run_log_data : $run_log_data . $content );
}
add_filter( 'the_content', 'oirl_add_run_log_data_to_post' );

/**
 * Load the proper CSS file (LTR or RTL).
 * @since 1.0.0
 */
function iorl_enqueue_css() {
	$css_file_name = 'run-log' . (is_rtl() ? '-rtl' : '') . '.css';
	wp_enqueue_style( 'wpdocsPluginStylesheet', plugins_url( $css_file_name, __FILE__ ), null, '1.0.0' );
}
add_action( 'wp_enqueue_scripts', 'iorl_enqueue_css' );

/**
 * Make this custom content type available for search/archive pages
 * @since 1.0.0
 *
 * @param WP_Query $query the query object.
 *
 * @return WP_Query the query object after adding run-log custom post_type.
 */
function iorl_run_log_update_get_posts( $query ) {
	if ( ! isset($query->query_vars['suppress_filters']) || false == $query->query_vars['suppress_filters'] ) {
		$this_qry_post_types = $query->get( 'post_type' );
		if( empty( $this_qry_post_types ) ) $this_qry_post_types = 'Post';
		$new_qry_post_types = array_merge( (array)$this_qry_post_types, array('oi_run_log_post') );
		$query->set( 'post_type', $new_qry_post_types );
	}
	return $query;
}
// Hook into the 'pre_get_posts' filter
add_filter( 'pre_get_posts', 'iorl_run_log_update_get_posts' );

/**
 * Register goal Custom Taxonomy.
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
// Hook into the 'init' action
add_action( 'init', 'iorl_register_goal_taxonomy', 0 );


/**
 * Register Gear Custom Taxonomy.
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
// Hook into the 'init' action
add_action( 'init', 'iorl_register_gear_taxonomy', 0 );


/**
 * Calculate pace/speed acording to given distance and duration.
 *
 * @since 1.0.0
 *
 * @param float $distance the distance you've run (should be bigger than 0).
 * @param string $duration the activety duration in hh:mm:ss format.
 * @param string $type Optional. Output type. Accepts 'pace' or 'speed'. Default 'pace'.
 *
 * @return float the pace/speed. 0 if invalid param given.
 */
function iorl_calculate_pace( $distance, $duration, $type = 'pace' ) {
	$distance = floatval( $distance );
	if( ! $distance || !preg_match( "/^(\d+):([0-5]\d):([0-5]\d)$/", $duration, $duration_matches ) || ! in_array($type, array('pace', 'speed')) ) {
		return 0;
	}
	
	if( $type == 'pace' ) {
		$duration_minutes = (intval( $duration_matches[3]) / 60 )
			+ intval( $duration_matches[2] )
			+ (intval( $duration_matches[1]) * 60 );
		$pace_raw = $duration_minutes / $distance;
		$output = sprintf( '%d:%02d', floor($pace_raw), ($pace_raw - floor($pace_raw)) * 60 );
	} else {
		$duration_hours = ( intval($duration_matches[3]) / (60 * 60) )
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
 * @param float $distance the distance to convert (should be bigger than 0).
 * @param string $conversion Optional. Accepts 'M2K' form Miles to Kilometers; 'K2M' from Kilometers to Miles. Default 'M2K'.
 *
 * @return float the conversion outcome. 0 if invalid param given.
 */
function iorl_distance_converter( $distance, $conversion = 'M2K' ) {
	if( !is_numeric($distance) || floatval($distance) <= 0 || !in_array($conversion, array('K2M', 'M2K')) ) {
		return 0;
	}
	
	if( $conversion == 'M2K' ) {
		return sprintf( '%.2f', floatval($distance) / 0.62137 );
	} else {
		return sprintf( '%.2f', floatval($distance) * 0.62137 );
	}
}

?>