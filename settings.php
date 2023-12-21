<?php

$settings->add(
    new admin_setting_heading(
        'block_needtodo/general_settings',
        get_string('general_settings', 'block_needtodo'),
        ''
    )
);

$cohorts = $DB->get_records('cohort', array(), 'name', 'id,name');

$options = array();
foreach($cohorts as $cohort)
{
    $membersCount = $DB->count_records('cohort_members', array('cohortid'=>$cohort->id));
    $cohort->name .= ' ('.$membersCount.')';
    $options = array_merge($options, array($cohort->id => $cohort->name));
}

$settings->add(new admin_setting_configselect(
        'block_accessreview/monitored_teachers_cohort',
        get_string('monitored_teachers_cohort', 'block_needtodo'), 
        get_string('monitored_teachers_cohort_tool', 'block_needtodo'),
        key($options),
        $options
    )
);

$settings->add(
    new admin_setting_configtext(
        'block_needtodo/days_to_check', 
        get_string('days_to_check', 'block_needtodo'), 
        get_string('days_to_check_tool', 'block_needtodo'), 
        8, 
        PARAM_INT
    )
);

$settings->add(
    new admin_setting_configtext(
        'block_needtodo/working_past_days', 
        get_string('working_past_days', 'block_needtodo'), 
        get_string('working_past_days_tool', 'block_needtodo'), 
        180, 
        PARAM_INT
    )
);

$settings->add(
    new admin_setting_heading(
        'block_needtodo/components',
        get_string('enabling_components', 'block_needtodo'),
        ''
    )
);

$settings->add(
    new admin_setting_configcheckbox(
        'block_needtodo/enable_chat_messages',
        get_string('site_messenger', 'block_needtodo'),
        get_string('site_messenger_desc', 'block_needtodo'),
        '1'
    )
);

$settings->add(
    new admin_setting_configcheckbox(
        'block_needtodo/enable_assign',
        get_string('assign', 'block_needtodo'),
        get_string('assign_desc', 'block_needtodo'),
        '1'
    )
);

$settings->add(
    new admin_setting_configcheckbox(
        'block_needtodo/enable_forum',
        get_string('forum', 'block_needtodo'),
        get_string('forum_desc', 'block_needtodo'),
        '1'
    )
);

$settings->add(
    new admin_setting_configcheckbox(
        'block_needtodo/enable_quiz',
        get_string('quiz', 'block_needtodo'),
        get_string('quiz_desc', 'block_needtodo'),
        '1'
    )
);

$settings->add(
    new admin_setting_configcheckbox(
        'block_needtodo/enable_coursework',
        get_string('coursework', 'block_needtodo'),
        get_string('coursework_desc', 'block_needtodo'),
        '0'
    )
);
