<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Make satellite displayname a description

class Migration_rename_sat_description extends CI_Migration
{

   public function up() {
      $this->db->query('ALTER TABLE `satellite` CHANGE `displayname` `description` VARCHAR(255) NULL DEFAULT NULL;');
   }

   public function down() {
      $this->db->query('ALTER TABLE `satellite` CHANGE `description` `displayname` VARCHAR(255) NULL DEFAULT NULL;');
   }

}
