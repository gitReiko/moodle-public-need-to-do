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
     * Prepares data neccessary for child classes.
     */
    abstract protected function init_neccessary_params() : void ;

    /**
     * Prepares data necessary for render.
     */
    abstract protected function init_courses_for_renderer() : void;

}
