<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kategori extends AdminPanel {

   public function __construct() {
      parent::__construct();

      $this->load->model('master_data/KategoriModel', 'model', true);
   }

   public function index() {
      $this->data = [
         'title' => 'Kategori',
         'pageType' => 'insert',
         'internalCss' => css_tag($this->config->item('datatable')['css']),
         'internalJs' => script_tag([
            $this->config->item('datatable')['js'],
            'assets/js/admin/kategori.bundle.js'
         ])
      ];

      $this->load->view('AdminPanel', $this->data);
   }

   public function edit() {
      $footerJs = [
         'edit' => $this->model->getEditKategori($this->segment5)
      ];

      $this->data = [
         'title' => 'Kategori',
         'pageType' => 'update',
         'internalCss' => css_tag($this->config->item('datatable')['css']),
         'internalJs' => script_tag([
            $this->config->item('datatable')['js'],
            'assets/js/admin/kategori.bundle.js'
         ]),
         'footerJs' => 'var content = ' .json_encode($footerJs). ';'
      ];

      $this->load->view('AdminPanel', $this->data);
   }

   public function submit() {
      $response = ['status' => false, 'errors' => [], 'message' => []];
      $pageType = post('pageType');
      
      $validation = [
         ['field' => 'pageType', 'rules' => 'required'],
         ['field' => 'id', 'rules' => ($pageType === 'update' ? 'required|numeric' : '')],
         ['field' => 'nama', 'rules' => 'required', 'errors' => [
            'required' => 'Tidak boleh kosong.'
         ]]
      ];
      $this->form_validation->set_rules($validation);
      if ($this->form_validation->run()) {
         $this->model->submit($pageType);
         $response['status'] = true;
      } else {
         foreach ($_POST as $key => $val) {
            $response['errors'][$key] = form_error($key) ? true : false;
            $response['message'][$key] = strip_tags(form_error($key));
         }
      }
      $this->output($response);
   }

   public function delete() {
      $response = ['status' => false];
      
      $validation = [
         ['field' => 'id', 'rules' => 'required|numeric']
      ];
      $this->form_validation->set_rules($validation);
      if ($this->form_validation->run()) {
         $id = post('id');

         $this->db
            ->where('id', $id)
            ->delete('tb_categories');
         $response['status'] = true;
      }
      $this->output($response);
   }

   public function getData() {
      if ($this->input->is_ajax_request()) {
         $query = $this->model->getData();
   
         $i = $_POST['start'];
         $response = array();
         foreach ($query->result() as $data) {
            $i++;
   
            $action = '<div class="btn-group btn-group-xs">';
            $action .= '<a href="'.site_url('adminpanel/master-data/kategori/edit/' . $data->id).'" class="btn waves-effect waves-light btn-outline-warning btn-sm"><i class="fas fa-edit"></i></a>';
            $action .= '<a data-id="'.$data->id.'" id="delete" class="btn waves-effect waves-light btn-outline-danger btn-sm"><i class="fas fa-trash-alt"></i></a>';
            $action .= '</div>';
   
            $result = array();
            $result[] = $i;
            $result[] = $data->nama;
            $result[] = $action;
   
            $response[] = $result;
         }
   
         $output = array(
            'draw' => intval($_POST['draw']),
            'recordsTotal' => intval($this->model->countData()),
            'recordsFiltered' => intval($this->model->filteredData()),
            'data' => $response
         );
         $this->output($output);
      } else {
         show_404();
      }
   }

}