<?php

/**
 * Plugin Name: BuddyPress Activity ShortCode
 * Description: Embed activity stream in page/post using shortcode
 * Author: BuddyDev
 * Plugin URI: https://buddydev.com/plugins/bp-activity-shortcode/
 * Author URI: https://buddydev.com/
 * Version: 1.0.8
 * License: GPL
 */

// exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper class.
 */
class BD_Activity_Stream_Shortcodes_Helper {

	/**
	 * Singleton instance.
	 *
	 * @var BD_Activity_Stream_Shortcodes_Helper
	 */
	private static $instance;

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->register_shortcodes();
	}

	/**
	 * Register ShortCode
	 *
	 * @example [activity-stream display_comments=threaded|none title=somethimg per_page=something]
	 */
	private function register_shortcodes() {
		add_shortcode( 'activity-stream', array( $this, 'generate_activity_stream' ) );
	}

	/**
	 * Get Instance
	 *
	 * @return BD_Activity_Stream_Shortcodes_Helper
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Generate activity content.
	 *
	 * @param array  $atts shortcode atts.
	 * @param string $content content.
	 *
	 * @return string
	 */
	public function generate_activity_stream( $atts, $content = null ) {

		// Hide if BuddyPress is not active.
		if ( ! function_exists( 'buddypress' ) ) {
			return '';
		}

		// allow to use all those args awesome!
		$atts = shortcode_atts( array(
			'title'            => 'Latest Activity',// title of the section.
			'pagination'       => 1,// show or not.
			'load_more'        => 0,
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
			'use_compat'       => bp_use_theme_compat_with_current_theme(),
			'allow_posting'    => false,    // experimental, some of the themes may not support it.
			'container_class'  => 'activity',// default container,
			'hide_on_activity' => 1,// hide on user and group activity pages.
            'for'              => '', // 'logged','displayed','author',
		), $atts );

		// hide on user activity, activity directory and group activity.
		if ( $atts['hide_on_activity'] && ( function_exists( 'bp_is_activity_component' ) && bp_is_activity_component() ||
		       function_exists( 'bp_is_group_home' ) && bp_is_group_home() ) ) {
			return '';
		}

		$activity_for = $atts['for'];
		if ( ! empty( $activity_for ) ) {

			unset( $atts['for'] );
			$atts['user_id'] = $this->get_user_id_for_context( $activity_for );

			if ( empty( $atts['user_id'] ) ) {
				return '';
			}
		}

		// start buffering.
		ob_start();
		?>

		<?php if ( $atts['use_compat'] ) : ?>
			<div id="buddypress">
		<?php endif; ?>

			<?php if ( $atts['title'] ) : ?>
				<h3 class="activity-shortcode-title"><?php echo $atts['title']; ?></h3>
			<?php endif; ?>

			<?php do_action( 'bp_before_activity_loop' ); ?>

			<?php if ( $atts['allow_posting'] && is_user_logged_in() ) : ?>
				<?php bp_locate_template( array( 'activity/post-form.php' ), true ); ?>
			<?php endif; ?>

			<?php if ( bp_has_activities( $atts ) ) : ?>

				<div class="<?php echo esc_attr( $atts['container_class'] ); ?> <?php if ( ! $atts['display_comments'] ) : ?> hide-activity-comments<?php endif; ?> shortcode-activity-stream">

					<?php if ( empty( $_POST['page'] ) ) : ?>
						<ul id="activity-stream" class="activity-list item-list">
					<?php endif; ?>

							<?php while ( bp_activities() ) : bp_the_activity(); ?>
								<?php bp_get_template_part( 'activity/entry' ); ?>
							<?php endwhile; ?>

							<?php if ( $atts['load_more'] && bp_activity_has_more_items() ) : ?>
								<li class="load-more">
									<a href="<?php bp_activity_load_more_link() ?>"><?php _e( 'Load More', 'buddypress' ); ?></a>
								</li>
							<?php endif; ?>

					<?php if ( empty( $_POST['page'] ) ) : ?>
						</ul>
					<?php endif; ?>

					<?php if ( $atts['pagination'] && ! $atts['load_more'] ) : ?>
						<div class="pagination">
							<div class="pag-count"><?php bp_activity_pagination_count(); ?></div>
							<div class="pagination-links"><?php bp_activity_pagination_links(); ?></div>
						</div>
					<?php endif; ?>

				</div>

			<?php else : ?>
				<div id="message" class="info">
					<p><?php _e( 'Sorry, there was no activity found. Please try a different filter.', 'buddypress' ); ?></p>
				</div>
			<?php endif; ?>

			<?php do_action( 'bp_after_activity_loop' ); ?>

			<form action="" name="activity-loop-form" id="activity-loop-form" method="post">
				<?php wp_nonce_field( 'activity_filter', '_wpnonce_activity_filter' ); ?>
			</form>

		<?php if ( $atts['use_compat'] ) : ?>
			</div>
		<?php endif; ?>

		<?php

		$output = ob_get_clean();

		return $output;
	}

	/**
	 * Get the user id for the givn context.
	 *
	 * @param string $context 'logged', 'displayed', 'author'.
	 *
	 * @return string
	 */
	private function get_user_id_for_context( $context ) {


	    $user_id = false;
		switch ( $context ) {

			case 'logged':
				$user_id = bp_loggedin_user_id();
				break;

			case 'displayed':
				$user_id = bp_displayed_user_id();
				break;

			case 'author':
				if ( is_singular() || in_the_loop() ) {
					$user_id = get_the_author_meta( 'ID' );
				} elseif ( is_author() ) {
					$user_id = get_queried_object_id();
				}

				break;
		}

		return $user_id;
	}

}
BD_Activity_Stream_Shortcodes_Helper::get_instance();
