<?php 

namespace NTD\Classes\DatabaseWriter;

require_once __DIR__.'/../lib/enums.php';
require_once __DIR__.'/../lib/getters/teachers.php';
require_once __DIR__.'/../lib/getters/common.php';
require_once __DIR__.'/main.php';

use \NTD\Classes\Components\Messanger\DatabaseWriter\Main as MessangerDatabaseWriter;
use \NTD\Classes\Components\Forum\DatabaseWriter\Refact as ForumDatabaseWriter;
use \NTD\Classes\Components\Quiz\DatabaseWriter\Main as QuizDatabaseWriter;
use \NTD\Classes\Lib\Getters\Teachers as tGet;
use \NTD\Classes\Lib\Enums as Enums; 

/**
 * Writes data to the database.
 * 
 * This data is subsequently used by the renderer to quickly form a block.
 * 
 * @todo крон должен просчитывать учителей из всех групп для всех курсов
 */
class MainTask extends Main 
{

    /**
     * All teachers whose work is monitored by the block
     */
    protected $teachers;

    /**
     * Prepares data for the class.
     */
    function __construct() 
    {
        $this->teachers = tGet::get_all_teachers();

        $this->clear_outdated_teachers();
    }

    /**
     * Writes messanger related information to database.
     * 
     * @return void
     */
    protected function write_messsanger() : void 
    {
        $messangerWriter = new MessangerDatabaseWriter(
            $this->teachers,
            Enums::UPDATE_DATA_ON_SITE_LEVEL
        );
        $messangerWriter->write();
    }

    /**
     * Writes data related to forum into database.
     * 
     * @return void
     */
    protected function write_forum() : void 
    {
        $forumWriter = new ForumDatabaseWriter(
            $this->teachers,
            Enums::UPDATE_DATA_ON_SITE_LEVEL
        );
        $forumWriter->write();
    }

    /**
     * Writes data related to quiz into database.
     * 
     * @return void
     */
    protected function write_quiz() : void 
    {
        $quizWriter = new QuizDatabaseWriter(
            $this->teachers,
            Enums::UPDATE_DATA_ON_SITE_LEVEL
        );
        $quizWriter->write();
    }

    /**
     * Clears outdated teachers from database. 
     * 
     * Outdated teacher is a teacher who is no longer used by the block
     */
    private function clear_outdated_teachers() : void 
    {
        $data = $this->get_all_block_data();

        foreach($data as $value)
        {
            if($this->is_teacher_not_exists($value))
            {
                $this->delete_outdated_teacher($value);
            }
        }
    }

    /**
     * Returns all block data. 
     * 
     * @return array data 
     */
    private function get_all_block_data() : ?array 
    {
        global $DB;
        return $DB->get_records('block_needtodo', array());
    }

    /**
     * Returns true if teacher used by block.
     * 
     * @param stdClass row of block table 
     * 
     * @return bool 
     */
    private function is_teacher_not_exists(\stdClass $value) : bool 
    {
        foreach($this->teachers as $teacher)
        {
            if($value->teacherid == $teacher->id)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Deletes outdated teachers. 
     * 
     * Outdated teacher is a teacher who is no longer used by the block
     * 
     * @param stdClass row of block table  
     */
    private function delete_outdated_teacher(\stdClass $value) : void 
    {
        global $DB;
        $where = array('id' => $value->id);
        $DB->delete_records('block_needtodo', $where);
    }

}
