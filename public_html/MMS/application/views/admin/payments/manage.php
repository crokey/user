<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();

$merchant_id = get_merchant_userid();

$is_full_access = get_is_full_access($merchant_id);

// Redirect if the user does not have full access
if (!$is_full_access) {
    redirect('https://user.bearlapay.com/MMS/admin/');
    exit;  // Make sure to stop further script execution after redirect
}

?>
<div id="wrapper">
    <div class="content">
        <div class="panel_s">
            <div style="--tw-bg-opacity: 1;
    border-radius: 0.375rem;
    padding: 2.5rem;
    position: relative;">
                <div class="panel-table-full">
                    <?php $this->load->view('admin/payments/table_html'); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
$(function() {
    initDataTable('.table-payments', admin_url + 'payments/table', undefined, undefined, 'undefined',
        <?php echo hooks()->apply_filters('payments_table_default_order', json_encode([0, 'desc'])); ?>);
});
</script>
</body>

</html>