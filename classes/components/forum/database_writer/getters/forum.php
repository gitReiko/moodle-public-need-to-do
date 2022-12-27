<?php 

namespace NTD\Classes\Components\Forum\DatabaseWriter\Getters;

class Forum  
{
    const FORCE_SUBSCRIPTION = 1;
    const NO_SUBSCRIPTION = 3;

    /**
     * Forums with subscription that match the settings.
     */
    private $forums;

    /**
     * Prepares data for class. 
     */
    function __construct() 
    {
        $this->forums = $this->get_all_forums_with_subscription();
        $this->simplify_forums_force_subscription();
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
     * Returns all forums with subscription from database.
     * 
     * @return array forums
     */
    private function get_all_forums_with_subscription()
    {
        global $DB;

        $sql = 'SELECT f.id, f.name, f.forcesubscribe,  
                f.course as courseid, c.fullname as coursename 
                FROM {forum} as f 
                INNER JOIN {course} as c 
                ON f.course = c.id
                WHERE forcesubscribe <> ?
                ORDER BY c.fullname, f.name';
        $params = array(self::NO_SUBSCRIPTION);

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Simplifies forums force subscriptions.
     * 
     * Sets true if enabled force subscription.
     * False if not.
     * 
     * Forced subscription has many statuses, but we are only interested in one.
     */
    private function simplify_forums_force_subscription() : void 
    {
        foreach($this->forums as $forum)
        {
            if($forum->forcesubscribe == self::FORCE_SUBSCRIPTION)
            {
                $forum->forcesubscribe = true;
            }
            else 
            {
                $forum->forcesubscribe = false;
            }
        }
    }

}
