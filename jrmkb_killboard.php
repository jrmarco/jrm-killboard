<?php
/*
Plugin Name: JRM Killboard
Description: Killboard for Eve Online Killmails - Plugin allows to store and display your corporation kills using the Killmail system. They can be synched manually or automatically via the ESI API ( please read the instruction to use the ESI API ). Lots of customization allows to display your killboard in the way you like it. Developed by jrmarco ( Pillar Omaristos ). Fly safe capsuler!
Version: 1.2
Author: jrmarco
Author URI: https://bigm.it
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html#content
Text Domain: jrm_killboard
Domain Path: /languages
*/

defined( 'ABSPATH' ) or die( 'Fly safe Capsuler!' );

define( 'JRM_KILLBOARD_VERSION', '1.2' );

$dummyDescription = __('Killboard for Eve Online Killmails - Plugin allows to store and display your corporation kills using the Killmail system. They can be synched manually or automatically via the ESI API ( please read the instruction to use the ESI API ). Lots of customization allows to display your killboard in the way you like it. Developed by jrmarco ( Pillar Omaristos ). Fly safe capsuler!');

// Include required JRM classes
if(is_admin()) {
    include plugin_dir_path(__FILE__).'includes/class.jrmkillboard.php';
}
include plugin_dir_path(__FILE__).'includes/class.jrmkillboardfe.php';

// Load translations
load_plugin_textdomain( 'jrm_killboard', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

// Attach activation/uninstall hooks
register_activation_hook( __FILE__, 'jrm_killboard_plugin_activation');
register_uninstall_hook( __FILE__, 'jrm_killboard_plugin_uninstall');

// Inlcude JS script only for plugin pages
if(isset($_GET['page']) && in_array($_GET['page'],['jrmevekillboard_main','jrmevekillboard_settings','jrmevekillboard_graphics'])) {
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
    //'dashicons-editor-customchar'
    $icon = plugins_url( dirname( plugin_basename( __FILE__ ) ).'/admin/images/spaceship.png' );
    add_menu_page('JRM Killboard','JRM Killboard','edit_pages','jrmevekillboard_main','', $icon);
    add_submenu_page('jrmevekillboard_main','JRM Killboard',__('Killboard','jrm_killboard'),
                     'edit_pages','jrmevekillboard_main','jrm_killboard_print_admin_page');
    add_submenu_page('jrmevekillboard_main', 'JRM Killboard Settings', __('Configurations','jrm_killboard'),
                     'edit_pages', 'jrmevekillboard_settings','jrm_killboard_print_settings_page');
    add_submenu_page('jrmevekillboard_main', 'JRM Killboard Graphic Settings', __('Graphics','jrm_killboard'),
                     'edit_pages', 'jrmevekillboard_graphics','jrm_killboard_print_graphics_settings_page');
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
        !file_exists(plugin_dir_path(__FILE__).'includes/class.jrmkillboard.php')) {
        echo __('JRM Killboard Fatal Error :: missing required files','jrm_killboard');
        wp_die();   
    }
}

// Init Admin page data & print it
function jrm_killboard_print_admin_page() {
    global $wpdb;

    jrm_killboard_verify_modules_and_files();

    $app = new JRMKillboard($wpdb);

    $totalKills = $app->countTotalKills();

    // Pagination data
    $elementsPerPage = get_option('jrm_killboard_elements');
    $lastPage   = 0;
    if($totalKills!=0) {
        $lastPage = ceil($totalKills/$elementsPerPage);
    }

    $priceSync = get_option('jrm_killboard_lastSync');

    $page       = 0;
    $prev       = $page;
    $next       = $page;

    // Pager
    if(isset($_GET['offset'])) {
        $inputPage = intval($_GET['offset']);
        if(!empty($inputPage)) {
            if($inputPage<0) {
                $page = 0;
            } elseif($inputPage>$lastPage) {
                $page = $lastPage-1;
            } else {
                $page = $inputPage;
            }
        } else {
            $page = 0;
        }
    }

    $prev = $page>0 ? $page-1 : $prev;
    $next = $page<($lastPage-1) ? $page+1 : $page;

    // View vars
    $killList = $app->getKills($page, false);
    $killsWithNoWorth = $app->countKillsWithNoValue();
    $pendingItems = $app->getItemWithoutPrice();
    $stats = $app->getStats();

    include plugin_dir_path(__FILE__).'admin/partials/main_panel.php';
}

// Init Admin settings page data & print it
function jrm_killboard_print_settings_page() {
    global $wpdb;

    jrm_killboard_verify_modules_and_files();

    // WP options
    $oauth = get_option('jrm_killboard_oauth_version');
    $esiClientId = get_option('jrm_killboard_esi_client_id');
    $esiClientSecret = get_option('jrm_killboard_esi_client_secret');
    $oauthLink = $oauth == '1' ? JRMKillboard::ESIAUTH : JRMKillboard::ESIAUTHV2 ;
    $corporationId = get_option('jrm_killboard_corporation_id');
    $cronjobEndpoint = get_option('jrm_killboard_cronjob_endpoint');
    $cronjobSecret = get_option('jrm_killboard_cronjob_secret');
    $maxSync = get_option('jrm_killboard_max_sync');
    $lastSync = get_option('jrm_killboard_lastSync');
    $killmailError = get_option('jrm_killboard_killmail_error');
    $killmailLog = get_option('jrm_killboard_killmail_log');
    $priceError = get_option('jrm_killboard_price_error');
    $priceLog = get_option('jrm_killboard_price_log');
    $processTime = get_option('jrm_killboard_fetch_start');
    $processingFailed = false;
    if($processTime) {
        $processingFailed = time()-$processTime>(1*60*60) ? true : $processingFailed;
    }

    $upload     = wp_upload_dir();
    $upload_dir = $upload['basedir'];
    $upload_dir = $upload_dir . JRMKillboard::DATADIR;
    $logFileLink = $upload_dir.'/processing.log';
    $logSize = round(filesize($logFileLink)/1000,2);

    $pluginPageUrl = admin_url('admin.php?page=jrmevekillboard_settings');

    // Callback handler from ESI authentication
    if(isset($_GET['code']) && isset($_GET['state']) && !empty($_GET['code']) && 
                !empty($_GET['state']) && $_GET['state']==get_option('jrm_killboard_esi_init_call') ) {
        $oauthVersion = get_option('jrm_killboard_oauth_version');
        $auth = JRMKillboard::performSSOAuthentication($oauthVersion,$esiClientId,$esiClientSecret,$_GET['code']);
        if($auth) {
            $esiStatus = true;
        } else {
            // Remove api auth data
            delete_option('jrm_killboard_esi_client_id');
            delete_option('jrm_killboard_esi_client_secret');
            delete_option('jrm_killboard_esi_expires_in');
            delete_option('jrm_killboard_esi_access_token');
            delete_option('jrm_killboard_esi_refresh_token');
            $esiClientId = '';
            $esiClientSecret = '';
        }
    }

    $esiStatus = (get_option('jrm_killboard_esi_access_token') && get_option('jrm_killboard_esi_refresh_token'));
    $esiUniqueCode = uniqid();
    update_option('jrm_killboard_esi_init_call',$esiUniqueCode);

    $app = new JRMKillboard($wpdb);
    $stats = $app->getStats();

    if($esiStatus) {
        // Fetch pages with killboard FE template
        $pages = get_posts([
            'post_type' => 'page',
            'meta_key' => '_wp_page_template',
            'meta_value' => '../public/jrmkillboard_public.php'
        ]);

        $page = false;
        if(!empty($pages)) {
            $page = $pages[count($pages)-1];
        }

        $externalCronLink  = $page ? get_page_link($page->ID) : get_site_url().'/YOUR-SLUG-PAGE/';
        $paramLinker = preg_match("/\?/", $externalCronLink) ? '&' : '?';
        if(empty($cronjobEndpoint)) {
            $cronjobEndpoint = base64_encode(uniqid());
            update_option('jrm_killboard_cronjob_endpoint',$cronjobEndpoint);
        }
        if(empty($cronjobSecret)) {
            $cronjobSecret = md5(base64_encode(uniqid()));
            update_option('jrm_killboard_cronjob_secret',$cronjobSecret);
        }
        $externalCronLink .= $paramLinker.'ep-kills='.urlencode($cronjobEndpoint);
        $paramLinker = preg_match("/\?/", $externalCronLink) ? '&' : '?';
        $externalCronLink .= $paramLinker.'secret='.urlencode($cronjobSecret);
    }

    include plugin_dir_path(__FILE__).'admin/partials/main_settings.php';
}

// Init Admin graphic settings page data & print it
function jrm_killboard_print_graphics_settings_page() {
    global $wpdb;

    jrm_killboard_verify_modules_and_files();

    // WP options
    $title = get_option('jrm_killboard_title');
    $elements = get_option('jrm_killboard_elements');
    $fontSize = get_option('jrm_killboard_font_size');
    $imageSize = get_option('jrm_killboard_image_size');
    $margin = get_option('jrm_killboard_margin');
    $padding = get_option('jrm_killboard_padding');
    $killType = get_option('jrm_killboard_kills_type');
    $killsBg = get_option('jrm_killboard_kills_bg');
    $killsText = get_option('jrm_killboard_kills_text');
    $deathBg = get_option('jrm_killboard_deaths_bg');
    $deathText = get_option('jrm_killboard_deaths_text');
    $footerColor = get_option('jrm_killboard_footer_color');
    $footerText = get_option('jrm_killboard_footer_text');
    $cols = get_option('jrm_killboard_cols');
    $devSign = get_option('jrm_killboard_dev_sign') == 'show' ? true : false;
    $btnStyles = get_option('jrm_killboard_btn_styles');
    $imgStyles = get_option('jrm_killboard_image_styles');
    $inspectItems = get_option('jrm_killboard_inspect_items') == 'show' ? true : false;
    $lastPage = get_option('jrm_killboard_last_page') == 'show' ? true : false;

    include plugin_dir_path(__FILE__).'admin/partials/main_graphics.php';
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

// Download json prices from ESI Api
function jrm_killboard_do_sync_price() {
    $nonce = sanitize_text_field($_POST['check']);

    if ( ! wp_verify_nonce(  $nonce, 'jrm_killboard_op_nonce' ) ) {
        echo json_encode(['status' => false, 'error' => 'Invalid request']);
    } else {
        if(JRMKillboard::fetchPriceSet()!=false) {
            $time = time();
            update_option('jrm_killboard_lastSync',$time);
            update_option('jrm_killboard_price_log',sprintf(__('Price synced %s','jrm_killboard'),'<br>('.date('Y-m-d H:i:s',$time).')'));
            JRMKillboard::appendLog('Price sync');
            echo json_encode(['status' => true]);
        } else {
            JRMKillboard::appendLog('Price sync - Permission error file/directory');
            echo json_encode(['status' => false, 'error' => 'uploads/jrm_killboard/:<br>'.__('Permission error file/directory')]);
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
        update_option('jrm_killboard_elements',sanitize_text_field($postData['elements']));
        update_option('jrm_killboard_font_size',sanitize_text_field($postData['font_size']));
        update_option('jrm_killboard_image_size',sanitize_text_field($postData['image_size']));
        update_option('jrm_killboard_margin',sanitize_text_field($postData['margin']));
        update_option('jrm_killboard_padding',sanitize_text_field($postData['padding']));
        update_option('jrm_killboard_kills_type',sanitize_text_field($postData['kill_type']));
        update_option('jrm_killboard_kills_bg',sanitize_text_field($postData['bg_kill']));
        update_option('jrm_killboard_kills_text',sanitize_text_field($postData['text_kill']));
        update_option('jrm_killboard_deaths_bg',sanitize_text_field($postData['bg_corporate_kill']));
        update_option('jrm_killboard_deaths_text',sanitize_text_field($postData['text_corporate_kill']));
        update_option('jrm_killboard_footer_color',sanitize_text_field($postData['footer_color']));
        update_option('jrm_killboard_footer_text',sanitize_text_field($postData['footer_text']));
        update_option('jrm_killboard_cols',$postData['cols']);
        update_option('jrm_killboard_dev_sign',sanitize_text_field($postData['dev_sign']));
        update_option('jrm_killboard_btn_styles',sanitize_text_field($postData['btn_styles']));
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
            'cols','dev_sign','last_page','inspect_items'
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
        $price = doubleval($price);
        $wpdb->update( $table, ['price' => $price, 'manual' => true],['id' => $id],['%d','%d'],['%d'] );
        $item = $app->fetchItem($id);
        JRMKillboard::appendLog("Item {$item->name} set price {$price}");
        echo json_encode(['status' => true]);
    }

    wp_die();
}

// Ajax pagination for FE killboard page
function jrm_killboard_get_table_data() {
    global $wpdb;

    $nonce = sanitize_text_field($_POST['check']);
    $current = sanitize_text_field($_POST['current']);
    $offset = sanitize_text_field($_POST['offset']);

    if ( !wp_verify_nonce(  $nonce, 'jrm_killboard_op_nonce' ) ) {
        echo json_encode(['status' => false, 'error' => 'Invalid request']);
    } elseif ( (is_null($current) || (empty($current) && $current!='0' )) || empty($offset) ) {
        echo json_encode(['status' => false, 'error' => 'Params']);
    } else {
        $app = new JRMKillboard($wpdb);
        // prepare pagination
        $elementsPerPage = get_option('jrm_killboard_elements');
        $killType = get_option('jrm_killboard_kills_type');
        $totalKills = $app->countTotalKills(true,$killType);
        // based on action calculate new page
        $last = ceil($totalKills/$elementsPerPage);
        switch ($offset) {
            case 'first' : $current = 0; break;
            case 'prev'  : $current = ($current>0) ? ($current-1) : $current; break;
            case 'next'  : $current = ($current<$last) ? ($current+1) : $current; break;
            case 'last'  : $current = $last-1; break;
        }
        // fetch kill based on pagination
        $kills = $app->getKills($current, true, $killType);

        $count = 0;
        $tableData = '';

        $selectedCols = get_option('jrm_killboard_cols');
        $columns = JRMKillboard::getTableColumns();
        $activeCols = array_intersect(array_keys($columns), $selectedCols);
        foreach ($kills as $kill) {
            $count++;
            // View params
            $arrAttackers = json_decode($kill->attackers);
            $objName      = $app->attackersDetails($kill->killmailId,$arrAttackers);
            $flatName     = $objName->names;
            $bgColorKill       = get_option('jrm_killboard_kills_bg');
            $textColorKill     = get_option('jrm_killboard_kills_text');
            $bgColorSuffered   = get_option('jrm_killboard_deaths_bg');
            $textColorSuffered = get_option('jrm_killboard_deaths_text');
            $bgColor = $bgColorSuffered;
            $textColor = $textColorSuffered;
            if($kill->corp != get_option('jrm_killboard_corporation_id')) {
                $bgColor = $bgColorKill;
                $textColor = $textColorKill;
            }
            $imageSize = get_option('jrm_killboard_image_size');
            $imageStyles = get_option('jrm_killboard_image_styles');
            $inspectItems = get_option('jrm_killboard_inspect_items') == 'show' ? true : false;

            $kill->worth = is_null($kill->worth) ? __('Calculating','jrm_killboard') : $kill->worth;
            $color = JRMKillboard::getSecurityStatusColor($kill->securityStatus);
            $securityStatus = '(<span style="color:'.$color.';">'.$kill->securityStatus.'</span>)';

            $worth = JRMKillboard::niceNumberFormat($kill->worth);
            $imgUrl = JRMKillboard::ESIIMAGEURL;
            // HTML response
            $tableData .= '<tr style="background-color:'.$bgColor.'; color:'.$textColor.';">';
            if(in_array('target', $activeCols)) {
                $tableData .= '<td style="padding: 10px;margin:0px; border-right: 0px;">'.
                              '<img src="'.$imgUrl.'types/'.$kill->shipId.'/render?size='.$imageSize.'"></td>'.
                              '<td style="border-left: 0px;"><b>'.$kill->shipName.'</b><br>'.__('Kill worth','jrm_killboard').'&nbsp;'.$worth.'&nbsp;ISK';
                if ($inspectItems) {
                    $tableData .= '<br><u class="load_items" data-id="'.$kill->killmailId.'" data-ship="'.$kill->shipName.'" ';
                    $tableData .= 'data-victim="'.$kill->victim.'">'.__('Inspect items','jrm_killboard').'</u>';
                }
                $tableData .= '</td>';
            }
            if(in_array('ship', $activeCols)) {
                $style = 'style="'.$imageStyles.' width: '.$imageSize.'px; height: '.$imageSize.'px;"';
                $tableData .= '<td style="border-right: 0px;"><img src="'.$imgUrl.'alliances/'.$kill->allid.'/logo?size='.$imageSize.'" '.$style.'></td>'.
                              '<td style="border-left: 0px; border-right: 0px;"><img src="'.$imgUrl.'corporations/'.$kill->corpid.'/logo?size='.$imageSize.'" '.$style.'></td>'.
                              '<td style="border-left: 0px; border-right: 0px;"><img src="'.$imgUrl.'characters/'.$kill->victimId.'/portrait?size='.$imageSize.'" '.$style.'></td>'.
                              '<td style="border-left: 0px; border-right: 0px;">'.__('Corporation','jrm_killboard').':&nbsp;'.$kill->corpname.'<br>'.
                              __('Victim','jrm_killboard').':&nbsp;<b>'.$kill->victim.'</b></td>';
            }
            if(in_array('attackers', $activeCols)) {
                $tableData .= '<td title="'.$objName->corporates.'">'.$flatName.'</td>';
            }
            if(in_array('damage', $activeCols)) {
                $tableData .= '<td>'.number_format($kill->damageTaken).'</td>';
            }
            if(in_array('location', $activeCols)) {
                $tableData .= '<td>'.$kill->systemName.' '.$securityStatus.'<br>'.
                    date('Y-m-d H:i:s e', $kill->killTime).'</td>';
            }
            $tableData .= '</tr>';
        }
        echo json_encode([
            'html' => $tableData, 
            'count' => $count, 
            'landing_page' => $current, 
            'index' => ($current+1).' '.__('of','jrm_killboard').' '.$last
        ]);
    }

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
    add_option('jrm_killboard_plugin_version', JRM_KILLBOARD_VERSION);
    add_option('jrm_killboard_oauth_version', 2);
    add_option('jrm_killboard_esi_client_id','');
    add_option('jrm_killboard_esi_client_secret','');
    add_option('jrm_killboard_cronjob_endpoint','syncKill'); 
    add_option('jrm_killboard_cronjob_secret','');
    add_option('jrm_killboard_corporation_id',-1);
    add_option('jrm_killboard_title',__('Killboard','jrm_killboard')); 
    add_option('jrm_killboard_max_sync',-1); 
    add_option('jrm_killboard_elements',20);
    add_option('jrm_killboard_font_size','x-small');
    add_option('jrm_killboard_image_size',64);
    add_option('jrm_killboard_margin','20px');
    add_option('jrm_killboard_padding','20px');
    add_option('jrm_killboard_kills_type','all');
    add_option('jrm_killboard_kills_bg','#008000');
    add_option('jrm_killboard_kills_text','#ffffff');
    add_option('jrm_killboard_deaths_bg','#a60303');
    add_option('jrm_killboard_deaths_text','#ffffff');
    add_option('jrm_killboard_footer_color','transparent');
    add_option('jrm_killboard_footer_text','#ffffff');
    add_option('jrm_killboard_cols',array_keys(JRMKillboard::getTableColumns()));
    add_option('jrm_killboard_lastSync',-1);
    add_option('jrm_killboard_dev_sign','show');
    add_option('jrm_killboard_btn_styles','padding:6px;');
    add_option('jrm_killboard_image_styles','display: inline;');
    add_option('jrm_killboard_last_page','hide');
    add_option('jrm_killboard_inspect_items','show');
}

// Create plugin upload folder
function jrm_killboard_upload_folder() {    
    $upload     = wp_upload_dir();
    $upload_dir = $upload['basedir'];
    $upload_dir = $upload_dir . JRMKillboard::DATADIR;
    if (! is_dir($upload_dir)) {
       mkdir( $upload_dir, 0700 );
    }
    touch($upload_dir.'/processing.log');
    chmod($upload_dir.'/processing.log',0775);
    JRMKillboard::clearLog();
}

// Create plugin db
function jrm_killboard_initDB() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $killboard   = $wpdb->prefix . JRMKillboard::TABKILLBOARD;
    $capsuler    = $wpdb->prefix . JRMKillboard::TABCAPSULER;
    $corporation = $wpdb->prefix . JRMKillboard::TABCORPORATION;
    $item        = $wpdb->prefix . JRMKillboard::TABITEM;
    $queue       = $wpdb->prefix . JRMKillboard::TABQUEUE;
    $query1 = "CREATE TABLE IF NOT EXISTS `{$killboard}` (
            `killmailId` bigint(20) unsigned NOT NULL,
            `hash` text NOT NULL,
            `systemName` varchar (255) NOT NULL,
            `securityStatus` double NULL,
            `attackers` text NOT NULL,
            `victimId` bigint(20) unsigned NOT NULL,
            `isCorporate` tinyint(1) NOT NULL DEFAULT '0',
            `damageTaken` double NULL,
            `killingBlow` bigint(20) unsigned NOT NULL,
            `shipId` bigint(20) unsigned NOT NULL,
            `items` text,
            `worth` double DEFAULT NULL,
            `killTime` int(11) NOT NULL,
            `active` tinyint(1) DEFAULT '1',
             PRIMARY KEY (`killmailId`)
           ) {$charset_collate};";
    $query2 = "CREATE TABLE IF NOT EXISTS `{$capsuler}` (
            `id` bigint(20) unsigned NOT NULL,
            `name` varchar(255) NOT NULL,
            `allianceId` bigint(20) unsigned,
            `corporationId` int(20) unsigned,
            `lastSync` int(11) unsigned NOT NULL,
             PRIMARY KEY (`id`)
           ) {$charset_collate};";
    $query3 = "CREATE TABLE IF NOT EXISTS `{$corporation}` (
            `id` bigint(20) unsigned NOT NULL,
            `name` varchar(255) NOT NULL,
            `lastSync` int(11) unsigned NOT NULL,
             PRIMARY KEY (`id`)
           ) {$charset_collate};";
    $query4 = "CREATE TABLE IF NOT EXISTS `{$item}` (
            `id` bigint(20) unsigned NOT NULL,
            `name` varchar(255) NOT NULL,
            `price` double NULL,
            `manual` tinyint(1) DEFAULT '1',
            `lastSync` int(11) unsigned NOT NULL,
             PRIMARY KEY (`id`)
           ) {$charset_collate};";
    $query5 = "CREATE TABLE IF NOT EXISTS `{$queue}` (
            `killmailId` bigint(20) unsigned NOT NULL,
            `hash` text NOT NULL,
            `status` tinyint(1) NOT NULL DEFAULT 0,
             PRIMARY KEY (`killmailId`)
           ) {$charset_collate};";
    $query6 = "INSERT INTO `{$capsuler}` (id,name,allianceId,corporationId,lastSync) ".
                "VALUES (-1,'NPC','-1','-1',0) ON DUPLICATE KEY UPDATE lastSync = 0;".
              "INSERT INTO `{$item}` (id,name,price,manual,lastSync) ".
                "VALUES (670,'Capsule',0,1,-1) ON DUPLICATE KEY UPDATE lastSync = 0;".
              "INSERT INTO `{$item}` (id,name,price,manual,lastSync) ".
                "VALUES (29148,'Corpse Female',0,1,-1) ON DUPLICATE KEY UPDATE lastSync = 0;".
              "INSERT INTO `{$item}` (id,name,price,manual,lastSync) ".
                "VALUES (25,'Corpse Male',0,1,-1) ON DUPLICATE KEY UPDATE lastSync = 0;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $query1 );
    dbDelta( $query2 );
    dbDelta( $query3 );
    dbDelta( $query4 );
    dbDelta( $query5 );
    dbDelta( $query6 );
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
    delete_option('jrm_killboard_plugin_version');
    delete_option('jrm_killboard_oauth_version');
    delete_option('jrm_killboard_esi_client_id');
    delete_option('jrm_killboard_esi_client_secret');
    delete_option('jrm_killboard_cronjob_endpoint');
    delete_option('jrm_killboard_cronjob_secret');
    delete_option('jrm_killboard_corporation_id');
    delete_option('jrm_killboard_title');
    delete_option('jrm_killboard_max_sync');
    delete_option('jrm_killboard_elements');
    delete_option('jrm_killboard_font_size');
    delete_option('jrm_killboard_image_size');
    delete_option('jrm_killboard_margin');
    delete_option('jrm_killboard_padding');
    delete_option('jrm_killboard_kills_type');
    delete_option('jrm_killboard_kills_bg');
    delete_option('jrm_killboard_kills_text');
    delete_option('jrm_killboard_deaths_bg');
    delete_option('jrm_killboard_deaths_text');
    delete_option('jrm_killboard_footer_color');
    delete_option('jrm_killboard_footer_text');
    delete_option('jrm_killboard_cols');
    delete_option('jrm_killboard_lastSync');
    delete_option('jrm_killboard_dev_sign');
    delete_option('jrm_killboard_price_error');
    delete_option('jrm_killboard_price_log');
    delete_option('jrm_killboard_killmail_error');
    delete_option('jrm_killboard_killmail_log');
    delete_option('jrm_killboard_esi_expires_in');
    delete_option('jrm_killboard_esi_access_token');
    delete_option('jrm_killboard_esi_refresh_token');
    delete_option('jrm_killboard_fetch_start');
    delete_option('jrm_killboard_esi_init_call');
    delete_option('jrm_killboard_btn_styles');
    delete_option('jrm_killboard_image_styles');
    delete_option('jrm_killboard_last_page');
    delete_option('jrm_killboard_inspect_items');
}

// Delete tables and data
function jrm_killboard_deleteDB() {
    global $wpdb;

    $killboard   = $wpdb->prefix . JRMKillboard::TABKILLBOARD;
    $capsuler    = $wpdb->prefix . JRMKillboard::TABCAPSULER;
    $corporation = $wpdb->prefix . JRMKillboard::TABCORPORATION;
    $item        = $wpdb->prefix . JRMKillboard::TABITEM;
    $queue       = $wpdb->prefix . JRMKillboard::TABQUEUE;
    $query1 = "DROP TABLE `{$killboard}`;";
    $query2 = "DROP TABLE `{$capsuler}`;";
    $query3 = "DROP TABLE `{$corporation}`;";
    $query4 = "DROP TABLE `{$item}`;";
    $query5 = "DROP TABLE `{$queue}`;";

    $wpdb->query( $query1 );
    $wpdb->query( $query2 );
    $wpdb->query( $query3 );
    $wpdb->query( $query4 );
    $wpdb->query( $query5 );
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
