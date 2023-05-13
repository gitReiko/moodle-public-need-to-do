<?php 

namespace NTD\Classes\Renderer\Activities;

require_once 'main.php';

use \NTD\Classes\Components\Forum\Renderer\Getter as ForumGetter;
use \NTD\Classes\Components\Quiz\Renderer\Getter as QuizGetter;
use \NTD\Classes\Lib\Common as cLib;
use \NTD\Classes\Lib\Enums as Enums; 

/**
 * Forms activities part of the block for the work to be done by the user.
 */
class MyWork extends Main 
{

    /**
     * Prepares data for class.
     * 
     * @param stdClass params of block instance
     * @param array teachers
     */
    function __construct(\stdClass $params, array $teachers)
    {
        $this->whoseWork = Enums::MY;

        parent::__construct($params, $teachers);
    }

    protected function get_course_cell_unique_classes() : string
    {
        return ' ntd-my-work-activity-course-cell ';
    }

    /**
     * Returns courses with added forums.
     * 
     * @return array courses which are needed to render the block
     */
    protected function add_forums_data() : ?array 
    {
        $myWork = true;
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
        $myWork = true;
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
            foreach($teacher->activities as $activity)
            {
                $cells.= $this->get_activities_cell($course, $activity, $i);
            }
        }

        return $cells;
    }

    /**
     * Returns activities cells.
     * 
     * @param stdClass course
     * @param stdClass activity
     * @param int iteration
     * 
     * @return string activity cells
     */
    private function get_activities_cell(\stdClass $course, \stdClass $activity, int $i) : string 
    {
        $classes = 'ntd-level-2-my-activities ntd-tooltip ntd-cursor-pointer ';

        if(cLib::is_item_number_too_large($i)) 
        {
            $classes .= 'ntd-hidden-box ';
        }

        $attr = array(
            'class' => $classes,
            'data-course-cell' => $course->id,
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
