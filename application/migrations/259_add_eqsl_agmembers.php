<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_add_eqsl_agmembers extends CI_Migration
{
   public function up() {
      if (!$this->db->table_exists('eQSL_agmembers')) {
            $this->dbforge->add_field(array(
                'id' => array(
                    'type' => 'INT',
                    'constraint' => 20,
                    'unsigned' => TRUE,
                    'auto_increment' => TRUE,
                    'unique' => TRUE
                ),
                'callsign' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 32,
                    'unsigned' => TRUE,
                ),
                'creation_date' => array(
                   'type' => 'TIMESTAMP',
                   'null' => FALSE,
                   'default' => 'CURRENT_TIMESTAMP'
                ),
                'last_modified' => array(
                   'type' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
                   'null' => FALSE
                )

            ));

            $this->dbforge->add_key('id', true);
            $this->dbforge->create_table('eQSL_agmembers');
            $this->db->query("ALTER TABLE eQSL_agmembers ADD UNIQUE INDEX `callsign_UNIQUE` (`callsign` ASC)");
            // add_key('id') automatically adds an index on this field.
            $this->db->query("ALTER TABLE eQSL_agmembers DROP INDEX id");
        }

   }

   public function down() {
      if ($this->db->table_exists('eQSL_agmembers')) {
        $this->dbforge->drop_table('eQSL_agmembers');
      }
   }
}
