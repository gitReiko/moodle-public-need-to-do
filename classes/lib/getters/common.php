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
     * Returns only active users.
     * 
     * @return array of teachers if there are users in the cohort.
     * @return null if the cohort is empty.
     */
    public static function get_cohort_teachers_from_global_settings()
    {
        $cohortId = get_config('block_needtodo', 'monitored_teachers_cohort');

        $teachers = self::get_users_from_cohort($cohortId);

        if(is_array($teachers))
        {
            $teachers = self::add_fullnames_to_teachers_array($teachers);
            $teachers = self::sort_teachers_by_fullname($teachers); 
        }

        return $teachers;
    }

    /**
     * Return array of users from user and cohort_members database tables.
     * 
     * Returns only active users.
     * 
     * @param int id of cohort
     * 
     * @return array of users if they exist.
     * @return null if the cohort is empty.
     */
    private static function get_users_from_cohort(int $cohortId)
    {
        global $DB;

        $sql = 'SELECT u.*
                FROM {cohort_members} AS cm 
                INNER JOIN {user} as u 
                ON cm.userid = u.id 
                WHERE cm.cohortid = ?
                AND u.deleted = 0
                AND u.suspended = 0';

        $params = array($cohortId);

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Adds fullnames to teachers array.
     * 
     * @param array of teachers
     * 
     * @return array of teachers with fullnames
     */
    private static function add_fullnames_to_teachers_array(array $teachers) : array 
    {
        foreach($teachers as $teacher)
        {
            $teacher->fullname = fullname($teacher, true);
        }

        return $teachers;
    }

    /**
     * Sorts teachers array by fullname.
     * 
     * @param array of teachers
     * 
     * @return array of teachers
     */
    private static function sort_teachers_by_fullname(array $teachers) : array 
    {
        usort($teachers, function($a, $b)
        {
            return strcmp($a->fullname, $b->fullname);
        });

        return $teachers;
    }

}
