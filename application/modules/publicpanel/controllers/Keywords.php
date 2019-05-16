<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Keywords extends PublicPanel {

   public function __construct() {
      parent::__construct();
   }

   public function index() {
      $footerJs = [
         'leftSidebar' => $this->leftSidebar(),
         'detail' => $this->_getContentLists($this->segment2)
      ];

      $this->data = [
         'title' => $footerJs['detail']['keywords'],
         'internalJs' => script_tag('assets/js/public/keywords.bundle.js'),
         'footerJs' => 'var content = '.json_encode($footerJs).';'
      ];

      $this->load->view('PublicPanel', $this->data);
   }

   private function _getContentLists($slug) {
      $this->db->trans_begin();
      $this->benchmark->mark('code_start');

      $query = $this->db
         ->select('a.label, count(b.idMstKeywords) as jumlahRepository')
         ->from('tb_mst_keywords a')
         ->join('tb_keywords b', 'b.idMstKeywords = a.id', 'left')
         ->where('a.slug', $slug)
         ->get();
      
      if ($query->num_rows() > 0) {
         $data = $query->row_array();

         $queryRepo = $this->db
            ->select('c.id, c.judul, d.file_size, d.file_ext, c.abstract')
            ->from('tb_mst_keywords a')
            ->join('tb_keywords b', 'b.idMstKeywords = a.id', 'left')
            ->join('tb_repository c', 'c.id = b.idRepository and c.publish = "1"', 'left')
            ->join('(select aa.idRepository, aa.file_ext, aa.file_size from tb_files aa limit 1) d', 'd.idRepository = c.id', 'left')
            ->where('a.slug', $slug)
            ->order_by('c.modified', 'desc')
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
      
         $response['keywords'] = $data['label'];
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