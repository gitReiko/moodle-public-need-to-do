<?php 

namespace NTD\Classes\Components\Quiz\DatabaseWriter;

require_once __DIR__.'/../../../lib/components/database_writer/template/teachers.php';
require_once 'activities.php';

use \NTD\Classes\Lib\Components\DatabaseWriter\Template\Teachers as TeachersTemplate;

/**
 * Processes an entity at the teacher level. 
 */
class Teachers extends TeachersTemplate 
{

    /**
     * Returns true if teacher can check quiz. 
     * 
     * @param int teacher 
     * 
     * @return bool 
     */
    protected function is_user_can_check_entity(int $teacherId) : bool 
    {
        $contextmodule = \context_module::instance($this->rawEntity->coursemoduleid);

        if(has_capability('mod/quiz:grade', $contextmodule, $teacherId)) 
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    /** 
     * Processes an assign submission at the activities level for teacher. 
     * 
     * @param stdClass course 
     * @param int checking teacher id  
     */
    protected function process_activities_level(\stdClass &$course, int $checkingTeacherId) : void 
    {
        $activities = new Activities($course, $checkingTeacherId, $this->rawEntity);
        $activities->process_level();
    }

}
