<?php 

namespace NTD\Classes\Renderer\Activities;

class LocalLib 
{

    /**
     * Returns true if unread messages exists.
     * 
     * @param stdClass entity
     * 
     * @return bool 
     */
    public static function is_unread_messages_exists(\stdClass $entity) : bool 
    {
        if($entity->unreaded)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    /**
     * Returns true if unchecked works exists.
     * 
     * @param stdClass entity
     * 
     * @return bool 
     */
    public static function is_unchecked_works_exists(\stdClass $entity) : bool 
    {
        if(isset($entity->unchecked) && $entity->unchecked)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

}
