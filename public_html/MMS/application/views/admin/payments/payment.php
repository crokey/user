<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-5">
              <button type="button" class="btn btn-default" style="margin-bottom: 10px;" onclick="window.history.back();">
    <i class="fa fa-arrow-left"></i> Go Back
</button>
                
                <div class="col-md-12 no-padding">
                    <div class="panel_s">
                      <?php
if ($payment->transaction_type == 'FX') {
    echo '<div class="panel-body">';
                          
    echo form_open($this->uri->uri_string());
    // Transaction type info
    echo '<i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="' . _l('transaction_type_info') . '"></i>';
    // Making inputs readonly
    
  
    echo render_input('paymentmethod', 'transaction_type', $payment->transaction_type, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']); 
    echo render_input('transactionid', 'payment_transaction_id', $payment->transaction_id, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']);
    echo render_input('transactionid', 'transaction_currency_from', $payment->exchange_from, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']);
    $formatted_amount_from = number_format((float)$payment->exchange_amount_from, 2, '.', ',');
    echo render_input('text', 'transaction_amount_from', $formatted_amount_from, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']); 
    echo render_input('text', 'transaction_fx_rate', $payment->fx_rates, 'number', ['readonly' => 'readonly', 'style' => 'color: #000000;']); 
    echo render_input('transactionid', 'transaction_currency_to', $payment->exchange_to, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']);
    $formatted_amount_to = number_format((float)$payment->exchange_amount_to, 2, '.', ',');
    echo render_input('text', 'transaction_amount_to', $formatted_amount_to, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']); 
    echo render_input('text', 'transaction_edit_date', $payment->date, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']);  
    $formatted_amount = number_format((float)$payment->balance, 2, '.', ',');
    echo render_input('text', 'transaction_amount_balance', $formatted_amount, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']);   

  	echo '<i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="' . _l('transaction_status_info') . '"></i>';
    echo render_input('text', 'transaction_status', $payment->status, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']);   

    hooks()->do_action('before_admin_edit_payment_form_submit', $payment); 

    // Submit button could also be removed or disabled if no form submission is required
    echo '<div class="btn-bottom-toolbar text-right">
        <button type="submit" class="btn btn-primary">' . _l('submit') . '</button>
    </div>';
    echo form_close(); 
    echo '</div>';
} else if ($payment->transaction_type == 'Withdraw') {
    echo '<div class="panel-body">';
                          
    echo form_open($this->uri->uri_string());
    // Transaction type info
    echo '<i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="' . _l('transaction_type_info') . '"></i>';
    // Making inputs readonly
    echo render_input('text', 'transaction_type', $payment->transaction_type, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']); 
    echo render_input('transactionid', 'payment_transaction_id', $payment->transaction_id, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']); 
    echo render_input('text', 'transaction_bank_name', $payment->bank_name, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']);  
    echo render_input('text', 'transaction_beneficiary_name', $payment->beneficiary_name, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']); 
    echo render_input('text', 'transaction_beneficiary_address', $payment->beneficiary_address, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']); 
    echo render_input('text', 'transaction_iban', $payment->iban, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']); ; 
    
    // Format the amount
    $formatted_amount = number_format((float)$payment->balance, 2, '.', ',');
    echo render_input('text', 'transaction_amount_balance', $formatted_amount, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']);   

  	echo '<i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="' . _l('transaction_status_info') . '"></i>';
  	echo render_input('text', 'transaction_status', $payment->status, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']);   

    hooks()->do_action('before_admin_edit_payment_form_submit', $payment); 

    // Submit button could also be removed or disabled if no form submission is required
    echo '<div class="btn-bottom-toolbar text-right">
        <button type="submit" class="btn btn-primary">' . _l('submit') . '</button>
    </div>';
    echo form_close(); 
    echo '</div>';
} else if ($payment->transaction_type == 'Transfer(In)') {
    echo '<div class="panel-body">';

    echo form_open($this->uri->uri_string());
    // Transaction type info
    echo '<i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="' . _l('transaction_type_info') . '"></i>';
    // Making inputs readonly
    echo render_input('text', 'transaction_type', $payment->transaction_type, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']); 
    echo render_input('transactionid', 'payment_transaction_id', $payment->transaction_id, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']); 
    echo render_input('text', 'transaction_beneficiary_name', $payment->beneficiary_name, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']);
    echo render_input('text', 'transaction_remitter_name', $payment->remitter_name, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']); 

    // Format the amount
    $formatted_amount = number_format((float)$payment->amount, 2, '.', ',');
    echo render_input('text', 'transaction_amount', $formatted_amount, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']);   

    echo render_input('text', 'transaction_currency', $payment->currency, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']);

    echo '<i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="' . _l('transaction_status_info') . '"></i>';
    echo render_input('text', 'transaction_status', $payment->status, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']);   

    hooks()->do_action('before_admin_edit_payment_form_submit', $payment); 

    // Submit button could also be removed or disabled if no form submission is required
    echo '<div class="btn-bottom-toolbar text-right">
        <button type="submit" class="btn btn-primary">' . _l('submit') . '</button>
    </div>';
    echo form_close(); 
    echo '</div>';
}
 else if ($payment->transaction_type == 'Exchange') {
    echo '<div class="panel-body">';
                          
    echo form_open($this->uri->uri_string());
    // Transaction type info
    echo '<i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="' . _l('transaction_type_info') . '"></i>';
    // Making inputs readonly
    echo render_input('text', 'transaction_type', $payment->transaction_type, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']); 
    echo render_input('transactionid', 'payment_transaction_id', $payment->transaction_id, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']); 
    echo render_input('text', 'transaction_exchange_amount_from', $payment->exchange_from, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']);  
    echo render_input('number', 'transaction_amount', $payment->exchange_amount_from, 'number', ['readonly' => 'readonly', 'style' => 'color: #000000;']); 
    echo render_input('number', 'transaction_exchange_rate', $payment->fx_rates, 'number', ['readonly' => 'readonly', 'style' => 'color: #000000;']); 
    echo render_input('text', 'transaction_exchange_amount_to', $payment->exchange_to, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']);  
    echo render_input('number', 'transaction_amount', $payment->exchange_amount_to, 'number', ['readonly' => 'readonly', 'style' => 'color: #000000;']);
    

    echo render_input('text', 'transaction_amount_balance', $payment->balance, 'number', ['readonly' => 'readonly', 'style' => 'color: #000000;']);
    echo render_input('text', 'transaction_amount_crypto_balance', $payment->crypto_balance, 'number', ['readonly' => 'readonly', 'style' => 'color: #000000;']); 

    echo '<i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="' . _l('transaction_status_info') . '"></i>';
    echo render_input('text', 'transaction_status', $payment->status, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']);   

    hooks()->do_action('before_admin_edit_payment_form_submit', $payment); 

    // Submit button could also be removed or disabled if no form submission is required
    echo '<div class="btn-bottom-toolbar text-right">
        <button type="submit" class="btn btn-primary">' . _l('submit') . '</button>
    </div>';
    echo form_close(); 
    echo '</div>';
}  else if ($payment->transaction_type == 'Withdraw(Crypto)') {
    echo '<div class="panel-body">';
                          
    echo form_open($this->uri->uri_string());
    // Transaction type info
    echo '<i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="' . _l('transaction_type_info') . '"></i>';
    // Making inputs readonly
    echo render_input('text', 'transaction_type', $payment->transaction_type, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']); 
    echo render_input('transactionid', 'payment_transaction_id', $payment->transaction_id, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']); 
    echo render_input('transactionid', 'transaction_crypto_from', $payment->currency, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']);  
    echo render_input('text', 'transaction_withdraw_amount_from', $payment->amount, 'number', ['readonly' => 'readonly', 'style' => 'color: #000000;']); 
     
     
    

   	echo render_input('text', 'transaction_remaining_balance', $payment->balance, 'number', ['readonly' => 'readonly', 'style' => 'color: #000000;']);
  
    echo render_input('text', 'transaction_wallet_name', $payment->beneficiary_name, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']);
  
   	echo render_input('text', 'transaction_wallet_address', $payment->beneficiary_address, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']); 
  
    echo render_input('text', 'transaction_edit_date', $payment->date, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']); 
  	
  	echo '<i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="' . _l('transaction_status_info') . '"></i>';

   	echo render_input('text', 'transaction_status', $payment->status, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']);   

    hooks()->do_action('before_admin_edit_payment_form_submit', $payment); 

    // Submit button could also be removed or disabled if no form submission is required
    echo '<div class="btn-bottom-toolbar text-right">
        <button type="submit" class="btn btn-primary">' . _l('submit') . '</button>
    </div>';
    echo form_close(); 
    echo '</div>';
}
                      else if ($payment->transaction_type == 'Crypto-to-Fiat') {
    echo '<div class="panel-body">';
                          
    echo form_open($this->uri->uri_string());
    // Transaction type info
    echo '<i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="' . _l('transaction_type_info') . '"></i>';
    // Making inputs readonly
    echo render_input('text', 'transaction_type', $payment->transaction_type, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']); 
    echo render_input('transactionid', 'payment_transaction_id', $payment->transaction_id, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']); 
    echo render_input('transactionid', 'transaction_exchange_amount_from', $payment->exchange_from, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']);  
    echo render_input('text', 'transaction_amount_from', $payment->exchange_amount_from, 'number', ['readonly' => 'readonly', 'style' => 'color: #000000;']); 
    echo render_input('text', 'transaction_exchange_rate', $payment->fx_rates, 'number', ['readonly' => 'readonly', 'style' => 'color: #000000;']); 
    echo render_input('transactionid', 'transaction_currency_to', $payment->exchange_to, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']);
    $formatted_amount_to = number_format((float)$payment->exchange_amount_to, 2, '.', ',');                    
    echo render_input('text', 'transaction_amount_to', $formatted_amount_to, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']); 
    echo render_input('text', 'transaction_edit_date', $payment->date, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']); 
    
    $formatted_balance = number_format((float)$payment->balance, 2, '.', ',');
    echo render_input('text', 'transaction_amount_balance', $formatted_balance, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']); 
                        
   echo '<i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="' . _l('transaction_status_info') . '"></i>';
   echo render_input('text', 'transaction_status', $payment->status, 'text', ['readonly' => 'readonly', 'style' => 'color: #000000;']);   

    hooks()->do_action('before_admin_edit_payment_form_submit', $payment); 

    // Submit button could also be removed or disabled if no form submission is required
    echo '<div class="btn-bottom-toolbar text-right">
        <button type="submit" class="btn btn-primary">' . _l('submit') . '</button>
    </div>';
    echo form_close(); 
    echo '</div>';
}

  ?>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="tw-flex tw-justify-between tw-mb-2.5">
                    <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700 tw-mb-0">
                        <?php echo _l('Transaction'); ?>
                    </h4>
                    <div class="tw-self-start">
                        <div class="btn-group">
                         
                            <!--
<a href="#" data-toggle="modal" data-target="#payment_send_to_client"
    class="payment-send-to-client btn-with-tooltip btn btn-default">
    <i class="fa-regular fa-envelope"></i>
</a>

<a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
    aria-haspopup="true" aria-expanded="false">
    <i class="fa-regular fa-file-pdf"></i>
    <?php //if (is_mobile()) {
        //echo ' PDF';
    //} ?> <span class="caret"></span>
</a>
-->


                            <ul class="dropdown-menu dropdown-menu-right">
                                <li class="hidden-xs">
                                    <a
                                        href="<?php echo admin_url('payments/pdf/' . $payment->id . '?output_type=I'); ?>">
                                        <?php echo _l('view_pdf'); ?>
                                    </a>
                                </li>
                                <li class="hidden-xs">
                                    <a href="<?php echo admin_url('payments/pdf/' . $payment->id . '?output_type=I'); ?>"
                                        target="_blank">
                                        <?php echo _l('view_pdf_in_new_window'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo admin_url('payments/pdf/' . $payment->id); ?>">
                                        <?php echo _l('download'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo admin_url('payments/pdf/' . $payment->id . '?print=true'); ?>"
                                        target="_blank">
                                        <?php echo _l('print'); ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <?php if (staff_can('delete',  'payments')) { ?>
                       <!-- <a href="<?php echo admin_url('payments/delete/' . $payment->id); ?>"
                            class="btn btn-danger _delete">
                            <i class="fa fa-remove"></i>
                        </a>-->
                        <?php } ?>
                    </div>
                </div>

                <div class="panel_s -tw-mt-1.5">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6 col-sm-6">
                                
                            </div>
                            <div class="col-sm-6 text-right">
                                
                            </div>
                        </div>
                        <div class="col-md-12 text-center">
                            <h3 class="text-uppercase tw-font-medium tw-text-neutral-600">
                                <?php echo _l('TRANSACTION RECEIPT'); ?>
                            </h3>
                        </div>
                        <div class="col-md-12 mtop40">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="tw-text-neutral-500"><?php echo _l('transaction_date'); ?> <span
                                            class="pull-right bold"><?php echo _d($payment->date); ?></span></p>
                                    <hr class="tw-my-2" />
                                    <p class="tw-text-neutral-500"><?php echo _l('transaction_type'); ?>
                                        <span class="pull-right bold">
                                            <?php echo $payment->transaction_type; ?>
                                            
                                        </span>
                                    </p>
                                    <?php if (!empty($payment->transaction_id)) { ?>
                                    <hr class="tw-my-2" />
                                    <p class="tw-text-neutral-500"><?php echo _l('payment_transaction_id'); ?>: <span
                                            class="pull-right bold"><?php echo $payment->transaction_id; ?></span></p>
                                    <?php } ?>
                                </div>
                                <div class="clearfix"></div>
                                <div class="col-md-6">
                                  
                                    
                                        
                                  
                                  <?php
                          if ($payment->transaction_type == 'Withdraw' || $payment->transaction_type == 'Transfer(In)') { 
                                    echo '<div class="payment-preview-wrapper">';
                                        echo _l('payment_total_amount');echo'<br>';
                                       $formatted_amount = number_format((float)$payment->amount, 2, '.', ','); 
                                        echo ($formatted_amount); 
                                    echo '</div>';
                          } else {
                            echo '<div class="payment-preview-wrapper">';
                            	echo _l('payment_total_amount'); echo'<br>';
                            $formatted_amount = number_format((float)$payment->exchange_amount_to, 2, '.', ','); 
                            	echo ($formatted_amount);
                            echo '</div>';
                          }
                            ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 mtop30">
                            <h4 class="tw-font-medium tw-text-neutral-600">
                                <?php echo _l('payment_for_string'); ?>
                            </h4>
                          <?php
                          if ($payment->transaction_type == 'Withdraw') { 
    echo '<div class="table-responsive">';
    echo '<table class="table table-bordered !tw-mt-0">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>' . _l('transaction_table_number') . '</th>';
    echo '<th>' . _l('transaction_table_bank_name') . '</th>';
    echo '<th>' . _l('transaction_table_date') . '</th>';
    echo '<th>' . _l('transaction_table_beneficiary_name') . '</th>';
    echo '<th>' . _l('transaction_table_amount') . '</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    echo '<tr>';
    echo '<td>' . $payment->transaction_id . '</td>';
    echo '<td>' . $payment->bank_name . '</td>';
    echo '<td>' . _d($payment->date) . '</td>';
    echo '<td>' . $payment->beneficiary_name . '</td>';
    $formatted_amount = number_format((float)$payment->amount, 2, '.', ',');                        
    echo '<td>' . $formatted_amount . '</td>'; 
    echo '</tr>';
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
} else if ($payment->transaction_type == 'FX') { 
    echo '<div class="table-responsive">';
    echo '<table class="table table-bordered !tw-mt-0">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>' . _l('transaction_table_number') . '</th>';
    echo '<th>' . _l('transaction_table_date') . '</th>';
    echo '<th>' . _l('transaction_table_currency_from') . '</th>';
    echo '<th>' . _l('transaction_table_amount_from') . '</th>';
    echo '<th>' . _l('transaction_table_currency_to') . '</th>';
    echo '<th>' . _l('transaction_table_amount_to') . '</th>';                       
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    echo '<tr>';
    echo '<td>' . $payment->transaction_id . '</td>';
    echo '<td>' . _d($payment->date) . '</td>';
    echo '<td>' . $payment->exchange_from . '</td>';
    $formatted_amount_from = number_format((float)$payment->exchange_amount_from, 2, '.', ',');
    echo '<td>' . $formatted_amount_from . '</td>';
    echo '<td>' . $payment->exchange_to . '</td>';
    $formatted_amount_to = number_format((float)$payment->exchange_amount_to, 2, '.', ',');
    echo '<td>' . $formatted_amount_to . '</td>';              
    echo '</tr>';
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
} else if ($payment->transaction_type == 'Exchange') { 
    echo '<div class="table-responsive">';
    echo '<table class="table table-bordered !tw-mt-0">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>' . _l('transaction_table_number') . '</th>';
    echo '<th>' . _l('transaction_table_date') . '</th>';
    echo '<th>' . _l('transaction_table_exchange_amount_from') . '</th>';
    echo '<th>' . _l('transaction_table_amount_from') . '</th>';
    echo '<th>' . _l('transaction_table_exchange_amount_to') . '</th>';
    echo '<th>' . _l('transaction_table_amount_to') . '</th>';                       
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    echo '<tr>';
    echo '<td>' . $payment->transaction_id . '</td>';
    echo '<td>' . _d($payment->date) . '</td>';
    echo '<td>' . $payment->exchange_from . '</td>';
    echo '<td>' . $payment->exchange_amount_from . '</td>';
    echo '<td>' . $payment->exchange_to . '</td>';
    echo '<td>' . $payment->exchange_amount_to . '</td>';              
    echo '</tr>';
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
} else if ($payment->transaction_type == 'Crypto-to-Fiat') { 
    echo '<div class="table-responsive">';
    echo '<table class="table table-bordered !tw-mt-0">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>' . _l('transaction_table_number') . '</th>';
    echo '<th>' . _l('transaction_table_date') . '</th>';
    echo '<th>' . _l('transaction_table_exchange_crypto_amount_from') . '</th>';
    echo '<th>' . _l('transaction_table_amount_from') . '</th>';
    echo '<th>' . _l('transaction_table_exchange_currency_amount_to') . '</th>';
    echo '<th>' . _l('transaction_table_amount_to') . '</th>';                       
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    echo '<tr>';
    echo '<td>' . $payment->transaction_id . '</td>';
    echo '<td>' . _d($payment->date) . '</td>';
    echo '<td>' . $payment->exchange_from . '</td>';
    echo '<td>' . $payment->exchange_amount_from . '</td>';
    echo '<td>' . $payment->exchange_to . '</td>';
    $formatted_amount_to = number_format((float)$payment->exchange_amount_to, 2, '.', ',');
    echo '<td>' . $formatted_amount_to . '</td>';  
    echo '</tr>';
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
} else if ($payment->transaction_type == 'Withdraw(Crypto)') { 
    echo '<div class="table-responsive">';
    echo '<table class="table table-bordered !tw-mt-0">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>' . _l('transaction_table_number') . '</th>';
    echo '<th>' . _l('transaction_table_date') . '</th>';
    echo '<th>' . _l('transaction_table_crypto_withdraw_from') . '</th>';
    echo '<th>' . _l('transaction_table_crypto_withdraw_amount_from') . '</th>';                      
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    echo '<tr>';
    echo '<td>' . $payment->transaction_id . '</td>';
    echo '<td>' . _d($payment->date) . '</td>';
    echo '<td>' . $payment->currency . '</td>';
    $formatted_amount = number_format((float)$payment->amount, 2, '.', ',');                        
    echo '<td>' . $formatted_amount . '</td>';           
    echo '</tr>';
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}  else if ($payment->transaction_type == 'Transfer(In)') { 
    echo '<div class="table-responsive">';
    echo '<table class="table table-bordered !tw-mt-0">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>' . _l('transaction_table_number') . '</th>';
    echo '<th>' . _l('transaction_table_date') . '</th>';
    echo '<th>' . _l('transaction_table_remitter_name') . '</th>';
    echo '<th>' . _l('transaction_table_crypto_withdraw_from') . '</th>';
    echo '<th>' . _l('transaction_table_crypto_withdraw_amount_from') . '</th>';                      
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    echo '<tr>';
    echo '<td>' . $payment->transaction_id . '</td>';
    echo '<td>' . _d($payment->date) . '</td>';
    echo '<td>' . $payment->remitter_name . '</td>';
    echo '<td>' . $payment->currency . '</td>';
    $formatted_amount = number_format((float)$payment->amount, 2, '.', ',');                        
    echo '<td>' . $formatted_amount . '</td>';           
    echo '</tr>';
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}

                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="btn-bottom-pusher"></div>
    </div>
</div>
<?php $this->load->view('admin/payments/send_to_client'); ?>
<?php init_tail(); ?>
<script>
$(function() {
    appValidateForm($('form'), {
        amount: 'required',
        date: 'required'
    });
});
</script>
</body>

</html>
