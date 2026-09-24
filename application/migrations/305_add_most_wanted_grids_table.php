<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_most_wanted_grids_table extends CI_Migration {

    public function up()
    {
        // Create Label Designer Templates table
        if (!$this->db->table_exists('most_wanted_grids')) {
            $this->dbforge->add_field(array(
                'id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'auto_increment' => TRUE,
                    'null' => FALSE
                ),
                'grid' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 4,
                    'null' => FALSE
                ),
                'perc' => array(
                    'type' => 'INT',
                    'constraint' => 3,
                    'null' => TRUE,
                )
            ));
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->create_table('most_wanted_grids');
        }
    }

    public function down()
    {
        $this->dbforge->drop_table('most_wanted_grids');
    }

}
