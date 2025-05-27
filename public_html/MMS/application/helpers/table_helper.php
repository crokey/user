<?php

defined('BASEPATH') or exit('No direct script access allowed');

hooks()->add_action('admin_init', function () {
    App_table::register(
        App_table::new('clients')->customfieldable('customers')->setPrimaryKeyName('userid')
    );
  

    

    App_table::register(
        App_table::new('tickets')->setPrimaryKeyName('ticketid')->customfieldable('tickets')
    );

    

    

   
});
