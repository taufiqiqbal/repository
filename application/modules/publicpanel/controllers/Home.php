<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends PublicPanel {

   public function __construct() {
      parent::__construct();
   }

   public function index() {
      $footerJs = [
         'leftSidebar' => $this->leftSidebar(),
         'repoLists' => $this->_getRepoLists()
      ];

      $this->data = [
         'title' => settings('appName'),
         'internalJs' => script_tag('assets/js/public/home.bundle.js'),
         'footerJs' => 'var content = '.json_encode($footerJs).';'
      ];

      $this->load->view('PublicPanel', $this->data);
   }

   private function _getRepoLists() {
      $query = $this->db
         ->select('a.id, a.judul, a.abstract, b.file_size, b.file_ext')
         ->from('tb_repository a')
         ->join('tb_files b', 'b.idRepository = a.id', 'left')
         ->where('a.publish', '1')
         ->group_by('a.id')
         ->order_by('modified', 'desc')
         ->get();
      
      $response = [];
      foreach ($query->result_array() as $data) {
         array_push($response, [
            'judul' => $data['judul'],
            'fileSize' => $data['file_size'],
            'fileExt' => $data['file_ext'],
            'abstract' => $this->setAbstract($data['abstract']),
            'url' => $this->root . site_url('detail/' . $data['id'])
         ]);
      }
      return $response;
   }

}