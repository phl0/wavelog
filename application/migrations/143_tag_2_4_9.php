<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
*   Tag Wavelog as 2.4.9
*/

class Migration_tag_2_4_9 extends CI_Migration {

    public function up()
    {
    
        // Tag Wavelog 2.4.9
        $this->db->where('option_name', 'version');
        $this->db->update('options', array('option_value' => '2.4.9'));
    }

    public function down()
    {
        $this->db->where('option_name', 'version');
        $this->db->update('options', array('option_value' => '2.4.8'));
    }
}