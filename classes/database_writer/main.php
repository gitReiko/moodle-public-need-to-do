<?php 

namespace NTD\Classes\DatabaseWriter;

require_once __DIR__.'/../components/messanger/database_writer.php';

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
     * 
     * @todo блок некорректно работает с кроном (гет тичерс - условие елсе).
     * @todo разделить функцию на 2 для гуи и для скрона
     * @todo крон должен просчитывать учителей из всех групп для всех курсов
     * @todo образение к параметру $this->config->courses_load_type
     */
    public function write_to_database() : void
    {
        if(is_array($this->teachers))
        {
            $this->write_messsanger();
            // forum posts
            // quiz works
            // assign works
        }
    }

    /**
     * Writes messanger related information to database.
     * 
     * @return void
     */
    protected function write_messsanger() : void 
    {
        $messangerWriter = new \NTD\Classes\Components\Messanger\DatabaseWriter(
            $this->teachers 
        );
        $messangerWriter->write();
    }



}
