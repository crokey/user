<?php

defined('BASEPATH') or exit('No direct script access allowed');

render_datatable([
  _l('payments_table_date_heading'),
  _l('Merchant'),//payments_table_merchant_name
    _l('Transaction ID'),//payments_table_transacitonnumber_heading
    _l('Remitter Name'),//payments_table_remitter_heading
    _l('Beneficiary Name'),//payments_table_beneficiary_heading
    _l('Transaction Type'),//payments_table_type_heading
    _l('Currency'),//payment_currency_heading
    _l('payments_table_amount_heading'),
    _l('Status'),//payments_table_status_heading
  
], (isset($class) ? $class : 'payments'), [], [
    'data-last-order-identifier' => 'payments',
    'data-default-order'         => get_table_last_order('payments'),
]);