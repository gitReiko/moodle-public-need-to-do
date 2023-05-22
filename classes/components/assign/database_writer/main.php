<?php 

namespace NTD\Classes\Components\Assign\DatabaseWriter;

require_once __DIR__.'/../../../lib/components/database_writer/main.php';
require_once __DIR__.'/../../../lib/components/database_writer/template/course.php';
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
        $this->componentName = Enums::ASSIGN;

        $this->outdatedTimestamp = time() - (get_config('block_needtodo', 'working_past_days') * Enums::SECONDS_IN_DAY);

        $this->moduleId = $this->get_module_id();
    }

    /** Prepares data neccessary for database writer. */
    protected function prepare_neccessary_data() : void 
    {
        $submissions = $this->get_unchecked_submissions();
        $submissions = $this->determine_timely_check($submissions);

        foreach($submissions as $submission)
        {
            $this->process_course_level($submission);
            $this->process_teachers_level($submission);
            // process actvities level is in teacher level
        }
        
        print_r($this->courses);
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

    }

    /**
     * Returns id of quiz module.
     * 
     * @return int quiz module id 
     */
    private function get_module_id() : int 
    {
        global $DB;

        $where = array('name' => 'assign');

        return $DB->get_field('modules', 'id', $where);
    }

    /**
     * Returns unchecked student submissions. 
     * 
     * @return array works 
     */
    private function get_unchecked_submissions() : ?array 
    {
        global $DB;

        $sql = 'SELECT asu.id AS submissionid, 
                a.id AS assignid, a.name AS assignname, 
                cm.id AS coursemoduleid, c.id AS courseid, c.fullname AS coursename, 
                asu.timemodified AS senttime, asu.userid AS studentid 
                FROM {assign} AS a 
                INNER JOIN {course_modules} AS cm 
                ON a.id = cm.instance 
                INNER JOIN {course} AS c 
                ON cm.course = c.id 
                INNER JOIN {assign_submission} AS asu 
                ON a.id = asu.assignment 
                INNER JOIN {assign_grades} AS ag 
                ON a.id = ag.assignment 
                INNER JOIN {user} u 
                ON asu.userid = u.id 
                WHERE cm.module = ? 
                AND cm.visible = 1 
                AND asu.status = ?
                AND asu.latest = 1 
                AND asu.timemodified > ? 
                AND asu.attemptnumber = ag.attemptnumber 
                AND asu.userid = ag.userid 
                AND ag.grade IS NULL 
                AND u.deleted = 0 
                AND u.suspended = 0 ';

        $params = array($this->moduleId, 'submitted', $this->outdatedTimestamp);

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Determines the timely and untimely of the submission.
     * 
     * @param array submissions
     * 
     * @return array submissions
     */
    private function determine_timely_check(?array $submissions) : ?array 
    {
        $untimelyPeriod = get_config('block_needtodo', 'days_to_check') * Enums::SECONDS_IN_DAY;
        $currentTime = time();

        foreach($submissions as &$submission)
        {
            $untimely = $submission->senttime + $untimelyPeriod;

            if($currentTime > $untimely)
            {
                $submission->untimelyCheck = 1;
                $submission->timelyCheck = 0;
            }
            else 
            {
                $submission->untimelyCheck = 0;
                $submission->timelyCheck = 1; 
            }

            $submission->untimelyRead = 0;
            $submission->timelyRead = 0;
        }

        return $submissions;
    }

    /**
     * Process submission on course level.
     * 
     * @param stdClass submission 
     */
    private function process_course_level(\stdClass $submission) : void 
    {
        $course = new Course($this->courses, $submission);
        $this->courses = $course->process_level();
    }

    /**
     * Process assign submission on teachers level.
     * 
     * @param stdClass assign submission 
     */
    private function process_teachers_level(\stdClass $submission) : void 
    {
        $teachers = new Teachers($this->courses, $this->teachers, $submission);
        $this->courses = $teachers->process_level(); 
    }

}
