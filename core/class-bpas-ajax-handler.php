<?php
/**
 * Handler class for plugin ajax request
 *
 * @package bp-activity-shortcode
 */

// Exit if access directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BPAS_Ajax_Handler
 */
class BPAS_Ajax_Handler {

	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->setup();
	}

	/**
	 * Setup Callbacks
	 */
	public function setup() {
		add_action( 'wp_ajax_bpas_load_activities', array( $this, 'load_activities' ) );
		add_action( 'wp_ajax_nopriv_bpas_load_activities', array( $this, 'load_activities' ) );
	}

	/**
	 * Load activities
	 */
	public function load_activities() {

		check_ajax_referer( 'bpas_load_activities' );

		unset( $_POST['_wpnonce'] );
		unset( $_POST['action'] );

		$args = wp_parse_args( $_POST, array(
			'display_comments' => 'threaded',
			'include'          => false,     // pass an activity_id or string of IDs comma-separated
			'exclude'          => false,     // pass an activity_id or string of IDs comma-separated
			'in'               => false,     // comma-separated list or array of activity IDs among which to search
			'sort'             => 'DESC',    // sort DESC or ASC
			'page'             => 1,         // which page to load
			'per_page'         => 5,         // how many per page.
			'max'              => false,     // max number to return.
			'count_total'      => true,

			// Scope - pre-built activity filters for a user (friends/groups/favorites/mentions).
			'scope'            => false,

			// Filtering
			'user_id'          => false,    // user_id to filter on
			'object'           => false,    // object to filter on e.g. groups, profile, status, friends
			'action'           => false,    // action to filter on e.g. activity_update, new_forum_post, profile_updated
			'primary_id'       => false,    // object ID to filter on e.g. a group_id or forum_id or blog_id etc.
			'secondary_id'     => false,    // secondary object ID to filter on e.g. a post_id.

			// Searching
			'search_terms'     => false,         // specify terms to search on.
			'hide_on_activity' => 1,// hide on user and group activity pages.
			'for'              => '', // 'logged','displayed','author'.
			'role'             => '', // use one or more role here(e.g administrator,editor etc).
		) );

		if ( ! empty( $_POST['bpas_action'] ) ) {
			$args['action'] = $_POST['bpas_action'];
		}

		if ( bp_has_activities( $args ) ) {

			ob_start();

		?>
			<?php while ( bp_activities() ) : bp_the_activity(); ?>
				<?php bp_get_template_part( 'activity/entry' ); ?>
			<?php endwhile; ?>
		<?php

			$content = ob_get_clean();

			wp_send_json_success( $content );
		} else {
			wp_send_json_error( array(
				'message' => __( 'Sorry, there was no activity found.', 'bp-activity-shortcode' ),
			) );
		}
	}
}

new BPAS_Ajax_Handler();
