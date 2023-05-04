<?php 

namespace NTD\Classes\Components\Quiz\DatabaseWriter;

require_once __DIR__.'/../../../lib/components/database_writer.php';
require_once 'course.php';

use \NTD\Classes\Lib\Components\DatabaseWriter;
use \NTD\Classes\Lib\Enums as Enums; 

class Refact extends DatabaseWriter 
{
    /** Id of quiz module. */
    private $moduleId;

    /** An array of courses that have attempts. */
    private $courses = array();

    /** Sets component name. */
    protected function set_component_name() : void
    {
        $this->componentName = Enums::QUIZ;
        $this->moduleId = $this->get_module_id();
    }

    /** Prepares data neccessary for database writer. */
    protected function prepare_neccessary_data() : void 
    {
        $attempts = $this->get_unchecked_attempts();

        foreach($attempts as $attempt)
        {
            $this->process_course_level($attempt);
            // 

            foreach($this->courses as $course)
            {
                if($course->courseid == $attempt->courseid)
                {
                    $teachers = $this->get_teachers_who_check_attempt($attempt);

                    if(count($teachers))
                    {
                        foreach($teachers as $teacher)
                        {
                            if($this->is_teacher_in_course_exists($course, $teacher))
                            {
                                // increase unchecked
                            }
                            else 
                            {
                                $course->teachers[] = $teacher;
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
        }

        //print_r($attempts);
        print_r($this->courses);


        /*
        foreach($attempts as $attempt)
        {
            $teacherAttempt = false;

            $quiz = $this->get_attempt_quiz($attempt);

            foreach($this->teachers as $teacher)
            {
                if($this->is_user_can_check_quiz($teacher, $quiz))
                {
                    $this->handle_teacher_attempt($teacher, $quiz);

                    $teacherAttempt = true;
                }
            }

            if($teacherAttempt == false)
            {
                // add zero teacher 
            }
        }

        
        $this->add_courses_to_teachers();
        $this->add_quizes_to_teacher_courses();
        $this->add_count_of_unchecked_student_works();

        $this->init_component_data();
        */
    }

    /**
     * Returns the record to be written to the database.
     * 
     * @param stdClass dataEntity
     * 
     * @return stdClass needtodo record for database
     */
    protected function get_needtodo_record(\stdClass $dataEntity) : \stdClass 
    {
        /*
        $needtodo = new \stdClass;
        $needtodo->component = $this->componentName;
        $needtodo->teacherid = $dataEntity->teacher->id;
        $needtodo->info = json_encode($dataEntity);
        $needtodo->updatetime = time();
        return $needtodo;
        */
    }

    /**
     * Returns id of quiz module.
     * 
     * @return int quiz module id 
     */
    private function get_module_id() : int 
    {
        global $DB;

        $where = array('name' => 'quiz');

        return $DB->get_field('modules', 'id', $where);
    }

    /**
     * Returns unchecked students quiz attempts.
     * 
     * @return array unchecked attempts 
     */
    private function get_unchecked_attempts() : ?array  
    {
        global $DB;

        $sql = 'SELECT qa.id AS attemptid, qa.userid AS studentid, 
                c.id AS courseid, c.fullname AS coursename, 
                cm.id AS coursemoduleid, 
                q.id AS quizid, q.name AS quizname
                FROM {quiz_attempts} AS qa 
                INNER JOIN {user} u 
                ON qa.userid = u.id 
                INNER JOIN {quiz} AS q 
                ON q.id = qa.quiz 
                INNER JOIN {course} AS c 
                ON c.id = q.course 
                INNER JOIN {course_modules} AS cm 
                ON cm.instance = q.id 
                WHERE qa.state = ? 
                AND qa.sumgrades IS NULL 
                AND cm.module = ? 
                AND cm.visible = 1 
                AND u.deleted = 0 
                AND u.suspended = 0 
                ORDER BY c.fullname, q.name ';

        $params = array('finished', $this->moduleId);

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Process attempt on course level.
     * 
     * @param stdClass attempt 
     */
    private function process_course_level(\stdClass $attempt) : void 
    {
        $course = new Course($this->courses, $attempt);
        $this->courses = $course->process_level();
    }

    /**
     * Returns teachers who check attempt. 
     * 
     * @param stdClass attempt 
     * 
     * @return array teachers 
     */
    private function get_teachers_who_check_attempt(\stdClass $attempt) : ?array 
    {
        $checkers = array();

        foreach($this->teachers as $teacher)
        {
            if($this->is_user_can_check_quiz($teacher->id, $attempt->coursemoduleid))
            {
                if($this->is_teacher_and_student_are_in_same_group($attempt, $teacher->id))
                {
                    $checker = new \stdClass;
                    $checker->id = $teacher->id;
                    $checker->name = $teacher->fullname;
                    $checker->email = $teacher->email;
                    $checker->phone1 = $teacher->phone1;
                    $checker->phone2 = $teacher->phone2;
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
     * @param int course module id  
     * 
     * @return bool 
     */
    private function is_user_can_check_quiz(int $teacherId, int $cmid) : bool 
    {
        $contextmodule = \context_module::instance($cmid);

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
     * @param stdClass attempt 
     * @param int teacher id 
     * 
     * @return bool 
     */
    private function is_teacher_and_student_are_in_same_group(\stdClass $attempt, int $teacherId) : bool 
    {
        $groups = $this->get_teacher_groups($attempt->courseid, $teacherId);

        foreach($groups as $group)
        {
            if(groups_is_member($group->id, $attempt->studentid))
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
    private function is_teacher_in_course_exists(\stdClass $course, \stdClass $teacher) : bool 
    {
        foreach($course->teachers as $cTeacher)
        {
            if($cTeacher->id == $teacher->id)
            {
                return false;
            }
        }

        return true;
    }




}
