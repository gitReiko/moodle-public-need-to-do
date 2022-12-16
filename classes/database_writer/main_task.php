<?php 

namespace NTD\Classes\DatabaseWriter;

require_once __DIR__.'/../lib/enums.php';
require_once __DIR__.'/../lib/getters/common.php';
require_once __DIR__.'/main.php';

use \NTD\Classes\Lib\Getters\Common as cGetter;
use NTD\Classes\Lib\Enums as Enums; 

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
        $this->teachers = $this->get_teachers();
    }

    /**
     * Returns all teachers from global and local settings whose work data needs to be updated.
     * 
     * @return array if teachers exist
     * @return null if not
     * 
     * @todo Восстановить работу в кроне. Учитывать глобальные данные и данные всех экземпляров блоков.
     */
    protected function get_teachers() 
    {
        //$teachers = cGetter::get_teachers_from_global_block_settings();

        // all block instances

        return $teachers;
    }

    /**
     * Writes messanger related information to database.
     * 
     * @return void
     */
    protected function write_messsanger() : void 
    {
        $messangerWriter = new \NTD\Classes\Components\Messanger\DatabaseWriter(
            $this->teachers,
            Enums::UPDATE_DATA_ON_SITE_LEVEL
        );
        $messangerWriter->write();
    }

}
