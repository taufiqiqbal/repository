<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AccountModel extends CI_Model {

   public function __construct() {
      parent::__construct();
   }

   function getUserEdit($id) {
      $query = $this->db
         ->select('fullname, username, email, role')
         ->from('tb_users')
         ->where('id', $id)
         ->get();
      
      if ($query->num_rows() > 0) {
         return $query->row_array();
      } else {
         show_404();
      }
   }

   function submit($pageType) {
      if ($pageType === 'insert') {
         $this->db->insert('tb_users', [
            'fullname' => post('fullname'),
            'username' => post('username'),
            'email' => post('email'),
            'password' => password_hash(post('password'), PASSWORD_BCRYPT),
            'role' => post('role'),
            'uploaded' => date('Y-m-d H:i:s')
         ]);
      } else if ($pageType === 'update') {
         $id = post('id');
         $password = post('password');

         $data['fullname'] = post('fullname');
         if (!empty($password))
            $data['password'] = password_hash($password, PASSWORD_BCRYPT);
         $data['role'] = post('role');

         $this->db->where('id', $id);
         $this->db->update('tb_users', $data);
      }
   }

   function getData() {
      $this->_queryData();
      if ($_POST['length'] !== -1)
         $this->db->limit($_POST['length'], $_POST['start']);
      return $this->db->get();
   }
   
   function countData() {
      $this->_queryData();
      return $this->db->get()->num_rows();
   }
   
   function filteredData() {
      $this->_queryData();
      return $this->db->count_all_results();
   }
   
   private function _queryData() {
      $this->db
         ->select('id, fullname, username, email, role')
         ->from('tb_users');
   
      $i = 0;
      $column_search = ['fullname', 'username', 'email'];
      foreach ($column_search as $item) {
         if ($_POST['search']['value']) {
            if ($i === 0) {
               $this->db->group_start();
               $this->db->like($item, $_POST['search']['value']);
            } else {
               $this->db->or_like($item, $_POST['search']['value']);
            }
   
            if (count($column_search) - 1 === $i)
               $this->db->group_end();
         }
         $i++;
      }
   }

}