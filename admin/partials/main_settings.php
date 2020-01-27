<div class="container-fluid" style="font-size: x-small;">
    <div style="background-image: url('https://web.ccpgamescdn.com/triallandingassets/new/eve-online.png'); background-repeat: no-repeat; padding: 2px; height: 50px; background-color: #000;"><h3 style="margin-left: 200px; color: #fff;">JRM Killboard :: <?php _e('Settings','jrm_killboard') ?></h3></div>
    <div class="row">
        <div class="col">
            <form id="setting_form">
            <input type="hidden" name="wpopnn" id="wpopnn" value="<?php echo wp_create_nonce('jrm_killboard_op_nonce') ?>" />
            <input type="hidden" id="loading-message" value="<?php _e('..Please wait..','jrm_killboard') ?>" />
            <input type="hidden" id="loading-long-message" value="<?php _e('..Please wait. Processing may take some time to complete..','jrm_killboard') ?>" />
            <div class="row">
                <div class="col" style="margin: auto; padding: auto;">
                    <h6><?php _e('General Settings','jrm_killboard') ?></h6><br>
                    <div id="esiconfig" class="border" style="padding: 5px;">
                        <p><?php _e('ESI Client Configurations','jrm_killboard') ?></p>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Corporation Id','jrm_killboard') ?></span></div>
                            <input type="text" class="form-control" id="corporation_id" name="corporation_id" placeholder="<?php _e('Corporation Id','jrm_killboard') ?>" value="<?php echo $corporationId ?>">
                        </div>
                        <p><b><?php echo sprintf(__('Auto Synchronization requires ESI Application. You can create one %s if you don\'t have it','jrm_killboard'),'<a href="https://developers.eveonline.com/applications" target="_blank">'.__('here','jrm_killboard').'</a>') ?></b></p>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Client Id','jrm_killboard') ?></span></div>
                            <input type="text" class="form-control" id="esi_client_id" name="esi_client_id" placeholder="<?php _e('Client Id','jrm_killboard') ?>" value="<?php echo $esiClientId ?>" <?php if($esiStatus) { echo 'disabled'; } ?>>
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Client Secret','jrm_killboard') ?></span></div>
                            <input type="password" class="form-control" id="esi_client_secret" name="esi_client_secret" placeholder="<?php _e('Client Secret','jrm_killboard') ?>" value="<?php echo !empty($esiClientSecret) ? '************' : '' ?>" <?php if($esiStatus) { echo 'disabled'; } ?>>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('OAuth Version','jrm_killboard') ?></span></div>
                                    <?php if(!empty($esiClientId) && !empty($esiClientSecret)) : ?>
                                    <input type="text" class="form-control" id="oauth" name="oauth" value="<?php echo $oauth ?>" disabled="">
                                    <?php else : ?>
                                    <div class="form-check form-check-inline" style="margin-left: 5px;">
                                        <input class="form-check-input" type="radio" name="oauth" id="oauth_v1" value="1" <?php if($oauth=='1') { echo 'checked'; } ?>>
                                        <label class="form-check-label"><?php _e('v1','jrm_killboard') ?></label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="oauth" id="oauth_v2" value="2" <?php if($oauth=='2') { echo 'checked'; } ?>>
                                        <label class="form-check-label"><?php _e('v2 (Recommended)','jrm_killboard') ?></label>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Synchronization','jrm_killboard') ?></span></div>
                                    <select class="custom-select" name="max_sync" id="max_sync">
                                        <?php foreach (JRMKillboard::getSyncOptions() as $key => $value) : ?>
                                            <option <?php if($key == $maxSync) { echo 'selected'; } ?> value="<?php echo $key ?>"><?php echo $value ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <p style="font-size: x-small;"><?php echo sprintf(__('Synchronization can be achieved using %s. We discourage the use of it, especially for heavy load platform or if you have direct access to system cron or external service. ','jrm_killboard'),'<a href="https://developer.wordpress.org/plugins/cron/" target="_blank">WP-Cron</a>') ?></p>
                        <div class="input-group mb-3">
                            <button class="btn btn-sm btn-outline-primary" type="button" onclick="saveConfig()"><?php _e('Save configurations','jrm_killboard') ?></button>
                        </div>
                        <div class="input-group mb-3">
                            <?php _e('ESI SSO Authentication','jrm_killboard') ?>
                        </div>
                        <div class="row">
                            <div class="col-3 inline-block">
                                <div class="alert alert-<?php echo ($esiStatus) ? 'success' : 'danger' ?>">
                                    <?php _e('Status','jrm_killboard') ?> : <?php echo ($esiStatus) ? _e('Synched','jrm_killboard') : _e('Offline','jrm_killboard') ?>
                                </div>
                            </div>
                            <?php if(!$esiStatus && !empty($esiClientId) && !empty($esiClientSecret)) : ?>
                            <div class="col-5 inline-block">
                                <button class="btn" data-auth-link="<?php echo $oauthLink ?>" data-esi-id="<?php echo $esiClientId ?>" data-esi-state="<?php echo $esiUniqueCode ?>" data-esi-scope="esi-killmails.read_corporation_killmails.v1" data-redirect="<?php echo $pluginPageUrl ?>" onclick="initAuthorization(this)" type="button">
                                    <?php //_e('EVE SSO Authenticate','jrm_killboard') ?>
                                    <img src="https://web.ccpgamescdn.com/eveonlineassets/developers/eve-sso-login-black-small.png" />
                                </button>
                            </div>
                            <?php endif; ?>
                            <?php if(!empty($esiClientId) && !empty($esiClientSecret)) : ?>
                            <div class="col-3 inline-block">
                                <button class="btn btn-danger" onclick="removeAuthorization()" type="button"><?php _e('Remove','jrm_killboard') ?></button>
                            </div>
                            <?php endif; ?>
                        </div>
                        <p style="font-size: x-small;">
                            <?php if(!empty($esiStatus)) { 
                                $externalPriceCronLink = str_replace('ep-kills', 'ep-prices', $externalCronLink);
                                echo __('Cronjob Access Point: ','jrm_killboard')."<br>Kills:&nbsp;<b>{$externalCronLink}</b><br>Prices:&nbsp;<b>{$externalPriceCronLink}</b>"; 
                            } ?>
                        </p>
                        <?php _e('External Cronjob Configuration','jrm_killboard') ?>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Endpoints Name','jrm_killboard') ?></span></div>
                            <input type="text" class="form-control" id="cron_endpoint" name="cron_endpoint" value="<?php echo $cronjobEndpoint ?>">
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Endpoints Secret','jrm_killboard') ?></span></div>
                            <input type="text" class="form-control" id="cron_secret" name="cron_secret" value="<?php echo $cronjobSecret ?>">
                            <div class="input-group-append"><button class="btn btn-secondary" onclick="generateToken()" type="button"><?php _e('Random','jrm_killboard') ?></button></div>
                        </div>
                        <div class="input-group mb-3">
                            <button class="btn btn-sm btn-outline-primary" type="button" onclick="saveConfig()"><?php _e('Save configurations','jrm_killboard') ?></button>
                        </div>
                    </div>
                </div>
                <div class="col" style="margin: auto; padding: auto; min-height: 700px;">
                    <h6><?php _e('Graphic Settings','jrm_killboard') ?></h6><br>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Killboard title','jrm_killboard') ?></span></div>
                        <input type="text" class="form-control" id="page_title" name="page_title" placeholder="<?php _e('Killboard title','jrm_killboard') ?>" value="<?php echo $title ?>">
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Margin','jrm_killboard') ?></span></div>
                                <input type="text" class="form-control" id="margin" name="margin" placeholder="20px" value="<?php echo $margin ?>">
                            </div>
                        </div>
                        <div class="col">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Padding','jrm_killboard') ?></span></div>
                                <input type="text" class="form-control" id="padding" name="padding" placeholder="20px" value="<?php echo $padding ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Elements per page','jrm_killboard') ?></span></div>
                                <select class="custom-select" name="elements" id="elements">
                                    <?php foreach (JRMKillboard::getElementsOptions() as $value) : ?>
                                        <option <?php if($value == $elements) { echo 'selected'; } ?> value="<?php echo $value ?>"><?php echo $value ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Show kills','jrm_killboard') ?></span></div>
                                <select class="custom-select" name="kill_type" id="kill_type">
                                    <option value="all" <?php if($killType=='all') { echo 'selected'; } ?>><?php _e('All','jrm_killboard') ?></option>
                                    <option value="done" <?php if($killType=='done') { echo 'selected'; } ?>><?php _e('Done','jrm_killboard') ?></option>
                                    <option value="suffered" <?php if($killType=='suffered') { echo 'selected'; } ?>><?php _e('Suffered','jrm_killboard') ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Font size','jrm_killboard') ?></span></div>
                                <select class="custom-select" name="font_size" id="font_size">
                                    <?php foreach(JRMKillboard::getFontSize() as $size) : ?>
                                    <option value="<?php echo $size ?>" <?php if($fontSize == $size) { echo 'selected'; } ?>><?php echo $size ?></option>
                                    <?php endforeach; ?> 
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Image size','jrm_killboard') ?></span></div>
                                <select class="custom-select" name="image_size" id="image_size">
                                    <?php foreach(JRMKillboard::getImageSize() as $imgSize => $desc) : ?>
                                    <option value="<?php echo $imgSize ?>" <?php if($imageSize == $imgSize) { echo 'selected'; } ?>><?php echo $desc ?></option>
                                    <?php endforeach; ?> 
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group" style="margin: auto;">
                        <?php _e('Kills done','jrm_killboard') ?>
                        <div class="row">
                            <div class="col">
                                <div class="input-group mb-3" onmouseout="colorPreview(this)">
                                    <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Background color','jrm_killboard') ?></span></div>
                                    <input type="text" class="form-control" id="bg_kill" name="bg_kill" placeholder="#fff" value="<?php echo $killsBg ?>">
                                    <input style="background-color: <?php echo $killsBg ?>" type="text" class="form-control col-2 quickview" disabled>
                                </div>
                            </div>
                            <div class="col">
                                <div class="input-group mb-3" onmouseout="colorPreview(this)">
                                    <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Text color','jrm_killboard') ?></span></div>
                                    <input type="text" class="form-control" id="text_kill" name="text_kill" placeholder="#000" value="<?php echo $killsText ?>">
                                    <input style="background-color: <?php echo $killsText ?>" type="text" class="form-control col-2 quickview" disabled>
                                </div>
                            </div>
                        </div>
                        <?php _e('Kills suffered','jrm_killboard') ?>
                        <div class="row">
                            <div class="col">
                                <div class="input-group mb-3" onmouseout="colorPreview(this)">
                                    <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Background color','jrm_killboard') ?></span></div>
                                    <input type="text" class="form-control" id="bg_corporate_kill" name="bg_corporate_kill" placeholder="red" value="<?php echo $deathBg ?>">
                                    <input style="background-color: <?php echo $deathBg ?>" type="text" class="form-control col-2 quickview" disabled>
                                </div>
                            </div>
                            <div class="col">
                                <div class="input-group mb-3" onmouseout="colorPreview(this)">
                                    <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Text color','jrm_killboard') ?></span></div>
                                    <input type="text" class="form-control" id="text_corporate_kill" name="text_corporate_kill" placeholder="white" value="<?php echo $deathText ?>">
                                    <input style="background-color: <?php echo $deathText ?>" type="text" class="form-control col-2 quickview" disabled>
                                </div>
                            </div>
                        </div>
                        <?php _e('Header,table header and footer settings','jrm_killboard') ?>
                        <div class="row">
                            <div class="col">
                                <div class="input-group mb-3" onmouseout="colorPreview(this)">
                                    <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Background color','jrm_killboard') ?></span></div>
                                    <input type="text" class="form-control" id="footer_color" name="footer_color" placeholder="lightgray" value="<?php echo $footerColor ?>">
                                    <input style="background-color: <?php echo $footerColor ?>" type="text" class="form-control col-2 quickview" disabled>
                                </div>
                            </div>
                            <div class="col">
                                <div class="input-group mb-3" onmouseout="colorPreview(this)">
                                    <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Text color','jrm_killboard') ?></span></div>
                                    <input type="text" class="form-control" id="footer_text" name="footer_text" placeholder="white" value="<?php echo $footerText ?>">
                                    <input style="background-color: <?php echo $footerText ?>" type="text" class="form-control col-2 quickview" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Display Developer Sign on Frontend','jrm_killboard') ?></span></div>
                            <div class="form-check form-check-inline" style="margin-left: 5px;">
                              <input class="form-check-input" type="radio" name="dev_sign" id="dev_sign_yes" value="show" <?php if($devSign) { echo 'checked'; } ?>>
                              <label class="form-check-label"><?php _e('Yes','jrm_killboard') ?></label>
                            </div>
                            <div class="form-check form-check-inline">
                              <input class="form-check-input" type="radio" name="dev_sign" id="dev_sign_no" value="hide" <?php if(!$devSign) { echo 'checked'; } ?>>
                              <label class="form-check-label"><?php _e('No','jrm_killboard') ?></label>
                            </div>
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Table columns','jrm_killboard') ?></span></div>
                            <select class="custom-select" multiple name="cols" id="cols">
                            <?php foreach (JRMKillboard::getTableColumns() as $key => $value) : ?>
                                <option <?php if(in_array($key,$cols)) { echo 'selected'; } ?> value="<?php echo $key ?>"><?php echo $value ?></option>
                            <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Logs','jrm_killboard') ?></span></div>
                            <button title="~ <?php echo $logSize ?> KB" class="btn btn-sm btn-outline-primary" type="button" onclick="getLog()"><?php _e('Read log','jrm_killboard') ?></button>
                            <button class="btn btn-sm btn-outline-danger" type="button" onclick="clearLog()"><?php _e('Clear log','jrm_killboard') ?></button>
                        </div>
                    </div>
                </div>
            </div>
            </form>
        </div>
        <div class="col-2">
            <h6 style="margin-top: 10px;"><?php _e('Statistics','jrm_killboard') ?></h6>
            <div id="jrm_alert" align="center" class="m-2 alert alert-success p-2"><?php _e('Ready','jrm_killboard') ?></div>
            <ul style="font-size: small;">
                <li><?php echo __('Capsuler','jrm_killboard').' : '.$stats['capsuler'] ?></li>
                <li><?php echo __('Killmails','jrm_killboard').' : '.$stats['kill'] ?></li>
                <li><?php echo __('Corporations','jrm_killboard').' : '.$stats['corporation'] ?></li>
                <li><?php echo __('Items','jrm_killboard').' : '.$stats['item'] ?></li>
                <?php if(!$esiStatus) : ?>
                <li><?php echo __('Price synced','jrm_killboard').' : '.date('Y-m-d H:i:s',$lastSync) ?></li>
                <?php endif; ?>
            </ul>
            <button class="btn btn-sm btn-outline-primary" type="button" onclick="syncPrice()"><?php _e('Sync price','jrm_killboard') ?></button><br><br>
            <button class="btn btn-sm btn-outline-primary" type="button" onclick="saveConfig()"><?php _e('Save configurations','jrm_killboard') ?></button>
            <?php if($esiStatus) : ?>
            <h6 style="margin-top: 10px;"><?php _e('Processing Logs','jrm_killboard') ?></h6>
            <?php if(!empty($killmailError) || !empty($killmailLog) || $processingFailed) : ?>
            <div class="alert <?php echo (!empty($killmailError) || $processingFailed) ? 'alert-danger' : ($processTime && !$processingFailed ? 'alert-warning' : 'alert-success') ?>"><?php echo $processingFailed ? __('Processed failed. Please restart it') : ((!empty($killmailError)) ? $killmailError : $killmailLog) ?></div>
            <?php endif; ?> 
            <?php if(!empty($priceError) || !empty($priceLog)) : ?>
            <div class="alert <?php echo (!empty($priceError)) ? 'alert-danger' : 'alert-success' ?>"><?php echo (!empty($priceError)) ? $priceError : $priceLog ?></div>
            <?php endif; ?> 
            <?php endif; ?> 
        </div>
    <span id='message_container' 
        data-error-text_corporate_kill="<?php _e('Suffered kill text color missing','jrm_killboard') ?>"
        data-error-bg_corporate_kill="<?php _e('Suffered kill background color missing','jrm_killboard') ?>"
        data-error-text_kill="<?php _e('Kill text color missing','jrm_killboard') ?>"
        data-error-bg_kill="<?php _e('Kill background color missing','jrm_killboard') ?>"
        data-error-corporation_id="<?php _e('Corporation ID missing','jrm_killboard') ?>"
        data-error-text_corporate_kill="<?php _e('Suffered kill text color missing','jrm_killboard') ?>"
        data-error-footer_color="<?php _e('Other color missing','jrm_killboard') ?>"
        data-error-footer_text="<?php _e('Other text color missing','jrm_killboard') ?>">
    </span>
    <?php include 'copyright_footer.php' ?>
</div>
<div id="modal_log" class="modal fade show" tabindex="-1" role="dialog" style="padding: 10px; font-size: small;">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?php _e('Logs','jrm_killboard') ?></h5>
        <button type="button" class="close" onclick="hideLogs()">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div id="modal_content" class="modal-body" style="padding: 10px; overflow-y: auto; max-height: 500px;"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm" onclick="hideLogs()"><?php _e('Close') ?></button>
      </div>    
  </div>
</div>