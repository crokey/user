<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="widget" id="widget-betting_history_7_days" data-name="Last 7 Days Betting History">

    <div class="panel_s">
        <div class="panel-body padding-10">
            <div class="widget-dragger"></div>
            <p class="tw-font-medium tw-flex tw-items-center tw-mb-0 tw-space-x-1.5 tw-p-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="tw-w-6 tw-h-6 tw-text-neutral-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h4l3 8 4-16 3 8h4" />
                </svg>
                <span class="tw-text-neutral-700">Last 7 Days Tipster Betting History</span>
            </p>
            <hr class="-tw-mx-3 tw-mt-3 tw-mb-3">

            <?php if (!empty($betting_history_7_days)) : ?>
                <ul class="list-group" style="overflow: visible;">
    <?php foreach ($betting_history_7_days as $tip): 
        $result = strtolower($tip['result']);
        $outlineClass = $result === 'win' ? 'border border-success' : ($result === 'lose' ? 'border border-danger' : 'border');
    ?>
        <li class="list-group-item <?= $outlineClass ?> tw-rounded-md position-relative"
    style="margin-bottom: 20px; padding: 10px 16px; overflow: visible;">

    <div>
        <strong><?= htmlspecialchars($tip['event']) ?></strong><br>
        <small class="text-muted"><?= _d($tip['date']) ?></small>
    </div>

    <!-- OUTSIDE stacking flow to avoid clipping -->
    <span class="badge"
        style="background-color: <?= $result === 'win' ? 'rgb(65, 255, 94)' : 'red' ?>;
               color: white;
               padding: 6px 10px;
               border-radius: 1rem;
               font-size: 12px;
               min-width: 50px;
               position: absolute;
               right: 16px;
               z-index: 10;">
        <?= ucfirst($result) ?>
    </span>
</li>




    <?php endforeach; ?>
</ul>

            <?php else: ?>
                <p class="text-muted tw-text-sm">No betting history in the last 7 days.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
