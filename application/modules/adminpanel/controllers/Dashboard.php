<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends AdminPanel {

   public function __construct() {
      parent::__construct();
   }

   public function index() {
      $this->data = [
         'title' => 'Dashboard',
         'internalJs' => script_tag('assets/js/admin/dashboard.bundle.js')
      ];

      $this->load->view('AdminPanel', $this->data);
   }

}