<?php 

namespace NTD\Classes\Components\Forum\DatabaseWriter;

require_once __DIR__.'/../../../lib/components/database_writer/template/course.php';

use \NTD\Classes\Lib\Components\DatabaseWriter\Template\Course as CourseSkeleton;

/**
 * Processes an entity at the course level. 
 */
class Course extends CourseSkeleton 
{

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
        $course->untimelyCheck = 0;
        $course->timelyCheck = 0;
        $course->untimelyRead = $this->rawEntity->untimelyRead;
        $course->timelyRead = $this->rawEntity->timelyRead;
        $course->teachers = array();

        $this->courses[] = $course;
    }

    /**
     * Increases unckecked value of course by value of unreaded raw. 
     */
    protected function increase_course_unchecked() : void 
    {
        foreach($this->courses as $course)
        {
            if($course->courseid == $this->rawEntity->courseid)
            {
                $course->untimelyRead += $this->rawEntity->untimelyRead;
                $course->timelyRead += $this->rawEntity->timelyRead;
            }
        }
    }

}
