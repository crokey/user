<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();


$merchant_id = get_staff_user_id();

$is_full_access = get_is_full_access($merchant_id);

// Redirect if the user does not have full access
if (!$is_full_access) {
    redirect('https://user.bearlapay.com/MMS/admin/');
    exit;  // Make sure to stop further script execution after redirect
}

$userId = get_merchant_userid();

// Ensure the current user is only viewing their own profile
if (isset($client) && $client->userid != $userId) {
    // If the client ID does not match the logged-in user, redirect to the admin dashboard
    redirect('https://user.bearlapay.com/MMS/admin/');
    exit;
}
?>


<div id="wrapper" class="customer_profile">
    <div class="content">
        
        <div class="row">
            <div class="col-md-3">
                <?php if (isset($client)) { ?>
                <h4 class="tw-text-lg tw-font-semibold tw-text-neutral-800 tw-mt-0">
                    <div class="tw-space-x-3 tw-flex tw-items-center">
                        <span class="tw-truncate">
                            #<?php echo $client->userid . ' ' . $title; ?>
                        </span>
                        <?php if (staff_can('delete',  'customers') || is_admin()) { ?>
                        <div class="btn-group">
                           
                            
                        </div>
                        <?php } ?>
                    </div>
                    
                </h4>
                <?php } ?>
            </div>
            <div class="clearfix"></div>

            <?php if (isset($client)) { ?>
            <div class="col-md-3">
                <?php $this->load->view('admin/clients/tabs'); ?>
            </div>
            <?php } ?>

            
          

            <div class="tw-mt-12 sm:tw-mt-0 <?php echo isset($client) ? 'col-md-9' : 'col-md-8 col-md-offset-2'; ?>">
                <div class="panel_s">
                  <?php if ($group == 'transactions') { ?>
                  <div style="--tw-bg-opacity: 1;
                              border-radius: 0.375rem;
                              padding: 1.0rem;
                              position: relative;">
  
                    
                  <?php } else { ?>
                  
                    <div class="panel-body">
 <?php  } ?>
                        <?php if (isset($client)) { ?>
                        <?php echo form_hidden('isedit'); ?>
                        <?php echo form_hidden('userid', $client->userid); ?>
                        <div class="clearfix"></div>
                        <?php } ?>
                        <div>
                            <div class="tab-content">
                                <?php $this->load->view((isset($tab) ? $tab['view'] : 'admin/clients/groups/profile')); ?>
                            </div>
                        </div>
                    </div>
                   
                    
                  
                </div>
            </div>
        </div>

    </div>
</div>
<?php init_tail(); ?>
<?php if (isset($client)) { ?>
<script>
$(function() {
    init_rel_tasks_table(<?php echo $client->userid; ?>, 'customer');
});
</script>
<?php } ?>
<?php $this->load->view('admin/clients/client_js'); ?>
</body>

</html>