<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Search extends PublicPanel {

   protected $search = null;

   public function __construct() {
      parent::__construct();

      $this->search = urldecode($this->segment2);
   }

   public function index() {
      $footerJs = [
         'leftSidebar' => $this->leftSidebar(),
         'detail' => $this->_getContentLists($this->search)
      ];

      $this->data = [
         'title' => $this->search,
         'internalJs' => script_tag('assets/js/public/search.bundle.js'),
         'footerJs' => 'var content = '.json_encode($footerJs).';'
      ];

      $this->load->view('PublicPanel', $this->data);
   }

   private function _getContentLists($search) {
      $this->db->trans_begin();
      $this->benchmark->mark('code_start');

      $query = $this->db
         ->select('count(a.id) as jumlahRepository')
         ->from('tb_repository a')
         ->where('a.publish', '1')
         ->like('a.judul', $search)
         ->get();
      
      if ($query->num_rows() > 0) {
         $data = $query->row_array();

         $queryRepo = $this->db
            ->select('a.id, a.judul, a.abstract, b.file_ext, b.file_size')
            ->from('tb_repository a')
            ->join('(select aa.idRepository, aa.file_size, aa.file_ext from tb_files aa) b', 'b.idRepository = a.id', 'left')
            ->like('a.judul', $search)
            ->where('a.publish', '1')
            ->order_by('a.modified', 'desc')
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

         $response['search'] = $search;
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