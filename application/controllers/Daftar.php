<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Daftar extends CI_Controller {

   public function __construct() {
      parent::__construct();
   }

   public function index() {
      $this->data = [
         'title' => 'Daftar Akun Baru',
         'internalJs' => script_tag('assets/js/login/daftar.bundle.js')
      ];

      $this->load->view('LoginPanel', $this->data);
   }

   public function submit() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => []];
      
         $validation = [
            ['field' => 'fullname', 'rules' => 'required', 'errors' => [
               'required' => 'Tidak boleh kosong.'
            ]],
            ['field' => 'username', 'rules' => 'required|callback_checkUsername', 'errors' => [
               'required' => 'Tidak boleh kosong.'
            ]],
            ['field' => 'email', 'rules' => 'required|valid_email|callback_checkEmail', 'errors' => [
               'required' => 'Tidak boleh kosong.',
               'valid_email' => 'Email tidak valid.'
            ]],
            ['field' => 'password', 'rules' => 'required|matches[confirmPassword]', 'errors' => [
               'required' => 'Tidak boleh kosong.',
               'matches' => 'Password tidak cocok.'
            ]],
            ['field' => 'confirmPassword', 'rules' => 'required|matches[password]', 'errors' => [
               'required' => 'Tidak boleh kosong.',
               'matches' => 'Password tidak cocok.'
            ]]
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            $this->db->insert('tb_users', [
               'fullname' => post('fullname'),
               'username' => post('username'),
               'email' => post('email'),
               'password' => password_hash(post('password'), PASSWORD_BCRYPT),
               'role' => '2',
               'uploaded' => date('Y-m-d H:i:s')
            ]);

            $response['status'] = true;
         } else {
            foreach ($_POST as $key => $val) {
               $response['errors'][$key] = form_error($key) ? true : false;
               $response['message'][$key] = strip_tags(form_error($key));
            }
         }
         $this->output->set_content_type('application/json')->set_output(json_encode($response));
      } else {
         show_404();
      }
   }

   public function checkUsername($str) {
      $query = $this->db
         ->select('username')
         ->from('tb_users')
         ->where('username', $str)
         ->get();
      
      if ($query->num_rows() > 0) {
         $this->form_validation->set_message('checkUsername', 'Username sudah terdaftar.');
         return false;
      } else {
         return true;
      }
   }

   public function checkEmail($str) {
      $query = $this->db
         ->select('email')
         ->from('tb_users')
         ->where('email', $str)
         ->get();
      
      if ($query->num_rows() > 0) {
         $this->form_validation->set_message('checkEmail', 'Email sudah terdaftar.');
         return false;
      } else {
         return true;
      }
   }

}