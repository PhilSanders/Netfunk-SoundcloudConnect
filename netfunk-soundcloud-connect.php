<?php
/* 
Plugin Name: Netfunk Soundcloud Connect 
Description: NetfunkDesign Soundcloud connection plugin provides widgets and short codes to display a users Sounds, Follewers, Playlists, Groups and more. Bringing any soundcloud profile to your website. 
Version: 1.0 
Date: 02/24/14 
Author: NetfunkDesign 
Author URI: http://www.netfunkdesign.com
Plugin URI: http://www.netfunkdesign.com 
*/

define('PLUGIN_DIR', plugins_url( '', __FILE__ ));
define('CLIEND_ID', '0689cc71488c38ff06cfdf44c58ad246'); 	// 6ad4755963f3868e450d98da516942b7
define('SECRETE_KEY', '285896339e2c777c0a40b75a23fa8796');	// 8414f1ffd99affd1ccd0dac37492dc4d
define('CALLBACK_URI', '/?action=soundcloud-auth'); 		// /?action=soundcloud-auth

require ( ABSPATH . WPINC . '/pluggable.php' );
require ( dirname(__FILE__).'/includes/functions.php' );
require ( dirname(__FILE__).'/includes/actions.php' );

/* register plugin css  */
function netfunk_soundcloud_connect_css() {
	wp_register_style('netfunk-soundcloud-connect-css', PLUGIN_DIR .'/css/style.css');
	wp_enqueue_style('netfunk-soundcloud-connect-css');
}
add_action('wp_enqueue_scripts', 'netfunk_soundcloud_connect_css');

/* initialize soundcloud PHP wrapper */
require ( dirname(__FILE__).'/includes/soundcloud.php' );
$SouncloudInit = new soundcloud_init();
$soundcloud = $SouncloudInit->soundcloud_connect();

/* soundcloud connection buttons and menu hooks */
add_action('soundcloud_authorize', array($SouncloudInit, 'soundcloud_connection'));
add_action('soundcloud_auth_link', array($SouncloudInit, 'soundcloud_auth_link'));
add_action('soundcloud_auth_link_mini', array($SouncloudInit, 'soundcloud_auth_link_mini'));
add_action('soundcloud_disconnect_link', array($SouncloudInit, 'soundcloud_disconnect_link'));
add_action('soundcloud_disconnect_link_mini', array($SouncloudInit, 'soundcloud_disconnect_link_mini'));
add_action('soundcloud_widget_menu', array($SouncloudInit, 'soundcloud_widget_menu'));

/* action page hooks  */ 
// custom, on-the-fly settings pages by netfunk
add_action_page('soundcloud', 'soundcloud', array($SouncloudInit, 'netfunk_soundcloud_settings_page'),'Soundcloud Settings');
add_action_page('soundcloud-auth', 'soundcloud-auth', array($SouncloudInit, 'netfunk_soundcloud_auth_page'),'Soundcloud Settings');
add_action_page('soundcloud-tracks', 'soundcloud-tracks', array($SouncloudInit, 'netfunk_soundcloud_tracks_page'),'Soundcloud Settings');
add_action_page('soundcloud-followers', 'soundcloud-followers', array($SouncloudInit, 'netfunk_soundcloud_followers_page'),'Soundcloud Settings');
add_action_page('soundcloud-groups', 'soundcloud-groups', array($SouncloudInit, 'netfunk_soundcloud_groups_page'),'Soundcloud Settings');
add_action_page('soundcloud-playlists', 'soundcloud-playlists', array($SouncloudInit, 'netfunk_soundcloud_playlists_page'),'Soundcloud Settings');

/* action page handler  */ 
function add_action_page($action, $pageclass, $function, $action_page_title) {
	$request_action = (!empty($_REQUEST['action']) ? $_REQUEST['action'] : '');
	if ($request_action == $action) {
	  add_filter('body_class', 'action_page_slug',1,1);
	  add_filter('action_page_title', 'action_page_title',1,1);
	  add_filter('action_page_sidebar', 'action_page_sidebar',1,4);
	  add_filter('the_content',$function,1,1);
	  add_action('template_redirect','action_page_template');
	}
}

/* action page <body> class */
function action_page_slug($classes) {
	if (!empty($classes)){
		$new_classes = array();
		foreach ($classes as $class){
			// remove the 'home' body element class
			if ( $class != 'home' )
			$new_classes[] = $class;
		}
		$new_classes[] = 'action-page';
		return $new_classes;
	}
}

/* action page content */
function action_page_content($content) {
	return $content;
}

/* action page <h1> title class */
function action_page_title() {
	global $action_page_title;
	echo $action_page_title;
}

function action_page_template() {
	get_header(); 
?>
	<div id="container" class="row">
	<div class="content row">
		<div class="large-12 small-12 columns">
			<br />
			<div class="large-6 small-12 columns left">
				<h1><?php do_action('action_page_title'); ?></h1>
			</div>
			<br class="clear" />
			<div class="large-9 columns">
				<?php 
					// place holder for additional action page footer information via 
					do_action('action_page_header'); 
				?>
				<div class="entry-content">
					<?php 
						if ( has_post_thumbnail() ) {
							//the_post_thumbnail();
						}
					?>
					<?php the_content(); ?>
					<br class="clear" />
				</div>
				<?php 
					// place holder for additional action page footer information via 
					do_action('action_page_footer'); 
				?>
			</div>
			<?php // place holder for action page sidebar content   
				do_action('action_page_sidebar'); 
			?>
		</div>
	</div><!--content-->
	</div><!--container-->
	<?php 
	get_footer(); 
	exit;
}
	
/* action page sidebar hook */
function action_page_sidebar() {
	global $plugin_widget_sidebar;
?>		
    <div class="large-3 small-12 columns right">
        <div id="sidebar" class="widget-area theme-action-sidebar">
            <ul class="sid">
                <?php dynamic_sidebar('action-widget-area'); ?> 
            </ul>
        </div>
    </div>
<?php
}

/* Soundcloud Connect Settings Page  */
function netfunk_soundcloud_options_fields ( ) { 
	global $current_user, $wp_roles;
	get_currentuserinfo();
?>
<br />
<hr style=" border: solid #DDD 1px;"/>
<div class="panel radius">
<h2>Soundcloud Connect (Netfunk)</h2>
<br />
<?php

	if (!get_user_meta($current_user->ID,'soundcloud_token')){
		echo '<h3>Connect to your Soundcloud account</h3>';
		do_action('soundcloud_auth_link');
	} else {
		echo '<h3>You are currently connected</h3>';
		do_action('soundcloud_disconnect_link');
	}
	
?>
<!--table class="form-table">
    <tr>
        <th><label for="netfunk_soundcloud_about">About You</label></th>
        <td>
            <textarea name="netfunk_soundcloud_about" id="netfunk_soundcloud_about" cols="30" rows="5"/><?php //echo esc_attr( get_the_author_meta( 'netfunk_soundcloud_about', $user->ID ) ); ?></textarea><br />
            <span class="description">Your personal information. Keep it short and simple.</span>
        </td>
    </tr>
</table-->
<br />
</div>
<?php }
add_action( 'show_user_profile', 'netfunk_soundcloud_options_fields' );
add_action( 'edit_user_profile', 'netfunk_soundcloud_options_fields' );

/* add soundclud settings options to the admin/profile page */
function netfunk_soundcloud_options_fields_update( $user_id ) {
	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;
	update_usermeta( $user_id, 'netfunk_soundcloud_about', $_POST['netfunk_soundcloud_about'] );
}
//add_action( 'personal_options_update', 'netfunk_soundcloud_options_fields_update' );
//add_action( 'edit_user_profile_update', 'netfunk_soundcloud_options_fields_update' );


/* create soundcloud settins options */
function netfunk_soundcloud_options_validate() {  

	$request_action = (!empty($_REQUEST['action']) ? $_REQUEST['action'] : '');

	/* Get user info. */
	global $current_user, $wp_roles;
	get_currentuserinfo();
	
	if (isset($_POST['action'])){
		
		// delete token / close connection to api
		if ($_POST['action'] == "delete_token" or $_REQUEST['action'] == "delete_token"){
		delete_user_meta($current_user->ID, "soundcloud_play_first");
		delete_user_meta($current_user->ID, "soundcloud_show_artwork");
		delete_user_meta($current_user->ID, "soundcloud_show_comments");
		delete_user_meta($current_user->ID, "soundcloud_token");
		delete_user_meta($current_user->ID, "soundcloud_refresh");
		header ("location: /edit-member"); }
		
		// save settings
		if ($_POST['action'] == "save_soundcloud_meta"){
		update_user_meta($current_user->ID, "soundcloud_default_image", $_POST['soundcloud_default_image']);
		update_user_meta($current_user->ID, "soundcloud_show_sounds", $_POST['soundcloud_show_sounds']);
		update_user_meta($current_user->ID, "soundcloud_show_followers", $_POST['soundcloud_show_followers']);
		update_user_meta($current_user->ID, "soundcloud_show_playlists", $_POST['soundcloud_show_playlists']);
		update_user_meta($current_user->ID, "soundcloud_show_groups", $_POST['soundcloud_show_groups']);
		update_user_meta($current_user->ID, "soundcloud_html5", $_POST['soundcloud_html5']);
		update_user_meta($current_user->ID, "soundcloud_play_first", $_POST['soundcloud_play_first']);
		update_user_meta($current_user->ID, "soundcloud_show_artwork", $_POST['soundcloud_show_artwork']);
		update_user_meta($current_user->ID, "soundcloud_show_comments", $_POST['soundcloud_show_comments']);
		$sc_update_settings = true;  }
	}
}
add_action( 'init', 'netfunk_soundcloud_options_validate' );

/* Sound Cloud Group Content Widget */
class Netfunk_Soundcloud_Group_Content_Widget extends WP_Widget {

	function Netfunk_Soundcloud_Group_Content_Widget(){
		$widget_ops = array('classname' => 'widget_soundcloud_panel', 
		'description' => __( "Netfunk Soundcloud Connect content widget. Normally used with NetfunkDesign themes. Use with caution otherwise.") );
		$this->WP_Widget('widget_soundcloud_panel', __('Soundcloud Group (by Netfunk)'), $widget_ops);
	}
	
	// get the group contributors
	function netfunk_soundcloud_home_contributors($group_id) {
		global $current_user, $soundcloud;
		if (class_exists('soundcloud_init')){
		if (!empty($group_id)){
		$my_groups = json_decode($soundcloud->get('groups/'.$group_id.'/contributors'), true);
		if (!empty($my_groups)){
		$n = 1;
		$limit = 30; 
		foreach ($my_groups as &$value) {
		if ($n <= $limit) {
		echo '<a href="' . $value['permalink_url'] . '" title="' . $value['username'] . '" target="_blank">'
		.'<img src="'. $value['avatar_url'] . '" alt="' . $value['username'] . '" border="0" width="54" height="54" /></a>';
		$n ++; } }
		} else {
			echo "<p>No contributors.</p>";}
		} else {
			echo "Soundcloud API Error: Group Empty!";}
		} else {
			echo "Netfunk Theme Soundcloud Plugin Required!";
		}
	}
	
	function widget( $args, $instance ) {
	extract($args);
	$custom_title = $instance[ 'custom_title' ];
	$soundcloud_id = $instance[ 'soundcloud_id' ];
	$soundcloud_name = $instance[ 'soundcloud_name' ];
	$soundcloud_desc = $instance[ 'soundcloud_desc' ];
	$soundcloud_dropbox = $instance[ 'soundcloud_dropbox' ];

?>

    <div class="row" style="background: #F76700; ">
    
    <div class="small-12 columns">
      <br />
      <a href="http://soundcloud.com/groups/<?php echo $soundcloud_name ?>" class="button secondary tiny round right" style="margin-top: 10px;">More Soundcloud</a>
      <div class="featured soundcloud_panel_widget">
        <h2 class="page-title"><?php echo $custom_title ?></h2>
      </div>
      <br />
    </div>

    <div class="large-8 columns left" style="margin-bottom: 20px;">
      <iframe style="width: 100%; height: 540px;" scrolling="no" frameborder="no" src="http://w.soundcloud.com/player/?url=http%3A%2F%2Fapi.soundcloud.com%2Fgroups%2F<?php echo $soundcloud_id ?>&amp;auto_play=false&amp;show_artwork=true&amp;color=4a5257"></iframe>
    </div>

    <div class="large-4 columns right">
      <div class="panel radius">
      <h4>Send us your tunes</h4>
      <?php echo $soundcloud_desc  ?>
      <br />
      <?php if ($soundcloud_dropbox != 0): ?>
        <br />
        <a class="soundcloud-dropbox" style="display: block; background: transparent url('http://a1.sndcdn.com/images/dropbox_small_white.png?d0e45d5') top left no-repeat; color: #888888; font-size: 10px; height: 50px; padding: 26px 60px 0 12px; width: 190px; text-decoration: none; font-family: 'CalibriRegular', 'TahomaNormal', 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.3em" target="_blank" href="http://soundcloud.com/groups/<?php echo $soundcloud_name ?>/dropbox">Send us your sounds</a>
      <?php endif; ?>
      <br class="clear" />
    
      <div class="small-6 left">
        <h4>Contributors</h4>
	  </div>
	  <div class="small-6 right text-right" style="padding-top: 10px;">
	    <a href="<?php echo 'http://soundcloud.com/groups/'.$soundcloud_name.'/contributors'; ?>" target="_blank">view all</a>
	  </div>
	  <br class="clear" />
	  <div class="small-12">
	    <?php $this->netfunk_soundcloud_home_contributors($soundcloud_id); ?>
	  </div>

	  </div>
    </div>
	<br class="clear" />

  </div>

<?php } 
	
	public function form( $instance ) {
	// outputs the options form on admin
	if ( isset( $instance[ 'custom_title' ] ) ) {
		$title = $instance[ 'custom_title' ];
	} else {
		$title = __( '', 'text_domain' ); }
	if ( isset( $instance[ 'soundcloud_id' ] ) ) {
		$soundcloud_id = $instance[ 'soundcloud_id' ];
	} else {
		$soundcloud_id = __( '', 'text_domain' ); }
	if ( isset( $instance[ 'soundcloud_name' ] ) ) {
		$soundcloud_name = $instance[ 'soundcloud_name' ];
	} else {
		$soundcloud_name = __( '', 'text_domain' ); }
	if ( isset( $instance[ 'soundcloud_desc' ] ) ) {
		$soundcloud_desc = $instance[ 'soundcloud_desc' ];
	} else {
		$soundcloud_desc = __( ''); }
	if ( isset( $instance[ 'soundcloud_dropbox' ] ) ) {
		$soundcloud_dropbox = $instance[ 'soundcloud_dropbox' ];
	} else {
		$soundcloud_dropbox = __( '0', 'text_domain' ); }
?>
	<p>
	<label for="<?php echo $this->get_field_id( 'custom_title' ); ?>"><?php _e( 'Widget Title:' ); ?></label> 
	<input class="widefat" id="<?php echo $this->get_field_id( 'custom_title' ); ?>" name="<?php echo $this->get_field_name( 'custom_title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
	</p>
	
	<p>
	<label for="<?php echo $this->get_field_id( 'soundcloud_id' ); ?>"><?php _e( 'Soundcloud Group ID:' ); ?></label> 
	<input class="widefat" id="<?php echo $this->get_field_id( 'soundcloud_id' ); ?>" name="<?php echo $this->get_field_name( 'soundcloud_id' ); ?>" type="text" value="<?php echo esc_attr( $soundcloud_id ); ?>" />
	</p>
	
	<p>
	<label for="<?php echo $this->get_field_id( 'soundcloud_name' ); ?>"><?php _e( 'Soundcloud Group Name:' ); ?></label> 
	<input class="widefat" id="<?php echo $this->get_field_id( 'soundcloud_name' ); ?>" name="<?php echo $this->get_field_name( 'soundcloud_name' ); ?>" type="text" value="<?php echo esc_attr( $soundcloud_name ); ?>" />
	</p>
	
	<p>
	<label for="<?php echo $this->get_field_id( 'soundcloud_desc' ); ?>"><?php _e( 'Soundcloud Group Description:' ); ?></label> 
	<textarea class="widefat" id="<?php echo $this->get_field_id( 'soundcloud_desc' ); ?>" name="<?php echo $this->get_field_name( 'soundcloud_desc' ); ?>"><?php echo esc_attr( $soundcloud_desc ); ?></textarea>
	</p>
	
	<p>
	<label for="<?php echo $this->get_field_id( 'soundcloud_dropbox' ); ?>"><?php _e( 'Show Your Dropbox Link:' ); ?></label> 
	<input class="widefat" id="<?php echo $this->get_field_id( 'soundcloud_dropbox' ); ?>" name="<?php echo $this->get_field_name( 'soundcloud_dropbox' ); ?>" type="text" value="<?php echo esc_attr( $soundcloud_dropbox ); ?>" />
	</p>
<?php 
	}

	public function update( $new_instance, $old_instance ) {
	// processes widget options to be saved
	$instance = array();
	$instance['soundcloud_id'] = ( ! empty( $new_instance['soundcloud_id'] ) ) ? strip_tags( $new_instance['soundcloud_id'] ) : '';
	$instance['custom_title'] = ( ! empty( $new_instance['custom_title'] ) ) ? strip_tags( $new_instance['custom_title'] ) : '';
	$instance['soundcloud_name'] = ( ! empty( $new_instance['soundcloud_name'] ) ) ? strip_tags( $new_instance['soundcloud_name'] ) : '';
	$instance['soundcloud_desc'] = ( ! empty( $new_instance['soundcloud_desc'] ) ) ? strip_tags( $new_instance['soundcloud_desc'] ) : '';
	$instance['soundcloud_dropbox'] = ( ! empty( $new_instance['soundcloud_dropbox'] ) ) ? strip_tags( $new_instance['soundcloud_dropbox'] ) : '0';
	return $instance;}
}
	
/* Sound Cloud Group Content Widget */
class Netfunk_Soundcloud_Menu_Widget extends WP_Widget {

	function Netfunk_Soundcloud_Menu_Widget(){
		$widget_ops = array('classname' => 'widget_soundcloud', 'description' => __( "This will display the options page menu for your Soundcloud account. Place in the 'action pages' sidebar.") );
		$this->WP_Widget('soundcloud', __('Soundcloud Options Menu (by Netfunk)'), $widget_ops);
	}
	
	function widget( $args, $instance ) { 
		global $current_user;
		extract($args);
		echo '<li id="soundcloud-connect-widget" class="widgetcontent soundcloud_menu_widget">';
		echo '<h5 class="widgettitle"><span class="webicon soundcloud small"></span>Soundcloud Integration</h5>';
		if (class_exists('soundcloud_init')) { 
			if (!get_user_meta($current_user->ID,'soundcloud_token')){
				do_action('soundcloud_auth_link');
			} else {
				do_action('soundcloud_widget_menu');
			}
		} else { 
			echo '<span class="error">Soundcloud Integration Plugin Required!</span>';
		}
		echo '</li>';
	}
}

/* register netfunktheme widgets (widgets.php) */
function netfunk_soundcloud_widgets() {
	register_widget('Netfunk_Soundcloud_Group_Content_Widget');
	register_widget('Netfunk_Soundcloud_Menu_Widget');
}
add_action('widgets_init', 'netfunk_soundcloud_widgets', 1);

//EOF