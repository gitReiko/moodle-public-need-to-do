<?php

$settings->add(
    new admin_setting_configtext(
        'block_needtodo/teacherscohort', 
        get_string('teacherscohort', 'block_needtodo'), 
        get_string('teacherscohort_tool', 'block_needtodo'), 
        1, 
        PARAM_INT
    )
);

