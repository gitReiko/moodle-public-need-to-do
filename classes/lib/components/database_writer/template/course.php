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

        if(isset($this->rawEntity->untimelyCheck))
        {
            $course->untimelyCheck = $this->rawEntity->untimelyCheck;
        }
        else 
        {
            $course->untimelyCheck = 0;
        }

        if(isset($this->rawEntity->timelyCheck))
        {
            $course->timelyCheck = $this->rawEntity->timelyCheck;
        }
        else 
        {
            $course->timelyCheck = 0;
        }

        if(isset($this->rawEntity->untimelyRead))
        {
            $course->untimelyRead = (int)$this->rawEntity->untimelyRead;
        }
        else 
        {
            $course->untimelyRead = 0;
        }

        if(isset($this->rawEntity->timelyRead))
        {
            $course->timelyRead = (int)$this->rawEntity->timelyRead;
        }
        else 
        {
            $course->timelyRead = 0;
        }

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
                if(isset($this->rawEntity->untimelyCheck))
                {
                    $course->untimelyCheck += $this->rawEntity->untimelyCheck;
                }

                if(isset($this->rawEntity->timelyCheck))
                {
                    $course->timelyCheck += $this->rawEntity->timelyCheck;
                }

                if(isset($this->rawEntity->untimelyRead))
                {
                    $course->untimelyRead += (int)$this->rawEntity->untimelyRead;
                }

                if(isset($this->rawEntity->timelyRead))
                {
                    $course->timelyRead += (int)$this->rawEntity->timelyRead;
                }
            }
        }
    }

}
