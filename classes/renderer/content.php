<?php 

namespace NTD\Classes\Renderer;

require_once __DIR__.'/../lib/getters/common.php';
require_once __DIR__.'/../database_writer/main.php';

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


        return 'from block';
    }

}
