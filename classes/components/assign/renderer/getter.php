<?php 

namespace NTD\Classes\Components\Assign\Renderer;

use NTD\Classes\Lib\Components\RendererGetter;
use \NTD\Classes\Lib\Enums as Enums; 

class Getter extends RendererGetter 
{

    /**
     * Returns component type. 
     * 
     * @return string component type 
     */
    protected function get_component_type() : string 
    {
        return Enums::ASSIGN;
    }

    /**
     * Returns true if user has teacher capability in component. 
     * 
     * @param int entity id 
     * 
     * @return bool 
     */
    protected function is_user_has_teacher_capability_in_component(int $cmid, \stdClass $teacher) : bool 
    {
        return has_capability('mod/assign:grade', \context_module::instance($cmid), $teacher);
    }

    /**
     * Returns link to activity. 
     * 
     * @param stdClass entity 
     * 
     * @return string 
     */
    protected function get_link_to_activity(\stdClass $entity) : string 
    {
        return '/mod/assign/view.php?id='.$entity->cmid.'&action=grading';
    }

}
