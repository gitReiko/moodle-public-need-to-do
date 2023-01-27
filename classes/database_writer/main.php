<?php 

namespace NTD\Classes\DatabaseWriter;

require_once __DIR__.'/../components/messanger/database_writer/main.php';
require_once __DIR__.'/../components/forum/database_writer/main.php';

use \NTD\Classes\Lib\Getters\Common as cGetter;
use NTD\Classes\Lib\Enums as Enums; 

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
            $this->write_messsanger();
            $this->write_forum();
            // quiz works
            // assign works
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



}
