<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="widget" id="widget-<?php echo create_widget_id(); ?>" data-name="<?php echo _l('user_widget'); ?>">
    <div class="panel_s user-data">
        <div class="panel-body home-activity">
            <div class="widget-dragger"></div>
            <div class="horizontal-scrollable-tabs panel-full-width-tabs">
                <div class="scroller scroller-left arrow-left"><i class="fa fa-angle-left"></i></div>
                <div class="scroller scroller-right arrow-right"><i class="fa fa-angle-right"></i></div>
                <div class="horizontal-tabs">
                    <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                        
                        
                        <li role="presentation">
                            <a href="#home_my_reminders"
                                onclick="initDataTable('.table-my-reminders', admin_url + 'misc/my_reminders', undefined, undefined,undefined,[2,'asc']);"
                                aria-controls="home_my_reminders" role="tab" data-toggle="tab">
                                <i class="fa-regular fa-clock menu-icon"></i> <?php echo _l('my_reminders'); ?>
                                <?php
                        $total_reminders = total_rows(
    db_prefix() . 'reminders',
    [
                           'isnotified' => 0,
                           'staff'      => get_staff_user_id(),
                        ]
);
                        if ($total_reminders > 0) {
                            echo '<span class="badge">' . $total_reminders . '</span>';
                        }
                        ?>
                            </a>
                        </li>
                        <?php if ((get_option('access_tickets_to_none_staff_members') == 1 && !is_staff_member()) || is_staff_member()) { ?>
                        <li role="presentation">
                            <a href="#home_tab_tickets" onclick="init_table_tickets(true);"
                                aria-controls="home_tab_tickets" role="tab" data-toggle="tab">
                                <i class="fa-regular fa-life-ring menu-icon"></i> <?php echo _l('home_tickets'); ?>
                            </a>
                        </li>
                        <?php } ?>
                        <?php if (is_staff_member()) { ?>
                        <li role="presentation">
                            <a href="#home_announcements" onclick="init_table_announcements(true);"
                                aria-controls="home_announcements" role="tab" data-toggle="tab">
                                <i class="fa fa-bullhorn menu-icon"></i> <?php echo _l('home_announcements'); ?>
                                <?php if ($total_undismissed_announcements != 0) {
                            echo '<span class="badge">' . $total_undismissed_announcements . '</span>';
                        } ?>
                            </a>
                        </li>
                        <?php } ?>
                        <?php if (is_admin()) { ?>
                        <li role="presentation">
                            <a href="#home_tab_activity" aria-controls="home_tab_activity" role="tab" data-toggle="tab">
                                <i class="fa fa-window-maximize menu-icon"></i>
                                <?php echo _l('home_latest_activity'); ?>
                            </a>
                        </li>
                        <?php } ?>
                        <?php hooks()->do_action('after_user_data_widget_tabs'); ?>
                    </ul>
                </div>
            </div>
            <div class="tab-content tw-mt-5">
                
                <?php if ((get_option('access_tickets_to_none_staff_members') == 1 && !is_staff_member()) || is_staff_member()) { ?>
                <div role="tabpanel" class="tab-pane" id="home_tab_tickets">
                    <a href="<?php echo admin_url('tickets'); ?>"
                        class="mbot20 inline-block full-width"><?php echo _l('home_widget_view_all'); ?></a>
                    <div class="clearfix"></div>
                    <div class="_filters _hidden_inputs hidden tickets_filters">
                        <?php
                           // On home only show on hold, open and in progress
                           echo form_hidden('ticket_status_1', true);
                           echo form_hidden('ticket_status_2', true);
                           echo form_hidden('ticket_status_4', true);
                           ?>
                    </div>
                    <?php echo AdminTicketsTableStructure(); ?>
                </div>
                <?php } ?>
                
                <div role="tabpanel" class="tab-pane" id="home_my_reminders">
                    <a href="<?php echo admin_url('misc/reminders'); ?>" class="mbot20 inline-block full-width">
                        <?php echo _l('home_widget_view_all'); ?>
                    </a>
                    <?php render_datatable([
                        _l('reminder_related'),
                        _l('reminder_description'),
                        _l('reminder_date'),
                        ], 'my-reminders'); ?>
                </div>
                <?php if (is_staff_member()) { ?>
                <div role="tabpanel" class="tab-pane" id="home_announcements">
                    <?php if (is_admin()) { ?>
                    <a href="<?php echo admin_url('announcements'); ?>"
                        class="mbot20 inline-block full-width"><?php echo _l('home_widget_view_all'); ?></a>
                    <div class="clearfix"></div>
                    <?php } ?>
                    <?php render_datatable([_l('announcement_name'), _l('announcement_date_list')], 'announcements'); ?>
                </div>
                <?php } ?>
                <?php if (is_admin()) { ?>
                <div role="tabpanel" class="tab-pane" id="home_tab_activity">
                    <a href="<?php echo admin_url('utilities/activity_log'); ?>"
                        class="mbot20 inline-block full-width"><?php echo _l('home_widget_view_all'); ?></a>
                    <div class="clearfix"></div>
                    <div class="activity-feed">
                        <?php foreach ($activity_log as $log) { ?>
                        <div class="feed-item">
                            <div class="date">
                                <span class="text-has-action" data-toggle="tooltip"
                                    data-title="<?php echo _dt($log['date']); ?>">
                                    <?php echo time_ago($log['date']); ?>
                                </span>
                            </div>
                            <div class="text">
                                <?php echo $log['staffid']; ?><br />
                                <?php echo $log['description']; ?>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
                <?php hooks()->do_action('after_user_data_widge_tabs_content'); ?>
            </div>
        </div>
    </div>
</div>