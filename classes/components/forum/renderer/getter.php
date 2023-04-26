<?php 

namespace NTD\Classes\Components\Forum\Renderer;

use \NTD\Classes\Lib\Getters\Teachers as tGet;
use \NTD\Classes\Lib\Enums as Enums; 

class Getter 
{

    /** Block instance params. */
    protected $params;

    /** Teachers whose data needs to be extracted. */
    protected $teachers;

    /** Courses which are needed to render the block */
    protected $courses;

    /**
     * Prepares data neccessary for class.
     * 
     * @param stdClass block instance params
     * @param array teachers whose data needs to be extracted
     * @param array courses which are needed to render the block
     */
    function __construct($params, $teachers, $courses)
    {
        $this->params = $params;
        $this->teachers = $teachers;
        $this->courses = $courses;

        $this->add_forums_to_courses();
        $this->count_teachers_unread_messages();
        $this->count_courses_unread_messages();
    }

    /** 
     * Returns courses with added forums.
     * 
     * @return array courses which are needed to render the block
     */
    public function get_courses_with_added_forums() : array 
    {
        return $this->courses;
    }

    /**
     * Adds forums to courses.
     */
    private function add_forums_to_courses() : void 
    {
        $teachersData = $this->get_teachers_forum_data();

        foreach($teachersData as $teacherData)
        {
            $data = json_decode($teacherData->info);

            foreach($data->forums as $forum)
            {
                if($this->is_user_has_manager_capability_in_forum($forum->cmid))
                {
                    $this->add_course_if_necessary($forum);
                    $this->add_teacher_if_necessary($data, $forum);
                    $this->add_activity_if_necessary($data, $forum);
                }
            }
        }
    }

    /**
     * Returns teachers data related to forum.
     */
    private function get_teachers_forum_data() 
    {
        global $DB;

        $teachersInCondition = tGet::get_where_in_condition_from_teachers_array($this->teachers);

        // Teachers may not exist
        if($teachersInCondition)
        {
            $sql = "SELECT * 
            FROM {block_needtodo} 
            WHERE component = ? 
            AND teacherid {$teachersInCondition}";

            $params = array(Enums::FORUM);

            return $DB->get_records_sql($sql, $params);
        }
        else
        {
            return null;
        }
    }

    /**
     * Returns true if the user sees the block has forum manager permissions.
     * 
     * @param int course module id 
     * 
     * @return bool 
     */
    private function is_user_has_manager_capability_in_forum(int $cmid) : bool 
    {
        return has_capability('mod/forum:addnews', \context_module::instance($cmid));
    }

    /**
     * Adds course to array if necessary.
     * 
     * @param stdClass forum 
     */
    private function add_course_if_necessary(\stdClass $forum) : void 
    {
        if($this->is_course_not_exists($forum))
        {
            $this->add_course_to_array($forum);
        }
    }

    /**
     * Returns true if course not exists.
     * 
     * @param stdClass forum
     * 
     * @return bool 
     */
    private function is_course_not_exists(\stdClass $forum) : bool 
    {
        foreach($this->courses as $course)
        {
            if($course->id == $forum->courseId)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Adds course to courses array. 
     * 
     * @param stdClass forum 
     */
    private function add_course_to_array(\stdClass $forum) : void 
    {
        $course = new \stdClass;
        $course->id = $forum->courseId;
        $course->name = $forum->courseName;
        $course->teachers = array();

        $this->courses[] = $course;
    }

    /**
     * Adds teacher to array if necessary.
     * 
     * @param stdClass data 
     * @param stdClass forum
     */
    private function add_teacher_if_necessary(\stdClass $data, \stdClass $forum) : void 
    {
        foreach($this->courses as $course)
        {
            if($course->id == $forum->courseId)
            {
                if($this->is_teacher_not_exists($data, $course))
                {
                    $this->add_teacher_to_course($data, $course);
                }
            }
        }
    }

    /**
     * Returns true if teacher not exists in course.
     * 
     * @param stdClass data 
     * @param stdClass course
     * 
     * @return bool 
     */
    private function is_teacher_not_exists(\stdClass $data, \stdClass $course) : bool 
    {
        if(count($course->teachers))
        {
            return true;
        }

        foreach($course->teachers as $teacher)
        {
            if($data->teacher->id == $teacher->id)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Adds teacher to teachers array in course.
     * 
     * @param stdClass data 
     * @param stdClass course
     */
    private function add_teacher_to_course(\stdClass $data, \stdClass $course) : void 
    {
        $teacher = new \stdClass;
        $teacher->id = $data->teacher->id;
        $teacher->name = $data->teacher->name;
        $teacher->email = $data->teacher->email;
        $teacher->phone1 = $data->teacher->phone1;
        $teacher->phone2 = $data->teacher->phone2;
        $teacher->activities = array();

        $course->teachers[] = $teacher;
    }

    /**
     * Adds activity teacher in course if necessary.
     * 
     * @param stdClass data 
     * @param stdClass forum
     */
    private function add_activity_if_necessary(\stdClass $data, \stdClass $forum) : void 
    {
        foreach($this->courses as $course)
        {
            if($course->id == $forum->courseId)
            {
                foreach($course->teachers as $teacher)
                {
                    if($teacher->id == $data->teacher->id)
                    {
                        if($this->is_activity_not_exists($teacher, $forum))
                        {
                            $this->add_forum_to_course($teacher, $forum);
                        }
                    }
                }
            }
        }
    }

    /**
     * Returns true if teacher not exists in teacher.
     * 
     * @param stdClass teacher 
     * @param stdClass forum
     * 
     * @return bool 
     */
    private function is_activity_not_exists(\stdClass $teacher, \stdClass $forum) : bool 
    {
        if(count($teacher->activities))
        {
            return true;
        }

        foreach($teacher->activities as $activity)
        {
            if($activity->id == $forum->id)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Adds forum to activities in teacher.
     * 
     * @param stdClass teacher
     * @param stdClass forum 
     */
    private function add_forum_to_course(\stdClass $teacher, \stdClass $forum) : void 
    {
        $activity = new \stdClass;
        $activity->id = $forum->id;
        $activity->name = $forum->name;
        $activity->cmid = $forum->cmid;
        $activity->type = Enums::FORUM;
        $activity->link = '/mod/forum/view.php?id='.$forum->cmid;
        $activity->unreadMessages = $forum->unreadedMessages;

        $teacher->activities[] = $activity;
    }

    /** 
     * Counts teachers unread messages. 
     */
    private function count_teachers_unread_messages() : void 
    {
        foreach($this->courses as $course)
        {
            foreach($course->teachers as $teacher)
            {
                $teacher->unreadMessages = $this->count_teacher_unread_messages($teacher->activities);
            }
        }
    }

    /**
     * Returns count of unread messages.
     * 
     * @param array activities 
     * 
     * @return int count of unread messages 
     */
    private function count_teacher_unread_messages(array $activities) : int 
    {
        $count = 0;

        foreach($activities as $activity)
        {
            if($activity->type == Enums::FORUM)
            {
                $count += $activity->unreadMessages;
            }
        }

        return $count;
    }

    /**
     * Counts courses unread messages. 
     */
    private function count_courses_unread_messages() : void 
    {
        foreach($this->courses as $course)
        {
            $course->unreadMessages = 0;

            foreach($course->teachers as $teacher)
            {
                $course->unreadMessages += $teacher->unreadMessages;
            }
        }
    }


}
