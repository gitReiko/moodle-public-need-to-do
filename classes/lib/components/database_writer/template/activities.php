<?php 

namespace NTD\Classes\Lib\Components\DatabaseWriter\Template;

/**
 * Processes an entity at the activities level. 
 */
class Activities  
{
    /** A course that have entities. */
    private $course;

    /** An id of teacher cheking entity. */
    private $checkingTeacherId;

    /** An entity of unchecked student work. */
    private $rawEntity;

    /**
     * Prepares data for class.
     */
    function __construct(\stdClass &$course, int $checkingTeacherId, \stdClass $rawEntity)
    {
        $this->course = $course;
        $this->checkingTeacherId = $checkingTeacherId;
        $this->rawEntity = $rawEntity;
    }

    /**
     * Processes an entity at the activities level. 
     */
    public function process_level() : void   
    {
        foreach($this->course->teachers as &$teacher)
        {
            if($this->is_course_teacher_it_checking_teacher($teacher))
            {
                if($this->is_activity_exists($teacher))
                {
                    $this->increase_activity_undone($teacher);
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
        if($teacher->id == $this->checkingTeacherId)
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
            if($activity->id == $this->rawEntity->entityid)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Increases undone values of activity by entity values. 
     * 
     * @param stdClass course teacher 
     */
    private function increase_activity_undone(\stdClass &$teacher) : void 
    {
        foreach($teacher->activities as $activity)
        {
            if($activity->id == $this->rawEntity->entityid)
            {
                $activity->untimelyCheck += $this->rawEntity->untimelyCheck;
                $activity->timelyCheck += $this->rawEntity->timelyCheck;
                $activity->untimelyRead += $this->rawEntity->untimelyRead;
                $activity->timelyRead += $this->rawEntity->timelyRead;
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
        $activity->id = $this->rawEntity->entityid;
        $activity->cmid = $this->rawEntity->coursemoduleid;
        $activity->name = $this->rawEntity->entityname;
        $activity->untimelyCheck = $this->rawEntity->untimelyCheck;
        $activity->timelyCheck = $this->rawEntity->timelyCheck;
        $activity->untimelyRead = $this->rawEntity->untimelyRead;
        $activity->timelyRead = $this->rawEntity->timelyRead;

        $teacher->activities[] = $activity;
    }


}
