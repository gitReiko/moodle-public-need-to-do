<?php 

namespace NTD\Classes\Components\Coursework\DatabaseWriter;

require_once __DIR__.'/../../../lib/components/database_writer/main.php';
require_once __DIR__.'/../../../lib/components/database_writer/template/course.php';
require_once __DIR__.'/../../../lib/components/database_writer/template/activities.php';
require_once 'courseworks.php';
require_once 'teachers.php';

use \NTD\Classes\Lib\Components\DatabaseWriter\Main as DatabaseWriter;
use \NTD\Classes\Lib\Components\DatabaseWriter\Template\Course;
use \NTD\Classes\Lib\Enums as Enums; 

class Main extends DatabaseWriter 
{
    /** Id of quiz module. */
    private $moduleId;

    /** An array of courses that have attempts. */
    private $courses = array();

    /** Outdated timestamp. Defines by global setting "working_past_days" */
    private $outdatedTimestamp;

    /** Sets component name. */
    protected function set_component_name() : void 
    {
        $this->componentName = Enums::COURSEWORK;

        $this->outdatedTimestamp = time() - (get_config('block_needtodo', 'working_past_days') * Enums::SECONDS_IN_DAY);

        $this->moduleId = $this->get_module_id();
    }

    /** Prepares data neccessary for database writer. */
    protected function prepare_neccessary_data() : void 
    {
        $courseworks = new Coursework($this->outdatedTimestamp);
        $undone = $courseworks->get_undone_teacher_work();

        foreach($undone as $coursework)
        {
            $this->process_course_level($coursework);
            $this->process_teachers_level($coursework);
            // process actvities level is in teacher level
        }

        $this->data = $this->courses;
    }

    /**
     * Returns the record to be written to the database.
     * 
     * @param stdClass dataEntity
     * 
     * @return stdClass needtodo record for database
     */
    protected function get_needtodo_record(\stdClass $dataEntity) : \stdClass 
    {
        $needtodo = new \stdClass;
        $needtodo->component = $this->componentName;
        $needtodo->entityid = $dataEntity->courseid;
        $needtodo->info = json_encode($dataEntity);
        $needtodo->updatetime = time();
        return $needtodo;
    }

    /**
     * Returns id of quiz module.
     * 
     * @return int quiz module id 
     */
    private function get_module_id() : int 
    {
        global $DB;

        $where = array('name' => Enums::COURSEWORK);

        return $DB->get_field('modules', 'id', $where);
    }

    /**
     * Process submission on course level.
     * 
     * @param stdClass submission 
     */
    private function process_course_level(\stdClass $coursework) : void 
    {
        $course = new Course($this->courses, $coursework);
        $this->courses = $course->process_level();
    }

    /**
     * Process assign submission on teachers level.
     * 
     * !!! A unique non-standard Teachers class.
     * 
     * @param stdClass assign submission 
     */
    private function process_teachers_level(\stdClass $coursework) : void 
    {
        $teachers = new Teachers($this->courses, $coursework);
        //$this->courses = $teachers->process_level(); 

        $teachers->process_level(); 
    }

}
