<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends AdminPanel {

   protected $idUsers = null;

   public function __construct() {
      parent::__construct();

      $this->idUsers = usersLogin('id');
   }

   public function index() {
      $footerJs = [
         'user' => [
            'avatar' => base_url('assets/images/' . usersLogin('avatar')),
            'oldAvatar' => usersLogin('avatar'),
            'fullname' => usersLogin('fullname'),
            'username' => usersLogin('username'),
            'email' => usersLogin('email')
         ]
      ];

      $this->data = [
         'title' => 'Profile',
         'internalJs' => script_tag('assets/js/admin/profile.bundle.js'),
         'footerJs' => 'var content = '.json_encode($footerJs).';'
      ];

      $this->load->view('AdminPanel', $this->data);
   }

   public function submit() {
      $response = ['status' => false, 'errors' => [], 'message' => []];
      
      $validation = [
         ['field' => 'fullname', 'rules' => 'required', 'errors' => [
            'required' => 'Tidak boleh kosong.'
         ]],
         ['field' => 'password', 'rules' => 'matches[confirmPassword]', 'errors' => [
            'matches' => 'Password tidak cocok.'
         ]],
         ['field' => 'confirmPassword', 'rules' => 'matches[password]', 'errors' => [
            'matches' => 'Konfirmasi password tidak cocok.'
         ]]
      ];
      $this->form_validation->set_rules($validation);
      if ($this->form_validation->run()) {
         $avatar = $this->uploadImage('avatar', [
            'upload_path' => 'assets/images/',
            'oldFile' => post('oldAvatar')
         ]);

         $password = post('password');
         $update['fullname'] = post('fullname');
         if (!empty($avatar['file_name']))
            $update['avatar'] = $avatar['file_name'];
         if (!empty($password))
            $update['password'] = password_hash($password, PASSWORD_BCRYPT);

         $this->db->where('id', $this->idUsers);
         $this->db->update('tb_users', $update);

         $response['status'] = true;
      } else {
         foreach ($_POST as $key => $val) {
            $response['errors'][$key] = form_error($key) ? true : false;
            $response['message'][$key] = strip_tags(form_error($key));
         }
      }
      $this->output($response);
   }

}