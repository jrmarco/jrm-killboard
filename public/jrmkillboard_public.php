<?php
/**
 * Template Name: JRM Killboard Frontend Page
 * @package    jrm_killboard
 * @author     jrmarco <developer@bigm.it>
 * @license    https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html#content
 * @link       https://bigm.it
 */

get_header();

$pluginDir = plugin_dir_path(__FILE__).'../includes/';

if(!file_exists($pluginDir . 'class.jrmkillboard.php') || !file_exists($pluginDir .'class.jrmkillboardfe.php')) {
    _e('Plugin error: JRM Killboard','jrm_killboard');
}
    include $pluginDir . 'class.jrmkillboard.php';
    global $wpdb;

    // WP-Cron checks
    $killSyncCron = get_option('jrm_killboard_max_sync');
    if($killSyncCron !='-1') {
        $time = wp_next_scheduled( 'jrm_killboard_cronjob' );
        if($time == false) {
            jrm_killboard_process_WpCron($killSyncCron);
        }
    }

    // Enqueue frontend JS script
    wp_enqueue_script('killboardjs_template', plugins_url('/js/killboard_template.js' , __FILE__ ), ['jquery'], true, true);
    wp_localize_script( 'killboardjs_template', 'axobject', ['ajaxurl' => admin_url( 'admin-ajax.php' )]);

    $killBoard  = new JRMKillboard($wpdb);

    $esiStatus = (get_option('jrm_killboard_esi_access_token') && get_option('jrm_killboard_esi_refresh_token'));
    $cronjobEndpoint = get_option('jrm_killboard_cronjob_endpoint');
    $cronjobSecret = get_option('jrm_killboard_cronjob_secret');
    // Cronjob validation entry point
    if($esiStatus && isset($_GET['secret']) && $_GET['secret'] == $cronjobSecret) {
        // Kills - cronjob endpoint
        if(isset($_GET['ep-kills']) && $_GET['ep-kills'] == $cronjobEndpoint) {
            $accessToken = get_option('jrm_killboard_esi_access_token');
            $corporationId = get_option('jrm_killboard_corporation_id');
            JRMKillboardFE::syncCorporationKills($wpdb, $corporationId, $accessToken);
        }
        // Price - cronjob endpoint
        if(isset($_GET['ep-prices']) && $_GET['ep-prices'] == $cronjobEndpoint) {
            if(JRMKillboard::fetchPriceSet()!=false) {
                $time = time();
                update_option('jrm_killboard_lastSync',$time);
                update_option('jrm_killboard_price_log',sprintf(__('Price synced %s','jrm_killboard'),'<br>('.date('Y-m-d H:i:s',$time).')'));
                delete_option('jrm_killboard_price_error');
            } else {
                $upload = wp_upload_dir();
                $upload_dir = $upload['basedir'];
                $upload_dir = $upload_dir . JRMKillboardFE::DATADIR;
                update_option('jrm_killboard_price_error',__('Permission error file/directory','jrm_killboard').' :: '.$upload_dir.'/price.json');
                delete_option('jrm_killboard_price_log');
            }
        }
    }

    // View vars
    $titleAlign = get_option('jrm_killboard_title_align');
    $margin = get_option('jrm_killboard_margin') ? 'margin:'.get_option('jrm_killboard_margin').';' : ''; 
    $padding = get_option('jrm_killboard_padding') ? 'padding: '.get_option('jrm_killboard_padding').';' : '';
    $elementsPerPage = get_option('jrm_killboard_elements');
    $btnStyles = get_option('jrm_killboard_btn_styles');
    $btnAlign = get_option('jrm_killboard_btn_align');
    $imageStyles = get_option('jrm_killboard_image_styles');
    $lastPageOnly = (get_option('jrm_killboard_last_page') == 'show') ? true : false;
    $inspectItems = (get_option('jrm_killboard_inspect_items') == 'show') ? true : false;
    $killType = get_option('jrm_killboard_kills_type');
    $kills = $killBoard->getKills($page, true, $killType);

    $totalKills = $killBoard->countTotalKills(true,$killType);
    $lastPage   = ceil($totalKills/$elementsPerPage);

    $bgColorKill       = get_option('jrm_killboard_kills_bg');
    $textColorKill     = get_option('jrm_killboard_kills_text');
    $bgColorSuffered   = get_option('jrm_killboard_deaths_bg');
    $textColorSuffered = get_option('jrm_killboard_deaths_text');
    $fontSize          = get_option('jrm_killboard_font_size');
    $imageSize         = get_option('jrm_killboard_image_size');
    $selectedCols      = get_option('jrm_killboard_cols');
    $titleColor        = get_option('jrm_killboard_title_color');
    $titleText         = get_option('jrm_killboard_title_text');
    $tableHeaderColor  = get_option('jrm_killboard_table_header_color');
    $tableHeaderText   = get_option('jrm_killboard_table_hedaer_text');
    $footerColor       = get_option('jrm_killboard_footer_color');
    $footerText        = get_option('jrm_killboard_footer_text');
    $inspectColor      = get_option('jrm_killboard_inspect_color');
    $inspectText       = get_option('jrm_killboard_inspect_text');
    $devSign           = (get_option('jrm_killboard_dev_sign') == 'show') ? true : false;

    $columns           = JRMKillboard::getTableColumns();
    $activeCols        = array_intersect(array_keys($columns), $selectedCols);

    include __DIR__.'/partials/frontend.php';