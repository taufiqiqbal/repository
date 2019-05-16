<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends AdminPanel {

   public function __construct() {
      parent::__construct();
   }

   public function index() {
      $footerJs = [
         'detail' => $this->_getDetailContent()
      ];
         
      $this->data = [
         'title' => 'Settings',
         'internalJs' => script_tag('assets/js/admin/settings.bundle.js'),
         'footerJs' => 'var content = '.json_encode($footerJs).';'
      ];

      $this->load->view('AdminPanel', $this->data);
   }

   public function handleUpload() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => []];
      
         $validation = [
            ['field' => 'fieldName', 'rules' => 'required']
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            $fieldName = post('fieldName');

            $file = $this->uploadImage('file', [
               'upload_path' => 'assets/images/',
               'oldFile' => post('oldFile')
            ]);

            $update[$fieldName] = $file['file_name'];
            $this->db->update('tb_settings', $update);

            $response['status'] = true;
            $response['content'] = $file['file_name'];
         }
         $this->output($response);
      } else {
         show_404();
      }
   }

   private function _getDetailContent() {
      $response = [
         'appFavicon' => settings('appFavicon'),
         'appLogo' => settings('appLogo'),
         'appName' => settings('appName'),
         'adminEmail' => settings('adminEmail')
      ];
      return $response;
   }

   public function submit() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => []];
      
         $validation = [
            ['field' => 'appName', 'rules' => 'required', 'errors' => [
               'required' => 'Tidak boleh kosong.'
            ]],
            ['field' => 'appEmail', 'rules' => 'required|valid_email', 'errors' => [
               'required' => 'Tidak boleh kosong.',
               'valid_email' => 'Email tidak valid.'
            ]]
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            $response['status'] = true;
         } else {
            foreach ($_POST as $key => $val) {
               $response['errors'][$key] = form_error($key) ? true : false;
               $response['message'][$key] = strip_tags(form_error($key));
            }
         }
         $this->output($response);
      } else {
         show_404();
      }
   }

}