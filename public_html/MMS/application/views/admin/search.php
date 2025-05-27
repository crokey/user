<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<ul class="dropdown-menu search-results animated fadeIn no-mtop display-block" id="top_search_dropdown">
    <?php
    $total = 0;
    foreach($result as $data){
       if(count($data['result']) > 0){
           $total++;
           ?>
           <li role="separator" class="divider"></li>
           <li class="dropdown-header"><?php echo $data['search_heading']; ?></li>
       <?php } ?>
       <?php foreach($data['result'] as $_result){
        $output = '';
        switch($data['type']){
            case 'clients':
            $output = '<a href="'.admin_url('clients/client/'.$_result['userid']).'">'.$_result['company'] .'</a>';
            break;
            case 'contacts':
            $output = '<a href="'.admin_url('clients/client/'.$_result['userid'].'?contactid='.$_result['id']).'">'.$_result['firstname'] .' ' . $_result['lastname'] .' <small>'.get_company_name($_result['userid']).'</small></a>';
            break;
            case 'staff':
            $output = '<a href="'.admin_url('staff/member/'.$_result['staffid']).'">'.$_result['firstname']. ' ' . $_result['lastname'] .'</a>';
            break;
            case 'tickets':
            $output = '<a href="'.admin_url('tickets/ticket/'.$_result['ticketid']).'">#'.$_result['ticketid'].' - '.$_result['subject'].'</a>';
            break;
            case 'custom_fields':
            $rel_data   = get_relation_data($_result['fieldto'], $_result['relid']);
            $rel_values = get_relation_values($rel_data, $_result['fieldto']);
            $output      = '<a class="pull-left" href="' . $rel_values['link'] . '">' . $rel_values['name'] .'<span class="pull-right">'._l($_result['fieldto']).'</span></a>';
            break;
        }
        ?>
        <li><?php echo hooks()->apply_filters('global_search_result_output', $output, ['result'=>$_result, 'type'=>$data['type']]); ?></li>
    <?php } ?>
<?php } ?>
<?php if($total == 0){ ?>
    <li class="padding-5 text-center search-no-results"><?php echo _l('not_results_found'); ?></li>
<?php } ?>
</ul>
