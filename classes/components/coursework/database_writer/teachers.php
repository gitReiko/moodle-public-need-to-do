<?php 

namespace NTD\Classes\Components\Coursework\DatabaseWriter;

use NTD\Classes\Lib\Getters\Common as cGet;

/**
 * Processes an entity at the teacher level. 
 * 
 * !!! A unique non-standard Teachers class.
 */
class Teachers 
{
    /** An array of courses that have etities. */
    protected $courses;

    /** Undone coursework entity. */
    protected $coursework;

    /**
     * Prepares data for class.
     */
    function __construct(array $courses, \stdClass $coursework)
    {
        $this->courses = $courses;
        $this->coursework = $coursework;
    }

    /**
     * Processes an entity at the teacher level. 
     * 
     * A absent checker is an entity which representing entities that have no one to check.
     * 
     * @return array courses with processed data.
     */
    public function process_level()
    {
        foreach($this->courses as $course)
        {
            if($course->courseid == $this->coursework->courseid)
            {
                if($this->is_teachers_array_for_course_not_exists($course))
                {
                    $course->teachers = array();
                }

                if($this->is_teacher_already_in_array($course->teachers))
                {
                    $this->update_teacher_in_courses_array($course);
                }
                else 
                {
                    $this->add_teacher_to_courses_array($course);
                }
            }
        }

        return $this->courses;
    }

    /**
     * Returns true if teacher array for course not exists.
     * 
     * @return bool 
     */
    private function is_teachers_array_for_course_not_exists(\stdClass $course) : bool 
    {
        if(isset($course->teachers))
        {
            return false;
        }
        else 
        {
            return true;
        }
    }

    /**
     * Returns true if teacher is already in the teachers array.
     * 
     * @param array course teachers
     * 
     * @return bool 
     */
    private function is_teacher_already_in_array(array $teachers) : bool 
    {
        foreach($teachers as $teacher)
        {
            if($teacher->id == $this->coursework->teacherid)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Updates teacher in courses array.
     * 
     * @param array course 
     * 
     * @return void 
     */
    private function update_teacher_in_courses_array(\stdClass &$course) : void 
    {
        foreach($course->teachers as $teacher)
        {
            if($teacher->id == $this->coursework->teacherid)
            {
                $teacher->timelyCheck += (int)$this->coursework->timelyCheck;
                $teacher->untimelyCheck += (int)$this->coursework->untimelyCheck;
                $teacher->timelyRead += (int)$this->coursework->timelyRead;
                $teacher->untimelyRead += (int)$this->coursework->untimelyRead;

                // add activity level
            }
        }
    }

    /**
     * Adds teacher to courses array.
     * 
     * @param stdClass course
     * 
     * @return void 
     */
    private function add_teacher_to_courses_array(\stdClass &$course) : void 
    {
        $user = cGet::get_user($this->coursework->teacherid);

        $teacher = new \stdClass;
        $teacher->id = $user->id;
        $teacher->name = fullname($user, true);
        $teacher->phone1 = $user->phone1;
        $teacher->phone2 = $user->phone2;
        $teacher->timelyCheck = (int)$this->coursework->timelyCheck;
        $teacher->untimelyCheck = (int)$this->coursework->untimelyCheck;
        $teacher->timelyRead = (int)$this->coursework->timelyRead;
        $teacher->untimelyRead = (int)$this->coursework->untimelyRead;
        $teacher->activities = array();

        // add activity level

        $course->teachers[] = $teacher;
    }


}
