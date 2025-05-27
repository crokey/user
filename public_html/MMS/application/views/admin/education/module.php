<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<?php
// Redirect logic at top of module.php view (optional, better placed in controller)
if ($module_id > 2) {
  redirect(admin_url('education/module/0'));
}
?>

<style>
    .module-content {
  min-height: 160px;
  position: relative;
}
.module-card {
  min-height: 220px;
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

<div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8 tw-mb-8">
  <div>
    <h3 class="tw-font-bold tw-text-2xl tw-mb-2"><?= $module['title'] ?></h3>
    <p class="tw-text-neutral-600 tw-text-base">This module covers the following topics:</p>
  </div>
  <div class="tw-text-sm tw-space-y-2">
    <h3 class="tw-font-bold tw-text-2xl tw-mb-2">Module Details</h3>
    <div class="tw-grid tw-grid-cols-2 tw-gap-y-1">
      <p><strong><i class="ph ph-clock"></i> <?= $module['time'] ?></strong></p>
      <p><strong><i class="ph ph-book-bookmark"></i> Module ID: <?= $module_id ?></strong></p>
      <p><strong><i class="ph ph-globe-hemisphere-east"></i> Language: English</strong></p>    
      <p><strong><i class="ph ph-graduation-cap"></i> Beginner Level</strong></p>
    </div>
  </div>
</div>
<div class="tw-mt-8">
  <h3 class="tw-font-bold tw-text-2xl tw-mb-2">Additional Course Information</h3>
  <div class="tw-text-base tw-text-neutral-700 tw-leading-relaxed">
    <p><?= !empty($module['info']) ? nl2br($module['info']) : 'No additional information provided for this module.' ?></p>
  </div>
</div>

</div>
</div>
</div>
</div>
<div class="panel_s tw-mt-6">
  <div class="panel-body">
    <h4 class="tw-font-bold tw-text-xl tw-mb-4">Module Content</h4>
    <ul class="nav nav-tabs tw-mb-4" role="tablist">
      <li class="nav-item" role="presentation">
        <a class="nav-link" id="material-tab" data-toggle="tab" href="#material" role="tab">Course Material</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" id="quiz-tab" data-toggle="tab" href="#quiz" role="tab">Quiz Yourself</a>
      </li>
    </ul>
    <div class="tab-content">
      <div class="tab-pane fade" id="material" role="tabpanel">
        <div class="tw-text-base tw-text-neutral-700 tw-leading-relaxed">
          <?= !empty($module['info']) ? nl2br($module['info']) : 'Course material will be available soon for this module.' ?>
        </div>
         <!-- ✅ Pagination inside the material tab -->
  <div id="pagination-wrapper" class="tw-mt-6 tw-text-center">
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
      <div class="tab-pane fade" id="quiz" role="tabpanel">
        <div class="tw-mt-4">
          <p class="tw-text-base tw-text-neutral-700 tw-mb-4">Test your knowledge from this module.</p>
          <div class="tw-mb-4">
            <strong>1. What does "odds" represent in betting?</strong>
            <div class="tw-mt-2">
              <label><input type="radio" name="q1"> A. The number of goals expected</label><br>
              <label><input type="radio" name="q1"> B. The probability of an outcome</label><br>
              <label><input type="radio" name="q1"> C. The number of players</label>
            </div>
          </div>
          <button class="btn btn-primary">Submit Answers</button>
        </div>
        
      </div>
    </div>
    
  </div>
</div>
<div class="tw-flex tw-justify-between tw-mt-6">
  <?php if ($module_id > 0): ?>
    <a href="<?= admin_url('education/module/' . ($module_id - 1)) ?>#<?= isset($_SERVER['QUERY_STRING']) && strpos($_SERVER['REQUEST_URI'], '#quiz') !== false ? 'quiz' : 'material' ?>" class="btn btn-primary">← Previous Module</a>
  <?php endif; ?>
  <?php if ($module_id + 1 < $total_modules && $module_id + 1 <= 2): ?>
    <a href="<?= admin_url('education/module/' . ($module_id + 1)) ?>#<?= isset($_SERVER['QUERY_STRING']) && strpos($_SERVER['REQUEST_URI'], '#quiz') !== false ? 'quiz' : 'material' ?>" class="btn btn-primary">Next Module →</a>
  <?php endif; ?>
</div>

</div>
<?php init_tail(); ?>
<script>
  $(document).ready(function () {
    // Show tab from hash
    const hash = window.location.hash || '#material';
    if ($(hash).length) {
      $('.nav-tabs a[href="' + hash + '"]').tab('show');
    }

    // Show/hide pagination based on tab
    function togglePagination(tabId) {
      if (tabId === '#quiz') {
        $('#pagination-wrapper').hide();
      } else {
        $('#pagination-wrapper').show();
      }
    }

    togglePagination(hash);

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
      const newHash = $(e.target).attr('href');
      history.replaceState(null, null, newHash);
      togglePagination(newHash);
    });
  });
</script>


