<?php 

namespace NTD\Classes\Renderer;

require_once __DIR__.'/../lib/getters/common.php';
require_once __DIR__.'/../lib/common.php';
require_once __DIR__.'/../lib/enums.php';
require_once __DIR__.'/../database_writer/main.php';
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
     * Updates data if necessary.
     */
    function __construct()
    {
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

        if(cLib::is_user_site_manager())
        {
            $content.= $this->get_manager_part_of_block();
        }

        return $content;
    }

    /**
     * Updates data if necessary.
     * 
     * @return void 
     */
    private function update_data_if_necessary() : void 
    {
        $updateLevel = optional_param(Enums::NEEDTODO_UPDATE_BUTTON, null, PARAM_TEXT);

        if($updateLevel !== null)
        {
            $writer = new \NTD\Classes\DatabaseWriter\Main($updateLevel);
            $writer->write_to_database();
        }
    }

    /**
     * Returns update button in html format.
     * 
     * @return string button in html format
     */
    private function get_update_button() : string 
    {
        $renderer = new UpdateButton;
        return $renderer->get_update_button();
    }

    /**
     * Returns my work part of the block.
     * 
     * @return string my works part of the block
     */
    private function get_my_work() : string 
    {
        $renderer = new MyWork;
        return $renderer->get_my_work();
    }

    /**
     * Returns html of manager part of block.
     * 
     * @return string html of manager part of block
     */
    private function get_manager_part_of_block() : string 
    {
        $renderer = new Manager;
        return $renderer->get_manager_part();
    }

}
