<?php 

namespace NTD\Classes\Components\Quiz\Renderer;

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
        return Enums::QUIZ;
    }

    /**
     * Returns data related to quiz from database.
     * 
     * @param string teachers in condition for database 
     * 
     * @return array quiz data 
     */
    protected function get_component_entities(\stdClass $data) : ?array 
    {
        return $data->quizes;
    }

    /**
     * Returns true if user has manager capability in component. 
     * 
     * @param int entity id 
     * 
     * @return bool 
     */
    protected function is_user_has_manager_capability_in_component(int $cmid) : bool 
    {
        return has_capability('mod/quiz:manage', \context_module::instance($cmid));
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
        return '/mod/quiz/view.php?id='.$entity->cmid;
    }

}
