<?php 

namespace NTD\Classes\Renderer;

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
    public function get_course_actitivities_part() : string 
    {
        $block = $this->get_header();

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

}
