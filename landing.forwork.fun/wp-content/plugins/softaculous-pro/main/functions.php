<?php

if(!defined('ABSPATH')){
	die('HACKING ATTEMPT!');
}

$softaculous_pro_settings = get_option('softaculous_pro_settings', array());

if(!empty($softaculous_pro_settings['disable_comments'])){
	include_once SOFTACULOUS_PRO_PLUGIN_PATH . '/main/disable-comments.php';
}

$spro_page = softaculous_pro_optGET('page');
if(is_admin() && !empty($spro_page) && $spro_page == 'assistant'){
	$spro_act = softaculous_pro_optGET('act');
	if(!empty($spro_act) && $spro_act == 'onboarding'){
		include_once(SOFTACULOUS_PRO_PLUGIN_PATH . '/main/onboarding.php');
	}
}

function softaculous_pro_activation_hook(){
	softaculous_pro_update_check();
}

function softaculous_pro_deactivation_hook() {
	
}

function softaculous_pro_uninstall_hook() {
	delete_option('softaculous_pro_version');
	delete_option('softaculous_pro_settings');
	delete_option('softaculous_pro_onboarding_notice_dismiss');
	delete_option('softaculous_pro_onboarding_done');
	delete_option('softaculous_pro_onboarding_dismiss');
	delete_option('softaculous_pro_setup_info');
	delete_option('softaculous_pro_onboarding_shown');
}

function softaculous_pro_load_plugin(){
	
	global $softaculous_pro;
	
	// Do we have to redirect the user to onboarding ? 
	if(is_admin() && current_user_can('administrator') && !wp_doing_ajax() && (empty($_GET['act']) || ($_GET['act'] != 'onboarding' && $_GET['act'] != 'license'))){
		
		$spro_is_dismissed = get_option('softaculous_pro_onboarding_dismiss');
		$spro_is_done = get_option('softaculous_pro_onboarding_done');
		$spro_is_shown = get_option('softaculous_pro_onboarding_shown');
		
		if(empty($spro_is_dismissed) && empty($spro_is_done) && empty($spro_is_shown)){
			wp_safe_redirect(admin_url('admin.php?page=assistant&act=onboarding'));
			exit(0);
		}
	}
	
	softaculous_pro_load_license();
	
	softaculous_pro_rebranding();
	
	// Show key details in Plugins data
	add_filter('plugin_row_meta', 'softaculous_pro_add_plugin_row_links', 10, 2);
	
	// Check for updates
	include_once(SOFTACULOUS_PRO_PLUGIN_PATH.'/lib/plugin-update-checker.php');
	$softaculous_pro_updater = SoftaculousPro_PucFactory::buildUpdateChecker(softaculous_pro_api_url().'updates.php?version='.SOFTACULOUS_PRO_VERSION.'&url='.rawurlencode(site_url()), SOFTACULOUS_PRO_FILE);
	
	// Add the license key to query arguments
	$softaculous_pro_updater->addQueryArgFilter('softaculous_pro_updater_filter_args');
	
	// Show the text to install the license key
	add_filter('puc_manual_final_check_link-softaculous-pro', 'softaculous_pro_updater_check_link', 10, 1);
	
	softaculous_pro_update_check();
	
	// Enqueues scripts and styles
	add_action('admin_enqueue_scripts', 'softaculous_pro_enqueue_scripts');

	add_action('admin_init', 'softaculous_pro_admin');
	
	if(is_admin() && !empty($_GET['page']) && $_GET['page'] == 'assistant'){
		add_filter('screen_options_show_screen', '__return_false');
	}
	
	add_action('admin_notices', 'softaculous_pro_license_notice');
	
	// Are you the Admin ?
	if(current_user_can('administrator')){
	
		if(softaculous_pro_is_display_notice()){
			add_action('admin_notices', 'softaculous_pro_admin_notice');
		}
		
		add_action('wp_ajax_softaculous_pro_dismissnotice', 'softaculous_pro_dismiss_notice');
		
		// This adds the left menu in WordPress Admin page
		add_action('admin_menu', 'softaculous_pro_admin_menu', 5);
	
		include_once SOFTACULOUS_PRO_PLUGIN_PATH . '/main/admin.php';
		add_action('admin_print_footer_scripts', 'softaculous_pro_assistant', 5);
		
		add_action('wp_ajax_softaculous_pro_wp_ajax', 'softaculous_pro_wp_ajax');
		
		add_action('admin_menu', 'softaculous_pro_remove_admin_elements');

		// Template Installation related ajax calls
		add_action('wp_ajax_softaculous_pro_template_info', 'softaculous_pro_templates_ajax');
		add_action('wp_ajax_softaculous_pro_start_install_template', 'softaculous_pro_templates_ajax');
		add_action('wp_ajax_softaculous_pro_selected_plugin_install', 'softaculous_pro_templates_ajax');
		add_action('wp_ajax_softaculous_pro_download_template', 'softaculous_pro_templates_ajax');
		add_action('wp_ajax_softaculous_pro_import_template', 'softaculous_pro_templates_ajax');
		
		// Setup information
		add_action('wp_ajax_softaculous_pro_setup_info', 'softaculous_pro_templates_ajax');
		
		// dismiss
		add_action('wp_ajax_softaculous_pro_onboarding_dismiss', 'softaculous_pro_templates_ajax');
		
		add_action('wp_ajax_softaculous_pro_option_value', 'softaculous_pro_templates_ajax');
		
	}
}

function softaculous_pro_templates_ajax() {
	
	if(!current_user_can('administrator')){
		return false;
	}

	include_once(dirname(__FILE__).'/onboarding.php');
	
	if($_REQUEST['action'] == 'softaculous_pro_template_info'){
		softaculous_pro_ajax_template_info();
	}
	
	if($_REQUEST['action'] == 'softaculous_pro_selected_plugin_install'){
		softaculous_pro_ajax_selected_plugin();
	}
	
	if($_REQUEST['action'] == 'softaculous_pro_start_install_template'){
		softaculous_pro_ajax_start_install_template();
	}
	
	if($_REQUEST['action'] == 'softaculous_pro_download_template'){
		softaculous_pro_ajax_download_template();
	}
	
	if($_REQUEST['action'] == 'softaculous_pro_import_template'){
		softaculous_pro_ajax_import_template();
	}
	
	if($_REQUEST['action'] == 'softaculous_pro_setup_info'){
		softaculous_pro_save_setup_info();
	}
	
	if($_REQUEST['action'] == 'softaculous_pro_option_value'){
		softaculous_pro_get_options();
	}
	
	if($_REQUEST['action'] == 'softaculous_pro_onboarding_dismiss'){
		softaculous_pro_onboarding_dismiss();
	}

}

function softaculous_pro_remove_admin_elements(){
	
	if(!empty($_GET['page']) && $_GET['page'] === 'assistant'){
		remove_all_actions('admin_notices');
		remove_all_actions('all_admin_notices');
	}
}

// Update check
function softaculous_pro_update_check(){

	$current_version = get_option('softaculous_pro_version', 0);
	$version = (int) str_replace('.', '', $current_version);

	if($current_version == SOFTACULOUS_PRO_VERSION){
		return true;
	}

	// Save the new Version
	update_option('softaculous_pro_version', SOFTACULOUS_PRO_VERSION);
}

function softaculous_pro_admin(){
	
	global $spro_tours;
	
	include_once SOFTACULOUS_PRO_PLUGIN_PATH . '/main/admin.php';
	
	$spro_tours = array('assistant' => 'admin.php?page=assistant', 'sidebar' => 'admin.php?page=assistant', 'dashboard' => 'index.php', 'plugins' => 'plugins.php', 'themes' => 'themes.php', 'users' => 'users.php', 'pages' => 'edit.php?post_type=page', 'posts' => 'edit.php');
}

// Shows the admin menu of Softaculous
function softaculous_pro_admin_menu() {
	
	$capability = 'activate_plugins';// TODO : Capability for accessing this page

	// Add the menu page
	add_menu_page(__('Assistant'), __('Assistant', 'softaculous-pro'), $capability, 'assistant', 'softaculous_pro_page_handler', 'dashicons-businessperson', 1);
	
}

// The Softaculous Settings Page
function softaculous_pro_page_handler(){
	
	global $softaculous_pro;

	if(!current_user_can('manage_options')){
		wp_die('Sorry, but you do not have permissions to change settings.');
	}
	
	$act = softaculous_pro_optGET('act');
	
	switch($act){
		
		case 'onboarding':
		include_once(SOFTACULOUS_PRO_PLUGIN_PATH . '/main/setup.php');
		break;
		
		case 'license':
		include_once(SOFTACULOUS_PRO_PLUGIN_PATH . '/main/license.php');
		softaculous_pro_license();
		break;
		
		case 'manage-plugins':
		include_once(SOFTACULOUS_PRO_PLUGIN_PATH . '/main/manage-plugins.php');
		softaculous_pro_manage_plugins();
		break;
		
		default:
		include_once(dirname(__FILE__).'/admin.php');
		softaculous_pro_page_settings();
	}

}

function softaculous_pro_enqueue_scripts(){
	
	wp_enqueue_style('softaculous-pro-style-admin', SOFTACULOUS_PRO_PLUGIN_URL . '/assets/css/admin.css', [], SOFTACULOUS_PRO_VERSION);

	wp_enqueue_style('softaculous-pro-style-intro', SOFTACULOUS_PRO_PLUGIN_URL . '/assets/css/intro.css', [], SOFTACULOUS_PRO_VERSION);
	
	wp_enqueue_style( 'softaculous-pro-style-font-awesome', SOFTACULOUS_PRO_PLUGIN_URL . '/assets/font-awesome/css/all.min.css', [], SOFTACULOUS_PRO_VERSION, 'all' );

	wp_enqueue_script('softaculous-pro-script-admin', SOFTACULOUS_PRO_PLUGIN_URL . '/assets/js/admin.js', [], SOFTACULOUS_PRO_VERSION, true);

	wp_enqueue_script('softaculous-pro-script-intro', SOFTACULOUS_PRO_PLUGIN_URL . '/assets/js/intro.js', [], SOFTACULOUS_PRO_VERSION, false);  

	wp_enqueue_script('softaculous-pro-script-jquery', SOFTACULOUS_PRO_PLUGIN_URL . '/assets/js/jquery.js', [], SOFTACULOUS_PRO_VERSION, false);
	
	wp_localize_script('softaculous-pro-script-admin', 'soft_pro_obj', array(
		'admin_url' => esc_url(admin_url()),
		'site_url' => esc_url(site_url()),
		'nonce' => wp_create_nonce('softaculous_pro_js_nonce'),
		'ajax_url' => admin_url('admin-ajax.php')
	));
}

/**
 * Display Softaculous notice on the basis of last dismiss date. When user manually dismisses the notice, it remains for 1 month
 *
 * @since		1.0
 */
function softaculous_pro_is_display_notice(){
	
	$soft_dismissable_notice_date = get_option('softaculous_pro_dismiss_notice_date');
	
	if(empty($soft_dismissable_notice_date)){
		return true;
	}
	
	$soft_dismissable_notice_date2 = new DateTime($soft_dismissable_notice_date);
	$current_date = new DateTime(date('Y-m-d'));
	$date_diff_month = $soft_dismissable_notice_date2->diff($current_date);

	//Do not display notice again for a month
	if($date_diff_month->m < 1){
		return false;
	}
	
	return true;
}

/**
 * Display Softaculous notice in dashboard
 *
 * @since		1.0
 */
function softaculous_pro_admin_notice($force = 0){
	
	if(!empty($_GET['page']) && $_GET['page'] == 'softaculous' && empty($force)){
		return '';
	}
	
	return '';
}

function softaculous_pro_license_notice(){
	
	global $softaculous_pro;
	
	if(is_admin() && current_user_can('activate_plugins') && !wp_doing_ajax()){
		
		if(empty($softaculous_pro['license']['license'])){
			$msg = sprintf(__('Your Softaculous Pro plugin is %1$s. Please enter the license key <a href="%2$s">here</a>.', 'softaculous-pro'), 
						'<font style="color:red;"><b>Unlicensed</b></font>',
						admin_url('admin.php?page=assistant&act=license')
						);
		}else{
			
			if(!empty($softaculous_pro['license']['url_mismatch'])){
				$msg = sprintf(__('Your Softaculous Pro plugin license is <b><font style="color:red;">not authorized</font></b> to be used on  <b><i>%1$s</i></b> or you have reached the maximum number of sites allowed for this license. Please upgrade your license <a href="%2$s" target="_blank">here</a>.', 'softaculous-pro'), 
							site_url(),
							SOFTACULOUS_PRO_BUY
							);
			}elseif(empty($softaculous_pro['license']['active'])){
				$msg = sprintf(__('Your Softaculous Pro plugin license has %1$s. Please renew your license for uninterrupted updates and support.', 'softaculous-pro'), 
							'<font style="color:red;"><b>Expired</b></font>'
							);
			}
		}
		
		if(!empty($msg)){
			echo '
			<div class="notice notice-error is-dismissible">
				<p>'.$msg.'</p>
			</div>';
		}
		
		// The notice to complete onboarding
		//$spro_is_shown = get_option('softaculous_pro_onboarding_shown');
		$spro_is_done = get_option('softaculous_pro_onboarding_done');
		$spro_is_dismissed = get_option('softaculous_pro_onboarding_notice_dismiss');
		
		if(empty($spro_is_done) && empty($spro_is_dismissed)){
			echo '
			<div class="notice notice-error is-dismissible" id="softaculous-onboarding-notice">
				<p>Your Onboarding process is not completed yet! <a href="admin.php?page=assistant&act=onboarding" target="_blank">Click here</a> to complete the simple setup process and build your site in a few steps.</p>
			</div>
			<script type="text/javascript">
				jQuery(document).ready(function($){
					$("#softaculous-onboarding-notice").on("click", ".notice-dismiss", function() {
						// Send an AJAX request to the server to dismiss the notice
						jQuery.ajax({
							type: "post",
							url: soft_pro_obj.ajax_url,
							data: {
								action: "softaculous_pro_wp_ajax",
								softaculous_pro_onboarding_notice_dismiss: 1,
								softaculous_pro_security: soft_pro_obj.nonce,
								data: [],
							},
						});
					});
				});
			</script>
			';
		}
		
	}
	
	return '';
}

function softaculous_pro_get_option($option_name, $default_value = false, $site_id = null){
	
    if($site_id !== null && is_multisite()){
        return get_site_option($option_name, $default_value);
    }
    return get_option($option_name, $default_value);
}

/**
 * Takes care of Slashes
 *
 * @param		string $string The string that will be processed
 * @return		string A string that is safe to use for Database Queries, etc
 * @since		1.0
 */
function softaculous_pro_inputsec($string){

	//get_magic_quotes_gpc is deprecated in php 7.4
	if(version_compare(PHP_VERSION, '7.4', '<')){
		if(!get_magic_quotes_gpc()){
		
			$string = addslashes($string);
		
		}else{
		
			$string = stripslashes($string);
			$string = addslashes($string);
		
		}
	}else{
		$string = addslashes($string);
	}
	
	// This is to replace ` which can cause the command to be executed in exec()
	$string = str_replace('`', '\`', $string);
	
	return $string;

}

/**
 * Converts Special characters to html entities
 *
 * @param        string $string The string containing special characters
 * @return       string A string containing special characters replaced by html entities of the format &#ASCIICODE;
 * @since     	 1.0
 */
function softaculous_pro_htmlizer($string){

	$string = htmlentities($string, ENT_QUOTES, 'UTF-8');
	
	preg_match_all('/(&amp;#(\d{1,7}|x[0-9a-fA-F]{1,6});)/', $string, $matches);
	
	foreach($matches[1] as $mk => $mv){		
		$tmp_m = softaculous_pro_entity_check($matches[2][$mk]);
		$string = str_replace($matches[1][$mk], $tmp_m, $string);
	}
	
	return $string;
	
}

/**
 * Used in function htmlizer()
 *
 * @param        string $string
 * @return       string
 * @since     	 1.0
 */
function softaculous_pro_entity_check($string){
	
	//Convert Hexadecimal to Decimal
	$num = ((substr($string, 0, 1) === 'x') ? hexdec(substr($string, 1)) : (int) $string);
	
	//Squares and Spaces - return nothing 
	$string = (($num > 0x10FFFF || ($num >= 0xD800 && $num <= 0xDFFF) || $num < 0x20) ? '' : '&#'.$num.';');
	
	return $string;
			
}

/**
 * OPTIONAL REQUEST of the given REQUEST Key
 *
 * @param        string $name The key of the $_REQUEST array i.e. the name of the input / textarea text 
 * @param        string $default The value to return if the $_REQUEST[$name] is NOT SET
 * @return       string Returns the string if the REQUEST is there otherwise the default value given.
 * @since     	 1.0
 */
function softaculous_pro_optREQ($name, $default = ''){

global $softaculous_error;

	//Check the POSTED NAME was posted
	if(isset($_REQUEST[$name])){
	
		return trim(sanitize_text_field($_REQUEST[$name]));
		
	}else{
		return $default;
	}
}

/**
 * OPTIONAL POST of the given POST Key
 *
 * @param        string $name The key of the $_POST array i.e. the name of the input / textarea text 
 * @param        string $default The value to return if the $_POST[$name] is NOT SET
 * @return       string Returns the string if the POST is there otherwise the default value given.
 * @since		1.4.6
 */
function softaculous_pro_optPOST($name, $default = ''){

global $softaculous_error;

	//Check the POSTED NAME was posted
	if(isset($_POST[$name])){
		
		if(is_array($_POST[$name])){
			$values = array_map('trim', $_POST[$name]);
			return array_map('sanitize_text_field', $values);
		}
	
		return trim(sanitize_text_field($_POST[$name]));
		
	}else{
		return $default;
	}

}

/**
 * OPTIONAL GET of the given GET Key i.e. dont throw a error if not there
 *
 * @param        string $name The key of the $_GET array i.e. the name of the input / textarea text 
 * @param        string $default The value to return if the $_GET[$name] is NOT SET
 * @return       string Returns the string if the GET is there otherwise the default value given.
 * @since     	 1.0
 */
function softaculous_pro_optGET($name, $default = ''){

global $softaculous_error;

	//Check the GETED NAME was GETed
	if(isset($_GET[$name])){
	
		return trim(sanitize_text_field($_GET[$name]));
		
	}else{
		return $default;
	}

}
	
function softaculous_pro_sp_api_url($main_server = 0){
	
	global $softaculous_pro;
	
	return softaculous_pro_api_url($main_server, 'sitepad');
	
}
	
function softaculous_pro_pfx_api_url($main_server = 0){
	
	global $softaculous_pro;
	
	return softaculous_pro_api_url($main_server, 'popularfx');
	
}
	
function softaculous_pro_api_url($main_server = 0, $suffix = ''){
	
	global $softaculous_pro;
	
	$r = array(
		'https://s0.softaculous.com/a/softwp/',
		'https://s1.softaculous.com/a/softwp/',
		'https://s2.softaculous.com/a/softwp/',
		'https://s3.softaculous.com/a/softwp/',
		'https://s4.softaculous.com/a/softwp/',
		'https://s5.softaculous.com/a/softwp/',
		'https://s7.softaculous.com/a/softwp/',
		'https://s8.softaculous.com/a/softwp/'
	);
	
	$mirror = $r[array_rand($r)];
	
	// If the license is newly issued, we need to fetch from API only
	if(!empty($main_server) || empty($softaculous_pro['license']['last_edit']) || 
		(!empty($softaculous_pro['license']['last_edit']) && (time() - 3600) < $softaculous_pro['license']['last_edit'])
	){
		$mirror = 'https://a.softaculous.com/softwp/';
	}
	
	// -1 indicates that we need to force the mirror server used for rendering static files e.g. screenshots
	if(!empty($main_server) && $main_server == '-1'){
		$mirror = $r[array_rand($r)];
	}
	
	if(!empty($suffix)){
		$mirror = str_replace('/softwp', '/'.$suffix, $mirror);
	}
	
	return $mirror;
	
}

// Load license data
function softaculous_pro_load_license(){
	
	global $softaculous_pro;

	// Load license
	$softaculous_pro['license'] = get_option('softaculous_pro_license', array());
	
	if(empty($softaculous_pro['license'])){
		return false;
	}
	
	// Update license details as well
	if(empty($softaculous_pro['license']['last_update']) || 
		(!empty($softaculous_pro['license']['last_update']) && (time() - $softaculous_pro['license']['last_update']) >= 86400)
	){
		
		$resp = wp_remote_get(softaculous_pro_api_url(1).'license.php?license='.$softaculous_pro['license']['license'].'&url='.rawurlencode(site_url()));
		
		// Did we get a response ?
		if(is_array($resp)){
			
			$tosave = json_decode($resp['body'], true);
			
			// Is it the license ?
			if(!empty($tosave['license'])){
				$tosave['last_update'] = time();
				update_option('softaculous_pro_license', $tosave);
				$softaculous_pro['license'] = $tosave;
			}
			
		}
		
	}
	
	return $softaculous_pro['license'];
}

// Load Softaculous rebranding settings
function softaculous_pro_rebranding(){
	
	global $softaculous_pro;
	
	$softaculous_pro['branding']['sn'] = 'Softaculous Pro';
	$softaculous_pro['branding']['logo_url'] = SOFTACULOUS_PRO_PLUGIN_URL.'assets/images/logo-white.png';
	$softaculous_pro['branding']['rebranded'] = 0;

	//Getting info if Softaculous rebranding done or not?
	$soft_rebranding = get_option('softaculous_pro_rebranding', '[]');
	
	if(!empty($soft_rebranding['logo_url'])){
		$softaculous_pro['branding']['logo_url'] = $soft_rebranding['logo_url'];
	}
	
	if(!empty($soft_rebranding['sn']) && $soft_rebranding['sn'] != 'Softaculous'){
		$softaculous_pro['branding']['sn'] = $soft_rebranding['sn'];
		$softaculous_pro['branding']['rebranded'] = 1;
	}
	
	if(!empty($soft_rebranding['default_hf_bg'])){
		$softaculous_pro['branding']['default_hf_bg'] = $soft_rebranding['default_hf_bg'];
	}
	
	if(!empty($soft_rebranding['default_hf_text'])){
		$softaculous_pro['branding']['default_hf_text'] = $soft_rebranding['default_hf_text'];
	}
	
	return true;
}

// Add our license key if ANY
function softaculous_pro_updater_filter_args($queryArgs) {
	
	global $softaculous_pro;
	
	if ( !empty($softaculous_pro['license']['license']) ) {
		$queryArgs['license'] = $softaculous_pro['license']['license'];
	}
	
	return $queryArgs;
}

// Handle the Check for update link and ask to install license key
function softaculous_pro_updater_check_link($final_link){
	
	global $softaculous_pro;
	
	if(empty($softaculous_pro['license']['license'])){
		return '<a href="'.admin_url('admin.php?page=assistant&act=license').'">Enter Pro License Key</a>';
	}
	
	return $final_link;
}

function softaculous_pro_report_error($error = array()) {
	
	if(empty($error)){
		return true;
	}
	
	$error_string = '<b>' . esc_html__('Please fix the below error(s):', 'softaculous-pro') . '</b><br />';
	
	foreach($error as $ev){
		$error_string .= '* ' . esc_html($ev) . '<br />';
	}
	
	echo '<div id="message" class="error"><p>' . $error_string . '</p></div>';
}

function softaculous_pro_add_plugin_row_links($links, $slug) {

	if(is_multisite() && is_network_admin()){
		return $links;
	}

	if ($slug !== SOFTACULOUS_PRO_BASE) {
		return $links;
	}

	if(!current_user_can('activate_plugins')){
		return $links;
	}
	
	$new_links = array(
		'plugins' => '<a href="admin.php?page=assistant&act=manage-plugins"><font style="color:green;">Explore Pro Plugins</font></a>',
		'tours' => '<a href="admin.php?page=assistant#tours">Tour</a>',
		'ai' => '<a href="admin.php?page=assistant#ai"><font style="color:green;">AI</font></a>',
	);

	$links = array_merge($links, $new_links);

	return $links;
}

function softaculous_pro_add_params($link){
	
	global $softaculous_pro;
	
	$link = rtrim($link, '?&');

	$query = parse_url($link, PHP_URL_QUERY);

	if ($query) {
		$link .= '&';
	} else {
		$link .= '?';
	}

	$link .= 'version=latest&license='.$softaculous_pro['license']['license'].'&url='.site_url();
	
	return $link;
}