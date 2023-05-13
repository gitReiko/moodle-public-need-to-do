<?php 

namespace NTD\Classes\Renderer\Activities;

require_once __DIR__.'/../../lib/components/renderer_getter.php';
require_once __DIR__.'/../../components/forum/renderer/getter.php';
require_once __DIR__.'/../../components/quiz/renderer/getter.php';
require_once 'locallib.php';

use \NTD\Classes\Lib\Enums as Enums; 
use \NTD\Classes\Lib\Common as cLib;

/**
 * Forms activities part of block.
 */
abstract class Main 
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
     * Teachers whose data needs to be extracted.
     */
    protected $teachers;

    /**
     * Determines if something is owned by the user.
     */
    protected $whoseWork;

    /**
     * Prepares data for class.
     * 
     * @param stdClass params of block instance
     * @param array teachers
     * @param string whoseWork
     */
    function __construct(\stdClass $params, array $teachers)
    {
        $this->params = $params;
        $this->teachers = $teachers;

        $this->init_courses_for_renderer();
    }

    /**
     * Returns activities part of block.
     * 
     * @return string course activities
     */
    public function get_activities_part() : string 
    {
        $activities = $this->get_list_of_course_activities();

        if(empty($activities))
        {
            return '';
        }
        else 
        {
            return $this->get_header().$activities;
        }
    }

    /**
     * Returns unique classes for course cell.
     * 
     * My work and manager's work have a different structure.
     * 
     * @return string unique classes 
     */
    abstract protected function get_course_cell_unique_classes() : string;

    /**
     * Returns course child cells.
     * 
     * @param stdClass course 
     * @param stdClass child class
     * 
     * @return strings child cells 
     */
    abstract protected function get_child_cells(\stdClass $course, string $childClass, int $i) : string ;

    /**
     * Returns entity unread forum messages label.
     * 
     * @param stdClass entity 
     * 
     * @return string forum label
     */
    protected function get_unread_forum_messages_label(\stdClass $entity) : string 
    {
        $attr = array('class' => 'ntd-undone-work');
        $text = ' <i class="fa fa-comments" aria-hidden="true"></i> ';
        $text.= $entity->unreaded;
        return \html_writer::tag('span', $text, $attr);
    }

    /**
     * Returns the designation of unchecked works. 
     * 
     * @param stdClass entity
     * 
     * @return string designation
     */
    protected function get_unckeched_works_label(\stdClass $entity) : string 
    {
        $attr = array('class' => 'ntd-undone-work');
        $text = ' <i class="fa fa-book" aria-hidden="true"></i> ';
        $text.= $entity->unchecked;
        return \html_writer::tag('span', $text, $attr);
    }

    /**
     * Returns activity title. 
     * 
     * @param stdClass activity 
     * 
     * @return string title
     */
    protected function get_activity_title(\stdClass $activity) : string 
    {
        $title = '';

        if($activity->type == Enums::FORUM)
        {
            $title.= get_string('forum', 'block_needtodo');
        }

        $title.= $activity->name;

        if($activity->unreaded)
        {
            $title.= '<br>'.get_string('unread_forum_messages', 'block_needtodo');
            $title.= $activity->unreaded;       
        }

        if($activity->unchecked)
        {
            $title.= '<br>'.get_string('unchecked_activities_works', 'block_needtodo');
            $title.= $activity->unchecked;       
        }

        return $title;
    }

    /**
     * Returns courses with added quizes.
     * 
     * @return array courses which are needed to render the block
     */
    abstract protected function add_quizes_data() : ?array ;

    /**
     * Returns courses with added forums.
     * 
     * @return array courses which are needed to render the block
     */
    abstract protected function add_forums_data() : ?array ;

    /**
     * Prepares data necessary for render.
     */
    private function init_courses_for_renderer() : void 
    {
        $this->courses = array();
        $this->courses = $this->add_forums_data();
        $this->courses = $this->add_quizes_data();

        $this->count_total_works();
        $this->sort_all_data();
    }

    /**
     * Returns header of class.
     * 
     * @return string header
     */
    private function get_header() : string 
    {
        $attr = array('class' => 'ntd-block-subheader');
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
        $blockClass = $this->get_hidden_elements_class_for_more_button();

        $i = 0;
        foreach($this->courses as $course)
        {
            if(cLib::is_item_number_too_large($i)) 
            {
                $class = 'ntd-hidden-box '.$blockClass;
                $childClass = $blockClass.Enums::CHILDS;
            }
            else 
            {
                $class = '';
                $childClass = '';
            }

            $list.= $this->get_course_cell($course, $class);
            $list.= $this->get_child_cells($course, $childClass, $i);

            $i++;
        }

        if(cLib::is_item_number_too_large($i)) 
        {
            $list.= cLib::get_show_more_button($blockClass, $childClass);
        }

        return $list;
    }

    /**
     * Returns class of hidden elements for more button. 
     * 
     * @return string id of more button
     */
    private function get_hidden_elements_class_for_more_button() : string 
    {
        return Enums::MORE.$this->whoseWork.Enums::ACTIVITIES.$this->params->instance;
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
            'class' => 'ntd-level-1 ntd-expandable ntd-tooltip '.$class.$this->get_course_cell_unique_classes(),
            'data-course-cell' => $course->id,
            'data-block-instance' => $this->params->instance,
            'data-whose-work' => $this->whoseWork,
            'title' => $this->get_course_title($course)
        );
        $text = $course->name;

        if(LocalLib::is_unread_messages_exists($course))
        {
            $text.= $this->get_unread_forum_messages_label($course);
        }

        if(LocalLib::is_unchecked_works_exists($course))
        {
            $text.= $this->get_unckeched_works_label($course);
        }

        $text.= $this->get_link_to_course($course->id);
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
        $title = get_string('course', 'block_needtodo').$course->name;

        if(LocalLib::is_unread_messages_exists($course))
        {
            $title.= '<br>'.get_string('unread_forum_messages', 'block_needtodo');
            $title.= $course->unreaded;  
        }

        if($course->unchecked)
        {
            $title.= '<br>'.get_string('unchecked_activities_works', 'block_needtodo');
            $title.= $course->unchecked;       
        }
     
        return $title;
    }

    /**
     * Returns link to the course.
     * 
     * @param int $courseId 
     * 
     * @return string link to the course 
     */
    private function get_link_to_course(int $courseId) : string 
    {
        $link = '<a href="/course/view.php?id='.$courseId.'">';
        $link.= ' <i class="fa fa-angle-double-right" aria-hidden="true"></i>';
        $link.= ' '.get_string('to_the_course', 'block_needtodo');
        $link.= '</a>';

        return $link;
    }

    /**
     * Sorts all courses data (courses, teachers, activities).
     */
    private function sort_all_data() : void 
    {
        foreach($this->courses as $course)
        {
            foreach($course->teachers as $teacher)
            {
                usort($teacher->activities, function($a, $b){
                    return strcmp($a->name, $b->name);
                });
            }

            usort($course->teachers, function($a, $b){
                return strcmp($a->name, $b->name);
            });
        }

        usort($this->courses, function($a, $b){
            return strcmp($a->name, $b->name);
        });
    }

    private function count_total_works() : void 
    {
        foreach($this->courses as $course)
        {
            $course->unreaded = 0;
            $course->unchecked = 0;

            foreach($course->teachers as $teacher)
            {
                $teacher->unreaded = 0;
                $teacher->unchecked = 0;

                foreach($teacher->activities as $activity)
                {
                    if(isset($activity->unreaded))
                    {
                        $teacher->unreaded += $activity->unreaded;
                    }

                    if(isset($activity->unchecked))
                    {
                        $teacher->unchecked += $activity->unchecked;
                    }
                }

                $course->unreaded += $teacher->unreaded;
                $course->unchecked += $teacher->unchecked;
            }
        }
    }

}
