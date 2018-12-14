<?php
/*
  Plugin Name: WP Online Users
  Description: This plugin will show you the number of online users as well as the list with names.
  Version: 1.0.0
  Author: Baikare Sandeep
  Author URI:   
  Text Domain: wp-online-users
  Domain Path:       /languages  
  
 */

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'WP_Online_Users' ) ) :

	class WP_Online_Users{

		/**
	     * @var The single instance of the class
	     *
	     * @since 1.0
	     */
		protected static $_instance = null;
		
		/**
	     * Main WPOU Instance
	     *
	     * @since 1.0
	     * @static
	     * @see WPOU()
	     * @return Main instance
	     */
		public static function get_instance(){
			if (is_null(self::$_instance)) {
	                self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
	     * Constructor for the WP_Online_Users class
	     *
	     * Sets up all the appropriate hooks and actions
	     * within our plugin.
	     */
		function __construct(){		

			add_action( 'wp', array( $this, 'WPOU_user_online_update' ) );
			add_action( 'wp_dashboard_setup', array( $this, 'WPOU_add_dashboard_widgets' ) );

		}

		function  WPOU_user_online_update(){

			if ( is_user_logged_in()) {

				// get the user activity the list
				$logged_in_users = get_transient('wpou_online_status');

				// get current user ID
				$user = wp_get_current_user();

				// check if the current user needs to update his online status;
				// he does if he doesn't exist in the list
				$no_need_to_update = isset($logged_in_users[$user->ID])

				    // and if his "last activity" was less than let's say ...1 minutes ago
				    && $logged_in_users[$user->ID] >  (time() - (1 * 60));

				// update the list if needed
				if(!$no_need_to_update){
				  $logged_in_users[$user->ID] = time();
				  set_transient('wpou_online_status', $logged_in_users, (2*60)); // 2 mins
				}
			}
		}

		/**
		 * Add a widget to the dashboard.
		 *
		 * This function is hooked into the 'wp_dashboard_setup' action below.
		 */
		function WPOU_add_dashboard_widgets() {
			if( current_user_can( 'administrator' ) ){
				wp_add_dashboard_widget(
			                 'wpou_online_users_dashboard_widget',         // Widget slug.
			                 __( 'WP Online Users', 'wp-online-users' ),         // Title.
			                 array( $this, 'WPOU_display_logged_in_users' ) // Display function.
			        );
			}
		}

		/**
		 * Function to show the number of online users with list
		 * 
		 */
		function WPOU_display_logged_in_users(){
			// get the user activity the list
			$logged_in_users = get_transient('wpou_online_status');

			if ( !empty( $logged_in_users ) ) {
				?>
					<div class="login-users">
						<table class="">
							<tr>
								<td> <?php _e( 'Total Online Users: ', 'wp-online-users' ); ?> </td>
								<td> <strong> <?php echo count( $logged_in_users ); ?> </strong> </td>
							</tr>												
						</table>
					</div>	
							<?php
							
							echo "<p> <strong>" . __( 'Logged in users are as following :', 'wp-online-users' ) . "</strong> </p>";
							$count = 0;
							foreach ( $logged_in_users as $user_id => $value ) {
									$user = get_user_by( 'id', $user_id ); 
									if( $user ){										
										echo sprintf(
												__( '<p>Logged in User name is %s </p>', 'wp-online-users' ),
												'<a href="' . esc_url( admin_url( 'user-edit.php?user_id=' . $user_id ) ) . '">' . $user->display_name . '</a>'
											);
									}									
							}
							?>
							<?php
			} else{
				_e('No user is logged in.', 'wp-online-users');
			}

		}

	}

endif;

/**
 * Returns the main instance.
 *
 * @since  1.1
 * @return WP_Online_Users
 */
function WPOU(){
	WP_Online_Users::get_instance();
}


$wpou = WPOU();