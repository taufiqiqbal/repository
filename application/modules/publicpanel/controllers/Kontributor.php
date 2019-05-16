<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kontributor extends PublicPanel {

   public function __construct() {
      parent::__construct();
   }

   public function index() {
      $footerJs = [
         'leftSidebar' => $this->leftSidebar(),
         'detail' => $this->_getContentLists($this->segment2)
      ];

      $this->data = [
         'title' => $footerJs['detail']['kontributor'],
         'internalJs' => script_tag('assets/js/public/kontributor.bundle.js'),
         'footerJs' => 'var content = '.json_encode($footerJs).';'
      ];

      $this->load->view('PublicPanel', $this->data);
   }

   private function _getContentLists($id) {
      $this->db->trans_begin();
      $this->benchmark->mark('code_start');

      $query = $this->db
         ->select('a.nama, count(b.idMstKontributor) as jumlahRepository')
         ->from('tb_mst_kontributor a')
         ->join('tb_kontributor b', 'b.idMstKontributor = a.id', 'left')
         ->where('a.id', $id)
         ->get();
      
      if ($query->num_rows() > 0) {
         $data = $query->row_array();

         $queryRepo = $this->db
            ->select('b.id, b.judul, c.file_size, c.file_ext, b.abstract')
            ->from('tb_kontributor a')
            ->join('tb_repository b', 'b.id = a.idRepository and b.publish = "1"', 'left')
            ->join('(select aa.idRepository, aa.file_ext, aa.file_size from tb_files aa limit 1) c', 'c.idRepository = b.id', 'left')
            ->where('a.idMstKontributor', $id)
            ->order_by('b.modified', 'desc')
            ->get();

         $this->benchmark->mark('code_end');

         $repoLists = [];
         foreach ($queryRepo->result_array() as $row) {
            array_push($repoLists, [
               'judul' => $row['judul'],
               'fileSize' => $row['file_size'],
               'fileExt' => $row['file_ext'],
               'abstract' => $this->setAbstract($row['abstract']),
               'url' => $this->root . site_url('detail/' . $row['id'])
            ]);
         }

         $response['kontributor'] = $data['nama'];
         $response['benchmark'] = $this->benchmark->elapsed_time('code_start', 'code_end');
         $response['jumlahRepository'] = $data['jumlahRepository'];
         $response['repoLists'] = $repoLists;
      } else {
         show_404();
      }
      
      if ($this->db->trans_status()) {
         $this->db->trans_commit();
         return $response;
      } else {
         $this->db->trans_rollback();
      }
   }

}