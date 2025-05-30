<?php

defined('BASEPATH') or exit('No direct script access allowed');

return App_table::find('clients')
    ->outputUsing(function ($params) {
        extract($params);

        $hasPermissionDelete = staff_can('delete',  'customers');

        $custom_fields = get_table_custom_fields('customers');
        $this->ci->db->query("SET sql_mode = ''");

        $aColumns = [
            '1',
            db_prefix() . 'clients.userid as userid',
            'company',
            'CONCAT(firstname, " ", lastname) as fullname',
            'email',
            db_prefix() . 'clients.phonenumber as phonenumber',
            db_prefix() . 'clients.active',
            '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'customer_groups JOIN ' . db_prefix() . 'customers_groups ON ' . db_prefix() . 'customer_groups.groupid = ' . db_prefix() . 'customers_groups.id WHERE customer_id = ' . db_prefix() . 'clients.userid ORDER by name ASC) as customerGroups',
            db_prefix() . 'clients.datecreated as datecreated',
        ];

        $sIndexColumn = 'userid';
        $sTable       = db_prefix() . 'clients';
        $where        = [];

        if ($filtersWhere = $this->getWhereFromRules()) {
            $where[] = $filtersWhere;
        }

        $join = [
            'LEFT JOIN ' . db_prefix() . 'contacts ON ' . db_prefix() . 'contacts.userid=' . db_prefix() . 'clients.userid AND ' . db_prefix() . 'contacts.is_primary=1',
        ];

        foreach ($custom_fields as $key => $field) {
            $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
            array_push($customFieldsColumns, $selectAs);
            array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
            array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . db_prefix() . 'clients.userid = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
        }

        $join = hooks()->apply_filters('customers_table_sql_join', $join);

        if (staff_cant('view', 'customers')) {
            array_push($where, 'AND ' . db_prefix() . 'clients.userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id=' . get_staff_user_id() . ')');
        }

        $aColumns = hooks()->apply_filters('customers_table_sql_columns', $aColumns);

        // Fix for big queries. Some hosting have max_join_limit
        if (count($custom_fields) > 4) {
            @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
        }

        $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
            db_prefix() . 'contacts.id as contact_id',
            'lastname',
            db_prefix() . 'clients.zip as zip',
            'registration_confirmed',
            'vat'
        ]);

        $output  = $result['output'];
        $rResult = $result['rResult'];

        foreach ($rResult as $aRow) {
            $row = [];

            // Bulk actions
            $row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow['userid'] . '"><label></label></div>';
            // User id
            $row[] = $aRow['userid'];

            // Company
            $company  = $aRow['company'];
            $isPerson = false;

            if ($company == '') {
                $company  = _l('no_company_view_profile');
                $isPerson = true;
            }

            $url = admin_url('clients/client/' . $aRow['userid']);

            if ($isPerson && $aRow['contact_id']) {
                $url .= '?contactid=' . $aRow['contact_id'];
            }

            $company = '<a href="' . $url . '">' . $company . '</a>';

            $company .= '<div class="row-options">';
            $company .= '<a href="' . admin_url('clients/client/' . $aRow['userid'] . ($isPerson && $aRow['contact_id'] ? '?group=contacts' : '')) . '">' . _l('view') . '</a>';

            if ($aRow['registration_confirmed'] == 0 && is_admin()) {
                $company .= ' | <a href="' . admin_url('clients/confirm_registration/' . $aRow['userid']) . '" class="text-success bold">' . _l('confirm_registration') . '</a>';
            }

            if (!$isPerson) {
                $company .= ' | <a href="' . admin_url('clients/client/' . $aRow['userid'] . '?group=contacts') . '">' . _l('customer_contacts') . '</a>';
            }

            if ($hasPermissionDelete) {
                $company .= ' | <a href="' . admin_url('clients/delete/' . $aRow['userid']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
            }

            $company .= '</div>';

            $row[] = $company;

            // Primary contact
            $row[] = ($aRow['contact_id'] ? '<a href="' . admin_url('clients/client/' . $aRow['userid'] . '?contactid=' . $aRow['contact_id']) . '" target="_blank">' . trim($aRow['fullname']) . '</a>' : '');

            // Primary contact email
            $row[] = ($aRow['email'] ? '<a href="mailto:' . $aRow['email'] . '">' . $aRow['email'] . '</a>' : '');

            // Primary contact phone
            $row[] = ($aRow['phonenumber'] ? '<a href="tel:' . $aRow['phonenumber'] . '">' . $aRow['phonenumber'] . '</a>' : '');

            // Toggle active/inactive customer
            $toggleActive = '<div class="onoffswitch" data-toggle="tooltip" data-title="' . _l('customer_active_inactive_help') . '">
    <input type="checkbox"' . ($aRow['registration_confirmed'] == 0 ? ' disabled' : '') . ' data-switch-url="' . admin_url() . 'clients/change_client_status" name="onoffswitch" class="onoffswitch-checkbox" id="' . $aRow['userid'] . '" data-id="' . $aRow['userid'] . '" ' . ($aRow[db_prefix() . 'clients.active'] == 1 ? 'checked' : '') . '>
    <label class="onoffswitch-label" for="' . $aRow['userid'] . '"></label>
    </div>';

            // For exporting
            $toggleActive .= '<span class="hide">' . ($aRow[db_prefix() . 'clients.active'] == 1 ? _l('is_active_export') : _l('is_not_active_export')) . '</span>';

            $row[] = $toggleActive;

            // Customer groups parsing
            $groupsRow = '';
            if ($aRow['customerGroups']) {
                $groups = explode(',', $aRow['customerGroups']);
                foreach ($groups as $group) {
                    $groupsRow .= '<span class="label label-default mleft5 customer-group-list pointer">' . $group . '</span>';
                }
            }

            $row[] = $groupsRow;

            $row[] = _dt($aRow['datecreated']);

            // Custom fields add values
            foreach ($customFieldsColumns as $customFieldColumn) {
                $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
            }

            $row['DT_RowClass'] = 'has-row-options';

            if ($aRow['registration_confirmed'] == 0) {
                $row['DT_RowClass'] .= ' info requires-confirmation';
                $row['Data_Title']  = _l('customer_requires_registration_confirmation');
                $row['Data_Toggle'] = 'tooltip';
            }

            if ($aRow[db_prefix().'clients.active'] == 0) {
                $row['DT_RowClass'] .= ' secondary';
            }
            
            $row = hooks()->apply_filters('customers_table_row_data', $row, $aRow);

            $output['aaData'][] = $row;
        }
        return $output;
    })->setRules([
        App_table_filter::new('phonenumber', 'TextRule')->label(_l('clients_phone')),
        App_table_filter::new('active', 'BooleanRule')->label(_l('customer_active')),
        

        
        App_table_filter::new('city', 'TextRule')->label(_l('clients_city')),
        App_table_filter::new('zip', 'TextRule')->label(_l('clients_zip')),
        App_table_filter::new('state', 'TextRule')->label(_l('clients_state')),
        App_table_filter::new('country', 'SelectRule')->label(_l('clients_country'))
            ->options(function ($ci) {
                return collect($ci->clients_model->get_clients_distinct_countries())->map(fn ($data) => [
                    'value' => $data['country_id'],
                    'label' => $data['short_name']
                ]);
            }),
        App_table_filter::new('customer_admins', 'MultiSelectRule')->label(_l('responsible_admin'))
            ->isVisible(fn () => staff_can('create', 'customers') || staff_can('edit', 'customers'))
            ->options(function ($ci) {
                return collect($ci->clients_model->get_customers_admin_unique_ids())->map(fn ($data) => [
                    'value' => $data['staff_id'],
                    'label' => get_staff_full_name($data['staff_id'])
                ]);
            })
            ->raw(function ($value, $operator, $sqlOperator) {
                return   db_prefix() . 'clients.userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id ' . $sqlOperator['operator'] . ' (' . implode(', ', $value) . '))';
            }),
        App_table_filter::new('groups', 'MultiSelectRule')->label(_l('customer_groups'))
            ->options(function ($ci) {
                return collect($ci->clients_model->get_groups())->map(fn ($group) => [
                    'value' => $group['id'],
                    'label' => $group['name']
                ]);
            })->raw(function ($value, $operator, $sqlOperator) {
                return db_prefix() . 'clients.userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_groups WHERE groupid ' . $sqlOperator['operator'] . ' (' . implode(', ', $value) . '))';
            }),
        App_table_filter::new('my_customers', 'BooleanRule')->label(_l('customers_assigned_to_me'))
            ->raw(function ($value) {
                return db_prefix() . 'clients.userid ' . ($value == '1' ? 'IN' : 'NOT IN') . ' (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id=' . get_staff_user_id() . ')';
            }),
        App_table_filter::new('requires_confirmation', 'BooleanRule')
            ->label(_l('customer_requires_registration_confirmation'))
            ->raw(function ($value) {
                return db_prefix() . 'clients.registration_confirmed=' . ($value == '1' ? '0' : '1');
            }),
    ]);
