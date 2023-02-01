<?php 

namespace NTD\Classes\Renderer;

require_once __DIR__.'/../lib/getters/common.php';
require_once __DIR__.'/../lib/common.php';
require_once __DIR__.'/../lib/enums.php';
require_once __DIR__.'/../database_writer/main_web.php';
require_once 'manager.php';
require_once 'my_work.php';
require_once 'update_button.php';

use NTD\Classes\Lib\Common as cLib; 
use NTD\Classes\Lib\Enums as Enums; 

/**
 * Forms the content of block Need to do.
 */
class Content 
{

    /**
     * Block instance params.
     */
    private $params;

    /**
     * Level on which data must be updated.
     */
    private $updateLevel;

    /**
     * Prepares data and updates data if necessary.
     * 
     * @param stdClass params of block instance
     */
    function __construct(\stdClass $params)
    {
        $this->params = $params;
        $this->updateLevel = $this->get_update_level_from_post();

        $this->update_data_if_necessary();
    }

    /**
     * Returns html content.
     * 
     * @return string html content
     */
    public function get_content() : string 
    {
        $content = $this->get_update_button();
        $content.= $this->get_my_work();

        if(cLib::is_user_can_monitor_other_users())
        {
            $content.= $this->get_manager_part_of_block();
        }

        return $content;
    }

    /**
     * Returns update level of data update if it exists.
     * 
     * @return string of update level
     * @return null if not exists
     */
    private function get_update_level_from_post()
    {
        return optional_param(Enums::NEEDTODO_UPDATE_BUTTON, null, PARAM_TEXT);;
    }

    /**
     * Updates data if necessary.
     * 
     * @return void 
     */
    private function update_data_if_necessary() : void 
    {
        if($this->is_update_necessary())
        {
            if($this->is_update_necessary_for_this_block_instance())
            {
                $writer = new \NTD\Classes\DatabaseWriter\MainWeb($this->params, $this->updateLevel);
                $writer->write_to_database();
            }
        }
    }

    /**
     * Returns true if update necessary.
     * 
     * @return bool 
     */
    private function is_update_necessary() 
    {
        if($this->updateLevel)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    /**
     * Returns true if update necessary for this block instance.
     * 
     * @return bool 
     */
    private function is_update_necessary_for_this_block_instance()
    {
        $blockInstance = optional_param(Enums::BLOCK_INSTANCE, null, PARAM_TEXT);

        if($blockInstance == $this->params->instance)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    /**
     * Returns update button in html format.
     * 
     * @return string button in html format
     */
    private function get_update_button() : string 
    {
        $renderer = new UpdateButton($this->params);
        return $renderer->get_update_button();
    }

    /**
     * Returns my work part of the block.
     * 
     * @return string my works part of the block
     */
    private function get_my_work() : string 
    {
        $renderer = new MyWork($this->params);
        return $renderer->get_my_work();
    }

    /**
     * Returns html of manager part of block.
     * 
     * @return string html of manager part of block
     */
    private function get_manager_part_of_block() : string 
    {
        $renderer = new Manager($this->params);
        return $renderer->get_manager_part();
    }

}
