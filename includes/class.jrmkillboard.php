<?php

/**
 * JRMKillboard - Helper class - Act also as interface between plugin and ESI
 * @package    jrm_killboard
 * @author     jrmarco <developer@bigm.it>
 * @license    https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html#content
 * @link       https://bigm.it
 */
class JRMKillboard {
    const TABKILLBOARD   = 'jrm_killboard';
    const TABCAPSULER    = 'jrm_capsuler';
    const TABCORPORATION = 'jrm_corporation';
    const TABITEM        = 'jrm_item';
    const TABQUEUE       = 'jrm_queue';
    const ESIURL         = 'https://esi.evetech.net/latest/';
    const ESIIMAGEURL    = 'https://images.evetech.net/';

    const ESITOKEN       = 'https://login.eveonline.com/oauth/token';
    const ESITOKENV2     = 'https://login.eveonline.com/v2/oauth/token';

    const ESIAUTH        = 'https://login.eveonline.com/oauth/authorize/';
    const ESIAUTHV2      = 'https://login.eveonline.com/v2/oauth/authorize/';

    const DATADIR        = '/jrm_killboard_data';

    private $db;
    private $prefix;
    private $corporationId;

    /**
     * Link wordpress db instance into class variable
     *
     * @param Wordpress Database object $wpdb
     */
    public function __construct($wpdb) {
        $this->db            = $wpdb;
        $this->prefix        = $this->db->prefix;
        $this->corporationId = get_option('jrm_killboard_corporation_id');
    }

    /**
     * Return an array of max sync time window between one ESI call and the other
     * @return Array Array of max sync time
     */
    public static function getSyncOptions() {
        return [
            -1 => __('Disabled','jrm_killboard'),
            24 => ' '.__('Every hour','jrm_killboard'),
            12 => ' '.__('Twice a day','jrm_killboard'),
            1 => ' '.__('Daily','jrm_killboard')
        ];
    }

    /**
     * Return an array of "elements per page"
     * @return Array Array of elements per page
     */
    public static function getElementsOptions() {
        return [5,10,20,50,100];
    }

    /**
     * Return an array of font size
     * @return Array Array of font size
     */
    public static function getFontSize() {
        return ['xx-small','x-small','small','medium','large','x-large','xx-large','xxx-large'];
    }

    /**
     * Return an array of image size
     * @return Array Array of image size
     */
    public static function getImageSize() {
        return [
            32 => __('Small','jrm_killboard'),
            64 => __('Medium','jrm_killboard'),
            128 => __('Big','jrm_killboard'),
            256 => __('Original','jrm_killboard')
        ];
    }

    /**
     * Return an array of killboard table columns
     * @return Array Array of table columns
     */
    public static function getTableColumns() {
        return [
            'ship' => __('Ship','jrm_killboard'),
            'target' => __('Target','jrm_killboard'),
            'attackers' => __('Attackers','jrm_killboard'),
            'damage' => __('Damage','jrm_killboard'),
            'location' => __('Position','jrm_killboard')
        ];
    }

    /**
     * Return Corporation Id
     * @return Integer Corporation Id
     */
    public function getCorporationId() {
        return $this->corporationId;
    }

    /**
     * Return results based on query params from self::TABKILLBOARD table
     *
     * @param integer $inputPage Current page
     * @param boolean $blnPublic Fetch only active results
     * @param string $type Fetch results based on type
     * @return array Array of query results from self::TABKILLBOARD table
     */
    public function getKills($inputPage = 0, $blnPublic = true, $type = null, $frontend = true) {
        // Get options from WP
        $frontendElementsPerPage = get_option('jrm_killboard_elements');
        $backendElementsPerPage = get_option('jrm_killboard_be_elements');
        $elemPagina = $frontend ? $frontendElementsPerPage : $backendElementsPerPage;
        // Calculate offset
        $offset     = $inputPage*$elemPagina;
        $whereConditions = 0;

        // Query builder
        $query   = 'SELECT k.*,c.name as victim, c.corporationId as corp, c.allianceId as allid, '.
                   'co.id as corpid, co.name as corpname, i.name as shipName '.
                   'FROM '.$this->prefix.self::TABKILLBOARD.' as k '.
                   'LEFT JOIN '.$this->prefix.self::TABCAPSULER.' as c ON k.victimId = c.id '.
                   'LEFT JOIN '.$this->prefix.self::TABCORPORATION.' as co ON c.corporationId = co.id '.
                   'LEFT JOIN '.$this->prefix.self::TABITEM.' as i ON i.id = k.shipId ';
        if($blnPublic) {
            $whereConditions++;
            $query .= "WHERE k.active = 1 ";
        }
        if($type == 'done') {
            $query .= ($whereConditions) ? ' AND k.isCorporate <> 1 ' : ' WHERE k.isCorporate <> 1 ';
        } elseif($type == 'suffered') {
            $query .= ($whereConditions) ? ' AND k.isCorporate = 1 ' : ' WHERE k.isCorporate = 1 ';
        }
        $query  .= "ORDER BY killTime DESC LIMIT %d,%d";
        $results = $this->db->get_results( vsprintf($query , [$offset, $elemPagina]));
        return $results;
    }

    /**
     * Return results based on query params from self::TABITEM table
     *
     * @param integer $inputPage Current page
     * @return array Array of query results from self::TABITEM table
     */
    public function getItems($inputPage = 0, $limited = true) {
        // Get options from WP
        $elemPagina = get_option('jrm_killboard_be_elements');
        // Calculate offset
        $offset = $inputPage*$elemPagina;

        // Query builder
        $query   = 'SELECT * FROM '.$this->prefix.self::TABITEM;
        if ($limited) {
            $query .= ' ORDER BY id DESC LIMIT %d,%d';
            $query = vsprintf($query , [$offset, $elemPagina]);
        } 
        $results = $this->db->get_results($query);
        return $results;
    }

    /**
     * Return count of results based on params
     * @param  boolean $blnPublic Fetch only active kill
     * @param  String  $type      Fetch based on kill type
     * @return Integer Count of total results
     */
    public function countTotalKills($blnPublic = true, $type = null) {
        $query   = 'SELECT count(*) as totale FROM '.$this->prefix.self::TABKILLBOARD.' as k ';
        if ($blnPublic) {
            $query .= ' WHERE k.active = 1';
            $whereConditions = true;
        }
        if($type == 'done') {
            $query .= ($whereConditions) ? ' AND k.isCorporate <> 1 ' : ' WHERE k.isCorporate <> 1 ';
        } elseif($type == 'suffered') {
            $query .= ($whereConditions) ? ' AND k.isCorporate = 1 ' : ' WHERE k.isCorporate = 1 ';
        }
        
        $results = $this->db->get_row( $query );
        return $results->totale;
    }

    /**
     * Return count of results
     * @return Integer Count of total results
     */
    public function countTotalItems() {
        $query   = 'SELECT count(*) as totale FROM '.$this->prefix.self::TABITEM;
        $results = $this->db->get_row( $query );
        return $results->totale;
    }

    /**
     * Return attackers details
     * @param  Integer $killId Killmail Id
     * @param  Array $arrAttackers Array of capsuler Ids
     * @return Object Attackers data object
     */
    public function attackersDetails($killId,$arrAttackers) {
        $objAttackers = new stdClass();
        $objAttackers->names = '';
        $objAttackers->corporates = ' ';

        $flat    = implode(',',$arrAttackers);
        
        $query   = 'SELECT `name`,`corporationId` as corp FROM '.$this->prefix.self::TABCAPSULER.' WHERE `id` IN (%s)';
        $results = $this->db->get_results( vsprintf($query, [$flat]) );
        if(!empty($results) && count($results)>0){
            $objAttackers->names = count($results);
            // fetch only corporates
            foreach ($results as $e) {
                $objAttackers->corporates .= $e->name.',';
            }
        }

        if(empty($objAttackers->names)) {
            $objAttackers->names = 'NPC';
        } else {
            $query = 'SELECT c.name as name FROM '.$this->prefix.self::TABKILLBOARD.' as k '.
                     'LEFT JOIN '.$this->prefix.self::TABCAPSULER.' as c ON k.killingBlow=c.id '.
                     'WHERE k.killmailId = %d';
            $row   = $this->db->get_row( vsprintf($query, [$killId]) );

            $objAttackers->names--;
            // Create descriptive comment
            if($objAttackers->names>1) {
                $objAttackers->names = __('Final blow','jrm_killboard').":&nbsp;<b>{$row->name}</b> <u>".__('and others','jrm_killboard').' '.$objAttackers->names.'</u>';
            } elseif($objAttackers->names>0) {
                $objAttackers->names = __('Final blow','jrm_killboard').":&nbsp;<b>{$row->name}</b> <u>".__('and another one','jrm_killboard').'</u>';
            } else {
                $objAttackers->names = __('Final blow','jrm_killboard').":&nbsp;<b>{$row->name}</b>";
            }
        }

        $objAttackers->corporates = substr($objAttackers->corporates,0,-1);

        return $objAttackers;
    }

    /**
     * Return results form self::TABCAPSULER table based on query params
     * @param  Array $arrId Array of capsulers Id
     * @return Array Array of query results from self::TABCAPSULER table
     */
    public function beAttackers($arrId) {
        $names   = '';
        $flat    = implode(',',$arrId);

        $query   = 'SELECT `name` FROM '.$this->prefix.self::TABCAPSULER.' WHERE `id` IN (%s)';
        $results = $this->db->get_results( vsprintf($query,[$flat]) );
        if(!empty($results)){
            foreach ($results as $row) {
                $names .= $row->name.', ';
            }
            $names = substr($names,0,-2);
        }

        return $names;
    }

    /**
     * Return count of results from self::TABKILLBOARD where worth field is null
     * @return Integer Count of results
     */
    public function countKillsWithNoValue() {
        $query = 'SELECT count(*) as totale FROM '.$this->prefix.self::TABKILLBOARD.' WHERE worth IS NULL';
        $row   = $this->db->get_row( $query );
        return $row->totale;
    }

    /**
     * Store Killmail data
     * @param  String $url ESI Killmail Url
     * @return Object Processing data
     */
    public function recordKill($url) {
        $processData = new stdClass();
        $processData->status = false;

        // Fetch killmail data
        $killDataObj  = $this->fetchKillmail($url);

        if($killDataObj->status) {
            $killData     = $killDataObj->response;
            $killData->hashmail = $killDataObj->hashmail;

            // Main infos
            $id           = $killData->killmail_id;
            $arrAttackers = $killData->attackers;
            $victim       = $killData->victim;

            // Store Victim object, Attackers list and Kill
            $blnVictim    = $this->persistCapsuler($victim->character_id);
            $blnAttackers = $this->persistAttackers($arrAttackers);
            $blnKill      = $this->persistKill($killData);

            if($blnVictim && $blnAttackers && $blnKill) {
                $processData->status = true;
            } else {
                self::appendLog('<span style="color:red;">Record Kill - Something went wrong. ID : '.$id.'</span>');
                $processData->error = __('Error:: Something went wrong and I was not able to store Killmail data',
                                         'jrm_killboard');
                $processData->code = 500;
            }
        } else {
            $processData->error = $killDataObj->error;
            $processData->code = $killDataObj->code;
        }

        return $processData;
    }

    /**
     * Search item price from the price file
     * @param  Integer $id Item Id
     * @return Double Price value
     */
    public function searchPrice($id, $skipDB = false) {
        // Fetch price file
        $upload     = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $upload_dir = $upload_dir . self::DATADIR;
        $rawPrice = file_get_contents($upload_dir.'/price.json');

        $avg = -1;
        $officialLastUpdate = get_option('jrm_killboard_lastSync');
        if(!empty($rawPrice)) {
            $prices = json_decode($rawPrice);
            foreach ($prices as $p) {
                if($p->type_id==$id) {
                    $avg = $p->average_price;
                    break;
                }
            }
        }

        if (!$skipDB) {
            // Search price into DB items
            $query = 'SELECT * FROM '.$this->prefix.self::TABITEM.' WHERE id = %d;';
            $item = $this->db->get_row( vsprintf($query, [$id]) );
            if ($item) {
                $avg = $officialLastUpdate >= $item->lastSync
                        ? $avg
                        : (!is_null($item->price) ? $item->price : $avg );
            }
        }

        return $avg;
    }

    /**
     * Store Item information into self::TABITEM table
     * @param  Integer $id Item Id
     * @param  Double $avg Item value
     * @return Object      Item object
     */
    public function storeItem($id,$avg) {
        $item = $this->fetchItem($id);
        if($item!=false) {
            $table = $this->prefix.self::TABITEM;
            $this->db->insert( $table, [
                'id' => $id, 
                'name' => $item->name, 
                'price' => $avg, 
                'manual' => false,
                'lastSync' => time()
            ],['%d','%s','%f','%d','%d'] );
        }

        return $item;
    }

    /**
     * Retrieve and download price file from ESI endpoint
     * @return Bool Process result
     */
    public static function fetchPriceSet() {
        $status = false;
        // Fetch price file
        $response = wp_remote_get(self::ESIURL.'markets/prices/?datasource=tranquility');
        $responseCode = wp_remote_retrieve_response_code($response);
        $raw = wp_remote_retrieve_body($response);

        if($responseCode == 200 && $raw) {
            $json = json_decode($raw);
            // Store price file
            $upload     = wp_upload_dir();
            $upload_dir = $upload['basedir'];
            $upload_dir = $upload_dir . self::DATADIR;
            if(!file_exists($upload_dir)) {
                self::createUploadDir();
            }
            // Clear previous data
            file_put_contents($upload_dir.'/price.json','');
            // Store new data
            $status = file_put_contents($upload_dir.'/price.json',json_encode($json));
        }

        return $status;
    }

    /**
     * Return statistics from tables
     * @return Array Array of stats
     */
    public function getStats() {
        $arrStats = [];
        $query    = 'SELECT count(*) as totale FROM '.$this->prefix.self::TABKILLBOARD;
        $results  = $this->db->get_row( $query );
        $arrStats['kill'] = $results->totale;

        $query    = 'SELECT count(*) as totale FROM '.$this->prefix.self::TABCAPSULER;
        $results  = $this->db->get_row( $query );
        $arrStats['capsuler'] = $results->totale;

        $query    = 'SELECT count(*) as totale FROM '.$this->prefix.self::TABCORPORATION;
        $results  = $this->db->get_row( $query );
        $arrStats['corporation'] = $results->totale;

        $query    = 'SELECT count(*) as totale FROM '.$this->prefix.self::TABITEM;
        $results  = $this->db->get_row( $query );
        $arrStats['item'] = $results->totale;

        return $arrStats;

    }

    /**
     * Return array of results from self::TABITEM where price IS NULL
     * @return Array Array of results from self::TABITEM
     */
    public function getItemWithoutPrice() {
        $query    = 'SELECT * FROM '.$this->prefix.self::TABITEM.' WHERE price IS NULL;';
        $results  = $this->db->get_results( $query );
        return $results;
    }

    /**
     * Format numbers with fancy text
     * @param  Mixed $n Number
     * @return String Number with fancy style
     */
    public static function niceNumberFormat($n) {
        if($n>1000000000000) { $n = round(($n/1000000000000),1).' T'; }
        else if($n>1000000000) { $n = round(($n/1000000000),1).' B'; }
        else if($n>1000000) { $n =  round(($n/1000000),1).' M'; }
        else if($n>1000) { $n = round(($n/1000),1).' k'; }

        return $n;
    }

    /**
     * Return a hexadecimal color value based on double input value 
     * @param  Double $val Double number
     * @return String Corresponding hexadecimal color value
     */
    public static function getSecurityStatusColor($val) {
        if($val<0.1) { return '#F00000'; }
        elseif($val>=0.1 && $val<0.2) { return '#D73000'; }
        elseif($val>=0.2 && $val<0.3) { return '#F04800'; }
        elseif($val>=0.3 && $val<0.4) { return '#F06000'; }
        elseif($val>=0.4 && $val<0.5) { return '#D77700'; }
        elseif($val>=0.5 && $val<0.6) { return '#EFEF00'; }
        elseif($val>=0.6 && $val<0.7) { return '#8FEF2F'; }
        elseif($val>=0.7 && $val<0.8) { return '#00F000'; }
        elseif($val>=0.8 && $val<0.9) { return '#00EF47'; }
        elseif($val>=0.9 && $val<1.0) { return '#48F0C0'; }
        elseif($val>=1.0) { return '#2FEFEF'; }
    }

    /**
     * Fetch Killmail data form ESI endpoint
     * @param  String $url Url to ESI endpoint
     * @return Object Killmail data
     */
    private function fetchKillmail($url) {
        $objResult = new stdClass();
        $objResult->status = false;
        $objResult->code   = 500;

        // Check url format
        if(preg_match("/^https\:\/\/esi\.evetech\.net\/(latest|v[0-9]{1})\/killmails\/[0-9]+\/[A-Za-z0-9]+/", $url)) {
            $response = wp_remote_get($url);
            $responseCode = wp_remote_retrieve_response_code($response);
            $raw = wp_remote_retrieve_body($response);

            $urlAndParams = explode('?', $url);
            $hashPart = preg_replace("/^https\:\/\/esi\.evetech\.net\/(latest|v[0-9]{1})\/killmails\/[0-9]+\//",'',$urlAndParams[0]);
            if($responseCode == 200 && $raw) {
                $json = json_decode($raw);

                // Check for errors
                if(isset($json->error) || is_null($json)) {
                    self::appendLog('<span style="color:red;">Fetch Killmail - '.$json->error.'</span>');
                    $objResult->status = false;
                    $objResult->error  = __('This Killmail does not exists','jrm_killboard');
                    $objResult->code   = 404;
                } else {
                    // Validate corporation ID based on configured 
                    if(preg_match('/(.*)\"corporation_id\":'.$this->corporationId.'(.*)/',$raw)) {
                        $objResult->status   = true;
                        $objResult->response = $json;
                        $objResult->hashmail = preg_replace("/[^A-Za-z0-9]+/",'',$hashPart);
                    } else {
                        self::appendLog('<span style="color:red;">Killmail does not belong to your corporation : '.$url.'</span>');
                        $objResult->status = false;
                        $objResult->error  = __("Killmail does not belong to your corporation",'jrm_killboard');
                        $objResult->code   = 501;
                    }
                }
            } else {
                $objResult->error  = __('Something went wrong','jrm_killboard');
            }
        } else {
            self::appendLog('<span style="color:red;"> Fetch Killmail - Invalid killmail url</span>');
            $objResult->error  = __('Invalid Killmail link','jrm_killboard');
        }

        return $objResult;
    }

    /**
     * Fetch Item data from Esi endpoint
     * @param  Integer $id Item Id
     * @return String JSON response from the endpoint
     */
    public function fetchItem($id) {
        if(empty($id)) { return false; }
        $url  = self::ESIURL."universe/types/{$id}/?datasource=tranquility&language=en-us";
        $response = wp_remote_get($url);
        $responseCode = wp_remote_retrieve_response_code($response);
        $raw = wp_remote_retrieve_body($response);
        if($responseCode == 200 && $raw) {
            $json = json_decode($raw);
            if(isset($json->error)) {
                $json = false;
            }
        } else {
            return false;
        }

        return $json;
    }

    /**
     * Fetch Capsuler data from Esi endpoint
     * @param  Integer $id Capsuler Id
     * @return String JSON response from the endpoint
     */
    private function fetchCapsuler($id) {
        if(empty($id)) { return false; }
        $url  = self::ESIURL."characters/{$id}/?datasource=tranquility";
        $response = wp_remote_get($url);
        $responseCode = wp_remote_retrieve_response_code($response);
        $raw = wp_remote_retrieve_body($response);
        if($responseCode == 200 && $raw) {
            $json = json_decode($raw);
            if(isset($json->error)) {
                $json = false;
            }
        } else {
            return false;
        }

        return $json;
    }

    /**
     * Fetch Corporation data from Esi endpoint
     * @param  Integer $id Corporation Id
     * @return String JSON response from the endpoint
     */
    private function fetchCorporation($id) {
        if(empty($id)) { return false; }
        $url  = self::ESIURL."corporations/{$id}/?datasource=tranquility";
        $response = wp_remote_get($url);
        $responseCode = wp_remote_retrieve_response_code($response);
        $raw = wp_remote_retrieve_body($response);
        if($responseCode == 200 && $raw) {
            $json = json_decode($raw);
            if(isset($json->error)) {
                $json = false;
            }
        } else {
            return false;
        }

        return $json;
    }

    /**
     * Fetch System data from Esi endpoint
     * @param  Integer $id System Id
     * @return String JSON response from the endpoint
     */
    public function fetchSystem($id) {
        if(empty($id)) { return false; }
        $url  = self::ESIURL."universe/systems/{$id}/?datasource=tranquility&language=en-us";
        $response = wp_remote_get($url);
        $responseCode = wp_remote_retrieve_response_code($response);
        $raw = wp_remote_retrieve_body($response);
        if($responseCode == 200 && $raw) {
            $json = json_decode($raw);
            if(isset($json->error)) {
                $json = false;
            }
        } else {
            return false;
        }

        return $json;
    }

    /**
     * Store Killmail info on DB
     * @param  Object $obj Killmail Object
     * @return Bool Process result
     */
    private function persistKill($obj) {
        $result = false;

        // Search for existing kill
        $exist = $this->verifyKill($obj->killmail_id);
        if($exist==false) {
            $table       = $this->prefix.self::TABKILLBOARD;
            $killingBlow = -1;
            $attackers   = [];

            // Populate Killmail data props
            foreach ($obj->attackers as $elem) {
                if(isset($elem->character_id)) {
                    $attackers[] = $elem->character_id;
                    if($elem->final_blow) {
                        $killingBlow = $elem->character_id;
                    }
                } else {
                    $attackers[] = -1;
                }
            }

            $damageTaken         = $obj->victim->damage_taken;
            $items               = $obj->victim->items;

            $ship                = new stdClass();
            $ship->ship_type_id  = $obj->victim->ship_type_id;
            $items[]             = $ship;

            // Store Ship data
            $blnStoreShip        = $this->persistShip($obj->victim->ship_type_id);
            // Fetch System data
            $systemObj           = $this->fetchSystemInfo($obj->solar_system_id);

            // If all required infos is available
            if($blnStoreShip && $systemObj!=false) {
                $data   = [
                    'killmailId'     => $obj->killmail_id,
                    'hash'           => $obj->hashmail,
                    'systemName'     => $systemObj->name,
                    'securityStatus' => round($systemObj->security,1),
                    'attackers'      => json_encode($attackers),
                    'victimId'       => $obj->victim->character_id,
                    'isCorporate'    => ($obj->victim->corporation_id == $this->corporationId),
                    'damageTaken'    => $damageTaken,
                    'killingBlow'    => $killingBlow,
                    'shipId'         => $obj->victim->ship_type_id,
                    'items'          => json_encode($items),
                    'killTime'       => strtotime($obj->killmail_time)
                ];
                $format = ['%d','%s','%s','%f','%s','%d','%d','%d','%d','%d','%s','%d'];
                $result = $this->db->insert($table,$data,$format);
            }
        } else {
            $result = true;
        }

        return $result;
    }

    /**
     * Store Capsuler info on DB
     * @param  Integer $id Capsuler Id
     * @return Bool Process result
     */
    private function persistCapsuler($id) {
        $result = false;

        // Search for existing capsuler
        $exists = $this->verifyCapsuler($id);
        if($exists==false) {
            $table  = $this->prefix.self::TABCAPSULER;

            // Fetch Capsuler data
            $capsuler = $this->fetchCapsuler($id);
            if($capsuler!=false) {
                $data   = [
                    'id'            => $id,
                    'name'          => utf8_encode($capsuler->name),
                    'allianceId'    => isset($capsuler->alliance_id) ? $capsuler->alliance_id : null,
                    'corporationId' => $capsuler->corporation_id,
                    'lastSync'      => (time()+(30*24*60*60))
                ];
                $format = ['%d','%s','%d','%d','%d'];
                $result = $this->db->replace($table,$data,$format);
                // If required data is available
                if($result) {
                    // Search for existing corporation
                    $exists = $this->verifyCorporation($capsuler->corporation_id);
                    if($exists==false) {
                        // Store corporation data
                        $result = $this->persistCorporation($capsuler->corporation_id);
                    } else {
                        $result = true;
                    }
                }
            }
        } else {
            $result = true;
        }

        return $result;
    }

    /**
     * Store Ship info on DB
     * @param  Integer $id Ship Id
     * @return Bool Process result
     */
    private function persistShip($id) {
        $result = false;

        // Search for existing ship
        $exists = $this->verifyItem($id);
        if($exists==false) {
            $table  = $this->prefix.self::TABITEM;

            $item = $this->fetchItem($id);
            $avg = $this->searchPrice($id);

            if($item!=false) {
                $data   = [
                    'id'            => $id,
                    'name'          => utf8_encode($item->name),
                    'price'         => $avg,
                    'manual'        => 0,
                    'lastSync'      => (time()+(30*24*60*60))
                ];
                $format = ['%d','%s','%f','%d','%d'];
                $result = $this->db->replace($table,$data,$format);
            }
        } else {
            $result = true;
        }

        return $result;
    }

    /**
     * Store kill related capsulers info on DB
     * @param  Array $arrayIds Capsuler Ids
     * @return Bool Process result
     */
    private function persistAttackers($arrayIds) {
        $result    = false;
        $arrResult = [];

        foreach ($arrayIds as $attacker) {
            if(isset($attacker->character_id)) {
                $arrResult[] = $this->persistCapsuler($attacker->character_id);
            }
        }

        if(!in_array(false,$arrResult)) {
            $result = true;
        }

        return $result;
    }

    /**
     * Store Corporation info on DB
     * @param  Integer $id Corporation Id
     * @return Bool Process result
     */
    private function persistCorporation($id) {
        $result = false;
        $table  = $this->prefix.self::TABCORPORATION;

        // Search for existing corporation
        $corporation = $this->fetchCorporation($id);
        if($corporation!=false) {
            $data   = [
                'id'       => $id,
                'name'     => utf8_encode($corporation->name),
                'lastSync' => (time()+(30*24*60*60))
            ];
            $format = ['%d','%s','%d'];
            $result = $this->db->replace($table,$data,$format);
        }

        return $result;
    }

    /**
     * Fetch System info from ESI endpoint
     * @param  Integer $systemId System Id
     * @return Object System object response
     */
    private function fetchSystemInfo($systemId) {
        $systemObj      = false;

        // Search for existing system
        $systemResponse = $this->fetchSystem($systemId);
        if($systemResponse!=false) {
            $systemObj           = new stdClass();
            $systemObj->name     = $systemResponse->name;
            $systemObj->security = round(floatval($systemResponse->security_status),1);
        }

        return $systemObj;
    }

    /**
     * Verify Killmail existence
     * @param  Integer $id Killmail Id
     * @return Bool Killmail existence
     */
    public function verifyKill($id) {
        $response = true;
        $query    = 'SELECT * FROM '.$this->prefix.self::TABKILLBOARD.' WHERE `killmailId` = %d';
        $result   = $this->db->get_row( vsprintf($query, [$id]) );
        if(is_null($result)) {
            $response = false;
        }

        return $response;
    }

    /**
     * Add Killmail to processing queue
     * @param  Integer $id Killmail Id
     * @return Bool Insertion status
     */
    public function push($killdata) {

        $table  = $this->prefix.self::TABQUEUE;
        $data   = ['killmailId' => $killdata->killmail_id, 'hash' => $killdata->killmail_hash, 'status' => 0];
        $format = ['%d', '%s', '%d'];
        $result = $this->db->insert($table,$data,$format);

        return $result;
    }

    /**
     * Verify Capsuler existence
     * @param  Integer $id Capsuler Id
     * @return Bool Capsuler existence
     */
    private function verifyCapsuler($id) {
        $response = true;

        $query    = 'SELECT * FROM '.$this->prefix.self::TABCAPSULER.' WHERE `id` = %d';
        $result   = $this->db->get_row( vsprintf($query, [$id]) );
        if(is_object($result)) {
            $sync = intval($result->lastSync);
            if(isset($result->lastSync) && time()>$sync) {
                $response = false;
            }
        } elseif(is_null($result)) {
            $response = false;
        }

        return $response;
    }

    /**
     * Verify Corporation existence
     * @param  Integer $id Corporation Id
     * @return Bool Corporation existence
     */
    private function verifyCorporation($id) {
        $response = true;

        $query    = 'SELECT * FROM '.$this->prefix.self::TABCORPORATION.' WHERE `id` = %d';
        $result   = $this->db->get_row( vsprintf($query, [$id]) );
        if(is_object($result)) {
            $sync = intval($result->lastSync);
            if(isset($result->lastSync) && time()>$sync) {
                $response = false;
            }
        } elseif(is_null($result)) {
            $response = false;
        }

        return $response;
    }

    /**
     * Verify Item existence
     * @param  Integer $id Item Id
     * @return Bool Item existence
     */
    public function verifyItem($id) {
        $query    = 'SELECT * FROM '.$this->prefix.self::TABITEM.' WHERE `id` = %d';
        $result   = $this->db->get_row( vsprintf($query,[$id]) );
        if(is_null($result)) {
            $result = false;
        }

        return $result;
    }

    /**
     * Perform OAuth 2.0 authentication on ESI API
     * @param  Integer $oauthVersion OAuth version
     * @param  Integer $clientId    Client Id
     * @param  String $clientSecret Client Secret
     * @param  String $code         Unique code request
     * @return Bool Processing result
     */
    public static function performSSOAuthentication($oauthVersion,$clientId,$clientSecret,$code) {
        /**
         * SSO Flow - References
         * @link https://github.com/esi/esi-docs/blob/master/docs/sso/web_based_sso_flow.md
         */

        self::appendLog('Start SSO ESI Authentication');

        $body = [
            'grant_type' => 'authorization_code',
            'code' => $code
        ];

        $headers = [
            'Authorization' => 'Basic '.base64_encode($clientId.':'.$clientSecret),
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Host' => 'login.eveonline.com',
        ];
         
        $args = array(
            'method' => 'POST',
            'body' => $body,
            'timeout' => '10',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => $headers,
            'cookies' => []
        );

        $authLink = $oauthVersion == '1' ? self::ESITOKEN : self::ESITOKENV2;
         
        $response = wp_remote_post( $authLink, $args );
        $responseCode = wp_remote_retrieve_response_code($response);
        $raw = wp_remote_retrieve_body($response);
        $esiResponse = json_decode($raw);

        if($responseCode == 200 && $raw) {
            // Everything ok : we store access/refresh token for future calls
            if(isset($esiResponse->access_token) && isset($esiResponse->refresh_token)) {
                self::appendLog('<span style="color:green;">Account Synced with ESI API</span>');
                update_option('jrm_killboard_esi_expires_in', time()+intval($esiResponse->expires_in));
                update_option('jrm_killboard_esi_access_token', $esiResponse->access_token);
                update_option('jrm_killboard_esi_refresh_token', $esiResponse->refresh_token);

                return true;
            } elseif(isset($esiResponse->error)) {
                self::appendLog('<span style="color:red;">'.$esiResponse->error_description.'</span>');
            }
        } else {
            $error = self::httpErrorText($responseCode,$esiResponse->error_description);
            self::appendLog('<span style="color:red;">'.$error.'</span>');
        }

        return false;
    }

    /**
     * Clear log
     *
     * @return void
     */
    public function clearQueue() {
        self::appendLog('Clear killmails queue');
        $table = $this->prefix.self::TABQUEUE;
        $clear = "DELETE FROM {$table} WHERE `status` >= 2";
        $this->db->query($clear);
    }

    /**
     * Process killmail from queue
     * @return Bool Processing result
     */
    public function processQueue() {
        self::appendLog('Process killmails queue');
        $table = $this->prefix.self::TABQUEUE;
        $query = "SELECT * FROM {$table} WHERE `status` < 2";
        $rows = $this->db->get_results($query);
        if(!is_null($rows) && !empty($rows)) {
            $count = 0;
            foreach ($rows as $killdata) {
                $count++;
                $id = $killdata->killmailId;
                $hash = $killdata->hash;
                // Set queue element to processing
                $this->db->update( $table, ['status' => 1],['killmailId' => $id],['%d'],['%d'] );

                $url = "https://esi.evetech.net/latest/killmails/{$id}/{$hash}/?datasource=tranquility";
                $response = $this->recordKill($url);
                $status = isset($response->code) ? $response->code : 2;
                $this->db->update( $table, ['status' => $status],['killmailId' => $id],['%d'],['%d'] );
            }
            self::appendLog("Found {$count} new killmails");
        }
        self::appendLog('Process killmails complete');
        self::appendLog('Starting worth value');
        $this->calculateKillsWorth();
        self::appendLog('Worth value ended');

        return true;
    }

    /**
     * Calculate kill worth
     * @return String JSON processing result
     */
    public function calculateKillsWorth() {
        $upload     = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $upload_dir = $upload_dir . self::DATADIR;
        // Check if price JSON file has been downloaded
        if(file_exists($upload_dir.'/price.json')) {
            $table  = self::TABKILLBOARD;
            // Select killmail with worth price empty
            $query  = "SELECT killmailId,items FROM {$this->db->prefix}{$table} WHERE `worth` IS null;";
            $rows   = $this->db->get_results($query);
            if(!empty($rows)){
                //$round  = 0;
                foreach ($rows as $i) {
                    $items  = [];
                    $jItems = json_decode($i->items);
                    if(!empty($jItems)) {
                        foreach ($jItems as $e) {
                            // For each item sum up quantity
                            if(isset($e->item_type_id)) {
                                if(!isset($items[$e->item_type_id])) {
                                    $items[$e->item_type_id] = 0;
                                }
                                if(isset($e->quantity_dropped)) {
                                    $items[$e->item_type_id] += $e->quantity_dropped;
                                }
                                if(isset($e->quantity_destroyed)) {
                                    $items[$e->item_type_id] += $e->quantity_destroyed;
                                }
                            } else {
                                $items[$e->ship_type_id] = 1;
                            }
                        }
                    }

                    if(!empty($items)) {
                        // Attach items to kill and worth it
                        $this->associateItemsAndWorth($i->killmailId,$items);
                    }
                    /*$round++;
                    if($round>10) {
                        break;
                    }*/
                }
            }

            return ['status' => true];
        } else {
            return [
                'status' => false, 
                'error' => __('Missing Price file','jrm_killboard').':'.$upload_dir.'/price.json'
            ];
        }
    }

    /**
     * Attach total value to killmail
     * @return String JSON processing result
     */
    public function associateItemsAndWorth($killId,$items) {
        $total           = 0;
        $blnMissingPrice = false;

        foreach ($items as $id => $quantity) {
            // Fetch item price
            $itemPrice = $this->searchPrice($id);
            if($itemPrice == -1) {
                $blnMissingPrice = true;
                $itemPrice = null;
            }
            $exists = $this->verifyItem($id);
            if($exists==false) {
                // If not exist try to store it
                $this->storeItem($id,$itemPrice);
            } else {
                if($exists->manual) {
                    $itemPrice = $exists->price;
                    $blnMissingPrice = false;
                }
                $table   = $this->db->prefix.self::TABITEM;
                $this->db->update( $table, ['price' => $itemPrice],['id' => $id],['%f'],['%d'] );
            }
            $total += $itemPrice*$quantity;
        }

        if($blnMissingPrice==false) {
            $table  = $this->db->prefix.self::TABKILLBOARD;
            $this->db->update( $table, ['worth' => $total],['killmailId' => $killId],['%f'],['%d'] );
        }
    }

    /**
     * Clear admin log
     * @return void
     */
    public static function clearLog() {
        // Update log
        $upload     = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $upload_dir = $upload_dir . JRMKillboard::DATADIR;
        $logFile = $upload_dir.'/processing.log';
        $date = date('Y-m-d H:i:s');
        $log = $date." Log created\n";
        file_put_contents($logFile,$log);    
    }

    /**
     * Clear admin log
     * @param string $logString String to be appended to the log
     * @return void
     */
    public static function appendLog($logString) {
        // Update log
        $upload     = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $upload_dir = $upload_dir . JRMKillboard::DATADIR;
        $logFile = $upload_dir.'/processing.log';

        $date = date('Y-m-d H:i:s');
        $log = file_get_contents($logFile);
        $log = '<div>'.$date.' : '.$logString."</div>".$log;
        file_put_contents($logFile,$log);
    }

    /**
     * Create the upload dir if needed
     * @return void
     */
    public static function createUploadDir() {
        $upload     = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $upload_dir = $upload_dir . self::DATADIR;
        if (! is_dir($upload_dir)) {
            mkdir( $upload_dir, 0755 );
        }
        chmod($upload_dir,0775);
    }

    /**
     * Return HTTP error code descriptive string
     *
     * @param Integer $responseCode HTTP Error code
     * @param Object $esiResponse ESI Api response object
     * @return String
     */
    public static function httpErrorText($responseCode,$textResponse) {
        if (empty($textResponse)) {
            switch ($responseCode) {
                case 400 : $textResponse = __('Error 400 : Bad Request','jrm_killboard'); break;
                case 401 : $textResponse = __('Error 401 : Unauthorized','jrm_killboard'); break;
                case 403 : $textResponse = __('Error 403 : Forbidden','jrm_killboard'); break;
                case 404 : $textResponse = __('Error 404 : Not Found','jrm_killboard'); break;
                case 408 : $textResponse = __('Error 408 : Request Timeout','jrm_killboard'); break;
                case 500 : $textResponse = __('Error 500 : Internal Server Error','jrm_killboard'); break;
                case 502 : $textResponse = __('Error 502 : Bad Gateway','jrm_killboard'); break;
                case 503 : $textResponse = __('Error 503 : Service Unavailable','jrm_killboard'); break;
                case 504 : $textResponse = __('Error 504 : Gateway Timeout','jrm_killboard'); break;
            }
        }

        return $textResponse;
    }

    /**
     * Return an array of items from Items table
     *
     * @param Integer $killmailId Killmail ID
     * @return Array Return array of items per killmail
     */
    public function fetchItemsList($killmailId) {
        $itemsList = false;
        $query = 'SELECT * FROM '.$this->prefix.self::TABKILLBOARD.' WHERE killmailId = %d;';
        $kill = $this->db->get_row( vsprintf($query, [$killmailId]) );
        if ($kill) {
            $items = json_decode($kill->items);
            foreach ($items as $item) {
                if (isset($item->item_type_id)) {
                    $itemsList[$item->item_type_id] = [
                        'id' => $item->item_type_id,
                        'quantity' => isset($item->quantity_dropped) ? $item->quantity_dropped : null
                    ];
                } else {
                    $itemsList[$item->ship_type_id] = [
                        'id' => $item->ship_type_id,
                        'quantity' => null
                    ];
                }
            }
            $ids = implode(',', array_keys($itemsList));
            $query = 'SELECT id,name FROM '.$this->prefix.self::TABITEM.' WHERE id IN ('.$ids.');';
            $itemsData = $this->db->get_results( $query );
            if (count($itemsList) != count($itemsData)) {
                return 'missing';
            }
            foreach ($itemsData as $item) {
                $itemsList[$item->id]['name'] = $item->name;
            }
            $itemsList = array_reverse(array_values($itemsList));
        }

        return $itemsList;
    }

    /**
     * Update items prices
     *
     * @return void
     */
    public function updateItemsPrice() {
        $table = $this->prefix.self::TABITEM;
        // Fetch all items
        $itemsList = $this->getItems(0,false);
        foreach ($itemsList as $item) {
            $avg = $this->searchPrice($item->id, true);
            if ($avg != -1) {
                $this->db->update( $table, ['price' => $avg, 'lastSync' => time() ],['id' => $item->id],['%f'],['%d'] );
            }
        }
    }
}
