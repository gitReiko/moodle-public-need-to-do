<?php 

namespace NTD\Classes\DatabaseWriter;

require_once __DIR__.'/../lib/enums.php';
require_once __DIR__.'/../lib/getters/teachers.php';
require_once __DIR__.'/main.php';

use \NTD\Classes\Components\Messanger\DatabaseWriter\Main as MessangerDatabaseWriter;
use \NTD\Classes\Components\Forum\DatabaseWriter\Main as ForumDatabaseWriter;
use \NTD\Classes\Lib\Getters\Teachers as tGet;
use NTD\Classes\Lib\Enums as Enums; 

/**
 * Writes data to the database.
 * 
 * This data is subsequently used by the renderer to quickly form a block.
 * 
 */
class MainWeb extends Main 
{

    /**
     * Block instance params.
     */
    protected $params;

    /**
     * The level at which data can be updated.
     * 
     * Can be site, course category or user.
     */
    protected $updateLevel;

    /**
     * All teachers whose work is monitored by the block
     */
    protected $teachers;

    /**
     * Prepares data for the class.
     */
    function __construct(\stdClass $params, string $updateLevel) 
    {
        $this->params = $params;
        $this->updateLevel = $updateLevel;
        $this->teachers = $this->get_teachers();
    }

    /**
     * Returns all teachers whose work data needs to be updated.
     * 
     * @return array if teachers exist
     * @return null if not
     */
    protected function get_teachers() 
    {
        if($this->updateLevel === Enums::UPDATE_DATA_ON_BLOCK_INSTANCE_LEVEL)
        {
            return tGet::get_teachers_from_cohort($this->params->cohort);
        }
        else if($this->updateLevel === Enums::UPDATE_DATA_ON_USER_LEVEL)
        {
            return tGet::get_user_who_works_with_block_in_teachers_array();
        }
        else 
        {
            throw new \Exception('Update data on unknown level (block Need to do).');
        }
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
            $this->updateLevel
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
            $this->updateLevel
        );
        $forumWriter->write();
    }

}
