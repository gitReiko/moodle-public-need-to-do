<?php 

namespace NTD\Classes\Components\Forum\DatabaseWriter\Getters;

class TeacherMessages   
{
    /**
     * All teachers whose work is monitored by the block
     */
    private $teachers;

    /**
     * Forums with all posts.
     */
    private $forums;

    function __construct($teachers, $forums)
    {
        $this->teachers = $teachers;
        $this->forums = $forums;

        $this->add_unreaded_messages_to_teachers();
    }

    private function get_teacher_messages()
    {
        // sv
    }

    /**
     * Adds unreaded messanges to teachers.
     */
    private function add_unreaded_messages_to_teachers() : void 
    {
        foreach($this->teachers as $teacher)
        {
            $teacher->forums = array();

            foreach($this->forums as $forum)
            {
                $this->add_unreaded_forum_messages_to_teacher($teacher, $forum);
            }
        }
    }

    /**
     * Adds unreaded messanges to teacher.
     */
    private function add_unreaded_forum_messages_to_teacher($teacher, $forum) : void 
    {
        $subscribed = false;
        $unreadedMessages = 0;

        if($forum->forcesubscribe) 
        {
            $subscribed = true;
        }
        else if($this->is_user_subscribed_to_forum($teacher->id, $forum->id))
        {
            $subscribed = true;
        }

        foreach($forum->discussions as $discussion)
        {
            if($this->is_user_subscribed_to_discussion($teacher->id, $forum->id, $discussion->id)) 
            {
                $subscribed = true;
            }

            if($subscribed)
            {
                $unreadedMessages += $this->unread_discussion_posts($teacher->id, $forum->id, $discussion); // !!!!!!!!!!!!!!!!
            }
        }

        if($unreadedMessages)
        {
            $this->add_forum_with_unread_messages($teacher, $forum->id, $unreadedMessages);
        }
    }

    /**
     * Returns true if user subscribed to forum.
     * 
     * @param int $userId
     * @param int $forumId
     * 
     * @return bool 
     */
    private function is_user_subscribed_to_forum(int $userId, int $forumId) : bool 
    {
        global $DB;
        $where = array('userid' => $userId, 'forum' => $forumId); 
        return $DB->record_exists('forum_subscriptions', $where);
    }

    /**
     * Returns true if user subscribed to discussion.
     * 
     * @param int $userId
     * @param int $forumId
     * @param int $discussionId
     * 
     * @return bool 
     */
    private function is_user_subscribed_to_discussion(int $userId, int $forumId, int $discussionId) : bool 
    {
        global $DB;
        $where = array(
            'userid' => $userId, 
            'forum' => $forumId,
            'discussion' => $discussionId
        ); 
        return $DB->record_exists('forum_discussion_subs', $where);
    }

    /**
     * Returns count of unreaded discussion posts.
     * 
     * @param int $teacherId
     * @param int $forumId
     * @param stdClass $discussion
     * 
     * @return int count of unreaded discussion posts 
     */
    private function unread_discussion_posts(int $teacherId, int $forumId, \stdClass $discussion) : int 
    {
        $unreaded = 0;

        foreach($discussion->posts as $post)
        {
            if($this->is_user_didnt_read_post($teacherId, $forumId, $discussion->id, $post->id))
            {
                $unreaded++;
            }
        }

        return $unreaded;
    }

    /**
     * Return true if post did not read.
     * 
     * @param int $teacherId
     * @param int $forumId
     * @param int $discussionId
     * @param int $postId
     * 
     * @return bool 
     */
    private function is_user_didnt_read_post(int $teacherId, int $forumId, int $discussionId, int $postId) : bool 
    {
        global $DB;

        $where = array(
            'userid' => $teacherId,
            'forumid' => $forumId,
            'discussionid' => $discussionId,
            'postid' => $postId
        );

        return !$DB->record_exists('forum_read', $where);
    }

    /**
     * Adds forum with unreaded messages to teacher. 
     * 
     * @param stdClass $teacher
     * @param int $forumId 
     * @param int $unreadedMessages
     */
    private function add_forum_with_unread_messages(\stdClass $teacher, int $forumId, int $unreadedMessages) : void 
    {
        $forum = new \stdClass;
        $forum->id = $forumId;
        $forum->unreadedMessages = $unreadedMessages;

        $teacher->forums[] = $forum;
    }

}
