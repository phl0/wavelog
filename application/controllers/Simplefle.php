<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SimpleFLE extends CI_Controller {

    public function index() {
        $this->load->model('user_model');
		if(!$this->user_model->authorize(2)) { $this->session->set_flashdata('notice', 'You\'re not allowed to do that!'); redirect('dashboard'); }


		$this->load->model('stations');
		$this->load->model('logbook_model');
		$this->load->model('modes');
		$this->load->model('bands');

		$data['station_profile'] = $this->stations->all_of_user();			// Used in the view for station location select
		$data['bands'] = $this->bands->get_all_bands();						// Fetching Bands for SFLE
		$data['modes'] = $this->modes_array();								// Fetching Modes for SFLE
		$data['active_station_profile'] = $this->stations->find_active();	// Prepopulate active Station in Station Location Selector
		$data['sat_active'] = array_search("SAT", $this->bands->get_user_bands(), true);


		$data['page_title'] = "Simple Fast Log Entry";

		$footerData = [];
		$footerData['scripts'] = [
			'assets/js/moment.min.js',
			'assets/js/datetime-moment.js',
			'assets/js/sections/simplefle.js?' . filemtime(realpath(__DIR__ . "/../../assets/js/sections/simplefle.js"))
		];

		$this->load->view('interface_assets/header', $data);
		$this->load->view('simplefle/index', $data);
		$this->load->view('interface_assets/footer', $footerData);

    }

	public function displaySyntax() {
		$this->load->view('simplefle/syntax_help');
	}

	public function displayOptions() {

		$data['callbook_lookup'] = $this->user_options_model->get_options('SimpleFLE',array('option_name'=>'callbook_lookup','option_key'=>'boolean'))->row()->option_value ?? 'true';

		$this->load->view('simplefle/options', $data);

	}

	public function saveOptions() {

		if($this->input->post('callbook_lookup')) {
			$this->user_options_model->set_option('SimpleFLE', 'callbook_lookup',  array('boolean' => xss_clean($this->input->post('callbook_lookup'))));
		} else {
			log_message('debug', 'SimpleFLE, saveOptions(); No Options to save. No Post Data');
		}

	}

	private function modes_array() {

		$this->load->model('modes');

		$result = $this->modes->all()->result_array();
		$modes = array();

		foreach ($result as $row) {
			$modes[] = array(
				'mode' => $row['mode'],
				'submode' => $row['submode'],
				'qrgmode' => $row['qrgmode']
			);
		}

		return $modes;
	}
	
}
