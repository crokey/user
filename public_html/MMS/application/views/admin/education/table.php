<!-- application/views/admin/education/view.php -->
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
              <div>
                <h3 class="tw-font-semibold tw-text-xl">Principal of UX Design</h3>
                <p class="tw-text-neutral-600 tw-mt-1">Understand key UX concepts and learn to improve your design process.</p>
                <button class="btn btn-primary tw-mt-2">Start Course</button>
              </div>
              <div class="tw-text-sm tw-text-right">
                <p><strong>2,000 Enrolled</strong></p>
                <p>40 Minutes • Beginner • Figma</p>
                <p>Certificate of Completion</p>
              </div>
            </div>

            <div class="tw-mb-6">
              <h4 class="tw-font-semibold tw-mb-2">Overall Progress</h4>
              <div class="progress">
                <div class="progress-bar" role="progressbar" style="width: 56%">56%</div>
              </div>
            </div>

            <div class="tw-mb-6">
              <h4 class="tw-font-semibold tw-mb-4">Modules (26)</h4>
              <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-4">
                <?php for ($i = 1; $i <= 6; $i++) { ?>
                  <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-4">
                    <div class="tw-flex tw-justify-between tw-items-center">
                      <h5 class="tw-font-medium">UI Design Principles</h5>
                      <span class="badge badge-success"><?= $i % 3 == 0 ? '100%' : ($i % 2 == 0 ? '30%' : '0%') ?></span>
                    </div>
                    <p class="tw-text-xs tw-text-neutral-500 tw-mt-1">58 Min • 4 Curriculum</p>
                    <ul class="tw-mt-2 tw-text-sm tw-text-neutral-700">
                      <li>UX Introduction</li>
                      <li>Jakob’s Law – Other Pages</li>
                      <li>Consistency is Key</li>
                    </ul>
                    <a href="#" class="tw-text-sm tw-text-blue-600 tw-mt-3 tw-inline-block">Show More</a>
                  </div>
                <?php } ?>
              </div>
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