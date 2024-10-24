<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Notes extends CI_Controller {

	function __construct()
	{
		parent::__construct();

		$this->load->model('user_model');
		if(!$this->user_model->authorize(2)) { $this->session->set_flashdata('error', __("You're not allowed to do that!")); redirect('dashboard'); }
	}


	/* Displays all notes in a list */
	public function index() {
		$this->load->model('note');
		$data['notes'] = $this->note->list_all();
		$data['page_title'] = __("Notes");
		$this->load->view('interface_assets/header', $data);
		$this->load->view('notes/main');
		$this->load->view('interface_assets/footer');
	}
	
	/* Provides function for adding notes to the system. */
	function add() {
	
		$this->load->model('note');
	
		$this->load->library('form_validation');

		$this->form_validation->set_rules('title', 'Note Title', 'required');
		$this->form_validation->set_rules('content', 'Content', 'required');


		if ($this->form_validation->run() == FALSE)
		{
			$data['page_title'] = __("Add Notes");
			$this->load->view('interface_assets/header', $data);
			$this->load->view('notes/add');
			$this->load->view('interface_assets/footer');
		}
		else
		{	
			$this->note->add();
			
			redirect('notes');
		}
	}
	
	/* View Notes */
	function view($id) {

		$clean_id = $this->security->xss_clean($id);

		if (! is_numeric($clean_id)) {
			show_404();
		}

		$this->load->model('note');
		
		$data['note'] = $this->note->view($clean_id);
		
		// Display
		$data['page_title'] = __("Note");
		$this->load->view('interface_assets/header', $data);
		$this->load->view('notes/view');
		$this->load->view('interface_assets/footer');
	}
	
	/* Edit Notes */
	function edit($id) {

		$clean_id = $this->security->xss_clean($id);

		if (! is_numeric($clean_id)) {
			show_404();
		}

		$this->load->model('note');
		$data['id'] = $clean_id;
		
		$data['note'] = $this->note->view($clean_id);
			
		$this->load->library('form_validation');

		$this->form_validation->set_rules('title', 'Note Title', 'required');
		$this->form_validation->set_rules('content', 'Content', 'required');


		if ($this->form_validation->run() == FALSE)
		{
			$data['page_title'] = __("Edit Note");
			$this->load->view('interface_assets/header', $data);
			$this->load->view('notes/edit');
			$this->load->view('interface_assets/footer');
		}
		else
		{
			$this->note->edit();
			
			redirect('notes');
		}
	}
	
	/* Delete Note */
	function delete($id) {

		$clean_id = $this->security->xss_clean($id);

		if (! is_numeric($clean_id)) {
			show_404();
		}	

		$this->load->model('note');
		$this->note->delete($clean_id);
		
		redirect('notes');
	}
}