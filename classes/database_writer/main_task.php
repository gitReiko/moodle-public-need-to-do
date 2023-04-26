<?php 

namespace NTD\Classes\DatabaseWriter;

require_once __DIR__.'/../lib/enums.php';
require_once __DIR__.'/../lib/getters/teachers.php';
require_once __DIR__.'/../lib/getters/common.php';
require_once __DIR__.'/main.php';

use \NTD\Classes\Components\Messanger\DatabaseWriter\Main as MessangerDatabaseWriter;
use \NTD\Classes\Components\Forum\DatabaseWriter\Main as ForumDatabaseWriter;
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

}
