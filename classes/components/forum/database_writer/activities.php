<?php 

namespace NTD\Classes\Components\Forum\DatabaseWriter;

class Activities 
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
     * Processes an unread post at the activities level. 
     * 
     * @return array courses with processed data.
     */
    public function process_level()
    {
        foreach($this->courses as $course)
        {
            if($course->courseid == $this->unreadPost->courseid)
            {
                foreach($course->teachers as $teacher)
                {
                    if($teacher->id == $this->unreadPost->teacherid)
                    {
                        if($this->is_activity_not_exists($teacher))
                        {
                            $this->add_activity_to_teacher($teacher);
                        }
                        else 
                        {
                            $this->increase_activity_unread($teacher);
                        }
                    }
                }
            }
        }

        return $this->courses;
    }

    /**
     * Returns true if activity not exists in teacher. 
     * 
     * @param stdClass teacher 
     * 
     * @return bool 
     */
    private function is_activity_not_exists(\stdClass $teacher) : bool 
    {
        foreach($teacher->activities as $activity)
        {
            if($activity->id == $this->unreadPost->forumid)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Adds activity to teacher array. 
     * 
     * @param stdClass teacher 
     */
    private function add_activity_to_teacher(\stdClass &$teacher) : void 
    {
        $activity = new \stdClass;
        $activity->id = $this->unreadPost->forumid;
        $activity->cmid = $this->unreadPost->forumcmid;
        $activity->name = $this->unreadPost->forumname;
        $activity->untimelyCheck = 0;
        $activity->timelyCheck = 0;
        $activity->untimelyRead = $this->unreadPost->untimelyRead;
        $activity->timelyRead = $this->unreadPost->timelyRead;

        $teacher->activities[] = $activity;
    }

    /**
     * Increases activity unread posts by unread value.
     * 
     * @param stdClass teacher 
     */
    private function increase_activity_unread(\stdClass &$teacher) : void 
    {
        foreach($teacher->activities as $activity)
        {
            if($activity->id == $this->unreadPost->forumid)
            {
                $activity->untimelyRead += $this->unreadPost->untimelyRead;
                $activity->timelyRead += $this->unreadPost->timelyRead;
            }
        }
    }

}
