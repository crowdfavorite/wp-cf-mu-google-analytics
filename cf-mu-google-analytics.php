<?php
/*
Plugin Name: cf-mu-google-analytics 
Plugin URI: http://crowdfavorite.com 
Description: Allows Network sites to have multiple google analytics accounts tracking at the same time. Initial version Requires CF-Post-Meta.
Version: .25 
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
*/

// ini_set('display_errors', '1'); ini_set('error_reporting', E_ALL);

if (!defined('PLUGINDIR')) {
	define('PLUGINDIR','wp-content/plugins');
}


if (is_file(trailingslashit(ABSPATH.PLUGINDIR).basename(__FILE__))) {
	define('CFGA_FILE', trailingslashit(ABSPATH.PLUGINDIR).basename(__FILE__));
}
else if (is_file(trailingslashit(ABSPATH.PLUGINDIR).dirname(__FILE__).'/'.basename(__FILE__))) {
	define('CFGA_FILE', trailingslashit(ABSPATH.PLUGINDIR).dirname(__FILE__).'/'.basename(__FILE__));
}

register_activation_hook(CFGA_FILE, 'cfga_install');

function cfga_install() {
// TODO
}


function cfga_init() {
	if (is_admin() && !function_exists("cf_input_block")) {

		echo '
<div class="error">
	<h3>A required Plugin is not installed or activated:</h3>
	<p>The Required Plugin "CF-POST-META" either is not installed, or it needs to be activated. Please activate the plugin and try again.</p>
</div>
		';

	}
}
add_action('init', 'cfga_init');

function cfga_get_global_tracker_accounts() {
	$global_tracker_codes = get_option('cfga_global_tracking_codes');
	// todo
	return true;
	
}

function cfga_get_local_tracker_accounts() {
	global $blog_id;
	$local_tracker_codes = get_blog_option($blog_id, 'cfga_local_tracking_codes');
	foreach ($local_tracker_codes as $tracker_code) {
		// todo
	}
	return true;
}

function cfga_request_handler() {
	if (!empty($_GET['cf_action'])) {
		switch ($_GET['cf_action']) {

			case 'cfga_admin_js':
				cfga_admin_js();
				break;
			
			case 'cfga_setup_js':
				cfga_setup_js();
				break;
			case 'cfga_insert_accounts_js':
				cfga_insert_accounts_js();
				break;
		}
	}
	if (!empty($_POST['cf_action'])) {
		switch ($_POST['cf_action']) {

			case 'cfga_update_settings':
				cfga_save_settings();
				wp_redirect(trailingslashit(get_bloginfo('wpurl')).'wp-admin/options-general.php?page='.basename(__FILE__).'&updated=true');
				die();
				break;
		}
	}
}
add_action('init', 'cfga_request_handler');


wp_enqueue_script('jquery');

function cfga_setup_js() {
	header('Content-type: text/javascript');
?>

var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));

<?
	die();
}
wp_enqueue_script('cfga_setup_js', trailingslashit(get_bloginfo('url')).'?cf_action=cfga_setup_js', array('jquery'));

function cfga_insert_accounts_js() {
	header('Content-type: text/javascript');
?>

try {
<?php
	echo cfga_get_global_tracker_accounts();
	echo cfga_get_local_tracker_accounts()
?>
	var pageTracker = _gat._getTracker("UA-11109524-1");
	pageTracker._trackPageview();
} 
catch(err) {}

<?
	die();
}
wp_enqueue_script('cfga_insert_accounts_js', trailingslashit(get_bloginfo('url')).'?cf_action=cfga_insert_accounts_js', array('jquery'));

function cfga_admin_js() {
	header('Content-type: text/javascript');
// TODO
	die();
}

wp_enqueue_script('cfga_admin_js', trailingslashit(get_bloginfo('url')).'?cf_action=cfga_admin_js', array('jquery'));


/*
$example_settings = array(
	'key' => array(
		'type' => 'int',
		'label' => 'Label',
		'default' => 5,
		'help' => 'Some help text here',
	),
	'key' => array(
		'type' => 'select',
		'label' => 'Label',
		'default' => 'val',
		'help' => 'Some help text here',
		'options' => array(
			'value' => 'Display'
		),
	),
);
*/
$cfga_settings = array(
	'cfga_local_tracking_codes' => array(
		'name' => '_cfga_local_tracking_codes',
		'type' => 'block',
		'label' => 'Google Analytics Tracking Codes for this Blog',
		'block_label' => 'Google Analytics Tracking Codes for this Blog',
		'default' => '',
		'help' => 'These tracking codes will aply to this blog only, and not to ',
		'items' => array(
			array(
				'name' => '_tracking_code',
				'type' => 'text',
				'label' => 'Enter a Google Analytics web property ID (Should be in the form of "UA-xxxxxx-x")'
			)
		)
	),
	'cfga_global_tracking_codes' => array(
		'name' => '_cfga_global_tracking_codes',
		'type' => 'block',
		'label' => 'Google Analytics Tracking Codes for the Site',
		'default' => 5,
		'help' => '',
		'block_label' => 'Google Analytics Tracking Codes for the Site',
		'items' => array(
			array(
				'name' => '_tracking_code',
				'type' => 'text',
				'label' => 'Enter a Google Analytics web property ID (Should be in the form of "UA-xxxxxx-x")'
			)
		),
	)
);

function cfga_setting($option) {
	$value = get_option($option);
	if (empty($value)) {
		global $cfga_settings;
		$value = $cfga_settings[$option]['default'];
	}
	echo '<h3>setting '.$option.'</h3><pre>'.print_r($value,TRUE).'</pre>';
	return $value;
}

function cfga_admin_menu() {
	if (current_user_can('manage_options')) {
		add_options_page(
			__('Configure Google Analytics', '')
			, __('cf mu google analytics', '')
			, 10
			, basename(__FILE__)
			, 'cfga_settings_form'
		);
	}
}
add_action('admin_menu', 'cfga_admin_menu');

function cfga_plugin_action_links($links, $file) {
	$plugin_file = basename(__FILE__);
	if (basename($file) == $plugin_file) {
		$settings_link = '<a href="options-general.php?page='.$plugin_file.'">'.__('Settings', '').'</a>';
		array_unshift($links, $settings_link);
	}
	return $links;
}
add_filter('plugin_action_links', 'cfga_plugin_action_links', 10, 2);

if (!function_exists('cf_settings_field')) {
	function cf_settings_field($key, $config) {
		$option = get_option($key);
		$option = maybe_unserialize($option);
		echo '<h3>key:</h3><p>'.$key.'</p>';
		echo '<h3>Option:</h3><pre>'.print_r($option,TRUE).'</pre>';
		if (empty($option) && !empty($config['default'])) {
			$option = $config['default'];
		}
		$label = '<label for="'.$key.'">'.$config['label'].'</label>';
		$help = '<span class="help">'.$config['help'].'</span>';
		switch ($config['type']) {
			case 'select':
				$output = $label.'<select name="'.$key.'" id="'.$key.'">';
				foreach ($config['options'] as $val => $display) {
					$option == $val ? $sel = ' selected="selected"' : $sel = '';
					$output .= '<option value="'.$val.'"'.$sel.'>'.htmlspecialchars($display).'</option>';
				}
				$output .= '</select>'.$help;
				break;
			case 'textarea':
				$output = $label.'<textarea name="'.$key.'" id="'.$key.'">'.htmlspecialchars($option).'</textarea>'.$help;
				break;
			case 'block':
	// 			$output = '
	// <div class="postbox " id="cf_meta_related-posts_container">
	// 	<div title="Click to toggle" class="handlediv"><br/></div>
	// 	<h3 class="hndle"><span>Related Posts/Pages</span></h3>
	// 	<div class="inside">
	// 		<input type="hidden" value="1" name="cf_meta_active"/>
	// 		<p/>
	// 		<div class="cf_meta_set">
	// 			';
				$block = new cf_input_block($config);
				$output .= $block->display();
	// 			$output .= '
	// 		</div>
	// 	</div>
	// </div>
	// 			';
				break;
			case 'string':
			case 'int':
			default:
				$output = $label.'<input name="'.$key.'" id="'.$key.'" value="'.htmlspecialchars($option).'" />'.$help;
				break;
		}
		return '<div class="option">'.$output.'<div class="clear"></div></div>';
	}
}

function cfga_settings_form() {
	global $cfga_settings;


	print('
<div class="wrap">
	<h2>'.__('Configure Google Analytics', '').'</h2>
	<form id="cfga_settings_form" name="cfga_settings_form" action="'.get_bloginfo('wpurl').'/wp-admin/options-general.php" method="post">
		<input type="hidden" name="cf_action" value="cfga_update_settings" />
		<fieldset class="options">
	');
	foreach ($cfga_settings as $key => $config) {
		if (current_user_can('manage_options') || $key != 'cfga_global_tracking_codes') {
			echo cf_settings_field($key, $config);
		}
		
	}
	print('
		</fieldset>
		<p class="submit">
			<input type="submit" name="submit" value="'.__('Save Settings', '').'" class="button-primary" />
		</p>
	</form>
</div>
	');
}

function cfga_save_settings() {
	if (!current_user_can('manage_options')) {
		return;
	}
	global $cfga_settings;
	foreach ($cfga_settings as $key => $option) {
		$value = '';
		switch ($option['type']) {
			case 'int':
				$value = intval($_POST[$key]);
				break;
			case 'select':
				$test = stripslashes($_POST[$key]);
				if (isset($option['options'][$test])) {
					$value = $test;
				}
				break;
			case 'block':
				echo '<h3>submited value</h3><pre>'.print_r($_POST['blocks']['_'.$key], TRUE).'</pre>';
				$value = serialize($_POST['blocks']['_'.$key]);
				echo '<p>'.$value.'</p>';
				// wp_die('');
				# code...
				break;
			case 'string':
			case 'textarea':
			default:
				$value = stripslashes($_POST[$key]);
				break;
		}
		update_option($key, $value);
	}
}

//a:22:{s:11:"plugin_name";s:22:"cf-mu-google-analytics";s:10:"plugin_uri";s:24:"http://crowdfavorite.com";s:18:"plugin_description";s:90:"Allows Network sites to have multiple google analytics accounts tracking at the same time.";s:14:"plugin_version";s:3:".25";s:6:"prefix";s:4:"cfga";s:12:"localization";N;s:14:"settings_title";s:26:"Configure Google Analytics";s:13:"settings_link";s:22:"cf mu google analytics";s:4:"init";s:1:"1";s:7:"install";s:1:"1";s:9:"post_edit";b:0;s:12:"comment_edit";b:0;s:6:"jquery";s:1:"1";s:6:"wp_css";b:0;s:5:"wp_js";b:0;s:9:"admin_css";b:0;s:8:"admin_js";s:1:"1";s:15:"request_handler";b:0;s:6:"snoopy";b:0;s:11:"setting_cat";b:0;s:14:"setting_author";b:0;s:11:"custom_urls";b:0;}

?>