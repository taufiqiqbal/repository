<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Account extends AdminPanel {

   protected $idUsers = null;
   protected $role = null;

   public function __construct() {
      parent::__construct();

      $this->idUsers = usersLogin('id');
      $this->role = usersLogin('role');
      $this->load->model('users/AccountModel', 'model', true);
   }

   public function index() {
      $this->data = [
         'title' => 'Account',         
         'internalCss' => css_tag($this->config->item('datatable')['css']),
         'internalJs' => script_tag([
            $this->config->item('datatable')['js'],
            'assets/js/admin/account_lists.bundle.js'
         ])
      ];

      $this->load->view('AdminPanel', $this->data);
   }
   
   public function tambah() {
      $this->data = [
         'title' => 'Add Account',
         'pageType' => 'insert',
         'internalJs' => script_tag('assets/js/admin/account_forms.bundle.js')
      ];

      $this->load->view('AdminPanel', $this->data);
   }
   
   public function edit() {
      $footerJs = [
         'detail' => $this->model->getUserEdit($this->segment5)
      ];

      $this->data = [
         'title' => 'Edit Account',
         'pageType' => 'update',
         'internalJs' => script_tag('assets/js/admin/account_forms.bundle.js'),
         'footerJs' => 'var content = '.json_encode($footerJs).';'
      ];

      $this->load->view('AdminPanel', $this->data);
   }

   public function submit() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => []];
         $pageType = post('pageType');
      
         $validation = [
            ['field' => 'pageType', 'rules' => 'required'],
            ['field' => 'id', 'rules' => ($pageType === 'update' ? 'required|numeric' : '')],
            ['field' => 'fullname', 'rules' => 'required', 'errors' => [
               'required' => 'Tidak boleh kosong.'
            ]],
            ['field' => 'username', 'rules' => ($pageType === 'insert' ? 'required|callback_checkDuplicate[username]' : ''), 'errors' => [
               'required' => 'Tidak boleh kosong.'
            ]],
            ['field' => 'email', 'rules' => ($pageType === 'insert' ? 'required|valid_email|callback_checkDuplicate[email]' : ''), 'errors' => [
               'required' => 'Tidak boleh kosong.',
               'valid_email' => 'Email tidak valid.'
            ]],
            ['field' => 'password', 'rules' => ($pageType === 'insert' ? 'required|matches[confirmPassword]' : 'matches[confirmPassword]'), 'errors' => [
               'required' => 'Tidak boleh kosong.',
               'matches' => 'Password tidak cocok.'
            ]],
            ['field' => 'confirmPassword', 'rules' => ($pageType === 'insert' ? 'required|matches[password]' : 'matches[password]'), 'errors' => [
               'required' => 'Tidak boleh kosong.',
               'matches' => 'Konfirmasi password tidak cocok.'
            ]],
            ['field' => 'role', 'rules' => 'required|numeric', 'errors' => [
               'required' => 'Tidak boleh kosong.',
               'numeric' => 'Hanya boleh diisi dengan angka.'
            ]],
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            $this->model->submit($pageType);
            $response['status'] = true;
            $response['message'] = 'Data berhasil disimpan.';
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

   public function checkDuplicate($str, $field) {
      $query = $this->db
         ->select($field)
         ->from('tb_users')
         ->where($field, $str)
         ->get();
      
      $message = [
         'email' => 'Email sudah terdaftar.',
         'username' => 'Username sudah terdaftar.'
      ];
      if ($query->num_rows() > 0) {
         $this->form_validation->set_message('checkDuplicate', $message[$field]);
         return false;
      } else {
         return true;
      }
   }

   public function delete() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => []];
      
         $validation = [
            ['field' => 'id', 'rules' => 'required|numeric']
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            $id = post('id');

            $this->db
               ->where('id', $id)
               ->delete('tb_users');
            $response['status'] = true;
         }
         $this->output($response);
      } else {
         show_404();
      }
   }

   public function getData() {
      if ($this->input->is_ajax_request()) {
         $query = $this->model->getData();
   
         $i = $_POST['start'];
         $response = array();
         foreach ($query->result() as $data) {
            $i++;
   
            $action = '<div class="btn-group btn-group-xs">';
            $action .= '<a href="'.site_url(($this->idUsers !== $data->id ? 'adminpanel/users/account/edit/' . $data->id : 'adminpanel/users/profile')).'" class="btn waves-effect waves-light btn-outline-warning btn-sm"><i class="fas fa-edit"></i></a>';
            if ($this->idUsers !== $data->id && $this->role === '1')
               $action .= '<a data-id="'.$data->id.'" id="delete" class="btn waves-effect waves-light btn-outline-danger btn-sm"><i class="fas fa-trash-alt"></i></a>';
            $action .= '</div>';
   
            $result = array();
            $result[] = $i;
            $result[] = $data->fullname;
            $result[] = $data->username;
            $result[] = $data->email;
            $result[] = userRole($data->role);
            $result[] = $action;
   
            $response[] = $result;
         }
   
         $output = array(
            'draw' => intval($_POST['draw']),
            'recordsTotal' => intval($this->model->countData()),
            'recordsFiltered' => intval($this->model->filteredData()),
            'data' => $response
         );
         $this->output($output);
      } else {
         show_404();
      }
   }

}