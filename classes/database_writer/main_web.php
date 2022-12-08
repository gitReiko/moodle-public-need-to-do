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
 */
class MainWeb extends Main 
{

    /**
     * Block instance config.
     */
    private $config;

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
    function __construct(\stdClass $config, string $updateLevel) 
    {
        $this->config = $config;
        $this->updateLevel = $updateLevel;
        $this->teachers = $this->get_teachers();
    }

    /**
     * Returns all teachers whose work data needs to be updated.
     * 
     * @return array if teachers exist
     * @return null if not
     * 
     * @todo handle teachers from custom block instances
     */
    protected function get_teachers() 
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

}
