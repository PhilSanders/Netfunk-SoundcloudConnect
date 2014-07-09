<?php

/*  Soundcloud PHP wrapper  */

/* soundcloud resources list */

# name 							# description 							# example value
// id 							integer ID 								123
// permalink 					permalink of the resource 				"summer-of-69"
// username 					username 								"Doctor Wilson"
// uri 							API resource URL 						http://api.soundcloud.com/comments/32562
// permalink_url 				URL to the SoundCloud.com page 	"		http://soundcloud.com/bryan/summer-of-69"
// avatar_url 					URL to a JPEG image 					"http://i1.sndcdn.com/avatars-000011353294-n0axp1-large.jpg"
// country 						country 								"Germany"
// full_name 					first and last name 					"Tom Wilson"
// city 						city 									"Berlin"
// description 					description 							"Another brick in the wall"
// discogs-name 				Discogs name 							"myrandomband"
// myspace-name 				MySpace name 							"myrandomband"
// website 						a URL to the website 					"http://facebook.com/myrandomband"
// website-title 				a custom title for the website 			"myrandomband on Facebook"
// online 						online status (boolean) 				true
// track_count 					number of public tracks 				4
// playlist_count 				number of public playlists 				5
// followers_count 				number of followers 					54
// followings_count 			number of followed users 				75
// public_favorites_count 		number of favorited public tracks 		7
// avatar_data 					binary data of user avatar 				(only for uploading)
// plan 						subscription plan of the user 			Pro Plus
// private_tracks_count 		number of private tracks 				34
// private_playlists_count 		number of private playlists 			6
// primary_email_confirmed 		boolean if email is confirmed 			true

require_once ( 'Soundcloud/Soundcloud.php' );

if (!class_exists('soundcloud_init')) {

	class soundcloud_init {
	
		function soundcloud_connect(){
			$soundcloud = new Services_Soundcloud(CLIEND_ID, SECRETE_KEY, site_url(CALLBACK_URI));
			$soundcloud->setDevelopment(false);
			return $soundcloud; 
		}
	
		// Netfunk-soundcloud Buttons
	
		function soundcloud_auth_link(){  
			global $soundcloud;
			$authorizeUrl = $soundcloud->getAuthorizeUrl();
			$bloginfo = get_bloginfo( 'name' );
			echo 'Connect your Soundcloud.com user profile with '.$bloginfo.'. Share and comments on your sounds right from your profile.';
			echo '<a href='.$authorizeUrl
			.'&scope=non-expiring&display=popup" style="padding: 0px; margin: 0px 0px 0px -5px; border: 0px;"><img src="' 
			. PLUGIN_DIR.'/images/btn-connect-sc-s.png" class="soundcloud_connect"/></a>'; }
	
		function soundcloud_auth_link_mini(){  
			global $soundcloud;
			$authorizeUrl = $soundcloud->getAuthorizeUrl();
			echo '<a href="'
			.$authorizeUrl
			.'&scope=non-expiring&display=popup" style="float: right; padding: 0px; margin: 0px 20px 0px 0px; border: 0px;"><img src="' 
			. PLUGIN_DIR.'/images/btn-connect-s.png" class="soundcloud_connect"/></a>'; }
	
		function soundcloud_disconnect_link(){ 
			echo '<form name="soundcloud_disconnect_form" id="soundcloud_disconnect_form" action="" method="post">';
			echo '<input name="soundcloud_disconnect" type="submit" id="soundcloud_disconnect" class="soundcloud_connect" value="" style="background: url(' 
			. PLUGIN_DIR .'/images/btn-disconnect-l.png) no-repeat top left; margin: -10px 0px 0px 20px; width: 140px; height: 29px; border: none;">';
			echo '<input type=hidden name=action value=delete_token>';
			echo '</form>'; }
	
		function soundcloud_disconnect_link_mini(){ 
			echo '<a href="'
			.site_url('/?action=soundcloud')
			.'&action=delete_token" class="soundcloud_connect_mini" style="background: url('
			. PLUGIN_DIR.'/images/btn-disconnect-s.png) no-repeat top left; float: right; margin: 0px 20px 0px 20px; width: 109px; height: 21px; border: none;"></a>';
			echo '<label style="float: right;">[ <a href="'
			.site_url('/?action=soundcloud').'">settings</a> ]</label>'; }

		// netfunk-soundcloud function page sidebar widgets array

		function soundcloud_widget_menu(){ 
				
			global $current_user, $soundcloud;
			get_currentuserinfo();
			
			if (get_user_meta($current_user->ID,'soundcloud_token')){
				$soundcloud_menu = '<ul>';
				$soundcloud_menu .= '<li><a href="'.site_url('/?action=soundcloud-tracks').'">My Tracks</a></li>';
				$soundcloud_menu .= '<li><a href="'.site_url('/?action=soundcloud-playlists').'">My Playlists</a></li>';
				$soundcloud_menu .= '<li><a href="'.site_url('/?action=soundcloud-followers').'">My Followers</a></li>';
				$soundcloud_menu .= '<li><a href="'.site_url('/?action=soundcloud-groups').'">My Groups</a></li>';
				$soundcloud_menu .= '<li><a href="'.site_url('/?action=soundcloud').'">Settings</a></li>';
				$soundcloud_menu .= '</ul>';
				
				$plugin_widget_sidebar = array(
				'Soundcloud-Settings-Menu' => array(
				'widget_id' => 'soundcloud-connect-widget',
				'widget_class' => 'soundcloud_menu_widget',
				'widget_title' => '<span class="webicon soundcloud small"></span>Soundcloud Intigration',
				'widget_content' => $soundcloud_menu ));
				
				echo $soundcloud_menu;
				
			} else {
				$authorizeUrl = $soundcloud->getAuthorizeUrl();
				$bloginfo = get_bloginfo( 'name' );
				echo "Connect your Soundcloud.com user profile with ".$bloginfo.". Share and comments on your sounds right from your profile.";
				echo "<a href=\"".$authorizeUrl."&scope=non-expiring&display=popup\" style=\"padding: 0px; margin: 0px 0px 0px -5px; border: 0px;\"><img src=\"" 
				. get_template_directory_uri() . "/plugins/soundcloud-api/soundcloud-api/images/btn-connect-sc-s.png\" class=\"soundcloud_connect\" border=\"0\"/></a>";
			
			}
		
		}
		

		// Member Pages
		
		/* netfunk-soundcloud edit profile page */
		function netfunk_soundcloud_auth_page() {

			/* Get user info. */
			global $soundcloud, $current_user, $wp_roles;
			get_currentuserinfo();
			
			if (!is_user_logged_in()): 
			
				_e('<p class="warning">You must be logged in to be here!</p><p><a href="/forum/ucp.php?mode=login">login here</a></p>', 'frontendprofile');
			 
			 else: 
				
				if ( isset($error) and $error != "" ) 
					echo '<p class="error">' . $error . '</p>'; 
				
				// if no Soundcloud Token in User Meta
				if (isset($_GET['code']) and !get_user_meta($current_user->ID,'soundcloud_token')){
			
					try {
						$accessToken = $soundcloud->accessToken($_GET['code']);
						// Add Soundcloud Accesss Token to User Meta Data
						add_user_meta($current_user->ID, "soundcloud_token", (isset($accessToken['access_token']) ? $accessToken['access_token'] : ''), true);		// soundcloud api token
						add_user_meta($current_user->ID, "soundcloud_refresh", (isset($accessToken['refresh_token']) ? $accessToken['refresh_token'] : ''), true);	// soundcloud api refresh token (cached)
						// initial user settings setup
						add_user_meta($current_user->ID, "soundcloud_default_image", "", true);							// use soundcloud image as default
						add_user_meta($current_user->ID, "soundcloud_html5", "true", true);								// use new HTML5 player
						add_user_meta($current_user->ID, "soundcloud_play_first", "", true);							// play first song on page
						add_user_meta($current_user->ID, "soundcloud_show_artwork", "true", true);						// show art work in player (html5 only)
						add_user_meta($current_user->ID, "soundcloud_show_comments", "true", true);						// show comments in player 
						// initial audio track settings setup
						add_user_meta($current_user->ID, "soundcloud_my_filter", "", true);								// filter tracks to not be displayed (?)
						add_user_meta($current_user->ID, "soundcloud_my_playlists", "", true);							// user playlist names / ids
						add_user_meta($current_user->ID, "soundcloud_my_playlist_meta", "", true);						// user playlist meta data
					} catch (Services_Soundcloud_Invalid_Http_Response_Code_Exception $e) {
						exit($e->getMessage());
					}
				}
				
				$soundcloud->setAccessToken(get_user_meta($current_user->ID,'soundcloud_token',true));
				
				$me = json_decode($soundcloud->get('me'), true);
				$bloginfo = get_bloginfo( 'name' );
				//$my_music = json_decode($soundcloud->get('tracks',array('user_id' => $me['id'])), true);			/* not using this */
				echo '<h4>Authentication Successful</h4>';
				echo '<p><img src="'.get_template_directory_uri().'/plugins/soundcloud-api/soundcloud-api/images/soundcloud-icon.png" style="float: right; margin: -50px 50px 10px 10px;"></p>';
				echo '<p>You are now fully connected between your soundcloud.com account and ' . $bloginfo . '.</p> ';
				echo '<p>';
				echo '<strong>Account Name:</strong> ' . $me [ 'username' ] . '<br /><br />';
				//echo '<strong>Email Address:</strong> " . $me [ 'email' ] . "<br /><br />';
				echo '<strong>Soundcloud URL:</strong> <a href="' . $me [ 'permalink_url' ] . '" target="_blank">' . $me [ 'permalink_url' ] . '</a><br />';
				echo '</p>';
				echo '<hr />';
				echo '<h4>Soundcloud Integration Features & Settings:</h4> ';
				echo '<br />';
				echo '<div class="large-6 small-12 columns left">';
				echo '<div class="push-2">';
				echo $this->soundcloud_widget_menu();
				echo '</div>';
				echo '</div>';
				echo '<div class="large-6 small-12 columns right text-right">';
				echo '<div>Did you connect here by mistake?</div>';
				do_action('soundcloud_disconnect_link');
				echo "</div>";
				echo '<br class="clear" \>';
				echo'<hr />';
				echo '<div class="text-center"><strong><a href="'.site_url('/?action=edit-member').'">Return to your account settings</a></strong></div>';
			
			endif;
		
		}
		
		/* netfunktheme soundcloud settings page */

		function netfunk_soundcloud_settings_page() {
		
			/* Get user info. */
			global $soundcloud, $current_user, $wp_roles;
			get_currentuserinfo();
		
			if (!is_user_logged_in()): 
			
				_e('<p class="warning">You must be logged in to be here!</p><p><a href="/forum/ucp.php?mode=login">login here</a></p>', 'frontendprofile');
			 
			 else: 
				
				if ( isset($error) and $error != "" ) 
					echo '<p class="error">' . $error . '</p>'; 
		
				try {
				   $soundcloud->setAccessToken(get_user_meta($current_user->ID,'soundcloud_token',true));
				   $me = json_decode($soundcloud->get('me'), true);
		
					// show update notice
					echo ( isset( $sc_update_settings ) and $sc_update_settings == true ? '<div class="small-12 columns text-center"><div class="panel radius success">Settings Updated!</div></div><br class="clear"/><br />' : '');
					// soundcloud authentication icon
					echo '<div class="small-12 columns">';
					echo '<div class="large-6 small-12 columns left">';
					echo '<h5 class="entry-title">Soundcloud API Authorization:</h5>';
					do_action('soundcloud_disconnect_link');
					echo '</div>';
					echo '<div class="large-6 small-12 columns right">'
					. '<div class="panel radius notice">'
					. '<strong style="display:block; margin-bottom: 8px;">Notice:</strong>Discconnecting will remove your saved settings. This will not remove soundcloud players you posted in the blog or forum.'
					. '</div>'
					. '</div>';
					echo '<br class="clear" />';
					echo '<hr />';
					// settings form
					echo '<form class="custom" name="form_soundcloud_settings" id="form_soundcloud_settings" action="" method="POST">';
					echo '<div class="large-6 small-12 columns left">';
					echo '<div class="large-3 small-12 columns left">';
					echo '<img src="'. $me['avatar_url'] .'">';
					echo '</div>';
					echo '<div class="large-9 small-12 columns right">';
					echo '<br />';
					echo '<h5>'. $me['username'] .' Profile Settings:</h5>';
					echo '<br />';
					// soundcloud default image
					echo '<label><input type="checkbox" name="soundcloud_default_image" id="soundcloud_default_image" value="true"' . (get_user_meta($current_user->ID,'soundcloud_default_image',true) == "true" ? ' checked' : '') . '/> &nbsp; Use my soundcloud default image.</label>';
					echo '<br />';
					// show my sounds on my author page 
					echo '<label><input type="checkbox" name="soundcloud_show_sounds" id="soundcloud_show_sounds" value="true"' . (get_user_meta($current_user->ID,'soundcloud_show_sounds',true) == "true" ? ' checked' : '') . '/> &nbsp; Show sounds on my author page [<a href="'.site_url('/?action=soundcloud-tracks').'"> edit </a>]</label>';
					echo '<br />';
					// show my followers on my author page 
					echo '<label><input type="checkbox" name="soundcloud_show_followers" id="soundcloud_show_followers" value="true"' . (get_user_meta($current_user->ID,'soundcloud_show_followers',true) == "true" ? ' checked' : '') . '/> &nbsp; Show followers on my author page [<a href="'.site_url('/?action=soundcloud-followers').'"> edit </a>]</label>';
					echo '<br />';
					// show my playlists on my author page 
					echo '<label><input type="checkbox" name="soundcloud_show_playlists" id="soundcloud_show_playlists" value="true"' . (get_user_meta($current_user->ID,'soundcloud_show_playlists',true) == "true" ? ' checked' : '') . '/> &nbsp; Show playlists on my author page [<a href="'.site_url('/?action=soundcloud-playlists').'"> edit </a>]</label>';
					echo '<br />';
					// show my groups on my author page 
					echo '<label><input type="checkbox" name="soundcloud_show_groups" id="soundcloud_show_groups" value="true"' . (get_user_meta($current_user->ID,'soundcloud_show_groups',true) == "true" ? ' checked' : '') . '/> &nbsp; Show user groups on my author page [<a href="'.site_url('/?action=soundcloud-groups').'"> edit </a>]</label>';
					echo '</div>';
					echo '</div>';
					echo '<div class="large-6 small-12 columns right">';
					echo '<div class="panel radius">';
					echo '<h5>Player Settings:</h5>';
					echo '<br />';
					echo '<label><input type="checkbox" name="soundcloud_html5" id="soundcloud_html5" value="true"' . (get_user_meta($current_user->ID,'soundcloud_html5',true) == "true" ? " checked" : "") . '/> &nbsp; Use the HTML5 player (recommended).</label><br />';
					echo '<label><input type="checkbox" name="soundcloud_play_first" id="soundcloud_play_first" value="true"' . (get_user_meta($current_user->ID,'soundcloud_play_first',true) == "true" ? " checked" : "") . '/> &nbsp; Play first track on page load.</label><br />';
					echo '<label><input type="checkbox" name="soundcloud_show_artwork" id="soundcloud_show_artwork" value="true"' . (get_user_meta($current_user->ID,'soundcloud_show_artwork',true) == "true" ? " checked" : "") . '/> &nbsp; Show Artwork (HTML5 only).</label><br />';
					echo '<label><input type="checkbox" name="soundcloud_show_comments" id="soundcloud_show_comments" value="true"' . (get_user_meta($current_user->ID,'soundcloud_show_comments',true) == "true" ? " checked" : "") . '/> &nbsp; Show Comments.</label><br /><br />';
					echo '<input type="hidden" name="action" value="save_soundcloud_meta">';
					echo '</div>';
					echo '</div>';
					echo '<br class="clear" />';
					echo '<hr />';
					echo '<h5>Save Settings</h5>';
					echo '<br />';
					echo '<div class="small-12 columns text-center">';
					echo '<input type="submit" name="sc_save_settings" id="sc_save_settings" value="save settings" class="button radius">';
					echo '</div>';
					echo '</form>';
					echo '</div>';
					echo '<br class="clear" />';
				} catch (Services_Soundcloud_Invalid_Http_Response_Code_Exception $e) {
					//echo $current_user->ID;
					exit($e->getMessage());
				}

			endif;

		}
		
		/* netfunktheme soundcloud edit my sounds */
		function netfunk_soundcloud_tracks_page() {
			global $soundcloud, $current_user;
			get_currentuserinfo();
			
			if (!is_user_logged_in()): 
			
				_e('<p class="warning">You must be logged in to be here!</p><p><a href="/forum/ucp.php?mode=login">login here</a></p>', 'frontendprofile');
			 
			 else: 
				
				if ( isset($error) and $error != "" ) 
					echo '<p class="error">' . $error . '</p>'; 
			
			// save settings on post
			if (isset($_POST['action'])){
				if ($_POST['action'] == 'netfunk_save_soundcloud_options'){
					update_user_meta($current_user->ID, 'soundcloud_soundstoshow', $_POST['soundcloud_soundstoshow']);
					$sc_update_settings = true; }
			}
			if (get_user_meta($current_user->ID,'soundcloud_token',true)){
				$soundcloud->setAccessToken(get_user_meta($current_user->ID,'soundcloud_token',true));
				$me = json_decode($soundcloud->get('me'), true);
				$my_music = json_decode($soundcloud->get('tracks',array('user_id' => $me['id'])), true);
				echo ( isset($sc_update_settings) && $sc_update_settings == true ? '<div class="small-12 columns text-center"><div class="panel radius success">Settings Updated!</div></div><br class="clear"/><br />' : '');
				// show soundcloud groups
		?>
				<div class="small-12 sound_cloud_content">
				<h4>My Soundcloud Sounds</h4>
				<hr />
		<?php
				if (!empty($my_music)){
					echo '<form class="custom" name="form_soundcloud_settings" id="form_soundcloud_settings" method="POST" action="">';
					$options = get_user_meta($current_user->ID,'soundcloud_soundstoshow',true); 
					$sc_auto_play = (get_user_meta($current_user->ID,'soundcloud_play_first',true) == "true" ? true : false);
					
					foreach ($my_music as &$value) {
						echo '<div class="panel'.(isset($options[$value['permalink']]) && $options[$value['permalink']] == "1" ? ' alt' : '').' radius">';
						echo '<div class="large-6 small-12 left">';
						echo '<h6>'.$value['title'].'</h6>';
						echo '</div>';
						echo '<div class="large-6 small-12 text-right right">';
						echo '<strong>Show on author page</strong> &nbsp; &nbsp; <input type="radio" name="soundcloud_soundstoshow['
						.$value['permalink'].']" id="soundcloud_soundstoshow['
						.$value['permalink'].']" value="1"' 
						. (isset($options[$value['permalink']]) && $options[$value['permalink']] == "1" ? ' checked' : '') . '/> Yes &nbsp; <input type="radio" name="soundcloud_soundstoshow['
						.$value['permalink'].']" id="soundcloud_soundstoshow['
						.$value['permalink'].']" value="0"' 
						. (isset($options[$value['permalink']]) && $options[$value['permalink']] == "1" ? '' : ' checked') . '/> No ';
						echo '</div>';
						echo '<hr />';
						echo '<div class="small-12 ">';
						echo soundcloud_shortcode(
						array('url' => $value['uri'],
						  'iframe' => ''.(get_user_meta($current_user->ID,'soundcloud_html5',true) == "true" ? "true" : "false").'',
						  'params' => 'auto_play='.($sc_auto_play != false ? "true" : "false").'&amp;show_user=true&amp;show_artwork='
							.(get_user_meta($current_user->ID,'soundcloud_show_artwork',true) == "true" ? "true" : "false")
							.'&amp;show_comments='
							.(get_user_meta($current_user->ID,'soundcloud_show_comments',true) == "true" ? "true" : "false").'&amp;color=283636&amp;theme_color=272b2c',
						  'height' => '',
						  'width'  => ''
						));
						$sc_auto_play = false;
						echo '</div>';
						echo '<br class="clear"/>';
						echo '</div>';
					} // endforeach
					echo '<hr />';
					echo '<h5>Save Settings</h5>';
					echo '<br />';
					echo '<div class="small-12 columns text-center">';
					echo '<input type="hidden" name="action" value="netfunk_save_soundcloud_options">';
					echo '<input type="submit" class="button radius button-primary" value="Save Options" />';
					echo '</div>';
					echo '</form>';	
				} else {
					echo '<p>No sounds yet.</p>';
				}

				?></div><?php
			
			}
			
			endif;
		}

		/* netfunktheme soundcloud edit my groups */
		function netfunk_soundcloud_groups_page() {
			global $soundcloud, $current_user;
			get_currentuserinfo();
			
			if (!is_user_logged_in()): 
			
				_e('<p class="warning">You must be logged in to be here!</p><p><a href="/forum/ucp.php?mode=login">login here</a></p>', 'frontendprofile');
			 
			 else: 
				
				if ( isset($error) and $error != "" ) 
					echo '<p class="error">' . $error . '</p>';
			
			// save settings on post
			if (isset($_POST['action']) && $_POST['action'] == "netfunk_save_soundcloud_options"){
				update_user_meta($current_user->ID, "soundcloud_groupstoshow", $_POST['soundcloud_groupstoshow']);
				$sc_update_settings = true; 
			}
		
		
			if (get_user_meta($current_user->ID,'soundcloud_token',true)){
				$soundcloud->setAccessToken(get_user_meta($current_user->ID,'soundcloud_token',true));
				$me = json_decode($soundcloud->get('me'), true);
				$my_groups = json_decode($soundcloud->get('groups',array('user_id' => $me['id'])), true);
				echo (isset($sc_update_settings) && $sc_update_settings == true ? '<div class="small-12 columns text-center"><div class="panel radius success">Settings Updated!</div></div><br class="clear"/><br />' : '');
				
				// show soundcloud groups
				?>
				<div class="small-12 sound_cloud_content">
				<h4>My Soundcloud Groups</h4>
				<hr />
	
				<?php
					
				if (!empty($my_groups)){

					echo '<form class="custom" name="form_soundcloud_settings" id="form_soundcloud_settings" method="POST" action="">';
					$options = get_user_meta($current_user->ID,'soundcloud_groupstoshow',true); 
					echo '<div class="panel radius">';
					echo '<div class="small-12 columns text-right">';
					echo '<strong>Show on my author page</strong>';
					echo '</div>';
					echo '<br />';
					echo '<br />';
					echo '<ul>';
	
						foreach ($my_groups as &$value) :

							echo '<li'.(isset($options[$value['permalink']]) && $options[$value['permalink']] == "1" ? ' class="" style="background: #242628;"' : '').'>';
							echo '<br />';
							echo '<div class="small-9 columns left">';
							echo '<h6><a href="' 
							. $value['permalink_url'] . '" target="_blank"><strong>' 
							. $value['name'] . '</strong></a> </h6>'
							. $value['short_description'] . '&nbsp;-&nbsp;'
							. '<a href="'.$value['creator']['permalink_url'].'" target="_blank">' . $value['creator']['username'] . '</a><br /><br />';
							echo '</div>';
							// edit options 
							echo '<div class="small-2 columns right">';
							echo '<br />';
							//$value['permalink'];
							echo '<span'.(isset($options[$value['permalink']]) && $options[$value['permalink']] != "1" ? ' class="" style="color: #555;"' : '').'>';
							echo '<input type="radio" name="soundcloud_groupstoshow['.$value['permalink'].']" id="soundcloud_groupstoshow['.$value['permalink'].']" value="1"' . (isset($options[$value['permalink']]) && $options[$value['permalink']] == "1" ? ' checked' : '') . '/> Yes &nbsp; <input type="radio" name="soundcloud_groupstoshow['.$value['permalink'].']" id="soundcloud_groupstoshow['.$value['permalink'].']" value="0"' . (isset($options[$value['permalink']]) && $options[$value['permalink']] == "1" ? '' : ' checked') . '/> No ';
							echo '</span>';
							echo '</div>';
							echo '<br class="clear" />';
							echo '</li>';

						endforeach;
					
					echo '</ul>';
					echo '</div>';
					echo '<h5>Save Settings</h5>';
					echo '<br />';
					echo '<div class="small-12 columns text-center">';
					echo '<input type="hidden" name="action" value="netfunk_save_soundcloud_options">';
					echo '<input type="submit" class="button radius button-primary" value="Save Options" />';
					echo '</div>';
					echo '</form>';
				
				} else {
					echo '<p>No groups yet.</p>';
				}

				?> </div> <?php

			}

			endif;

		} 

		/* netfunktheme soundcloud edit my playlists */
		function netfunk_soundcloud_playlists_page() {
			global $soundcloud, $current_user;
			get_currentuserinfo();
			
			if (!is_user_logged_in()): 
			
				_e('<p class="warning">You must be logged in to be here!</p><p><a href="/forum/ucp.php?mode=login">login here</a></p>', 'frontendprofile');
			 
			 else: 
				
				if ( isset($error) and $error != "" ) 
					echo '<p class="error">' . $error . '</p>';
			
			// save settings on post
			if (isset($_POST['action'])){
		
				if ($_POST['action'] == "netfunk_save_soundcloud_options"){
					update_user_meta($current_user->ID, "soundcloud_liststoshow", $_POST['soundcloud_liststoshow']);
					$sc_update_settings = true; }
			}
		
			if (get_user_meta($current_user->ID,'soundcloud_token',true)){
				$soundcloud->setAccessToken(get_user_meta($current_user->ID,'soundcloud_token',true));
				$me = json_decode($soundcloud->get('me'), true);
				$my_playlists = json_decode($soundcloud->get('playlists',array('user_id' => $me['id'])), true);
				echo (isset($sc_update_settings) && $sc_update_settings == true ? '<div class="small-12 columns text-center"><div class="panel radius success">Settings Updated!</div></div><br class="clear"/><br />' : '');

				// show soundcloud groups
				?>
				<div class="small-12 sound_cloud_content">
				<br />
				<br />
				<h4>My Soundcloud Groups</h4>
				<hr />
				<?php
				if (!empty($my_playlists)){
					echo '<form class="custom" name="form_soundcloud_settings" id="form_soundcloud_settings" method="POST" action="">';
					$options = get_user_meta($current_user->ID,'soundcloud_liststoshow',true); 
					echo '<div class="panel radius">';
					echo '<div class="small-12 columns text-right">';
					echo '<strong>Show on my author page</strong>';
					echo '</div>';
					echo '<ul>';
					echo '<li>';

					foreach ($my_playlists as &$value) {
						echo '<div class="small-9 columns left">';
						echo '<h6 class="paneltitle"><a href="' . $value['permalink_url'] . '" target="_blank">' . $value['title'] . '</a></h6> ';
						
						$n = 1;
						foreach ($value['tracks'] as &$track){
							echo $n . ' - ' . $track['title'] . ' - <a href="'.$track['permalink_url'].'" target="_blank">' . $track['user']['username'] . '</a><br />';
							$n++;
						}

						echo '<br />';
						echo '</div>';
						echo '<br />';
						echo '<br />';

						// options
						echo '<div class="small-3 columns text-center right">';
						echo '<span'.(isset($options[$value['permalink']]) && $options[$value['permalink']] != "1" ? ' class="" style="color: #555;"' : '').'>';
						echo '<input type="radio" name="soundcloud_liststoshow['.$value['permalink'].']" id="soundcloud_liststoshow['.$value['permalink'].']" value="1"' . (isset($options[$value['permalink']]) && $options[$value['permalink']] == "1" ? ' checked' : '') . '/> Yes &nbsp; <input type="radio" name="soundcloud_liststoshow['.$value['permalink'].']" id="soundcloud_liststoshow['.$value['permalink'].']" value="0"' . (isset($options[$value['permalink']]) && $options[$value['permalink']] == "1" ? '' : ' checked') . '/> No ';
						echo '</span>';
						echo '</div>';
					}
					
					echo '<br class="clear"/>';
					echo '</li>';
					echo '</ul>';
					echo '<br class="clear"/>';
					echo '</div>';
					echo '<hr />';
					echo '<h5>Save Settings</h5>';
					echo '<br />';
					echo '<div class="small-12 columns text-center">';
					echo '<input type="hidden" name="action" value="netfunk_save_soundcloud_options">';
					echo '<input type="submit" class="button radius button-primary" value="Save Options" />';
					echo '</div>';
					echo '</form>';
					
				} else {
					echo '<p>No groups yet.</p>';
				}
				
				?> </div> <?php
			
			}
			
			endif;
		
		} 

		/* netfunktheme soundcloud edit my followers */
		function netfunk_soundcloud_followers_page() {
			global $soundcloud, $current_user;
			get_currentuserinfo();
			
			if (!is_user_logged_in()): 
			
				_e('<p class="warning">You must be logged in to be here!</p><p><a href="/forum/ucp.php?mode=login">login here</a></p>', 'frontendprofile');
			 
			 else: 
				
				if ( isset($error) and $error != "" ) 
					echo '<p class="error">' . $error . '</p>';
			
			// save settings on post
			if (isset($_POST['action'])){
				if ($_POST['action'] == "netfunk_save_soundcloud_options"){
					update_user_meta($current_user->ID, "soundcloud_show_followers", $_POST['soundcloud_show_followers']);
					$sc_update_settings = true; }
			}

			if (get_user_meta($current_user->ID,'soundcloud_token',true)){
				$soundcloud->setAccessToken(get_user_meta($current_user->ID,'soundcloud_token',true));
				$me = json_decode($soundcloud->get('me'), true);
				$my_followers = json_decode($soundcloud->get('me/followers',array('user_id' => $me['id'],'limit' => '48')), true);
				$total_followers =  $me['followers_count'];
				
				echo (isset($sc_update_settings) && $sc_update_settings == true ? '<div class="small-12 columns text-center"><div class="panel radius success">Settings Updated!</div></div><br class="clear"/><br />' : '');
				
				// show soundcloud groups
				?>
				<div class="small-12 sound_cloud_content">
				<br />
				<br />
				<h4>My Soundcloud Followers</h4>
				<hr />

				<?php
				
				if (!empty($my_followers)){
				
					echo '<form class="custom" name="form_soundcloud_settings" id="form_soundcloud_settings" method="POST" action="">';
					$option = get_user_meta($current_user->ID,'soundcloud_show_followers',true); 
					$display_followers = count($my_followers);
					echo '<div class="panel radius">';
					echo '<div class="small-6 columns text-left">';
					echo '<strong>Total Followers:</strong> ('.$total_followers .') &nbsp; - <a href="'.$me['permalink_url'].'/followers" target="_blank">view all</a>';
					echo '</div>';

					// option
					echo '<div class="small-6 columns text-right">';
					echo '<strong>Show on my author page:</strong> &nbsp; <input type="radio" name="soundcloud_show_followers" id="soundcloud_show_followers" value="true"' . (get_user_meta($current_user->ID,'soundcloud_show_followers',true) == "true" ? ' checked' : '') . '/> Yes &nbsp; <input type="radio" name="soundcloud_show_followers" id="soundcloud_show_followers" value=""' . (get_user_meta($current_user->ID,'soundcloud_show_followers',true) != "true" ? ' checked' : '') . '/> No ';
					echo '</div>';

					echo '<br class="clear"/>';
					echo '<br />';
					echo '<hr />';
					echo '<div class="small-12 columns">';
					
					// randomize list of followers
					shuffle ($my_followers);
					foreach ($my_followers as &$value) {
						echo '<div class="small-1 left">';
						echo '<a href="' . $value['permalink_url'] . '" title="' . $value['username'] . '" target="_blank"><img src="' . $value['avatar_url'] . '" border="0" /></a>';
						echo '<br />';
						echo '</div>';
					}
					
					echo '<br class="clear"/>';
					echo '</div>';
					echo '<br class="clear"/>';
					echo '</div>';
					echo '<hr />';
					echo '<h5>Save Settings</h5>';
					echo '<br />';
					echo '<div class="small-12 columns text-center">';
					echo '<input type="hidden" name="action" value="netfunk_save_soundcloud_options">';
					echo '<input type="submit" class="button radius button-primary" value="Save Options" />';
					echo '</div>';
					echo '</form>';
				
				} else {
					echo '<p>No followers yet.</p>';
				}
				
				?> </div> <?php
				
				/* Debug  */
				
				//echo '<pre>';
				//echo '<h6>debug</h6>';
				//print_r ($options);
				//echo '</pre>';
				//echo get_user_meta($current_user->ID,'soundcloud_show_followers',true);
				
			}

			endif;
		}

	} //endclass 

} //endif 

// EOF