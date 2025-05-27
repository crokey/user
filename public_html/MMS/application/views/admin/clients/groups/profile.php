<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php if (isset($client)) { ?>
<h4 class="customer-profile-group-heading"><?php echo _l('client_add_edit_profile'); ?></h4>
<?php } ?>

<div class="row">
    <?php echo form_open($this->uri->uri_string(), ['class' => 'client-form', 'autocomplete' => 'off']); ?>
    <div class="additional"></div>
    <div class="col-md-12">
        <div class="horizontal-scrollable-tabs panel-full-width-tabs">
            <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
            <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
            <div class="horizontal-tabs">
                <ul class="nav nav-tabs customer-profile-tabs nav-tabs-horizontal" role="tablist">
                    <li role="presentation" class="<?php echo !$this->input->get('tab') ? 'active' : ''; ?>">
                        <a href="#contact_info" aria-controls="contact_info" role="tab" data-toggle="tab">
                            <?php echo _l('customer_profile_details'); ?>
                        </a>
                    </li>
                    <?php
                  $customer_custom_fields = false;
                  if (total_rows(db_prefix() . 'customfields', ['fieldto' => 'customers', 'active' => 1]) > 0) {
                      $customer_custom_fields = true; ?>
                    <li role="presentation" class="<?php if ($this->input->get('tab') == 'custom_fields') {
                          echo 'active';
                      }; ?>">
                        <a href="#custom_fields" aria-controls="custom_fields" role="tab" data-toggle="tab">
                            <?php echo hooks()->apply_filters('customer_profile_tab_custom_fields_text', _l('custom_fields')); ?>
                        </a>
                    </li>
                    <?php
                  } ?>
                    <li role="presentation">
                        <a href="#billing_and_shipping" aria-controls="billing_and_shipping" role="tab"
                            data-toggle="tab">
                            <?php echo _l('billing_shipping'); ?>
                        </a>
                    </li>
                  <li role="presentation">
                        <a href="#reconcilliation_info" aria-controls="reconcilliation_info" role="tab"
                            data-toggle="tab">
                            <?php echo _l('reconcilliation_info'); ?>
                        </a>
                    </li>
                    <?php hooks()->do_action('after_customer_billing_and_shipping_tab', isset($client) ? $client : false); ?>
                    <?php if (isset($client)) { ?>
                    
                    <?php hooks()->do_action('after_customer_admins_tab', $client); ?>
                    <?php } ?>
                </ul>
            </div>
        </div>
        <div class="tab-content mtop15">
            <?php hooks()->do_action('after_custom_profile_tab_content', isset($client) ? $client : false); ?>
            <?php if ($customer_custom_fields) { ?>
            <div role="tabpanel" class="tab-pane <?php if ($this->input->get('tab') == 'custom_fields') {
                      echo ' active';
                  }; ?>" id="custom_fields">
                <?php $rel_id = (isset($client) ? $client->userid : false); ?>
                <?php echo render_custom_fields('customers', $rel_id); ?>
            </div>
            <?php } ?>
            <div role="tabpanel" class="tab-pane<?php if (!$this->input->get('tab')) {
                      echo ' active';
                  }; ?>" id="contact_info">
                <div class="row">
                    
                    <div class="col-md-<?php echo !isset($client) ? 12 : 8; ?>">
                        <?php hooks()->do_action('before_customer_profile_company_field', $client ?? null); ?>
                        
                        
                        <?php echo render_input('company', 'client_company', $client->company, 'text', ['readonly' => 'readonly']); ?>


                        <div id="company_exists_info" class="hide"></div>
                        <?php hooks()->do_action('after_customer_profile_company_field', $client ?? null); ?>
                        
                      <?php echo render_input('vat', 'client_vat_number', $client->vat, 'text', ['readonly' => 'readonly']); ?>
                        <?php hooks()->do_action('before_customer_profile_phone_field', $client ?? null); ?>
                      <?php echo render_input('phonenumber', 'client_phonenumber', $client->phonenumber, 'text', ['readonly' => 'readonly']); ?>

                        <?php hooks()->do_action('after_customer_profile_company_phone', $client ?? null); ?>
                        <?php if ((isset($client) && empty($client->website)) || !isset($client)) {
                      $value = (isset($client) ? $client->website : '');
                      echo render_input('website', 'client_website', $value);
  
  $value = isset($client) ? $client->client_email : '';
        echo render_input('client_email', 'Email', $value);
                  } else { ?>
                      
                      <?php echo render_input('email', 'client_email', $client->client_email, 'text', ['readonly' => 'readonly']); ?>
                        <div class="form-group">
                            <label for="website"><?php echo _l('client_website'); ?></label>
                            <div class="input-group">
                                <input type="text" name="website" id="website" value="<?php echo $client->website; ?>"
                                    class="form-control" readonly>
                                <span class="input-group-btn">
                                    <a href="<?php echo maybe_add_http($client->website); ?>" class="btn btn-default"
                                        target="_blank" tabindex="-1">
                                        <i class="fa fa-globe"></i></a>
                                </span>

                            </div>
                        </div>
                        <?php }
                     
                     ?>
                      
                      <div class="row">
    <div class="col-md-6">
   

<div class="form-group">
    
    
  <?php echo render_input('status', 'merchant_add_edit_status', $client->account_status, 'text', ['readonly' => 'readonly']); ?>
</div>

</div>
                       <div class="col-md-6">


<div class="form-group">
    <label for="merchant_application"><?php echo _l('merchant_add_edit_application'); ?></label>
    <input type="text" 
           class="form-control" 
           id="merchant_application" 
           value="<?php echo $client->is_application_submitted == '1' ? 'Yes' : 'No'; ?>" 
           disabled>
    <input type="hidden" 
           name="merchant_application" 
           value="<?php echo $client->is_application_submitted; ?>">
</div>


</div>
</div>
  
                    </div>
                  
                </div>
              
            </div>
          
          
            <?php if (isset($client)) { ?>
            <div role="tabpanel" class="tab-pane" id="customer_admins">
                <?php if (staff_can('create',  'customers') || staff_can('edit',  'customers')) { ?>
                <a href="#" data-toggle="modal" data-target="#customer_admins_assign"
                    class="btn btn-primary mbot30"><?php echo _l('assign_admin'); ?></a>
                <?php } ?>
                <table class="table dt-table">
                    <thead>
                        <tr>
                            <th><?php echo _l('staff_member'); ?></th>
                            <th><?php echo _l('customer_admin_date_assigned'); ?></th>
                            <?php if (staff_can('create',  'customers') || staff_can('edit',  'customers')) { ?>
                            <th><?php echo _l('options'); ?></th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customer_admins as $c_admin) { ?>
                        <tr>
                            <td><a href="<?php echo admin_url('profile/' . $c_admin['staff_id']); ?>">
                                    <?php echo staff_profile_image($c_admin['staff_id'], [
                           'staff-profile-image-small',
                           'mright5',
                           ]);
                           echo get_staff_full_name($c_admin['staff_id']); ?></a>
                            </td>
                            <td data-order="<?php echo $c_admin['date_assigned']; ?>">
                                <?php echo _dt($c_admin['date_assigned']); ?></td>
                            <?php if (staff_can('create',  'customers') || staff_can('edit',  'customers')) { ?>
                            <td>
                                <a href="<?php echo admin_url('clients/delete_customer_admin/' . $client->userid . '/' . $c_admin['staff_id']); ?>"
                                    class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete">
                                    <i class="fa-regular fa-trash-can fa-lg"></i>
                                </a>
                            </td>
                            <?php } ?>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <?php } ?>
            <div role="tabpanel" class="tab-pane" id="billing_and_shipping">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-6">
                                <h4
                                    class="tw-font-medium tw-text-base tw-text-neutral-700 tw-flex tw-justify-between tw-items-center tw-mt-0 tw-mb-6">  
                                </h4>

        <?php
// Assuming you're initializing the $bank object somewhere in your code
// For a new bank, ensure there's a default status set if none exists
if (!isset($client) || $client === null) {
    $client = new stdClass();
}
$client->is_application_submitted	 = '0'; // Default status for new banks

?>

<div class="form-group">
    <?php echo render_input('address', 'client_address', $client->address, 'text', ['readonly' => 'readonly']); ?>
  
</div>

                              
                              <div class="row">
    <div class="col-md-6">
     
<div class="form-group">
    
    
  <?php echo render_input('city', 'client_city', $client->city, 'text', ['readonly' => 'readonly']); ?>
</div>

</div>
                       <div class="col-md-6">
     
<div class="form-group">
    <?php echo render_input('state', 'client_state', $client->state, 'text', ['readonly' => 'readonly']); ?>
</div>


</div>
</div>
                              
                              <div class="row">
    <div class="col-md-6">
     
<div class="form-group">
    
    
  <?php echo render_input('zip', 'client_postal_code', $client->zip, 'text', ['readonly' => 'readonly']); ?>
</div>

</div>
                       <div class="col-md-6">
     
<div class="form-group">
    <?php 
    $countries = get_all_countries();
    $customer_default_country = get_option('customer_default_country');
    $selected = (isset($client->country) ? $client->country : $customer_default_country);
    // Render the select menu as disabled for display
    echo render_select('country_disabled', $countries, ['country_id', ['short_name']], 'clients_country', $selected, ['data-none-selected-text' => _l('dropdown_non_selected_tex'), 'disabled' => true]);
    // Use a hidden input to actually submit the value
    echo form_hidden('country', $selected);
    ?>
</div>



</div>
</div>


                                
                            </div>
                            
                            <?php if (isset($client) &&
                        (total_rows(db_prefix() . 'invoices', ['clientid' => $client->userid]) > 0 || total_rows(db_prefix() . 'estimates', ['clientid' => $client->userid]) > 0 || total_rows(db_prefix() . 'creditnotes', ['clientid' => $client->userid]) > 0)) { ?>
                            <div class="col-md-12">
                                <div class="alert alert-warning">
                                    <div class="checkbox checkbox-default -tw-mb-0.5">
                                        <input type="checkbox" name="update_all_other_transactions"
                                            id="update_all_other_transactions">
                                        <label for="update_all_other_transactions">
                                            <?php echo _l('customer_update_address_info_on_invoices'); ?><br />
                                        </label>
                                    </div>
                                    <p class="tw-ml-7 tw-mb-0">
                                        <?php echo _l('customer_update_address_info_on_invoices_help'); ?>
                                    </p>
                                    <div class="checkbox checkbox-default">
                                        <input type="checkbox" name="update_credit_notes" id="update_credit_notes">
                                        <label for="update_credit_notes">
                                            <?php echo _l('customer_profile_update_credit_notes'); ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="reconcilliation_info">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-6">

                      <div class="percentage-input">          
    <?php $value = (isset($client->Rolling_Reserve) ? $client->Rolling_Reserve : ''); ?>
    <?php echo render_input('Rolling_Reserve', 'Rolling_Reserve', $value, 'number', ['readonly' => 'readonly']); ?>
</div><br>

                                       

<div class="percentage-input">
    <?php $value = (isset($client->MDR_Fee) ? $client->MDR_Fee : ''); ?>
    <?php echo render_input('MDR_Fee', 'MDR_Fee', $value, 'number', ['readonly' => 'readonly']); ?>
</div>

<?php $value = (isset($client->Monthly_Gateway_Fee) ? $client->Monthly_Gateway_Fee : ''); ?>
<?php echo render_input('Monthly_Gateway_Fee', 'Monthly_Gateway_Fee', $value,'number', ['readonly' => 'readonly']); ?>

<?php $value = (isset($client->Monthly_Deduction_Fee) ? $client->Monthly_Deduction_Fee : ''); ?>
<?php echo render_input('Monthly_Deduction_Fee', 'Monthly_Deduction_Fee', $value, 'number', ['readonly' => 'readonly']); ?>

<?php $value = (isset($client->Transaction_Fee) ? $client->Transaction_Fee : ''); ?>
<?php echo render_input('Transaction_Fee', 'Transaction_Fee', $value, 'number', ['readonly' => 'readonly']); ?>


<?php $value = (isset($client->Chargeback_Fees) ? $client->Chargeback_Fees : ''); ?>
<?php echo render_input('Chargeback_Fees', 'Chargeback_Fees', $value, 'number', ['readonly' => 'readonly']); ?>

<div class="percentage-input">
    <?php $value = (isset($client->Bank_Payment_Fee) ? $client->Bank_Payment_Fee : ''); ?>
    <?php echo render_input('Bank_Payment_Fee', 'Settlement_Fee', $value, 'number', ['readonly' => 'readonly']); ?>
</div>
                              <hr>
                              
                             
                              
                              
                              

                              <?php 
// Example PHP code to fetch custom columns for a client
$query = $this->db->get_where('tblreconciliation', array('client_id' => $client->userid));
$customColumns = $query->result_array();
?>
                              <?php foreach ($customColumns as $column): ?>
    <div class="button-input">
      
      <?php
      $columnId = $this->input->post('column_id'); 
      
// Now, perform the deletion using the correct column name ('id') in your query
    $this->db->where('id', $columnId);
    
      
echo render_input('reconciliation[' . $column['id'] . '][column_figure]', $column['column_name'], $column['column_figure'], 'number', ['readonly' => 'readonly']);


?>
      
    </div>
    
    
<?php endforeach; ?>

<!-- Button to trigger modal -->
                              

                                    
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?php if (isset($client)) { ?>
<?php if (staff_can('create',  'customers') || staff_can('edit',  'customers')) { ?>
<div class="modal fade" id="customer_admins_assign" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('clients/assign_admins/' . $client->userid)); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('assign_admin'); ?></h4>
            </div>
            <div class="modal-body">
                <?php
               $selected = [];
               foreach ($customer_admins as $c_admin) {
                   array_push($selected, $c_admin['staff_id']);
               }
               echo render_select('customer_admins[]', $staff, ['staffid', ['firstname', 'lastname']], '', $selected, ['multiple' => true], [], '', '', false); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<?php } ?>
<?php } ?>
<?php $this->load->view('admin/clients/client_group'); ?>
  