<?php 

namespace NTD\Classes\DatabaseWriter;

require_once __DIR__.'/../components/messanger/database_writer/main.php';
require_once __DIR__.'/../components/assign/database_writer/main.php';
require_once __DIR__.'/../components/coursework/database_writer/main.php';
require_once __DIR__.'/../components/forum/database_writer/main.php';
require_once __DIR__.'/../components/quiz/database_writer/main.php';
require_once __DIR__.'/../lib/getters/teachers.php';
require_once __DIR__.'/../lib/common.php';

use \NTD\Classes\Lib\Common as cLib;
use \NTD\Classes\Lib\Enums as Enums; 

/**
 * Writes data to the database.
 * 
 * This data is subsequently used by the renderer to quickly form a block.
 * 
 */
abstract class Main 
{

    /**
     * Block instance config.
     */
    protected $config;

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
     * Writes all necessary for block data into database.
     * 
     * @return void 
     */
    public function write_to_database() : void
    {
        if(is_array($this->teachers))
        {
            if(get_config('block_needtodo', 'enable_chat_messages'))
            {
                $this->write_messsanger();
            }

            if(get_config('block_needtodo', 'enable_forum'))
            {
                $this->write_forum();
            }

            if(get_config('block_needtodo', 'enable_quiz'))
            {
                $this->write_quiz();
            }

            if(get_config('block_needtodo', 'enable_assign'))
            {
                $this->write_assign();
            }

            if(cLib::is_coursework_module_installed())
            {
                if(get_config('block_needtodo', 'enable_coursework'))
                {
                    $this->write_coursework();
                }
            }
        }
    }

    /**
     * Writes messanger related information to database.
     * 
     * @return void
     */
    abstract protected function write_messsanger() : void ;

    /**
     * Writes data related to forum into database.
     * 
     * @return void
     */
    abstract protected function write_forum() : void ;

    /**
     * Writes data related to quiz into database.
     * 
     * @return void
     */
    abstract protected function write_quiz() : void ;

    /**
     * Writes data related to assign into database.
     * 
     * @return void
     */
    abstract protected function write_assign() : void ;

    /**
     * Writes data related to coursework into database.
     * 
     * @return void
     */
    abstract protected function write_coursework() : void ;

}
