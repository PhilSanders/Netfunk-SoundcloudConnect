<?php 

/* action pages */

require_once ( ABSPATH . '/wp-content/plugins/netfunk-soundcloud-connect/includes/soundcloud.php' );

add_action( 'admin_init', 'soundcloud_connect_options_init' );
add_action( 'admin_menu', 'soundcloud_connect_options_add_page' );

$request_action = (!empty($_REQUEST['action']) ? $_REQUEST['action'] : '');


// register theme plugin settings

function soundcloud_connect_options_init(){
	register_setting( 'soundcloud_connect_plugin_options', 'soundcloud_connect_options', 'soundcloud_connect_options_validate' );
}

// add plugin submenu to general options

function soundcloud_connect_options_add_page() {
	add_submenu_page('options-general.php',__( 'Netfunk soundcloud Connect' ),
	__('Soundcloud Connect' ),'edit_theme_options','soundcloud_connect', 'soundcloud_connect_options_page' );
}

// missing plugin admin notices 

add_action( 'admin_notices', 'soundcloud_connect_notices' );

function soundcloud_connect_notices(){
    global $current_screen;
    if ( $current_screen->parent_base == 'soundcloud_connect' )
    echo '<div><p>Warning - changing settings on these pages may cause problems with your website\'s design!</p></div>';
}

/* theme plugin shortcode generator */

if (!function_exists( 'soundcloud_connect_member_profile_shortcode')){
	# soundcloud_connect_shortcode ( $shortcode , $function ) 
	# soundcloud_connect_shortcode ( 'soundcloud_connect_member_edit_page', 'soundcloud_connect_edit_profile_page' )  
	# shortcode would be '[soundcloud_connect_member_edit_page]' 
	function soundcloud_connect_shortcode ( $shortcode, $function ) {
		add_shortcode ($shortcode, $function);
	}
}

add_action( 'soundcloud_connect_shortcode', 'soundcloud_connect_shortcode' );


/* theme plugin action page generator */
// soundcloud_connect_action_page_init ($action, $class, $function);
// soundcloud_connect_action_page_init ('edit-member', 'edit-member', 'soundcloud_connect_edit_profile_page'); 
function soundcloud_connect_action_page_init ($action, $class, $function) {

	global $request_action;

	if ($request_action == $action) {
  
	  add_filter('body_class','soundcloud_connect_edit_member_page_slug',1,1);
	  
	  add_filter('the_action_title','soundcloud_connect_the_action_title',1,1);
	  
	  add_filter('the_content',$function,1,1);
	  
	  add_action('template_redirect', 'soundcloud_connect_action_page_template');
	  
	}

}


/* plugin action page <body> class */

function soundcloud_connect_edit_member_page_slug($classes) {

	$new_classes = array();

	foreach ($classes as $class){

		// remove the 'home' body element class
		if ( $class != 'home' )
		$new_classes[] = $class;

	}
	
	$new_classes[] = 'member-profile-edit';
	
	return $new_classes;
	
}


/* plugin plugin action page shortcode */

function soundcloud_connect_action_page_content($content) {

	return $content;

}


/* plugin action page <h1> title class */


function soundcloud_connect_the_action_title () {

	global $action_page_title;

	echo $action_page_title;

}

// add_filter('the_action_title','soundcloud_connect_the_action_title',1,3);

add_filter('the_action_title','soundcloud_connect_the_action_title',1,1);



function soundcloud_connect_action_page_template() {
	
  include (get_template_directory()."/action.php");
  
  exit;
  
}




/* theme plugin sidebar hook */

function soundcloud_connect_action_page_sidebar_widget () {

	global $plugin_widget_sidebar;
	
	if (is_array( $plugin_widget_sidebar )){
		
		?>
        
        <div class="large-3 small-12 columns right">
		
				<div id="sidebar" class="widget-area theme-action-sidebar">
        
        			<ul class="sid">
        
					<?php foreach ( $plugin_widget_sidebar as $widget ) { ?>		
                    
                    <li id="<?php echo (!empty($widget['widget_id']) ? $widget['widget_id'] : ""); ?>" class="widgetcontent<?php echo (!empty($widget['widget_class']) ? " " . $widget['widget_class'] : ""); ?>">
                    
					<?php if (!empty($widget['widget_title'])){ ?>
                        
                            <h5 class="widgettitle"><?php echo $widget['widget_title']; ?></h5>
            
                     <?php  } ?> 

                     <?php echo $widget['widget_content'] ?>

                     </li>

                    <?php
                
                    }
                    
                    ?> 
                    
         			</ul>

        		</div>
			
            
            	<hr />
            
			
            </div>

		<?php

	} else {
		
		
		
	}

}

add_filter('soundcloud_connect_action_sidebar','soundcloud_connect_action_page_sidebar_widget',1,4); 







// validate theme plugin options
	
function soundcloud_connect_options_validate( $input ) {
	
	$options = get_option( 'soundcloud_connect_options' );

	if (isset($input)){

		foreach ( $input as $plugin ){

			if (isset($input['action']) && $input['action'] != 'delete-selected'){

				// activate plugin
				
				if (isset($input['action']) && $input['action'] == 'activate-selected')
					
					$activate = 1;
				
				// deactivate plugin
				
				else if (isset($input['action']) && $input['action'] == 'deactivate-selected')
				
					$activate = 0;
		
				// plugin options
				
				if ( $plugin != 'action')
					$options[$plugin] = wp_filter_nohtml_kses( $activate );

			} else {
				
				// delete plugin
				remove_plugin($plugin);

			}

		}
	}
	
	return $options;

}



/* theme plugins options page */

function soundcloud_connect_options_page() {

	global $current_user;

	if ( ! isset( $_REQUEST['settings-updated'] ) )
		$_REQUEST['settings-updated'] = false;

	?>
	
	<div class="wrap">
	
	
		<div id="icon-themes" class="icon32"></div>  
		
		
		<?php echo "<h2>" . __( 'Soundcloud Connect by NetfunkDesign', 'netfunk-soundcloud-connect' ) . "</h2>"; ?>
		

		<hr style=" border: solid #DDD 1px;"/>

		<br />
		

		<?php if ( false !== $_REQUEST['settings-updated'] ) : ?>
		
			<div class="updated fade"><p><strong><?php _e( 'Options saved', 'netfunk-soundcloud-connect' ); ?></strong></p></div>
		
        	<hr style=" border: solid #DDD 1px;"/>
        
		<?php endif; ?>
        
        
        <?php

		if (!get_user_meta($current_user->ID,'soundcloud_token')){
			
			do_action('soundcloud_auth_link');
		
		} else {
			
			do_action('soundcloud_disconnect_link');
		
		}
			
		
		?>
		

		<form method="post" action="options.php">

		<h3>More plugins? What are these?</h3>

		<p>Breaksculture plugins are designed to add extened support for popular content services such as APIs and advanced style and layout options. <br />
		
		Our plugins are design to merge flawlessly, and look amazing, with our Breaksculture theme design. <br />
		
		Enable or disable plugins below to expand, or simplify, your horizons. </p>

		<br />
		

		<h3>Where do I get more of these plugins?</h3>
		
		<p>This theme comes with only a few plugin, but may purchase more at our website for a reasonable price. <br /> 
		
		If you would like all featured plugins included, you may purchase the expanded theme package at our website.
        
        
        <br />
        
        <br />
        
        	<a href="http://www.breaksculture.com" target="_blank" class="aligncenter button-primary">BreaksCulture.com</a>
        
        
        </p>

		<br />

		<hr style=" border: solid #DDD 1px;"/>
		
		<br />

		<h3>Theme Plugins</h3>

		<div class="tablenav top">

			<div class="actions bulkactions">
	
				<select name="soundcloud_connect_options[action]" id="soundcloud_connect_options[action]">
				
					<option selected="selected" value="-1"> Bulk Actions </option>
					
					<option value="activate-selected"> Activate </option>
					
					<option value="deactivate-selected"> Deactivate </option>
					
					<option value="delete-selected"> Delete </option>
					
					<!--option value="update-selected"> Update </option-->

				</select>
				
				<input id="doaction" class="button action" type="submit" value="Apply" name="">
		
			</div>
	
		</div>
		
		
		<table class="wp-list-table widefat plugins" cellspacing="0">
	 
		 <thead>
	  
		  <tr>
	 
			<th id="cb" class="manage-column column-cb check-column" style="" scope="col"> <input type="checkbox" name="checkall" id="checkall" value="1"> </th>
	 
			<th id="name" class="manage-column column-name" style="" scope="col"> Plugin </th>
	 
			<th id="description" class="manage-column column-description" style="" scope="col"> Description </th>
	
		  </tr>
	  
		</thead>
		
		<tbody id="the-list">
		  
		  <?php 
		
			//get_valid_theme_plugins();

		 ?>
		  
		  </tbody>
		  
		  <tfoot>
		  
		  <tr>
		
			<th id="cb" class="manage-column column-cb check-column" style="" scope="col"> <input type="checkbox" name="checkall" id="checkall" value="1"> </th>
		
			<th id="name" class="manage-column column-name" style="" scope="col"> Plugin </th>
		
			<th id="description" class="manage-column column-description" style="" scope="col"> Description </th>
		
		  </tr>
	  
		</tfoot>
	 
		</table>

		<br />

		<hr style=" border: solid #DDD 1px;"/>
		
		<br />
		
		<!--h3>Save settings </h3>

		<p class="submit">
			<input type="submit" class="button-primary" value="<?php //_e( 'Save Options', 'netfunk-soundcloud-connect' ); ?>" />
		</p-->

	</form>

	</div>

	<?php

	/* Debug  */
				
		//$options = get_option( 'soundcloud_connect_options' );
					
		//echo '<pre>';
		//echo '<h6>debug</h6>';
		//print_r ($options);
		//echo '</pre>';

}




