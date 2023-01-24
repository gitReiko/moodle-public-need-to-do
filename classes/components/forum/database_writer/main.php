<?php 

namespace NTD\Classes\Components\Forum\DatabaseWriter;

require_once 'getters/forum.php';
require_once 'getters/teacherMessages.php';

use \NTD\Classes\Components\Forum\DatabaseWriter\Getters\TeacherMessages;
use \NTD\Classes\Components\Forum\DatabaseWriter\Getters\Forum;

class Main 
{

    /**
     * All teachers whose work is monitored by the block
     */
    private $teachers;

    /**
     * Level on which data must be updated.
     */
    private $updateLevel;

    /**
     * Forums with all posts.
     */
    private $forums;

    /** Unread teachers messages prepared for writing to the database.  */
    private $unreadMessages;

    /**
     * Prepares data for the class.
     * 
     * @param array of all teachers whose work is monitored by the block
     */
    function __construct(array $teachers, string $updateLevel)
    {
        $this->teachers = $teachers;
        $this->updateLevel = $updateLevel;

        $this->forums = $this->get_forums();
        $this->unreadMessages = $this->get_unread_teachers_messages();

        foreach($this->unreadMessages as $unreadMessage)
        {
            print_r($unreadMessage);

            foreach($unreadMessage->teacher->forums as $forum)
            {
                print_r($forum);
                echo '<hr>';
            }

            echo '<hr><hr>';
        }

        //print_r($this->teachers);


        

    }

    /**
     * Writes data related to forum into database.
     * 
     * @return void
     */
    public function write() : void 
    { 
        // Узяць усе дыскусіі кожнага форума 
        // Калі прымусовая падпіска адключана
        // Вызначыць ці падпісан настаўнік на дыскусію

        // Узяць усе паведамленні кожнай дыскусіі усіх форумаў
        
        // Прагнаць усіх настаўнікаў па ўсіх форумах
        // дыскусіях
        // паведамленнях 
        // І вызначыць, ці прачытаў ён паведамленне

        // Запісаць інфармацыю ў базу дадзеных


        // падписка - на форум
        // падписка на дыскусию


        echo 'WORK';
    }

    /**
     * Returns forums with subscription.
     * 
     * @return array forums if exists
     */
    private function get_forums() 
    {
        $forums = new Forum;
        return $forums->get_forums();
    }

    /**
     * Returns teachers with unread messages.
     * 
     * @return array teachers with unread messages
     */
    private function get_unread_teachers_messages() 
    {
        $teachers = new TeacherMessages(
            $this->teachers, $this->forums
        );
        return $teachers->get_unread_teachers_messages();
    }



}
