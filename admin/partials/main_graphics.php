<div class="card-header">
    <div style="background-image: url('https://web.ccpgamescdn.com/triallandingassets/new/eve-online.png'); background-repeat: no-repeat; padding: 2px; width: 100%; height: 50px; background-color: #000;"><h3 style="margin-left: 200px; color: #fff;">JRM Killboard <?php _e('Graphics Settings','jrm_killboard') ?></h3></div>
</div>
<div class="container" style="font-size: x-small;">
    <form id="graphics_form">
        <input type="hidden" name="wpopnn" id="wpopnn" value="<?php echo wp_create_nonce('jrm_killboard_op_nonce') ?>" />
        <input type="hidden" id="loading-message" value="<?php _e('..Please wait..','jrm_killboard') ?>" />
        <input type="hidden" id="loading-long-message" value="<?php _e('..Please wait. Processing may take some time to complete..','jrm_killboard') ?>" />
        <div class="row">
            <div class="card col">
                <div class="card-header">
                    <h4><?php _e('Page structure','jrm_killboard') ?></h4>
                </div>
                <div class="card-body">
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
                    <div class="input-group mb-3">
                        <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Custom image styles','jrm_killboard') ?></span></div>
                        <input type="text" class="form-control" id="image_styles" name="image_styles" placeholder="<?php echo 'Suggested : display: inline;' ?>" value="<?php echo $imgStyles ?>">
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Custom button styles','jrm_killboard') ?></span></div>
                        <input type="text" class="form-control" id="btn_styles" name="btn_styles" placeholder="<?php echo 'Suggested : padding:6px;' ?>" value="<?php echo $btnStyles ?>">
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Table columns','jrm_killboard') ?></span></div>
                        <select class="custom-select" multiple name="cols" id="cols">
                        <?php foreach (JRMKillboard::getTableColumns() as $key => $value) : ?>
                            <option <?php if(in_array($key,$cols)) { echo 'selected'; } ?> value="<?php echo $key ?>"><?php echo $value ?></option>
                        <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Inspect items','jrm_killboard') ?></span></div>
                                <div class="form-check form-check-inline" style="margin-left: 5px;">
                                  <input class="form-check-input" type="radio" name="inspect_items" id="inspect_items_yes" value="show" <?php if($inspectItems) { echo 'checked'; } ?>>
                                  <label class="form-check-label"><?php _e('Yes','jrm_killboard') ?></label>
                                </div>
                                <div class="form-check form-check-inline">
                                  <input class="form-check-input" type="radio" name="inspect_items" id="inspect_items_no" value="hide" <?php if(!$inspectItems) { echo 'checked'; } ?>>
                                  <label class="form-check-label"><?php _e('No','jrm_killboard') ?></label>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Show last page only','jrm_killboard') ?></span></div>
                                <div class="form-check form-check-inline" style="margin-left: 5px;">
                                  <input class="form-check-input" type="radio" name="last_page" id="last_page_yes" value="show" <?php if($lastPage) { echo 'checked'; } ?>>
                                  <label class="form-check-label"><?php _e('Yes','jrm_killboard') ?></label>
                                </div>
                                <div class="form-check form-check-inline">
                                  <input class="form-check-input" type="radio" name="last_page" id="last_page_no" value="hide" <?php if(!$lastPage) { echo 'checked'; } ?>>
                                  <label class="form-check-label"><?php _e('No','jrm_killboard') ?></label>
                                </div>
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
                        <button class="btn btn-sm btn-outline-primary" type="button" onclick="saveGraphics()"><?php _e('Save configurations','jrm_killboard') ?></button>
                    </div>
                </div>
                <div class="card-header">
                    <h4><?php _e('Colors','jrm_killboard') ?></h4>
                </div>
                <div class="card-body">
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
                            <button class="btn btn-sm btn-outline-primary" type="button" onclick="saveGraphics()"><?php _e('Save configurations','jrm_killboard') ?></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="card">
                    <div class="card-header">
                        <h4><?php _e('Status','jrm_killboard') ?></h4>
                    </div>
                    <div class="card-body">
                        <div id="jrm_alert" align="center" class="m-2 alert alert-success p-2"><?php _e('Ready','jrm_killboard') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="container-fluid" style="font-size: x-small;">
    <span id='message_container' 
        data-error-text_corporate_kill="<?php _e('Suffered kill text color missing','jrm_killboard') ?>"
        data-error-bg_corporate_kill="<?php _e('Suffered kill background color missing','jrm_killboard') ?>"
        data-error-text_kill="<?php _e('Kill text color missing','jrm_killboard') ?>"
        data-error-bg_kill="<?php _e('Kill background color missing','jrm_killboard') ?>"
        data-error-text_corporate_kill="<?php _e('Suffered kill text color missing','jrm_killboard') ?>"
        data-error-footer_color="<?php _e('Other color missing','jrm_killboard') ?>"
        data-error-footer_text="<?php _e('Other text color missing','jrm_killboard') ?>">
    </span>
    <?php include 'copyright_footer.php' ?>
</div>
