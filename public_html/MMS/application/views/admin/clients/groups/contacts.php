<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (isset($client)) { ?>
<h4 class="customer-profile-group-heading">
    <?php echo is_empty_customer_company($client->userid) ? _l('Our Team') : _l('Our Team'); ?></h4>

<?php if ((staff_can('create',  'customers') || is_customer_admin($client->userid)) && $client->registration_confirmed == '1') {
    $disable_new_contacts = false;
    if (is_empty_customer_company($client->userid) && total_rows(db_prefix() . 'contacts', ['userid' => $client->userid]) == 1) {
        $disable_new_contacts = true;
    } ?>
<div class="inline-block new-contact-wrapper" data-title="<?php echo _l('customer_contact_person_only_one_allowed'); ?>"
    <?php if ($disable_new_contacts) { ?> data-toggle="tooltip" <?php } ?>>
   
</div>
<?php
} ?>
<?php
   $table_data = [_l('clients_list_full_name')];
   if (is_gdpr() && get_option('gdpr_enable_consent_for_contacts') == '1') {
       array_push($table_data, [
            'name'     => _l('gdpr_consent') . ' (' . _l('gdpr_short') . ')',
            'th_attrs' => ['id' => 'th-consent', 'class' => 'not-export'],
         ]);
   }
  $table_data     = array_merge($table_data, [_l('client_email'), _l('contact_position'), _l('client_phonenumber'), _l('clients_list_last_login')]);
   $custom_fields = get_custom_fields('contacts', ['show_on_table' => 1]);
   foreach ($custom_fields as $field) {
       array_push($table_data, [
       'name'     => $field['name'],
       'th_attrs' => ['data-type' => $field['type'], 'data-custom-field' => 1],
     ]);
   }
   echo render_datatable($table_data, 'contacts'); ?>
<?php } ?>
<div id="contact_data"></div>
<div id="consent_data"></div>