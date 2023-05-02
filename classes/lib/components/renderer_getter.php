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
     * Returns component entities related to teacher. 
     * 
     * @param stdClass need to do block data 
     * 
     * @return array component entities related to teacher 
     */
    abstract protected function get_component_entities(\stdClass $data) : ?array ;

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
        $teachersData = $this->get_component_data();

        foreach($teachersData as $teacherData)
        {
            $data = json_decode($teacherData->info);

            $entities = $this->get_component_entities($data);

            foreach($entities as $entity)
            {
                $teacher = cGet::get_user($data->teacher->id);

                if($this->is_user_has_teacher_capability_in_component($entity->cmid, $teacher))
                {
                    $this->add_course_if_necessary($entity);
                    $this->add_teacher_if_necessary($data, $entity);
                    $this->add_activity_if_necessary($data, $entity);
                }
            }
        }
    }

    /**
     * Returns teachers data related to component.
     */
    private function get_component_data() : ?array 
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
    
            $params = array($this->componentType);
    
            return $DB->get_records_sql($sql, $params);
        }
        else
        {
            return null;
        }
    }

    /**
     * Adds course to array if necessary.
     * 
     * @param stdClass entity 
     */
    private function add_course_if_necessary(\stdClass $entity) : void 
    {
        if($this->is_course_not_exists($entity))
        {
            $this->add_course_to_array($entity);
        }
    }

    /**
     * Returns true if course not exists.
     * 
     * @param stdClass entity
     * 
     * @return bool 
     */
    private function is_course_not_exists(\stdClass $entity) : bool 
    {
        foreach($this->courses as $course)
        {
            if($course->id == $entity->courseId)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Adds course to courses array. 
     * 
     * @param stdClass entity 
     */
    private function add_course_to_array(\stdClass $entity) : void 
    {
        $course = new \stdClass;
        $course->id = $entity->courseId;
        $course->name = $entity->courseName;
        $course->teachers = array();

        $this->courses[] = $course;
    }

    /**
     * Adds teacher to array if necessary.
     * 
     * @param stdClass data 
     * @param stdClass entity
     */
    private function add_teacher_if_necessary(\stdClass $data, \stdClass $entity) : void 
    {
        foreach($this->courses as $course)
        {
            if($course->id == $entity->courseId)
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
     * @param stdClass entity
     */
    private function add_activity_if_necessary(\stdClass $data, \stdClass $entity) : void 
    {
        foreach($this->courses as $course)
        {
            if($course->id == $entity->courseId)
            {
                foreach($course->teachers as $teacher)
                {
                    if($teacher->id == $data->teacher->id)
                    {
                        if($this->is_activity_not_exists($teacher, $entity))
                        {
                            $this->add_activity_to_course($teacher, $entity);
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
     * @param stdClass entity
     * 
     * @return bool 
     */
    private function is_activity_not_exists(\stdClass $teacher, \stdClass $entity) : bool 
    {
        if(count($teacher->activities))
        {
            return true;
        }

        foreach($teacher->activities as $activity)
        {
            if(($activity->id == $entity->id) && ($activity->type == $entity->type))
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
     * @param stdClass entity 
     */
    private function add_activity_to_course(\stdClass $teacher, \stdClass $entity) : void 
    {
        $activity = new \stdClass;
        $activity->id = $entity->id;
        $activity->name = $entity->name;
        $activity->cmid = $entity->cmid;
        $activity->type = $this->componentType;
        $activity->link = $this->get_link_to_activity( $entity);

        if($this->componentType == Enums::FORUM)
        {
            $activity->unreadMessages = $entity->unreadedMessages;
        }
        else 
        {
            $activity->uncheckedWorks = $entity->needtocheck;
        }

        $teacher->activities[] = $activity;
    }

}
