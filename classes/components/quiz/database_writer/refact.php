<?php 

namespace NTD\Classes\Components\Quiz\DatabaseWriter;

require_once __DIR__.'/../../../lib/components/database_writer.php';
require_once 'course.php';
require_once 'teachers.php';

use \NTD\Classes\Lib\Components\DatabaseWriter;
use \NTD\Classes\Lib\Enums as Enums; 

class Refact extends DatabaseWriter 
{
    /** Id of quiz module. */
    private $moduleId;

    /** An array of courses that have attempts. */
    private $courses = array();

    /** Sets component name. */
    protected function set_component_name() : void
    {
        $this->componentName = Enums::QUIZ;
        $this->moduleId = $this->get_module_id();
    }

    /** Prepares data neccessary for database writer. */
    protected function prepare_neccessary_data() : void 
    {
        $attempts = $this->get_unchecked_attempts();

        foreach($attempts as $attempt)
        {
            $this->process_course_level($attempt);
            $this->process_teachers_level($attempt);

            
        }

        //print_r($attempts);
        print_r($this->courses);

        //$this->init_component_data();
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
        $needtodo->teacherid = $dataEntity->teacher->id;
        $needtodo->info = json_encode($dataEntity);
        $needtodo->updatetime = time();
        return $needtodo;
        */
    }

    /**
     * Returns id of quiz module.
     * 
     * @return int quiz module id 
     */
    private function get_module_id() : int 
    {
        global $DB;

        $where = array('name' => 'quiz');

        return $DB->get_field('modules', 'id', $where);
    }

    /**
     * Returns unchecked students quiz attempts.
     * 
     * @return array unchecked attempts 
     */
    private function get_unchecked_attempts() : ?array  
    {
        global $DB;

        $sql = 'SELECT qa.id AS attemptid, qa.userid AS studentid, 
                c.id AS courseid, c.fullname AS coursename, 
                cm.id AS coursemoduleid, 
                q.id AS quizid, q.name AS quizname
                FROM {quiz_attempts} AS qa 
                INNER JOIN {user} u 
                ON qa.userid = u.id 
                INNER JOIN {quiz} AS q 
                ON q.id = qa.quiz 
                INNER JOIN {course} AS c 
                ON c.id = q.course 
                INNER JOIN {course_modules} AS cm 
                ON cm.instance = q.id 
                WHERE qa.state = ? 
                AND qa.sumgrades IS NULL 
                AND cm.module = ? 
                AND cm.visible = 1 
                AND u.deleted = 0 
                AND u.suspended = 0 
                ORDER BY c.fullname, q.name ';

        $params = array('finished', $this->moduleId);

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Process attempt on course level.
     * 
     * @param stdClass attempt 
     */
    private function process_course_level(\stdClass $attempt) : void 
    {
        $course = new Course($this->courses, $attempt);
        $this->courses = $course->process_level();
    }

    /**
     * Process attempt on teachers level.
     * 
     * @param stdClass attempt 
     */
    private function process_teachers_level(\stdClass $attempt) : void 
    {
        $teachers = new Teachers($this->courses, $this->teachers, $attempt);
        $this->courses = $teachers->process_level(); 
    }






}
