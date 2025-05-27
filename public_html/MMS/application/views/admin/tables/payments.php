

<?php

defined('BASEPATH') or exit('No direct script access allowed');

$hasPermissionDelete = staff_can('delete',  'payments');

$aColumns = [
    db_prefix() . 'transactions.id as id',
    'client_id',
    'date',
    'transaction_id',
    'remitter_name',
    'beneficiary_name',
    'transaction_type',
    'currency',
    'amount',
    'status',
    'tblclients.company as merchant_name',
];

$join = [
    'LEFT JOIN tblclients ON tblclients.userid = ' . db_prefix() . 'transactions.client_id',
];

$loggedInMerchantId = get_merchant_userid();  // This is just an example, replace it with actual method to get current user ID

$where = [
    'AND ' . db_prefix() . 'transactions.client_id = ' . $loggedInMerchantId, // Filter transactions by current user ID
];

if ($clientid != '') {
    array_push($where, 'AND ' . db_prefix() . 'clients.userid=' . $this->ci->db->escape_str($clientid));
}

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'transactions';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'client_id',
]);

$output  = $result['output'];
$rResult = $result['rResult'];



foreach ($rResult as $aRow) {
    $row = [];

    $link = admin_url('payments/payment/' . $aRow['id']);
    $options = icon_btn('payments/payment/' . $aRow['id'], 'fa-regular fa-pen-to-square');

    $numberOutput = '<a href="' . $link . '">' . $aRow['date'] . '</a>';
    $numberOutput .= '<div class="">';
    $numberOutput .= '<a href="' . $link . '">' . _l('view') . '</a>';
    $numberOutput .= '</div>';

    // Row 1
    $row[] = $numberOutput;
    $row[] = (!empty($aRow['merchant_name'])) ? $aRow['merchant_name'] : 'N/A'; // Use merchant_name
    $row[] = $aRow['transaction_id'];
    $row[] = (!empty($aRow['remitter_name'])) ? $aRow['remitter_name'] : 'N/A';
    $row[] = (!empty($aRow['beneficiary_name'])) ? $aRow['beneficiary_name'] : 'N/A';
    $row[] = $aRow['transaction_type'];
    $row[] = $aRow['currency'];

    // Formatting the amount based on currency
    $formatted_amount = number_format((float)$aRow['amount'], 2, '.', ',');
    switch ($aRow['currency']) {
        case 'EUR':
            $formatted_amount = '€' . $formatted_amount;
            break;
        case 'USD':
            $formatted_amount = '$' . $formatted_amount;
            break;
        case 'CAD':
            $formatted_amount = 'CA$' . $formatted_amount;
            break;
        case 'AUD':
            $formatted_amount = 'AU$' . $formatted_amount;
            break;
        case 'GBP':
            $formatted_amount = '£' . $formatted_amount;
            break;
        case 'JPY':
            $formatted_amount = '¥' . $formatted_amount;
            break;
        default:
            $formatted_amount = $formatted_amount; // No symbol for other currencies
            break;
    }
    $row[] = $formatted_amount;

    $status = $aRow['status']; // Get the status from your data source
    $statusClass = ucfirst(strtolower($status)); // Capitalizes the first letter
    $row[] = "<span class='status $statusClass'>" . _d($status) . "</span>";

    $row['DT_RowClass'] = 'has-row-options';

    $output['aaData'][] = $row;
}
