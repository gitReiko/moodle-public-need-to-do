<?php 

namespace NTD\Classes\Lib\Components\DatabaseWriter\Template;

/**
 * Processes an entity at the course level. 
 */
class Course  
{
    /** An array of courses that have entity. */
    protected $courses;

    /** The entity handled by the class */
    protected $rawEntity;

    /**
     * Prepares data for class.
     */
    function __construct(array $courses, \stdClass $rawEntity)
    {
        $this->courses = $courses;
        $this->rawEntity = $rawEntity;
    }

    /**
     * Processes an entity at the course level. 
     * 
     * @return array courses with processed data.
     */
    public function process_level()
    {
        if($this->is_course_not_exists())
        {
            $this->add_course_to_array();
        }
        else 
        {
            $this->increase_course_unchecked();
        }

        return $this->courses;
    }

    /**
     * Returns true if course exists in array. 
     * 
     * @return bool 
     */
    protected function is_course_not_exists() : bool 
    {
        foreach($this->courses as $course)
        {
            if($course->courseid == $this->rawEntity->courseid)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Adds course to array. 
     */
    protected function add_course_to_array() : void 
    {
        $course = new \stdClass;
        $course->courseid = $this->rawEntity->courseid;
        $course->coursename = $this->rawEntity->coursename;
        $course->untimelyCheck = $this->rawEntity->untimelyCheck;
        $course->timelyCheck = $this->rawEntity->timelyCheck;
        $course->untimelyRead = 0;
        $course->timelyRead = 0;
        $course->teachers = array();

        $this->courses[] = $course;
    }

    /**
     * Increases unckecked value of course by 1. 
     */
    protected function increase_course_unchecked() : void 
    {
        foreach($this->courses as $course)
        {
            if($course->courseid == $this->rawEntity->courseid)
            {
                $course->untimelyCheck += $this->rawEntity->untimelyCheck;
                $course->timelyCheck += $this->rawEntity->timelyCheck;
            }
        }
    }

}
