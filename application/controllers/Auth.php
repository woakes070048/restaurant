<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth extends Cpanel_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function login()
	{ 
		// Check whether the user is already logged-in
		if ($this->ion_auth->logged_in()) {
			redirect('', 'refresh');
		}

		$this->load->library('form_validation');
		//validate form input
		$this->form_validation->set_rules('identity', 'Email', 'required');
		$this->form_validation->set_rules('password', 'Password', 'required');
		

		if ($this->form_validation->run() == true) {
			//check to see if the user is logging in
			//check for "remember me"
			
			 $remember	 		= (bool) $this->input->post('remember'); 
			 if ($remember) {
				 $rememberMe	= TRUE;
			 } else {
				 $rememberMe	= FALSE;
			 }
			
			if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $rememberMe)) {
				//if the login is successful
				//redirect them back to the home page
				$this->session->set_flashdata('message', $this->ion_auth->messages());
				$this->session->set_flashdata('message_type', 'info');
				redirect('', 'refresh');
			} else {
				//if the login was un-successful
				//redirect them back to the login page
				$this->session->set_flashdata('message', $this->ion_auth->errors());
				$this->session->set_flashdata('message_type', 'danger');
				redirect('auth/login', 'refresh'); //use redirects instead of loading views for compatibility with MY_Controller libraries
			}
		} else {
			//the user is not logging in so display the login page
			//set the flash data error message if there is one
			if (validation_errors()) {
				$this->data['message'] 		= validation_errors();
				$this->data['message_type'] = 'danger';
			} else {
				$this->data['message'] 		= $this->session->flashdata('message');
				$this->data['message_type'] = $this->session->flashdata('message_type');
			}
			$this->render('login');
		}
	} 
	public function change_password() { // change password
		
		if ($this->ion_auth->logged_in()) { // Check whether the user is already logged-in
			
		
		$this->load->library('form_validation');
		$this->form_validation->set_rules('user_name', 'user name', 'required');
		$this->form_validation->set_rules('current_pass', 'current password', 'required');
		$this->form_validation->set_rules('new_password', 'new password', 'required');
		$this->form_validation->set_rules('con_password', 'confirm password', 'required');
		if ($this->form_validation->run() == true) { 
				if ($this->ion_auth->change_password($this->input->post('user_name'), $this->input->post('current_pass'), $this->input->post('new_password'))) {
				$this->session->set_flashdata('message', $this->ion_auth->messages());
				$this->session->set_flashdata('message_type', 'info');
				redirect('auth/logout', 'refresh');
				} else {
				$this->session->set_flashdata('message', $this->ion_auth->errors());
				$this->session->set_flashdata('message_type', 'danger');
				redirect('manage/', 'refresh'); //use redirects instead of loading views for compatibility with MY_Controller libraries
				}
		} else {
			//the user is not logging in so display the login page
			//set the flash data error message if there is one
			if (validation_errors()) {
				$this->data['message'] 		= "Sorry you don't have permission to access this application";
				$this->data['message_type'] = 'danger';
			} else {
				$this->data['message'] 		= $this->session->flashdata('message');
				$this->data['message_type'] = $this->session->flashdata('message_type');
			}

			redirect('manage/','');
		}
	} else {
		//if the login was un-successful
		//redirect them back to the login page
		$this->session->set_flashdata('message', $this->ion_auth->errors());
		$this->session->set_flashdata('message_type', 'danger');
		redirect('auth/login', 'refresh'); //use redirects instead of loading views for compatibility with MY_Controller libraries
	}
		
	}
	
	public function create_user() { // create new user . Admin privilege
		
		if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) { // Check whether the user is already logged-in and is admin 
			
		$this->load->library('form_validation');
		$this->form_validation->set_rules('user_name', 'user name', 'required');
		$this->form_validation->set_rules('new_password', 'new password', 'required');
		$this->form_validation->set_rules('con_password', 'confirm password', 'required');
		$this->form_validation->set_rules('user_email', 'email address', 'required');
		$this->form_validation->set_rules('user_first_name', 'first name', 'required');
		$this->form_validation->set_rules('user_group', 'user type', 'required');
		if ($this->form_validation->run() == true) {
			
			$userName			= $this->input->post('user_name');
			$password			= $this->input->post('new_password');
			$email 				= $this->input->post('user_email');
			$fisrtName			= $this->input->post('user_first_name');
			$lastName			= $this->input->post('user_last_name');
			$userGroup			= $this->input->post('user_group');
			$additionalData 	= array(
								'first_name' => $fisrtName,
								'last_name' => $lastName,
								);
			$group 				= array($userGroup); // Sets user.
			if ($this->ion_auth->register($userName, $password, $email, $additionalData, $group)) {
				$this->session->set_flashdata('message', $this->ion_auth->messages());
				$this->session->set_flashdata('message_type', 'success');
				redirect('manage/', 'refresh');
			} else {
				$this->session->set_flashdata('message', $this->ion_auth->errors());
				$this->session->set_flashdata('message_type', 'danger');
				redirect('manage/', 'refresh');
			}
				
			}
			
		}
	
	}
	public function staus_control(){ // status changing of users
		if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) { // Check whether the user is already logged-in and is admin 
			 $userID			= $this->input->post('userDt');
			 $user 				= $this->ion_auth->user($userID)->row();
			 $statusCode		= ($user->active == 1) ? 0 : 1;
			 $data 				= array(
										'active' => $statusCode
										 );
			 if ($this->ion_auth->update($userID,$data)) {
				 $this->render('ajax/user-list');
			 }
		}
	}
	
	public function remove_user() {
		if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) { // Check whether the user is already logged-in and is admin 
			 $userID			= $this->input->post('userDt');
			 if ($this->ion_auth->delete_user($userID)) {
				 $this->render('ajax/user-list');
			 }
		}
	}
	
	public function logout()
	{
		//log the user out
		$logout = $this->ion_auth->logout();

		//redirect them to the login page
		$this->session->set_flashdata('message', $this->ion_auth->messages());
		$this->session->set_flashdata('message_type', 'info');
		redirect('auth/login', 'refresh');
	}

}