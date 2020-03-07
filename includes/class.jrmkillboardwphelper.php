<?php

/**
 * JRMWPHelper - DB & Option Init / Removal - Admin pages printer
 * @package    jrm_killboard
 * @author     jrmarco <developer@bigm.it>
 * @license    https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html#content
 * @link       https://bigm.it
 */
class JRMKillboardWPH {
    /**
     * Return  wordpress plugin tables queries
     *
     * @param WordPressDatabase $wpdb Wordpress DB entity
     * @return Array Return array of query
     */
    public static function initDB($wpdb) {
        $charset_collate = $wpdb->get_charset_collate();

        $killboard   = $wpdb->prefix . JRMKillboard::TABKILLBOARD;
        $capsuler    = $wpdb->prefix . JRMKillboard::TABCAPSULER;
        $corporation = $wpdb->prefix . JRMKillboard::TABCORPORATION;
        $item        = $wpdb->prefix . JRMKillboard::TABITEM;
        $queue       = $wpdb->prefix . JRMKillboard::TABQUEUE;
        $query[] = "CREATE TABLE IF NOT EXISTS `{$killboard}` (
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
        $query[] = "CREATE TABLE IF NOT EXISTS `{$capsuler}` (
                `id` bigint(20) unsigned NOT NULL,
                `name` varchar(255) NOT NULL,
                `allianceId` bigint(20) unsigned,
                `corporationId` int(20) unsigned,
                `lastSync` int(11) unsigned NOT NULL,
                 PRIMARY KEY (`id`)
               ) {$charset_collate};";
        $query[] = "CREATE TABLE IF NOT EXISTS `{$corporation}` (
                `id` bigint(20) unsigned NOT NULL,
                `name` varchar(255) NOT NULL,
                `lastSync` int(11) unsigned NOT NULL,
                 PRIMARY KEY (`id`)
               ) {$charset_collate};";
        $query[] = "CREATE TABLE IF NOT EXISTS `{$item}` (
                `id` bigint(20) unsigned NOT NULL,
                `name` varchar(255) NOT NULL,
                `price` double NULL,
                `manual` tinyint(1) DEFAULT '1',
                `lastSync` int(11) unsigned NOT NULL,
                 PRIMARY KEY (`id`)
               ) {$charset_collate};";
        $query[] = "CREATE TABLE IF NOT EXISTS `{$queue}` (
                `killmailId` bigint(20) unsigned NOT NULL,
                `hash` text NOT NULL,
                `status` tinyint(1) NOT NULL DEFAULT 0,
                 PRIMARY KEY (`killmailId`)
               ) {$charset_collate};";
        $query[] = "INSERT INTO `{$capsuler}` (id,name,allianceId,corporationId,lastSync) ".
                    "VALUES (-1,'NPC','-1','-1',0) ON DUPLICATE KEY UPDATE lastSync = 0;".
                  "INSERT INTO `{$item}` (id,name,price,manual,lastSync) ".
                    "VALUES (670,'Capsule',0,1,-1) ON DUPLICATE KEY UPDATE lastSync = 0;".
                  "INSERT INTO `{$item}` (id,name,price,manual,lastSync) ".
                    "VALUES (29148,'Corpse Female',0,1,-1) ON DUPLICATE KEY UPDATE lastSync = 0;".
                  "INSERT INTO `{$item}` (id,name,price,manual,lastSync) ".
                    "VALUES (25,'Corpse Male',0,1,-1) ON DUPLICATE KEY UPDATE lastSync = 0;";

        return $query;
    }

    /**
     * Init WP plugin options
     *
     * @return void
     */
    public static function initOptions() {
        add_option('jrm_killboard_plugin_version', JRM_KILLBOARD_VERSION);
        add_option('jrm_killboard_oauth_version', 2);
        add_option('jrm_killboard_esi_client_id','');
        add_option('jrm_killboard_esi_client_secret','');
        add_option('jrm_killboard_cronjob_endpoint','syncKill'); 
        add_option('jrm_killboard_cronjob_secret','');
        add_option('jrm_killboard_corporation_id',-1);
        add_option('jrm_killboard_title',__('Killboard','jrm_killboard'));
        add_option('jrm_killboard_title_align','left');
        add_option('jrm_killboard_max_sync',-1); 
        add_option('jrm_killboard_elements',20);
        add_option('jrm_killboard_be_elements',20);
        add_option('jrm_killboard_font_size','x-small');
        add_option('jrm_killboard_image_size',64);
        add_option('jrm_killboard_margin','20px');
        add_option('jrm_killboard_padding','20px');
        add_option('jrm_killboard_kills_type','all');
        add_option('jrm_killboard_kills_bg','#008000');
        add_option('jrm_killboard_kills_text','#000000');
        add_option('jrm_killboard_deaths_bg','#a60303');
        add_option('jrm_killboard_deaths_text','#000000');
        add_option('jrm_killboard_title_color','transparent');
        add_option('jrm_killboard_title_text','#000000');
        add_option('jrm_killboard_table_header_color','transparent');
        add_option('jrm_killboard_table_header_text','#000000');
        add_option('jrm_killboard_footer_color','transparent');
        add_option('jrm_killboard_footer_text','#000000');
        add_option('jrm_killboard_inspect_color','transparent');
        add_option('jrm_killboard_inspect_text','#000000');
        add_option('jrm_killboard_cols',array_keys(JRMKillboard::getTableColumns()));
        add_option('jrm_killboard_lastSync',-1);
        add_option('jrm_killboard_dev_sign','show');
        add_option('jrm_killboard_btn_styles','padding:6px;');
        add_option('jrm_killboard_btn_align','left');
        add_option('jrm_killboard_image_styles','display: inline;');
        add_option('jrm_killboard_last_page','hide');
        add_option('jrm_killboard_inspect_items','show');
    }

    /**
     * Init view params for Plugin Admin main page
     *
     * @param WordPressDatabase $wpdb Wordpress DB entity
     * @return void
     */
    public static function mainPage($wpdb) {
        $app = new JRMKillboard($wpdb);

        $totalKills = $app->countTotalKills();

        // Pagination data
        $elementsPerPage = get_option('jrm_killboard_be_elements');
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
        $killList = $app->getKills($page, false, null, false);
        $killsWithNoWorth = $app->countKillsWithNoValue();
        $pendingItems = $app->getItemWithoutPrice();
        $stats = $app->getStats();

        include plugin_dir_path(__FILE__).'../admin/partials/main_panel.php';
    }

    /**
     * Init view params for Plugin Admin settings page
     *
     * @param WordPressDatabase $wpdb Wordpress DB entity
     * @return void
     */
    public static function settingsPage($wpdb) {
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

        $upload = wp_upload_dir();
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

        include plugin_dir_path(__FILE__).'../admin/partials/main_settings.php';
    }

    /**
     * Init view params for Plugin Admin graphics page
     *
     * @return void
     */
    public static function graphicsPage() {
        // WP options
        $title = get_option('jrm_killboard_title');
        $titleAlign = get_option('jrm_killboard_title_align');
        $elements = get_option('jrm_killboard_elements');
        $be_elements = get_option('jrm_killboard_be_elements');
        $fontSize = get_option('jrm_killboard_font_size');
        $imageSize = get_option('jrm_killboard_image_size');
        $margin = get_option('jrm_killboard_margin');
        $padding = get_option('jrm_killboard_padding');
        $killType = get_option('jrm_killboard_kills_type');
        $killsBg = get_option('jrm_killboard_kills_bg');
        $killsText = get_option('jrm_killboard_kills_text');
        $deathBg = get_option('jrm_killboard_deaths_bg');
        $deathText = get_option('jrm_killboard_deaths_text');
        $titleColor = get_option('jrm_killboard_title_color');
        $titleText = get_option('jrm_killboard_title_text');
        $tableHeaderColor = get_option('jrm_killboard_table_header_color');
        $tableHeaderText = get_option('jrm_killboard_table_header_text');
        $footerColor = get_option('jrm_killboard_footer_color');
        $footerText = get_option('jrm_killboard_footer_text');
        $inspectColor = get_option('jrm_killboard_inspect_color');
        $inspectText = get_option('jrm_killboard_inspect_text');
        $cols = get_option('jrm_killboard_cols');
        $devSign = get_option('jrm_killboard_dev_sign') == 'show' ? true : false;
        $btnStyles = get_option('jrm_killboard_btn_styles');
        $btnAlign = get_option('jrm_killboard_btn_align');
        $imgStyles = get_option('jrm_killboard_image_styles');
        $inspectItems = get_option('jrm_killboard_inspect_items') == 'show' ? true : false;
        $lastPage = get_option('jrm_killboard_last_page') == 'show' ? true : false;

        include plugin_dir_path(__FILE__).'../admin/partials/main_graphics.php';
    }

    /**
     * Init view params for Plugin Admin items page
     *
     * @param WordPressDatabase $wpdb Wordpress DB entity
     * @return void
     */
    public static function itemsPage($wpdb) {
        // WP options
        $app = new JRMKillboard($wpdb);
        $totalItems = $app->countTotalItems();

        // Pagination data
        $elementsPerPage = get_option('jrm_killboard_be_elements');
        $lastPage   = 0;
        if($totalItems!=0) {
            $lastPage = ceil($totalItems/$elementsPerPage);
        }

        $page = 0;
        $prev = $page;
        $next = $page;

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
        $itemsList = $app->getItems($page);

        include plugin_dir_path(__FILE__).'../admin/partials/main_items.php';
    }

    /**
     * Return HTML structure for pager
     *
     * @param WordPressDatabase $wpdb Wordpress DB entity
     * @return void
     */
    public static function killboardPager($wpdb) {
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
    }

    /**
     * Delete WP plugin options
     *
     * @return void
     */
    public static function removeOptions() {
        delete_option('jrm_killboard_plugin_version');
        delete_option('jrm_killboard_oauth_version');
        delete_option('jrm_killboard_esi_client_id');
        delete_option('jrm_killboard_esi_client_secret');
        delete_option('jrm_killboard_cronjob_endpoint');
        delete_option('jrm_killboard_cronjob_secret');
        delete_option('jrm_killboard_corporation_id');
        delete_option('jrm_killboard_title');
        delete_option('jrm_killboard_title_align');
        delete_option('jrm_killboard_max_sync');
        delete_option('jrm_killboard_elements');
        delete_option('jrm_killboard_be_elements');
        delete_option('jrm_killboard_font_size');
        delete_option('jrm_killboard_image_size');
        delete_option('jrm_killboard_margin');
        delete_option('jrm_killboard_padding');
        delete_option('jrm_killboard_kills_type');
        delete_option('jrm_killboard_kills_bg');
        delete_option('jrm_killboard_kills_text');
        delete_option('jrm_killboard_deaths_bg');
        delete_option('jrm_killboard_deaths_text');
        delete_option('jrm_killboard_title_color');
        delete_option('jrm_killboard_title_text');
        delete_option('jrm_killboard_table_header_color');
        delete_option('jrm_killboard_table_header_text');
        delete_option('jrm_killboard_footer_color');
        delete_option('jrm_killboard_footer_text');
        delete_option('jrm_killboard_inspect_color');
        delete_option('jrm_killboard_inspect_text');
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
        delete_option('jrm_killboard_btn_align');
        delete_option('jrm_killboard_image_styles');
        delete_option('jrm_killboard_last_page');
        delete_option('jrm_killboard_inspect_items');
    }

    /**
     * Drop WP plugin tables
     *
     * @param WordPressDatabase $wpdb Wordpress DB entity
     * @return void
     */
    public static function deleteDB($wpdb) {
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
}
