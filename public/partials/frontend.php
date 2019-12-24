<section id="primary" class="content-area">
    <main id="main" class="site-main" style="padding: 0 5% 0 5%;">
        <header class="entry-header">
            <h1 class="entry-title"><?php echo get_option('jrm_killboard_title') ?></h1>
        </header>
        <div style="font-size: <?php echo $fontSize ?>; <?php echo $margin.' '.$padding ?>">     
            <input type="hidden" name="fenon" id="fenon" value="<?php echo wp_create_nonce('jrm_killboard_op_nonce') ?>" />
            <?php if(!empty($kills) && $elementsPerPage>10) : ?>
            <div class="inline" style="margin: 5px;">
                <div class="input-group-prepend">
                    <input class="btn jrm_killboard_pager" type="button" data-mode="first" value="<?php _e('First','jrm_killboard') ?>">
                    <input class="btn jrm_killboard_pager" type="button" data-mode="prev" value="<<">
                    <input id="pageIndex" class="btn" type="button" disabled value="1 <?php echo __('of','jrm_killboard').' '.$lastPage ?>">
                    <input class="btn jrm_killboard_pager" type="button" data-mode="next" value=">>">
                    <input class="btn jrm_killboard_pager" type="button" data-mode="last" value="<?php _e('Last','jrm_killboard') ?>">
                </div>
            </div>
            <?php endif; ?>
            <table class="table">
                <thead class="thead">
                    <tr>
                        <?php foreach ($activeCols as $columnName) {
                                $colParams = 'scope="col"';
                                if($columnName == 'target' || $columnName == 'ship') {
                                    $colParams = 'scope="col" colspan="2"';
                                }
                                echo "<th {$colParams}>".__($columns[$columnName],'jrm_killboard')."</th>";
                            }
                        ?>
                    </tr>
                </thead>
                <tbody id="tabledata" data-page="0">
                <?php if(!empty($kills)) : ?>
                    <?php foreach ($kills as $r) :
                        $arrAttackers = json_decode($r->attackers);
                        $objName      = $killBoard->attackersDetails($r->killmailId,$arrAttackers);
                        $flatName     = $objName->names;
                        $bgColor = $bgColorSuffered;
                        $textColor = $textColorSuffered;
                        if($r->corp != get_option('jrm_killboard_corporation_id')) {
                            $bgColor = $bgColorKill;
                            $textColor = $textColorKill;
                        }
                        if(is_null($r->worth)) {
                            $r->worth = __('Calculating','jrm_killboard');
                        }
                        $color          = JRMKillboard::getSecurityStatusColor($r->securityStatus);
                        $securityStatus = '(<span style="color:'.$color.';">'.$r->securityStatus.'</span>)';

                        $worth = JRMKillboard::niceNumberFormat($r->worth);
                    ?>
                    <tr style="background-color:<?php echo $bgColor ?>; color:<?php echo $textColor ?>;">
                        <?php if(in_array('target', $activeCols)) : ?>
                        <td style="padding: 10px;margin:0px; border-right: 0px;" align="center"><img src="https://images.evetech.net/types/<?php echo $r->shipId ?>/render?size=<?php echo $imageSize ?>"></td>
                        <td style="border-left: 0px;"><b><?php echo $r->shipName ?></b><br><?php _e('Kill worth','jrm_killboard') ?>&nbsp;<?php echo $worth ?>&nbsp;ISK</td>
                        <?php endif; ?>
                        <?php if(in_array('ship', $activeCols)) : ?>
                        <td style="padding: 10px;margin:0px; border-right: 0px;" align="center"><img src="https://images.evetech.net/alliances/<?php echo $r->allid ?>/logo?size=<?php echo $imageSize ?>"><img src="https://images.evetech.net/corporations/<?php echo $r->corpid ?>/logo?size=<?php echo $imageSize ?>"><img src="https://images.evetech.net/characters/<?php echo $r->victimId ?>/portrait?size=<?php echo $imageSize ?>"></td>
                        <td style="border-left: 0px;"><?php _e('Corporation','jrm_killboard') ?>:&nbsp;<?php echo $r->corpname ?><br><?php _e('Victim','jrm_killboard') ?>:&nbsp;<b><?php echo $r->victim ?></b></td>
                        <?php endif; ?>
                        <?php if(in_array('attackers', $activeCols)) : ?>
                        <td title="<?php echo $objName->corporates ?>"><?php echo $flatName ?></td>
                        <?php endif; ?>
                        <?php if(in_array('damage', $activeCols)) : ?>
                        <td><?php echo number_format($r->damageTaken) ?></td>
                        <?php endif; ?>
                        <?php if(in_array('location', $activeCols)) : ?>
                        <td><?php echo $r->systemName.' '.$securityStatus ?><br><?php echo date('Y-m-d H:i:s e', $r->killTime) ?></td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td><?php _e('No kills available','jrm_killboard') ?></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
            <div><?php _e('Date are synced on the Eve Online time','jrm_killboard') ?></div>
            <?php if(!empty($kills)) : ?>
            <div class="inline" style="margin: 5px;">
                <div class="input-group-prepend">
                    <input class="btn jrm_killboard_pager" type="button" data-mode="first" value="<?php _e('First','jrm_killboard') ?>">
                    <input class="btn jrm_killboard_pager" type="button" data-mode="prev" value="<<">
                    <input id="pageIndex" class="btn" type="button" disabled value="1 <?php echo __('of','jrm_killboard').' '.$lastPage ?>">
                    <input class="btn jrm_killboard_pager" type="button" data-mode="next" value=">>">
                    <input class="btn jrm_killboard_pager" type="button" data-mode="last" value="<?php _e('Last','jrm_killboard') ?>">
                </div>
            </div>
            <?php endif; ?>
        </div><!-- .entry-content -->
        <footer class="entry-footer" style="font-size: xx-small;">
            <?php if($devSign) : ?>
            <?php $loveMessage = __('Made with ♥ by %s','jrm_killboard'); ?>
            <p align="right"><?php echo sprintf($loveMessage,'<a href="https://bigm.it" target="_blank">jrmarco</a>') ?></p>
            <?php endif; ?>
            <hr />
            <p><b>CCP Copyright Notice</b></p>
            EVE Online and the EVE logo are registered trademarks of CCP hf. EVE Online and all associated logos and designs are the intellectual property of CCP hf. All the images, game data coming from the ESI API or other recognizable features of the intellectual property relating to these trademarks are likewise the intellectual property of CCP hf. <?php echo get_bloginfo('name') ?> uses EVE Online and all associated logos and designs for information purposes only on this website but does not endorse, and is not in any way affiliated with it. CCP is in no way responsible for the content nor functioning of <?php echo get_bloginfo('name') ?>, nor can it be liable for any damage arising from the use of it. All Eve Related Materials are Property Of <a href="http://www.ccpgames.com/" target="_blank">CCP Games</a>. <?php echo get_bloginfo('name') ?> makes use of ESI Api and Eve Online Developer applications. All information can be found on <a href="https://developers.eveonline.com/" target="_blank">official website</a> - <a href="https://developers.eveonline.com/resource/license-agreement" target="_blank">License Agreement</a> - © 2014 CCP hf. All rights reserved. "EVE", "EVE Online", "CCP", and all related logos and images are trademarks or registered trademarks of CCP hf.
        </footer><!-- footer -->
    </main><!-- #main -->
</section><!-- #primary -->

<?php
get_footer();