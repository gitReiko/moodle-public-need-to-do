<?php 

namespace NTD\Classes\Renderer;

require_once __DIR__.'/../lib/getters/common.php';
require_once __DIR__.'/../lib/common.php';
require_once __DIR__.'/../database_writer/main.php';
require_once 'manager.php';

use NTD\Classes\Lib\Common as cLib; 

/**
 * Forms the content of block Need to do.
 */
class Content 
{

    /**
     * Returns html content.
     * 
     * @return string html content
     */
    public function get_content() : string 
    {

        $writer = new \NTD\Classes\DatabaseWriter\Main;
        $writer->write_to_database();

        $blockContent = 'from block';


        if(cLib::is_user_site_manager())
        {
            $blockContent.= $this->get_manager_part_of_block();
        }

        return $blockContent;
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
