<?php 

namespace NTD\Classes\Lib\Getters;

require_once 'common.php';

/**
 * Contains teacher getters.
 */
class Teachers 
{

    /**
     * Returns all teachers from global and local blocks settings.
     * 
     * @return array if teachers exist
     * @return null if not
     */
    public static function get_all_teachers() : ?array 
    {
        $cohorts = self::get_unique_teachers_cohorts();

        $teachers = array();
        foreach($cohorts as $cohort)
        {
            $cohortTeachers = self::get_teachers_from_cohort($cohort);
            $teachers = array_merge($teachers, $cohortTeachers);
        }

        $teachers = self::get_unique_teachers($teachers);

        return $teachers;
    }

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
        $cohortId = Common::get_id_of_global_block_cohort();
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

    /**
     * Returns unique teachers cohorts if exists.
     * 
     * @return array cohorts if exists
     */
    private static function get_unique_teachers_cohorts()
    {
        $cohorts = array();

        $globalCohort = self::get_cohort_from_global_settings();

        if(isset($globalCohort))
        {
            $cohorts[] = $globalCohort;
        }

        $localCohorts = self::get_teacher_cohorts_from_block_instances();

        if(count($localCohorts) > 0)
        {
            $cohorts = array_merge($cohorts, $localCohorts);
        }

        $cohorts = array_unique($cohorts);

        return $cohorts;
    }

    /** 
     * Returns teacher cohort from global settings.
     * 
     * @return int cohort id
     */
    private static function get_cohort_from_global_settings()
    {
        return Common::get_id_of_global_block_cohort();
    }

    /**
     * Returns teachers cohorts from block instances.
     * 
     * @return array teacher cohorts if exists
     */
    private static function get_teacher_cohorts_from_block_instances()
    {
        $data = self::get_block_instances_data();
        $data = self::decode_block_instances_data($data);
        return self::get_cohorts_from_block_instances($data);
    }

    /**
     * Returns block instances data.
     * 
     * @return array if data exists
     * @return null if not
     */
    private static function get_block_instances_data() : ?array 
    {
        global $DB;
        $where = array('blockname' => 'needtodo');
        return $DB->get_records('block_instances', $where);
    }

    /**
     * Decodes block instances data.
     * 
     * @param array block instances data if exists
     * 
     * @return array block instances data if exists
     */
    private static function decode_block_instances_data($instances) : ?array 
    {
        foreach($instances as $instance)
        {
            $instance->configdata = unserialize(base64_decode($instance->configdata));
        }

        return $instances;
    }

    /**
     * Returns teacher cohorts from block instances.
     * 
     * @param array block instances data if exists
     * 
     * @return array block instances data if exists
     */
    private static function get_cohorts_from_block_instances($instances) : array 
    {
        $cohorts = array();

        foreach($instances as $instance)
        {
            if(isset($instance->configdata->use_local_settings))
            {
                if($instance->configdata->use_local_settings == 1)
                {
                    $cohorts[] = $instance->configdata->local_cohort;
                }
            }
        }

        return $cohorts;
    }

    /**
     * Returns array of unique teachers.
     * 
     * @param array teachers
     * 
     * @return array unique teachers
     */
    private static function get_unique_teachers($teachers) : array 
    {
        $unique = array();

        foreach($teachers as $teacher)
        {
            if(self::is_teacher_unique($unique, $teacher))
            {
                $unique[] = $teacher;
            }
        }

        return $unique;
    }

    /**
     * Returns true if teacher unique. 
     * 
     * @param array unique teachers 
     * @param stdClass processed teacher
     * 
     * @return bool 
     */
    private static function is_teacher_unique($unique, $teacher) : bool 
    {
        foreach($unique as $value)
        {
            if($value->id == $teacher->id)
            {
                return false;
            }
        }

        return true;
    }

}
