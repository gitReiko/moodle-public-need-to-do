<?php 

namespace NTD\Classes\Components\Forum\DatabaseWriter;

class Teachers 
{
    /** An array of courses that have entity. */
    protected $courses;

    /** An unread post. */
    protected $unreadPost;

    /**
     * Prepares data for class.
     */
    function __construct(array $courses, \stdClass $unreadPost)
    {
        $this->courses = $courses;
        $this->unreadPost = $unreadPost;
    }

    /**
     * Processes an entity at the course level. 
     * 
     * @return array courses with processed data.
     */
    public function process_level()
    {
        foreach($this->courses as $course)
        {
            if($course->courseid == $this->unreadPost->courseid)
            {
                if($this->is_teacher_not_exists($course))
                {
                    $this->add_teacher_to_course($course);
                }
                else 
                {
                    $this->increase_teacher_unread($course);
                }
            }
        }

        return $this->courses;
    }

    /**
     * Returns true if teacher not exists in course. 
     * 
     * @param stdClass course 
     * 
     * @return bool 
     */
    private function is_teacher_not_exists(\stdClass $course) : bool 
    {
        foreach($course->teachers as $teacher)
        {
            if($teacher->id == $this->unreadPost->teacherid)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Adds teacher to course. 
     * 
     * @param stdClass course 
     */
    private function add_teacher_to_course(\stdClass &$course) : void 
    {
        $teacher = new \stdClass;
        $teacher->id = $this->unreadPost->teacherid;
        $teacher->name = $this->unreadPost->teachername;
        $teacher->email = $this->unreadPost->teacheremail;
        $teacher->phone1 = $this->unreadPost->teacherphone1;
        $teacher->phone2 = $this->unreadPost->teacherphone2;
        $teacher->unchecked = 0;
        $teacher->unreaded = $this->unreadPost->unreaded;
        $teacher->activities = array();

        $course->teachers[] = $teacher;
    }

    /**
     * Increases teacher unread posts by unread value.
     * 
     * @param stdClass course 
     */
    private function increase_teacher_unread(\stdClass &$course) : void 
    {
        foreach($course->teachers as $teacher)
        {
            if($teacher->id == $this->unreadPost->teacherid)
            {
                $teacher->unreaded += $this->unreadPost->unreaded;
            }
        }
    }


}
