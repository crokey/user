<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $this->load->view('authentication/includes/head.php'); ?>
    <link href="<?php echo base_url('assets/css/loginwindow.css?v=' . time()); ?>" rel="stylesheet" id="custom-css">
</head>
<body class="tw-bg-neutral-100 login_admin">

<div class="tw-max-w-md tw-mx-auto tw-pt-24 authentication-form-wrapper tw-relative tw-z-20">
    <div class="company-logo text-center">
        <div class="auth-logo-img" title="Bearlapay">
            <?php get_dark_company_logo(); ?>
        </div>
    </div>
    <h1 class="tw-text-2xl tw-text-neutral-800 text-center tw-font-semibold tw-mb-5">
        Locked Screen
    </h1>

    <div class="tw-bg-white tw-mx-2 sm:tw-mx-6 tw-py-6 tw-px-6 sm:tw-px-8 tw-shadow tw-rounded-lg">
        <?php $this->load->view('authentication/includes/alerts'); ?>
        <?php echo form_open(admin_url('authentication/verify_lock')); ?>
        <div class="form-group">
            <label for="password" class="control-label">Enter Password to Continue</label>
            <input type="password" id="password" name="password" class="form-control" autofocus="1">
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block">Unlock</button>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>

</body>
</html>
