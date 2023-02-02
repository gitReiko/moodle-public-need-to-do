<?php 

namespace NTD\Classes\Renderer;

require_once 'course_activities.php';
require_once __DIR__.'/../components/forum/renderer/manager_getter.php';

use \NTD\Classes\Components\Forum\Renderer\ManagerGetter as ForumGetter;
use \NTD\Classes\Lib\Getters\Common as cGetter;

/**
 * Forms activities part.
 */
class ManagerCoursesActivities extends CoursesActivities 
{

    /**
     * Teachers whose data needs to be extracted.
     */
    protected $teachers;

    /**
     * Prepares data.
     * 
     * @param stdClass params of block instance
     */
    function __construct(\stdClass $params)
    {
        parent::__construct($params);
    }
    /** 
     * Prepares teachers neccessary data.
     */
    protected function init_neccessary_params() : void 
    {
        $this->teachers = $this->get_teachers();
    }

    /**
     * Prepares data necessary for render.
     */
    protected function init_courses_for_renderer() : void 
    {
        $this->courses = array();
        $this->courses = $this->add_forums_data();
    }

    /**
     * Returns teachers from global or local settings.
     * 
     * @return array teachers 
     */
    private function get_teachers()
    {
        return cGetter::get_teachers_from_cohort($this->params->cohort);
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

}
