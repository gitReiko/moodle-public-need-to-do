<?php 

namespace NTD\Classes\Lib\Getters;

/**
 * Contains teacher getters.
 */
class Teachers 
{

    /**
     * Returns the teachers with whom the block works.
     * 
     * Based on the cohort specified in the global block settings.
     * 
     * Doesn't return suspended and deleted users.
     * 
     * @return array of teachers if there are users in the cohort.
     * @return null if the cohort is empty.
     */
    public static function get_teachers_from_global_block_settings() : ?array
    {
        $cohortId = get_config('block_needtodo', 'monitored_teachers_cohort');
        return self::get_teachers_from_cohort($cohortId);
    }

    /**
     * Returns teachers from cohort.
     * 
     * 
     * Doesn't return suspended and deleted users.
     * 
     * @return array of teachers if there are users in the cohort.
     * @return null if the cohort is empty.
     */
    public static function get_teachers_from_cohort(int $cohortId) : ?array 
    {
        $teachers = self::get_cohort_members($cohortId);

        if(is_array($teachers))
        {
            $teachers = self::add_fullnames_to_teachers_array($teachers);
            $teachers = self::sort_teachers_by_fullname($teachers); 
        }

        return $teachers;
    }

    /**
     * Returns array of teachers which contains only the user who works with block.
     * 
     * The array of teachers is needed because the unified functions are written to operate on an array.
     * 
     * @return array teachers with only user
     */
    public static function get_user_who_works_with_block_in_teachers_array() : array 
    {
        global $USER;
        $teachers = array($USER);
        $teachers = self::add_fullnames_to_teachers_array($teachers);
        return $teachers;
    }

    /**
     * Returns list of teachers for the database WHERE IN condition.
     * 
     * @param array of teachers
     * 
     * @return string in database condition
     */
    public static function get_where_in_condition_from_teachers_array(array $teachers) : ?string 
    {
        if(empty($teachers))
        {
            return null;
        }
        else 
        {
            $inCondition = ' IN (';

            foreach($teachers as $teacher)
            {
                $inCondition.= $teacher->id.',';
            }
    
            // Remove the last comma.
            $inCondition = substr($inCondition, 0, -1);
    
            $inCondition.= ') ';
    
            return $inCondition;
        }
    }

    /**
     * Returns cohort members.
     * 
     * Doesn't return suspended and deleted users.
     * 
     * @param int id of cohort
     * 
     * @return array of users if they exist.
     * @return null if the cohort is empty.
     */
    private static function get_cohort_members(int $cohortId)
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
