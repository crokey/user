<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (isset($client)) { ?>
<h4 class="customer-profile-group-heading"><?php echo _l('client_payments_tab'); ?></h4>

<?php
                          
    $this->load->view('admin/payments/profiletable_html', ['class' => 'payments-single-client']);
    
    ?>
<?php } ?>