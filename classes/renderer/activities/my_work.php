<?php 

namespace NTD\Classes\Renderer\Activities;

require_once 'main.php';

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
     * Returns course child cells.
     * 
     * @param stdClass course 
     * @param stdClass child class
     * 
     * @return strings child cells 
     */
    protected function get_child_cells(\stdClass $course, string $childClass) : string
    {
        $cells = '';

        foreach($course->teachers as $teacher)
        {
            foreach($teacher->activities as $activity)
            {
                $cells.= $this->get_activities_cell($course, $activity);
            }
        }

        return $cells;
    }

    /**
     * Returns activities cells.
     * 
     * @param stdClass course
     * @param stdClass activity 
     * 
     * @return string activity cells
     */
    private function get_activities_cell(\stdClass $course, \stdClass $activity) : string 
    {
        $attr = array(
            'class' => 'ntd-level-2-my-activities ntd-tooltip ntd-cursor-pointer',
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
