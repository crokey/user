<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="tw-flex tw-justify-between tw-items-end tw-mb-3">
    <h4 class="tw-my-0 tw-font-semibold tw-text-lg tw-text-neutral-700 section-heading section-heading-contact">
        <?php echo _l('clients_my_contact'); ?>
    </h4>
    <a href="<?php echo site_url('contacts'); ?>" class="btn btn-primary">
        <?php echo _l('clients_my_contacts'); ?>
    </a>
</div>
<?php
         echo form_open_multipart(
    site_url('contacts/contact/' . (isset($my_contact) ? $my_contact->id : '')),
    ['id' => 'contact-form']
);
         ?>
<div class="panel_s">
    <div class="panel-body">

        <div class="row">
            <div class="col-md-12">
                <?php if (isset($my_contact)) { ?>
                <img src="<?php echo contact_profile_image_url($my_contact->id, 'thumb'); ?>" id="contact-img"
                    class="client-profile-image-thumb">
                <?php if (!empty($my_contact->profile_image)) { ?>
                <a href="#" onclick="delete_contact_profile_image(<?php echo $my_contact->id; ?>); return false;"
                    class="text-danger pull-right" id="contact-remove-img"><i class="fa fa-remove"></i></a>
                <?php } ?>
                <hr />
                <?php } ?>
                <div id="contact-profile-image"
                    class="form-group<?php echo isset($my_contact) && !empty($my_contact->profile_image) ? ' hide' : ''; ?>">
                    <label for="profile_image" class="profile-image"><?php echo _l('client_profile_image'); ?></label>
                    <input type="file" name="profile_image" class="form-control" id="profile_image">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <?php $value = (isset($my_contact) ? $my_contact->firstname : ''); ?>
                        <?php echo render_input('firstname', 'client_firstname', $value); ?>
                        <?php echo form_error('firstname'); ?>
                    </div>
                    <div class="col-md-6">
                        <?php $value = (isset($my_contact) ? $my_contact->lastname : ''); ?>
                        <?php echo render_input('lastname', 'client_lastname', $value); ?>
                        <?php echo form_error('lastname'); ?>
                    </div>
                    <div class="col-md-6">
                        <?php $value = (isset($my_contact) ? $my_contact->email : ''); ?>
                        <?php echo render_input('email', 'client_email', $value, 'email'); ?>
                        <?php echo form_error('email'); ?>
                    </div>
                    <div class="col-md-6">
                        <?php
                      if (!isset($my_contact)) {
                          $value = $calling_code ?: '';
                      } else {
                          $value = empty($my_contact->phonenumber) ? $calling_code : $my_contact->phonenumber;
                      }
                   ?>
                        <?php echo render_input('phonenumber', 'client_phonenumber', $value, 'text', ['autocomplete' => 'off']); ?>
                        <?php echo form_error('phonenumber'); ?>
                    </div>
                    <div class="col-md-6">
                        <?php $value = (isset($my_contact) ? $my_contact->title : ''); ?>
                        <?php echo render_input('title', 'contact_position', $value); ?>
                    </div>
                    
                </div>
                
                
                
                
            </div>
            
            
        </div>
    </div>
    <div class="panel-footer">
        <div class="text-right">
            <button type="submit" class="btn btn-primary" autocomplete="off">
                <?php echo _l('submit'); ?>
            </button>
        </div>
    </div>
</div>
<?php echo form_close(); ?>
</div>
<script>
$('#send_set_password_email').click(function() {
    $('.client_password_set_wrapper').toggle();
    $('.password').prop('disabled', $(this).prop('checked') === true);
});

function delete_contact_profile_image(contact_id) {
    $.post(site_url + 'contacts/delete_profile_image/' + contact_id).done(function() {
        $('body').find('#contact-profile-image').removeClass('hide');
        $('body').find('#contact-remove-img').addClass('hide');
        $('body').find('#contact-img').attr('src', site_url + 'assets/images/user-placeholder.jpg');
    });
}
</script>