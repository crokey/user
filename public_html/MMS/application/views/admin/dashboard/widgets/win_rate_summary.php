<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="widget" id="widget-<?php echo create_widget_id(); ?>" data-name="Win Rate Summary">
<style>
    .stat-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
        padding: 20px;
        text-align: center;
        margin-bottom: 20px;
        transition: 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
    }

    .progress-ring {
        position: relative;
        width: 60px;
        height: 60px;
        margin: 0 auto 10px;
    }

    .progress-ring circle {
        fill: none;
        stroke-width: 6;
        transform: rotate(-90deg);
        transform-origin: center;
    }

    .circle-bg { stroke: #e5e7eb; }
    .circle-purple { stroke: #8b5cf6; }
    .circle-yellow { stroke: #facc15; }
    .circle-red { stroke: #f87171; }

    .percentage-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-weight: bold;
        font-size: 14px;
        color: #111827;
    }

    .stat-title {
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 4px;
    }

    .stat-value {
    font-size: 16px;
    font-weight: 500;
    color: #4b5563;
    margin-top: 0.5rem;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.stat-card:hover .stat-value {
    opacity: 1;
}

    .count-label {
        font-size: 12px;
        margin-top: 4px;
        color: #4b5563;
    }

    

</style>

<div class="panel_s">
    <div class="panel-body padding-10">
        <div class="widget-dragger"></div>
        <p class="tw-font-medium tw-flex tw-items-center tw-mb-0 tw-space-x-1.5 rtl:tw-space-x-reverse tw-p-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="tw-w-6 tw-h-6 tw-text-neutral-500">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3.75 6.75h16.5M3.75 12H12m-8.25 5.25h16.5" />
            </svg>
            <span class="tw-text-neutral-700">
                <?php echo _l("Win Rate Summary"); ?>
            </span>
        </p>

        <hr class="-tw-mx-3 tw-mt-3 tw-mb-6">

        <div class="row">
            <?php
                $items = [
                    ['label' => 'Last 7 Days', 'value' => $win_summary['last7']['percentage'], 'color' => 'purple', 'count' => $win_summary['last7']['count']],
                    ['label' => 'This Month', 'value' => $win_summary['month']['percentage'], 'color' => 'yellow', 'count' => $win_summary['month']['count']],
                    ['label' => 'Overall', 'value' => $win_summary['overall']['percentage'], 'color' => 'red', 'count' => $win_summary['overall']['count']],
                ];

                foreach ($items as $item):
                    $percentage = (int)$item['value'];
                    $dash = 251.2 * ($percentage / 100); // circle circumference
            ?>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-title"><?= $item['label'] ?></div>
                        <div class="stat-value"><?= $item['count'] ?></div>

                        <div class="progress-ring" title="<?= $item['count'] ?>">
                            <svg width="60" height="60">
                                <circle class="circle-bg" cx="30" cy="30" r="26" />
                                <circle class="circle-<?= $item['color'] ?>" cx="30" cy="30" r="26"
                                        stroke-dasharray="<?= $dash ?>, 251.2" />
                            </svg>
                            <div class="percentage-text"><?= $percentage ?>%</div>
                        </div>

                        <div class="count-label">Win Rate</div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</div>
