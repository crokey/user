<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="panel_s">
            <div style="--tw-bg-opacity: 1;
    border-radius: 0.375rem;
    
    position: relative;">
                <div class="panel-table-full">
                    <?php $this->load->view('admin/payments/profiletable_html'); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
$(function() {
    initDataTable('.table-payments', admin_url + 'payments/profiletable', undefined, undefined, 'undefined',
        <?php echo hooks()->apply_filters('payments_table_default_order', json_encode([0, 'desc'])); ?>);
});
</script>
</body>

</html>