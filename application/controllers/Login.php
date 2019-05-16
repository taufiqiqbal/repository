<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use \Firebase\JWT\JWT;

class Login extends CI_Controller {

   public function __construct() {
      parent::__construct();
   }

   public function index() {
      $this->data = [
         'title' => 'Login',
         'internalJs' => script_tag('assets/js/login/login.bundle.js')
      ];

      $this->_checkLoginSession();
      $this->load->view('LoginPanel', $this->data);
   }

   public function submit() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => []];
      
         $validation = [
            ['field' => 'username', 'rules' => 'required', 'errors' => [
               'required' => 'Tidak boleh kosong.'
            ]],
            ['field' => 'password', 'rules' => 'required', 'errors' => [
               'required' => 'Tidak boleh kosong.'
            ]]
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            $username = post('username');
            $password = post('password');
            
            if ($this->_resolve_user_login($username, $password)) {
               $user = $this->_get_user_id_from_username($username);

               $this->session->set_userdata(array(
                  'isLogin' => true,
                  'idUsers' => (int) $user['id']
               ));

               $response['status'] = true;
            }
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

   private function _resolve_user_login($username, $password) {
      $hash = $this->db
			->select('password')
			->from('tb_users')
         ->where('username', $username)
			->get()
			->row('password');

		return $this->_verify_password_hash($password, $hash);
   }

   private function _get_user_id_from_username($username) {
      return $this->db
			->select('id')
			->from('tb_users')
         ->where('username', $username)
			->get()
			->row_array();
   }

   private function _verify_password_hash($password, $hash) {
      return password_verify($password, $hash);
   }

   public function logout() {
      $this->session->unset_userdata('isLogin');
      $this->session->unset_userdata('idUsers');
      $this->session->sess_destroy();
      redirect('login');
   }

   private function _checkLoginSession() {
      $isLogin = $this->session->userdata('isLogin');
      $idUsers = $this->session->userdata('idUsers');

      if ($isLogin && $idUsers) {
         redirect('/adminpanel/dashboard');
      }
   }

   public function submitForgotPassword() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => []];
      
         $validation = [
            ['field' => 'emailForgot', 'rules' => 'required|valid_email|callback_checkEmail', 'errors' => [
               'required' => 'Tidak boleh kosong.',
               'valid_email' => 'Email tidak valid.'
            ]]
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            require APPPATH . 'vendor/autoload.php';

            $mail = new PHPMailer(true);
            try {
               //Server settings
               $mail->SMTPDebug     = 0;
               $mail->isSMTP();
               $mail->Host          = 'smtp.gmail.com';
               $mail->SMTPAuth      = true;
               $mail->Username      = 'sqone.developer@gmail.com';
               $mail->Password      = 'zrghseciyhkayrac';
               $mail->SMTPSecure    = 'tls';
               $mail->Port          = 587;
               $mail->SMTPOptions   = [
                  'ssl' => [
                     'verify_peer' => false,
                     'verify_peer_name' => false,
                     'allow_self_signed' => true
                  ]
               ];

               //Recipients
               $mail->setFrom('sqone.developer@gmail.com', 'Aplikasi Taufik');
               $mail->addAddress(post('emailForgot'), 'Pulihkan Kata Sandi');

               // Content
               $key = $this->config->item('jwt_key');
               $token = [
                  'email' => post('emailForgot')
               ];
               $jwt = JWT::encode($token, $key);
               $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http';
               $protocol = isset($_SERVER["HTTPS"]) ? 'https://' : 'http://';

               $mail->isHTML(true);
               $mail->Subject = 'Pulihkan Kata Sandi';
               $mail->Body    = '<a href="'.$protocol.site_url('login/validateResetPassword/' . $jwt).'">'.$protocol.site_url('login/validateResetPassword/' . $jwt).'</a>';

               $mail->send();
               $response['status'] = true;
               $response['content'] = 'Pesan telah terkirim';
            } catch (Exception $e) {
               $response['content'] = "Pesan gagal terkirim";
            }
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

   public function checkEmail($str) {
      $query = $this->db
         ->select('email')
         ->from('tb_users')
         ->where('email', $str)
         ->get();
      
      if (!$query->num_rows() > 0) {
         if (empty($str))
            $this->form_validation->set_message('checkEmail', 'Tidak boleh kosong.');
         else
            $this->form_validation->set_message('checkEmail', 'Email anda masukkan tidak terdaftar.');
         return false;
      } else {
         return true;
      }
   }

   public function validateResetPassword() {
      require APPPATH . 'vendor/autoload.php';

      try {
         $jwt = $this->uri->segment(3);
         $decoded = (array) JWT::decode($jwt, $this->config->item('jwt_key'), ['HS256']);
         $email = $decoded['email'];

         $query = $this->db
            ->select('email')
            ->from('tb_users')
            ->where('email', $email)
            ->get();
         
         if ($query->num_rows() > 0) {
            $footerJs = ['email' => $email];

            $this->data = [
               'title' => 'Pulihkan Kata Sandi',
               'internalJs' => script_tag('assets/js/login/reset_pass.bundle.js'),
               'footerJs' => 'var content = '.json_encode($footerJs).';'
            ];

            $this->load->view('LoginPanel', $this->data);
         } else {
            show_404();
         }
      } catch (\Exception $e) {
         show_404();
      }
   }

   public function submitNewPassword() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => []];
      
         $validation = [
            ['field' => 'email', 'rules' => 'required|valid_email|callback_checkEmail'],
            ['field' => 'newPassword', 'rules' => 'required|matches[confirmNewPassword]', 'errors' => [
               'required' => 'Tidak boleh kosong.',
               'matches' => 'Password tidak cocok.'
            ]],
            ['field' => 'confirmNewPassword', 'rules' => 'required|matches[newPassword]', 'errors' => [
               'required' => 'Tidak boleh kosong.',
               'matches' => 'Konfirmasi password tidak cocok.'
            ]]
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            $email = post('email');
            $newPassword = post('newPassword');

            $this->db->where('email', $email);
            $this->db->update('tb_users', [
               'password' => password_hash($newPassword, PASSWORD_BCRYPT)
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

}