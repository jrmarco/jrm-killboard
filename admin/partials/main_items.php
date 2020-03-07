<div class="card-header">
    <div style="background-image: url('https://web.ccpgamescdn.com/triallandingassets/new/eve-online.png'); background-repeat: no-repeat; padding: 2px; width: 100%; height: 50px; background-color: #000;"><h3 style="margin-left: 200px; color: #fff;">JRM Killboard <?php _e('Items management','jrm_killboard') ?></h3></div>
</div>
<div class="container" style="font-size: x-small;">
    <input type="hidden" name="wpopnn" id="wpopnn" value="<?php echo wp_create_nonce('jrm_killboard_op_nonce') ?>" />
    <input type="hidden" id="confirm-message" value="<?php _e('Would you like to delete this Killmail [%d] ?','jrm_killboard') ?>" />
    <input type="hidden" id="loading-message" value="<?php _e('..Please wait..','jrm_killboard') ?>" />
    <input type="hidden" id="loading-long-message" value="<?php _e('..Please wait. Processing may take some time to complete..','jrm_killboard') ?>" />
    <div style="margin:10px;">
        <div id="jrm_alert" align="center" class="m-2 alert p-2 hidden"></div>
        <?php if ($elementsPerPage>10) : ?>
        <div class="inline" style="margin: 5px;">
            <div class="input-group-prepend">
                <button class="mr-2 btn btn-sm btn-outline-secondary" type="button" onclick="pagination(0,<?php echo $lastPage ?>)">
                    <?php _e('First page','jrm_killboard') ?>
                </button>
                <button class="btn btn-sm btn-outline-secondary" type="button" onclick="pagination(<?php echo $prev.','.$lastPage ?>)">
                    <?php _e('Previous','jrm_killboard') ?>
                </button>
                <button class="btn btn-sm btn-outline-secondary" type="button" disabled>
                    <?php echo ($page+1).' '.__('of','jrm_killboard').' '.$lastPage ?>
                </button>
                <button class="mr-2 btn btn-sm btn-outline-secondary" type="button" onclick="pagination(<?php echo $next.','.$lastPage ?>)">
                    <?php _e('Next','jrm_killboard') ?>
                </button>
                <button class="btn btn-sm btn-outline-secondary" type="button" onclick="pagination(<?php echo ($lastPage-1).','.$lastPage?>)">
                    <?php _e('Last page','jrm_killboard') ?>
                </button>
                <button class="ml-2 btn btn-sm btn-warning" type="button" onclick="updateItemsPrice(true)">
                    <?php _e('Update items price','jrm_killboard') ?>
                </button>
            </div>
        </div>
        <?php endif; ?>
        <span><?php _e('Current time','jrm_killboard') ?> <?php echo date( 'Y-m-d H:i:s e', current_time( 'timestamp', 1 ) ) ?></span>
        <table id="items" class="table table-striped table-bordered table-dark m-3" data-page="<?php echo $page ?>">
            <thead class="thead-dark">
                <tr><!--<th scope="col" width="50px;"><?php _e('Select','jrm_killboard' ) ?></th>--><th scope="col" width="50px;"><?php _e('Icon','jrm_killboard') ?></th><th scope="col"><?php _e('Object','jrm_killboard') ?></th><th scope="col"><?php _e('Price','jrm_killboard') ?></th><th scope="col"><?php _e('Source','jrm_killboard') ?></th><th scope="col"><?php _e('Synchronized','jrm_killboard') ?></th></tr>
            </thead>
            <tbody>
                <?php foreach ($itemsList as $item) : ?>
                    <tr>
                        <!--<td align="center"><input class="multiselect_items" type="checkbox" data-value="<?php echo $item->id ?>"></td>-->
                        <td><img src="https://images.evetech.net/types/<?php echo $item->id ?>/icon?size=32" /></td>
                        <td><?php echo $item->name ?></td>
                        <?php $dressed = JRMKillboard::niceNumberFormat($item->price) ?>
                        <td ondblclick="enableEdit()" data-id="<?php echo $item->id ?>" data-editing="false" data-flatprice="<?php echo $item->price ?>" data-dressed="<?php echo $dressed ?>"><?php echo is_null($item->price) ? __('Missing','jrm_killboard') : $dressed ?></td>
                        <td><?php echo $item->manual ? __('Manual','jrm_killboard') : __('ESI','jrm_killboard') ?></td>
                        <td><?php echo date('Y-m-d H:i:s e',$item->lastSync)?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="inline" style="margin: 5px;">
        <div class="input-group-prepend">
            <button class="mr-2 btn btn-sm btn-outline-secondary" type="button" onclick="pagination(0,<?php echo $lastPage ?>)">
                <?php _e('First page','jrm_killboard') ?>
            </button>
            <button class="btn btn-sm btn-outline-secondary" type="button" onclick="pagination(<?php echo $prev.','.$lastPage ?>)">
                <?php _e('Previous','jrm_killboard') ?>
            </button>
            <button class="btn btn-sm btn-outline-secondary" type="button" disabled>
                <?php echo ($page+1).' '.__('of','jrm_killboard').' '.$lastPage ?>
            </button>
            <button class="mr-2 btn btn-sm btn-outline-secondary" type="button" onclick="pagination(<?php echo $next.','.$lastPage ?>)">
                <?php _e('Next','jrm_killboard') ?>
            </button>
            <button class="btn btn-sm btn-outline-secondary" type="button" onclick="pagination(<?php echo ($lastPage-1).','.$lastPage?>)">
                <?php _e('Last page','jrm_killboard') ?>
            </button>
            <button class="ml-2 btn btn-sm btn-warning" type="button" onclick="updateItemsPrice(true)">
                <?php _e('Update items price','jrm_killboard') ?>
            </button>
        </div>
    </div>
</div>
<div class="container-fluid" style="font-size: x-small;">
    <?php include 'copyright_footer.php' ?>
</div>