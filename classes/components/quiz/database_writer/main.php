<?php 

namespace NTD\Classes\Components\Quiz\DatabaseWriter;

require_once __DIR__.'/../../../lib/components/database_writer/main.php';
require_once __DIR__.'/../../../lib/components/database_writer/template/course.php';
require_once 'teachers.php';
require_once __DIR__.'/../../../lib/components/database_writer/template/activities.php';

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
        $this->componentName = Enums::QUIZ;

        $this->outdatedTimestamp = time() - (get_config('block_needtodo', 'working_past_days') * Enums::SECONDS_IN_DAY);

        $this->moduleId = $this->get_module_id();
    }

    /** Prepares data neccessary for database writer. */
    protected function prepare_neccessary_data() : void 
    {
        $attempts = $this->get_unchecked_attempts();
        $attempts = $this->determine_timely_check($attempts);

        foreach($attempts as $attempt)
        {
            $this->process_course_level($attempt);
            $this->process_teachers_level($attempt);
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
                q.id AS entityid, q.name AS entityname, 
                qa.timemodified AS senttime 
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
                AND qa.timemodified > ? 
                ORDER BY c.fullname, q.name ';

        $params = array('finished', $this->moduleId, $this->outdatedTimestamp);

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Determines the timely and untimely of the attempt.
     * 
     * @param array attempts
     * 
     * @return array attempts
     */
    private function determine_timely_check(?array $attempts) : ?array 
    {
        $untimelyPeriod = get_config('block_needtodo', 'days_to_check') * Enums::SECONDS_IN_DAY;
        $currentTime = time();

        foreach($attempts as &$attempt)
        {
            $untimely = $attempt->senttime + $untimelyPeriod;

            if($currentTime > $untimely)
            {
                $attempt->untimelyCheck = 1;
                $attempt->timelyCheck = 0;
            }
            else 
            {
                $attempt->untimelyCheck = 0;
                $attempt->timelyCheck = 1; 
            }

            $attempt->untimelyRead = 0;
            $attempt->timelyRead = 0;
        }

        return $attempts;
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
