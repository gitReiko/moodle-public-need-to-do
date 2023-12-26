<?php 

namespace NTD\Classes\Components\Coursework\DatabaseWriter;

use NTD\Classes\Lib\Getters\Common as cGetter;
use \NTD\Classes\Lib\Enums as Enums; 

class Coursework 
{
    /** Coursework to be done by teachers. */
    private $undone;

    /** Outdated timestamp. Defines by global setting "working_past_days" */
    private $outdatedTimestamp;

    /** Id of moodle module plugin */
    private $moduleId;

    private $untimelyWork;

    function __construct(int $outdatedTimestamp)
    {
        $this->outdatedTimestamp = $outdatedTimestamp;
        $this->moduleId = cGetter::get_module_id(Enums::COURSEWORK);

        $unchecked = $this->get_sent_for_check_courseworks();
        $unchecked = $this->filter_out_ready_courseworks($unchecked);
        $unchecked = $this->prepare_undone_for_parent_class($unchecked);
        $unchecked = $this->prepare_unchecked_for_parent_class($unchecked);

        $unreaded = $this->get_unreaded_messages();
        $unreaded = $this->filter_out_non_teacher_messages($unreaded);
        $unreaded = $this->prepare_undone_for_parent_class($unreaded);
        $unreaded = $this->prepare_unreaded_for_parent_class($unreaded);

        $this->undone = array_merge($unchecked, $unreaded);       
    }

    /**
     * Returns undone teachers work.
     * 
     * @return array undone
     */
    public function get_undone_teacher_work() : ?array 
    {
        return $this->undone;
    }

    /**
     * Returns sent for check courseworks.
     * 
     * @return array sent for check courseworks
     */
    private function get_sent_for_check_courseworks() : ?array 
    {
        global $DB;

        $sql = 'SELECT max(changetime) as senttime, coursework, student
                FROM {coursework_students_statuses} 
                WHERE type = ? 
                AND status = ? 
                AND changetime > ? 
                GROUP BY coursework, student
                ORDER BY coursework, student';

        $params = array(
            'coursework', 
            'sent_for_check',
            $this->outdatedTimestamp
        );

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Returns only unchecked courseworks.
     * 
     * @param array sent for check courseworks
     * 
     * @return array not checked courseworks
     */
    private function filter_out_ready_courseworks(?array $courseworks) : ?array 
    {
        $unchecked = array();

        foreach($courseworks as $coursework)
        {
            if($this->is_coursework_not_checked($coursework))
            {
                $unchecked[] = $coursework;
            }
        }

        return $unchecked;
    }


    /**
     * Returns true if coursework not checked.
     * 
     * @param stdClass coursework
     * 
     * @return bool 
     */
    private function is_coursework_not_checked(\stdClass $coursework) : bool 
    {
        global $DB;

        $sql = 'SELECT id 
                FROM {coursework_students_statuses}
                WHERE coursework = ?
                AND student = ? 
                AND status = ? 
                AND changetime > ?';

        $params = array(
            $coursework->coursework, 
            $coursework->student, 
            'ready',
            $coursework->senttime
        );

        return !$DB->record_exists_sql($sql, $params);
    }

    /**
     * Returns undone courseworks prepared for parent class.
     * 
     * @param array raw undone
     * 
     * @param array prepared undone
     */
    private function prepare_undone_for_parent_class(?array $courseworks) : ?array 
    {
        foreach($courseworks as &$coursework)
        {
            $data = $this->get_necessary_unchecked_data($coursework);

            $coursework->courseid = $data->courseid;
            $coursework->coursename = $data->coursename;
            $coursework->entityid = $coursework->coursework;
            $coursework->entityname = $data->entityname;

            $coursework->coursemoduleid = cGetter::get_course_module_id(
                $coursework->courseid,
                $this->moduleId,
                $coursework->entityid
            );
        }

        return $courseworks;
    }

    /**
     * Returns unchecked courseworks prepared for parent class.
     * 
     * @param array raw unchecked
     * 
     * @param array prepared unchecked
     */
    private function prepare_unchecked_for_parent_class(?array $courseworks) : ?array 
    {
        $untimelyCheck = time() - get_config('block_needtodo', 'days_to_check') * Enums::SECONDS_IN_DAY;

        foreach($courseworks as &$coursework)
        {
            $coursework->untimelyRead = 0;
            $coursework->timelyRead = 0;

            if($coursework->senttime > $untimelyCheck)
            {
                $coursework->untimelyCheck = 0;
                $coursework->timelyCheck = 1;
            }
            else 
            {
                $coursework->untimelyCheck = 1;
                $coursework->timelyCheck = 0;
            }
        }

        return $courseworks;
    }

    /**
     * Returns data necessary for parent class.
     * 
     * @param stdClass something undone
     * 
     * @return stdClass necessary data
     */
    private function get_necessary_unchecked_data(?\stdClass $undone) : ?\stdClass 
    {
        global $DB;

        $sql = 'SELECT cs.teacher, 
                c.id as courseid, c.fullname as coursename,
                cw.name as entityname 
                FROM {coursework_students} as cs 
                INNER JOIN {coursework} as cw 
                ON cs.coursework = cw.id
                INNER JOIN {course} as c 
                ON cw.course = c.id 
                WHERE cs.coursework = ?
                AND cs.student = ?';

        $params = array($undone->coursework, $undone->student);

        return $DB->get_record_sql($sql, $params);
    }

    /**
     * Returns unreaded coursework messages.
     * 
     * @return array unreaded messages
     */
    private function get_unreaded_messages() : ?array 
    {
        global $DB;

        $sql = 'SELECT id, coursework, userto, sendtime, userfrom as student 
                FROM {coursework_chat} 
                WHERE readed = ?
                AND sendtime > ?';
        $params = array(0, $this->outdatedTimestamp);

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Returns unreaded teachers chat messages.
     * 
     * @param array unreaded messages
     * 
     * @param array unreaded teachers messages
     */
    private function filter_out_non_teacher_messages(?array $unreaded) : ?array 
    {
        $teachers = array();

        foreach($unreaded as $message)
        {
            if($this->is_receiver_teacher($message))
            {
                $teachers[] = $message;
            }
        }

        return $teachers;
    }

    /**
     * Returns true if receiver it's teacher.
     * 
     * @param stdClass message 
     * 
     * @return bool 
     */
    private function is_receiver_teacher(\stdClass $message) : bool 
    {
        global $DB;

        $where = array(
            'coursework' => $message->coursework,
            'teacher' => $message->userto
        );

        return $DB->record_exists('coursework_teachers', $where);
    }

    /**
     * Returns unreaded courseworks prepared for parent class.
     * 
     * @param array raw unreaded
     * 
     * @param array prepared unreaded
     */
    private function prepare_unreaded_for_parent_class(?array $courseworks) : ?array 
    {
        $untimelyCheck = time() - get_config('block_needtodo', 'days_to_check') * Enums::SECONDS_IN_DAY;

        foreach($courseworks as &$coursework)
        {
            $coursework->untimelyCheck = 0;
            $coursework->timelyCheck = 0;

            if($coursework->senttime > $untimelyCheck)
            {
                $coursework->untimelyRead = 0;
                $coursework->timelyRead = 1;
            }
            else 
            {
                $coursework->untimelyRead = 1;
                $coursework->timelyRead = 0;
            }
        }

        return $courseworks;
    }

}
