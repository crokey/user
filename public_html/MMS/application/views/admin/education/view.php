<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<style>

    .module-content {
  min-height: 160px; /* Adjust based on your content */
  position: relative;
}

.module-card {
  min-height: 220px; /* or whatever keeps consistent spacing */
}

.blurred {
  filter: blur(4px);
  pointer-events: none;
  user-select: none;
}

.lock-overlay {
  height: 0;
  width: 0;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 10;
  font-size: 3rem;
  color: #6b7280;
  pointer-events: auto;
}


.lock-tooltip-wrapper {
  display: inline-block;
}

.lock-tooltip {
  visibility: hidden;
  opacity: 0;
  width: max-content;
  max-width: 220px;
  background-color: #1f2937;
  color: #fff;
  text-align: center;
  border-radius: 6px;
  padding: 6px 12px;
  position: absolute;
  z-index: 20;
  bottom: 120%;
  left: 50%;
  transform: translateX(-50%);
  transition: opacity 0.3s ease;
  font-size: 12px;
  white-space: nowrap;
  pointer-events: none;
}

.lock-tooltip::after {
  content: "";
  position: absolute;
  top: 100%;
  left: 50%;
  transform: translateX(-50%);
  border-width: 5px;
  border-style: solid;
  border-color: #1f2937 transparent transparent transparent;
}

.lock-tooltip-wrapper:hover .lock-tooltip {
  visibility: visible;
  opacity: 1;
}
</style>

<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <!-- Course Header -->
            <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8 tw-mb-8">
  <!-- Left: Course Description -->
  <div>
    <h3 class="tw-font-bold tw-text-2xl tw-mb-2">Course Description</h3>
    <p class="tw-text-neutral-600 tw-text-base">
      Unlock the strategies, psychology, and mathematics behind successful sports betting. This beginner-friendly course is designed to give you a solid foundation in how betting markets work, how to manage risk, and how to interpret odds like a professional. Whether you're interested in betting for entertainment or want to build a more disciplined approach, this course will teach you to bet smarter—not just harder.

      
    </p>
    
  </div>

  <!-- Right: Course Details -->
  <div class="tw-text-sm tw-space-y-2">
    <h3 class="tw-font-bold tw-text-2xl tw-mb-2">Course Details</h3>
    <div class="tw-grid tw-grid-cols-2 tw-gap-y-1">
      <p><strong><i class="ph ph-users"></i> 2,000 Enrolled</strong></p>
      <p><strong><i class="ph ph-clock"></i> 1 Hour 40 Minutes</strong></p>
      <p><strong><i class="ph ph-globe-hemisphere-east"></i> English</strong></p>
      <p><strong><i class="ph ph-book-bookmark"></i> Subscription Access</strong></p>
      <p><strong><i class="ph ph-graduation-cap"></i> Beginner Level</strong></p>
    </div>
  </div>
</div>

            <!-- Progress -->
<div class="tw-mb-6">
  <h4 class="tw-font-semibold tw-mb-2">Overall Progress</h4>
  <div class="progress">
    <div class="progress-bar" 
     role="progressbar" 
     style="width: <?= $progress ?? 0 ?>%;" 
     data-percent="<?= $progress ?? 0 ?>" 
     aria-valuenow="<?= $progress ?? 0 ?>" 
     aria-valuemin="0" 
     aria-valuemax="100">
  <?= $progress ?? 0 ?>%
</div>

  </div>
</div>





            <!-- Modules -->
            <?php
$modules = [
  [
    'title' => 'Introduction to Betting',
    'time' => '45 Min • 4 Topics',
    'topics' => ['How Odds Work', 'Types of Bets', 'Risk vs Reward']
  ],
  [
    'title' => 'Bankroll Management',
    'time' => '30 Min • 3 Topics',
    'topics' => ['Setting Limits', 'Unit Size', 'Avoiding Tilt']
  ],
  [
    'title' => 'Value Betting Explained',
    'time' => '40 Min • 3 Topics',
    'topics' => ['Finding Value', 'Implied Probability', 'Market Inefficiencies']
  ],
  [
    'title' => 'Betting Psychology',
    'time' => '35 Min • 3 Topics',
    'topics' => ['Emotional Discipline', 'Cognitive Biases', 'Long-term Mindset']
  ],
  [
    'title' => 'Live/In-Play Betting',
    'time' => '50 Min • 5 Topics',
    'topics' => ['Live Odds Movement', 'Momentum Shifts', 'Timing Entries']
  ],
  [
    'title' => 'Advanced Strategies',
    'time' => '60 Min • 6 Topics',
    'topics' => ['Arbitrage Betting', 'Line Shopping', 'Data-Driven Decisions']
  ]
];
?>

<div class="tw-mb-6">
  <h4 class="tw-font-semibold tw-mb-4">Modules (<?= count($modules) ?>)</h4>
  <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-6">
    <?php foreach ($modules as $index => $module):
  $locked = $index > 2; // First 3 unlocked
  $moduleUrl = admin_url('education/module/' . $index);
?>
  <div class="tw-relative tw-rounded-lg tw-shadow-sm tw-p-4 module-card tw-flex tw-flex-col tw-justify-between" style="background-color: #f1f5f9;">

    <?php if (!$locked): ?>
      <a href="<?= $moduleUrl ?>" target="_blank" class="tw-absolute tw-inset-0" style="z-index: 20;"></a>
    <?php else: ?>
      <div class="lock-tooltip-wrapper lock-overlay" style="cursor: pointer;">
        <i class="ph ph-lock"></i>
        <div class="lock-tooltip">
          Unlocks: <?= date('F j, Y', strtotime('+1 week')) ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="module-content <?= $locked ? 'blurred' : '' ?>">
      <div class="tw-flex tw-justify-between tw-items-center">
        <h5 class="tw-font-medium"><?= $module['title'] ?></h5>
        <span class="badge <?= $locked ? 'badge-secondary' : 'badge-success' ?>">
          <?= $index % 3 == 0 ? '100%' : ($index % 2 == 0 ? '30%' : '0%') ?>
        </span>
      </div>
      <p class="tw-text-xs tw-text-neutral-500 tw-mt-1"><?= $module['time'] ?></p>
      <ul class="tw-mt-2 tw-text-sm tw-text-neutral-700">
        <?php foreach ($module['topics'] as $topic): ?>
          <li><?= $topic ?></li>
        <?php endforeach; ?>
      </ul>
      <span class="tw-text-sm tw-text-blue-600 tw-mt-3 tw-inline-block">Show More</span>
    </div>

  </div>
<?php endforeach; ?>

  </div>
</div>


              <!-- Pagination -->
              <div class="tw-mt-6 tw-text-center">
                <nav>
                  <ul class="pagination">
                    <li class="page-item"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item"><a class="page-link" href="#">Next</a></li>
                  </ul>
                </nav>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>
