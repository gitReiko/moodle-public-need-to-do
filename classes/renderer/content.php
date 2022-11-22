<?php 

namespace NTD\Classes\Renderer;

require_once __DIR__.'/../lib/getters/common.php';

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

        $cohorts = new \NTD\Classes\Lib\Getters\Common;
        print_r($cohorts->get_cohort_teachers_from_global_settings());


        return 'from block';
    }

}
