<?php 

namespace NTD\Classes\Components\Forum\DatabaseWriter\Getters;

/**
 * Forums getter for forum component.
 * 
 * Returns all forums to which educators can subscribe.
 */
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
        $this->add_discussions_to_forums();
        $this->add_posts_to_forums();
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

    /**
     * Adds discussions to forums.
     */
    private function add_discussions_to_forums() : void 
    {
        foreach($this->forums as $forum)
        {
            $forum->discussions = $this->get_forum_discussions($forum->id);
        }
    }

    /**
     * Returns forum discussions.
     * 
     * @param int $forumId
     * 
     * @return array forum discussions if its exists
     */
    private function get_forum_discussions(int $forumId)
    {
        global $DB;
        $where = array(
            'forum' => $forumId
        );
        return $DB->get_records('forum_discussions', $where, '', 'id');
    }

    /**
     * Adds posts to forums.
     */
    private function add_posts_to_forums() 
    {
        foreach($this->forums as $forum)
        {
            foreach($forum->discussions as $discussion)
            {
                $discussion->posts = $this->get_discussion_posts($discussion->id);
            }
        }
    }

    /**
     * Returns discussion posts.
     * 
     * @param int $discussionId
     * 
     * @return array discussion posts if exists
     */
    private function get_discussion_posts(int $discussionId)
    {
        global $DB;
        $where = array(
            'discussion' => $discussionId
        );
        return $DB->get_records('forum_posts', $where, '', 'id');
    }

}
