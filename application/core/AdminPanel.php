<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AdminPanel extends CI_Controller {

   protected $segment4;
   protected $segment5;

   public function __construct() {
      parent::__construct();

      $this->_checkLoginSession();
      $this->segment4 = $this->uri->segment(4);
      $this->segment5 = $this->uri->segment(5);
   }

   function index() {}

   public function uploadImage($name, $params = []) {
      $config = [
         'upload_path' => FCPATH . $params['upload_path'],
         'allowed_types' => 'jpg|jpeg|bmp|png|gif',
         'max_size' => 2048,
         'detect_mime' => true,
         'remove_spaces' => true,
         'file_ext_tolower' => true
      ];
      $this->load->library('upload', $config);
      if ($this->upload->do_upload($name)) {
         $data = $this->upload->data();
         @chmod($data['full_path'], 0777);
         @unlink(FCPATH . $params['upload_path'] . @$params['oldFile']);

         return $data;
      }
   }
   
   public function uploadFile($name, $params = []) {
      $config = [
         'upload_path' => FCPATH . $params['upload_path'],
         'allowed_types' => $params['allowed_types'],
         'max_size' => 0,
         'detect_mime' => true,
         'remove_spaces' => true,
         'file_ext_tolower' => true
      ];
      $this->load->library('upload', $config);
      if ($this->upload->do_upload($name)) {
         $data = $this->upload->data();
         @chmod($data['full_path'], 0777);
         @unlink(FCPATH . $params['upload_path'] . @$params['oldFile']);

         return $data;
      }
   }

   public function output($content) {
      $this->output
         ->set_content_type('application/json')
         ->set_output(json_encode($content));
   }

   private function _checkLoginSession() {
      $isLogin = $this->session->userdata('isLogin');
      $idUsers = $this->session->userdata('idUsers');

      if (!$isLogin && !$idUsers) {
         redirect('login');
      }
   }

}