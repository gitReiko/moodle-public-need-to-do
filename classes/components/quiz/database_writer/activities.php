<?php 

namespace NTD\Classes\Components\Quiz\DatabaseWriter;

/**
 * Processes an attempt at the activities level. 
 */
class Activities  
{
    /** A teacher that have attempts. */
    private $teacher;

    /** An attempt of student to complete quiz. */
    private $attempt;

    /**
     * Prepares data for class.
     */
    function __construct(\stdClass $teacher, \stdClass $attempt)
    {
        $this->teacher = $teacher;
        $this->attempt = $attempt;
    }

    /**
     * Processes an attempt at the activities level. 
     */
    public function process_level() : \stdClass  
    {
        if($this->is_activity_exists())
        {
            echo 'increase<hr>';
            $this->increase_activity_unchecked();
        }
        else 
        {
            $this->add_activity_to_array();
        }

        return $this->teacher;
    }

    /**
     * Returns true if activity exists in teacher array. 
     * 
     * @return bool 
     */
    private function is_activity_exists() : bool 
    {
        foreach($this->teacher->activities as $activity)
        {
            echo 'activity id '.$activity->id.' == $this->attempt->quizid'.$this->attempt->quizid.'<hr>';
            if($activity->id == $this->attempt->quizid)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Increases unckecked value of activity by 1. 
     */
    private function increase_activity_unchecked() : void 
    {
        /*
        while($i < count($this->teacher->activities))
        {
            if($this->teacher->activities[$i]->unchecked == $this->attempt->quizid)
            {
                $this->teacher->activities[$i]->unchecked++;
            }

            $i++;
        }
        */


        foreach($this->teacher->activities as $activity)
        {
            if($activity->id == $this->attempt->quizid)
            {
                $activity->unchecked++;
            }
        }
    }

    /**
     * Adds activity to teacher array.
     */
    private function add_activity_to_array() : void 
    {
        $activity = new \stdClass;
        $activity->id = $this->attempt->quizid;
        $activity->cmid = $this->attempt->coursemoduleid;
        $activity->name = $this->attempt->quizname;
        $activity->unchecked = 1;
        $activity->unreaded = 0;

        print_r($activity);
        echo '<hr>';

        $this->teacher->activities[] = $activity;
    }


}
