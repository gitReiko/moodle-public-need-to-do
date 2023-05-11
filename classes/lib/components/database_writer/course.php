<?php 

namespace NTD\Classes\Lib\Components\DatabaseWriter;

/**
 * Processes an entity at the course level. 
 */
abstract class Course  
{
    /** An array of courses that have entity. */
    protected $courses;

    /** The entity handled by the class */
    protected $rawEntity;

    /**
     * Prepares data for class.
     */
    function __construct(array $courses, \stdClass $rawEntity)
    {
        $this->courses = $courses;
        $this->rawEntity = $rawEntity;
    }

    /**
     * Processes an entity at the course level. 
     * 
     * @return array courses with processed data.
     */
    public function process_level()
    {
        if($this->is_course_not_exists())
        {
            $this->add_course_to_array();
        }
        else 
        {
            $this->increase_course_unchecked();
        }

        return $this->courses;
    }

    /**
     * Returns true if course exists in array. 
     * 
     * @return bool 
     */
    abstract protected function is_course_not_exists() : bool ;

    /**
     * Adds course to array. 
     */
    abstract protected function add_course_to_array() : void ;

    /**
     * Increases unckecked value of course by 1. 
     */
    abstract protected function increase_course_unchecked() : void ;

}
