<?php 

namespace NTD\Classes\Components\Quiz\DatabaseWriter;

/**
 * Processes an attempt at the teacher level. 
 */
class Teachers  
{
    /** An array of courses that have attempts. */
    private $courses;

    /** An array of teachers with whow block works. */
    private $teachers;

    /** An attempt of student to complete quiz. */
    private $attempt;

    /**
     * Prepares data for class.
     */
    function __construct(array $courses, array $teachers, \stdClass $attempt)
    {
        $this->courses = $courses;
        $this->teachers = $teachers;
        $this->attempt = $attempt;
    }

    /**
     * Processes an attempt at the course level. 
     * 
     * @return array courses with processed data.
     */
    public function process_level()
    {
        foreach($this->courses as $course)
        {
            if($course->courseid == $this->attempt->courseid)
            {
                $teachers = $this->get_teachers_who_check_attempt();

                if(count($teachers))
                {
                    foreach($teachers as $teacher)
                    {
                        if($this->is_teacher_in_course_not_exists($course, $teacher))
                        {
                            $this->add_teacher_to_course($course, $teacher);
                        }
                        else 
                        {
                            $this->increase_teacher_unchecked($course, $teacher);
                        }
                    }
                }
                else 
                {
                    // add empty teacher 
                }




                //print_r($teachers);
            }
        }

        return $this->courses;
    }

    /**
     * Returns teachers who check attempt. 
     * 
     * @return array teachers 
     */
    private function get_teachers_who_check_attempt() : ?array 
    {
        $checkers = array();

        foreach($this->teachers as $teacher)
        {
            if($this->is_user_can_check_quiz($teacher->id))
            {
                if($this->is_teacher_and_student_are_in_same_group($teacher->id))
                {
                    $checker = new \stdClass;
                    $checker->id = $teacher->id;
                    $checker->name = $teacher->fullname;
                    $checker->email = $teacher->email;
                    $checker->phone1 = $teacher->phone1;
                    $checker->phone2 = $teacher->phone2;
                    $checker->uncheked = 1;
                    $checker->unreaded = 0;
                    $checker->activities = array();

                    $checkers[] = $checker;
                }
            }
        }

        return $checkers;
    }

    /**
     * Returns true if teacher can check quiz. 
     * 
     * @param int teacher 
     * 
     * @return bool 
     */
    private function is_user_can_check_quiz(int $teacherId) : bool 
    {
        $contextmodule = \context_module::instance($this->attempt->coursemoduleid);

        if(has_capability('mod/quiz:grade', $contextmodule, $teacherId)) 
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    /**
     * Returns true if teacher can check quiz attempt.
     * 
     * @param int teacher id 
     * 
     * @return bool 
     */
    private function is_teacher_and_student_are_in_same_group(int $teacherId) : bool 
    {
        $groups = $this->get_teacher_groups($this->attempt->courseid, $teacherId);

        foreach($groups as $group)
        {
            if(groups_is_member($group->id, $this->attempt->studentid))
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
     * Increases unckecked value of teacher by 1. 
     */
    private function increase_teacher_unchecked(\stdClass &$course, \stdClass $teacher) : void 
    {
        foreach($course->teachers as $cTeacher)
        {
            if($cTeacher->id == $teacher->id)
            {
                $cTeacher->uncheked++;
            }
        }
    }







}
