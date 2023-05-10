<?php 

namespace NTD\Classes\Components\Quiz\DatabaseWriter;

/**
 * Processes an attempt at the activities level. 
 */
class Activities  
{
    /** A course that have attempts. */
    private $course;

    /** An id of teacher who can check attempt. */
    private $checkingTeacherId;

    /** An attempt of student which complete quiz. */
    private $attempt;

    /**
     * Prepares data for class.
     */
    function __construct(\stdClass &$course, int $checkingTeacherId, \stdClass $attempt)
    {
        $this->course = $course;
        $this->checkingTeacherId = $checkingTeacherId;
        $this->attempt = $attempt;
    }

    /**
     * Processes an attempt at the activities level. 
     */
    public function process_level() : void   
    {
        foreach($this->course->teachers as &$teacher)
        {
            if($this->is_course_teacher_it_checking_teacher($teacher))
            {
                if($this->is_activity_exists($teacher))
                {
                    $this->increase_activity_unchecked($teacher);
                }
                else 
                {
                    $this->add_activity_to_array($teacher);
                }
            }
        }
    }

    /**
     * Returns true if course teacher is checking teacher. 
     * 
     * @param stdClass course teacher 
     * 
     * @return bool 
     */
    private function is_course_teacher_it_checking_teacher(\stdClass $teacher) : bool 
    {
        if($teacher->id === $this->checkingTeacherId)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    /**
     * Returns true if activity exists in teacher array. 
     * 
     * @param stdClass course teacher 
     * 
     * @return bool 
     */
    private function is_activity_exists(\stdClass $teacher) : bool 
    {
        foreach($teacher->activities as $activity)
        {
            if($activity->id == $this->attempt->quizid)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Increases unckecked value of activity by 1. 
     * 
     * @param stdClass course teacher 
     */
    private function increase_activity_unchecked(\stdClass &$teacher) : void 
    {
        foreach($teacher->activities as $activity)
        {
            if($activity->id == $this->attempt->quizid)
            {
                $activity->unchecked++;
            }
        }
    }

    /**
     * Adds activity to teacher array.
     * 
     * @param stdClass course teacher 
     */
    private function add_activity_to_array(\stdClass &$teacher) : void 
    {
        $activity = new \stdClass;
        $activity->id = $this->attempt->quizid;
        $activity->cmid = $this->attempt->coursemoduleid;
        $activity->name = $this->attempt->quizname;
        $activity->unchecked = 1;
        $activity->unreaded = 0;

        $teacher->activities[] = $activity;
    }


}
