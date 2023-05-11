<?php 

namespace NTD\Classes\Components\Forum\DatabaseWriter;

require_once __DIR__.'/../../../lib/components/database_writer.php';
require_once 'course.php';
require_once 'forum.php';

use \NTD\Classes\Lib\Components\DatabaseWriter;
use \NTD\Classes\Lib\Enums as Enums; 

class Refact extends DatabaseWriter 
{

    /** An array of courses that have unread forum posts. */
    private $courses = array();

    /** Sets component name. */
    protected function set_component_name() : void
    {
        $this->componentName = Enums::FORUM;
    }

    /** Prepares data neccessary for database writer. */
    protected function prepare_neccessary_data() : void 
    {
        $forums = $this->get_forums();

        foreach($forums as $forum)
        {
            $this->process_course_level($forum);
            //$this->process_teachers_level($forum);
            // process actvities level is in teacher level
        }

        print_r($this->courses);

        //$this->data = $this->courses;
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
        /*
        $needtodo = new \stdClass;
        $needtodo->component = $this->componentName;
        $needtodo->teacherid = $dataEntity->courseid;
        $needtodo->info = json_encode($dataEntity);
        $needtodo->updatetime = time();
        return $needtodo;
        */
    }

    /**
     * Returns forums with subscription.
     * 
     * @return array forums if exists
     */
    private function get_forums() 
    {
        $forums = new Forum;
        return $forums->get_forums();
    }

    /**
     * Process forum on course level.
     * 
     * @param stdClass forum 
     */
    private function process_course_level(\stdClass $forum) : void 
    {
        $course = new Course($this->courses, $forum);
        $this->courses = $course->process_level();
    }

}
