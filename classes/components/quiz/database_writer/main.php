<?php 

namespace NTD\Classes\Components\Quiz\DatabaseWriter;

require_once __DIR__.'/../../../lib/components/database_writer.php';

use \NTD\Classes\Lib\Components\DatabaseWriter;
use \NTD\Classes\Lib\Enums as Enums; 

class Main extends DatabaseWriter 
{
    private $moduleId;

    /** Sets component name. */
    protected function set_component_name() : void
    {
        $this->componentName = Enums::QUIZ;
        $this->moduleId = $this->get_module_id();
    }

    /** Prepares data neccessary for database writer. */
    protected function prepare_neccessary_data() : void 
    {
        $this->add_courses_to_teachers();
        $this->add_quizes_to_teacher_courses();
        $this->add_count_of_unchecked_student_works();

        $this->init_component_data();
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
        $needtodo = new \stdClass;
        $needtodo->component = $this->componentName;
        $needtodo->teacherid = $dataEntity->teacher->id;
        $needtodo->info = json_encode($dataEntity);
        $needtodo->updatetime = time();
        return $needtodo;
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
     * Adds list of courses user is enrolled into to the teachers array.
     */
    private function add_courses_to_teachers() : void 
    {
        foreach($this->teachers as $teacher)
        {
            $onlyactive = true;

            $teacher->courses = enrol_get_all_users_courses(
                $teacher->id, 
                $onlyactive
            );
        }
    }

    /**
     * Adds quizes to teacher courses array.
     */
    private function add_quizes_to_teacher_courses() : void 
    {
        foreach($this->teachers as $teacher)
        {
            foreach($teacher->courses as $course)
            {
                $course->quizes = array();

                $quizes = $this->get_course_quizes($course->id);

                foreach($quizes as $quiz)
                {
                    if($this->is_user_can_check_quiz($teacher, $quiz))
                    {
                        $course->quizes[] = $quiz;
                    }
                }
            }
        }
    }

    /**
     * Returns all course quizes. 
     * 
     * @param int course id
     * 
     * @return array|null list of course quizes.
     */
    private function get_course_quizes(int $courseId) : ?array 
    {
        global $DB;

        $sql = 'SELECT q.id, cm.id as cmid, q.name 
                FROM {course_modules} AS cm 
                INNER JOIN {quiz} as q 
                ON cm.instance = q.id 
                WHERE cm.course = ? 
                AND cm.module = ? 
                AND cm.visible = 1 
                ORDER BY q.name ';

        $params = array($courseId, $this->moduleId);

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Returns true if teacher can check quiz. 
     * 
     * @param stdClass teacher 
     * @param stdClass quiz 
     * 
     * @return bool 
     */
    private function is_user_can_check_quiz(\stdClass $teacher, \stdClass $quiz) : bool 
    {
        $contextmodule = \context_module::instance($quiz->cmid);

        if(has_capability('mod/quiz:grade', $contextmodule, $teacher->id)) 
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    /**
     * Adds count of unchecked students works.
     */
    private function add_count_of_unchecked_student_works() : void 
    {
        foreach($this->teachers as $teacher)
        {
            foreach($teacher->courses as $course)
            {
                foreach($course->quizes as $quiz)
                {
                    $quiz->needtocheck = 0;

                    $attempts = $this->get_unchecked_attempts($quiz);

                    foreach($attempts as $attempt)
                    {
                        if($this->is_teacher_can_check_attempt(
                            $course->id,
                            $teacher->id, 
                            $attempt->studentid
                            )
                        )
                        {
                            $quiz->needtocheck++;
                        }
                    }
                }
            }
        }
    }

    /**
     * Returns unchecked students attempts from quiz.
     * 
     * @param stdClass quiz 
     * 
     * @return array unchecked attempts 
     */
    private function get_unchecked_attempts(\stdClass $quiz) : ?array  
    {
        global $DB;

        $sql = 'SELECT u.id as studentid 
                FROM {quiz_attempts} as qa 
                INNER JOIN {user} u 
                ON qa.userid = u.id 
                WHERE qa.quiz = ?
                AND qa.state = ? 
                AND qa.sumgrades IS NULL 
                AND u.deleted = 0
                AND u.suspended = 0';

        $params = array(
            $quiz->id, 
            'finished'
        );

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Returns true if teacher can check quiz attempt.
     * 
     * @param int course id
     * @param int teacher id 
     * @param int student id 
     * 
     * @return bool 
     */
    private function is_teacher_can_check_attempt(int $courseId, int $teacherId, int $studentId) : bool 
    {
        $withmembers = array($teacherId, $studentId);

        $groups = groups_get_all_groups(
            $courseId, 
            0, // $userid (default)
            0, // $groupingid (default)
            'g.*', // $fields (default)
            $withmembers
        );

        if(empty($groups))
        {
            return false;
        }
        else 
        {
            return true;
        }
    }

    /**
     * Initiates data based on an array of teachers.
     * 
     * Data is needed to write information related to component into database.
     */
    private function init_component_data() : void 
    {
        $data = array();

        foreach($this->teachers as $teacher)
        {     
            $dataEntity = new \stdClass;

            $dataEntity->teacher = new \stdClass;
            $dataEntity->teacher->id = $teacher->id;
            $dataEntity->teacher->email = $teacher->email;
            $dataEntity->teacher->name = $teacher->fullname;
            $dataEntity->teacher->phone1 = $teacher->phone1;
            $dataEntity->teacher->phone2 = $teacher->phone2;

            $dataEntity->quizes = array();

            foreach($teacher->courses as $course)
            {
                foreach($course->quizes as $cQuiz)
                {
                    if($cQuiz->needtocheck)
                    {
                        $quiz = new \stdClass;
                        $quiz->id = $cQuiz->id;
                        $quiz->cmid = $cQuiz->cmid;
                        $quiz->name = $cQuiz->name;
                        $quiz->courseId = $course->id;
                        $quiz->courseName = $course->fullname;
                        $quiz->needtocheck = $cQuiz->needtocheck;

                        $dataEntity->quizes[] = $quiz;
                    }
                }                
            }

            if(count($dataEntity->quizes))
            {
                $data[] = $dataEntity;
            }

        }

        $this->data = $data;
    }

}
