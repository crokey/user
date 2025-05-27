<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!-- Modal Contact -->
<div class="modal fade" id="contact" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open(admin_url('clients/form_contact/' . $customer_id . ($contactid ? '/' . $contactid : '')), ['id' => 'contact-form', 'autocomplete' => 'off']); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <div class="tw-flex">
                    <div class="tw-mr-4 tw-flex-shrink-0 tw-relative">
                        <?php if (isset($contact)) { ?>
                        <img src="<?php echo contact_profile_image_url($contact->id, 'small'); ?>" id="contact-img"
                            class="client-profile-image-small">
                        <?php if (!empty($contact->profile_image)) { ?>
                        <a href="#" onclick="delete_contact_profile_image(<?php echo $contact->id; ?>); return false;"
                            class="tw-bg-neutral-500/30 tw-text-neutral-600 hover:tw-text-neutral-500 tw-h-8 tw-w-8 tw-inline-flex tw-items-center tw-justify-center tw-rounded-full tw-absolute tw-inset-0"
                            id="contact-remove-img"><i class="fa fa-remove tw-mt-1"></i></a>
                        <?php } ?>
                        <?php } ?>
                    </div>
                    <div>
                        <h4 class="modal-title tw-mb-0"><?php echo $title; ?></h4>
                        <p class="tw-mb-0">
                            <?php echo get_company_name($customer_id, true); ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">

                        <div id="contact-profile-image" class="form-group<?php if (isset($contact) && !empty($contact->profile_image)) {
    echo ' hide';
} ?>">
                            
                        </div>
                        <?php if (isset($contact)) { ?>
                        <div class="alert alert-warning hide" role="alert" id="contact_proposal_warning">
                            <?php echo _l('proposal_warning_email_change', [_l('contact_lowercase'), _l('contact_lowercase'), _l('contact_lowercase')]); ?>
                            <hr />
                            <a href="#" id="contact_update_proposals_emails" data-original-email=""
                                onclick="update_all_proposal_emails_linked_to_contact(<?php echo $contact->id; ?>); return false;"><?php echo _l('update_proposal_email_yes'); ?></a>
                            <br />
                            <a href="#"
                                onclick="close_modal_manually('#contact'); return false;"><?php echo _l('update_proposal_email_no'); ?></a>
                        </div>
                        <?php } ?>
                        <!-- // For email exist check -->
                        <?php echo form_hidden('contactid', $contactid); ?>
                        <?php $value = (isset($contact) ? $contact->firstname : ''); ?>
                        <?php echo render_input('firstname', 'client_firstname', $value, 'text' , ['readonly' => 'readonly'] ); ?>
                        <?php $value = (isset($contact) ? $contact->lastname : ''); ?>
                        <?php echo render_input('lastname', 'client_lastname', $value, 'text' , ['readonly' => 'readonly'] ); ?>
                        <?php $value = (isset($contact) ? $contact->title : ''); ?>
                        <?php echo render_input('title', 'contact_position', $value, 'text' , ['readonly' => 'readonly'] ); ?>
                        <?php $value = (isset($contact) ? $contact->email : ''); ?>
                        <?php echo render_input('email', 'client_email', $value, 'email' , ['readonly' => 'readonly'] ); ?>
                        <?php
                            if (!isset($contact)) {
                                $value = $calling_code ?: '';
                            } else {
                                $value = empty($contact->phonenumber) ? $calling_code : $contact->phonenumber;
                            }
                        ?>
                        <?php echo render_input('phonenumber', 'client_phonenumber', $value, 'text' , ['readonly' => 'readonly'] ); ?>
                        


                        
                        </div>
                      
                    </div>
                </div>
                <?php hooks()->do_action('after_contact_modal_content_loaded'); ?>
            </div>
            
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php if (!isset($contact)) { ?>
<script>
$(function() {
    // Guess auto email notifications based on the default contact permissios
    var permInputs = $('input[name="permissions[]"]');
    $.each(permInputs, function(i, input) {
        input = $(input);
        if (input.prop('checked') === true) {
            $('#contact_email_notifications [data-perm-id="' + input.val() + '"]').prop('checked',
                true);
        }
    });
});
</script>
<?php } ?>