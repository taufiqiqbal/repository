<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PublicPanel extends CI_Controller {

   protected $segment2 = null;
   var $root = null;

   public function __construct() {
      parent::__construct();

      $this->output->set_header('cache-control: public, must-revalidate, max-age=3600');
      $this->output->set_header('x-content-type-options: nosniff');
      $this->output->set_header('x-frame-options: SAMEORIGIN');
      $this->output->set_header('x-xss-protection: 1; mode=block');
      header_remove("X-Powered-By");

      $this->segment2 = $this->uri->segment(2);
      $this->root = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'];
      $this->load->helper('text');
   }

   public function leftSidebar() {
      $query = $this->db
         ->select('a.nama, a.slug, count(c.id) as jumlahRepository')
         ->from('tb_categories a')
         ->join('tb_repository_categories b', 'b.idCategories = a.id', 'left')
         ->join('tb_repository c', 'c.id = b.idRepository and c.publish = "1"', 'left')
         ->group_by('a.id')
         ->get();
      
      return $query->result_array();
   }

   public function setAbstract($content) {
      $dom = new DOMDocument;
      $dom->loadHTML(html_entity_decode($content));

      return word_limiter(strip_tags($dom->textContent), 80);
   }

   public function setHeaderMeta($params = []) {
      $string = '<meta name="citation_title" content="'.$params['title'].'">';
      if (!empty($params['author'])) {
         foreach (explode(',', $params['author']) as $data) {
            $string .= '<meta name="citation_author" content="'.ucwords(explode(':', $data)[1]).'">';
         }
      }
      $string .= '<meta name="citation_publication_date" content="'.date('Y/m/d', strtotime($params['publication_date'])).'">';
      $string .= '<meta name="citation_journal_title" content="'.settings('appName').'">';
      if (!empty($params['issn']))
         $string .= '<meta name="citation_issn" content="'.$params['issn'].'">';
      if (!empty($params['isbn']))
         $string .= '<meta name="citation_isbn" content="'.$params['isbn'].'">';
      if (!empty($params['volume']))
         $string .= '<meta name="citation_volume" content="'.$params['volume'].'">';
      if (!empty($params['files'])) {
         foreach ($params['files'] as $data) {
            $string .= '<meta name="citation_pdf_url" content="'.$this->root.base_url('assets/upload/'.$data['orig_name']).'">';
         }
      }
      return $string;
   }

}