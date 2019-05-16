<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Detail extends PublicPanel {

   public function __construct() {
      parent::__construct();
   }

   public function index() {
      $footerJs = [
         'leftSidebar' => $this->leftSidebar(),
         'detail' => $this->_getDetailContent($this->segment2)
      ];

      $headerMeta = $this->setHeaderMeta([
         'title' => $footerJs['detail']['judul'],
         'author' => $footerJs['detail']['penulis'],
         'publication_date' => $footerJs['detail']['tanggal_publish'],
         'pdf_url' => $footerJs['detail']['orig_name'],
         'issn' => $footerJs['detail']['issn'],
         'isbn' => $footerJs['detail']['isbn'],
         'volume' => $footerJs['detail']['volume'],
         'files' => $footerJs['detail']['files']
      ]);

      $this->data = [
         'title' => $footerJs['detail']['judul'],
         'headerMeta' => $headerMeta,
         'internalJs' => script_tag('assets/js/public/detail.bundle.js'),
         'footerJs' => 'var content = '.json_encode($footerJs).';'
      ];

      $this->load->view('PublicPanel', $this->data);
   }

   private function _getDetailContent($id) {
      $this->db->trans_begin();

      $query = $this->db
         ->select('a.id, a.judul, a.tanggal_publish, a.penerbit, a.abstract, b.penulis, c.kontributor, d.kataKunci, e.kategori')
         ->from('tb_repository a')
         ->join('(select aa.idRepository, group_concat(bb.id, ":", bb.nama) as penulis from tb_penulis aa join tb_mst_penulis bb on bb.id = aa.idMstPenulis) b', 'b.idRepository = a.id', 'left')
         ->join('(select aa.idRepository, group_concat(bb.id, ":", bb.nama) as kontributor from tb_kontributor aa join tb_mst_kontributor bb on bb.id = aa.idMstKontributor) c', 'c.idRepository = a.id', 'left')
         ->join('(select aa.idRepository, group_concat(bb.slug, ":", bb.label) as kataKunci from tb_keywords aa join tb_mst_keywords bb on bb.id = aa.idMstKeywords) d', 'd.idRepository = a.id', 'left')
         ->join('(select bb.idRepository, group_concat(aa.slug, ":", aa.nama) as kategori from tb_categories aa join tb_repository_categories bb on bb.idCategories = aa.id where bb.idRepository = "'.$id.'") e', 'e.idRepository = a.id', 'left')
         ->where('a.id', $id)
         ->where('a.publish', '1')
         ->get();
      
      if ($query->num_rows() > 0) {
         $list_fields = $query->list_fields();
         $data = $query->row_array();

         $queryPrefences = $this->db
            ->select('name')
            ->from('tb_prefences')
            ->where('idRepository', $id)
            ->get();
         
         $prefences = [];
         foreach ($queryPrefences->result_array() as $row) {
            $prefences[] = $row['name'];
         }

         $queryFiles = $this->db
            ->select('description, orig_name, file_size, file_type')
            ->from('tb_files')
            ->where('idRepository', $id)
            ->get();
      
         $response = [];
         foreach ($list_fields as $fields) {
            if ($fields === 'abstract') {
               $response['abstract'] = html_entity_decode($data['abstract']);
            } else {
               $response[$fields] = (string) $data[$fields];
               $response['prefences'] = $prefences;
               $response['files'] = $queryFiles->result_array();
            }
            $response['uri'] = $this->root . site_url('detail/' . $id);
         }
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