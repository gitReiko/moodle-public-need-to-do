<?php 

namespace NTD\Classes\Components\Forum\DatabaseWriter\Getters;

class Forum  
{
    const NO_SUBSCRIPTION = 3;

    /**
     * Forums that match the settings.
     */
    private $forums;

    function __construct() 
    {
        $this->forums = $this->get_all_forums_with_subscription();
    }

    /**
     * Returns all forums that match the settings.
     * 
     * @return array forums
     */
    public function get_forums()
    {
        return $this->forums;
    }

    /**
     * Returns all forums from database.
     */
    private function get_all_forums_with_subscription()
    {
        global $DB;

        $sql = 'SELECT id, course, name, forcesubscribe
                FROM {forum}
                WHERE forcesubscribe <> ?';
        $params = array(self::NO_SUBSCRIPTION);

        return $DB->get_records_sql($sql, $params);
    }

}
