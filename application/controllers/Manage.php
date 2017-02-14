<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Manage extends Cpanel_Controller
{
	protected static $upload_config 	= array();
	const MAX_IMAGES_ALLOWED 			= 5;
	public function __construct() {	
		parent::__construct();
		$this->load->library('form_validation');
		$this->load->library("pagination");
		$this->load->helper('url');
		$this->load->model('manage_model');
		$this->data['message'] 			= $this->session->flashdata('message');
		$this->data['message_type'] 	= $this->session->flashdata('message_type');
	}
	
	public function index($page = NULL) {
		if ($page) {
			$this->render($page);
		} else  {
			$this->render('dashboard');
		}
		}
		
	#manage menu attributes here 
	function menu_attribute() {
		$allAttributes		= _DB_data($this->tables['attributes'],null, null, null, array('entity_id', 'desc')); 
		$this->add_data(compact('allAttributes'));
		$this->render('manage-menu-attributes');
	}

	#load add page 
	function add_attribute() {
			$attribute_name		= '';
			$attribute_status	= '';
		$this->add_data(compact('attribute_name', 'attribute_status'));
		$this->render('add-menu-attributes');
	}
	
	#load edit attributes
	function edit_attribute($attId	= NULL) {
		if ($attId) {
			$get_data					= _DB_get_record($this->tables['attributes'], array('entity_id' => $attId));
			$attribute_name				= '';
			$attribute_status			= '';
			$this->add_data(compact('get_data', 'attribute_name', 'attribute_status'));
			$this->render('add-menu-attributes');
			
		} else {
			$this->session->set_flashdata('message', "Oops! something went wrong. Try again later.");
			$this->session->set_flashdata('message_type', 'danger');
			redirect('manage/menu_attribute', 'refresh');
		}
	}
	
	#add or edit menu attributes
	function submit_attribute() {
		$editId					= $this->input->post('edit_id', true);
		if (!$editId) {
		$this->form_validation->set_rules('attribute_name','Attribute Name','trim|required|is_unique[attributes.attribute_name]');
		} else {
			$this->form_validation->set_rules('attribute_name','Attribute Name','trim|required');
		}
		$this->form_validation->set_rules('attribute_status','Attribute Status','trim|required');
		$attribute_name			= $this->input->post('attribute_name', true);
		$attribute_status		= $this->input->post('attribute_status', true);
		if ($this->form_validation->run() == true) {
		 	
			if ($editId) {
			$update				= _DB_update($this->tables['attributes'], array('attribute_name' => $attribute_name, 'status' => $attribute_status), array('entity_id' => $editId));
				if ($update) {
				 $this->session->set_flashdata('message', "Attribute '<strong>$attribute_name</strong>' has been updated successfully.");
				$this->session->set_flashdata('message_type', 'success');
				redirect('manage/menu_attribute', 'refresh');
			} else {
				 $this->session->set_flashdata('message', "Oops! Something went wrong. Try again later");
				 $this->session->set_flashdata('message_type', 'danger');
				 redirect('manage/menu_attribute', 'refresh');
			}
			} else {
				$insert				= _DB_insert($this->tables['attributes'], array('attribute_name' => $attribute_name, 'status' => $attribute_status));
				if ($insert) {
				 $this->session->set_flashdata('message', "Attribute '<strong>$attribute_name</strong>' has been Added successfully.");
				$this->session->set_flashdata('message_type', 'success');
				redirect('manage/menu_attribute', 'refresh');
			} else {
				 $this->session->set_flashdata('message', "Oops! Something went wrong. Try again later");
				 $this->session->set_flashdata('message_type', 'danger');
				 redirect('manage/menu_attribute', 'refresh');
			}
			}
			
			} else {
				 $this->session->set_flashdata('message', validation_errors());
				 $this->session->set_flashdata('message_type', 'danger'); 
			}
			$this->add_data(compact('attribute_name', 'attribute_status'));
			redirect('manage/add_attribute', 'refresh');
	}
	
	#list menu categories
	function menu_categories() {
		$allCategories				= $this->manage_model->get_categories();
		$this->data['categories']	= $allCategories;
		$this->render('manage-menu-categories');
	}
	
	#add new category
	function add_category() {
		$this->data['attributes']		= _DB_data($this->tables['attributes'], array('status' => 1), null, null, null);
		$this->render('add-menu-category');
	}
	
	#sumbit category
	function submit_category() {
		$dateTime				= date('Y-m-d H:i:s');		
		$editId					= $this->input->post('edit_id', true);
		if (!$editId) {
		$this->form_validation->set_rules('category_name','Category Name','trim|required|is_unique[category_entity.entity_name]');
		} else {
			$this->form_validation->set_rules('category_name','Category Name','trim|required');
		}
		$this->form_validation->set_rules('attribute_name','Attribute','trim|required');
		$this->form_validation->set_rules('category_status','Category Status','trim|required');
		$category_name			= $this->input->post('category_name', true);
		$attribute_id			= $this->input->post('attribute_name', true);
		$category_status		= $this->input->post('category_status', true);
		if ($this->form_validation->run() == true) {
		 	
			if ($editId) {
			$update				= _DB_update($this->tables['category_entity'], array('attribute_id' => $attribute_id, 'entity_name' => $category_name,  'updated_at' => $dateTime, 'status' => $category_status), array('entity_id' => $editId));
				if ($update) {
				 $this->session->set_flashdata('message', "Category '<strong>$category_name</strong>' has been updated successfully.");
				$this->session->set_flashdata('message_type', 'success');
				redirect('manage/menu_categories', 'refresh');
			} else {
				 $this->session->set_flashdata('message', "Oops! Something went wrong. Try again later");
				 $this->session->set_flashdata('message_type', 'danger');
				 redirect('manage/menu_categories', 'refresh');
			}
			} else {
				$insert				= _DB_insert($this->tables['category_entity'], array('attribute_id' => $attribute_id, 'entity_name' => $category_name, 'created_at' => $dateTime, 'status' => $category_status));
				if ($insert) {
				 $this->session->set_flashdata('message', "Category '<strong>$category_name</strong>' has been Added successfully.");
				$this->session->set_flashdata('message_type', 'success');
				redirect('manage/menu_categories', 'refresh');
			} else {
				 $this->session->set_flashdata('message', "Oops! Something went wrong. Try again later");
				 $this->session->set_flashdata('message_type', 'danger');
				 redirect('manage/menu_categories', 'refresh');
			}
			}
			
			} else {
				 $this->session->set_flashdata('message', validation_errors());
				 $this->session->set_flashdata('message_type', 'danger'); 
			}
			$this->add_data(compact('attribute_name', 'attribute_status'));
			redirect('manage/add_category', 'refresh');
	}
	
	#load edit category
	function edit_category($catId	= NULL) {
		if ($catId) {
			
			$get_data					= _DB_get_record($this->tables['category_entity'], array('entity_id' => $catId));
			$attributes					= _DB_data($this->tables['attributes'], array('status' => 1), null, null, null);
			$this->add_data(compact('get_data', 'attributes'));
			$this->render('add-menu-category');
			
		} else {
			$this->session->set_flashdata('message', "Oops! something went wrong. Try again later.");
			$this->session->set_flashdata('message_type', 'danger');
			redirect('manage/menu_categories', 'refresh');
		}
	} 
	
}