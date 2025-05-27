

<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php if (isset($client)) { ?>
<h4 class="customer-profile-group-heading"><?php echo _l('Application'); ?></h4>
<?php } ?>

<div class="row">
    <?php echo form_open($this->uri->uri_string(), ['class' => 'client-form', 'autocomplete' => 'off']); ?>
    <div class="additional"></div>
    <div class="col-md-12">
        <div class="horizontal-scrollable-tabs panel-full-width-tabs">
            <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
            <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
            <div class="horizontal-tabs">
              <?php if (!empty($pdf_url)) : ?>
    <iframe src="<?php echo $pdf_url; ?>" width="100%" height="800px"></iframe>
<?php else : ?>
    <p>Contract not available</p>
<?php endif; ?>


            </div>
  
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
