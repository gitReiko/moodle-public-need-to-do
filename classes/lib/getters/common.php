<?php 

namespace NTD\Classes\Lib\Getters;

/**
 * Contains common block getters.
 */
class Common 
{
    
    /**
     * Return array of teachers with which the block will work.
     * 
     * Based on the cohort specified in the global block settings.
     * 
     * @return array of teachers if there are users in the cohort.
     * @return null if the cohort is empty.
     */
    public static function get_cohort_teachers_from_global_settings()
    {
        global $DB;

        $where = array(
            'cohortid' => get_config('block_needtodo', 'teacherscohort')
        );

        return $DB->get_records('cohort_members', $where, '', 'userid as id');
    }

}
