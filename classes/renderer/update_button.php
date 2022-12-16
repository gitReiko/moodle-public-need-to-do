<?php 

namespace NTD\Classes\Renderer;

use NTD\Classes\Lib\Common as cLib; 
use NTD\Classes\Lib\Enums as Enums; 

/**
 * Forms an update button for a block in html format.
 * 
 */
class UpdateButton 
{

    /**
     * Block instance params.
     */
    private $params;

    /**
     * Prepares data and updates data if necessary.
     * 
     * @param stdClass $params
     */
    function __construct(\stdClass $params)
    {
        $this->params = $params;
    }

    /**
     * Returns an update button for a block in html format.
     * 
     * @return string update button for a block
     */
    public function get_update_button() : string 
    {
        $btn = $this->get_html_form_start();

        if(cLib::is_user_can_monitor_other_users())
        {
            $btn.= $this->get_param_update_data_on_block_instance_level();
        }
        else 
        {
            $btn.= $this->get_param_update_data_on_user_level();
        }
        
        $btn.= $this->get_param_block_instance();
        $btn.= $this->get_button();

        $btn.= $this->get_html_form_end();

        return $btn;
    }

    /**
     * Returns start of html form. 
     * 
     * @return string start of html form
     */
    private function get_html_form_start() : string 
    {
        $attr = array('method' => 'post');
        return \html_writer::start_tag('form', $attr);
    }

    
    /**
     * Returns param required to update the data at block instance level.
     * 
     * @return string param required to update 
     */
    private function get_param_update_data_on_block_instance_level() : string 
    {
        $attr = array(
            'type' => 'hidden',
            'name' => Enums::NEEDTODO_UPDATE_BUTTON,
            'value' => Enums::UPDATE_DATA_ON_BLOCK_INSTANCE_LEVEL
        );
        return \html_writer::empty_tag('input', $attr);
    }

    /**
     * Returns param required to update the data at the user level.
     * 
    * @return string param required to update 
     */
    private function get_param_update_data_on_user_level() : string 
    {
        $attr = array(
            'type' => 'hidden',
            'name' => Enums::NEEDTODO_UPDATE_BUTTON,
            'value' => Enums::UPDATE_DATA_ON_USER_LEVEL
        );
        return \html_writer::empty_tag('input', $attr);
    }

    /**
     * Returns block instance param. 
     * 
     * It is needed because there can be multiple instances of the block on the page.
     * 
     * @return string block instance param
     */
    private function  get_param_block_instance() : string 
    {
        $attr = array(
            'type' => 'hidden',
            'name' => Enums::BLOCK_INSTANCE,
            'value' => $this->params->instance
        );
        return \html_writer::empty_tag('input', $attr);
    }

    /**
     * Returns html button.
     * 
     * @return string html button
     */
    private function get_button() : string 
    {
        $attr = array(
            'type' => 'submit',
            'name' => 'needtodo_button',
            'value' => get_string('update_data', 'block_needtodo')
        );

        return \html_writer::empty_tag('input', $attr);
    }

    /**
     * Returns end of html form. 
     * 
     * @return string end of html form
     */
    private function get_html_form_end() : string 
    {
        return \html_writer::end_tag('form');
    }

}
