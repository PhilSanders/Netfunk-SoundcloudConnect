<?php

/* Netfunk-Soundcloud-Connect Funtions */ 

// AUTHOR PAGE ADDONS //

/* netfunktheme soundcloud show author sounds */

if (!function_exists('netfunk_soundcloud_author_sounds')){

	function netfunk_soundcloud_author_sounds($user_id) {
	
		global $soundcloud;
		
		//$user_id = get_the_author_meta( 'ID' );     // DEBUG LINE
	
		//echo $user_id;
	
		if (class_exists('soundcloud_init')){
	
			if (get_user_meta($user_id,'soundcloud_token',true)){
	
				if (get_user_meta($user_id,'soundcloud_show_sounds',true)  == "true"){
	
					$soundcloud->setAccessToken(get_user_meta($user_id,'soundcloud_token',true));
					$me = json_decode($soundcloud->get('me'), true);
					$my_music = json_decode($soundcloud->get('tracks',array('user_id' => $me['id'])), true);

				?>
					
				<div class="small-12 sound_cloud_content">
				
				<br />
				
				<h4>Soundcloud Sounds</h4>
					
				<?php

				if (!empty($my_music)){ 
			
					$options = get_user_meta($user_id,'soundcloud_soundstoshow',true); 
			
					$sc_auto_play = (get_user_meta($user_id,'soundcloud_play_first',true) == "true" ? true : false);

					echo '<div class="panel radius">';

					foreach ($my_music as &$value) {
						
						if ( $options[$value['permalink']] == 1 ){
	
							echo '<br />';
	
							echo '<h6 class="paneltitle"><a href="'.$value['permalink_url'].'" target="_blank">'.$value['title'].'</a></h6>';
							
							echo soundcloud_shortcode(
							
								array('url' => $value['uri'],
									  'iframe' => ''.(get_user_meta($user_id,'soundcloud_html5',true) == "true" ? "true" : "false").'',
									  'params' => 'auto_play='.($sc_auto_play != false ? "true" : "false").'&amp;show_user=true&amp;show_artwork='
											.(get_user_meta($user_id,'soundcloud_show_artwork',true) == "true" ? "true" : "false")
											.'&amp;show_comments='
											.(get_user_meta($user_id,'soundcloud_show_comments',true) == "true" ? "true" : "false").'&amp;color=283636&amp;theme_color=272b2c',
									  'height' => '',
									  'width'  => ''
							));
							
							echo "<br /><br />";
	
							$sc_auto_play = false;

						} //endif
					
					} //endforeach
					
					echo '</div>';
					
				} else {
				
					echo "<p>No sounds yet.</p>";
					
				}
				
				
				//echo '<pre>';
				//echo '<h6>debug</h6>';
				//print_r ($options);
				//echo '</pre>';
				//echo get_user_meta($user_id,'soundcloud_groupstoshow['.$value['permalink'].']',true);

					
				?>
				
				
				
				</div>
		
				<?php
				
				}

			}
		
		} else {
		
			echo "Sounddcloud API Required";

		}
	}
}
	
	
	
/* netfunktheme soundcloud author followers */

if (!function_exists('netfunk_soundcloud_author_followers')){

	function netfunk_soundcloud_author_followers($user_id) {

		global $soundcloud, $current_user;
	
		if (class_exists('soundcloud_init')){
	
			if (get_user_meta($user_id,'soundcloud_token',true)){
	
				if (get_user_meta($user_id,'soundcloud_show_followers',true)  == "true"){

					$soundcloud->setAccessToken(get_user_meta($user_id,'soundcloud_token',true));
					$my_followers = json_decode($soundcloud->get('me/followers',array('user_id' => $user_id,'limit' => '48')), true);
					
					$me = json_decode($soundcloud->get('me',array('user_id' => $user_id)), true);
					$total_followers =  $me['followers_count'];
					$display_followers = count($my_followers);
					
					?>
					
					<div class="large-12 small-12 columns soundcloud_followers_content">
					
					
					<?php

					if (!empty($my_followers)){

						$options = get_user_meta($user_id,'soundcloud_show_followers',true); 

						//echo '<div class="panel radius">';

						

						shuffle ($my_followers);

						foreach ($my_followers as &$value) {
				
							echo '<div class="small-1 left">';
				
							echo '<a href="' . $value['permalink_url'] . '" title="' . $value['username'] . '" target="_blank"><img src="' . $value['avatar_url'] . '" border="0" /></a>';
	
							echo '<br />';
	
							echo '</div>';
	
						}
						
						//echo '</div>';
					
					?> 
					
					<br class="clear" />
					
					<br />
					
					<h6 class="text-right"><span class="webicon soundcloud small"></span> Soundcloud Fans | 
					
					<?php echo '<small><strong>Total Followers:</strong> ('.$total_followers .') &nbsp; - <a href="'.$me['permalink_url'].'/followers" target="_blank">view all</a></small>'; ?></h6>
	
					
					<?php 
					
					} else {
					
						echo '<p>No followers yet.</p>';
						
					}
					
				?> </div> <?php
					
				}
			}
		}
	}
}
	
	
/* netfunktheme soundcloud author groups */

if (!function_exists('netfunk_soundcloud_author_groups')){

	function netfunk_soundcloud_author_groups($user_id) {

		global $soundcloud;
	
		if (class_exists('soundcloud_init')){
	
			if (get_user_meta($user_id,'soundcloud_token',true)){
	
				if (get_user_meta($user_id,'soundcloud_show_groups',true)  == "true"){

					$soundcloud->setAccessToken(get_user_meta($user_id,'soundcloud_token',true));
					$me = json_decode($soundcloud->get('me'), true);
					$my_groups = json_decode($soundcloud->get('groups',array('user_id' => $me['id'])), true);
					
					?>
					
					<div class="small-12 sound_cloud_content">
					
					<br />
					
					<h4>Soundcloud Groups</h4>
	
					<?php
					
					if (!empty($my_groups)){

						$options = get_user_meta($user_id,'soundcloud_groupstoshow',true); 

						echo '<div class="panel radius">';

						foreach ($my_groups as &$value) {
						
							if ( $options[$value['permalink']] == 1 ){
							
								echo '<h4 class="paneltitle"><a href="'. $value['permalink_url'] . '" target="_blank">'. $value['name'] . '</a></h4>'
								. $value['short_description'] . '<br />' 
								. '<span style="color: #888">Created By:</span> '. '<a href="'.$value['creator']['permalink_url'].'" target="_blank">' . $value['creator']['username'] . '</a><br /><br />';
								
								echo '<hr />';
								
							} //endif
						
						} //endforeach
						
						echo '</div>';
					
					} else {
					
						echo '<p>No groups yet.</p>';
						
					}
					
				?> </div> <?php
					
				}
			}
		}
	}
}
	
	
/* netfunktheme soundcloud author playlists */

if (!function_exists('netfunk_soundcloud_author_playlists')){

	function netfunk_soundcloud_author_playlists($user_id) {

		global $soundcloud;
	
		if (class_exists('soundcloud_init')){
	
			if (get_user_meta($user_id,'soundcloud_token',true)){
	
				if (get_user_meta($user_id,'soundcloud_show_playlists',true)  == "true"){

					$soundcloud->setAccessToken(get_user_meta($user_id,'soundcloud_token',true));
					$me = json_decode($soundcloud->get('me'), true);
					$my_playlists = json_decode($soundcloud->get('playlists',array('user_id' => $me['id'])), true);
					
					?>
					
					<div class="small-12 sound_cloud_content">
					
					<br />
					
					<h4>Soundcloud Playlists</h4>

					<?php
					
					if (!empty($my_playlists)){

						$options = get_user_meta($user_id,'soundcloud_liststoshow',true); 
						
						echo '<div class="panel radius">';

						foreach ($my_playlists as &$value) {
				
							if ( $options[$value['permalink']] == 1 ){
				
								echo '<h6 class="paneltitle"><a href="' . $value['permalink_url'] . '" target="_blank">' . $value['title'] . '</a></h6> ';
	
								$n = 1;
	
								echo '<ul>';
								
								echo '<li>';
	
								foreach ($value['tracks'] as &$track){
	
									echo $n . ' - ' . $track['title'] . ' - <a href="'.$track['permalink_url'].'" target="_blank">' . $track['user']['username'] . '</a><br />';
	
									$n++;
								}
	
								echo '<br />';
								
								echo '</li>';
								
								echo '</ul>';
							
							}
						
						}
						
						echo '</div>';

					} else {
					
						echo '<p>No playlists yet.</p>';
						
					}
					
				?> </div> <?php
					
				}
			}
		}
	}
}
	

/* author page plugin hooks */

function soundcloud_author_followers_filter(){
/* Soundcloud Followers */
$user_id = get_the_author_meta('ID');
echo '<div class="large-9 small-12 right show-for-small-up">';
echo netfunk_soundcloud_author_followers($user_id);
echo '</div>';}

add_action('netfunk_author_page_info', 'soundcloud_author_followers_filter',1,0);

function soundcloud_author_sounds_filter(){
$user_id = get_the_author_meta('ID');
netfunk_soundcloud_author_sounds($user_id);}

add_action('netfunk_author_page_info', 'soundcloud_author_sounds_filter',2,0);

function soundcloud_author_playlists_filter(){
$user_id = get_the_author_meta('ID');
netfunk_soundcloud_author_playlists($user_id);}

add_action('netfunk_author_page_info', 'soundcloud_author_playlists_filter',3,0);

function soundcloud_author_groups_filter(){
$user_id = get_the_author_meta('ID');
netfunk_soundcloud_author_groups($user_id);}

add_action('netfunk_author_page_info', 'soundcloud_author_groups_filter',4,0);

//EOF