<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class KategoriModel extends CI_Model {

   public function __construct() {
      parent::__construct();
   }

   function getEditKategori($id) {
      $query = $this->db
         ->select('nama')
         ->from('tb_categories')
         ->where('id', $id)
         ->get();
      
      if ($query->num_rows() > 0) {
         $list_fields = $query->list_fields();
         $data = $query->row_array();
      
         $response = [];
         foreach ($list_fields as $fields) {
            $response[$fields] = (string) $data[$fields];
         }
         return $response;
      } else {
         show_404();
      }
   }

   function submit($pageType) {
      $nama = post('nama');

      if ($pageType === 'insert') {
         $this->db->insert('tb_categories', [
            'nama' => $nama,
            'slug' => url_title($nama, 'dash', true),
            'uploaded' => date('Y-m-d H:i:s')
         ]);
      } else if ($pageType === 'update') {
         $id = post('id');

         $this->db->where('id', $id);
         $this->db->update('tb_categories', [
            'nama' => $nama,
            'slug' => url_title($nama, 'dash', true)
         ]);
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
         ->select('id, nama')
         ->from('tb_categories');
   
      $i = 0;
      $column_search = ['nama'];
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