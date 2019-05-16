<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Repository extends AdminPanel {

   protected $idUsers = null;
   protected $appName = null;

   public function __construct() {
      parent::__construct();

      $this->idUsers = usersLogin('id');
      $this->appName = settings('appName');
      $this->load->model('RepositoryModel', 'model', true);
   }

   public function index() {
      $this->data = [
         'title' => 'Repository',
         'internalCss' => css_tag($this->config->item('datatable')['css']),
         'internalJs' => script_tag([
            $this->config->item('datatable')['js'],
            'assets/js/admin/repo_lists.bundle.js'
         ])
      ];

      $this->session->unset_userdata('tempFile');
      $this->load->view('AdminPanel', $this->data);
   }

   public function tambah() {
      $this->db->insert('tb_repository', [
         'judul' => 'Judul belum di atur...',
         'setSpec' => 'com_' . time(),
         'idUsers' => $this->idUsers,
         'penerbit' => $this->appName,
         'uploaded' => date('Y-m-d H:i:s')
      ]);
      $id = $this->db->insert_id();
      redirect('adminpanel/repository/edit/' . $id);
   }

   public function edit() {
      $footerJs = [
         'detail' => $this->model->getEditRepository($this->segment4),
         'categories' => $this->model->getCategories()
      ];

      $this->data = [
         'title' => 'Edit Repository',
         'internalJs' => script_tag([
            'assets/js/admin/repo_forms.bundle.js'
         ]),
         'footerJs' => 'var content = ' .json_encode($footerJs). ';'
      ];

      $this->load->view('AdminPanel', $this->data);
   }

   public function submit() {
      $response = ['status' => false, 'errors' => [], 'message' => []];
      
      $validation = [
         ['field' => 'id', 'rules' => 'required|numeric'],
         ['field' => 'judul', 'rules' => 'required', 'errors' => [
            'required' => 'Can not be empty.'
         ]],
         ['field' => 'abstract', 'rules' => 'required|htmlentities', 'errors' => [
            'required' => 'Can not be empty.'
         ]],
         /* ['field' => 'penerbit', 'rules' => 'required', 'errors' => [
            'required' => 'Can not be empty.'
         ]], */
         ['field' => 'tanggal_publish', 'rules' => 'required', 'errors' => [
            'required' => 'Can not be empty.'
         ]],
         ['field' => 'categories', 'rules' => 'required', 'errors' => [
            'required' => 'Can not be empty.'
         ]],
         ['field' => 'volume', 'rules' => 'numeric', 'errors' => [
            'numeric' => 'Can only be filled with numbers.'
         ]]
      ];
      $this->form_validation->set_rules($validation);
      if ($this->form_validation->run()) {
         $this->model->submit();
         $response['status'] = true;
      } else {
         foreach ($_POST as $key => $val) {
            $response['errors'][$key] = form_error($key) ? true : false;
            $response['message'][$key] = strip_tags(form_error($key));
         }
      }
      $this->output($response);
   }

   public function uploadBerkas() {
      $response = ['status' => false, 'errors' => [], 'message' => []];      
      $validation = [
         ['field' => 'idRepository', 'rules' => 'required|numeric'],
         ['field' => 'fileDescription', 'rules' => 'required', 'errors' => [
            'required' => 'Can not be empty.'
         ]],
         ['field' => 'file', 'rules' => 'callback_checkFileUpload']
      ];
      $this->form_validation->set_rules($validation);
      if ($this->form_validation->run()) {
         $file = $this->uploadFile('file', [
            'upload_path' => 'assets/upload/',
            'allowed_types' => 'pdf|doc|docx|xls|xlsx'
         ]);

         $this->db->insert('tb_files', [
            'idRepository' => post('idRepository'),
            'description' => post('fileDescription'),
            'orig_name' => $file['orig_name'],
            'file_ext' => $file['file_ext'],
            'file_size' => $file['file_size'],
            'file_type' => $file['file_type']
         ]);
         $response['status'] = true;
      } else {
         foreach ($_POST as $key => $val) {
            $response['errors'][$key] = form_error($key) ? true : false;
            $response['message'][$key] = strip_tags(form_error($key));
         }
         foreach ($_FILES as $key => $val) {
            $response['errors'][$key] = form_error($key) ? true : false;
            $response['message'][$key] = strip_tags(form_error($key));
         }
      }
      $this->output($response);
   }

   public function checkFileUpload($str) {
      if ($str === 'undefined') {
         $this->form_validation->set_message('checkFileUpload', 'Can not be empty.');
         return false;
      } else {
         return true;
      }
   }

   public function updateKeywords() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => []];
      
         $validation = [
            ['field' => 'idRepository', 'rules' => 'required|numeric'],
            ['field' => 'label', 'rules' => 'required']
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            $this->model->updateKeywords();
            $response['status'] = true;
         }
         $this->output($response);
      } else {
         show_404();
      }
   }

   public function getRepositoryKeywords() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => []];
      
         $validation = [
            ['field' => 'idRepository', 'rules' => 'required|numeric']
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            $response['status'] = true;
            $response['content'] = $this->model->getRepositoryKeywords();
         }
         $this->output($response);
      } else {
         show_404();
      }
   }

   public function deleteKeywords() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => []];
      
         $validation = [
            ['field' => 'idRepository', 'rules' => 'required|numeric'],
            ['field' => 'id', 'rules' => 'required|numeric']
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            $idRepository = post('idRepository');
            $id = post('id');

            $this->db
               ->where('id', $id)
               ->where('idRepository', $idRepository)
               ->delete('tb_keywords');
            $response['status'] = true;
         }
         $this->output($response);
      } else {
         show_404();
      }
   }

   public function setPenulis() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => []];
      
         $validation = [
            ['field' => 'idRepository', 'rules' => 'required|numeric'],
            ['field' => 'nama', 'rules' => 'required']
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            $this->model->setPenulis();
            $response['status'] = true;
         }
         $this->output($response);
      } else {
         show_404();
      }
   }

   public function getPenulis() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => []];
      
         $validation = [
            ['field' => 'idRepository', 'rules' => 'required|numeric']
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            $response['status'] = true;
            $response['content'] = $this->model->getPenulis();
         }
         $this->output($response);
      } else {
         show_404();
      }
   }

   public function deletePenulis() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => []];
      
         $validation = [
            ['field' => 'idRepository', 'rules' => 'required|numeric'],
            ['field' => 'id', 'rules' => 'required|numeric']
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            $idRepository = post('idRepository');
            $id = post('id');

            $this->db
               ->where('id', $id)
               ->where('idRepository', $idRepository)
               ->delete('tb_penulis');            
            $response['status'] = true;
         }
         $this->output($response);
      } else {
         show_404();
      }
   }

   public function setKontributor() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => []];
      
         $validation = [
            ['field' => 'idRepository', 'rules' => 'required|numeric'],
            ['field' => 'nama', 'rules' => 'required']
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            $this->model->setKontributor();
            $response['status'] = true;
         }
         $this->output($response);
      } else {
         show_404();
      }
   }

   public function getKontributor() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => []];
      
         $validation = [
            ['field' => 'idRepository', 'rules' => 'required|numeric']
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            $response['status'] = true;
            $response['content'] = $this->model->getKontributor();
         }
         $this->output($response);
      } else {
         show_404();
      }
   }

   public function deleteKontributor() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => []];
      
         $validation = [
            ['field' => 'idRepository', 'rules' => 'required|numeric'],
            ['field' => 'id', 'rules' => 'required|numeric']
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            $idRepository = post('idRepository');
            $id = post('id');

            $this->db
               ->where('idRepository', $idRepository)
               ->where('id', $id)
               ->delete('tb_kontributor');
            $response['status'] = true;
         }
         $this->output($response);
      } else {
         show_404();
      }
   }

   public function getSuggestKeywords() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => []];
      
         $validation = [
            ['field' => 'suggest', 'rules' => 'required']
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            $response['status'] = true;
            $response['content'] = $this->model->getSuggestKeywords();
         }
         $this->output($response);
      } else {
         show_404();
      }
   }

   public function getSuggestPenulis() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => []];
      
         $validation = [
            ['field' => 'suggest', 'rules' => 'required']
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            $response['status'] = true;
            $response['content'] = $this->model->getSuggestPenulis();
         }
         $this->output($response);
      } else {
         show_404();
      }
   }
   
   public function getSuggestKontributor() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => []];
      
         $validation = [
            ['field' => 'suggest', 'rules' => 'required']
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            $response['status'] = true;
            $response['content'] = $this->model->getSuggestKontributor();
         }
         $this->output($response);
      } else {
         show_404();
      }
   }

   public function handleSuggestKeywords() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => []];
      
         $validation = [
            ['field' => 'idMstKeywords', 'rules' => 'required|numeric'],
            ['field' => 'idRepository', 'rules' => 'required|numeric']
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            $this->model->handleSuggestKeywords();
            $response['status'] = true;
         }
         $this->output($response);
      } else {
         show_404();
      }
   }

   public function handleSuggestKontributor() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => []];
      
         $validation = [
            ['field' => 'idMstKontributor', 'rules' => 'required|numeric'],
            ['field' => 'idRepository', 'rules' => 'required|numeric']
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            $this->model->handleSuggestKontributor();
            $response['status'] = true;
         }
         $this->output($response);
      } else {
         show_404();
      }
   }

   public function handleSuggestPenulis() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => []];
      
         $validation = [
            ['field' => 'idMstPenulis', 'rules' => 'required|numeric'],
            ['field' => 'idRepository', 'rules' => 'required|numeric']
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            $this->model->handleSuggestPenulis();
            $response['status'] = true;
         }
         $this->output($response);
      } else {
         show_404();
      }
   }

   public function deleteRepository() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => []];
      
         $validation = [
            ['field' => 'id', 'rules' => 'required|numeric']
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            $this->db->trans_begin();
            
            $id = post('id');
            $this->db
               ->where('id', $id)
               ->delete('tb_repository');

            $queryFile = $this->db
               ->select('orig_name')
               ->from('tb_files')
               ->where('idRepository', $id)
               ->get();
            
            if ($queryFile->num_rows() > 0) {
               foreach ($queryFile->result_array() as $data) {
                  @unlink(FCPATH . 'assets/upload/' . $data['orig_name']);
               }
               $this->db
                  ->where('idRepository', $id)
                  ->delete('tb_files');
            }

            if ($this->db->trans_status()) {
               $this->db->trans_commit();
               $response['status'] = true;
            } else {
               $this->db->trans_rollback();
            }
         }
         $this->output($response);
      } else {
         show_404();
      }
   }

   public function getData() {
      if ($this->input->is_ajax_request()) {
         $query = $this->model->getData();
   
         $i = $_POST['start'];
         $response = array();
         foreach ($query->result() as $data) {
            $i++;
   
            $action = '<div class="btn-group btn-group-xs">';
            $action .= '<a href="'.site_url('adminpanel/repository/edit/' . $data->id).'" class="btn waves-effect waves-light btn-outline-warning btn-sm"><i class="fas fa-edit"></i></a>';
            $action .= '<a data-id="'.$data->id.'" id="delete" class="btn waves-effect waves-light btn-outline-danger btn-sm"><i class="fas fa-trash-alt"></i></a>';
            $action .= '<a href="'.site_url('detail/' . $data->id).'" class="btn waves-effect waves-light btn-outline-info btn-sm" target="_blank"><i class="fas fa-external-link-alt"></i></a>';
            $action .= '</div>';
   
            $result = array();
            $result[] = $i;
            $result[] = $data->judul;
            $result[] = $data->penerbit;
            $result[] = $data->author;
            $result[] = ($data->publish === '1' ? '<i class="mdi mdi-check-all"></i>' : '<i class="mdi mdi-close"></i>');
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

   public function getFiles() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => []];
         $validation = [
            ['field' => 'idRepository', 'rules' => 'required|numeric']
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            $response['status'] = true;
            $response['content'] = $this->model->getFiles(post('idRepository'));
         }
         $this->output($response);
      } else {
         show_404();
      }
   }

   public function deleteFiles() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => 'Something when wrong.'];
      
         $validation = [
            ['field' => 'id', 'rules' => 'required|numeric'],
            ['field' => 'file_name', 'rules' => 'required']
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            $id = post('id');
            $file_name = post('file_name');

            @unlink(FCPATH . 'assets/upload/' . $file_name);

            $this->db
               ->where('id', $id)
               ->delete('tb_files');
            $response['status'] = true;
         }
         $this->output($response);
      } else {
         show_404();
      }
   }

   public function insertPrefences() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => []];
      
         $validation = [
            ['field' => 'idRepository', 'rules' => 'required|numeric'],
            ['field' => 'name', 'rules' => 'required']
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            $this->model->insertPrefences(post('idRepository'));
            $response['status'] = true;
         }
         $this->output($response);
      } else {
         show_404();
      }
   }

   public function getPrefences() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => []];
      
         $validation = [
            ['field' => 'idRepository', 'rules' => 'required|numeric']
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            $response['status'] = true;
            $response['content'] = $this->model->getPrefences(post('idRepository'));
         }
         $this->output($response);
      } else {
         show_404();
      }
   }

   public function deletePrefences() {
      if ($this->input->is_ajax_request()) {
         $response = ['status' => false, 'errors' => [], 'message' => []];
      
         $validation = [
            ['field' => 'id', 'rules' => 'required|numeric']
         ];
         $this->form_validation->set_rules($validation);
         if ($this->form_validation->run()) {
            $id = post('id');

            $this->db
               ->where('id', $id)
               ->delete('tb_prefences');
            $response['status'] = true;
         }
         $this->output($response);
      } else {
         show_404();
      }
   }

}