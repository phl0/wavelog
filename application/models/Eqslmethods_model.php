<?php

class Eqslmethods_model extends CI_Model {

    function sync() {

        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $users = $this->get_eqsl_users();

        foreach ($users as $user) {
            $this->uploadUser($user->user_id, $user->user_eqsl_name, $user->user_eqsl_password);
            $this->downloadUser($user->user_id, $user->user_eqsl_name, $user->user_eqsl_password);
        }
    }

    function downloadUser($userid, $username, $password) {
        $this->load->library('EqslImporter');

        $config['upload_path'] = './uploads/';
        $eqsl_locations = $this->all_of_user_with_eqsl_nick_defined($userid);

        $eqsl_results = array();

        foreach ($eqsl_locations->result_array() as $eqsl_location) {
            $this->eqslimporter->from_callsign_and_QTH(
                $eqsl_location['station_callsign'],
                $eqsl_location['eqslqthnickname'],
                $config['upload_path'],
                $eqsl_location['station_id']
            );

            $eqsl_results[] = $this->eqslimporter->fetch($password); // Hint: for debugging add YYYYMMDD as second argument to force from older date
        }
    }

    function uploadUser($userid, $username, $password) {
        $data['user_eqsl_name'] = $this->security->xss_clean($username);
        $data['user_eqsl_password'] = $this->security->xss_clean($password);
        $clean_userid = $this->security->xss_clean($userid);

        $qslsnotsent = $this->eqslmethods_model->eqsl_not_yet_sent($clean_userid);

        foreach ($qslsnotsent->result_array() as $qsl) {
            $data['user_eqsl_name'] = $qsl['station_callsign'];
            $adif = $this->generateAdif($qsl, $data);

            $status = $this->uploadQso($adif, $qsl);
        }
    }

    // Build out the ADIF info string according to specs https://eqsl.cc/qslcard/ADIFContentSpecs.cfm
    function generateAdif($qsl, $data) {
        $COL_QSO_DATE = date('Ymd', strtotime($qsl['COL_TIME_ON']));
        $COL_TIME_ON = date('Hi', strtotime($qsl['COL_TIME_ON']));

        # Set up the single record file
        $adif = "https://www.eqsl.cc/qslcard/importADIF.cfm?";
        $adif .= "ADIFData=WavelogUpload%20";

        /* Handy reference of escaping chars
			"<" = 3C
			">" = 3E
			":" = 3A
			" " = 20
			"_" = 5F
			"-" = 2D
			"." = 2E
			"&" = 26
		*/

        $adif .= "%3C";
        $adif .= "ADIF%5FVER";
        $adif .= "%3A";
        $adif .= "4";
        $adif .= "%3E";
        $adif .= "1%2E00 ";
        $adif .= "%20";

        $adif .= "%3C";
        $adif .= "EQSL%5FUSER";
        $adif .= "%3A";
        $adif .= strlen($data['user_eqsl_name']);
        $adif .= "%3E";
        $adif .= $data['user_eqsl_name'];
        $adif .= "%20";

        $adif .= "%3C";
        $adif .= "EQSL%5FPSWD";
        $adif .= "%3A";
        $adif .= strlen($data['user_eqsl_password']);
        $adif .= "%3E";
        $adif .= urlencode($data['user_eqsl_password']);
        $adif .= "%20";

        $adif .= "%3C";
        $adif .= "EOH";
        $adif .= "%3E";

        # Lay out the required fields
        $adif .= "%3C";
        $adif .= "QSO%5FDATE";
        $adif .= "%3A";
        $adif .= "8";
        $adif .= "%3E";
        $adif .= $COL_QSO_DATE;
        $adif .= "%20";

        $adif .= "%3C";
        $adif .= "TIME%5FON";
        $adif .= "%3A";
        $adif .= "4";
        $adif .= "%3E";
        $adif .= $COL_TIME_ON;
        $adif .= "%20";

        $adif .= "%3C";
        $adif .= "CALL";
        $adif .= "%3A";
        $adif .= strlen($qsl['COL_CALL']);
        $adif .= "%3E";
        $adif .= $qsl['COL_CALL'];
        $adif .= "%20";

        $adif .= "%3C";
        $adif .= "MODE";
        $adif .= "%3A";
        $adif .= strlen($qsl['COL_MODE']);
        $adif .= "%3E";
        $adif .= $qsl['COL_MODE'];
        $adif .= "%20";

        if (isset($qsl['COL_SUBMODE'])) {
            $adif .= "%3C";
            $adif .= "SUBMODE";
            $adif .= "%3A";
            $adif .= strlen($qsl['COL_SUBMODE']);
            $adif .= "%3E";
            $adif .= $qsl['COL_SUBMODE'];
            $adif .= "%20";
        }

        $adif .= "%3C";
        $adif .= "BAND";
        $adif .= "%3A";
        $adif .= strlen($qsl['COL_BAND']);
        $adif .= "%3E";
        $adif .= $qsl['COL_BAND'];
        $adif .= "%20";

        # End all the required fields

        // adding RST_Sent
        $adif .= "%3C";
        $adif .= "RST%5FSENT";
        $adif .= "%3A";
        $adif .= strlen($qsl['COL_RST_SENT']);
        $adif .= "%3E";
        $adif .= $qsl['COL_RST_SENT'];
        $adif .= "%20";

        // adding prop mode if it isn't blank
        if ($qsl['COL_PROP_MODE']) {
            $adif .= "%3C";
            $adif .= "PROP%5FMODE";
            $adif .= "%3A";
            $adif .= strlen($qsl['COL_PROP_MODE']);
            $adif .= "%3E";
            $adif .= $qsl['COL_PROP_MODE'];
            $adif .= "%20";
        }

        // adding sat name if it isn't blank
        if ($qsl['COL_SAT_NAME'] != '') {
            $adif .= "%3C";
            $adif .= "SAT%5FNAME";
            $adif .= "%3A";
            $adif .= strlen($qsl['COL_SAT_NAME']);
            $adif .= "%3E";
            $adif .= str_replace('-', '%2D', $qsl['COL_SAT_NAME']);
            $adif .= "%20";
        }

        // adding sat mode if it isn't blank
        if ($qsl['COL_SAT_MODE'] != '') {
            $adif .= "%3C";
            $adif .= "SAT%5FMODE";
            $adif .= "%3A";
            $adif .= strlen($qsl['COL_SAT_MODE']);
            $adif .= "%3E";
            $adif .= $qsl['COL_SAT_MODE'];
            $adif .= "%20";
        }

        // adding qslmsg if it isn't blank
        if ($qsl['COL_QSLMSG'] != '') {
            $qsl['COL_QSLMSG'] = str_replace(array(chr(10), chr(13)), array(' ', ' '), $qsl['COL_QSLMSG']);
            $adif .= "%3C";
            $adif .= "QSLMSG";
            $adif .= "%3A";
            $adif .= strlen($qsl['COL_QSLMSG']);
            $adif .= "%3E";
            $adif .= str_replace('&', '%26', $qsl['COL_QSLMSG']);
            $adif .= "%20";
        }

        if ($qsl['eqslqthnickname'] != '') {
            $adif .= "%3C";
            $adif .= "APP%5FEQSL%5FQTH%5FNICKNAME";
            $adif .= "%3A";
            $adif .= strlen($qsl['eqslqthnickname']);
            $adif .= "%3E";
            $adif .= $qsl['eqslqthnickname'];
            $adif .= "%20";
        }

        // adding sat mode if it isn't blank
        if ($qsl['station_gridsquare'] != '') {
            $adif .= "%3C";
            $adif .= "MY%5FGRIDSQUARE";
            $adif .= "%3A";
            $adif .= strlen($qsl['station_gridsquare']);
            $adif .= "%3E";
            $adif .= $qsl['station_gridsquare'];
            $adif .= "%20";
        }

        # Tie a bow on it!
        $adif .= "%3C";
        $adif .= "EOR";
        $adif .= "%3E";

        # Make sure we don't have any spaces
        $adif = str_replace(" ", '%20', $adif);

        return $adif;
    }

    function uploadQso($adif, $qsl) {
        $this->load->model('eqslmethods_model');
        $status = "";

        // begin script
        $ch = curl_init();

        // basic curl options for all requests
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        // use the URL we built
        curl_setopt($ch, CURLOPT_URL, $adif);

        $result = curl_exec($ch);
        $chi = curl_getinfo($ch);
        curl_close($ch);

        /*  Time for some error handling
			Things we might get back
			Result: 0 out of 0 records added -> eQSL didn't understand the format
			Result: 1 out of 1 records added -> Fantastic
			Error: No match on eQSL_User/eQSL_Pswd -> eQSL credentials probably wrong
			Warning: Y=2013 M=08 D=11 F6ARS 15M JT65 Bad record: Duplicate
			Result: 0 out of 1 records added -> Dupe, OM!
		*/

        if ($chi['http_code'] == "200") {
            if (stristr($result, "Result: 1 out of 1 records added")) {
                $status = "Sent";
                $this->eqslmethods_model->eqsl_mark_sent($qsl['COL_PRIMARY_KEY']);
            } else {
                if (stristr($result, "Error: No match on eQSL_User/eQSL_Pswd")) {
                    $this->session->set_flashdata('warning', 'Your eQSL username and/or password is incorrect.');
                    redirect('eqsl/export');
                } else {
                    if (stristr($result, "Result: 0 out of 0 records added")) {
                        $this->session->set_flashdata('warning', 'Something went wrong with eQSL.cc!');
                        redirect('eqsl/export');
                    } else {
                        if (stristr($result, "Bad record: Duplicate")) {
                            $status = "Duplicate";

                            # Mark the QSL as sent if this is a dupe.
                            $this->eqslmethods_model->eqsl_mark_sent($qsl['COL_PRIMARY_KEY']);
                        }
                    }
                }
            }
        } else {
            if ($chi['http_code'] == "500") {
                $this->session->set_flashdata('warning', 'eQSL.cc is experiencing issues. Please try exporting QSOs later.');
                redirect('eqsl/export');
            } else {
                if ($chi['http_code'] == "400") {
                    $this->session->set_flashdata('warning', 'There was an error in one of the QSOs. You might want to manually upload them.');
                    redirect('eqsl/export');
                    $status = "Error";
                } else {
                    if ($chi['http_code'] == "404") {
                        $this->session->set_flashdata('warning', 'It seems that the eQSL site has changed. Please open up an issue on GitHub.');
                        redirect('eqsl/export');
                    }
                }
            }
        }
        log_message('debug', $result);
        return $status;
    }

    function mark_all_as_sent() {
        $data = array(
            'COL_EQSL_QSL_SENT' => 'Y',
            'COL_EQSL_QSLSDATE'  => date('Y-m-d') . " 00:00:00",
        );

        $userid = $this->session->userdata('user_id');
        if ($userid ?? '' != '') {
            $stations = $this->get_all_user_locations($userid);
            $logbooks_locations_array = array();
            foreach ($stations->result() as $row) {
                array_push($logbooks_locations_array, $row->station_id);
            }
            if (count($logbooks_locations_array) > 0) {
                $this->db->where_in('station_id', $logbooks_locations_array);
                $this->db->group_start();
                $this->db->where('COL_EQSL_QSL_SENT', 'N');
                $this->db->or_where('COL_EQSL_QSL_SENT', 'R');
                $this->db->or_where('COL_EQSL_QSL_SENT', 'Q');
                $this->db->or_where('COL_EQSL_QSL_SENT', null);
                $this->db->group_end();

                $this->db->update($this->config->item('table_name'), $data);
            }
        }
    }

    function get_eqsl_users() {
        $this->db->select('user_eqsl_name, user_eqsl_password, user_id');
        $this->db->where('coalesce(user_eqsl_name, "") != ""');
        $this->db->where('coalesce(user_eqsl_password, "") != ""');
        $query = $this->db->get($this->config->item('auth_table'));
        return $query->result();
    }

    /*
     * Gets all station location for user, for use in cron where we don't have any login
     */
    function get_all_user_locations($userid) {
        $this->db->select('station_profile.*, dxcc_entities.name as station_country, dxcc_entities.end as dxcc_end');
        $this->db->where('user_id', $userid);
        $this->db->join('dxcc_entities', 'station_profile.station_dxcc = dxcc_entities.adif', 'left outer');
        return $this->db->get('station_profile');
    }

    // Show all QSOs we need to send to eQSL
    function eqsl_not_yet_sent($userid = null) {
        if ($userid == null) {
            $this->load->model('logbooks_model');
            $logbooks_locations_array = $this->logbooks_model->list_logbook_relationships($this->session->userdata('active_station_logbook'));
        } else {
            $stations = $this->get_all_user_locations($userid);
            $logbooks_locations_array = array();
            foreach ($stations->result() as $row) {
                array_push($logbooks_locations_array, $row->station_id);
            }
            array_push($logbooks_locations_array, -9999);
        }

        $this->db->select('station_profile.*, ' . $this->config->item('table_name') . '.COL_PRIMARY_KEY, ' . $this->config->item('table_name') . '.COL_TIME_ON, ' . $this->config->item('table_name') . '.COL_CALL, ' . $this->config->item('table_name') . '.COL_MODE, ' . $this->config->item('table_name') . '.COL_SUBMODE, ' . $this->config->item('table_name') . '.COL_BAND, ' . $this->config->item('table_name') . '.COL_COMMENT, ' . $this->config->item('table_name') . '.COL_RST_SENT, ' . $this->config->item('table_name') . '.COL_PROP_MODE, ' . $this->config->item('table_name') . '.COL_SAT_NAME, ' . $this->config->item('table_name') . '.COL_SAT_MODE, ' . $this->config->item('table_name') . '.COL_QSLMSG');
        $this->db->from('station_profile');
        $this->db->join($this->config->item('table_name'), 'station_profile.station_id = ' . $this->config->item('table_name') . '.station_id');
        $this->db->where("coalesce(station_profile.eqslqthnickname, '') <> ''");
        $this->db->where($this->config->item('table_name') . '.COL_CALL !=', '');
        $this->db->group_start();
        $this->db->where($this->config->item('table_name') . '.COL_EQSL_QSL_SENT is null');
        $this->db->or_where($this->config->item('table_name') . '.COL_EQSL_QSL_SENT', '');
        $this->db->or_where($this->config->item('table_name') . '.COL_EQSL_QSL_SENT', 'R');
        $this->db->or_where($this->config->item('table_name') . '.COL_EQSL_QSL_SENT', 'Q');
        $this->db->or_where($this->config->item('table_name') . '.COL_EQSL_QSL_SENT', 'N');
        $this->db->group_end();
        $this->db->where_in('station_profile.station_id', $logbooks_locations_array);

        return $this->db->get();
    }

    // Show all QSOs whose eQSL card images we did not download yet
    function eqsl_not_yet_downloaded($userid = null) {
        if ($userid == null) {
            $this->load->model('logbooks_model');
            $logbooks_locations_array = $this->logbooks_model->list_logbook_relationships($this->session->userdata('active_station_logbook'));
        } else {
            $stations = $this->get_all_user_locations($userid);
            $logbooks_locations_array = array();
            foreach ($stations->result() as $row) {
                array_push($logbooks_locations_array, $row->station_id);
            }
            array_push($logbooks_locations_array, -9999);
        }

        $this->db->select('station_profile.station_id, ' . $this->config->item('table_name') . '.COL_PRIMARY_KEY, ' . $this->config->item('table_name') . '.COL_TIME_ON, ' . $this->config->item('table_name') . '.COL_CALL, ' . $this->config->item('table_name') . '.COL_MODE, ' . $this->config->item('table_name') . '.COL_SUBMODE, ' . $this->config->item('table_name') . '.COL_BAND, ' . $this->config->item('table_name') . '.COL_PROP_MODE, ' . $this->config->item('table_name') . '.COL_SAT_NAME, ' . $this->config->item('table_name') . '.COL_SAT_MODE, ' . $this->config->item('table_name') . '.COL_QSLMSG, eQSL_images.qso_id');
        $this->db->from('station_profile');
        $this->db->join($this->config->item('table_name'), 'station_profile.station_id = ' . $this->config->item('table_name') . '.station_id');
        $this->db->join('eQSL_images', 'eQSL_images.qso_id = ' . $this->config->item('table_name') . '.COL_PRIMARY_KEY', 'left outer');
        //$this->db->where("coalesce(station_profile.eqslqthnickname, '') <> ''");
        $this->db->where($this->config->item('table_name') . '.COL_CALL !=', '');
        $this->db->where($this->config->item('table_name') . '.COL_EQSL_QSL_RCVD', 'Y');
        $this->db->where('qso_id', NULL);
        $this->db->where_in('station_profile.station_id', $logbooks_locations_array);
        $this->db->order_by("COL_TIME_ON", "desc");

        return $this->db->get();
    }

    // Mark the QSO as sent to eQSL
    function eqsl_mark_sent($primarykey) {
        $data = array(
            'COL_EQSL_QSLSDATE' => date('Y-m-d H:i:s'), // eQSL doesn't give us a date, so let's use current
            'COL_EQSL_QSL_SENT' => 'Y',
        );

        $this->db->where('COL_PRIMARY_KEY', $primarykey);

        $this->db->update($this->config->item('table_name'), $data);

        return "eQSL Sent";
    }

    // Returns all the distinct callsign, eqsl nick pair for the current user/supplied user
    function all_of_user_with_eqsl_nick_defined($userid = null) {
        if ($userid == null) {
            $this->db->where('user_id', $this->session->userdata('user_id'));
        } else {
            $this->db->where('user_id', $userid);
        }

        $this->db->where('eqslqthnickname IS NOT NULL');
        $this->db->where('eqslqthnickname !=', '');
        $this->db->from('station_profile');
        $this->db->select('station_callsign, eqslqthnickname, station_id');
        $this->db->distinct(TRUE);

        return $this->db->get();
    }

    // Get the last date we received an eQSL
    function eqsl_last_qsl_rcvd_date($callsign, $nickname) {
        $qso_table_name = $this->config->item('table_name');
        $this->db->from($qso_table_name);

        $this->db->join(
            'station_profile',
            'station_profile.station_id = ' . $qso_table_name . '.station_id AND station_profile.eqslqthnickname != ""'
        );
        $this->db->where('station_profile.station_callsign', $callsign);
        $this->db->where('station_profile.eqslqthnickname', $nickname);

        $this->db->select("DATE_FORMAT(COL_EQSL_QSLRDATE,'%Y%m%d') AS COL_EQSL_QSLRDATE", FALSE);
        $this->db->where('COL_EQSL_QSLRDATE IS NOT NULL');
        $this->db->order_by("COL_EQSL_QSLRDATE", "desc");
        $this->db->limit(1);

        $query = $this->db->get();
        $row = $query->row();

        if (isset($row->COL_EQSL_QSLRDATE)) {
            return $row->COL_EQSL_QSLRDATE;
        } else {
            // No previous date (first time import has run?), so choose UNIX EPOCH!
            // Note: date is yyyy/mm/dd format
            return '19700101';
        }
    }

    // Update a QSO with eQSL QSL info
    // We could also probably use this:
    // https://eqsl.cc/qslcard/VerifyQSO.txt
    // https://www.eqsl.cc/qslcard/ImportADIF.txt
    function eqsl_update($datetime, $callsign, $band, $mode, $qsl_status, $station_callsign, $station_id) {
        $data = array(
            'COL_EQSL_QSLRDATE' => date('Y-m-d H:i:s'), // eQSL doesn't give us a date, so let's use current
            'COL_EQSL_QSL_RCVD' => $qsl_status
        );

        $this->db->where('COL_TIME_ON >= DATE_ADD(DATE_FORMAT("' . $datetime . '", \'%Y-%m-%d %H:%i\' ), INTERVAL -15 MINUTE )');
        $this->db->where('COL_TIME_ON <= DATE_ADD(DATE_FORMAT("' . $datetime . '", \'%Y-%m-%d %H:%i\' ), INTERVAL 15 MINUTE )');
        $this->db->where('COL_CALL', $callsign);
        $this->db->where('COL_STATION_CALLSIGN', $station_callsign);
        $this->db->where('COL_BAND', $band);
        $this->db->where('COL_MODE', $mode);
        $this->db->where('station_id', $station_id);

        $this->db->update($this->config->item('table_name'), $data);

        return "Updated";
    }

    // Determine if we've already received an eQSL for this QSO
    function eqsl_dupe_check($datetime, $callsign, $band, $mode, $qsl_status, $station_callsign, $station_id) {
        $this->db->select('COL_EQSL_QSLRDATE');
        $this->db->where('COL_TIME_ON >= DATE_ADD(DATE_FORMAT("' . $datetime . '", \'%Y-%m-%d %H:%i\' ), INTERVAL -15 MINUTE )');
        $this->db->where('COL_TIME_ON <= DATE_ADD(DATE_FORMAT("' . $datetime . '", \'%Y-%m-%d %H:%i\' ), INTERVAL 15 MINUTE )');
        $this->db->where('COL_CALL', $callsign);
        $this->db->where('COL_BAND', $band);
        $this->db->where('COL_MODE', $mode);
        $this->db->where('COL_STATION_CALLSIGN', $station_callsign);
        $this->db->where('COL_EQSL_QSL_RCVD', $qsl_status);
        $this->db->where('station_id', $station_id);
        $this->db->limit(1);

        $query = $this->db->get($this->config->item('table_name'));
        $row = $query->row();

        if ($row != null) {
            return true;
        }
        return false;
    }
}
