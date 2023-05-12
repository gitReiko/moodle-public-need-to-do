<?php 

namespace NTD\Classes\Lib\Components;

use \NTD\Classes\Lib\Getters\Teachers as tGet;
use \NTD\Classes\Lib\Getters\Common as cGet;
use \NTD\Classes\Lib\Enums as Enums; 

abstract class RendererGetter 
{
    /** Block instance params. */
    protected $params;

    /** Teachers whose data needs to be extracted. */
    protected $teachers;

    /** Courses which are needed to render the block */
    protected $courses;

    /** Component type */
    protected $componentType;

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

        $this->componentType = $this->get_component_type();
        $this->add_component_to_courses();
    }

    /** 
     * Returns courses with component data.
     * 
     * @return array courses which are needed to render the block
     */
    public function get_courses_with_component_data() : array 
    {
        return $this->courses;
    }

    /**
     * Returns component type. 
     * 
     * @return string component type 
     */
    abstract protected function get_component_type() : string ;

    /**
     * Returns true if user has teacher capability in component. 
     * 
     * @param int entity id 
     * 
     * @return bool 
     */
    abstract protected function is_user_has_teacher_capability_in_component(int $entityId, \stdClass $teacher) : bool ;

    /**
     * Returns link to activity. 
     * 
     * @param stdClass entity 
     * 
     * @return string 
     */
    abstract protected function get_link_to_activity(\stdClass $entity) : string ;

    /**
     * Adds component entities to courses.
     */
    private function add_component_to_courses() : void 
    {
        $data = $this->get_component_data();

        foreach($data as $courseDB)
        {
            foreach($courseDB->teachers as $teacherDB)
            {
                foreach($teacherDB->activities as $activityDB)
                {
                    if($this->is_teacher_it_absent_checker($teacherDB->id) 
                        || $this->is_teacher_belongs_to_block_instance($teacherDB->id))
                    {
                        $this->add_course_if_necessary($courseDB);
                        $this->add_teacher_if_necessary($courseDB, $teacherDB);
                        $this->add_activity_if_necessary($courseDB, $teacherDB, $activityDB);
                    }
                }
            }
        }
    }

    /**
     * Returns teachers data related to component.
     * 
     * @return array data 
     */
    private function get_component_data() : ?array 
    {
        // from all courses or from category courses
        // for now only from all courses 
        $data = $this->get_component_data_from_all_courses();

        $data = $this->decode_data_from_json($data);

        return $data;
    }

    /**
     * Returns component data from all courses.
     * 
     * @return array data 
     */
    private function get_component_data_from_all_courses() : ?array 
    {
        global $DB;

        $where = array('component' => $this->componentType);

        return $DB->get_records('block_needtodo', $where);
    }

    /**
     * Returns decoded from json data.
     * 
     * @param stdClass data 
     * 
     * @return array decoded data 
     */
    private function decode_data_from_json(?array $data) : ?array 
    {
        $decoded = array();

        foreach($data as $value)
        {
            $decoded[] = json_decode($value->info);
        }

        return $decoded;
    }

    /**
     * Returns true if teacher it's absent checker. 
     * 
     * @param int teacher id 
     * 
     * @return bool 
     */
    private function is_teacher_it_absent_checker(int $teacherId) : bool 
    {
        if($teacherId == Enums::ABSENT_CHECKER_ID)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    /**
     * Returns true if teacher belongs to block instance. 
     * 
     * @param int $teacherId
     * 
     * @return bool 
     */
    private function is_teacher_belongs_to_block_instance(int $teacherId) : bool 
    {
        foreach($this->teachers as $teacher)
        {
            if($teacher->id == $teacherId)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Adds course to array if necessary.
     * 
     * @param stdClass course from database 
     */
    private function add_course_if_necessary(\stdClass $courseDB) : void 
    {
        if($this->is_course_not_exists($courseDB))
        {
            $this->add_course_to_array($courseDB);
        }
    }

    /**
     * Returns true if course not exists.
     * 
     * @param stdClass course from database 
     * 
     * @return bool 
     */
    private function is_course_not_exists(\stdClass $courseDB) : bool 
    {
        foreach($this->courses as $course)
        {
            if($course->id == $courseDB->courseid)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Adds course to courses array. 
     * 
     * @param stdClass course from database  
     */
    private function add_course_to_array(\stdClass $courseDB) : void 
    {
        $course = new \stdClass;
        $course->id = $courseDB->courseid;
        $course->name = $courseDB->coursename;
        $course->unchecked = $courseDB->unchecked;
        $course->unreaded = $courseDB->unreaded;
        $course->teachers = array();

        $this->courses[] = $course;
    }

    /**
     * Adds teacher to array if necessary.
     * 
     * @param stdClass course from database  
     * @param stdClass teacher from database  
     */
    private function add_teacher_if_necessary(\stdClass $courseDB, \stdClass $teacherDB) : void 
    {
        foreach($this->courses as $course)
        {
            if($course->id == $courseDB->courseid)
            {
                if($this->is_teacher_not_exists($course, $teacherDB))
                {
                    $this->add_teacher_to_course($course, $teacherDB);
                }
            }
        }
    }

    /**
     * Returns true if teacher not exists in course.
     * 
     * @param stdClass course 
     * @param stdClass teacher from database 
     * 
     * @return bool 
     */
    private function is_teacher_not_exists(\stdClass $course, \stdClass $teacherDB) : bool 
    {
        foreach($course->teachers as $teacher)
        {
            if($teacher->id == $teacherDB->id)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Adds teacher to teachers array in course.
     * 
     * @param stdClass course 
     * @param stdClass teacher from database 
     */
    private function add_teacher_to_course(\stdClass $course, \stdClass $teacherDB) : void 
    {
        $teacher = new \stdClass;
        $teacher->id = $teacherDB->id;
        $teacher->name = $teacherDB->name;
        $teacher->email = $teacherDB->email;
        $teacher->phone1 = $teacherDB->phone1;
        $teacher->phone2 = $teacherDB->phone2;
        $teacher->unchecked = $teacherDB->unchecked;
        $teacher->unreaded = $teacherDB->unreaded;
        $teacher->activities = array();

        $course->teachers[] = $teacher;
    }

    /**
     * Adds activity teacher in course if necessary.
     * 
     * @param stdClass course from database 
     * @param stdClass teacher from database 
     * @param stdClass activity from database  
     */
    private function add_activity_if_necessary(\stdClass $courseDB, \stdClass $teacherDB, \stdClass $activityDB) : void 
    {
        foreach($this->courses as $course)
        {
            if($course->id == $courseDB->courseid)
            {
                foreach($course->teachers as $teacher)
                {
                    if($teacher->id == $teacherDB->id)
                    {
                        if($this->is_activity_not_exists($teacher, $activityDB))
                        {
                            $this->add_activity_to_course($teacher, $activityDB);
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
     * @param stdClass activity from database 
     * 
     * @return bool 
     */
    private function is_activity_not_exists(\stdClass $teacher, \stdClass $activityDB) : bool 
    {
        if(count($teacher->activities))
        {
            return true;
        }

        foreach($teacher->activities as $activity)
        {
            if(($activity->id == $activityDB->id) && ($activity->type == $activityDB->type))
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Adds entity to activities in teacher.
     * 
     * @param stdClass teacher
     * @param stdClass activity from database  
     */
    private function add_activity_to_course(\stdClass $teacher, \stdClass $activityDB) : void 
    {
        $activity = new \stdClass;
        $activity->id = $activityDB->id;
        $activity->name = $activityDB->name;
        $activity->cmid = $activityDB->cmid;
        $activity->type = $this->componentType;
        $activity->link = $this->get_link_to_activity($activityDB);
        $activity->unchecked = $activityDB->unchecked;
        $activity->unreaded = $activityDB->unreaded;

        $teacher->activities[] = $activity;
    }


}
