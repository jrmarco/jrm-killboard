<?php 
/**
 * JRMKillboardFE - Frontend Helper class
 * @package    jrm_killboard
 * @author     jrmarco <developer@bigm.it>
 * @license    http://opensource.org/licenses/MIT
 * @link       https://bigm.it
 */
class JRMKillboardFE {

    private static $instance;
    protected $templates;
    public static function get_instance() {

        if ( null == self::$instance ) {
            self::$instance = new JRMKillboardFE();
        } 

        return self::$instance;

    } 

    // Initializes plugin
    private function __construct() {

        $this->templates = array();


        // Add a filter to inject template into the cache
        if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) {

            // 4.6 and older
            add_filter(
                'page_attributes_dropdown_pages_args',
                array( $this, 'register_project_templates' )
            );

        } else {

            // Add a filter to the wp 4.7 version attributes metabox
            add_filter(
                'theme_page_templates', array( $this, 'add_new_template' )
            );

        }

        // Add a filter to the save post to inject out template into the page cache
        add_filter(
            'wp_insert_post_data', 
            array( $this, 'register_project_templates' ) 
        );


        /**
         * Add a filter to the template include to determine if the page has our template 
         * assigned and return it's path
         */
        add_filter(
            'template_include', 
            array( $this, 'view_project_template') 
        );


        // Add template
        $this->templates = array(
            '../public/jrmkillboard_public.php' => 'JRM Killboard',
        );
            
    } 

    /**
     * Adds template to template page dropdown
     */
    public function add_new_template( $posts_templates ) {
        $posts_templates = array_merge( $posts_templates, $this->templates );
        return $posts_templates;
    }

    /**
     * Adds template to pages cache
     */
    public function register_project_templates( $atts ) {

        // Create the key used for the themes cache
        $cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

        // Retrieve the cache list. If it doesn't exist, or it's empty prepare an array
        $templates = wp_get_theme()->get_page_templates();
        if ( empty( $templates ) ) {
            $templates = array();
        } 

        // New cache
        wp_cache_delete( $cache_key , 'themes');

        // Add template to the list of templates by merging new one with existing
        $templates = array_merge( $templates, $this->templates );

        // Add the modified cache
        wp_cache_add( $cache_key, $templates, 'themes', 1800 );

        return $atts;

    } 

    /**
     * Checks if the template is assigned to the page
     */
    public function view_project_template( $template ) {
        global $post;

        // Return template if post is empty
        if ( ! $post ) {
            return $template;
        }

        // Return default template if we don't have a custom one defined
        if ( ! isset( $this->templates[get_post_meta( 
            $post->ID, '_wp_page_template', true 
        )] ) ) {
            return $template;
        } 

        $file = plugin_dir_path( __FILE__ ). get_post_meta( 
            $post->ID, '_wp_page_template', true
        );

        // Just to be safe, we check if the file exist first
        if ( file_exists( $file ) ) {
            return $file;
        } else {
            echo _e('JRMKillboard Fatal Error:: Missing template file','jrm_killboard');
        }

        // Return template
        return $template;

    }

    /**
     * Perform ESI Api Kill syncronization
     * @param  Object  $wpdb          Wordpress Database object
     * @param  Integer  $corporationId Corporation Id
     * @param  String  $accessToken   ESI SSO Access Token
     * @param  Integer $repeated      Counter of processing cycle
     * @return void
     */
    public static function syncCorporationKills($wpdb, $corporationId, $accessToken) {
        delete_option('jrm_killboard_killmail_error');
        delete_option('jrm_killboard_killmail_log');

        $expire = get_option('jrm_killboard_esi_expires_in');

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        JRMKillboard::appendLog("Sync killmails via ESI ({$ip})");
        if(time()>$expire) {
            $renew = self::renewToken();
            if(!$renew) {
                JRMKillboard::appendLog('<span style="color:red;">Cannot renew SSO ESI token</span>');
                update_option('jrm_killboard_killmail_error',
                              __('Fatal Error - Cannot renew SSO ESI token','jrm_killboard').' @ '.date('Y-m-d H:i:s',time()));
                return true;
            } else {
                $accessToken = get_option('jrm_killboard_esi_access_token');
            }
        }

        $url = JRMKillboard::
                ESIURL.'corporations/'.$corporationId.'/killmails/recent/?datasource=tranquility&token='.$accessToken;

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "Accept: */*",
            "Accept-Encoding: gzip, deflate",
            "Cache-Control: no-cache",
            "Connection: keep-alive",
            "Host: esi.evetech.net",
            "User-Agent: PHP-Curl/".curl_version()['version'],
            "cache-control: no-cache"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        /**
         * Inject testing data : $response =file_get_contents(__DIR__.'/killmail_report.json');
         */

        if ($err) {
            JRMKillboard::appendLog('<span style="color:red;">'.$err.'</span>');
            update_option('jrm_killboard_killmail_error',"cURL Error: " . $err);
        } else {
            $date = date('Y-m-d H:i:s');
            $objResponse = json_decode($response);
            if(!isset($objResponse->error)) {
                $killmails = $objResponse;
                update_option('jrm_killboard_fetch_start',time());
                $message = sprintf(__('Fetching kill started %s','jrm_killboard'),'<br>('.$date.')');
                update_option('jrm_killboard_killmail_log',$message);
                // Init 
                $app = new JRMKillboard($wpdb);
                $app->clearQueue();
                // Store killmails 
                foreach ($killmails as $killmail) {
                    if(!$app->verifyKill($killmail->killmail_id)) {
                        $app->push($killmail);
                    }
                }

                $app->processQueue();

                update_option('jrm_killboard_killmail_log',
                    __('Fetching kills, Completed','jrm_killboard')."<br>({$date})");
                delete_option('jrm_killboard_fetch_start');
                delete_option('jrm_killboard_killmail_error');
            } else {
                JRMKillboard::appendLog('<span style="color:red;">'.$objResponse->error.'</span>');
                update_option('jrm_killboard_killmail_error',$objResponse->error."<br>({$date})");
            }
        }
    }

    /**
     * Renew ESI SSO Authentication
     * @return Bool Process result
     */
    public static function renewToken() {
        $clientId = get_option('jrm_killboard_esi_client_id');
        $clientSecret = get_option('jrm_killboard_esi_client_secret');
        $token = get_option('jrm_killboard_esi_refresh_token');

        $curl = curl_init();

        $post = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $token,
            'scope' => 'esi-killmails.read_corporation_killmails.v1'
        ];

        /**
         * SSO Refresh token Flow - References
         * @link https://github.com/esi/esi-docs/blob/master/docs/sso/refreshing_access_tokens.md
         */

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://login.eveonline.com/v2/oauth/token",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => http_build_query($post),
          CURLOPT_HTTPHEADER => [
            "Accept: */*",
            "Accept-Encoding: gzip, deflate",
            "Authorization: Basic ".base64_encode($clientId.':'.$clientSecret),
            "Cache-Control: no-cache",
            "Connection: keep-alive",
            "Content-Type: application/x-www-form-urlencoded",
            "Host: login.eveonline.com",
            "User-Agent: PHP-Curl/".curl_version()['version'],
          ]
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            JRMKillboard::appendLog('<span style="color:red;"> SSO ESI Renew token '.$err.'</span>');
            update_option('jrm_killboard_killmail_error',__('Fatal Error:: cURL Error '.$err,'jrm_killboard'));
        } else {
            if($response) {
                $esiResponse = json_decode($response);
                if(isset($esiResponse->access_token) && isset($esiResponse->refresh_token)) {
                    // Update options reference
                    update_option('jrm_killboard_esi_expires_in', time()+intval($esiResponse->expires_in));
                    update_option('jrm_killboard_esi_access_token', $esiResponse->access_token);
                    update_option('jrm_killboard_esi_refresh_token', $esiResponse->refresh_token);

                    return true;
                } elseif(isset($esiResponse->error)) {
                    JRMKillboard::appendLog('<span style="color:red;">'.$esiResponse->error_description.'</span>');
                }
            }

            return false;
        }
    }

} 