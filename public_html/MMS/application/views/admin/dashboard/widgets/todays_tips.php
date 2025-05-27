

<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>


<div class="widget" id="widget-<?php echo create_widget_id(); ?>" data-name="Today's Tips">


    <div class="row">
        <div class="col-md-12">
            <div class="panel_s">
                <div class="panel-body padding-10">
                    <div class="widget-dragger"></div>

                    <p
                        class="tw-font-medium tw-flex tw-items-center tw-mb-0 tw-space-x-1.5 rtl:tw-space-x-reverse tw-p-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="tw-w-6 tw-h-6 tw-text-neutral-500">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6.75h16.5M3.75 12H12m-8.25 5.25h16.5" />
                        </svg>

                        <span class="tw-text-neutral-700">
                            <?php echo _l("Today's Tips"); ?>
                        </span>
                    </p>

                    <hr class="-tw-mx-3 tw-mt-3 tw-mb-6">

                    <?php if (!empty($todays_tips_grouped)) : ?>
                        <ul class="list-group">
                            <?php foreach ($todays_tips_grouped as $category => $tips): ?>
                                <?php 
                                    $tip = reset($tips); 
                                    $category_clean = strtolower($category);
                                    // Pick an icon per sport
                                    switch ($category) {
    case 'football':
        $icon = '<iconify-icon icon="mdi:soccer" style="font-size: 24px; color: #4b5563;"></iconify-icon>';
        break;
    case 'american_football':
        $icon = '<iconify-icon icon="mdi:football" style="font-size: 24px; color: #4b5563;"></iconify-icon>';
        break;
    case 'tennis':
        $icon = '<iconify-icon icon="mdi:tennis" style="font-size: 24px; color: #4b5563;"></iconify-icon>';
        break;
    case 'horse_racing':
        $icon = '<iconify-icon icon="mdi:horse-variant" style="font-size: 24px; color: #4b5563;"></iconify-icon>';
        break;
    default:
        $icon = '<iconify-icon icon="mdi:trophy" style="font-size: 24px; color: #4b5563;"></iconify-icon>';
}



                                ?>
                               <li class="list-group-item position-relative" style="cursor: pointer;" onclick="window.location.href='<?= admin_url('tips'); ?>'">

    <!-- Icon and badge "pinned" to the border -->
    <div style="position: absolute; top: -10px; right: 15px; background: #fff; padding: 0 6px;" class="d-flex align-items-center">
        
        <span class="badge badge-primary ml-1"><?= $icon ?><?= count($tips); ?> tip<?= count($tips) > 1 ? 's' : ''; ?></span>
    </div>

    <!-- Main content of the tip -->
    <strong><?= htmlspecialchars($tip['event']); ?></strong><br>
    <span class="text-muted text-uppercase" style="font-size: 12px;">
        <?= ucfirst(str_replace('_', ' ', $category)); ?>
    </span><br>
    <?= htmlspecialchars($tip['tip']); ?>
    <strong>@ <?= number_format((float) $tip['odds'], 2); ?></strong>
</li>






                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="tw-text-sm tw-text-gray-500">No tips added for today.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>




