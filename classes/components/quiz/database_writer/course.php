<?php 

namespace NTD\Classes\Components\Quiz\DatabaseWriter;

/**
 * Processes an attempt at the course level. 
 */
class Course  
{
    /** An array of courses that have attempts. */
    private $courses;

    /** An attempt of student which complete quiz */
    private $attempt;

    /**
     * Prepares data for class.
     */
    function __construct(array $courses, \stdClass $attempt)
    {
        $this->courses = $courses;
        $this->attempt = $attempt;
    }

    /**
     * Processes an attempt at the course level. 
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
    private function is_course_not_exists() : bool 
    {
        foreach($this->courses as $course)
        {
            if($course->courseid == $this->attempt->courseid)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Adds course to array. 
     */
    private function add_course_to_array() : void 
    {
        $course = new \stdClass;
        $course->courseid = $this->attempt->courseid;
        $course->coursename = $this->attempt->coursename;
        $course->uncheked = 1;
        $course->unreaded = 0;
        $course->teachers = array();

        $this->courses[] = $course;
    }

    /**
     * Increases unckecked value of course by 1. 
     */
    private function increase_course_unchecked() : void 
    {
        foreach($this->courses as $course)
        {
            if($course->courseid == $this->attempt->courseid)
            {
                $course->uncheked++;
            }
        }
    }

}
