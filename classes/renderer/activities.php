<?php 

namespace NTD\Classes\Renderer;

require_once __DIR__.'/../components/forum/renderer/getter.php';

use \NTD\Classes\Components\Forum\Renderer\Getter as ForumGetter;
use \NTD\Classes\Lib\Enums as Enums; 
use \NTD\Classes\Lib\Common as cLib;

/**
 * Forms activities part of block.
 */
class Activities
{

    /**
     * Courses with activities and teachers.
     * 
     * Contains all data neccessary for render.
     */
    private $courses;

    /**
     * Block instance params.
     */
    private $params;

    /**
     * Class name for more button.
     */
    private $more;

    /**
     * Teachers whose data needs to be extracted.
     */
    private $teachers;

    /**
     * Prepares data.
     * 
     * @param stdClass params of block instance
     * @param array teachers
     */
    function __construct(\stdClass $params, array $teachers, string $moreButtonId)
    {
        $this->params = $params;
        $this->teachers = $teachers;
        $this->more = $moreButtonId;

        $this->init_courses_for_renderer();
    }

    /**
     * Returns activities part of block.
     * 
     * @return string course activities
     */
    public function get_activities_part() : string 
    {
        $block = $this->get_header();
        $block.= $this->get_list_of_course_activities();

        return $block;
    }

    /**
     * Prepares data necessary for render.
     */
    private function init_courses_for_renderer() : void 
    {
        $this->courses = array();
        $this->courses = $this->add_forums_data();
    }

    /**
     * Returns courses with added forums.
     * 
     * @return array courses which are needed to render the block
     */
    private function add_forums_data() 
    {
        $forum = new ForumGetter($this->params, $this->teachers, $this->courses);
        return $forum->get_courses_with_added_forums();
    }

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
        $blockClass = $this->more.$this->params->instance;

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
            $list.= $this->get_teachers_cell($course, $childClass);

            $i++;
        }

        if(cLib::is_item_number_too_large($i)) 
        {
            $list.= cLib::get_show_more_button($blockClass, $childClass);
        }

        return $list;
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
     * Returns course teachers cells.
     * 
     * @param stdClass course 
     * @param stdClass child class
     * 
     * @return strings teachers cells 
     */
    private function get_teachers_cell(\stdClass $course, string $childClass) : string 
    {
        $cells = '';

        foreach($course->teachers as $teacher)
        {
            $className = 'ntd-expandable ntd-level-2  ntd-tooltip ';
            $className.= 'ntd-hidden-box ntd-activity-teacher-cell ';
            $className.= $childClass;

            $attr = array(
                'class' => $className,
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
                $cells.= $this->get_activities_cell($teacher, $activity, $className);
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
     * @param string class name
     * 
     * @return string activity cells
     */
    private function get_activities_cell(\stdClass $teacher, \stdClass $activity, string $className) : string 
    {
        $attr = array(
            'class' => 'ntd-level-3 ntd-tooltip ntd-hidden-box ntd-cursor-pointer'.$className,
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
