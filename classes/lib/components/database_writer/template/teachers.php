<?php 

namespace NTD\Classes\Lib\Components\DatabaseWriter\Template;

use \NTD\Classes\Lib\Enums as Enums; 

/**
 * Processes an entity at the teacher level. 
 */
abstract class Teachers  
{
    /** An array of courses that have etities. */
    protected $courses;

    /** An array of teachers with whow block works. */
    protected $teachers;

   /** The entity handled by the class. */
   protected $rawEntity;

    /**
     * Prepares data for class.
     */
    function __construct(array $courses, array $teachers, \stdClass $rawEntity)
    {
        $this->courses = $courses;
        $this->teachers = $teachers;
        $this->rawEntity = $rawEntity;
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
            if($course->courseid == $this->rawEntity->courseid)
            {
                $teachers = $this->get_teachers_checking_entity();

                if($this->is_teachers_checking_entity_exists($teachers))
                {
                    foreach($teachers as $teacher)
                    {
                        if($this->is_teacher_in_course_not_exists($course, $teacher))
                        {
                            $this->add_teacher_to_course($course, $teacher);
                        }
                        else 
                        {
                            $this->increase_teacher_undone($course, $teacher);
                        }

                        $this->process_activities_level($course, $teacher->id);
                    }
                }
                else 
                {
                    if($this->is_absent_checker_exists($course))
                    {
                        $this->increase_absent_checker_undone($course);
                    }
                    else 
                    {
                        $this->add_absent_checker_to_course($course);
                    }

                    $this->process_absent_teacher_activities_level($course);
                }
            }
        }

        return $this->courses;
    }

    /** 
     * Processes an entity at the activities level for teacher. 
     * 
     * @param stdClass course 
     * @param int checking teacher id  
     */
    abstract protected function process_activities_level(\stdClass &$course, int $checkingTeacherId) : void ;

    /**
     * Returns true if teacher can check quiz. 
     * 
     * @param int teacher 
     * 
     * @return bool 
     */
    abstract protected function is_user_can_check_entity(int $teacherId) : bool ;

    /**
     * Returns teachers checking entity. 
     * 
     * @return array teachers 
     */
    private function get_teachers_checking_entity() : ?array 
    {
        $checkers = array();

        foreach($this->teachers as $teacher)
        {
            if($this->is_user_can_check_entity($teacher->id))
            {
                if($this->is_teacher_and_student_are_in_same_group($teacher->id))
                {
                    $checker = new \stdClass;
                    $checker->id = $teacher->id;
                    $checker->name = $teacher->fullname;
                    $checker->email = $teacher->email;
                    $checker->phone1 = $teacher->phone1;
                    $checker->phone2 = $teacher->phone2;
                    $checker->untimelyCheck = $this->rawEntity->untimelyCheck;
                    $checker->timelyCheck = $this->rawEntity->timelyCheck;
                    $checker->untimelyRead = $this->rawEntity->untimelyRead;
                    $checker->timelyRead = $this->rawEntity->timelyRead;
                    $checker->activities = array();

                    $checkers[] = $checker;
                }
            }
        }

        return $checkers;
    }

    /**
     * Returns true if teachers checking entity exists. 
     * 
     * @param array teachers 
     * 
     * @return bool 
     */
    private function is_teachers_checking_entity_exists(array $teachers) : bool 
    {
        if(count($teachers))
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    /**
     * Returns true if teacher is in the same group with the student.
     * 
     * @param int teacher id 
     * 
     * @return bool 
     */
    private function is_teacher_and_student_are_in_same_group(int $teacherId) : bool 
    {
        $groups = $this->get_teacher_groups($this->rawEntity->courseid, $teacherId);

        foreach($groups as $group)
        {
            if(groups_is_member($group->id, $this->rawEntity->studentid))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns array of teacher groups.
     * 
     * @param int course id 
     * @param int teacher id 
     * 
     * @return array groups
     */
    private function get_teacher_groups(int $courseId, int $teacherId) : ?array 
    {
        global $DB;

        $sql = 'SELECT DISTINCT g.id 
                FROM {groups_members} AS gm 
                INNER JOIN {groups} AS g 
                ON g.id = gm.groupid 
                WHERE g.courseid = ? 
                AND gm.userid = ?';

        $params = array($courseId, $teacherId);

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Returns true if teacher exists in course. 
     * 
     * @param stdClass course 
     * @param stdClass teacher 
     * 
     * @return bool 
     */
    private function is_teacher_in_course_not_exists(\stdClass $course, \stdClass $teacher) : bool 
    {
        if(count($course->teachers) === 0)
        {
            return true;
        }

        foreach($course->teachers as $cTeacher)
        {
            if($cTeacher->id == $teacher->id)
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
     * @param stdClass teacher 
     */
    private function add_teacher_to_course(\stdClass &$course, \stdClass $teacher) : void 
    {
        $course->teachers[] = $teacher;
    }

    /**
     * Increases undone values of teacher entity values. 
     * 
     * @param stdClass course 
     * @param stdClass teacher 
     */
    private function increase_teacher_undone(\stdClass &$course, \stdClass $teacher) : void 
    {
        foreach($course->teachers as $cTeacher)
        {
            if($cTeacher->id == $teacher->id)
            {
                $cTeacher->untimelyCheck += $this->rawEntity->untimelyCheck;
                $cTeacher->timelyCheck += $this->rawEntity->timelyCheck;
                $cTeacher->untimelyRead += $this->rawEntity->untimelyRead;
                $cTeacher->timelyRead += $this->rawEntity->timelyRead;
            }
        }
    }

    /**
     * Returns true if absent checker exists. 
     * 
     * A absent checker is an entity representing checking entity that have no one to check.
     * 
     * @param stdClass course 
     * 
     * @return bool 
     */
    private function is_absent_checker_exists(\stdClass $course) : bool 
    {
        foreach($course->teachers as $teacher)
        {
            if($teacher->id === Enums::ABSENT_CHECKER_ID)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Increases undone values of absent checker by entity values. 
     * 
     * @param stdClass course 
     */
    private function increase_absent_checker_undone(\stdClass &$course) : void 
    {
        foreach($course->teachers as $teacher)
        {
            if($teacher->id === Enums::ABSENT_CHECKER_ID)
            {
                $teacher->untimelyCheck += $this->rawEntity->untimelyCheck;
                $teacher->timelyCheck += $this->rawEntity->timelyCheck;
                $teacher->untimelyRead += $this->rawEntity->untimelyRead;
                $teacher->timelyRead += $this->rawEntity->timelyRead;
            }
        }
    }

    /**
     * Adds absent checker to course. 
     * 
     * @param stdClass course 
     */
    private function add_absent_checker_to_course(\stdClass &$course) : void 
    {
        $absentChecker = new \stdClass;
        $absentChecker->id = Enums::ABSENT_CHECKER_ID;
        $absentChecker->name = get_string('no_one_to_check', 'block_needtodo');
        $absentChecker->email = null;
        $absentChecker->phone1 = null;
        $absentChecker->phone2 = null;
        $absentChecker->untimelyCheck = $this->rawEntity->untimelyCheck;
        $absentChecker->timelyCheck = $this->rawEntity->timelyCheck;
        $absentChecker->untimelyRead = $this->rawEntity->untimelyRead;
        $absentChecker->timelyRead = $this->rawEntity->timelyRead;
        $absentChecker->activities = array();

        $course->teachers[] = $absentChecker;
    }

    private function process_absent_teacher_activities_level(\stdClass &$course) : void 
    {
        foreach($course->teachers as $teacher)
        {
            if($teacher->id === Enums::ABSENT_CHECKER_ID)
            {
                $this->process_activities_level($course, $teacher->id);
            }
        }
    }

}
