<?php
/*
Plugin Name: Submittable
Plugin URI: http://www.submittable.com/wordpress
Description: Plugin for integrating Submittable&trade; data into your WordPress powered website.
Author: R.Peterson
Version: 1.0.9
*/

// ------------------------------------------------------------------------
// REQUIRE MINIMUM VERSION OF WORDPRESS:
// ------------------------------------------------------------------------

function requires_wordpress_version() {
	global $wp_version;
	$plugin = plugin_basename( __FILE__ );
	$plugin_data = get_plugin_data( __FILE__, false );

	if ( version_compare($wp_version, "3.0", "<" ) ) {
		if( is_plugin_active($plugin) ) {
			deactivate_plugins( $plugin );
			wp_die( "'".$plugin_data['Name']."' requires WordPress 3.0 or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );
		}
	}
}
add_action( 'admin_init', 'requires_wordpress_version' );

// ------------------------------------------------------------------------
// PLUGIN PREFIX:
// ------------------------------------------------------------------------

// 'submittable_' prefix

// ------------------------------------------------------------------------
// REGISTER HOOKS & CALLBACK FUNCTIONS:
// ------------------------------------------------------------------------

register_activation_hook(__FILE__, 'submittable_add_defaults');
register_uninstall_hook(__FILE__, 'submittable_delete_plugin_options');
add_action('admin_init', 'submittable_init' );
add_action('admin_menu', 'submittable_add_options_page');
add_filter( 'plugin_action_links', 'submittable_plugin_action_links', 10, 2 );

// --------------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_uninstall_hook(__FILE__, 'submittable_delete_plugin_options')
// --------------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE USER DEACTIVATES AND DELETES THE PLUGIN. IT SIMPLY DELETES
// THE PLUGIN OPTIONS DB ENTRY (WHICH IS AN ARRAY STORING ALL THE PLUGIN OPTIONS).
// --------------------------------------------------------------------------------------

// Delete options table entries ONLY when plugin deactivated AND deleted
function submittable_delete_plugin_options() {
	delete_option('submittable_options');
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_activation_hook(__FILE__, 'submittable_add_defaults')
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE PLUGIN IS ACTIVATED. IF THERE ARE NO THEME OPTIONS
// CURRENTLY SET, OR THE USER HAS SELECTED THE CHECKBOX TO RESET OPTIONS TO THEIR
// DEFAULTS THEN THE OPTIONS ARE SET/RESET.
//
// OTHERWISE, THE PLUGIN OPTIONS REMAIN UNCHANGED.
// ------------------------------------------------------------------------------

// Define default option settings
function submittable_add_defaults() {
	$tmp = get_option('submittable_options');
    if(($tmp['chk_default_options_db']=='1')||(!is_array($tmp))) {
		delete_option('submittable_options'); // so we don't have to reset all the 'off' checkboxes too! (don't think this is needed but leave for now)
		$arr = array(	"chk_default_options_db" => "",
						"subdomain" => "",
						"button_label" => "",
						"show_fees" => "",
						"show_types" => "",
						"show_main_description" => "",
						"include_css" => "yes"
		);
		update_option('submittable_options', $arr);
	}
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: add_action('admin_init', 'submittable_init' )
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE 'admin_init' HOOK FIRES, AND REGISTERS THE PLUGIN
// SETTINGS WITH THE WORDPRESS SETTINGS API.
// ------------------------------------------------------------------------------

// Init plugin options to white list our options
function submittable_init(){
	register_setting( 'submittable_plugin_options', 'submittable_options', 'submittable_validate_options' );
	wp_enqueue_style('submittable-default-css', plugin_dir_url(__FILE__).'css/submittable-admin.css');
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: add_action('admin_menu', 'submittable_add_options_page');
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE 'admin_menu' HOOK FIRES, AND ADDS THE NEW OPTIONS
// PAGE TO THE SETTINGS MENU.
// ------------------------------------------------------------------------------

// Add menu page
function submittable_add_options_page() {
	add_options_page(__('Submittable Plugin Options', 'submittable'), __('Submittable', 'submittable'), 'manage_options', __FILE__, 'submittable_render_form');
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION SPECIFIED IN: add_options_page()
// ------------------------------------------------------------------------------
// THIS FUNCTION IS SPECIFIED IN add_options_page() AS THE CALLBACK FUNCTION THAT
// ACTUALLY RENDER THE PLUGIN OPTIONS FORM AS A SUB-MENU UNDER THE EXISTING
// SETTINGS ADMIN MENU.
// ------------------------------------------------------------------------------

// Render the Plugin options form
function submittable_render_form() {
	?>
	<div class="wrap">

		<!-- Display Plugin Icon, Header, and Description -->

		<div class="icon32" id="submittable_admin_icon"><br></div>

		<h2><?php _e('Submittable&trade; Plugin Options', 'submittable'); ?></h2>

		<!-- Beginning of the Plugin Options Form -->
		<form method="post" action="options.php">

			<?php settings_fields('submittable_plugin_options'); ?>
			<?php $options = get_option('submittable_options'); ?>

            <?php // print_r($options); ?>

			<!-- Table Structure Containing Form Controls -->
			<!-- Each Plugin Option Defined on a New Table Row -->
			<table class="form-table">

                <!-- Usage -->
                <tr valign="top">
					<th scope="row"><?php _e('Shortcode Usage', 'submittable'); ?><pre>[submittable]</pre></th>
					<td>
                    <?php _e('Simply add the Submittable&trade; shortcode ( [submittable] ) to any post/page in your site. <br /><i>Be sure to also enter your custom Submittable&trade; "subdomain" below.</i>', 'submittable'); ?>
					</td>
				</tr>

				<tr><td colspan="2"><div class="submittable-spacer"></div></td></tr>

				<!-- Subdomain -->
				<tr >
					<th scope="row"><?php _e('Submittable&trade; Sub Domain', 'submittable'); ?><br /><i>(ex: http://YOURORGNAME.submittable.com)</i></th>
					<td>
						<input type="text" size="20" name="submittable_options[subdomain]" value="<?php echo $options['subdomain']; ?>" /><span class="submittable-input-span"><?php _e('Only add the SUBDOMAIN, not the full domain. So just the first part of your URL(i.e. YOURORGNAME).', 'submittable'); ?></span>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e('Custom Button Label', 'submittable'); ?><br /><i><?php _e('(ex: "Apply Today", "Submit Now")', 'submittable'); ?></i></th>
					<td>
						<input type="text" size="20" name="submittable_options[button_label]" value="<?php echo $options['button_label']; ?>" />
					</td>
				</tr>

				<!-- CSS Button Group -->
                <tr valign="top">
					<th scope="row"><?php _e('Include CSS?', 'submittable'); ?></th>
					<td>
						<label class="submittable-relative-css">
                        	<input name="submittable_options[include_css]" type="radio" value="yes" <?php checked('yes', $options['include_css']); ?> />
                            <?php _e('Include Plugin CSS?', 'submittable'); ?>
                            <span><?php _e('[Default Styling]', 'submittable'); ?></span>
                        </label>

						<label class="submittable-relative-css">
                        	<input name="submittable_options[include_css]" type="radio" value="no" <?php checked('no', $options['include_css']); ?> />
                            <?php _e('Disable Plugin CSS?', 'submittable'); ?>
                            <span><?php _e('[Requires custom CSS]', 'submittable'); ?></span>
                        </label>
                        <span class="submittable-input-span"><?php _e('Select whether or not you\'d like the plugin to include (CSS) style rules or not.', 'submittable'); ?></span>
					</td>
				</tr>

                <!-- Show Main Description Option -->
                <tr valign="top">
					<th scope="row"><?php _e('Show Main Description?', 'submittable'); ?></th>
					<td>
                        <label>
                        	<input name="submittable_options[show_main_description]" type="checkbox" value="yes" <?php if (isset($options['show_main_description'])) { checked('yes', $options['show_main_description']); } ?> />
                            <?php _e('Yes, show the "Main" description.', 'submittable'); ?>
                        </label>

						<span class="submittable-input-span"><?php _e('Show the "Main" Submittable&trade; Description text above your submission listings.', 'submittable'); ?></span>
                    </td>
                </tr>

                <!-- Show Fees Option -->
                <tr valign="top">
					<th scope="row"><?php _e('Show Fees?', 'submittable'); ?></th>
					<td>
                        <label>
                        	<input name="submittable_options[show_fees]" type="checkbox" value="yes" <?php if (isset($options['show_fees'])) { checked('yes', $options['show_fees']); } ?> />
                            <?php _e('Yes, show fee for each item.', 'submittable'); ?>
                        </label>

						<?php /*?><span class="submittable-input-span">Describe Fees Option in detail here</span><?php */?>
                    </td>
                </tr>

                <!-- Show Types Option -->
                <tr valign="top">
					<th scope="row"><?php _e('Show Document Types?', 'submittable'); ?></th>
					<td>
                        <label>
                        	<input name="submittable_options[show_types]" type="checkbox" value="yes" <?php if (isset($options['show_types'])) { checked('yes', $options['show_types']); } ?> />
                            <?php _e('Yes, show accepted document types for all items.', 'submittable'); ?>
                        </label>

						<?php /*?><span class="submittable-input-span">Describe Document Types Option in detail Here</span><?php */?>
                    </td>
                </tr>

				<tr><td colspan="2"><div class="submittable-spacer"></div></td></tr>

				<!-- Remove Branding -->
				<tr valign="top" >
					<th scope="row"><?php _e('Remove Branding?', 'submittable'); ?></th>
					<td>
						<label>
                        	<input name="submittable_options[remove_branding]" type="checkbox" value="yes" <?php if (isset($options['remove_branding'])) { checked('yes', $options['remove_branding']); } ?> />
                            <?php _e('Yes, Please remove the Submittable&trade; branding on my site.', 'submittable'); ?>
                        </label>
					</td>
				</tr>

                <!-- Reset Data -->
				<tr valign="top" >
					<th scope="row"><?php _e('Reset Data?', 'submittable'); ?></th>
					<td>
						<label>
                        	<input name="submittable_options[chk_default_options_db]" type="checkbox" value="1" <?php if (isset($options['chk_default_options_db'])) { checked('1', $options['chk_default_options_db']); } ?> />
                            <?php _e('Restore defaults upon plugin deactivation/reactivation', 'submittable'); ?>
                        </label>
						<span class="submittable-input-span"><?php _e('Only check this if you want to reset plugin settings upon Plugin reactivation', 'submittable'); ?></span>
					</td>
				</tr>

			</table>


			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'submittable') ?>" />
			</p>
		</form>

		<div id="submittable_social">

			<a href="http://www.submittable.com" id="submittable_footer_logo" title="<?php _e('Powered By Submittable', 'submittable'); ?>" target="_blank"><img src="https://mnager.submittable.com/Public/Images/submittable-footer-logo.png" /></a>

		</div>

	</div>
	<?php
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function submittable_validate_options($input) {

	$input['subdomain'] = preg_replace( '/\s+/', '', $input['subdomain'] ); // strip whitespace from subdomain first
	$input['subdomain'] =  wp_filter_nohtml_kses($input['subdomain']); // Sanitize textbox input (strip html tags, and escape characters )
	$input['button_label'] =  wp_filter_nohtml_kses($input['button_label']); // Sanitize textbox input (strip html tags, and escape characters )
	return $input;

}

// Display a Settings link on the main Plugins page
function submittable_plugin_action_links( $links, $file ) {

	if ( $file == plugin_basename( __FILE__ ) ) {
		$submittable_links = '<a href="'.get_admin_url().'options-general.php?page=submission-manager-by-submittable/submittable.php">'.__('Settings').'</a>';
		// make the 'Settings' link appear first
		array_unshift( $links, $submittable_links );
	}

	return $links;
}

// ------------------------------------------------------------------------------
// SHORTCODE:
// ------------------------------------------------------------------------------

function submittable_get_content($atts) {
     //reno attributes
	 $submittable_options = get_option('submittable_options');
	 $submittable_options['subdomain'] = preg_replace( '/\s+/', '', $submittable_options['subdomain'] );

	 //checking for blank/empty value
	 if ($submittable_options['subdomain'] == '') {

		 //force enqueue styles if there's an error
		wp_enqueue_style('submittable-default-css', plugin_dir_url(__FILE__).'css/submittable-default.css');
		$error_no_sub = '<div id="submittable_content"><div class="alert">';
		$error_no_sub .= __('Oops! Looks like there\'s some setup info needed with the Submittable Plugin.', 'submittable');
		if (current_user_can('manage_options')) {
			$error_no_sub .= '<br />'.__('Admin: The "subdomain" value is missing in the <a href="/wp-admin/options-general.php?page=submittable/submittable.php">Plugin Settings!', 'submittable');
		}
		$error_no_sub .= '</a></div></div>';
		 return $error_no_sub;
		 exit;

	 } else {

		 $submittable_content = ''; // reset variable

		// Get RSS Feed(s)
		include_once(ABSPATH . WPINC . '/feed.php');

		// Cache Filter Duration
		function return_30( $seconds )
			{
			  // change the default feed cache recreation period to 1 hour
			  return 30;
			}

		// Get a SimplePie feed object from the specified feed source.

		add_filter( 'wp_feed_cache_transient_lifetime' , 'return_30' );
		
		$subdomain = $submittable_options['subdomain'];
		$numDots = substr_count( $submittable_options['subdomain'], '.');
		if( $numDots == 0 )
			$subdomain = $subdomain . '.submittable.com';
		
		$rss_url = 'http://'.$subdomain.'/rss/';
		
		$submittable_rss = fetch_feed($rss_url);
		
		remove_filter( 'wp_feed_cache_transient_lifetime' , 'return_30' );

        //$submittable_rss->set_cache_duration(60);
		if (is_wp_error( $submittable_rss ) ) { // If there's an error getting the RSS feed
			$error_string = $submittable_rss->get_error_message();
			//force enqueue styles if there's an error
			wp_enqueue_style('submittable-default-css', plugin_dir_url(__FILE__).'css/submittable-default.css');
			$error_no_rss = '<div id="submittable_content"><div class="alert"><p>';
			$error_no_rss .= __('Oops! There is an error getting the Information from Submittable&trade;.', 'submittable');
			if (current_user_can('manage_options')) {
				$error_no_rss .= '<br />'.__('Admin: Please double check that your entered "<a href="/wp-admin/options-general.php?page=submittable/submittable.php">subdomain</a>" is valid.', 'submittable');
				$error_no_rss .= '<br />'.sprintf(__('You entered: <i>%s</i> for the subdomain.', 'submittable'), $submittable_options['subdomain']);
			}
			$error_no_rss .= '</p><span>';
			$error_no_rss .= '</span></div></div>';
			return $error_no_rss;
			exit;

		} else { // no RSS Error, carrying on
			$submittable_rss->enable_order_by_date(false);

			if ($submittable_options['button_label'] != '') { // reset the button label, if overriding text has been added
				$button_label = $submittable_options['button_label']."&nbsp;&raquo;";
			} else { // Stock Button Text
				$button_label = __('Learn More', 'submittable')."&nbsp;&raquo;";

		}

			// Figure out how many total items there are.
			$maxitems = $submittable_rss->get_item_quantity(0); // setting get_item_quantity to "0" returns all items

			// Build an array of all the items, starting with element 0 (first element).
			//$rss_items = array_reverse($submittable_rss->get_items(0, $maxitems));
			$rss_items = $submittable_rss->get_items(0, $maxitems);

		} // end is_wp_error check

		$submittable_content .= '<div id="submittable_content">';

		if ($submittable_options['show_main_description'] == "yes") {

			$submittable_content .= '<div id="feed_description">';
			$submittable_content .= $submittable_rss->get_description();
			$submittable_content .= '</div>';

		}

		$submittable_content .= '<ul>';

		if ($maxitems == 0) {

			$submittable_content .= '<li>'.__('No items.', 'submittable').'</li>';

	 	} else {

			// Loop through each feed item and display each item as a hyperlink.
    		foreach ( $rss_items as $item ) :

				$submittable_content .= '<li class="clearfix">';

				$submittable_content .= '<h2><a href="'.esc_url( $item->get_permalink() ).'" title="'.__('Posted', 'submittable').' '.$item->get_date('j F Y | g:i a').'" target="_blank">';
					$submittable_content .= $item->get_title();
				$submittable_content .= '</a></h2>';

				$submittable_content .= '<div class="sub-description">';
				$submittable_content .= $item->get_content();
				$submittable_content .= '</div>';

				// check on the fees
				$submittable_fee = $item->get_item_tags('', 'fee');
				$submittable_amount = $submittable_fee[0]['data'];

				if ($submittable_amount != "$0.00" && $submittable_options['show_fees'] == "yes") {
					$submittable_content .= '<div class="submittable-types">';
					//$submittable_content .= '<b>__('Submission Fee:', 'submittable')</b>&nbsp;';
					$submittable_content .= $submittable_amount;
					$submittable_content .= '</div>';
				}

				// check on the submission types
				$submittable_docs = $item->get_item_tags('', 'acceptableTypes');
				$submittable_types = $submittable_docs[0]['data'];

					if ($submittable_types != "" && $submittable_options['show_types'] == "yes") {
						$submittable_content .= '<div class="submittable-fee">';
						$submittable_content .= '<b>'.__('Accepted Document Types:', 'submittable').'</b>&nbsp;';
						$submittable_content .= $submittable_types;
						$submittable_content .= '</div>';
					}

				$submittable_content .= '<a href="'.esc_url( $item->get_permalink() ).'" class="button" target="_blank">'.$button_label.'</a>';

				$submittable_content .= '</li>';

			endforeach;

		} // end loop through each feed item

		$submittable_content .= '</ul>';

		if($submittable_options['remove_branding'] != 'yes') {
			$submittable_content .= '<div id="submittable_branding">';
			$submittable_content .= '<a href="http://www.submittable.com" target="_blank"><img src="'.plugin_dir_url(__FILE__).'images/submittable-powered.png" alt="" /></a>';
			$submittable_content .= '</div>';
		}

		$submittable_content .= '</div> <!-- /submittable content -->';


	} // end sudomain check

	// Return the output to the shortcode
	return $submittable_content;

} // end submittable_get_content function

add_shortcode('submittable', 'submittable_get_content');


// CHECK TO SEE IF CSS SHOULD BE ENQUEUE'd or NOT

function submittable_filter_posts() {
	$options = get_option('submittable_options');
	if ($options['include_css'] == "yes") {
			return true;
		} else {
			return false;
		}
}

if (submittable_filter_posts()) {

	add_filter('the_posts', 'submittable_enqueue'); // the_posts gets triggered before wp_head

 }

/*
 * Find shortcode and enqueue stylesheet only on pages/posts where it is found
 */
function submittable_enqueue($posts){
	if (empty($posts)) return $posts;

	$shortcode_exists = false; // use this flag to see if styles and scripts need to be enqueued
	$css_files = array();
	foreach ($posts as $post) {

		// find shortcode
		if (preg_match("/\[submittable\]/", $post->post_content, $matches) > 0) {
			$shortcode_exists = true; // ah ha!
		}
	}

	if ($shortcode_exists) {
		//wp_enqueue_script('submittable-js', "http://link.goes.here.js");
		wp_enqueue_style('submittable-default-css', plugin_dir_url(__FILE__).'css/submittable-default.css');
	}

	return $posts;
}