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
     * Returns an update button for a block in html format.
     * 
     * @return string update button for a block
     */
    public function get_update_button() : string 
    {
        $btn = $this->get_html_form_start();

        if(cLib::is_user_site_manager())
        {
            $btn.= $this->get_update_all_site_data_param();
        }
        
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
     * Returns param required to update all site data.
     * 
     * @return string param required to update all site data
     */
    private function get_update_all_site_data_param() : string 
    {
        $attr = array(
            'type' => 'hidden',
            'name' => Enums::NEEDTODO_UPDATE_BUTTON,
            'value' => Enums::NEEDTODO_SITE_UPDATE
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
