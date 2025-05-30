<?php

defined('BASEPATH') or exit('No direct script access allowed');

$total_client_contacts = total_rows(db_prefix() . 'contacts', ['userid' => $client_id]);
$this->ci->load->model('gdpr_model');

$consentContacts = get_option('gdpr_enable_consent_for_contacts');
$aColumns        = [ 'CONCAT(firstname, \' \', lastname) as full_name'];
if (is_gdpr() && $consentContacts == '1') {
    array_push($aColumns, '1');
}
$aColumns = array_merge($aColumns, [
    'email',
    'title',
    'phonenumber',
    'last_login',
]);

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'contacts';
$join         = [];

$custom_fields = get_table_custom_fields('contacts');

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . db_prefix() . 'contacts.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
}

$where = ['AND userid=' . $this->ci->db->escape_str($client_id)];

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [db_prefix() . 'contacts.id as id', 'userid', 'is_primary']);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $rowName = '<img src="' . contact_profile_image_url($aRow['id']) . '" class="client-profile-image-small mright5"><a href="#" onclick="contact(' . $aRow['userid'] . ',' . $aRow['id'] . ');return false;">' . $aRow['full_name'] . '</a>';

    

    $rowName .= '<a href="#" onclick="contact(' . $aRow['userid'] . ',' . $aRow['id'] . ');</a>';

   

    

    $rowName .= '</div>';


    $row[] = $rowName;

    if (is_gdpr() && $consentContacts == '1') {
        $consentHTML = '<p class="bold"><a href="#" onclick="view_contact_consent(' . $aRow['id'] . '); return false;">' . _l('view_consent') . '</a></p>';
        $consents    = $this->ci->gdpr_model->get_consent_purposes($aRow['id'], 'contact');
        foreach ($consents as $consent) {
            $consentHTML .= '<p style="margin-bottom:0px;">' . $consent['name'] . (!empty($consent['consent_given']) ? '<i class="fa fa-check text-success pull-right"></i>' : '<i class="fa fa-remove text-danger pull-right"></i>') . '</p>';
        }
        $row[] = $consentHTML;
    }

    $row[] = '<a href="mailto:' . $aRow['email'] . '">' . $aRow['email'] . '</a>';

    $row[] = $aRow['title'];

    $row[] = '<a href="tel:' . $aRow['phonenumber'] . '">' . $aRow['phonenumber'] . '</a>';

   

    $row[] = (!empty($aRow['last_login']) ? '<span class="text-has-action is-date" data-toggle="tooltip" data-title="' . _dt($aRow['last_login']) . '">' . time_ago($aRow['last_login']) . '</span>' : '');

    // Custom fields add values
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $row['DT_RowClass'] = 'has-row-options';


    $row = hooks()->apply_filters('admin_customer_contacts_table_row', $row, $aRow);
    $output['aaData'][] = $row;
}
