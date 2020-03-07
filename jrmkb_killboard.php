<?php
/*
Plugin Name: JRM Killboard
Description: Killboard for Eve Online Killmails - Plugin allows to store and display your corporation kills using the Killmail system. They can be synched manually or automatically via the ESI API ( please read the instruction to use the ESI API ). Lots of customization allows to display your killboard in the way you like it. Developed by jrmarco ( Pillar Omaristos ). Fly safe capsuler!
Version: 1.3.1
Author: jrmarco
Author URI: https://bigm.it
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html#content
Text Domain: jrm_killboard
Domain Path: /languages
*/

defined( 'ABSPATH' ) or die( 'Fly safe Capsuler!' );

define( 'JRM_KILLBOARD_VERSION', '1.3.1' );

$dummyDescription = __('Killboard for Eve Online Killmails - Plugin allows to store and display your corporation kills using the Killmail system. They can be synched manually or automatically via the ESI API ( please read the instruction to use the ESI API ). Lots of customization allows to display your killboard in the way you like it. Developed by jrmarco ( Pillar Omaristos ). Fly safe capsuler!');

// Include required JRM classes
if(is_admin()) {
    include plugin_dir_path(__FILE__).'includes/class.jrmkillboard.php';
    include plugin_dir_path(__FILE__).'includes/class.jrmkillboardwphelper.php';
}
include plugin_dir_path(__FILE__).'includes/class.jrmkillboardfe.php';

// Load translations
load_plugin_textdomain( 'jrm_killboard', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

// Attach activation/uninstall hooks
register_activation_hook( __FILE__, 'jrm_killboard_plugin_activation');
register_uninstall_hook( __FILE__, 'jrm_killboard_plugin_uninstall');

// Inlcude JS script only for plugin pages
if(isset($_GET['page']) && in_array($_GET['page'],['jrmevekillboard_main','jrmevekillboard_settings','jrmevekillboard_graphics','jrmevekillboard_items'])) {
    add_action('admin_enqueue_scripts', 'jrm_killboard_enqueue_script');
}

// Init JRMKillboardFE class for frontend
add_action('plugins_loaded', array( 'JRMKillboardFE', 'get_instance' ) );

// Include all ajax callbacks for admin section
if ( is_admin() ) {
    add_action('admin_menu', 'jrm_killboard_add_admin_menu');
    add_action( 'wp_ajax_jrm_killboard_hide_kill', 'jrm_killboard_hide_kill' );
    add_action( 'wp_ajax_jrm_killboard_remove_kill', 'jrm_killboard_remove_kill' );
    add_action( 'wp_ajax_jrm_killboard_do_sync_price', 'jrm_killboard_do_sync_price' );
    add_action( 'wp_ajax_jrm_killboard_do_sync_worth', 'jrm_killboard_do_sync_worth' );
    add_action( 'wp_ajax_jrm_killboard_do_set_item_price', 'jrm_killboard_do_set_item_price' );
    add_action( 'wp_ajax_jrm_killboard_do_store_settings', 'jrm_killboard_do_store_settings' );
    add_action( 'wp_ajax_jrm_killboard_do_store_graphics_settings', 'jrm_killboard_do_store_graphics_settings' );
    add_action( 'wp_ajax_jrm_killboard_do_upload_killmail', 'jrm_killboard_do_upload_killmail' );
    add_action( 'wp_ajax_jrm_killboard_do_remove_sso_auth', 'jrm_killboard_do_remove_sso_auth' );
    add_action( 'wp_ajax_jrm_killboard_do_get_log', 'jrm_killboard_do_get_log' );
    add_action( 'wp_ajax_jrm_killboard_do_clear_log', 'jrm_killboard_do_clear_log' );
    add_action( 'wp_ajax_jrm_killboard_toggle_kills', 'jrm_killboard_toggle_kills' );
    add_action( 'wp_ajax_jrm_killboard_delete_bulk', 'jrm_killboard_delete_bulk' );
    add_action( 'wp_ajax_jrm_killboard_do_update_items_price', 'jrm_killboard_do_update_items_price' );

    // Check for required updates
    $currentPluginVersion = get_option('jrm_killboard_plugin_version');
    if ($currentPluginVersion != false && $currentPluginVersion != JRM_KILLBOARD_VERSION) {
        jrm_killboard_process_plugin_update();
    }
}

// WP-Cron checks
$killSyncCron = get_option('jrm_killboard_max_sync');
if($killSyncCron !='-1') {
    $time = wp_next_scheduled( 'jrm_killboard_cronjob' );
    if($time == false) {
        jrm_killboard_process_WpCron($killSyncCron);
    }
}

// Include all ajax callbacks
add_action( 'wp_ajax_jrm_killboard_get_table_data', 'jrm_killboard_get_table_data' );
add_action( 'wp_ajax_nopriv_jrm_killboard_get_table_data', 'jrm_killboard_get_table_data' );
add_action( 'wp_ajax_jrm_killboard_load_items', 'jrm_killboard_load_items' );
add_action( 'wp_ajax_nopriv_jrm_killboard_load_items', 'jrm_killboard_load_items' );
add_action( 'jrm_killboard_cronjob', 'jrm_killboard_perform_cron' );

// Generate BE plugin admin menu
function jrm_killboard_add_admin_menu() {
    $icon = plugins_url( dirname( plugin_basename( __FILE__ ) ).'/admin/images/spaceship.png' );
    add_menu_page('JRM Killboard','JRM Killboard','edit_pages','jrmevekillboard_main','', $icon);
    add_submenu_page('jrmevekillboard_main','JRM Killboard',__('Killboard','jrm_killboard'),
                     'edit_pages','jrmevekillboard_main','jrm_killboard_print_admin_page');
    add_submenu_page('jrmevekillboard_main', 'JRM Killboard Settings', __('Configurations','jrm_killboard'),
                     'edit_pages', 'jrmevekillboard_settings','jrm_killboard_print_settings_page');
    add_submenu_page('jrmevekillboard_main', 'JRM Killboard Graphic Settings', __('Graphics','jrm_killboard'),
                     'edit_pages', 'jrmevekillboard_graphics','jrm_killboard_print_graphics_settings_page');
    add_submenu_page('jrmevekillboard_main', 'JRM Killboard Items', __('Items','jrm_killboard'),
                     'edit_pages', 'jrmevekillboard_items','jrm_killboard_print_items');
}

// Function verify existence of php modules, settings and required files
function jrm_killboard_verify_modules_and_files() {
    if(!extension_loaded('curl')) {
        echo __('JRM Killboard Fatal Error :: Php Curl module required','jrm_killboard');
        wp_die();
    }

    if( !ini_get('allow_url_fopen') ) {
        echo __('JRM Killboard Fatal Error :: Php option allow_url_fopen not active','jrm_killboard');
        wp_die();      
    }

    if(!file_exists(plugin_dir_path(__FILE__).'includes/class.jrmkillboardfe.php') || 
        !file_exists(plugin_dir_path(__FILE__).'includes/class.jrmkillboard.php') || 
        !file_exists(plugin_dir_path(__FILE__).'includes/class.jrmkillboardwphelper.php')) {
        echo __('JRM Killboard Fatal Error :: missing required files','jrm_killboard');
        wp_die();   
    }
}

// Init Admin page data & print it
function jrm_killboard_print_admin_page() {
    global $wpdb;

    jrm_killboard_verify_modules_and_files();
    JRMKillboardWPH::mainPage($wpdb);
}

// Init Admin settings page data & print it
function jrm_killboard_print_settings_page() {
    global $wpdb;

    jrm_killboard_verify_modules_and_files();
    JRMKillboardWPH::settingsPage($wpdb);
}

// Display items modal
function jrm_killboard_print_items() {
    global $wpdb;

    jrm_killboard_verify_modules_and_files();
    JRMKillboardWPH::itemsPage($wpdb);
}

// Init Admin graphic settings page data & print it
function jrm_killboard_print_graphics_settings_page() {
    global $wpdb;

    jrm_killboard_verify_modules_and_files();
    JRMKillboardWPH::graphicsPage();
}

// Delete kill from the killboard
function jrm_killboard_remove_kill() {
    $nonce = sanitize_text_field($_POST['check']);
    $id    = intval(sanitize_text_field($_POST['id']));

    if ( ! wp_verify_nonce(  $nonce, 'jrm_killboard_op_nonce' ) || empty($id) ) {
        echo json_encode(['status' => false, 'error' => 'Invalid request']);
    } else {
        global $wpdb;

        $table  = $wpdb->prefix.JRMKillboard::TABKILLBOARD;
        $status = $wpdb->delete( $table, ['killmailId' => $id],['%d']);
        echo json_encode(['status' => $status]);
        JRMKillboard::appendLog('Removed Kill '.$id);
    }
    wp_die();
}

// Hide kill from the killboard
function jrm_killboard_hide_kill() {
    $nonce  = sanitize_text_field($_POST['check']);
    $id     = intval(sanitize_text_field($_POST['id']));
    $toggle = sanitize_text_field($_POST['status']);
    $toggle = ($toggle == 'show') ? true : false;

    if ( ! wp_verify_nonce(  $nonce, 'jrm_killboard_op_nonce' ) || empty($id)) {
        echo json_encode(['status' => false, 'error' => 'Invalid request']);
    } else {
        global $wpdb;

        $table  = $wpdb->prefix.JRMKillboard::TABKILLBOARD;
        $status = $wpdb->update( $table, ['active' => $toggle],['killmailId' => $id],['%d'],['%d'] );
        echo json_encode(['status' => $status]);
    }
    wp_die();
}

// Delete multiple kills
function jrm_killboard_delete_bulk() {
    $nonce  = sanitize_text_field($_POST['check']);
    $rawIds = sanitize_text_field($_POST['ids']);
    $ids = explode(',', $rawIds);

    if ( ! wp_verify_nonce(  $nonce, 'jrm_killboard_op_nonce' ) || empty($ids)) {
        echo json_encode(['status' => false, 'error' => 'Invalid request']);
    } else {
        global $wpdb;

        $table  = $wpdb->prefix.JRMKillboard::TABKILLBOARD;
        foreach ($ids as $id) {
            $wpdb->delete( $table, ['killmailId' => intval($id)],['%d']);
            JRMKillboard::appendLog('Removed Kill '.$id);
        }
        echo json_encode(['status' => true]);
    }
    wp_die();
}

// Toggle multiple kills
function jrm_killboard_toggle_kills() {
    $nonce  = sanitize_text_field($_POST['check']);
    $rawIds = sanitize_text_field($_POST['ids']);
    $ids = explode(',', $rawIds);
    $toggle = sanitize_text_field($_POST['toggle']) == 'show' ? true : false;

    if ( ! wp_verify_nonce(  $nonce, 'jrm_killboard_op_nonce' ) || empty($ids)) {
        echo json_encode(['status' => false, 'error' => 'Invalid request']);
    } else {
        global $wpdb;

        $table  = $wpdb->prefix.JRMKillboard::TABKILLBOARD;
        foreach ($ids as $id) {
            $wpdb->update( $table, ['active' => $toggle],['killmailId' => intval($id)],['%d'],['%d'] );
        }

        echo json_encode(['status' => true]);
    }
    wp_die();
}

// Download json prices from ESI Api
function jrm_killboard_do_sync_price() {
    $nonce = sanitize_text_field($_POST['check']);
    $callback = isset($_POST['callback']) && sanitize_text_field($_POST['callback']) == true;

    if ( ! wp_verify_nonce(  $nonce, 'jrm_killboard_op_nonce' ) ) {
        echo json_encode(['status' => false, 'error' => 'Invalid request']);
    } else {
        if(JRMKillboard::fetchPriceSet()!=false) {
            $time = time();
            update_option('jrm_killboard_lastSync',$time);
            update_option('jrm_killboard_price_log',sprintf(__('Price synced %s','jrm_killboard'),'<br>('.date('Y-m-d H:i:s',$time).')'));
            JRMKillboard::appendLog('Price sync');
            $response = ['status' => true];
            if ($callback) {
                $response['callback'] = wp_create_nonce('jrm_killboard_op_nonce');
            }
            echo json_encode($response);
        } else {
            JRMKillboard::appendLog('Price sync - Permission error file/directory');
            echo json_encode(['status' => false, 'error' => 'uploads/jrm_killboard/:<br>'.__('Permission error file/directory','jrm_killboard')]);
        }
    }

    wp_die();
}

// Calculate kill worth price
function jrm_killboard_do_sync_worth() {
    global $wpdb;

    $nonce = sanitize_text_field($_POST['check']);
    if ( ! wp_verify_nonce(  $nonce, 'jrm_killboard_op_nonce' ) ) {
        echo json_encode(['status' => false, 'error' => 'Invalid request']);
    } else {
        $app = new JRMKillboard($wpdb);
        JRMKillboard::appendLog('Starting worth value');
        $response = $app->calculateKillsWorth();
        JRMKillboard::appendLog('Worth value ended');
        echo json_encode($response);
    }

    wp_die();
}

// Save plugin settings
function jrm_killboard_do_store_settings() {
    $nonce = sanitize_text_field($_POST['check']);
    $postData = jrm_killboard_do_validate_post_data($_POST['settings']);

    if ( !wp_verify_nonce(  $nonce, 'jrm_killboard_op_nonce' ) || !$postData ) {
        echo json_encode(['status' => false, 'error' => 'Invalid request']);
    } else {
        $esiClientId = get_option('jrm_killboard_esi_client_id');
        $esiClientSecret = get_option('jrm_killboard_esi_client_secret');

        $esiClientIdPost = sanitize_text_field($postData['esi_client_id']);
        $esiClientSecretPost = sanitize_text_field($postData['esi_client_secret']);
        /**
         * Esi Client ID & Secret are stored only if they were empty ( on DB ) and 
         * passed data is not empty. To remove them we have a proper function
         */
        if( empty($esiClientId) && empty($esiClientSecret) ) {
            if( !empty($esiClientIdPost) && !empty($esiClientSecretPost) ) {
                update_option('jrm_killboard_esi_client_id', $esiClientIdPost);
                update_option('jrm_killboard_esi_client_secret', $esiClientSecretPost);
            }
        }
        // Update options
        update_option('jrm_killboard_oauth_version',sanitize_text_field($postData['oauth']));
        update_option('jrm_killboard_corporation_id',sanitize_text_field($postData['corporation_id'])); 
        update_option('jrm_killboard_cronjob_endpoint',sanitize_text_field($postData['cron_endpoint'])); 
        update_option('jrm_killboard_cronjob_secret',sanitize_text_field($postData['cron_secret'])); 
        update_option('jrm_killboard_max_sync',sanitize_text_field($postData['max_sync'])); 
        jrm_killboard_process_WpCron($postData['max_sync']);
        JRMKillboard::appendLog('Configurations saved');

        echo json_encode(['status' => true]);
    }

    wp_die();
}

// Save plugin graphics settings
function jrm_killboard_do_store_graphics_settings() {
    $nonce = sanitize_text_field($_POST['check']);
    $postData = jrm_killboard_do_validate_post_data($_POST['settings'],'graphics');

    if ( !wp_verify_nonce(  $nonce, 'jrm_killboard_op_nonce' ) || !$postData ) {
        echo json_encode(['status' => false, 'error' => 'Invalid request']);
    } else {
        // Update options
        update_option('jrm_killboard_title',sanitize_text_field($postData['page_title'])); 
        update_option('jrm_killboard_title_align',sanitize_text_field($postData['title_align'])); 
        update_option('jrm_killboard_elements',sanitize_text_field($postData['elements']));
        update_option('jrm_killboard_be_elements',sanitize_text_field($postData['be_elements']));
        update_option('jrm_killboard_font_size',sanitize_text_field($postData['font_size']));
        update_option('jrm_killboard_image_size',sanitize_text_field($postData['image_size']));
        update_option('jrm_killboard_margin',sanitize_text_field($postData['margin']));
        update_option('jrm_killboard_padding',sanitize_text_field($postData['padding']));
        update_option('jrm_killboard_kills_type',sanitize_text_field($postData['kill_type']));
        update_option('jrm_killboard_kills_bg',sanitize_text_field($postData['bg_kill']));
        update_option('jrm_killboard_kills_text',sanitize_text_field($postData['text_kill']));
        update_option('jrm_killboard_deaths_bg',sanitize_text_field($postData['bg_corporate_kill']));
        update_option('jrm_killboard_deaths_text',sanitize_text_field($postData['text_corporate_kill']));
        update_option('jrm_killboard_title_color',sanitize_text_field($postData['title_color']));
        update_option('jrm_killboard_title_text',sanitize_text_field($postData['title_text']));
        update_option('jrm_killboard_table_header_color',sanitize_text_field($postData['table_header_color']));
        update_option('jrm_killboard_table_header_text',sanitize_text_field($postData['table_header_text']));
        update_option('jrm_killboard_footer_color',sanitize_text_field($postData['footer_color']));
        update_option('jrm_killboard_footer_text',sanitize_text_field($postData['footer_text']));
        update_option('jrm_killboard_inspect_color',sanitize_text_field($postData['inspect_color']));
        update_option('jrm_killboard_inspect_text',sanitize_text_field($postData['inspect_text']));
        update_option('jrm_killboard_cols',$postData['cols']);
        update_option('jrm_killboard_dev_sign',sanitize_text_field($postData['dev_sign']));
        update_option('jrm_killboard_btn_styles',sanitize_text_field($postData['btn_styles']));
        update_option('jrm_killboard_btn_align',sanitize_text_field($postData['btn_align']));
        update_option('jrm_killboard_image_styles',sanitize_text_field($postData['image_styles']));
        update_option('jrm_killboard_last_page',sanitize_text_field($postData['last_page']));
        update_option('jrm_killboard_inspect_items',sanitize_text_field($postData['inspect_items']));
        JRMKillboard::appendLog('Graphics configurations saved');

        echo json_encode(['status' => true]);
    }

    wp_die();
}

// WP-Cron enable selected period for Cronjob
function jrm_killboard_process_WpCron($mode) {
    // Remove existing Wordpress cronjob 
    $timestamp = wp_next_scheduled( 'jrm_killboard_cronjob' );
    wp_unschedule_event( $timestamp, 'jrm_killboard_cronjob' );
    add_action( 'jrm_killboard_cronjob', 'jrm_killboard_perform_cron' );
    // Init Wordpress cronjob based on selection
    switch ($mode) {
        case 24: wp_schedule_event( time()+(60*60), 'hourly', 'jrm_killboard_cronjob' ); break;
        case 12: wp_schedule_event( time()+(12*60*60), 'twicedaily', 'jrm_killboard_cronjob' ); break;
        case 1: wp_schedule_event( time()+(24*60*60), 'daily', 'jrm_killboard_cronjob' ); break;
    }
}

// Process and store killmail
function jrm_killboard_do_upload_killmail() {
    global $wpdb;

    $nonce = sanitize_text_field($_POST['check']);
    $url = esc_url($_POST['link_kill']);

    if ( !wp_verify_nonce(  $nonce, 'jrm_killboard_op_nonce' ) || empty($url) ) {
        echo json_encode(['status' => false, 'error' => 'Invalid request']);
    } else {
        $app = new JRMKillboard($wpdb);
        JRMKillboard::appendLog('Killmail manual load');
        $processing = $app->recordKill($url);
        $response = [
            'status' => $processing->status,
            'error' => isset($processing->error) ? $processing->error : null,
        ];
        echo json_encode($response);
    }

    wp_die();
}

// Validate settings post data
function jrm_killboard_do_validate_post_data($formData, $type = 'settings') {
    // Admitted props only
    $expected = ['oauth','corporation_id', 'cron_secret','max_sync'];
    if ($type == 'graphics') {
        $expected = [
            'elements', 'font_size', 'image_size','kill_type','bg_kill','text_kill',
            'bg_corporate_kill','text_corporate_kill','footer_color','footer_text',
            'cols','dev_sign','last_page','inspect_items','btn_align','title_align'
        ];        
    }
    $posted = array_keys($formData);
    $diff = array_diff($expected,$posted);
    if(!empty($diff)) {
        return false;
    }
    // Cols cannot be blank, if empty we select them all
    if(empty($formData['cols'])) {
        $formData['cols'] = array_keys(JRMKillboard::getTableColumns());
    }

    return $formData;
}

// Remove ESI SSO references
function jrm_killboard_do_remove_sso_auth() {
    delete_option('jrm_killboard_esi_client_id');
    delete_option('jrm_killboard_esi_client_secret');
    delete_option('jrm_killboard_esi_access_token');
    delete_option('jrm_killboard_esi_refresh_token');
    delete_option('jrm_killboard_esi_expires_in');
    JRMKillboard::appendLog('SSO Auth Removed');
}

// Set item price manually
function jrm_killboard_do_set_item_price() {
    global $wpdb;

    $nonce = sanitize_text_field($_POST['check']);
    $id    = sanitize_text_field($_POST['id']);
    $price = sanitize_text_field($_POST['price']);

    if ( ! wp_verify_nonce(  $nonce, 'jrm_killboard_op_nonce' ) || empty($id) || !preg_match("/[0-9]+/",$price) ) {
        echo json_encode(['status' => false, 'error' => __('Invalid request','jrm_killboard')]);
    } else {
        $app = new JRMKillboard($wpdb);
        $table  = $wpdb->prefix.JRMKillboard::TABITEM;
        $price = preg_replace("/[^0-9\.\,]/", '', $price);
        $price = doubleval(str_replace(',', '.', $price));
        $wpdb->update( $table, ['price' => $price, 'manual' => true, 'lastSync' => time() ],['id' => $id],['%f','%d','%d'],['%d'] );
        $item = $app->fetchItem($id);
        $itemName = is_array($item) ? $item['name'] : $item->name;
        JRMKillboard::appendLog("Item {$itemName} set price {$price}");
        echo json_encode(['status' => true]);
    }

    wp_die();
}

// Update all items price
function jrm_killboard_do_update_items_price() {
    global $wpdb;

    $nonce = sanitize_text_field($_POST['check']);

    if ( ! wp_verify_nonce(  $nonce, 'jrm_killboard_op_nonce' ) ) {
        echo json_encode(['status' => false, 'error' => __('Invalid request','jrm_killboard')]);
    } else {
        $app = new JRMKillboard($wpdb);
        $app->updateItemsPrice();
        JRMKillboard::appendLog("Items price updated");
        echo json_encode(['status' => true]);
    }

    wp_die();
}

// Ajax pagination for FE killboard page
function jrm_killboard_get_table_data() {
    global $wpdb;

    JRMKillboardWPH::killboardPager($wpdb);
    wp_die();
}

// Get log
function jrm_killboard_do_get_log() {
    $nonce = sanitize_text_field($_POST['check']);
    if ( !wp_verify_nonce(  $nonce, 'jrm_killboard_op_nonce' ) ) {
        echo json_encode(['status' => false, 'error' => 'Invalid request']);
    }
    $upload     = wp_upload_dir();
    $upload_dir = $upload['basedir'];
    $upload_dir = $upload_dir . JRMKillboard::DATADIR;
    $logData = file_get_contents($upload_dir.'/processing.log');
    echo json_encode(['html' => $logData]);
    wp_die();
}

// Clear log
function jrm_killboard_do_clear_log() {
    $nonce = sanitize_text_field($_POST['check']);
    if ( !wp_verify_nonce(  $nonce, 'jrm_killboard_op_nonce' ) ) {
        echo json_encode(['status' => false, 'error' => 'Invalid request']);
    }
    JRMKillboard::clearLog();
    wp_die();
}

// Cronjob function
function jrm_killboard_perform_cron() {
    global $wpdb;

    include plugin_dir_path(__FILE__).'includes/class.jrmkillboard.php';

    JRMKillboard::appendLog('WP-Cron started');
    $esiStatus = (get_option('jrm_killboard_esi_access_token') && get_option('jrm_killboard_esi_refresh_token'));
    $accessToken = get_option('jrm_killboard_esi_access_token');
    $corporationId = get_option('jrm_killboard_corporation_id');
    if($esiStatus) {
        JRMKillboardFE::syncCorporationKills($wpdb, $corporationId, $accessToken);    
    }
    
    if(JRMKillboard::fetchPriceSet()!=false) {
        $time = time();
        JRMKillboard::appendLog('Sync Price');
        update_option('jrm_killboard_lastSync',$time);
        update_option('jrm_killboard_price_log',sprintf(__('Price synced %s','jrm_killboard'),'<br>('.date('Y-m-d H:i:s',$time).')'));
        delete_option('jrm_killboard_price_error');
    } else {
        $upload = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $upload_dir = $upload_dir . JRMKillboardFE::DATADIR;
        update_option('jrm_killboard_price_error',__('Permission error file/directory','jrm_killboard').
                      ' :: '.$upload_dir.'/price.json');
        delete_option('jrm_killboard_price_log');
    }

    JRMKillboard::appendLog('WP-Cron complete');
}

// Fetch items list for given killmail id
function jrm_killboard_load_items() {
    $nonce = sanitize_text_field($_POST['check']);
    $killmailId = intval(sanitize_text_field($_POST['id']));

    if ( ! wp_verify_nonce(  $nonce, 'jrm_killboard_op_nonce' ) || empty($killmailId) ) {
        echo json_encode(['status' => false, 'error' => 'Invalid request']);
    } else {
        global $wpdb;

        $app = new JRMKillboard($wpdb);
        $itemsList = $app->fetchItemsList($killmailId);
        echo json_encode(['items' => $itemsList]);
    }
    wp_die();
}

// Enqueue CSS/JS Admin file
function jrm_killboard_enqueue_script() {
    wp_register_style( 'jrm_killboard_css_main', plugins_url('/css/bootstrap.min.css', __FILE__ ) , array(), null );
    wp_enqueue_style('jrm_killboard_css_main');
    wp_register_script('jrm_killboard_admin_js', plugins_url('/admin/js/killboard.js' , __FILE__ ), 'jquery', false, true);
    wp_enqueue_script('jrm_killboard_admin_js');
}

// Plugin Activation
function jrm_killboard_plugin_activation() {
    // Create plugin upload folder
    jrm_killboard_upload_folder();
    // Init Db
    JRMKillboard::appendLog('Init DB tables');
    jrm_killboard_initDB();

    // Check for required updates
    $currentPluginVersion = get_option('jrm_killboard_plugin_version');
    if ($currentPluginVersion != false && $currentPluginVersion != JRM_KILLBOARD_VERSION) {
        jrm_killboard_process_plugin_update();
    }

    // Init options
    JRMKillboardWPH::initOptions();
}

// Create plugin upload folder
function jrm_killboard_upload_folder() {    
    $upload     = wp_upload_dir();
    $upload_dir = $upload['basedir'];
    $upload_dir = $upload_dir . JRMKillboard::DATADIR;
    if (! is_dir($upload_dir)) {
        mkdir( $upload_dir, 0755 );
    }
    chmod($upload_dir,0775);
    touch($upload_dir.'/processing.log');
    chmod($upload_dir.'/processing.log',0775);
    JRMKillboard::clearLog();
}

// Create plugin db
function jrm_killboard_initDB() {
    global $wpdb;

    $queries = JRMKillboardWPH::initDB($wpdb);
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    foreach ($queries as $query) {
        dbDelta( $query );
    }
}

// Process plugin upgrade actions
function jrm_killboard_process_plugin_update() {
    $currentVersion = get_option('jrm_killboard_plugin_version');
    switch ($currentVersion) {
        case '1.1':
        case '1.1.1':
            // Fix issue with upload dir folder/file permissions
            $upload     = wp_upload_dir();
            $upload_dir = $upload['basedir'];
            $upload_dir = $upload_dir . JRMKillboard::DATADIR;
            touch($upload_dir.'/processing.log');
            chmod($upload_dir.'/processing.log',0755);
            break;
    }
    update_option('jrm_killboard_plugin_version', JRM_KILLBOARD_VERSION);
}

// Plugin Uninstall
function jrm_killboard_plugin_uninstall() {
    // Remove DB data
    jrm_killboard_deleteDB();
    // Remove plugin folder
    jrm_killboard_remove_folder();
    // Remove options
    JRMKillboardWPH::removeOptions();
}

// Delete tables and data
function jrm_killboard_deleteDB() {
    global $wpdb;

    JRMKillboardWPH::deleteDB($wpdb);
}

// Delete plugin upload folder
function jrm_killboard_remove_folder() {
    $upload     = wp_upload_dir();
    $upload_dir = $upload['basedir'];
    $upload_dir = $upload_dir . JRMKillboard::DATADIR;
    if(file_exists($upload_dir.'/price.json')) {
        unlink($upload_dir.'/price.json');
    }
    if(file_exists($upload_dir.'/processing.log')) {
        unlink($upload_dir.'/processing.log');
    }
    rmdir($upload_dir);
}
