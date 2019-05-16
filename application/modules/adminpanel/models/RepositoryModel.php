<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RepositoryModel extends CI_Model {

   public function __construct() {
      parent::__construct();
   }

   function getPrefences($idRepository) {
      $query = $this->db
         ->select('id, name')
         ->from('tb_prefences')
         ->where('idRepository', $idRepository)
         ->get();
      
      return $query->result_array();
   }

   function insertPrefences($idRepository) {
      $this->db->insert('tb_prefences', [
         'idRepository' => $idRepository,
         'name' => post('name')
      ]);
   }

   function getFiles($idRepository) {
      $query = $this->db
         ->select('*')
         ->from('tb_files')
         ->where('idRepository', $idRepository)
         ->get();
      
      return $query->result_array();
   }

   function handleSuggestKeywords() {
      $this->db->insert('tb_keywords', [
         'idRepository' => post('idRepository'),
         'idMstKeywords' => post('idMstKeywords')
      ]);
   }

   function handleSuggestPenulis() {
      $this->db->insert('tb_penulis', [
         'idRepository' => post('idRepository'),
         'idMstPenulis' => post('idMstPenulis')
      ]);
   }

   function handleSuggestKontributor() {
      $this->db->insert('tb_kontributor', [
         'idRepository' => post('idRepository'),
         'idMstKontributor' => post('idMstKontributor')
      ]);
   }

   function getSuggestKontributor() {
      $query = $this->db
         ->select('id, nama')
         ->from('tb_mst_kontributor')
         ->like('nama', post('suggest'))
         ->get();
      
      return $query->result_array();
   }

   function getSuggestKeywords() {
      $query = $this->db
         ->select('id, label')
         ->from('tb_mst_keywords')
         ->like('label', post('suggest'))
         ->get();
      
      return $query->result_array();
   }
   
   function getSuggestPenulis() {
      $query = $this->db
         ->select('id, nama')
         ->from('tb_mst_penulis')
         ->like('nama', post('suggest'))
         ->get();
      
      return $query->result_array();
   }

   function getCategories() {
      $query = $this->db
         ->select('id, nama')
         ->from('tb_categories')
         ->get();
      
      return $query->result_array();
   }

   function getKontributor() {
      $idRepository = post('idRepository');

      $query = $this->db
         ->select('a.id, b.nama')
         ->from('tb_kontributor a')
         ->join('tb_mst_kontributor b', 'b.id = a.idMstKontributor')
         ->where('a.idRepository', $idRepository)
         ->get();
      
      return $query->result_array();
   }

   function setKontributor() {
      $this->db->trans_begin();

      $this->db->insert('tb_mst_kontributor', [
         'nama' => post('nama')
      ]);
      $idMstKontributor = $this->db->insert_id();

      $this->db->insert('tb_kontributor', [
         'idRepository' => post('idRepository'),
         'idMstKontributor' => $idMstKontributor
      ]);
      
      if ($this->db->trans_status()) {
         $this->db->trans_commit();
         return true;
      } else {
         $this->db->trans_rollback();
      }
   }

   function getPenulis() {
      $idRepository = post('idRepository');

      $query = $this->db
         ->select('a.id, b.nama')
         ->from('tb_penulis a')
         ->join('tb_mst_penulis b', 'b.id = a.idMstPenulis')
         ->where('a.idRepository', $idRepository)
         ->get();
      
      return $query->result_array();
   }

   function setPenulis() {
      $this->db->trans_begin();

      $this->db->insert('tb_mst_penulis', [
         'nama' => post('nama')
      ]);
      $idMstPenulis = $this->db->insert_id();

      $this->db->insert('tb_penulis', [
         'idRepository' => post('idRepository'),
         'idMstPenulis' => $idMstPenulis
      ]);
      
      if ($this->db->trans_status()) {
         $this->db->trans_commit();
         return true;
      } else {
         $this->db->trans_rollback();
      }
   }

   function getRepositoryKeywords() {
      $idRepository = post('idRepository');

      $query = $this->db
         ->select('a.id, b.label')
         ->from('tb_keywords a')
         ->join('tb_mst_keywords b', 'b.id = a.idMstKeywords')
         ->where('a.idRepository', $idRepository)
         ->get();
      
      return $query->result_array();
   }

   function updateKeywords() {
      $this->db->trans_begin();

      $label = post('label');

      $this->db->insert('tb_mst_keywords', [
         'label' => $label,
         'slug' => url_title($label, 'dash', true)
      ]);
      $idMstKeywords = $this->db->insert_id();

      $this->db->insert('tb_keywords', [
         'idRepository' => post('idRepository'),
         'idMstKeywords' => $idMstKeywords
      ]);
      
      if ($this->db->trans_status()) {
         $this->db->trans_commit();
      } else {
         $this->db->trans_rollback();
      }
   }

   function submit() {
      $this->db->trans_begin();

      $id = post('id');

      $this->db->where('id', $id);
      $this->db->update('tb_repository', [
         'judul' => post('judul'),
         'abstract' => post('abstract'),
         'tanggal_publish' => post('tanggal_publish'),
         'publish' => '1',
         'issn' => post('issn'),
         'isbn' => post('isbn'),
         'volume' => post('volume')
      ]);

      $this->db
         ->where('idRepository', $id)
         ->delete('tb_repository_categories');
      
      $categories = post('categories');
      $setCategories = [];
      foreach (explode(',', $categories) as $key) {
         if (!empty($key)) {
            array_push($setCategories, [
               'idRepository' => $id,
               'idCategories' => $key
            ]);  
         }
      }
      $this->db->insert_batch('tb_repository_categories', $setCategories);
      
      if ($this->db->trans_status()) {
         $this->db->trans_commit();
      } else {
         $this->db->trans_rollback();
      }
   }

   function getEditRepository($id) {
      $query = $this->db
         ->select('a.*, b.categories')
         ->from('tb_repository a')
         ->join('(select aa.idRepository, group_concat(aa.idCategories) as categories from tb_repository_categories aa where aa.idRepository = "'.$id.'") b', 'b.idRepository = a.id', 'left')
         ->where('a.id', $id)
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
         ->select('a.id, a.judul, a.penerbit, b.fullname as author, a.publish')
         ->from('tb_repository a')
         ->join('tb_users b', 'b.id = a.idUsers', 'left');
   
      $i = 0;
      $column_search = ['a.judul', 'a.penerbit', 'b.fullname'];
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