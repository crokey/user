<?php

defined('BASEPATH') or exit('No direct script access allowed');


function app_init_admin_sidebar_menu_items()
{
    $CI = &get_instance();

    $CI->app_menu->add_sidebar_menu_item('dashboard', [
        'name'     => _l('als_dashboard'),
        'href'     => admin_url(),
        'position' => 1,
        'icon'     => 'fa fa-home',
        'badge'    => [],
    ]);

    $client_id = get_related_client_id_for_staff();
 
    $merchant_id = get_merchant_userid();
    $is_application_submitted = get_is_application_submitted($merchant_id);
    $is_full_access = get_is_full_access($client_id);

 

    // Add 'Account Overview' if the application is submitted
    /*if ($is_full_access && $is_application_submitted && $client_id && (staff_can('view', 'customers') || have_assigned_customers() || (!have_assigned_customers() && staff_can('create', 'customers')))) {
        $CI->app_menu->add_sidebar_menu_item('customers', [
            'name'     => _l('Acccount Overview'),
            'href'     => admin_url('clients/client/' . $client_id),
            'position' => 5,
            'icon'     => 'fa-regular fa-user',
            'badge'    => [],
        ]);
    }

    $menu_item = [
        'collapse' => true,
        'name'     => _l('als_application_form'),
        'position' => 10,
        'icon'     => 'fa-regular fa-clipboard',
        'badge'    => [],
    ];

    // Check if the application is not submitted
    if (!$is_application_submitted) {
        // Add the menu item only if the application is not submitted
        $CI->app_menu->add_sidebar_menu_item('application', $menu_item);
    }
  
    // Add 'Accounting' if the application is submitted
    if ($is_full_access && $is_application_submitted) {
        $CI->app_menu->add_sidebar_menu_item('accounting', [
            'collapse' => true,
            'name'     => _l('als_accounting'),
            'position' => 55,
            'icon'     => 'fa fa-book',
            'badge'    => [],
        ]);
  
        // Assuming you need to add child items under 'accounting' only when the application is submitted
        if (staff_can('view',  'expenses') || staff_can('view_own',  'expenses')) {
            $CI->app_menu->add_sidebar_children_item('accounting', [
                'slug'     => 'payments',
                'name'     => _l('Transactions'),
                'href'     => admin_url('payments'),
                'icon'     => 'fa-regular fa-file-lines',
                'position' => 5,
                'badge'    => [],
            ]);
        }
    }

    // Continue adding other menu items that are not dependent on the application submission status
    $CI->app_menu->add_sidebar_menu_item('communication', [
        'collapse' => true,
        'name'     => _l('als_communication'),
        'position' => 55,
        'icon'     => 'fa fa-comments',
        'badge'    => [],
    ]);*/
  
    // Add further items...
  
  	$CI->app_menu->add_sidebar_menu_item('tips', [
        'name'     => _l('Tips'),
        'href'     => admin_url('tips'),
        'position' => 5,
        'icon'     => 'ph ph-receipt',
        'badge'    => [],
    ]);
  
   $CI->app_menu->add_sidebar_menu_item('education', [
        'name'     => _l('Strategy & Education'),
        'href'     => admin_url('education'),
        'position' => 10,
        'icon'     => 'fa fa-book',
        'badge'    => [],
    ]);
  
  $CI->app_menu->add_sidebar_menu_item('mybets', [
    'name'     => _l('My Betting'),
    'href'     => admin_url('mybets'),
    'position' => 15,
    'icon'     => 'ph ph-dice-six', // For Phosphor
]);
  
  $CI->app_menu->add_sidebar_menu_item('communication', [
        'collapse' => true,
        'name'     => _l('Support & Community'),
        'position' => 20,
        'icon'     => 'fa fa-comments',
        'badge'    => [],
    ]);
  
  $CI->app_menu->add_sidebar_children_item('communication', [
        'slug'     => 'chat',
        'name'     => _l('Live Chat'),
        'href'     => admin_url('#'),
        'icon'     => 'ph ph-chat-circle-dots',
        'position' => 25,
        'badge'    => [],
    ]);
  
  $CI->app_menu->add_sidebar_children_item('communication', [
        'slug'     => 'faq',
        'name'     => _l('FAQs'),
        'href'     => admin_url('#'),
        'icon'     => 'ph ph-question',
        'position' => 30,
        'badge'    => [],
    ]);
  
  $CI->app_menu->add_sidebar_children_item('communication', [
        'slug'     => 'telegram',
        'name'     => _l('Join Telegram Group'),
        'href'     => admin_url('#'),
        'icon'     => 'ph ph-telegram-logo',
        'position' => 35,
        'badge'    => [],
    ]);


}
