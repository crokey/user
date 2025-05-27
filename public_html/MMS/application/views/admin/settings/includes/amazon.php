<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<h4><?php echo _l('aws_settings'); ?></h4>

<!-- AWS Access Key -->
<?php echo render_input('settings[aws_access_key]', 'AWS Access Key', get_option('aws_access_key')); ?>

<!-- AWS Secret Key -->
<?php echo render_input('settings[aws_secret_key]', 'AWS Secret Key', get_option('aws_secret_key')); ?>

<!-- AWS Default Region -->
<?php echo render_input('settings[aws_default_region]', 'AWS Default Region', get_option('aws_default_region')); ?>

<!-- AWS S3 Bucket Name -->
<?php echo render_input('settings[aws_s3_bucket]', 'AWS S3 Bucket Name', get_option('aws_s3_bucket')); ?>

<!-- Other AWS settings as needed -->
<!-- ... -->

<hr />

<!-- Save Button -->
<!-- You can add a save button to submit the form with the new settings -->
<button type="submit" class="btn btn-primary"><?php echo _l('save'); ?></button>
