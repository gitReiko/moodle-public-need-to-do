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
        $blockClass = 'ntd-more-activities-'.$this->params->instance;

        $i = 0;
        foreach($this->courses as $course)
        {
            if($this->is_item_number_too_large($i)) 
            {
                $class = 'ntd-hidden-box '.$blockClass;
            }
            else 
            {
                $class = '';
            }

            $list.= $this->get_course_cell($course, $class);
            $list.= $this->get_teachers_cell($course);

            $i++;
        }

        if($this->is_item_number_too_large($i)) 
        {
            $list.= $this->get_show_more_button($blockClass);
        }

        return $list;
    }

    /**
     * Returns true if item number is too large.
     * 
     * @param int $number 
     * 
     * @return bool 
     */
    private function is_item_number_too_large(int $number) : bool 
    {
        if($number > 5) 
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    /**
     * Returns show / hide more button. 
     * 
     * @param string $class
     * 
     * @return string show / hide more button
     */
    private function get_show_more_button(string $class) : string 
    {
        $attr = array(
            'class' => 'ntd-cursor-pointer',
            'data-show-text' =>  get_string('show_more', 'block_needtodo'),
            'data-hide-text' =>  get_string('hide_more', 'block_needtodo'),
            'onclick' => 'show_hide_more(this,`'.$class.'`)',
            'style' => 'margin-bottom: 0px'
        );
        $text = get_string('show_more', 'block_needtodo');
        return \html_writer::tag('p', $text, $attr);
    }

    /**
     * Returns course cell. 
     * 
     * @param stdClass course
     * @param string class
     * 
     * @return string course
     */
    private function get_course_cell(\stdClass $course, string $class) : string 
    {
        $attr = array(
            'class' => 'ntd-expandable ntd-activity-course-cell ntd-tooltip '.$class,
            'data-course-cell' => $course->id,
            'data-block-instance' => $this->params->instance,
            'data-whose-work' => Enums::NOT_MY_WORK,
            'title' => $this->get_course_title($course)
        );
        $text = $course->name;
        $text.= $this->get_unread_forum_messages_label($course);
        return \html_writer::tag('div', $text, $attr);
    }

    /**
     * Returns tile for course cell.
     * 
     * @param stdClass course 
     * 
     * @return string title 
     */
    private function get_course_title(\stdClass $course)
    {
        $title = get_string('course', 'block_needtodo').$course->name.'<br>';
        $title.= get_string('unread_forum_messages', 'block_needtodo');
        $title.= $course->unreadMessages;
        return $title;
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
                'class' => 'ntd-expandable ntd-level-2 ntd-tooltip ntd-hidden-box ntd-activity-teacher-cell',
                'data-course-cell' => $course->id,
                'data-teacher-cell' => $teacher->id,
                'data-block-instance' => $this->params->instance,
                'data-whose-work' => Enums::NOT_MY_WORK,
                'title' => $this->get_teacher_contacts($teacher, $teacher->unreadMessages)
            );
            $text = $teacher->name;
            $text.= $this->get_unread_forum_messages_label($teacher);
            $cells.= \html_writer::tag('div', $text, $attr);

            foreach($teacher->activities as $activity)
            {
                $cells.= $this->get_activities_cell($teacher, $activity);
            }
        }

        return $cells;
    }

    /**
     * Returns teacher contacts prepared for render.
     * 
     * @param stdClass teacher 
     * @param int unread count 
     * 
     * @return string contacts prepared for render
     */
    private function get_teacher_contacts(\stdClass $teacher, int $unreadCount) : string 
    {
        $unreadText = get_string('unread_forum_messages', 'block_needtodo');
        $unreadText.= $unreadCount;

        return cLib::get_teacher_contacts($teacher, $unreadText);
    }

    /**
     * Returns activities cells.
     * 
     * @param stdClass teacher 
     * @param stdClass activity 
     * 
     * @return string activity cells
     */
    private function get_activities_cell(\stdClass $teacher, \stdClass $activity) : string 
    {
        $attr = array(
            'class' => 'ntd-level-3 ntd-tooltip ntd-hidden-box ntd-cursor-pointer',
            'data-teacher-cell' => $teacher->id,
            'data-block-instance' => $this->params->instance,
            'data-whose-work' => Enums::NOT_MY_WORK,
            'title' => $this->get_activity_title($activity)
        );
        $text = $activity->name;
        $text.= $this->get_unread_forum_messages_label($activity);

        $text = \html_writer::tag('a', $text, array('href' => $activity->link));

        return \html_writer::tag('div', $text, $attr);
    }

    /**
     * Returns activity title. 
     * 
     * @param stdClass activity 
     * 
     * @return string title
     */
    private function get_activity_title(\stdClass $activity) : string 
    {
        $title = '';

        if($activity->type == Enums::FORUM)
        {
            $title.= get_string('forum', 'block_needtodo');
        }

        $title.= $activity->name.'<br>';
        $title.= get_string('unread_forum_messages', 'block_needtodo');
        $title.= $activity->unreadMessages;
        return $title;
    }



}
