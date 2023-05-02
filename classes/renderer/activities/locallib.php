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
        if(isset($entity->unreadMessages) && $entity->unreadMessages)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

}
