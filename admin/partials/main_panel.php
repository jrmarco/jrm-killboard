<div class="container-fluid" style="font-size: x-small;">
    <input type="hidden" name="wpopnn" id="wpopnn" value="<?php echo wp_create_nonce('jrm_killboard_op_nonce') ?>" />
    <input type="hidden" id="confirm-message" value="<?php _e('Would you like to delete this Killmail [%d] ?','jrm_killboard') ?>" />
    <input type="hidden" id="loading-message" value="<?php _e('..Please wait..','jrm_killboard') ?>" />
    <input type="hidden" id="loading-long-message" value="<?php _e('..Please wait. Processing may take some time to complete..','jrm_killboard') ?>" />
    <div style="background-image: url('https://web.ccpgamescdn.com/triallandingassets/new/eve-online.png'); background-repeat: no-repeat; padding: 2px; width: 100%; height: 50px; background-color: #000;"><h3 style="margin-left: 200px; color: #fff;">JRM Killboard</h3></div>
    <div style="margin:10px;">
        <div id="jrm_alert" align="center" class="m-2 alert p-2 hidden"></div>
        <?php _e('Manually import Killmail','jrm_killboard') ?>
        <div class="input-group mb-3">
            <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Killmail URL','jrm_killboard') ?></span></div>
            <input type="text" class="form-control" placeholder="https://esi.evetech.net/.../killmails/...." name="link_kill" id="link_kill" >
            <div class="input-group-append"><button class="btn btn-outline-secondary" type="button" onclick="fetchKillmail()"><?php _e('Load Kill','jrm_killboard') ?></button></div>
        </div>
        <?php if($killsWithNoWorth>0 || (!is_null($pendingItems) && !empty($pendingItems)) ) : ?>
        <div class="border" style="padding: 5px;">
            <p><?php _e('Item price need your attention:','jrm_killboard') ?></p>
            <div class="input-group-prepend">
                <div class="input-group mb-3">
                <?php if($killsWithNoWorth>0) : ?>
                    <button class="btn btn-sm btn-outline-primary" style="margin-right: 10px;" onclick="calculateWorthValue()"><?php _e('Calculate costs','jrm_killboard') ?></button>
                <?php endif; ?>
                <?php if(!is_null($pendingItems) && !empty($pendingItems)) : ?>
                    <div class="input-group-prepend"><span class="input-group-text" style="font-size: x-small;"><?php _e('Missing price','jrm_killboard') ?></span></div>
                    <select class="col-3" id="items">
                    <?php foreach ($pendingItems as $p) : ?>
                        <option value="<?php echo $p->id ?>"><?php echo $p->name ?></option>
                    <?php endforeach; ?>
                    </select>
                    <input type="text" id="price">
                    <button class="btn btn-sm btn-outline-primary" type="button" onclick="setPriceValue()"><?php _e('Set price','jrm_killboard') ?></button>
                <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <table id="killboard" class="table table-striped table-bordered table-dark m-3" data-page="<?php echo $page ?>">
            <thead class="thead-dark">
                <tr>
                    <th scope="col"><?php _e('Objective','jrm_killboard') ?></th><th scope="col"><?php _e('Date','jrm_killboard') ?></th><th scope="col"><?php _e('Attacker','jrm_killboard') ?></th><th scope="col"><?php _e('Value','jrm_killboard') ?></th><th><?php _e('Actions','jrm_killboard') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if(!is_null($killList) && !empty($killList)) : ?>
                <?php foreach ($killList as $r) :
                    $arrAttackers = json_decode($r->attackers);
                    $flatName     = $app->beAttackers($arrAttackers);
                    $flatName     = empty($flatName) ? 'NPC' : $flatName;
                    $flag         = $r->active ? 'hide' : 'show';
                    $toggle       = $r->active ? __('Hide','jrm_killboard') : __('Show','jrm_killboard');
                    $worth        = (!is_null($r->worth)) 
                        ? JRMKillboard::niceNumberFormat($r->worth) 
                        : '<b>'.__('Pending','jrm_killboard').'</b>';
                ?>
                    <tr>
                        <td><?php echo __('Victim','jrm_killboard').": <b>{$r->victim}</b>" ?><br><?php echo __('Corporation','jrm_killboard').": <b>{$r->corpname}</b>" ?></td>
                        <td><?php echo date('Y-m-d H:i:s e',$r->killTime)?></td>
                        <td style="font-size: xx-small;" title="<?php echo $flatName ?>"><?php echo (strlen($flatName)<=70) ? $flatName : substr($flatName,0,70).' ...' ?></td><td><?php echo $worth ?></td>
                        <td align="center" style="width: 20%;">
                            <button class="btn btn-sm btn-outline-warning" type="button" onclick="toggleKill(<?php echo "{$r->killmailId},'{$flag}'" ?>)"><?php echo $toggle ?></button>&nbsp;
                            <button class="btn btn-sm btn-outline-primary" type="button" onclick="window.open('<?php echo "https://esi.evetech.net/latest/killmails/{$r->killmailId}/{$r->hash}/" ?>')">Killmail</button>&nbsp;
                            <button class="btn btn-sm btn-outline-danger" type="button" onclick="removeKill(<?php echo $r->killmailId ?>)"><?php _e('Delete','jrm_killboard') ?></button>&nbsp;</td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <td><?php _e('No kills available','jrm_killboard') ?></td>
            <?php endif; ?> 
            </tbody>
        </table>
    </div>
    <div class="inline" style="margin: 5px;">
        <div class="input-group-prepend">
            <button class="btn btn-outline-secondary" type="button" onclick="pagination(0,<?php echo $lastPage ?>)">
                <?php _e('First page','jrm_killboard') ?>
            </button>
            <button class="btn btn-outline-secondary" type="button" onclick="pagination(<?php echo $prev.','.$lastPage ?>)">
                <?php _e('Previous','jrm_killboard') ?>
            </button>
            <button class="btn btn-outline-secondary" type="button" disabled>
                <?php echo ($page+1).' '.__('of','jrm_killboard').' '.$lastPage ?>
            </button>
            <button class="btn btn-outline-secondary" type="button" onclick="pagination(<?php echo $next.','.$lastPage ?>)">
                <?php _e('Next','jrm_killboard') ?>
            </button>
            <button class="btn btn-outline-secondary" type="button" onclick="pagination(<?php echo ($lastPage-1).','.$lastPage?>)">
                <?php _e('Last page','jrm_killboard') ?>
            </button>
            <input type="text" id="custom_page" class="form-control col-1" placeholder="" aria-label="<?php _e('Page','jrm_killboard')?>" placeholder="<?php _e('Page','jrm_killboard') ?>" >
            <button class="btn btn-outline-secondary" type="button" onclick="pagination('custom',<?php echo $lastPage ?>)">
                <?php _e('Go','jrm_killboard') ?>
            </button>
        </div>
    </div>
    <?php include 'copyright_footer.php' ?>
</div>