<?php

defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class Clients extends AdminController
{
  
  
    /* List all clients */
    public function index()
    {
        if (staff_cant('view', 'customers')) {
            if (!have_assigned_customers() && staff_cant('create', 'customers')) {
                access_denied('customers');
            }
        }

        
        
        $data['title']          = _l('clients');




        $data['customer_admins'] = $this->clients_model->get_customers_admin_unique_ids();

        $whereContactsLoggedIn = '';
        if (staff_cant('view', 'customers')) {
            $whereContactsLoggedIn = ' AND userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id=' . get_staff_user_id() . ')';
        }

        $data['contacts_logged_in_today'] = $this->clients_model->get_contacts('', 'last_login LIKE "' . date('Y-m-d') . '%"' . $whereContactsLoggedIn);

        $data['countries'] = $this->clients_model->get_clients_distinct_countries();
        $data['table'] = App_table::find('clients');
        $this->load->view('admin/clients/manage', $data);
    }
   //This shows the data for the table 
    public function table()
    {
        if (staff_cant('view', 'customers')) {
            if (!have_assigned_customers() && staff_cant('create', 'customers')) {
                ajax_access_denied();
            }
        }

        App_table::find('clients')->output();
    }
  
 
 public function deleteColumn() {
   
    // Assuming this method is called via a POST request with columnId and clientId (optional) being passed
    $columnId = $this->input->post('column_id');
    $clientId = $this->input->post('client_id'); // Assuming client_id is passed if needed for additional security check
   
   

    // Attempt to delete the custom column
    $success = $this->clients_model->deleteCustomColumn($columnId, $clientId);

    if ($success) {
        set_alert('success', _l('updated_successfully', _l('client')));
    } else if (!$success){
        // Consider setting an error alert if deletion was not successful
        set_alert('danger', _l('deletion_failed'));
    }

    redirect(admin_url('clients/client/' . $clientId));
}


    public function all_contacts()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('all_contacts');
        }

        if (is_gdpr() && get_option('gdpr_enable_consent_for_contacts') == '1') {
            $this->load->model('gdpr_model');
            $data['consent_purposes'] = $this->gdpr_model->get_consent_purposes();
        }

        $data['title'] = _l('customer_contacts');
        $this->load->view('admin/clients/all_contacts', $data);
    }

    /* Edit client or add new client*/
    public function client($id = '')
    {

        if (staff_cant('view', 'customers')) {
            if ($id != '' && !is_customer_admin($id)) {
                access_denied('customers');
            }
        }

        if ($this->input->post() && !$this->input->is_ajax_request()) {
            if ($id == '') {
                if (staff_cant('create', 'customers')) {
                    access_denied('customers');
                }

                $data = $this->input->post();

                $save_and_add_contact = false;
                if (isset($data['save_and_add_contact'])) {
                    unset($data['save_and_add_contact']);
                    $save_and_add_contact = true;
                }
                $id = $this->clients_model->add($data);
                if (staff_cant('view', 'customers')) {
                    $assign['customer_admins']   = [];
                    $assign['customer_admins'][] = get_staff_user_id();
                    $this->clients_model->assign_admins($assign, $id);
                }
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('client')));
                    if ($save_and_add_contact == false) {
                        redirect(admin_url('clients/client/' . $id));
                    } else {
                        redirect(admin_url('clients/client/' . $id . '?group=contacts&new_contact=true'));
                    }
                }
            } else {
                if (staff_cant('edit', 'customers')) {
                    if (!is_customer_admin($id)) {
                        access_denied('customers');
                    }
                }
                $success = $this->clients_model->update($this->input->post(), $id);
                if ($success == true) {
                    set_alert('success', _l('updated_successfully', _l('client')));
                }
                redirect(admin_url('clients/client/' . $id));
            }
        }

        $group         = !$this->input->get('group') ? 'profile' : $this->input->get('group');
        $data['group'] = $group;

        if ($group != 'contacts' && $contact_id = $this->input->get('contactid')) {
            redirect(admin_url('clients/client/' . $id . '?group=contacts&contactid=' . $contact_id));
        }

        // Customer groups
        $data['groups'] = $this->clients_model->get_groups();

        if ($id == '') {
            $client_id = get_related_client_id_for_staff();
redirect(admin_url('clients/client/' . $client_id));

        } else {
            $client                = $this->clients_model->get($id);
            $data['customer_tabs'] = get_customer_profile_tabs($id);

            if (!$client) {
                show_404();
            }

            $data['contacts'] = $this->clients_model->get_contacts($id);
            $data['tab']      = isset($data['customer_tabs'][$group]) ? $data['customer_tabs'][$group] : null;

            if (!$data['tab']) {
                show_404();
            }

            // Fetch data based on groups
            if ($group == 'profile') {
                $data['customer_groups'] = $this->clients_model->get_customer_groups($id);
                $data['customer_admins'] = $this->clients_model->get_admins($id);
            } elseif ($group == 'attachments') {
                $data['attachments'] = get_all_customer_attachments($id);
            } elseif ($group == 'vault') {
                $data['vault_entries'] = hooks()->apply_filters('check_vault_entries_visibility', $this->clients_model->get_vault_entries($id));

                if ($data['vault_entries'] === -1) {
                    $data['vault_entries'] = [];
                }
            } elseif ($group == 'notes') {
                $data['user_notes'] = $this->misc_model->get_notes($id, 'customer');
            } elseif ($group == 'statement') {
                if (staff_cant('view', 'invoices') && staff_cant('view', 'payments')) {
                    set_alert('danger', _l('access_denied'));
                    redirect(admin_url('clients/client/' . $id));
                }

                $data = array_merge($data, prepare_mail_preview_data('customer_statement', $id));
            } elseif ($group == 'map') {
                if (get_option('google_api_key') != '' && !empty($client->latitude) && !empty($client->longitude)) {
                    $this->app_scripts->add('map-js', base_url($this->app_scripts->core_file('assets/js', 'map.js')) . '?v=' . $this->app_css->core_version());

                    $this->app_scripts->add('google-maps-api-js', [
                        'path'       => 'https://maps.googleapis.com/maps/api/js?key=' . get_option('google_api_key') . '&callback=initMap',
                        'attributes' => [
                            'async',
                            'defer',
                            'latitude'       => "$client->latitude",
                            'longitude'      => "$client->longitude",
                            'mapMarkerTitle' => "$client->company",
                        ],
                        ]);
                }
            }

            $data['staff'] = $this->staff_model->get('', ['active' => 1]);

            $data['client'] = $client;
            $title          = $client->company;

            // Get all active staff members (used to add reminder)
            $data['members'] = $data['staff'];

            if (!empty($data['client']->company)) {
                // Check if is realy empty client company so we can set this field to empty
                // The query where fetch the client auto populate firstname and lastname if company is empty
                if (is_empty_customer_company($data['client']->userid)) {
                    $data['client']->company = '';
                }
            }
        }

        

        if ($id != '') {
            $customer_currency = $data['client']->default_currency;

            

            if (is_array($customer_currency)) {
                $customer_currency = (object) $customer_currency;
            }

            $data['customer_currency'] = $customer_currency;

            $slug_zip_folder = (
                $client->company != ''
                ? $client->company
                : get_contact_full_name(get_primary_contact_user_id($client->userid))
            );

            $data['zip_in_folder'] = slug_it($slug_zip_folder);
        }

        $data['bodyclass'] = 'customer-profile dynamic-create-groups';
        $data['title']     = $title;
      
     // Fetch the HTML content from the function
    $html = $this->generate_pdf_html(); // Assuming 'generate_pdf_html' is the function generating the HTML content

    // Pass the HTML content to the view file
    $data['html_content'] = $html;
      
     $pdfUrl = $this->contractPage(); // Fetch the contract URL or message

    // Determine if the response is a URL or a message
    if (strpos($pdfUrl, 'http') === 0) {
        // It's a URL
        $data['pdf_url'] = $pdfUrl;
        $data['pdf_message'] = ''; // No message needed
    } else {
        // It's a message
        $data['pdf_url'] = ''; // No URL available
        $data['pdf_message'] = $pdfUrl; // Store the message
    }

        $this->load->view('admin/clients/client', $data);
      
    }
  
  public function contractPage() {
    $contactId = get_staff_user_id();
    $userId = $this->getUserIdFromContact($contactId);
    $clientName = $this->getCompanyNameByUserId($userId);

    // Initialize S3 client
    $s3 = new S3Client([
        'version' => 'latest',
        'region' => 'eu-west-1',
        'credentials' => [
            'key' => 'AKIATLFNSPEQL5BWGCD2',
            'secret' => '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',
        ],
    ]);

    $bucket = 'optimumway';
    $prefix = 'merchants/' . $clientName . '/view/contract/';

    try {
        // List objects in the bucket to find the PDF file
        $objects = $s3->listObjects([
            'Bucket' => $bucket,
            'Prefix' => $prefix,
        ]);

        // Check if contents are available
        if (!empty($objects['Contents'])) {
            foreach ($objects['Contents'] as $object) {
                $key = $object['Key'];
                
                // Check if the object key matches the desired criteria for the PDF file
                if (strpos($key, 'Contract-') !== false && pathinfo($key, PATHINFO_EXTENSION) === 'pdf') {
                    // Generate a pre-signed URL for downloading the file
                    $cmd = $s3->getCommand('GetObject', [
                        'Bucket' => $bucket,
                        'Key'    => $key
                    ]);
                    $request = $s3->createPresignedRequest($cmd, '+20 minutes'); // URL expires in 20 minutes
                    $url = (string) $request->getUri();
                    
                    return $url; // Return the pre-signed URL of the PDF file
                }
            }
        }
    } catch (AwsException $e) {
        // Log the error for debugging purposes
        error_log("Error retrieving PDF file: " . $e->getMessage());
        return "Error retrieving contract. Please try again later.";
    }

    return "Contract not available at this time."; // Return this message if no PDF is found or list is empty
}

  
  public function contract() {
    // Get the URL of the PDF file
    $pdf_url = $this->contractPage();

    // Pass the PDF URL to the view
    $data['pdf_url'] = $pdf_url;

    // Load the view
    $this->load->view('admin/clients/groups/contract', $data);
}

    public function export($contact_id)
    {
        if (is_admin()) {
            $this->load->library('gdpr/gdpr_contact');
            $this->gdpr_contact->export($contact_id);
        }
    }

    // Used to give a tip to the user if the company exists when new company is created
    public function check_duplicate_customer_name()
    {
        if (staff_can('create',  'customers')) {
            $companyName = trim($this->input->post('company'));
            $response    = [
                'exists'  => (bool) total_rows(db_prefix() . 'clients', ['company' => $companyName]) > 0,
                'message' => _l('company_exists_info', '<b>' . $companyName . '</b>'),
            ];
            echo json_encode($response);
        }
    }

    public function save_longitude_and_latitude($client_id)
    {
        if (staff_cant('edit', 'customers')) {
            if (!is_customer_admin($client_id)) {
                ajax_access_denied();
            }
        }

        $this->db->where('userid', $client_id);
        $this->db->update(db_prefix() . 'clients', [
            'longitude' => $this->input->post('longitude'),
            'latitude'  => $this->input->post('latitude'),
        ]);
        if ($this->db->affected_rows() > 0) {
            echo 'success';
        } else {
            echo 'false';
        }
    }

    public function form_contact($customer_id, $contact_id = '')
    {
        if (staff_cant('view', 'customers')) {
            if (!is_customer_admin($customer_id)) {
                echo _l('access_denied');
                die;
            }
        }
        $data['customer_id'] = $customer_id;
        $data['contactid']   = $contact_id;

        if (is_automatic_calling_codes_enabled()) {
            $clientCountryId = $this->db->select('country')
                ->where('userid', $customer_id)
                ->get('clients')->row()->country ?? null;

            $clientCountry = get_country($clientCountryId);
            $callingCode   = $clientCountry ? '+' . ltrim($clientCountry->calling_code, '+') : null;
        } else {
            $callingCode = null;
        }

        if ($this->input->post()) {
            $data             = $this->input->post();
            $data['password'] = $this->input->post('password', false);

            if ($callingCode && !empty($data['phonenumber']) && $data['phonenumber'] == $callingCode) {
                $data['phonenumber'] = '';
            }

            unset($data['contactid']);

            if ($contact_id == '') {
                if (staff_cant('create', 'customers')) {
                    if (!is_customer_admin($customer_id)) {
                        header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad error');
                        echo json_encode([
                            'success' => false,
                            'message' => _l('access_denied'),
                        ]);
                        die;
                    }
                }
                $id      = $this->clients_model->add_contact($data, $customer_id);
                $message = '';
                $success = false;
                if ($id) {
                    handle_contact_profile_image_upload($id);
                    $success = true;
                    $message = _l('added_successfully', _l('contact'));
                }
                echo json_encode([
                    'success'             => $success,
                    'message'             => $message,
                    'has_primary_contact' => (total_rows(db_prefix() . 'contacts', ['userid' => $customer_id, 'is_primary' => 1]) > 0 ? true : false),
                    'is_individual'       => is_empty_customer_company($customer_id) && total_rows(db_prefix() . 'contacts', ['userid' => $customer_id]) == 1,
                ]);
                die;
            }
            if (staff_cant('edit', 'customers')) {
                if (!is_customer_admin($customer_id)) {
                    header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad error');
                    echo json_encode([
                            'success' => false,
                            'message' => _l('access_denied'),
                        ]);
                    die;
                }
            }
            $original_contact = $this->clients_model->get_contact($contact_id);
            $success          = $this->clients_model->update_contact($data, $contact_id);
            $message          = '';
            $proposal_warning = false;
            $original_email   = '';
            $updated          = false;
            if (is_array($success)) {
                if (isset($success['set_password_email_sent'])) {
                    $message = _l('set_password_email_sent_to_client');
                } elseif (isset($success['set_password_email_sent_and_profile_updated'])) {
                    $updated = true;
                    $message = _l('set_password_email_sent_to_client_and_profile_updated');
                }
            } else {
                if ($success == true) {
                    $updated = true;
                    $message = _l('updated_successfully', _l('contact'));
                }
            }
            if (handle_contact_profile_image_upload($contact_id) && !$updated) {
                $message = _l('updated_successfully', _l('contact'));
                $success = true;
            }
            if ($updated == true) {
                $contact = $this->clients_model->get_contact($contact_id);
                if (total_rows(db_prefix() . 'proposals', [
                        'rel_type' => 'customer',
                        'rel_id' => $contact->userid,
                        'email' => $original_contact->email,
                    ]) > 0 && ($original_contact->email != $contact->email)) {
                    $proposal_warning = true;
                    $original_email   = $original_contact->email;
                }
            }
            echo json_encode([
                    'success'             => $success,
                    'proposal_warning'    => $proposal_warning,
                    'message'             => $message,
                    'original_email'      => $original_email,
                    'has_primary_contact' => (total_rows(db_prefix() . 'contacts', ['userid' => $customer_id, 'is_primary' => 1]) > 0 ? true : false),
                ]);
            die;
        }


        $data['calling_code'] = $callingCode;

        if ($contact_id == '') {
            $title = _l('add_new', _l('contact_lowercase'));
        } else {
            $data['contact'] = $this->clients_model->get_contact($contact_id);

            if (!$data['contact']) {
                header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad error');
                echo json_encode([
                    'success' => false,
                    'message' => 'Contact Not Found',
                ]);
                die;
            }
            $title = $data['contact']->firstname . ' ' . $data['contact']->lastname;
        }

        $data['customer_permissions'] = get_contact_permissions();
        $data['title']                = $title;
        $this->load->view('admin/clients/modals/contact', $data);
    }

    public function confirm_registration($client_id)
    {
        if (!is_admin()) {
            access_denied('Customer Confirm Registration, ID: ' . $client_id);
        }
        $this->clients_model->confirm_registration($client_id);
        set_alert('success', _l('customer_registration_successfully_confirmed'));
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function update_file_share_visibility()
    {
        if ($this->input->post()) {
            $file_id           = $this->input->post('file_id');
            $share_contacts_id = [];

            if ($this->input->post('share_contacts_id')) {
                $share_contacts_id = $this->input->post('share_contacts_id');
            }

            $this->db->where('file_id', $file_id);
            $this->db->delete(db_prefix() . 'shared_customer_files');

            foreach ($share_contacts_id as $share_contact_id) {
                $this->db->insert(db_prefix() . 'shared_customer_files', [
                    'file_id'    => $file_id,
                    'contact_id' => $share_contact_id,
                ]);
            }
        }
    }

    public function delete_contact_profile_image($contact_id)
    {
        $this->clients_model->delete_contact_profile_image($contact_id);
    }

    public function mark_as_active($id)
    {
        $this->db->where('userid', $id);
        $this->db->update(db_prefix() . 'clients', [
            'active' => 1,
        ]);
        redirect(admin_url('clients/client/' . $id));
    }

    public function consents($id)
    {
        if (staff_cant('view', 'customers')) {
            if (!is_customer_admin(get_user_id_by_contact_id($id))) {
                echo _l('access_denied');
                die;
            }
        }

        $this->load->model('gdpr_model');
        $data['purposes']   = $this->gdpr_model->get_consent_purposes($id, 'contact');
        $data['consents']   = $this->gdpr_model->get_consents(['contact_id' => $id]);
        $data['contact_id'] = $id;
        $this->load->view('admin/gdpr/contact_consent', $data);
    }

    public function update_all_proposal_emails_linked_to_customer($contact_id)
    {
        $success = false;
        $email   = '';
        if ($this->input->post('update')) {
            $this->load->model('proposals_model');

            $this->db->select('email,userid');
            $this->db->where('id', $contact_id);
            $contact = $this->db->get(db_prefix() . 'contacts')->row();

            $proposals = $this->proposals_model->get('', [
                'rel_type' => 'customer',
                'rel_id'   => $contact->userid,
                'email'    => $this->input->post('original_email'),
            ]);
            $affected_rows = 0;

            foreach ($proposals as $proposal) {
                $this->db->where('id', $proposal['id']);
                $this->db->update(db_prefix() . 'proposals', [
                    'email' => $contact->email,
                ]);
                if ($this->db->affected_rows() > 0) {
                    $affected_rows++;
                }
            }

            if ($affected_rows > 0) {
                $success = true;
            }
        }
        echo json_encode([
            'success' => $success,
            'message' => _l('proposals_emails_updated', [
                _l('contact_lowercase'),
                $contact->email,
            ]),
        ]);
    }

    public function assign_admins($id)
    {
        if (staff_cant('create', 'customers') && staff_cant('edit', 'customers')) {
            access_denied('customers');
        }
        $success = $this->clients_model->assign_admins($this->input->post(), $id);
        if ($success == true) {
            set_alert('success', _l('updated_successfully', _l('client')));
        }

        redirect(admin_url('clients/client/' . $id . '?tab=customer_admins'));
    }

    public function delete_customer_admin($customer_id, $staff_id)
    {
        if (staff_cant('create', 'customers') && staff_cant('edit', 'customers')) {
            access_denied('customers');
        }

        $this->db->where('customer_id', $customer_id);
        $this->db->where('staff_id', $staff_id);
        $this->db->delete(db_prefix() . 'customer_admins');
        redirect(admin_url('clients/client/' . $customer_id) . '?tab=customer_admins');
    }

    public function delete_contact($customer_id, $id)
    {
        if (staff_cant('delete', 'customers')) {
            if (!is_customer_admin($customer_id)) {
                access_denied('customers');
            }
        }
        $contact      = $this->clients_model->get_contact($id);
        $hasProposals = false;
        if ($contact && is_gdpr()) {
            if (total_rows(db_prefix() . 'proposals', ['email' => $contact->email]) > 0) {
                $hasProposals = true;
            }
        }

        $this->clients_model->delete_contact($id);
        if ($hasProposals) {
            $this->session->set_flashdata('gdpr_delete_warning', true);
        }
        redirect(admin_url('clients/client/' . $customer_id . '?group=contacts'));
    }

    public function contacts($client_id)
    {
        $this->app->get_table_data('contacts', [
            'client_id' => $client_id,
        ]);
    }

    public function upload_attachment($id)
    {
        handle_client_attachments_upload($id);
    }

    public function add_external_attachment()
    {
        if ($this->input->post()) {
            $this->misc_model->add_attachment_to_database($this->input->post('clientid'), 'customer', $this->input->post('files'), $this->input->post('external'));
        }
    }

    public function delete_attachment($customer_id, $id)
    {
        if (staff_can('delete',  'customers') || is_customer_admin($customer_id)) {
            $this->clients_model->delete_attachment($id);
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    /* Delete client */
    public function delete($id)
    {
        if (staff_cant('delete', 'customers')) {
            access_denied('customers');
        }
        if (!$id) {
            redirect(admin_url('clients'));
        }
        $response = $this->clients_model->delete($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('customer_delete_transactions_warning', _l('invoices') . ', ' . _l('estimates') . ', ' . _l('credit_notes')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('client')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('client_lowercase')));
        }
        redirect(admin_url('clients'));
    }

    /* Staff can login as client */
    public function login_as_client($id)
    {
        if (is_admin()) {
            login_as_client($id);
        }
        hooks()->do_action('after_contact_login');
        redirect(site_url());
    }

    public function get_customer_billing_and_shipping_details($id)
    {
        echo json_encode($this->clients_model->get_customer_billing_and_shipping_details($id));
    }

    /* Change client status / active / inactive */
    public function change_contact_status($id, $status)
    {
        if (staff_can('edit',  'customers') || is_customer_admin(get_user_id_by_contact_id($id))) {
            if ($this->input->is_ajax_request()) {
                $this->clients_model->change_contact_status($id, $status);
            }
        }
    }

    /* Change client status / active / inactive */
    public function change_client_status($id, $status)
    {
        if ($this->input->is_ajax_request()) {
            $this->clients_model->change_client_status($id, $status);
        }
    }

    /* Zip function for credit notes */
    public function zip_credit_notes($id)
    {
        $has_permission_view = staff_can('view',  'credit_notes');

        if (!$has_permission_view && staff_cant('view_own', 'credit_notes')) {
            access_denied('Zip Customer Credit Notes');
        }

        if ($this->input->post()) {
            $this->load->library('app_bulk_pdf_export', [
                'export_type'       => 'credit_notes',
                'status'            => $this->input->post('credit_note_zip_status'),
                'date_from'         => $this->input->post('zip-from'),
                'date_to'           => $this->input->post('zip-to'),
                'redirect_on_error' => admin_url('clients/client/' . $id . '?group=credit_notes'),
            ]);

            $this->app_bulk_pdf_export->set_client_id($id);
            $this->app_bulk_pdf_export->in_folder($this->input->post('file_name'));
            $this->app_bulk_pdf_export->export();
        }
    }

    public function zip_invoices($id)
    {
        $has_permission_view = staff_can('view',  'invoices');
        if (!$has_permission_view && staff_cant('view_own', 'invoices')
            && get_option('allow_staff_view_invoices_assigned') == '0') {
            access_denied('Zip Customer Invoices');
        }

        if ($this->input->post()) {
            $this->load->library('app_bulk_pdf_export', [
                'export_type'       => 'invoices',
                'status'            => $this->input->post('invoice_zip_status'),
                'date_from'         => $this->input->post('zip-from'),
                'date_to'           => $this->input->post('zip-to'),
                'redirect_on_error' => admin_url('clients/client/' . $id . '?group=invoices'),
            ]);

            $this->app_bulk_pdf_export->set_client_id($id);
            $this->app_bulk_pdf_export->in_folder($this->input->post('file_name'));
            $this->app_bulk_pdf_export->export();
        }
    }

    /* Since version 1.0.2 zip client estimates */
    public function zip_estimates($id)
    {
        $has_permission_view = staff_can('view',  'estimates');
        if (!$has_permission_view && staff_cant('view_own', 'estimates')
            && get_option('allow_staff_view_estimates_assigned') == '0') {
            access_denied('Zip Customer Estimates');
        }

        if ($this->input->post()) {
            $this->load->library('app_bulk_pdf_export', [
                'export_type'       => 'estimates',
                'status'            => $this->input->post('estimate_zip_status'),
                'date_from'         => $this->input->post('zip-from'),
                'date_to'           => $this->input->post('zip-to'),
                'redirect_on_error' => admin_url('clients/client/' . $id . '?group=estimates'),
            ]);

            $this->app_bulk_pdf_export->set_client_id($id);
            $this->app_bulk_pdf_export->in_folder($this->input->post('file_name'));
            $this->app_bulk_pdf_export->export();
        }
    }

    public function zip_payments($id)
    {
        $has_permission_view = staff_can('view',  'payments');

        if (!$has_permission_view && staff_cant('view_own', 'invoices')
            && get_option('allow_staff_view_invoices_assigned') == '0') {
            access_denied('Zip Customer Payments');
        }

        $this->load->library('app_bulk_pdf_export', [
                'export_type'       => 'payments',
                'payment_mode'      => $this->input->post('paymentmode'),
                'date_from'         => $this->input->post('zip-from'),
                'date_to'           => $this->input->post('zip-to'),
                'redirect_on_error' => admin_url('clients/client/' . $id . '?group=payments'),
            ]);

        $this->app_bulk_pdf_export->set_client_id($id);
        $this->app_bulk_pdf_export->set_client_id_column(db_prefix() . 'clients.userid');
        $this->app_bulk_pdf_export->in_folder($this->input->post('file_name'));
        $this->app_bulk_pdf_export->export();
    }

    public function import()
    {
        if (staff_cant('create', 'customers')) {
            access_denied('customers');
        }

        $dbFields = $this->db->list_fields(db_prefix() . 'contacts');
        foreach ($dbFields as $key => $contactField) {
            if ($contactField == 'phonenumber') {
                $dbFields[$key] = 'contact_phonenumber';
            }
        }

        $dbFields = array_merge($dbFields, $this->db->list_fields(db_prefix() . 'clients'));

        $this->load->library('import/import_customers', [], 'import');

        $this->import->setDatabaseFields($dbFields)
                     ->setCustomFields(get_custom_fields('customers'));

        if ($this->input->post('download_sample') === 'true') {
            $this->import->downloadSample();
        }

        if ($this->input->post()
            && isset($_FILES['file_csv']['name']) && $_FILES['file_csv']['name'] != '') {
            $this->import->setSimulation($this->input->post('simulate'))
                          ->setTemporaryFileLocation($_FILES['file_csv']['tmp_name'])
                          ->setFilename($_FILES['file_csv']['name'])
                          ->perform();


            $data['total_rows_post'] = $this->import->totalRows();

            if (!$this->import->isSimulation()) {
                set_alert('success', _l('import_total_imported', $this->import->totalImported()));
            }
        }

        $data['groups']    = $this->clients_model->get_groups();
        $data['title']     = _l('import');
        $data['bodyclass'] = 'dynamic-create-groups';
        $this->load->view('admin/clients/import', $data);
    }

    public function groups()
    {
        if (!is_admin()) {
            access_denied('Customer Groups');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('customers_groups');
        }
        $data['title'] = _l('customer_groups');
        $this->load->view('admin/clients/groups_manage', $data);
    }

    public function group()
    {
        if (!is_admin() && get_option('staff_members_create_inline_customer_groups') == '0') {
            access_denied('Customer Groups');
        }

        if ($this->input->is_ajax_request()) {
            $data = $this->input->post();
            if ($data['id'] == '') {
                $id      = $this->clients_model->add_group($data);
                $message = $id ? _l('added_successfully', _l('customer_group')) : '';
                echo json_encode([
                    'success' => $id ? true : false,
                    'message' => $message,
                    'id'      => $id,
                    'name'    => $data['name'],
                ]);
            } else {
                $success = $this->clients_model->edit_group($data);
                $message = '';
                if ($success == true) {
                    $message = _l('updated_successfully', _l('customer_group'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            }
        }
    }

    public function delete_group($id)
    {
        if (!is_admin()) {
            access_denied('Delete Customer Group');
        }
        if (!$id) {
            redirect(admin_url('clients/groups'));
        }
        $response = $this->clients_model->delete_group($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('customer_group')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('customer_group_lowercase')));
        }
        redirect(admin_url('clients/groups'));
    }

    public function bulk_action()
    {
        hooks()->do_action('before_do_bulk_action_for_customers');
        $total_deleted = 0;
        if ($this->input->post()) {
            $ids    = $this->input->post('ids');
            $groups = $this->input->post('groups');

            if (is_array($ids)) {
                foreach ($ids as $id) {
                    if ($this->input->post('mass_delete')) {
                        if ($this->clients_model->delete($id)) {
                            $total_deleted++;
                        }
                    } else {
                        if (!is_array($groups)) {
                            $groups = false;
                        }
                        $this->client_groups_model->sync_customer_groups($id, $groups);
                    }
                }
            }
        }

        if ($this->input->post('mass_delete')) {
            set_alert('success', _l('total_clients_deleted', $total_deleted));
        }
    }

    public function vault_entry_create($customer_id)
    {
        $data = $this->input->post();

        if (isset($data['fakeusernameremembered'])) {
            unset($data['fakeusernameremembered']);
        }

        if (isset($data['fakepasswordremembered'])) {
            unset($data['fakepasswordremembered']);
        }

        unset($data['id']);
        $data['creator']      = get_staff_user_id();
        $data['creator_name'] = get_staff_full_name($data['creator']);
        $data['description']  = nl2br($data['description']);
        $data['password']     = $this->encryption->encrypt($this->input->post('password', false));

        if (empty($data['port'])) {
            unset($data['port']);
        }

        $this->clients_model->vault_entry_create($data, $customer_id);
        set_alert('success', _l('added_successfully', _l('vault_entry')));
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function vault_entry_update($entry_id)
    {
        $entry = $this->clients_model->get_vault_entry($entry_id);

        if ($entry->creator == get_staff_user_id() || is_admin()) {
            $data = $this->input->post();

            if (isset($data['fakeusernameremembered'])) {
                unset($data['fakeusernameremembered']);
            }
            if (isset($data['fakepasswordremembered'])) {
                unset($data['fakepasswordremembered']);
            }

            $data['last_updated_from'] = get_staff_full_name(get_staff_user_id());
            $data['description']       = nl2br($data['description']);

            if (!empty($data['password'])) {
                $data['password'] = $this->encryption->encrypt($this->input->post('password', false));
            } else {
                unset($data['password']);
            }

            if (empty($data['port'])) {
                unset($data['port']);
            }

            $this->clients_model->vault_entry_update($entry_id, $data);
            set_alert('success', _l('updated_successfully', _l('vault_entry')));
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function vault_entry_delete($id)
    {
        $entry = $this->clients_model->get_vault_entry($id);
        if ($entry->creator == get_staff_user_id() || is_admin()) {
            $this->clients_model->vault_entry_delete($id);
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function vault_encrypt_password()
    {
        $id            = $this->input->post('id');
        $user_password = $this->input->post('user_password', false);
        $user          = $this->staff_model->get(get_staff_user_id());

        if (!app_hasher()->CheckPassword($user_password, $user->password)) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['error_msg' => _l('vault_password_user_not_correct')]);
            die;
        }

        $vault    = $this->clients_model->get_vault_entry($id);
        $password = $this->encryption->decrypt($vault->password);

        $password = html_escape($password);

        // Failed to decrypt
        if (!$password) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad error');
            echo json_encode(['error_msg' => _l('failed_to_decrypt_password')]);
            die;
        }

        echo json_encode(['password' => $password]);
    }

    public function get_vault_entry($id)
    {
        $entry = $this->clients_model->get_vault_entry($id);
        unset($entry->password);
        $entry->description = clear_textarea_breaks($entry->description);
        echo json_encode($entry);
    }

    public function statement_pdf()
    {
        $customer_id = $this->input->get('customer_id');

        if (staff_cant('view', 'invoices') && staff_cant('view', 'payments')) {
            set_alert('danger', _l('access_denied'));
            redirect(admin_url('clients/client/' . $customer_id));
        }

        $from = $this->input->get('from');
        $to   = $this->input->get('to');

        $data['statement'] = $this->clients_model->get_statement($customer_id, to_sql_date($from), to_sql_date($to));

        try {
            $pdf = statement_pdf($data['statement']);
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        $type = 'D';
        if ($this->input->get('print')) {
            $type = 'I';
        }

        $pdf->Output(slug_it(_l('customer_statement') . '-' . $data['statement']['client']->company) . '.pdf', $type);
    }

    public function send_statement()
    {
        $customer_id = $this->input->get('customer_id');

        if (staff_cant('view', 'invoices') && staff_cant('view', 'payments')) {
            set_alert('danger', _l('access_denied'));
            redirect(admin_url('clients/client/' . $customer_id));
        }

        $from = $this->input->get('from');
        $to   = $this->input->get('to');

        $send_to = $this->input->post('send_to');
        $cc      = $this->input->post('cc');

        $success = $this->clients_model->send_statement_to_email($customer_id, $send_to, $from, $to, $cc);
        // In case client use another language
        load_admin_language();
        if ($success) {
            set_alert('success', _l('statement_sent_to_client_success'));
        } else {
            set_alert('danger', _l('statement_sent_to_client_fail'));
        }

        redirect(admin_url('clients/client/' . $customer_id . '?group=statement'));
    }

    public function statement()
    {
        if (staff_cant('view', 'invoices') && staff_cant('view', 'payments')) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad error');
            echo _l('access_denied');
            die;
        }

        $customer_id = $this->input->get('customer_id');
        $from        = $this->input->get('from');
        $to          = $this->input->get('to');

        $data['statement'] = $this->clients_model->get_statement($customer_id, to_sql_date($from), to_sql_date($to));

        $data['from'] = $from;
        $data['to']   = $to;

        $viewData['html'] = $this->load->view('admin/clients/groups/_statement', $data, true);

        echo json_encode($viewData);
    }
  
  public function getUserIdFromContact($id) {
        $this->db->select('userid');
        $this->db->from('tblcontacts');
        // Assuming there is a relation where 'id' from 'tblcontacts' matches the staff ID
        $this->db->where('id', $id);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row()->userid;
        } else {
            return null;
        }
    }
  
  public function getCompanyNameByUserId($userId) {
    $this->db->select('company');
    $this->db->from('tblclients');
    $this->db->where('userid', $userId);
    $query = $this->db->get();

    if ($query->num_rows() > 0) {
        return $query->row()->company;
    } else {
        return null;
    }
}

  private function generate_pdf_html()
{
    
    if (!function_exists('convert_null_values')) {
    function convert_null_values($data) {
    // Check if data is iterable before using foreach
    if (is_array($data) || is_object($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = isset($value) ? htmlspecialchars($value) : 'N/A';
        }
        return $data;
    } else {
        // Log unexpected data types for debugging
        error_log('Expected array or object, received ' . gettype($data));
        return []; // Return an empty array or other appropriate default value
    }
}
}

    
    $s3 = new S3Client([
    'version' => 'latest',
    'region' => 'eu-west-1', // Replace 'your-region' with your AWS region, e.g., 'us-east-1'
    'credentials' => [
        'key' => 'AKIATLFNSPEQL5BWGCD2',
        'secret' => '21pBpmKSvFsbWm98NZxl/nqU9cPtO7C8i9g1VKpn',
    ],
]);
    
    $bucketName = 'optimumway';
    $contactId = get_staff_user_id();
        $userId = $this->getUserIdFromContact($contactId);
        $clientName = $this->getCompanyNameByUserId($userId);
    // Download the signature image from S3
$signatureKey = 'merchants/' . $clientName . '/view/application/signature/principal_signature1.png';
$signatureImage = $s3->getObject([
    'Bucket' => $bucketName,
    'Key' => $signatureKey,
]);

// Get the signature image content
$signatureContent = $signatureImage['Body'];

// Convert the signature image content to base64
$signatureData = base64_encode($signatureContent);
    
    
        $signatureKey = 'merchants/' . $clientName . '/view/application/signature/principal_signature2.png';
$blankKey = 'images/blank.png';
    
    // Fetch and encode the first signature
$signatureData1 = $this->getBase64ImageData($s3, $bucketName, 'merchants/' . $clientName . '/view/application/signature/principal_signature1.png');

// Fetch and encode the second signature
$signatureData2 = $this->getBase64ImageData($s3, $bucketName, 'merchants/' . $clientName . '/view/application/signature/principal_signature2.png');
    
    // Get the client ID
        $clientID = get_merchant_userid(); // Assuming this function exists in your controller

        // Call the model method to fetch form data
        $formData = $this->clients_model->getFormData($clientID);
    
    $formData = convert_null_values($formData);


// Get the signature image data, or use the blank image if the signature doesn't exist
// Correctly calling the private method within the same class
    $signatureData = $this->getBase64ImageData($s3, $bucketName, $signatureKey);
    $blankImageData = $this->getBase64ImageData($s3, $bucketName, $blankKey);
    
    // Build and return the HTML content for the PDF
    $html = '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/><title>file_1689270128563</title><meta name="author" content="Len"/>
  <style type="text/css">* {
    margin: 0;
    padding: 0;
    text-indent: 0;
    box-sizing: border-box;
  -moz-box-sizing: border-box;
}
    
    body {
  margin: 0;
  padding: 0;
  background-color: #FAFAFA;
  font: 12pt "Tahoma";
}   
@page {
            size: A4;
            margin: 0;
        }
.page {
  width: 19cm;
  min-height: 29.7cm;
  padding: 0.5cm;
  margin: 1cm auto;
  display: none;
}

/* Show only the first page by default */
        .page:first-child {
            display: block;
        }
        
        /* Style for pagination buttons */
        .pagination {
            text-align: center;
            margin-top: 20px;
            display: flex;
            align-items: center;
            justify-content: center;

        }
        .pagination button {
            padding: 8px 16px;
            margin: 0 5px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: #fff;
        }
        .pagination button:hover {
            background-color: #0056b3;
        }
        
        /* Style for active button */
        .pagination button.active {
            background-color: #0056b3;
            color: #fff;
        }

.subpage {
  padding: 1cm;
  border: 5px red solid;
  height: 256mm;
  outline: 2cm #FFEAEA solid;
}


    
    @page {
  size: A4;
  margin: 0;
}

@media print {
  .page {
    margin: 0;
    border: initial;
    border-radius: initial;
    width: initial;
    min-height: initial;
    box-shadow: initial;
    background: initial;
    page-break-after: always;
  }
}

.s1 {
    color: #FFF;
    font-family: Arial, sans-serif;
    font-style: normal;
    font-weight: bold;
    text-decoration: none;
    font-size: 8pt;
}

.s2 {
    color: black;
    font-family: Arial, sans-serif;
    font-style: normal;
    font-weight: bold;
    text-decoration: none;
    font-size: 8pt;
}

.s3 {
    color: #FFF;
    font-family: Arial, sans-serif;
    font-style: normal;
    font-weight: bold;
    text-decoration: none;
    font-size: 6pt;
}

.s5 {
    color: #F00;
    font-family: Arial, sans-serif;
    font-style: normal;
    font-weight: normal;
    text-decoration: none;
    font-size: 8pt;
}

.s6 {
    color: black;
    font-family: Calibri, sans-serif;
    font-style: normal;
    font-weight: normal;
    text-decoration: none;
    font-size: 8pt;
}

h1 {
    color: #F00;
    font-family: Arial, sans-serif;
    font-style: normal;
    font-weight: bold;
    text-decoration: none;
    font-size: 8pt;
}

.s7 {
    color: #0A0;
    font-family: Arial, sans-serif;
    font-style: normal;
    font-weight: bold;
    text-decoration: none;
    font-size: 8pt;
}

.s8 {
    color: black;
    font-family: Arial, sans-serif;
    font-style: normal;
    font-weight: bold;
    text-decoration: none;
    font-size: 8pt;
}

p {
    color: black;
    font-family: Arial, sans-serif;
    font-style: normal;
    font-weight: bold;
    text-decoration: none;
    font-size: 8pt;
    margin: 0pt;
}

.s9 {
    color: #0F0;
    font-family: Arial, sans-serif;
    font-style: normal;
    font-weight: bold;
    text-decoration: none;
    font-size: 8pt;
}

.s10 {
    color: black;
    font-family: Arial, sans-serif;
    font-style: normal;
    font-weight: normal;
    text-decoration: none;
    font-size: 8pt;
    vertical-align: 1pt;
}

.s11 {
    color: #0F0;
    font-family: Arial, sans-serif;
    font-style: normal;
    font-weight: normal;
    text-decoration: none;
    font-size: 8pt;
}

table,
tbody {
    vertical-align: top;
    overflow: visible;
    
}

</style>
  
  </head><body>
  <div class="book">
  <div class="page">
  <img class="logo" src="/MMS/modules/applications/views/logo2.png?v=<?php echo time(); ?>" alt="Logo"><br><br>
  <table style="border-collapse:collapse; width: 100%;" cellspacing="0">
    <tbody>
        <tr style="height:14pt">
            <td style="width: 50%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="9" bgcolor="#15213a">
                <p class="s1" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">COMPANY
                    PROFILE</p>
            </td>
        </tr>
        <tr style="height:28pt">
            <td style="width: 50%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="5">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Merchant
                    Name (DBA or Trade Name) <span style=" color: #0029F5;"><br>' . (isset($formData['name']) ? htmlspecialchars($formData['name']) : 'N/A') . '</span></p>
            </td>
            <td style="width:278pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="4">
                <p class="s2" style="padding-top: 1pt;padding-left: 6pt;text-indent: 0pt;text-align: left;">Corporate/
                    Legal Name <span style=" color: #0029F5;"><br>' . (isset($formData['corporate_name']) ? htmlspecialchars($formData['corporate_name']) : 'N/A') . '</span></p>
            </td>
        </tr>
        <tr style="height:28pt">
            <td style="width: 50%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="5">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Location
                    Address <span style=" color: #0029F5;"><br>' . (isset($formData['address']) ? htmlspecialchars($formData['address']) : 'N/A') . '</span></p>
            </td>
            <td style="width: 50%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="4">
                <p class="s2" style="padding-top: 1pt;padding-left: 6pt;text-indent: 0pt;text-align: left;">Corporate/
                    Billing Address  <span style=" color: #0029F5;"><br>' . (isset($formData['corporate_address']) ? htmlspecialchars($formData['corporate_address']) : 'N/A') . '</span></p>
            </td>
        </tr>
        <tr style="height:38pt">
    <td style="width: 20%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
        colspan="3">
        <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">City, State
        <span style=" color: #0029F5;"><br>' . (isset($formData['city']) ? htmlspecialchars($formData['city']) : 'N/A') . '</span></p>
    </td>
    <td
        style="width: 16%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt" 
        colspan="1">
        <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Zip/Postal
            Code<span style=" color: #0029F5;"><br>' . (isset($formData['zipcode']) ? htmlspecialchars($formData['zipcode']) : 'N/A') . '</span></p>
    </td>
    <td
        style="width: 16%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt" 
        colspan="1">
        <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Country<span style=" color: #0029F5;"><br>' . (isset($formData['country']) ? htmlspecialchars($formData['country']) : 'N/A') . '</span></p>
    </td>
    <td style="width: 16%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
        colspan="2">
        <p class="s2" style="padding-top: 1pt;padding-left: 6pt;text-indent: 0pt;text-align: left;">City, State
        <span style=" color: #0029F5;"><br>' . (isset($formData['corporate_city']) ? htmlspecialchars($formData['corporate_city']) : 'N/A') . '</span></p>
    </td>
    <td
        style="width: 16%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt" 
        colspan="1">
        <p class="s2"
            style="padding-top: 1pt;padding-left: 5pt;padding-right: 8pt;text-indent: 0pt;line-height: 108%;text-align: left;">
            Zip/Postal Code <span style=" color: #0029F5;"><br>' . (isset($formData['corporate_postal']) ? htmlspecialchars($formData['corporate_postal']) : 'N/A') . '</span></p>
    </td>
    <td
        style="width: 16%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt" 
        colspan="1">
        <p class="s2" style="padding-top: 1pt;padding-left: 6pt;text-indent: 0pt;text-align: left;">Country <span style=" color: #0029F5;"><br>' . (isset($formData['corporate_country']) ? htmlspecialchars($formData['corporate_country']) : 'N/A') . '</span></p>
    </td>
</tr>

        <tr style="height:28pt">
            <td style="width:130pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="4">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Contact Name
                    / Relationship <span style=" color: #0029F5;"><br>' . (isset($formData['contact_name']) ? htmlspecialchars($formData['contact_name']) : 'N/A') . '</span></p>
            </td>
            <td style="width:155pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="2">
                <p class="s2" style="padding-top: 1pt;padding-left: 6pt;text-indent: 0pt;text-align: left;">Email
                    Address <span style=" color: #0029F5;">' . (isset($formData['contact_email']) ? htmlspecialchars($formData['contact_email']) : 'N/A') . '</span></p>
            </td>
            <td style="width:130pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="2">
                <p class="s2" style="padding-top: 1pt;padding-left: 6pt;text-indent: 0pt;text-align: left;">Technical
                    Contact Name <span style=" color: #0029F5;"><br>' . (isset($formData['technicalcontact_name']) ? htmlspecialchars($formData['technicalcontact_name']) : 'N/A') . '</span></p>
            </td>
            <td
                style="width:138pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="1">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Email
                    Address <span style=" color: #0029F5;"><br>' . (isset($formData['technicalcontact_email']) ? htmlspecialchars($formData['technicalcontact_email']) : 'N/A') . '</span></p>
            </td>
        </tr>
        
        <tr style="height:28pt">
            <td style="width: 33%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:2pt;border-right-style:solid;border-right-width:1pt"
                colspan="4">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Telephone
                    Number <span style=" color: #0029F5;"><br>' . (isset($formData['contact_phone']) ? htmlspecialchars($formData['contact_phone']) : 'N/A') . '</span></p>
            </td>
           
            <td style="width: 33%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:2pt;border-right-style:solid;border-right-width:1pt"
                colspan="3">
                <p class="s2" style="padding-top: 1pt;padding-left: 6pt;text-indent: 0pt;text-align: left;">Billing
                    Contact Name <span style=" color: #0029F5;"><br>' . (isset($formData['billingcontact_name']) ? htmlspecialchars($formData['billingcontact_name']) : 'N/A') . '</span></p>
            </td>
            <td
                style="width: 33%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:2pt;border-right-style:solid;border-right-width:1pt"
                colspan="2">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Email
                    Address <span style=" color: #0029F5;"><br>' . (isset($formData['billingcontact_email']) ? htmlspecialchars($formData['billingcontact_email']) : 'N/A') . '</span></p>
            </td>        
        </tr>
        
        
        <tr style="height:28pt">
            <td style="width:183pt;border-top-style:solid;border-top-width:2pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="4">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Country of
                    Registration <span style=" color: #0029F5;"><br>' . (isset($formData['registration_country']) ? htmlspecialchars($formData['registration_country']) : 'N/A') . '</span></p>
            </td>
            <td style="width:220pt;border-top-style:solid;border-top-width:2pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:2pt;border-right-style:solid;border-right-width:1pt"
                colspan="3">
                <p class="s2" style="padding-top: 1pt;padding-left: 6pt;text-indent: 0pt;text-align: left;">Company
                    Registration Number <span style=" color: #0029F5;"><br>' . (isset($formData['companyregistration_taxnumber']) ? htmlspecialchars($formData['companyregistration_taxnumber']) : 'N/A') . '</span></p>
                
            </td>
            <td
                style="width:160pt;border-top-style:solid;border-top-width:2pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:2pt;border-right-style:solid;border-right-width:1pt"
                colspan="2">
                <p class="s2" style="padding-top: 1pt;padding-left: 7pt;text-indent: 0pt;text-align: left;">VAT
                    Identification # <span style=" color: #0029F5;"><br>' . (isset($formData['vatidentification_number']) ? htmlspecialchars($formData['vatidentification_number']) : 'N/A') . '</span></p>
            </td>
        </tr>
        
        <tr style="height:19pt">
            <td style="width: 25%;border-top-style:solid;border-top-width:2pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="4">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Length of
                    Time in Business: <span style=" color: #0029F5;">' . (isset($formData['lengthbussiness_time']) ? htmlspecialchars($formData['lengthbussiness_time']) : 'N/A') . '</span></p>
            </td>
            <td style="width: 25%;border-top-style:solid;border-top-width:2pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="2">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Capital
                    Resources (assets): <span style=" color: #0029F5;">' . (isset($formData['capital_resources']) ? htmlspecialchars($formData['capital_resources']) : 'N/A') . '</span></p>
            </td>
            <td style="width: 25%;border-top-style:solid;border-top-width:2pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="2">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Turnover
                    Last Year (income): <span style=" color: #0029F5;">' . (isset($formData['turnover_lastyear']) ? htmlspecialchars($formData['turnover_lastyear']) : 'N/A') . '</span></p>
            </td>
            <td
                style="width: 25%;border-top-style:solid;border-top-width:2pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="1">
                <p class="s2" style="padding-top: 1pt;padding-left: 7pt;text-indent: 0pt;text-align: left;">Number of
                    Employees <span style=" color: #0029F5;">' . (isset($formData['number_employees']) ? htmlspecialchars($formData['number_employees']) : 'N/A') . '</span></p>
            </td>
        </tr>
        
        <tr style="height:14pt">
            <td style="width:563pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:2pt;border-right-style:solid;border-right-width:1pt"
                colspan="9" bgcolor="#15213a">
                <p class="s1"
                    style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;line-height: 11pt;text-align: left;">
                    OWNERSHIP PROFILE <span class="s3">(ownership must equal 50% or more)</span></p>
            </td>
        </tr>
        
        <tr style="height:28pt">
            <td style="width: 26%;border-top-style:solid;border-top-width:2pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="4">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Name -
                    Principal #1 <span style=" color: #0029F5;"><br>' . (isset($formData['name_principal1']) ? htmlspecialchars($formData['name_principal1']) : 'N/A') . '</span></p>
            </td>
            <td
                style="width: 10%;border-top-style:solid;border-top-width:2pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="1">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Title <span style=" color: #0029F5;"><br>' . (isset($formData['principal1_title']) ? htmlspecialchars($formData['principal1_title']) : 'N/A') . '</p>
            </td>
            <td
                style="width: 18%;border-top-style:solid;border-top-width:2pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt" colspan="1">
                <p class="s2" style="padding-top: 1pt;padding-left: 6pt;text-indent: 0pt;text-align: left;">% Owned <span style=" color: #0029F5;"><br>' . (isset($formData['principal1_ownedpercentage']) ? htmlspecialchars($formData['principal1_ownedpercentage']) : 'N/A') . '</p>
            </td>
            <td style="width: 26%;border-top-style:solid;border-top-width:2pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="2">
                <p class="s2" style="padding-top: 1pt;padding-left: 6pt;text-indent: 0pt;text-align: left;">Telephone
                    Number <span style=" color: #0029F5;">' . (isset($formData['principal1_phonenumber']) ? htmlspecialchars($formData['principal1_phonenumber']) : 'N/A') . '</span></p>
            </td>
            <td
                style="width: 20%;border-top-style:solid;border-top-width:2pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="1">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Email
                    Address <span style=" color: #0029F5;">' . (isset($formData['principal1_email']) ? htmlspecialchars($formData['principal1_email']) : 'N/A') . '</span></p>
            </td>
        </tr>
        
        <tr style="height:20pt">
            <td style="width: 25%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="5">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Date of
                    Birth <span style=" color: #0029F5;">' . (isset($formData['principal1_dateofbirth']) ? htmlspecialchars($formData['principal1_dateofbirth']) : 'N/A') . '</span></p>
            </td>
            <td style="width: 25%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="1">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;"></p>
            </td>
            <td style="width: 25%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="2">
                <p class="s2" style="padding-left: 7pt;text-indent: 0pt;line-height: 10pt;text-align: left;">
                    Identification Type <span style=" color: #F00;"><br>Passport Upload</span></p>
            </td>
            <td
                style="width: 25%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="1">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;"></p>
            </td>
        </tr>
        
        <tr style="height:30pt">
            <td style="width: 35%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="5">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Address <span style=" color: #0029F5;">' . (isset($formData['principal1_address']) ? htmlspecialchars($formData['principal1_address']) : 'N/A') . '</span></p>
            </td>
            <td style="width: 20%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="1">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">City, State
                <span style=" color: #0029F5;"><br>' . (isset($formData['principal1_city']) ? htmlspecialchars($formData['principal1_city']) : 'N/A') . '</span></p>
            </td>
            <td style="width: 20%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="2">
                <p class="s2" style="padding-top: 1pt;padding-left: 6pt;text-indent: 0pt;text-align: left;">Zip/Postal
                    Code <span style=" color: #0029F5;"><br>' . (isset($formData['principal1_postal']) ? htmlspecialchars($formData['principal1_postal']) : 'N/A') . '</span></p>
            </td>
            <td
                style="width: 25%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="1">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Country <span style=" color: #0029F5;"><br>' . (isset($formData['principal1_country']) ? htmlspecialchars($formData['principal1_country']) : 'N/A') . '</span></p>
            </td>
        </tr>
        <tr style="height:28pt">
            <td style="width: 26%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="4">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Name -
                    Principal #2 <span style=" color: #0029F5;"><br>' . (isset($formData['name_principal2']) ? htmlspecialchars($formData['name_principal2']) : 'N/A') . '</span></p>
            </td>
            <td
                style="width: 10%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt" colspan="1">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Title <span style=" color: #0029F5;"><br>' . (isset($formData['principal2_title']) ? htmlspecialchars($formData['principal2_title']) : 'N/A') . '</span></p>
            </td>
            <td
                style="width: 18%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt" colspan="1">
                <p class="s2" style="padding-top: 1pt;padding-left: 6pt;text-indent: 0pt;text-align: left;">% Owned <span style=" color: #0029F5;"><br>' . (isset($formData['principal2_ownedpercentage']) ? htmlspecialchars($formData['principal2_ownedpercentage']) : 'N/A') . '</span></p>
            </td>
            <td style="width: 26%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="2">
                <p class="s2" style="padding-top: 1pt;padding-left: 6pt;text-indent: 0pt;text-align: left;">Telephone
                    Number <span style=" color: #0029F5;"><br>' . (isset($formData['principal2_phonenumber']) ? htmlspecialchars($formData['principal2_phonenumber']) : 'N/A') . '</span></p>
            </td>
            <td
                style="width: 20%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="1">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Email
                    Address <span style=" color: #0029F5;"><br>' . (isset($formData['principal2_email']) ? htmlspecialchars($formData['principal2_email']) : 'N/A') . '</span></p>
            </td>
        </tr>
        <tr style="height:20pt">
            <td style="width:183pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="5">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Date of
                    Birth <span style=" color: #0029F5;"><br>' . (isset($formData['principal2_dateofbirth']) ? htmlspecialchars($formData['principal2_dateofbirth']) : 'N/A') . '</span></p>
            </td>
            <td style="width:102pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="1">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;"></p>
            </td>
            <td style="width:132pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="2">
                <p class="s2" style="padding-left: 7pt;text-indent: 0pt;line-height: 10pt;text-align: left;">
                    Identification Type <span class="s5"><br>Passport Upload</span></p>
            </td>
            <td
                style="width:146pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="1">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;"></p>
            </td>
        </tr>
        <tr style="height:30pt">
            <td style="width: 35%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="5">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Address <span style=" color: #0029F5;">' . (isset($formData['principal2_address']) ? htmlspecialchars($formData['principal2_address']) : 'N/A') . '</span></p>
            </td>
            <td style="width: 20%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="1">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">City, State
                <span style=" color: #0029F5;"><br>' . (isset($formData['principal2_city']) ? htmlspecialchars($formData['principal2_city']) : 'N/A') . '</span></p>
            </td>
            <td style="width: 20%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="2">
                <p class="s2" style="padding-top: 1pt;padding-left: 6pt;text-indent: 0pt;text-align: left;">Zip/Postal
                    Code <span style=" color: #0029F5;"><br>' . (isset($formData['principal2_postal']) ? htmlspecialchars($formData['principal2_postal']) : 'N/A') . '</span></p>
            </td>
            <td
                style="width: 25%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="1">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Country <span style=" color: #0029F5;"><br>' . (isset($formData['principal2_country']) ? htmlspecialchars($formData['principal2_country']) : 'N/A') . '</span></p>
            </td>
        </tr>
        <tr style="height:14pt">
            <td style="width:563pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="9" bgcolor="#15213a">
                <p class="s1" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">BUSINESS
                    PROFILE</p>
            </td>
        </tr>
        <tr style="height:41pt">
            <td style="width:563pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="9">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Please
                    provide a profile of the company <span style=" color: #0029F5;">' . (isset($formData['profile_of_the_company']) ? htmlspecialchars($formData['profile_of_the_company']) : 'N/A') . '</span></p>
            </td>
        </tr>
        <tr style="height:28pt">
            <td style="width:146pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="4">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Current
                    Acquirer <span style=" color: #0029F5;"><br>' . (isset($formData['current_acquirer']) ? htmlspecialchars($formData['current_acquirer']) : 'N/A') . '</span></p>
            </td>
            <td style="width:139pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="2">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Current
                    Gateway <span style=" color: #0029F5;"><br>' . (isset($formData['current_gateway']) ? htmlspecialchars($formData['current_gateway']) : 'N/A') . '</span></p>
            </td>
            <td style="width:278pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="3">
                <p class="s2" style="padding-top: 1pt;padding-left: 6pt;text-indent: 0pt;text-align: left;">Reason for
                    leaving current acquirer: <span style=" color: #0029F5;">' . (isset($formData['leavingcurrent_acquirer']) ? htmlspecialchars($formData['leavingcurrent_acquirer']) : 'N/A') . '</span></p>
            </td>
        </tr>
        <tr style="height:23pt">
            <td style="width:285pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="5">
                <p class="s2" style="padding-top: 7pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Length of
                    time accepting credit cards: <span style=" color: #0029F5;"><br>' . (isset($formData['creditcard_timeaccept']) ? htmlspecialchars($formData['creditcard_timeaccept']) : 'N/A') . '</span></p>
            </td>
            <td style="width:278pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
    colspan="4">
    <p class="s2" style="padding-top: 1pt;padding-left: 6pt;text-indent: 0pt;text-align: left;">Jurisdiction of transactions:
        <span style="color: #0029F5;">
            % U.S. ' . (isset($formData['us_percent']) ? htmlspecialchars($formData['us_percent']) : 'N/A') . ' %
            Europe ' . (isset($formData['europe_percent']) ? htmlspecialchars($formData['europe_percent']) : 'N/A') . ' %
            Asia ' . (isset($formData['asia_percent']) ? htmlspecialchars($formData['asia_percent']) : 'N/A') . ' %
            ROW ' . (isset($formData['row_percent']) ? htmlspecialchars($formData['row_percent']) : 'N/A') . ' %
        </span>
    </p>
</td>

        </tr>
        <tr style="height:31pt">            
            <td style="width: 33%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="5">
                <p class="s2" style="padding-left: 7pt;text-indent: 0pt;line-height: 10pt;text-align: left;">Estimated
                    Monthly Volume <span style=" color: #0029F5;"><br>' . (isset($formData['required_monthly_processing_volume']) ? htmlspecialchars($formData['required_monthly_processing_volume']) : 'N/A') . '</span></p>
            </td>
            <td
                style="width: 33%;;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt" colspan="2"> 
                <p class="s2"
                    style="padding-top: 1pt;padding-left: 5pt;padding-right: 26pt;text-indent: 0pt;line-height: 108%;text-align: left;">
                    Average Ticket <span style=" color: #0029F5;"><br>' . (isset($formData['average_ticket']) ? htmlspecialchars($formData['average_ticket']) : 'N/A') . '</span></p>
            </td>
            <td
                style="width: 33%;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt" colspan="2">
                <p class="s2" style="padding-top: 1pt;padding-left: 6pt;text-indent: 0pt;text-align: left;">Highest
                    Ticket <span style=" color: #0029F5;"><br>' . (isset($formData['highest_ticket']) ? htmlspecialchars($formData['highest_ticket']) : 'N/A') . '</span></p>
            </td>
        </tr>
        <tr style="height:33pt">
            <td style="width:563pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="9">
                <p class="s2" style="padding-top: 1pt;padding-left: 4pt;text-indent: 0pt;text-align: left;">URL(s) <span style=" color: #0029F5;">' . (isset($formData['business_profile_url']) ? htmlspecialchars($formData['business_profile_url']) : 'N/A') . '</span></p>
            </td>
        </tr>
        <tr style="height:132pt">
            <td style="width:563pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="9">
                <p class="s2" style="padding-top: 1pt;padding-left: 4pt;text-indent: 0pt;text-align: left;">Descriptor
                    (max 25 characters: For example - company name, phone #, URL) you require to be shown on Credit Card
                    statement <span style=" color: #0029F5;">' . (isset($formData['descriptor_preference']) ? htmlspecialchars($formData['descriptor_preference']) : 'N/A') . '</span></p>
            </td>
        </tr>
        <tr style="height:112pt">
            <td style="width:273pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:2pt;border-right-style:solid;border-right-width:1pt"
                colspan="5">
                <p class="s2" style="padding-top: 1pt;padding-left: 4pt;text-indent: 0pt;text-align: left;">Description
                    of products/ services sold <span style=" color: #0029F5;">' . (isset($formData['description_products']) ? htmlspecialchars($formData['description_products']) : 'N/A') . '</span></p>
            </td>
            <td style="width:290pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:2pt;border-right-style:solid;border-right-width:1pt"
                colspan="4">
                <p class="s2" style="padding-top: 1pt;padding-left: 4pt;text-indent: 0pt;text-align: left;">Recurring
                    Services? YES NO If yes describe <span style=" color: #0029F5;">' . (isset($formData['recurring_services_options']) ? htmlspecialchars($formData['recurring_services_options']) : 'N/A') . ' ' . (isset($formData['recurring_services_describe']) ? htmlspecialchars($formData['recurring_services_describe']) : 'N/A') . '</span></p>
            </td>
        </tr>
        <tr style="height:8pt">
            <td style="width:273pt;border-top-style:solid;border-top-width:2pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="5">
                <p style="text-indent: 0pt;text-align: left;"><br></p>
            </td>
            <td style="width:290pt;border-top-style:solid;border-top-width:2pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt"
                colspan="4">
                <p style="text-indent: 0pt;text-align: left;"><br></p>
            </td>
        </tr>
        <tr style="height:22pt">
            <td
                style="width:68pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:2pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="9">
                <p class="s2" style="padding-top: 1pt;padding-left: 4pt;text-indent: 0pt;text-align: left;">Card Types Required <span style="color: #0029F5;">' . (isset($formData['selectedOptions']) ? htmlspecialchars($formData['selectedOptions']) : 'N/A') . '</span>
                </p>
            </td>
        </tr>
    </tbody>
</table>

<p style="text-indent: 0pt;text-align: left;"><br></p>
  <h1 style="padding-top: 3pt;padding-left: 0pt;text-indent: 0pt;line-height: 10pt;text-align: left;">Please create a drop box with all of these available cards, where the Merchant can choose which card services they require, so more than 1 selection if required.</h1>
  <p style="text-indent: 0pt;text-align: left;"><br></p>
  <p style="text-indent: 0pt;text-align: left;"><br></p>
  <p style="text-indent: 0pt;text-align: left;"><span></span></p><br>
 </div> 
  <div class="page-break"></div> <!-- Insert a page break -->

  
  <div class="page">  
    <table style="border-collapse:collapse;" cellspacing="0">
    <tbody><br><br><br><br>
        <tr style="height:14pt">
            <td style="width:563pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="9" bgcolor="#15213a">
                <p class="s1" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">CURRENCY REQUESTED</p>
            </td>
        </tr>
        <tr style="height:35pt">
            <td style="width:285pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="9">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">In which currency are your products sold? <span style=" color: #0029F5;">' . (isset($formData['currency_require_processing']) ? htmlspecialchars($formData['currency_require_processing']) : 'N/A') . '</span></p>
            </td>
        </tr>
        <tr style="height:35pt">
            <td style="width:285pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="9">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">In which currency would you like payment to be transferred to your bank account? <span style=" color: #0029F5;">' . (isset($formData['currency_require_settlement']) ? htmlspecialchars($formData['currency_require_settlement']) : 'N/A') . '</span></p>
            </td>
            
        </tr>
        
        <tr style="height:16pt">
            <td style="width:200pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:2pt;border-right-style:solid;border-right-width:1pt"
                colspan="3" bgcolor="#15213a">
                <p class="s1"
                    style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;line-height: 11pt;text-align: left;">
                    PROCESSING HISTORY </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:2pt;border-right-style:solid;border-right-width:1pt"
                 colspan="1" bgcolor="#15213a">
                <p class="s1"
                    style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;line-height: 11pt;text-align: left;">
                    LAST MONTH</p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:2pt;border-right-style:solid;border-right-width:1pt"
                 bgcolor="#15213a">
                <p class="s1"
                    style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;line-height: 11pt;text-align: left;">
                    2 MONTHS AGO</p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:2pt;border-right-style:solid;border-right-width:1pt"
                 bgcolor="#15213a">
                <p class="s1"
                    style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;line-height: 11pt;text-align: left;">
                    3 MONTHS AGO</p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:2pt;border-right-style:solid;border-right-width:1pt"
                 bgcolor="#15213a">
                <p class="s1"
                    style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;line-height: 11pt;text-align: left;">
                    4 MONTHS AGO</p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:2pt;border-right-style:solid;border-right-width:1pt"
                 bgcolor="#15213a">
                <p class="s1"
                    style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;line-height: 11pt;text-align: left;">
                    5 MONTHS AGO</p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:2pt;border-right-style:solid;border-right-width:1pt"
                  bgcolor="#15213a">
                <p class="s1"
                    style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;line-height: 11pt;text-align: left;">
                    6 MONTHS AGO</p>
            </td>
        </tr>
        <tr style="height:16pt">
            <td style="width:200pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="3">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Sales volume</p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                 
                <p><span style=" color: #0029F5;"> ' . (isset($formData['sales_volume_lastmonth']) ? htmlspecialchars($formData['sales_volume_lastmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                
                <p> <span style=" color: #0029F5;">' . (isset($formData['sales_volume_secondmonth']) ? htmlspecialchars($formData['sales_volume_secondmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                <p> <span style=" color: #0029F5;"> ' . (isset($formData['sales_volume_thirdmonth']) ? htmlspecialchars($formData['sales_volume_thirdmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                <p> <span style=" color: #0029F5;">' . (isset($formData['sales_volume_fourthmonth']) ? htmlspecialchars($formData['sales_volume_fourthmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                <p> <span style=" color: #0029F5;">' . (isset($formData['sales_volume_fifthmonth']) ? htmlspecialchars($formData['sales_volume_fifthmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                <p> <span style=" color: #0029F5;">' . (isset($formData['sales_volume_sixthmonth']) ? htmlspecialchars($formData['sales_volume_sixthmonth']) : 'N/A') . '</span>
                    </p>
            </td>
        </tr>
      <tr style="height:16pt">
            <td style="width:200pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="3">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Number of transactions</p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                 
                <p> <span style=" color: #0029F5;">' . (isset($formData['number_of_transcations_lastmonth']) ? htmlspecialchars($formData['number_of_transcations_lastmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                
                <p> <span style=" color: #0029F5;">' . (isset($formData['number_of_transcations_secondmonth']) ? htmlspecialchars($formData['number_of_transcations_secondmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                <p> <span style=" color: #0029F5;">' . (isset($formData['number_of_transcations_thirdmonth']) ? htmlspecialchars($formData['number_of_transcations_thirdmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                <p> <span style=" color: #0029F5;">' . (isset($formData['number_of_transcations_fourthmonth']) ? htmlspecialchars($formData['number_of_transcations_fourthmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                <p> <span style=" color: #0029F5;">' . (isset($formData['number_of_transcations_fifthmonth']) ? htmlspecialchars($formData['number_of_transcations_fifthmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                <p> <span style=" color: #0029F5;">' . (isset($formData['number_of_transcations_sixthmonth']) ? htmlspecialchars($formData['number_of_transcations_sixthmonth']) : 'N/A') . '</span>
                    </p>
            </td>
        </tr>
      <tr style="height:16pt">
            <td style="width:200pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="3">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Chargeback volume</p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                 
                <p> <span style=" color: #0029F5;">' . (isset($formData['chargeback_volume_lastmonth']) ? htmlspecialchars($formData['chargeback_volume_lastmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                
                <p> <span style=" color: #0029F5;">' . (isset($formData['chargeback_volume_secondmonth']) ? htmlspecialchars($formData['chargeback_volume_secondmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                <p> <span style=" color: #0029F5;">' . (isset($formData['chargeback_volume_thirdmonth']) ? htmlspecialchars($formData['chargeback_volume_thirdmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                <p> <span style=" color: #0029F5;"> ' . (isset($formData['chargeback_volume_fourthmonth']) ? htmlspecialchars($formData['chargeback_volume_fourthmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                <p> <span style=" color: #0029F5;">' . (isset($formData['chargeback_volume_fifthmonth']) ? htmlspecialchars($formData['chargeback_volume_fifthmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                <p> <span style=" color: #0029F5;">' . (isset($formData['chargeback_volume_sixthmonth']) ? htmlspecialchars($formData['chargeback_volume_sixthmonth']) : 'N/A') . '</span>
                    </p>
            </td>
        </tr>
      <tr style="height:16pt">
            <td style="width:200pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="3">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Number of chargebacks</p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                 
                <p> <span style=" color: #0029F5;">' . (isset($formData['number_of_chargebacks_lastmonth']) ? htmlspecialchars($formData['number_of_chargebacks_lastmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                
                <p> <span style=" color: #0029F5;">' . (isset($formData['number_of_chargebacks_secondmonth']) ? htmlspecialchars($formData['number_of_chargebacks_secondmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                <p> <span style=" color: #0029F5;">' . (isset($formData['number_of_chargebacks_thirdmonth']) ? htmlspecialchars($formData['number_of_chargebacks_thirdmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                <p> <span style=" color: #0029F5;">' . (isset($formData['number_of_chargebacks_fourthmonth']) ? htmlspecialchars($formData['number_of_chargebacks_fourthmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                <p> <span style=" color: #0029F5;">' . (isset($formData['number_of_chargebacks_fifthmonth']) ? htmlspecialchars($formData['number_of_chargebacks_fifthmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                <p> <span style=" color: #0029F5;">' . (isset($formData['number_of_chargebacks_sixthmonth']) ? htmlspecialchars($formData['number_of_chargebacks_sixthmonth']) : 'N/A') . '</span>
                    </p>
            </td>
        </tr>
      <tr style="height:16pt">
            <td style="width:200pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="3">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Refunds volume</p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                 
                <p> <span style=" color: #0029F5;">' . (isset($formData['refunds_volume_lastmonth']) ? htmlspecialchars($formData['refunds_volume_lastmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                
                <p> <span style=" color: #0029F5;">' . (isset($formData['refunds_volume_secondmonth']) ? htmlspecialchars($formData['refunds_volume_secondmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                <p> <span style=" color: #0029F5;">' . (isset($formData['refunds_volume_thirdmonth']) ? htmlspecialchars($formData['refunds_volume_thirdmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                <p> <span style=" color: #0029F5;">' . (isset($formData['refunds_volume_fourthmonth']) ? htmlspecialchars($formData['refunds_volume_fourthmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                <p> <span style=" color: #0029F5;">' . (isset($formData['refunds_volume_fifthmonth']) ? htmlspecialchars($formData['refunds_volume_fifthmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                <p> <span style=" color: #0029F5;">' . (isset($formData['refunds_volume_sixthmonth']) ? htmlspecialchars($formData['refunds_volume_sixthmonth']) : 'N/A') . '</span>
                    </p>
            </td>
        </tr>
      <tr style="height:16pt">
            <td style="width:200pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="3">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Number of refunds</p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                 
                <p> <span style=" color: #0029F5;">' . (isset($formData['number_of_refunds_lastmonth']) ? htmlspecialchars($formData['number_of_refunds_lastmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                
                <p> <span style=" color: #0029F5;">' . (isset($formData['number_of_refunds_secondmonth']) ? htmlspecialchars($formData['number_of_refunds_secondmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                <p> <span style=" color: #0029F5;">' . (isset($formData['number_of_refunds_thirdmonth']) ? htmlspecialchars($formData['number_of_refunds_thirdmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                <p> <span style=" color: #0029F5;">' . (isset($formData['number_of_refunds_fourthmonth']) ? htmlspecialchars($formData['number_of_refunds_fourthmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                <p> <span style=" color: #0029F5;">' . (isset($formData['number_of_refunds_fifthmonth']) ? htmlspecialchars($formData['number_of_refunds_fifthmonth']) : 'N/A') . '</span>
                    </p>
            </td>
          <td style="width:60pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt">
                <p> <span style=" color: #0029F5;">' . (isset($formData['number_of_refunds_sixthmonth']) ? htmlspecialchars($formData['number_of_refunds_sixthmonth']) : 'N/A') . '</span>
                    </p>
            </td>
        </tr>
        
        <tr style="height:16pt">
            <td style="width:563pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="9" bgcolor="#15213a">
                <p class="s1" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">CARDHOLDER DATA STORAGE COMPLIANCE</p>
            </td>
        </tr>
        <tr style="height:30pt">
            <td style="width:563pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="9">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Are you using software or gateway application? <span style=" color: #0029F5;">' . (isset($formData['using_software_or_gateway_application']) ? htmlspecialchars($formData['using_software_or_gateway_application']) : 'N/A') . '</span></p>
            </td>
        </tr>
        <tr style="height:30pt">
            <td style="width:563pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="9">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">What third party software company/vendor did you purchase your application from? <span style=" color: #0029F5;">' . (isset($formData['third_party_software_company_vendor_purchase']) ? htmlspecialchars($formData['third_party_software_company_vendor_purchase']) : 'N/A') . '</span></p>
            </td>
        </tr>
        <tr style="height:30pt">
            <td style="width:563pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="9">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">What is the name of the third party software? <span style=" color: #0029F5;">' . (isset($formData['name_third_party_software']) ? htmlspecialchars($formData['name_third_party_software']) : 'N/A') . '</span></p>
            </td>
        </tr>
        <tr style="height:30pt">
            <td style="width:563pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="9">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">What is the version of the third party software? <span style=" color: #0029F5;">' . (isset($formData['version_third_party_software']) ? htmlspecialchars($formData['version_third_party_software']) : 'N/A') . '</span></p>
            </td>
        </tr>
        <tr style="height:30pt">
            <td style="width:563pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="9">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Do your transactions process through any other third parties, web hosting companies or gateways? <span style=" color: #0029F5;">' . (isset($formData['process_through_any_other_third_parties']) ? htmlspecialchars($formData['process_through_any_other_third_parties']) : 'N/A') . '</span></p>
            </td>
        </tr>
      
      <tr style="height:30pt">
            <td style="width:563pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="9">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Do you or your vendor receive, pass, transmit or store the full cardholder number, electronically? <span style=" color: #0029F5;">' . (isset($formData['store_full_cardholder_number_electronically']) ? htmlspecialchars($formData['store_full_cardholder_number_electronically']) : 'N/A') . '</span></p>
            </td>
        </tr>
      
      <tr style="height:30pt">
            <td style="width:563pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="9">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">If yes, where is card data stored? <span style=" color: #0029F5;">' . (isset($formData['selectedOptions2']) ? htmlspecialchars($formData['selectedOptions2']) : 'N/A') . '</span></p>
            </td>
        </tr>
        <tr style="height:30pt">
            <td style="width:563pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="9">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Are you or your vendor PCI/DSS (Payment Card Industry/Data Security Standard) compliant? <span style=" color: #0029F5;">' . (isset($formData['pci_dss_compliant']) ? htmlspecialchars($formData['pci_dss_compliant']) : 'N/A') . '</span></p>
            </td>
        </tr>
      <tr style="height:30pt">
            <td style="width:563pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="9">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">What is the name of your Qualified Security Assessor? <span style=" color: #0029F5;">' . (isset($formData['qualified_security_assessor']) ? htmlspecialchars($formData['qualified_security_assessor']) : 'N/A') . '</span></p>
            </td>
        </tr>
      <tr style="height:30pt">
            <td style="width:563pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="9">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Date of Compliance? <span style=" color: #0029F5;">' . (isset($formData['date_of_compliance']) ? htmlspecialchars($formData['date_of_compliance']) : 'N/A') . '</span></p>
            </td>
        </tr>
      <tr style="height:30pt">
            <td style="width:563pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="9">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Date of last scan? <span style=" color: #0029F5;">' . (isset($formData['date_of_last_scan']) ? htmlspecialchars($formData['date_of_last_scan']) : 'N/A') . '</span></p>
            </td>
        </tr>
      <tr style="height:30pt">
            <td style="width:563pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="9">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Have your ever experienced an account data compromise? <span style=" color: #0029F5;">' . (isset($formData['experienced_account_data_compromise']) ? htmlspecialchars($formData['experienced_account_data_compromise']) : 'N/A') . '</span></p>
            </td>
        </tr>
      <tr style="height:41pt">
            <td style="width:563pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="9">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">If yes, When? <span style=" color: #0029F5;">' . (isset($formData['experienced_account_data_compromise_when']) ? htmlspecialchars($formData['experienced_account_data_compromise_when']) : 'N/A') . '</span></p>
            </td>
        </tr>
      <tr style="height:14pt">
            <td style="width:563pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="9" bgcolor="#15213a">
                <p class="s1" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;"></p>
            </td>
        </tr>
    </tbody>
</table>
  
  <h1 style="padding-top: 3pt;padding-left: 0pt;text-indent: 0pt;line-height: 10pt;text-align: left; color: black;">Declaration:</h1>
  <p style="text-indent: 0pt;text-align: left;"><br></p>
  <h1 style="width:550pt; padding-top: 3pt;padding-left: 0pt;text-indent: 0pt;line-height: 10pt;text-align: left; color: black;">I hereby confirm to be the owner of the listed website(s). I further declare to have full control and authorization of the website content. I acknowledge and agree that I will not use the Processing System for transactions relating to; 1) Sales made under a different trade name or business affiliation than indicated on this Agreement or otherwise approved by the acquirer in writing; 2) Fines or Penalties of any kind, losses, damages or any other costs that are beyond the Total Sale Price; 3) Any transaction that violates any law, ordinance, or regulation applicable to my business; 4) Goods which I / we know will be resold by a customer whom I / we reasonably should know is not ordinarily in the business of selling such goods; 5) Sales by third parties; 6) Any other amounts for which a customer has not specifically authorized payment through the acquirer; 7) Cash, travellers cheques, Cash equivalents, or other negotiable instruments; or 8) Amounts which do not represent a bona fide sale of goods or services by me / us. I also declare on behalf of the company and on behalf of myself that, to the best of our knowledge, neither the company nor the website nor myself (or any of us) have ever been involved in excessive chargeback’s, fraud or content violation nor have any of the above ever terminated by an acquirer or asked by an acquirer to terminate an agreement within a set period of time.</h1>
  <p style="text-indent: 0pt;text-align: left;"><br></p>
  <p style="text-indent: 0pt;text-align: left;"><br></p>
  <h1 style="padding-top: 3pt;padding-left: 0pt;text-indent: 0pt;line-height: 10pt;text-align: left; color: black;">By printing your name below, you here by agree and accept.</h1>
  
  <table style="border-collapse:collapse;" cellspacing="0">
    <tbody>
        <tr style="height:41pt">
            <td style="width:260pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="5">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Principal # 1 <img src="data:image/png;base64,' . $signatureData1 . '" alt="Principal Signature" style="max-width: 50%; height: 10%;"></p>
            </td>
          <td style="width:260pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
    colspan="4">
    <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Principal # 2 <img src="data:image/png;base64,' . $signatureData2 . '" alt="Principal Signature" style="max-width: 50%; height: 10%;"></p>
    
    

</td>


        </tr>
      <tr style="height:21pt">
        <td style="width:260pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="5">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Principal # 1 Date <span style=" color: #0029F5;">' . (isset($formData['principal1_dateofsignature']) ? htmlspecialchars($formData['principal1_dateofsignature']) : 'N/A') . '</span></p>
            </td>
          <td style="width:260pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="4">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Principal # 2 Date <span style=" color: #0029F5;">' . (isset($formData['principal2_dateofsignature']) ? htmlspecialchars($formData['principal2_dateofsignature']) : 'N/A') . '</span></p>
            </td>
        </tr>
      <tr style="height:14pt">
            <td style="width:563pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="9" bgcolor="#15213a">
                <p class="s1" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">SUPPORTING DOCUMENTS</p>
            </td>
        </tr>
      <tr style="height:41pt">
        <td style="width:260pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="5">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">6 Month Merchant Processing Statements</p>
            </td>
          <td style="width:260pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="4">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Certificate of Incorporation (Orginating Co. & EU registered CO.)</p>
            </td>
        </tr>
      <tr style="height:41pt">
        <td style="width:260pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="5">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Articles/ Memorandum of Association</p>
            </td>
          <td style="width:260pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="4">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Business/Operating License</p>
            </td>
        </tr>
      <tr style="height:41pt">
        <td style="width:260pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="5">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Business Bank Statement</p>
            </td>
          <td style="width:303pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:1pt;border-right-style:solid;border-right-width:1pt"
                colspan="4">
                <p class="s2" style="padding-top: 1pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">Chargeback and refund screenshots</p>
            </td>
            
        </tr>
        <tr style="height:41pt">
        <td style="width:563pt;border-top-style:solid;border-top-width:1pt;border-left-style:solid;border-left-width:1pt;border-bottom-style:solid;border-bottom-width:2pt;border-right-style:solid;border-right-width:1pt"
                colspan="9">
                <p class="s2" style="padding-top: 1pt;padding-left: 4pt;text-indent: 0pt;text-align: left;">Any Further Information to support your Application <span style=" color: #0029F5;">' . (isset($formData['further_information_application']) ? htmlspecialchars($formData['further_information_application']) : 'N/A') . '</span></p>
            </td>
        </tr>
    </tbody>
</table>
  <p style="text-indent: 0pt;text-align: left;"><br></p>
  <p style="text-indent: 0pt;text-align: left;"><br></p>
  <p style="text-indent: 0pt;text-align: left;"><br></p>
  <p style="text-indent: 0pt;text-align: left;"><span></span></p>
    
    </div>
    </div>
    
    <!-- Pagination buttons -->
    <div class="pagination">
        <button id="page1Btn" class="active" onclick="showPage(0); return false;">Page 1</button>
        <button id="page2Btn" onclick="showPage(1); return false;">Page 2</button>
    </div>
    <script>
        function showPage(pageIndex) {
            var pages = document.querySelectorAll(".page");
            var pageButtons = document.querySelectorAll(".pagination button");
            for (var i = 0; i < pages.length; i++) {
                pages[i].style.display = "none";
                pageButtons[i].classList.remove("active");
            }
            pages[pageIndex].style.display = "block";
            pageButtons[pageIndex].classList.add("active");
        }
    </script>
</body>
</html>';

    return $html;
}
  
private function getBase64ImageData($s3, $bucketName, $key) {
    try {
        $result = $s3->getObject([
            'Bucket' => $bucketName,
            'Key' => $key,
        ]);
        $body = (string) $result['Body'];
        error_log("getBase64ImageData: Successfully retrieved image data for key {$key}");
        return base64_encode($body);
    } catch (AwsException $e) {
        error_log("getBase64ImageData: Error retrieving image data - " . $e->getMessage());
        return null;
    }
}




}
