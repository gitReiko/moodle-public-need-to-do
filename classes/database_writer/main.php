<?php 

namespace NTD\Classes\DatabaseWriter;

require_once __DIR__.'/../lib/getters/common.php';
require_once __DIR__.'/../lib/enums.php';
require_once __DIR__.'/../components/messanger/database_writer.php';

use \NTD\Classes\Lib\Getters\Common as cGetter;
use NTD\Classes\Lib\Enums as Enums; 

/**
 * Writes data to the database.
 * 
 * This data is subsequently used by the renderer to quickly form a block.
 * 
 */
class Main 
{

    /**
     * The level at which data can be updated.
     * 
     * Can be site, course category or user.
     */
    private $updateLevel;

    /**
     * All teachers whose work is monitored by the block
     */
    private $teachers;

    /**
     * Prepares data for the class.
     */
    function __construct(string $updateLevel) 
    {
        $this->updateLevel = $updateLevel;
        $this->teachers = $this->get_teachers();
    }

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
     * Returns all teachers whose work data needs to be updated.
     * 
     * @return array if teachers exist
     * @return null if not
     * 
     * @todo handle teachers from custom block instances
     */
    private function get_teachers() 
    {
        if($this->updateLevel === Enums::UPDATE_DATA_ON_SITE_LEVEL)
        {
            return cGetter::get_teachers_from_global_block_settings();
        }
        else if($this->updateLevel === Enums::UPDATE_DATA_ON_COURSE_CATEGORY_LEVEL)
        {
            //return $this->get_teachers_from_local_block_settings();
        }
        else if($this->updateLevel === Enums::UPDATE_DATA_ON_USER_LEVEL)
        {
            return cGetter::get_teachers_array_with_user_only();
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
    private function write_messsanger() : void 
    {
        $messangerWriter = new \NTD\Classes\Components\Messanger\DatabaseWriter(
            $this->teachers 
        );
        $messangerWriter->write();
    }



}
