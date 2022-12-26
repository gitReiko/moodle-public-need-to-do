<?php 

namespace NTD\Classes\DatabaseWriter;

require_once __DIR__.'/../lib/enums.php';
require_once __DIR__.'/../lib/getters/common.php';
require_once __DIR__.'/main.php';

use \NTD\Classes\Lib\Getters\Common as cGetter;
use NTD\Classes\Lib\Enums as Enums; 

/**
 * Writes data to the database.
 * 
 * This data is subsequently used by the renderer to quickly form a block.
 * 
 * @todo крон должен просчитывать учителей из всех групп для всех курсов
 */
class MainTask extends Main 
{

    /**
     * All teachers whose work is monitored by the block
     */
    protected $teachers;

    /**
     * Prepares data for the class.
     */
    function __construct() 
    {
        $this->teachers = $this->get_teachers();
    }

    /**
     * Returns all teachers from global and local settings whose work data needs to be updated.
     * 
     * @return array if teachers exist
     * @return null if not
     * 
     * @todo Восстановить работу в кроне. Учитывать глобальные данные и данные всех экземпляров блоков.
     */
    protected function get_teachers() 
    {
        $cohorts = $this->get_unique_teachers_cohorts();

        $teachers = array();
        foreach($cohorts as $cohort)
        {
            $cohortTeachers = cGetter::get_teachers_from_cohort($cohort);
            $teachers = array_merge($teachers, $cohortTeachers);
        }

        $teachers = $this->get_unique_teachers($teachers);

        return $teachers;
    }

    /**
     * Writes messanger related information to database.
     * 
     * @return void
     */
    protected function write_messsanger() : void 
    {
        $messangerWriter = new \NTD\Classes\Components\Messanger\DatabaseWriter(
            $this->teachers,
            Enums::UPDATE_DATA_ON_SITE_LEVEL
        );
        $messangerWriter->write();
    }

    /**
     * Writes data related to forum into database.
     * 
     * @return void
     */
    protected function write_forum() : void 
    {
        $forumWriter = new \NTD\Classes\Components\Forum\DatabaseWriter\Main(
            $this->teachers,
            Enums::UPDATE_DATA_ON_SITE_LEVEL
        );
        $forumWriter->write();
    }

    /**
     * Returns unique teachers cohorts if exists.
     * 
     * @return array cohorts if exists
     */
    private function get_unique_teachers_cohorts()
    {
        $cohorts = array();

        $globalCohort = $this->get_cohort_from_global_settings();

        if(isset($globalCohort))
        {
            $cohorts[] = $globalCohort;
        }

        $localCohorts = $this->get_teacher_cohorts_from_block_instances();

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
    private function get_cohort_from_global_settings()
    {
        return get_config('block_needtodo', 'monitored_teachers_cohort');
    }

    /**
     * Returns teachers cohorts from block instances.
     * 
     * @return array teacher cohorts if exists
     */
    private function get_teacher_cohorts_from_block_instances()
    {
        $data = $this->get_block_instances_data();
        $data = $this->decode_block_instances_data($data);
        return $this->get_cohorts_from_block_instances($data);
    }

    /**
     * Returns block instances data.
     * 
     * @return array if data exists
     * @return null if not
     */
    private function get_block_instances_data()
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
    private function decode_block_instances_data($instances)
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
    private function get_cohorts_from_block_instances($instances)
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
    private function get_unique_teachers($teachers)
    {
        $unique = array();

        foreach($teachers as $teacher)
        {
            if($this->is_teacher_unique($unique, $teacher))
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
    private function is_teacher_unique($unique, $teacher) : bool 
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
