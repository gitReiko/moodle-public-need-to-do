<?php

$settings->add(
    new admin_setting_configtext(
        'block_needtodo/monitored_teachers_cohort', 
        get_string('monitored_teachers_cohort', 'block_needtodo'), 
        get_string('monitored_teachers_cohort_tool', 'block_needtodo'), 
        1, 
        PARAM_INT
    )
);

