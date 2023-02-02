<?php 

namespace NTD\Classes\Renderer;

use NTD\Classes\Lib\Enums as Enums; 
use NTD\Classes\Lib\Common as cLib;

/**
 * Forms manager activities part.
 */
abstract class CoursesActivities
{
    /**
     * Courses with activities and teachers.
     * 
     * Contains all data neccessary for render.
     */
    protected $courses;

    /**
     * Block instance params.
     */
    protected $params;

    /**
     * Prepares data.
     * 
     * @param stdClass params of block instance
     */
    function __construct(\stdClass $params)
    {
        $this->params = $params;
        $this->init_neccessary_params();
        $this->init_courses_for_renderer();
    }

    /**
     * Returns course activities part of block.
     * 
     * @return string course activities
     */
    public function get_course_activities_part() : string 
    {
        $block = $this->get_header();
        $block.= $this->get_list_of_course_activities();

        return $block;
    }

    /** 
     * Prepares data neccessary for child classes.
     */
    abstract protected function init_neccessary_params() : void ;

    /**
     * Prepares data necessary for render.
     */
    abstract protected function init_courses_for_renderer() : void;

    /**
     * Returns header of class.
     * 
     * @return string header
     */
    private function get_header() : string 
    {
        $attr = array('class' => 'ntd-messanger-header');
        $text = get_string('course_activities', 'block_needtodo');
        return \html_writer::tag('p', $text, $attr);
    }

    /**
     * Returns list of course activities.
     * 
     * @return string list of course activities
     */
    private function get_list_of_course_activities() : string 
    {
        $list = '';

        foreach($this->courses as $course)
        {
            $list.= $this->get_course_cell($course);
            $list.= $this->get_teachers_cell($course);
        }

        return $list;
    }

    /**
     * Returns course cell. 
     * 
     * @param stdClass course
     * 
     * @return string course
     */
    private function get_course_cell(\stdClass $course) : string 
    {
        $attr = array(
            'class' => 'ntd-expandable ntd-teacher-cell',
            'data-course-cell' => $course->id,
            'data-block-instance' => $this->params->instance,
            'data-whose-work' => Enums::NOT_MY_WORK,
        );
        $text = $course->name;
        $text.= $this->get_unread_forum_messages_label($course);
        return \html_writer::tag('div', $text, $attr);
    }
    
    /**
     * Returns entity unread forum messages label.
     * 
     * @param stdClass entity 
     * 
     * @return string forum label
     */
    private function get_unread_forum_messages_label(\stdClass $entity) : string 
    {
        $attr = array('class' => 'ntd-undone-work');
        $text = ' <i class="fa fa-comments" aria-hidden="true"></i> ';
        $text.= $entity->unreadMessages;        
        return \html_writer::tag('span', $text, $attr);
    }

    /**
     * Returns course teachers cells.
     * 
     * @param stdClass course 
     * 
     * @return strings teachers cells 
     */
    private function get_teachers_cell(\stdClass $course) : string 
    {
        $cells = '';

        foreach($course->teachers as $teacher)
        {
            $attr = array(
                'class' => 'ntd-expandable ntd-level-2 ntd-tooltip ntd-hidden-box',
                'data-course-cell' => $course->id,
                'data-block-instance' => $this->params->instance,
                'data-whose-work' => Enums::NOT_MY_WORK,
                'title' => cLib::get_teacher_contacts($teacher)
            );
            $text = $teacher->name;
            $text.= $this->get_unread_forum_messages_label($teacher);
            $cells.= \html_writer::tag('div', $text, $attr);
        }

        return $cells;
    }



}
