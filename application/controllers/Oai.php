<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Oai extends CI_Controller {

   var $root = '';

   public function __construct() {
      parent::__construct();

      $this->root = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'];
   }

   public function index() {
      $string = '<?xml version="1.0" encoding="UTF-8"?>';
      $string .= '<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">';
      $string .= '<responseDate>'.date('Y-m-d').'T'.date('h:i:s').'Z</responseDate>';
      $string .= '<request verb="Identify">' . $this->root . site_url('oai') . '</request>';
      $string .= '<Identify>';
      $string .= '<repositoryName>'.settings('appName').'</repositoryName>';
      $string .= '<baseURL>' . $this->root . site_url('oai') . '</baseURL>';
      $string .= '<protocolVersion>2.0</protocolVersion>';
      $string .= '<adminEmail>'.settings('adminEmail').'</adminEmail>';
      $string .= '<earliestDatestamp>'.date('Y-m-d', strtotime(settings('modified'))).'T'.date('h:i:s', strtotime(settings('modified'))).'Z</earliestDatestamp>';
      $string .= '<deletedRecord>transient</deletedRecord>';
      $string .= '<granularity>YYYY-MM-DDThh:mm:ssZ</granularity>';
      $string .= '<description>';
      $string .= '<oai-identifier xmlns="http://www.openarchives.org/OAI/2.0/oai-identifier" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai-identifier http://www.openarchives.org/OAI/2.0/oai-identifier.xsd">';
      $string .= '<scheme>oai</scheme>';
      $string .= '<repositoryIdentifier>' . site_url() . '</repositoryIdentifier>';
      $string .= '<delimiter>:</delimiter>';
      $string .= '<sampleIdentifier>oai:'.site_url().'</sampleIdentifier>';
      $string .= '</oai-identifier>';
      $string .= '</description>';
      $string .= '</Identify>';
      $string .= '</OAI-PMH>';

      $this->output->set_content_type('text/xml')->set_output($string);
   }

   public function sets() {
      $query = $this->db
         ->select('judul, setSpec')
         ->from('tb_repository')
         ->order_by('modified', 'desc')
         ->where('publish', '1')
         ->get();

      $string = '<?xml version="1.0" encoding="UTF-8"?>';
      $string .= '<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">';
      $string .= '<responseDate>'.date('Y-m-d').'T'.date('h:i:s').'Z</responseDate>';
      $string .= '<request verb="ListSets">'.$this->root . site_url('oai/sets').'</request>';
      $string .= '<ListSets>';
      foreach ($query->result_array() as $data) {
         $string .= '<set>';
         $string .= '<setSpec>'.$data['setSpec'].'</setSpec>';
         $string .= '<setName>'.$data['judul'].'</setName>';
         $string .= '</set>';
      }
      $string .= '</ListSets>';
      $string .= '</OAI-PMH>';

      $this->output->set_content_type('text/xml')->set_output($string);
   }

   public function records() {
      $query = $this->db
         ->select('*')
         ->from('tb_repository')
         ->where('publish', '1')
         ->order_by('modified', 'desc')
         ->get();

      $string = '<?xml version="1.0" encoding="UTF-8"?>';
      $string .= '<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">';
      $string .= '<responseDate>'.date('Y-m-d').'T'.date('h:i:s').'Z</responseDate>';
      $string .= '<request verb="ListRecords" metadataPrefix="oai_dc">'.$this->root . site_url('oai/records').'</request>';
      $string .= '<ListRecords>';
      foreach ($query->result_array() as $data) {
         $string .= '<record>';
         $string .= '<header>';
         $string .= '<identifier>oai:'.str_replace('/', ':', site_url().':detail:'.$data['id']).'</identifier>';
         $string .= '<datestamp>'.date('Y-m-d', strtotime($data['modified'])).'T'.date('h:i:s', strtotime($data['modified'])).'Z</datestamp>';
         $string .= '<setSpec>'.$data['setSpec'].'</setSpec>';
         $string .= '</header>';
         $string .= '<metadata>';
         $string .= '<oai_dc:dc xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" xmlns:doc="http://www.lyncode.com/xoai" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:dc="http://purl.org/dc/elements/1.1/" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd">';
         $string .= '<dc:title>'.$data['judul'].'</dc:title>';
         $string .= $this->_setPenulis($data['id']);
         $string .= $this->_setKontributor($data['id']);
         $string .= $this->_setKeywords($data['id']);
         $string .= $this->_setAbstract($data['abstract']);
         $string .= '<dc:date>'.date('Y-m-d', strtotime($data['uploaded'])).'T'.date('h:i:s', strtotime($data['uploaded'])).'Z</dc:date>';
         $string .= '<dc:date>'.date('Y', strtotime($data['tanggal_publish'])).'</dc:date>';
         $string .= $this->_setCategories($data['id']);
         $string .= '<dc:identifier>'.$data['id'].'</dc:identifier>';
         $string .= '<dc:identifier>'.$this->root.site_url('detail/'.$data['id']).'</dc:identifier>';
         $string .= '<dc:language>id</dc:language>';
         $string .= $this->_setFiles($data['id']);
         $string .= '<dc:publisher>'.$data['penerbit'].'</dc:publisher>';
         $string .= '</oai_dc:dc>';
         $string .= '</metadata>';
         $string .= '</record>';
      }
      $string .= '</ListRecords>';
      $string .= '</OAI-PMH>';

      $this->output->set_content_type('text/xml')->set_output($string);
   }

   public function identifier() {
      $query = $this->db
         ->select('*')
         ->from('tb_repository')
         ->where('publish', '1')
         ->order_by('modified', 'desc')
         ->get();

      $string .= '<?xml version="1.0" encoding="UTF-8"?>';
      $string .= '<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">';
      $string .= '<responseDate>'.date('Y-m-d').'T'.date('h:i:s').'Z</responseDate>';
      $string .= '<request verb="ListIdentifiers" metadataPrefix="oai_dc">'.$this->root.site_url('oai/identifier').'</request>';
      $string .= '<ListIdentifiers>';
      foreach ($query->result_array() as $data) {
         $string .= '<header>';
         $string .= '<identifier>oai:'.str_replace('/', ':', site_url().':detail:'.$data['id']).'</identifier>';
         $string .= '<datestamp>'.date('Y-m-d', strtotime($data['modified'])).'T'.date('h:i:s', strtotime($data['modified'])).'Z</datestamp>';
         $string .= '<setSpec>'.$data['setSpec'].'</setSpec>';
         $string .= '</header>';
      }
      $string .= '</ListIdentifiers>';
      $string .= '</OAI-PMH>';

      $this->output->set_content_type('text/xml')->set_output($string);
   }

   public function metadata() {
      $string = '<?xml version="1.0" encoding="UTF-8"?>';
      $string .= '<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">';
      $string .= '<responseDate>'.date('Y-m-d').'T'.date('h:i:s').'Z</responseDate>';
      $string .= '<request verb="ListMetadataFormats">'.$this->root.site_url('oai/metadata').'</request>';
      $string .= '<ListMetadataFormats>';
      $string .= '<metadataFormat>';
      $string .= '<metadataPrefix>uketd_dc</metadataPrefix>';
      $string .= '<schema>http://naca.central.cranfield.ac.uk/ethos-oai/2.0/uketd_dc.xsd</schema>';
      $string .= '<metadataNamespace>http://naca.central.cranfield.ac.uk/ethos-oai/2.0/</metadataNamespace>';
      $string .= '</metadataFormat>';
      $string .= '<metadataFormat>';
      $string .= '<metadataPrefix>dim</metadataPrefix>';
      $string .= '<schema>http://www.dspace.org/schema/dim.xsd</schema>';
      $string .= '<metadataNamespace>http://www.dspace.org/xmlns/dspace/dim</metadataNamespace>';
      $string .= '</metadataFormat>';
      $string .= '<metadataFormat>';
      $string .= '<metadataPrefix>oai_dc</metadataPrefix>';
      $string .= '<schema>http://www.openarchives.org/OAI/2.0/oai_dc.xsd</schema>';
      $string .= '<metadataNamespace>http://www.openarchives.org/OAI/2.0/oai_dc/</metadataNamespace>';
      $string .= '</metadataFormat>';
      $string .= '<metadataFormat>';
      $string .= '<metadataPrefix>marc</metadataPrefix>';
      $string .= '<schema>http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd</schema>';
      $string .= '<metadataNamespace>http://www.loc.gov/MARC21/slim</metadataNamespace>';
      $string .= '</metadataFormat>';
      $string .= '<metadataFormat>';
      $string .= '<metadataPrefix>etdms</metadataPrefix>';
      $string .= '<schema>http://www.ndltd.org/standards/metadata/etdms/1.0/etdms.xsd</schema>';
      $string .= '<metadataNamespace>http://www.ndltd.org/standards/metadata/etdms/1.0/</metadataNamespace>';
      $string .= '</metadataFormat>';
      $string .= '<metadataFormat>';
      $string .= '<metadataPrefix>xoai</metadataPrefix>';
      $string .= '<schema>http://www.lyncode.com/schemas/xoai.xsd</schema>';
      $string .= '<metadataNamespace>http://www.lyncode.com/xoai</metadataNamespace>';
      $string .= '</metadataFormat>';
      $string .= '<metadataFormat>';
      $string .= '<metadataPrefix>qdc</metadataPrefix>';
      $string .= '<schema>http://dublincore.org/schemas/xmls/qdc/2006/01/06/dcterms.xsd</schema>';
      $string .= '<metadataNamespace>http://purl.org/dc/terms/</metadataNamespace>';
      $string .= '</metadataFormat>';
      $string .= '<metadataFormat>';
      $string .= '<metadataPrefix>rdf</metadataPrefix>';
      $string .= '<schema>http://www.openarchives.org/OAI/2.0/rdf.xsd</schema>';
      $string .= '<metadataNamespace>http://www.openarchives.org/OAI/2.0/rdf/</metadataNamespace>';
      $string .= '</metadataFormat>';
      $string .= '<metadataFormat>';
      $string .= '<metadataPrefix>ore</metadataPrefix>';
      $string .= '<schema>http://tweety.lanl.gov/public/schemas/2008-06/atom-tron.sch</schema>';
      $string .= '<metadataNamespace>http://www.w3.org/2005/Atom</metadataNamespace>';
      $string .= '</metadataFormat>';
      $string .= '<metadataFormat>';
      $string .= '<metadataPrefix>mods</metadataPrefix>';
      $string .= '<schema>http://www.loc.gov/standards/mods/v3/mods-3-1.xsd</schema>';
      $string .= '<metadataNamespace>http://www.loc.gov/mods/v3</metadataNamespace>';
      $string .= '</metadataFormat>';
      $string .= '<metadataFormat>';
      $string .= '<metadataPrefix>mets</metadataPrefix>';
      $string .= '<schema>http://www.loc.gov/standards/mets/mets.xsd</schema>';
      $string .= '<metadataNamespace>http://www.loc.gov/METS/</metadataNamespace>';
      $string .= '</metadataFormat>';
      $string .= '<metadataFormat>';
      $string .= '<metadataPrefix>didl</metadataPrefix>';
      $string .= '<schema>http://standards.iso.org/ittf/PubliclyAvailableStandards/MPEG-21_schema_files/did/didl.xsd</schema>';
      $string .= '<metadataNamespace>urn:mpeg:mpeg21:2002:02-DIDL-NS</metadataNamespace>';
      $string .= '</metadataFormat>';
      $string .= '</ListMetadataFormats>';
      $string .= '</OAI-PMH>';

      $this->output->set_content_type('text/xml')->set_output($string);
   }

   private function _setFiles($id) {
      $query = $this->db
         ->select('file_type')
         ->from('tb_files')
         ->where('idRepository', $id)
         ->get();
      
      $string = '';
      if ($query->num_rows() > 0) {
         foreach ($query->result_array() as $data) {
            $string .= '<dc:format>'.$data['file_type'].'</dc:format>';
         }
      }
      return $string;
   }

   private function _setCategories($id) {
      $query = $this->db
         ->select('b.nama')
         ->from('tb_repository_categories a')
         ->join('tb_categories b', 'b.id = a.idCategories')
         ->where('a.idRepository', $id)
         ->get();
      
      if ($query->num_rows() > 0) {
         foreach ($query->result_array() as $data) {
            $string .= '<dc:type>'.ucwords($data['nama']).'</dc:type>';
         }
      } else {
         $string = '';
      }
      return $string;
   }

   private function _setKontributor($id) {
      $query = $this->db
         ->select('b.nama')
         ->from('tb_kontributor a')
         ->join('tb_mst_kontributor b', 'b.id = a.idMstKontributor')
         ->where('a.idRepository', $id)
         ->get();
      
      if ($query->num_rows() > 0) {
         foreach ($query->result_array() as $data) {
            $string .= '<dc:contributor>'.ucwords($data['nama']).'</dc:contributor>';
         }
      } else {
         $string = '';
      }
      return $string;
   }

   private function _setPenulis($id) {
      $query = $this->db
         ->select('b.nama')
         ->from('tb_penulis a')
         ->join('tb_mst_penulis b', 'b.id = a.idMstPenulis')
         ->where('a.idRepository', $id)
         ->get();
      
      if ($query->num_rows() > 0) {
         foreach ($query->result_array() as $data) {
            $string .= '<dc:creator>'.ucwords($data['nama']).'</dc:creator>';
         }
      } else {
         $string = '';
      }
      return $string;
   }

   private function _setKeywords($id) {
      $query = $this->db
         ->select('b.label')
         ->from('tb_keywords a')
         ->join('tb_mst_keywords b', 'b.id = a.idMstKeywords')
         ->where('a.idRepository', $id)
         ->get();
      
      if ($query->num_rows() > 0) {
         foreach ($query->result_array() as $data) {
            $string .= '<dc:subject>'.ucwords($data['label']).'</dc:subject>';
         }
      } else {
         $string = '';
      }
      return $string;
   }

   private function _setAbstract($content) {
      $dom = new DOMDocument;
      $dom->loadHTML(html_entity_decode($content));
      $parse = $dom->getElementsByTagName('p');

      $string = '';
      foreach ($parse as $data) {
         $string .= '<dc:description>'.strip_tags($data->nodeValue).'</dc:description>';
      }
      return $string;
   }

}