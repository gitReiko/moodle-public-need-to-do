<?php 

namespace NTD\Classes\Renderer\Activities;

require_once 'main.php';
require_once 'locallib.php';

use \NTD\Classes\Components\Forum\Renderer\Getter as ForumGetter;
use \NTD\Classes\Components\Quiz\Renderer\Getter as QuizGetter;
use \NTD\Classes\Lib\Enums as Enums; 
use \NTD\Classes\Lib\Common as cLib;

/**
 * Forms activities part of the block for manager.
 */
class Manager extends Main 
{

    /**
     * Prepares data for class.
     * 
     * @param stdClass params of block instance
     * @param array teachers
     */
    function __construct(\stdClass $params, array $teachers)
    {
        $this->whoseWork = Enums::OTHER;

        parent::__construct($params, $teachers);
    }

    protected function get_course_cell_unique_classes() : string
    {
        return ' ntd-manager-activity-course-cell ';
    }

    /**
     * Returns courses with added forums.
     * 
     * @return array courses which are needed to render the block
     */
    protected function add_forums_data() : ?array 
    {
        $myWork = false;
        $forum = new ForumGetter($this->params, $this->teachers, $this->courses, $myWork);
        return $forum->get_courses_with_component_data();
    }

    /**
     * Returns courses with added quizes.
     * 
     * @return array courses which are needed to render the block
     */
    protected function add_quizes_data() : ?array 
    {
        $myWork = false;
        $quiz = new QuizGetter($this->params, $this->teachers, $this->courses, $myWork);
        return $quiz->get_courses_with_component_data();
    }

    /**
     * Returns course child cells.
     * 
     * @param stdClass course 
     * @param stdClass child class
     * 
     * @return strings child cells 
     */
    protected function get_child_cells(\stdClass $course, string $childClass, int $i) : string
    {
        $cells = '';

        foreach($course->teachers as $teacher)
        {
            $className = 'ntd-expandable ntd-level-2-other-activities  ntd-tooltip ';
            $className.= 'ntd-hidden-box ntd-manager-activity-teacher-cell ';
            $className.= $childClass;

            $attr = array(
                'class' => $className,
                'data-course-cell' => $course->id,
                'data-teacher-cell' => $teacher->id,
                'data-block-instance' => $this->params->instance,
                'data-whose-work' => $this->whoseWork,
                'title' => $this->get_teacher_contacts($teacher)
            );
            $text = $teacher->name;

            if(LocalLib::is_unread_messages_exists($teacher))
            {
                $text.= $this->get_unread_forum_messages_label($teacher);
            }

            if(LocalLib::is_unchecked_works_exists($teacher))
            {
                $text.= $this->get_unckeched_works_label($teacher);
            }

            $cells.= \html_writer::tag('div', $text, $attr);

            foreach($teacher->activities as $activity)
            {
                $cells.= $this->get_activities_cell($course, $teacher, $activity, $className);
            }
        }

        return $cells;
    }

    /**
     * Returns teacher contacts prepared for render.
     * 
     * @param stdClass teacher 
     * 
     * @return string contacts prepared for render
     */
    private function get_teacher_contacts(\stdClass $teacher) : string 
    {
        $unreadText = '';

        if(LocalLib::is_unread_messages_exists($teacher))
        {
            $unreadText.= '<br>'.get_string('total_unread_messages', 'block_needtodo');
            $unreadText.= $teacher->timelyRead + $teacher->untimelyRead;
            $unreadText.= '<br>'.get_string('untimely_unread_messages', 'block_needtodo');
            $unreadText.= $teacher->untimelyRead;
        }

        if($teacher->timelyCheck || $teacher->untimelyCheck)
        {
            $unreadText.= '<br>'.get_string('total_unchecked_works', 'block_needtodo');
            $unreadText.= $teacher->timelyCheck + $teacher->untimelyCheck;
            $unreadText.= '<br>'.get_string('untimely_unchecked_works', 'block_needtodo');
            $unreadText.= $teacher->untimelyCheck;     
        }

        return cLib::get_teacher_contacts($teacher, $unreadText);
    }

    /**
     * Returns activities cells.
     * 
     * @param stdClass course
     * @param stdClass teacher 
     * @param stdClass activity 
     * @param string class name
     * 
     * @return string activity cells
     */
    private function get_activities_cell(\stdClass $course, \stdClass $teacher, \stdClass $activity, string $className) : string 
    {
        $attr = array(
            'class' => 'ntd-level-3 ntd-tooltip ntd-hidden-box ntd-cursor-pointer'.$className,
            'data-course-cell' => $course->id,
            'data-teacher-cell' => $teacher->id,
            'data-block-instance' => $this->params->instance,
            'data-whose-work' => $this->whoseWork,
            'title' => $this->get_activity_title($activity)
        );
        $text = $activity->name;

        if(LocalLib::is_unread_messages_exists($course))
        {
            $text.= $this->get_unread_forum_messages_label($activity);
        }

        if(LocalLib::is_unchecked_works_exists($activity))
        {
            $text.= $this->get_unckeched_works_label($activity);
        }

        $text = \html_writer::tag('a', $text, array('href' => $activity->link));

        return \html_writer::tag('div', $text, $attr);
    }

}
