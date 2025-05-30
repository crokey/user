<?php

use app\services\CustomerProfileBadges;

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Check whether the contact email is verified
 * @since  2.2.0
 * @param  mixed  $id contact id
 * @return boolean
 */
function is_contact_email_verified($id = null)
{
    $id = !$id ? get_contact_user_id() : $id;

    if (isset($GLOBALS['contact']) && $GLOBALS['contact']->id == $id) {
        return !is_null($GLOBALS['contact']->email_verified_at);
    }

    $CI = &get_instance();

    $CI->db->select('email_verified_at');
    $CI->db->where('id', $id);
    $contact = $CI->db->get(db_prefix() . 'contacts')->row();

    if (!$contact) {
        return false;
    }

    return !is_null($contact->email_verified_at);
}

/**
 * Check whether the user disabled verification emails for contacts
 * @return boolean
 */
function is_email_verification_enabled()
{
    return total_rows(db_prefix() . 'emailtemplates', ['slug' => 'contact-verification-email', 'active' => 0]) == 0;
}
/**
 * Check if client id is used in the system
 * @param  mixed  $id client id
 * @return boolean
 */
function is_client_id_used($id)
{
    $total = 0;

    $checkCommonTables = [db_prefix() . 'subscriptions', db_prefix() . 'creditnotes', db_prefix() . 'projects', db_prefix() . 'invoices', db_prefix() . 'expenses', db_prefix() . 'estimates'];

    foreach ($checkCommonTables as $table) {
        $total += total_rows($table, [
            'client' => $id,
        ]);
    }

    $total += total_rows(db_prefix() . 'contracts', [
        'client' => $id,
    ]);

    $total += total_rows(db_prefix() . 'proposals', [
        'rel_id'   => $id,
        'rel_type' => 'customer',
    ]);

    $total += total_rows(db_prefix() . 'tickets', [
        'userid' => $id,
    ]);

    $total += total_rows(db_prefix() . 'tasks', [
        'rel_id'   => $id,
        'rel_type' => 'customer',
    ]);

    return hooks()->apply_filters('is_client_id_used', $total > 0 ? true : false, $id);
}
/**
 * Check if customer has subscriptions
 * @param  mixed $id customer id
 * @return boolean
 */
function customer_has_subscriptions($id)
{
    return hooks()->apply_filters('customer_has_subscriptions', total_rows(db_prefix() . 'subscriptions', ['clientid' => $id]) > 0);
}
/**
 * Get client by ID or current queried client
 * @param  mixed $id client id
 * @return mixed
 */
function get_client($id = null)
{
    if (empty($id) && isset($GLOBALS['client'])) {
        return $GLOBALS['client'];
    }

    // Client global object not set
    if (empty($id)) {
        return null;
    }

    $client = get_instance()->clients_model->get($id);

    return $client;
}
/**
 * Get predefined tabs array, used in customer profile
 * @return array
 */
function get_customer_profile_tabs()
{
    return get_instance()->app_tabs->get_customer_profile_tabs();
}

/**
 * Filter only visible tabs selected from the profile and add badge
 * @param  array $tabs available tabs
 * @param  int $id client
 * @return array
 */
function filter_client_visible_tabs($tabs, $id = '')
{
    $newTabs               = [];
    $customerProfileBadges = null;

    $visible = get_option('visible_customer_profile_tabs');
    if ($visible != 'all') {
        $visible = unserialize($visible);
    }

    if ($id !== '') {
        $customerProfileBadges = new CustomerProfileBadges($id);
    }

    $appliedSettings = is_array($visible);
    foreach ($tabs as $key => $tab) {

        // Check visibility from settings too
        if ($key != 'profile' && $key != 'contacts' && $appliedSettings) {
            if (array_key_exists($key, $visible) && $visible[$key] == false) {
                continue;
            }
        }

        if (!is_null($customerProfileBadges)) {
            $tab['badge'] = $customerProfileBadges->getBadge($tab['slug']);
        }

        $newTabs[$key] = $tab;
    }

    return hooks()->apply_filters('client_filtered_visible_tabs', $newTabs);
}
/**
 * @todo
 * Find a way to get the customer_id inside this function or refactor the hook
 * @param  string $group the tabs groups
 * @return null
 */
function app_init_customer_profile_tabs()
{
    $client_id = null;

    $remindersText = _l('client_reminders_tab');

    if ($client = get_client()) {
        $client_id = $client->userid;

        $total_reminders = total_rows(
            db_prefix() . 'reminders',
            [
                'isnotified' => 0,
                'staff'      => get_staff_user_id(),
                'rel_type'   => 'customer',
                'rel_id'     => $client_id,
            ]
        );

        if ($total_reminders > 0) {
            $remindersText .= ' <span class="badge">' . $total_reminders . '</span>';
        }
    }

    $CI = &get_instance();

    $CI->app_tabs->add_customer_profile_tab('profile', [
        'name'     => _l('client_add_edit_profile'),
        'icon'     => 'fa fa-user-circle',
        'view'     => 'admin/clients/groups/profile',
        'position' => 5,
        'badge'    => [],
    ]);
  
  $CI->app_tabs->add_customer_profile_tab('application', [
        'name'     => _l('Application'),
        'icon'     => 'fa-regular fa-file-lines',
        'view'     => 'admin/clients/groups/application',
        'position' => 10,
        'badge'    => [],
    ]);

    $CI->app_tabs->add_customer_profile_tab('contacts', [
        'name'     => 'Team',
        'icon'     => 'fa-solid fa-address-book',
        'view'     => 'admin/clients/groups/contacts',
        'position' => 15,
        'badge'    => [],
    ]);
  
  
  



   /* $CI->app_tabs->add_customer_profile_tab('tickets', [
        'name'     => _l('tickets'),
        'icon'     => 'fa-regular fa-life-ring',
        'view'     => 'admin/clients/groups/tickets',
        'visible'  => ((get_option('access_tickets_to_none_staff_members') == 1 && !is_staff_member()) || is_staff_member()),
        'position' => 75,
        'badge'    => [],
    ]);*/
  
   
  
  $CI->app_tabs->add_customer_profile_tab('contract', [
        'name'     => _l('Contract'),
        'icon'     => 'fa-solid fa-file-signature',
        'view'     => 'admin/clients/groups/contract',
        'position' => 25,
        'badge'    => [],
    ]);

   /* $CI->app_tabs->add_customer_profile_tab('attachments', [
       'name'     => _l('customer_attachments'),
        'icon'     => 'fa fa-paperclip',
        'view'     => 'admin/clients/groups/attachments',
        'position' => 85,
        'badge'    => [],
    ]);*/



}

/**
 * Get client id by lead id
 * @since  Version 1.0.1
 * @param  mixed $id lead id
 * @return mixed     client id
 */
function get_client_id_by_lead_id($id)
{
    $CI = &get_instance();
    $CI->db->select('userid')->from(db_prefix() . 'clients')->where('leadid', $id);

    return $CI->db->get()->row()->userid;
}

/**
 * Check if contact id passed is primary contact
 * If you dont pass $contact_id the current logged in contact will be checked
 * @param  string  $contact_id
 * @return boolean
 */
function is_primary_contact($contact_id = '')
{
    if (!is_numeric($contact_id)) {
        $contact_id = get_contact_user_id();
    }

    if (total_rows(db_prefix() . 'contacts', ['id' => $contact_id, 'is_primary' => 1]) > 0) {
        return true;
    }

    return false;
}

/**
 * Check if client have invoices with multiple currencies
 * @return booelan
 */
function is_client_using_multiple_currencies($clientid = '', $table = null)
{
    if (!$table) {
        $table = db_prefix() . 'invoices';
    }

    $CI = &get_instance();

    $clientid = $clientid == '' ? get_client_user_id() : $clientid;
    $CI->load->model('currencies_model');
    $currencies            = $CI->currencies_model->get();
    $total_currencies_used = 0;
    foreach ($currencies as $currency) {
        $CI->db->where('currency', $currency['id']);
        $CI->db->where('clientid', $clientid);
        $total = $CI->db->count_all_results($table);
        if ($total > 0) {
            $total_currencies_used++;
        }
    }

    $retVal = true;
    if ($total_currencies_used > 1) {
        $retVal = true;
    } elseif ($total_currencies_used == 0 || $total_currencies_used == 1) {
        $retVal = false;
    }

    return hooks()->apply_filters('is_client_using_multiple_currencies', $retVal, [
        'client_id' => $clientid,
        'table'     => $table,
    ]);
}


/**
 * Function used to check if is really empty customer company
 * Can happen user to have selected that the company field is not required and the primary contact name is auto added in the company field
 * @param  mixed  $id
 * @return boolean
 */
function is_empty_customer_company($id)
{
    $CI = &get_instance();
    $CI->db->select('company');
    $CI->db->from(db_prefix() . 'clients');
    $CI->db->where('userid', $id);
    $row = $CI->db->get()->row();
    if ($row) {
        if ($row->company == '') {
            return true;
        }

        return false;
    }

    return true;
}

/**
 * Get ids to check what files with contacts are shared
 * @param  array  $where
 * @return array
 */
function get_customer_profile_file_sharing($where = [])
{
    $CI = &get_instance();
    $CI->db->where($where);

    return $CI->db->get(db_prefix() . 'shared_customer_files')->result_array();
}

/**
 * Get customer id by passed contact id
 * @param  mixed $id
 * @return mixed
 */
function get_user_id_by_contact_id($id)
{
    $CI = &get_instance();

    $userid = $CI->app_object_cache->get('user-id-by-contact-id-' . $id);
    if (!$userid) {
        $CI->db->select('userid')
            ->where('id', $id);
        $client = $CI->db->get(db_prefix() . 'contacts')->row();

        if ($client) {
            $userid = $client->userid;
            $CI->app_object_cache->add('user-id-by-contact-id-' . $id, $userid);
        }
    }

    return $userid;
}

/**
 * Get primary contact user id for specific customer
 * @param  mixed $userid
 * @return mixed
 */
function get_primary_contact_user_id($userid)
{
    $CI = &get_instance();
    $CI->db->where('userid', $userid);
    $CI->db->where('is_primary', 1);
    $row = $CI->db->get(db_prefix() . 'contacts')->row();

    if ($row) {
        return $row->id;
    }

    return false;
}

/**
 * Get client full name
 * @param  string $contact_id Optional
 * @return string Firstname and Lastname
 */
function get_contact_full_name($contact_id = '')
{
    $contact_id == '' ? get_contact_user_id() : $contact_id;

    $CI = &get_instance();

    $contact = $CI->app_object_cache->get('contact-full-name-data-' . $contact_id);

    if (!$contact) {
        $CI->db->where('id', $contact_id);
        $contact = $CI->db->select('firstname,lastname')->from(db_prefix() . 'contacts')->get()->row();
        $CI->app_object_cache->add('contact-full-name-data-' . $contact_id, $contact);
    }

    if ($contact) {
        return $contact->firstname . ' ' . $contact->lastname;
    }

    return '';
}
/**
 * Return contact profile image url
 * @param  mixed $contact_id
 * @param  string $type
 * @return string
 */
function contact_profile_image_url($contact_id, $type = 'small')
{
    $url  = base_url('assets/images/user-placeholder.jpg');
    $CI   = &get_instance();
    $path = $CI->app_object_cache->get('contact-profile-image-path-' . $contact_id);

    if (!$path) {
        $CI->app_object_cache->add('contact-profile-image-path-' . $contact_id, $url);

        $CI->db->select('profile_image');
        $CI->db->from(db_prefix() . 'contacts');
        $CI->db->where('id', $contact_id);
        $contact = $CI->db->get()->row();

        if ($contact && !empty($contact->profile_image)) {
            $path = 'uploads/client_profile_images/' . $contact_id . '/' . $type . '_' . $contact->profile_image;
            $CI->app_object_cache->set('contact-profile-image-path-' . $contact_id, $path);
        }
    }

    if ($path && file_exists($path)) {
        $url = base_url($path);
    }

    return $url;
}
/**
 * Used in:
 * Search contact tickets
 * Project dropdown quick switch
 * Calendar tooltips
 * @param  [type] $userid [description]
 * @return [type]         [description]
 */
function get_company_name($userid, $prevent_empty_company = false)
{
    $_userid = get_client_user_id();
    if ($userid !== '') {
        $_userid = $userid;
    }
    $CI = &get_instance();

    $select = ($prevent_empty_company == false ? get_sql_select_client_company() : 'company');

    $client = $CI->db->select($select)
        ->where('userid', $_userid)
        ->from(db_prefix() . 'clients')
        ->get()
        ->row();
    if ($client) {
        return $client->company;
    }

    return '';
}


/**
 * Get client default language
 * @param  mixed $clientid
 * @return mixed
 */
function get_client_default_language($clientid = '')
{
    if (!is_numeric($clientid)) {
        $clientid = get_client_user_id();
    }

    $CI = &get_instance();
    $CI->db->select('default_language');
    $CI->db->from(db_prefix() . 'clients');
    $CI->db->where('userid', $clientid);
    $client = $CI->db->get()->row();
    if ($client) {
        return $client->default_language;
    }

    return '';
}

/**
 * Function is customer admin
 * @param  mixed  $id       customer id
 * @param  staff_id  $staff_id staff id to check
 * @return boolean
 */
function is_customer_admin($id, $staff_id = '')
{
    $staff_id = is_numeric($staff_id) ? $staff_id : get_staff_user_id();
    $CI       = &get_instance();
    $cache    = $CI->app_object_cache->get($id . '-is-customer-admin-' . $staff_id);

    if ($cache) {
        return $cache['retval'];
    }

    $total = total_rows(db_prefix() . 'customer_admins', [
        'customer_id' => $id,
        'staff_id'    => $staff_id,
    ]);

    $retval = $total > 0 ? true : false;
    $CI->app_object_cache->add($id . '-is-customer-admin-' . $staff_id, ['retval' => $retval]);

    return $retval;
}
/**
 * Check if staff member have assigned customers
 * @param  mixed $staff_id staff id
 * @return boolean
 */
function have_assigned_customers($staff_id = '')
{
    $CI       = &get_instance();
    $staff_id = is_numeric($staff_id) ? $staff_id : get_staff_user_id();
    $cache    = $CI->app_object_cache->get('staff-total-assigned-customers-' . $staff_id);

    if (is_numeric($cache)) {
        $result = $cache;
    } else {
        $result = total_rows(db_prefix() . 'customer_admins', [
            'staff_id' => $staff_id,
        ]);
        $CI->app_object_cache->add('staff-total-assigned-customers-' . $staff_id, $result);
    }

    return $result > 0 ? true : false;
}
/**
 * Check if contact has permission
 * @param  string  $permission permission name
 * @param  string  $contact_id     contact id
 * @return boolean
 */
function has_contact_permission($permission, $contact_id = '')
{
    $CI = &get_instance();

    if (!class_exists('app')) {
        $CI->load->library('app');
    }

    $permissions = get_contact_permissions();

    if (empty($contact_id)) {
        $contact_id = get_contact_user_id();
    }

    foreach ($permissions as $_permission) {
        if ($_permission['short_name'] == $permission) {
            return total_rows(db_prefix() . 'contact_permissions', [
                'permission_id' => $_permission['id'],
                'userid'        => $contact_id,
            ]) > 0;
        }
    }

    return false;
}
/**
 * Load customers area language
 * @param  string $customer_id
 * @return string return loaded language
 */
function load_client_language($customer_id = '')
{
    $CI = &get_instance();
    if (!$CI->load->is_loaded('cookie')) {
        $CI->load->helper('cookie');
    }

    if (defined('CLIENTS_AREA') && get_contact_language() != '' && !is_language_disabled()) {
        $language = get_contact_language();
    } else {
        $language = get_option('active_language');

        if ((is_client_logged_in() || $customer_id != '') && !is_language_disabled()) {
            $client_language = get_client_default_language($customer_id);

            if (!empty($client_language)
                && file_exists(APPPATH . 'language/' . $client_language)) {
                $language = $client_language;
            }
        }

        // set_contact_language($language);
    }

    $CI->lang->is_loaded = [];
    $CI->lang->language  = [];

    $CI->lang->load($language . '_lang', $language);
    load_custom_lang_file($language);

    $GLOBALS['language'] = $language;

    $GLOBALS['locale'] = get_locale_key($language);

    $CI->lang->set_last_loaded_language($language);

    hooks()->do_action('after_load_client_language', $language);

    return $language;
}
/**
 * Check if client have transactions recorded
 * @param  mixed $id clientid
 * @return boolean
 */
function client_have_transactions($id)
{
    $total = 0;

    foreach ([db_prefix() . 'invoices', db_prefix() . 'creditnotes', db_prefix() . 'estimates'] as $table) {
        $total += total_rows($table, [
            'clientid' => $id,
        ]);
    }

    $total += total_rows(db_prefix() . 'expenses', [
        'clientid' => $id,
        'billable' => 1,
    ]);

    $total += total_rows(db_prefix() . 'proposals', [
        'rel_id'   => $id,
        'rel_type' => 'customer',
    ]);

    return hooks()->apply_filters('customer_have_transactions', $total > 0, $id);
}


/**
 * Predefined contact permission
 * @return array
 */
function get_contact_permissions()
{
    $permissions = [
        [
            'id'         => 1,
            'name'       => _l('customer_permission_invoice'),
            'short_name' => 'invoices',
        ],
        [
            'id'         => 2,
            'name'       => _l('customer_permission_estimate'),
            'short_name' => 'estimates',
        ],
        [
            'id'         => 3,
            'name'       => _l('customer_permission_contract'),
            'short_name' => 'contracts',
        ],
        [
            'id'         => 4,
            'name'       => _l('customer_permission_proposal'),
            'short_name' => 'proposals',
        ],
        [
            'id'         => 5,
            'name'       => _l('customer_permission_support'),
            'short_name' => 'support',
        ],
        [
            'id'         => 6,
            'name'       => _l('customer_permission_projects'),
            'short_name' => 'projects',
        ],
    ];

    return hooks()->apply_filters('get_contact_permissions', $permissions);
}

function get_contact_permission($name)
{
    $permissions = get_contact_permissions();

    foreach ($permissions as $permission) {
        if ($permission['short_name'] == $name) {
            return $permission;
        }
    }

    return false;
}

/**
 * Additional checking for customers area, when contact edit his profile
 * This function will check if the checkboxes for email notifications should be shown
 * @return boolean
 */
function can_contact_view_email_notifications_options()
{
    return has_contact_permission('invoices')
        || has_contact_permission('estimates')
        || has_contact_permission('projects')
        || has_contact_permission('contracts');
}

/**
 * With this function staff can login as client in the clients area
 * @param  mixed $id client id
 */
function login_as_client($id)
{
    $CI = &get_instance();

    $CI->db->select(db_prefix() . 'contacts.id, active')
        ->where('userid', $id)
        ->where('is_primary', 1);

    $primary = $CI->db->get(db_prefix() . 'contacts')->row();

    if (!$primary) {
        set_alert('danger', _l('no_primary_contact'));
        redirect($_SERVER['HTTP_REFERER']);
    } elseif ($primary->active == '0') {
        set_alert('danger', 'Customer primary contact is not active, please set the primary contact as active in order to login as client');
        redirect($_SERVER['HTTP_REFERER']);
    }

    $CI->load->model('announcements_model');
    $CI->announcements_model->set_announcements_as_read_except_last_one($primary->id);

    $user_data = [
        'client_user_id'      => $id,
        'contact_user_id'     => $primary->id,
        'client_logged_in'    => true,
        'logged_in_as_client' => true,
    ];

    $CI->session->set_userdata($user_data);
}

function send_customer_registered_email_to_administrators($client_id)
{
    $CI = &get_instance();
    $CI->load->model('staff_model');
    $admins = $CI->staff_model->get('', ['active' => 1, 'admin' => 1]);

    foreach ($admins as $admin) {
        send_mail_template('customer_new_registration_to_admins', $admin['email'], $client_id, $admin['staffid']);
    }
}

/**
 * Return and perform additional checkings for contact consent url
 * @param  mixed $contact_id contact id
 * @return string
 */
function contact_consent_url($contact_id)
{
    $CI = &get_instance();

    $consent_key = get_contact_meta($contact_id, 'consent_key');

    if (empty($consent_key)) {
        $consent_key = app_generate_hash() . '-' . app_generate_hash();
        $meta_id     = false;
        if (total_rows(db_prefix() . 'contacts', ['id' => $contact_id]) > 0) {
            $meta_id = add_contact_meta($contact_id, 'consent_key', $consent_key);
        }
        if (!$meta_id) {
            return '';
        }
    }

    return site_url('consent/contact/' . $consent_key);
}

/**
 *  Get customer attachment
 * @param   mixed $id   customer id
 * @return  array
 */
function get_all_customer_attachments($id)
{
    $CI = &get_instance();

    $attachments                = [];
    $attachments['invoice']     = [];
    $attachments['estimate']    = [];
    $attachments['credit_note'] = [];
    $attachments['proposal']    = [];
    $attachments['contract']    = [];
    $attachments['lead']        = [];
    $attachments['task']        = [];
    $attachments['customer']    = [];
    $attachments['ticket']      = [];
    $attachments['expense']     = [];

    $has_permission_expenses_view = staff_can('view',  'expenses');
    $has_permission_expenses_own  = staff_can('view_own',  'expenses');
    if ($has_permission_expenses_view || $has_permission_expenses_own) {
        // Expenses
        $CI->db->select('clientid,id');
        $CI->db->where('clientid', $id);
        if (!$has_permission_expenses_view) {
            $CI->db->where('addedfrom', get_staff_user_id());
        }

        $CI->db->from(db_prefix() . 'expenses');
        $expenses = $CI->db->get()->result_array();
        $ids      = array_column($expenses, 'id');
        if (count($ids) > 0) {
            $CI->db->where_in('rel_id', $ids);
            $CI->db->where('rel_type', 'expense');
            $_attachments = $CI->db->get(db_prefix() . 'files')->result_array();
            foreach ($_attachments as $_att) {
                array_push($attachments['expense'], $_att);
            }
        }
    }


   
    
    
    

    

    

    $CI->db->select('ticketid,userid');
    $CI->db->where('userid', $id);
    $CI->db->from(db_prefix() . 'tickets');
    $tickets = $CI->db->get()->result_array();

    $ids = array_column($tickets, 'ticketid');

    if (count($ids) > 0) {
        $CI->db->where_in('ticketid', $ids);
        $_attachments = $CI->db->get(db_prefix() . 'ticket_attachments')->result_array();

        foreach ($_attachments as $_att) {
            array_push($attachments['ticket'], $_att);
        }
    }

    

    $CI->db->where('rel_id', $id);
    $CI->db->where('rel_type', 'customer');
    $client_main_attachments = $CI->db->get(db_prefix() . 'files')->result_array();

    $attachments['customer'] = $client_main_attachments;

    return hooks()->apply_filters('all_client_attachments', $attachments, $id);
}

/**
 * Used in customer profile vaults feature to determine if the vault should be shown for staff
 * @param  array $entries vault entries from database
 * @return array
 */
function _check_vault_entries_visibility($entries)
{
    $new = [];
    foreach ($entries as $entry) {
        if ($entry['visibility'] != 1) {
            if ($entry['visibility'] == 2 && !is_admin() && $entry['creator'] != get_staff_user_id()) {
                continue;
            } elseif ($entry['visibility'] == 3 && $entry['creator'] != get_staff_user_id() && !is_admin()) {
                continue;
            }
        }
        $new[] = $entry;
    }

    if (count($new) == 0) {
        $new = -1;
    }

    return $new;
}
/**
 * Default SQL select for selecting the company
 *
 * @param string $as
 *
 * @return string
 */
function get_sql_select_client_company($as = 'company')
{
    return 'CASE ' . db_prefix() . 'clients.company WHEN \' \' THEN (SELECT CONCAT(firstname, \' \', lastname) FROM ' . db_prefix() . 'contacts WHERE userid = ' . db_prefix() . 'clients.userid and is_primary = 1) ELSE ' . db_prefix() . 'clients.company END as ' . $as;
}

/**
 * @since  2.7.0
 * check if logged in user can manage contacts
 * @return boolean
 */
function can_loggged_in_user_manage_contacts()
{
    $id      = get_contact_user_id();
    $contact = get_instance()->clients_model->get_contact($id);

    if (($contact->is_primary != 1) || (get_option('allow_primary_contact_to_manage_other_contacts') != 1)) {
        return false;
    }

    if (is_individual_client()) {
        return false;
    }

    return true;
}

/**
 * @since  2.7.0
 * check if logged in customer is an individual
 * @return boolean
 */
function is_individual_client()
{
    return is_empty_customer_company(get_client_user_id())
        && total_rows(db_prefix() . 'contacts', ['userid' => get_client_user_id()]) == 1;
}

/**
 * @since  2.7.0
 * Set logged in contact language
 * @return void
 */
function set_contact_language($lang, $duration = 60 * 60 * 24 * 31 * 3)
{
    set_cookie('contact_language', $lang, $duration);
}

/**
 * @since  2.7.0
 * get logged in contact language
 * @return string
 */
function get_contact_language()
{
    if (!is_null(get_cookie('contact_language'))) {
        return get_cookie('contact_language');
    }

    return '';
}

/**
 * @since  2.9.0
 *
 * Indicates whether the contact automatically
 * appended calling codes feature is enabled based on the
 * customer selected country
 *
 * @return boolean
 */
function is_automatic_calling_codes_enabled()
{
    return hooks()->apply_filters('automatic_calling_codes_enabled', true);
}