<?php
/**
 * Plugin Name: BuddyPress Activity Shortcode
 * Author: Brajesh Singh(BuddyDev)
 * Plugin URI: http://buddydev.com/plugins/bp-activity-shortcode/
 * Author URI: http://buddydev.com/members/sbrajesh/
 * Version: 1.0.2
 * License: GPL
 */
class BD_Activity_Stream_Shortcodes_Helper{
    
    private static $instance;

    private function __construct() {
  
        //register shortcodes
        $this->register_shortcodes();
  
    }
   
    /**
     * Register  shortcodes
     * 
     */
    private function register_shortcodes() {
        //[activity-stream display_comments=threaded|none title=somethimg per_page=something]
        
        add_shortcode( 'activity-stream', array( $this, 'generate_activity_stream' ) );
     
               

    }
    /**
     * Get Instance
     * 
     * 
     * @return BD_Activity_Stream_Shortcodes_Helper
     */
    public static function get_instance() {

        if ( !isset( self::$instance ) )
            self::$instance = new self();

        return self::$instance;
    }
    
    

    public function generate_activity_stream( $atts, $content = null ) {
        //allow to use all those args awesome!
       $atts=shortcode_atts(array(
            'title'				=> 'Latest Activity',//title of the section
            'pagination'		=> 'true',//show or not
            'display_comments'	=> 'threaded',
            'include'			=> false,     // pass an activity_id or string of IDs comma-separated
            'exclude'			=> false,     // pass an activity_id or string of IDs comma-separated
            'in'				=> false,     // comma-separated list or array of activity IDs among which to search
            'sort'				=> 'DESC',    // sort DESC or ASC
            'page'				=> 1,         // which page to load
            'per_page'			=> 5,         //how many per page
            'max'				=> false,     // max number to return

            // Scope - pre-built activity filters for a user (friends/groups/favorites/mentions)
            'scope'				=> false,

            // Filtering
            'user_id'			=> false,    // user_id to filter on
            'object'			=> false,    // object to filter on e.g. groups, profile, status, friends
            'action'			=> false,    // action to filter on e.g. activity_update, new_forum_post, profile_updated
            'primary_id'		=> false,    // object ID to filter on e.g. a group_id or forum_id or blog_id etc.
            'secondary_id'		=> false,    // secondary object ID to filter on e.g. a post_id

            // Searching
            'search_terms'		=> false,         // specify terms to search on
            'use_compat'		=> bp_use_theme_compat_with_current_theme(),
		   'allow_posting'		=> false,	//experimental, some of the themes may not support it.
        ), $atts );
       
        extract( $atts );
	//hide on user activity, activity directory and group activity
      if( function_exists('bp_is_activity_component') && bp_is_activity_component() || function_exists('bp_is_group_home') && bp_is_group_home() )
		  return '';
        
        ob_start(); ?>
	
    <?php if( $use_compat):?>
        <div id="buddypress">
    <?php endif;?>		
	<?php if($title): ?>
            <h3 class="activity-shortcode-title"><?php echo $title; ?></h3>
        <?php endif;?>    
		
        <?php do_action( 'bp_before_activity_loop' ); ?>
			
		<?php if ( $allow_posting && is_user_logged_in() ) : ?>

			<?php bp_locate_template( array( 'activity/post-form.php'), true ); ?>

		<?php endif; ?>
			
        <?php if ( bp_has_activities($atts)  ) : ?>
            <div class="activity <?php if(!$display_comments): ?> hide-activity-comments<?php endif; ?> shortcode-activity-stream">

                 <?php if ( empty( $_POST['page'] ) ) : ?>

                    <ul id="activity-stream" class="activity-list item-list">

                 <?php endif; ?>

                 <?php while ( bp_activities() ) : bp_the_activity(); ?>

                    <?php bp_get_template_part( 'activity/entry'); ?>

                 <?php endwhile; ?>

                 <?php if ( empty( $_POST['page'] ) ) : ?>
                    </ul>
                 <?php endif; ?>
                
                <?php if($pagination):?>
                    <div class="pagination">
                        <div class="pag-count"><?php bp_activity_pagination_count(); ?></div>
                        <div class="pagination-links"><?php bp_activity_pagination_links(); ?></div>
                    </div>
                <?php endif;?>
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
     <?php if( $use_compat ):?>       
        </div>
     <?php endif;?>
    <?php 

	$output = ob_get_clean();
	
	
	return $output;

    }

}

BD_Activity_Stream_Shortcodes_Helper::get_instance();
