<div class="container">
    <h1><?= __("Timeline"); ?></h1>

    <form class="form" action="<?php echo site_url('timeline'); ?>" method="post" enctype="multipart/form-data">
        <!-- Select Basic -->
                <div class="mb-3 row">
                    <label class="col-md-1 control-label" for="band"><?= __("Band") ?></label>
                    <div class="col-md-3">
                        <select id="band" name="band" class="form-select">
                            <option value="All" <?php if ($this->input->post('band') == "All" || $this->input->method() !== 'post') echo ' selected'; ?> ><?= __("All") ?></option>
                            <?php foreach($worked_bands as $band) {
                                echo '<option value="' . $band . '"';
                                if ($this->input->post('band') == $band) echo ' selected';
                                echo '>' . $band . '</option>'."\n";
                            } ?>
                        </select>
                    </div>

                    <label class="col-md-1 control-label" for="mode"><?= __("Mode") ?></label>
                    <div class="col-md-3">
                        <select id="mode" name="mode" class="form-select">
                            <option value="All" <?php if ($this->input->post('mode') == "All" || $this->input->method() !== 'post') echo ' selected'; ?> ><?= __("All") ?></option>
                            <?php
                            foreach($modes->result() as $mode){
                                if ($mode->submode == null) {
                                    echo '<option value="' . $mode->mode . '"';
                                    if ($this->input->post('mode') == $mode->mode) echo ' selected';
                                    echo '>' . $mode->mode . '</option>'."\n";
                                } else {
                                    echo '<option value="' . $mode->submode . '"';
                                    if ($this->input->post('mode') == $mode->submode) echo ' selected';
                                    echo '>' . $mode->submode . '</option>'."\n";
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>

        <div class="mb-3 row">
            <label class="col-md-1 control-label" for="award"><?= __("Award") ?></label>
                <div class="col-md-3">
                    <select id="award" name="award" class="form-select">
                        <option value="dxcc" <?php if ($this->input->post('award') == "dxcc") echo ' selected'; ?> ><?= __("DX Century Club (DXCC)"); ?></option>
                        <option value="was" <?php if ($this->input->post('award') == "was") echo ' selected'; ?> ><?= __("Worked All States (WAS)"); ?></option>
                        <option value="iota" <?php if ($this->input->post('award') == "iota") echo ' selected'; ?> ><?= __("Islands On The Air (IOTA)"); ?></option>
                        <option value="waz" <?php if ($this->input->post('award') == "waz") echo ' selected'; ?> ><?= __("Worked All Zones (WAZ)"); ?></option>
                        <option value="vucc" <?php if ($this->input->post('award') == "vucc") echo ' selected'; ?> ><?= __("VHF / UHF Century Club (VUCC)"); ?></option>
                    </select>
                </div>
                <div class="col-md-1 control-label"><?= __("Confirmation") ?></div>
                <div class="col-md-3">
                    <div class="form-check-inline">
                        <input class="form-check-input" type="checkbox" name="qsl" value="1" id="qsl" <?php if ($this->input->post('qsl'))  echo ' checked="checked"'; ?> >
                        <label class="form-check-label" for="qsl"><?= __("QSL") ?></label>
                    </div>
                    <div class="form-check-inline">
                        <input class="form-check-input" type="checkbox" name="lotw" value="1" id="lotw" <?php if ($this->input->post('lotw')) echo ' checked="checked"'; ?> >
                        <label class="form-check-label" for="lotw"><?= __("LoTW") ?></label>
                    </div>
                    <div class="form-check-inline">
                        <input class="form-check-input" type="checkbox" name="eqsl" value="1" id="eqsl" <?php if ($this->input->post('eqsl')) echo ' checked="checked"'; ?> >
                        <label class="form-check-label" for="eqsl"><?= __("eQSL") ?></label>
                    </div>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-md-1 control-label" for="button1id"></label>
                <div class="col-md-10">
                    <button id="button1id" type="submit" name="button1id" class="btn btn-primary"><?= __("Show") ?></button>
                </div>
            </div>

    </form>

    <?php 
    // Get Date format
    if($this->session->userdata('user_date_format')) {
        // If Logged in and session exists
        $custom_date_format = $this->session->userdata('user_date_format');
    } else {
        // Get Default date format from /config/wavelog.php
        $custom_date_format = $this->config->item('qso_date_format');
    }
    ?>

    <?php

    if ($timeline_array) {
        switch ($this->input->post('award')) {
            case 'dxcc': $result = write_dxcc_timeline($timeline_array, $custom_date_format, $bandselect, $modeselect, $this->input->post('award')); break;
            case 'was':  $result = write_was_timeline($timeline_array, $custom_date_format, $bandselect, $modeselect, $this->input->post('award')); break;
            case 'iota': $result = write_iota_timeline($timeline_array, $custom_date_format, $bandselect, $modeselect, $this->input->post('award')); break;
            case 'waz':  $result = write_waz_timeline($timeline_array, $custom_date_format, $bandselect, $modeselect, $this->input->post('award')); break;
            case 'vucc':  $result = write_vucc_timeline($timeline_array, $custom_date_format, $bandselect, $modeselect, $this->input->post('award')); break;
        }
    }
    else {
        echo '<div class="alert alert-danger" role="alert">' . __("Nothing found!") . '</div>';
    }
    ?>

</div>

<?php

function write_dxcc_timeline($timeline_array, $custom_date_format, $bandselect, $modeselect, $award) {
    $i = count($timeline_array);
    echo '<table style="width:100%" class="table table-sm timelinetable table-bordered table-hover table-striped table-condensed text-center">
              <thead>
                    <tr>
                        <td>#</td>
                        <td>'.__("Date").'</td>
                        <td>'.__("Prefix").'</td>
                        <td>'.__("Country").'</td>
                        <td>'.__("Status").'</td>
                        <td>'.__("End Date").'</td>
                        <td>'.__("Show QSO's").'</td>
                    </tr>
                </thead>
                <tbody>';

    foreach ($timeline_array as $line) {
        $date_as_timestamp = strtotime($line->date);
        echo '<tr>
                <td>' . $i-- . '</td>
                <td>' . date($custom_date_format, $date_as_timestamp) . '</td>
                <td>' . $line->prefix . '</td>
                <td>' . $line->col_country . '</td>
                <td>';
        if (!empty($line->end)) echo '<span class="badge text-bg-danger">'.__("Deleted DXCC").'</span>';
        echo '</td>
                <td>' . $line->end . '</td>
                <td><a href=javascript:displayTimelineContacts("' . $line->adif . '","'. $bandselect . '","'. $modeselect . '","' . $award .'")>'.__("Show").'</a></td>
               </tr>';
    }
    echo '</tfoot></table></div>';
}

function write_was_timeline($timeline_array, $custom_date_format, $bandselect, $modeselect, $award) {
    $i = count($timeline_array);
    echo '<table style="width:100%" class="table table-sm timelinetable table-bordered table-hover table-striped table-condensed text-center">
              <thead>
                    <tr>
                        <td>#</td>
                        <td>'.__("Date").'</td>
                        <td>'.__("State").'</td>
                        <td>'.__("Show QSO's").'</td>
                    </tr>
                </thead>
                <tbody>';

    foreach ($timeline_array as $line) {
        $date_as_timestamp = strtotime($line->date);
        echo '<tr>
                <td>' . $i-- . '</td>
                <td>' . date($custom_date_format, $date_as_timestamp) . '</td>
                <td>' . $line->col_state . '</td>
                <td><a href=javascript:displayTimelineContacts("' . $line->col_state . '","'. $bandselect . '","'. $modeselect . '","' . $award .'")>'.__("Show").'</a></td>
               </tr>';
    }
    echo '</tfoot></table></div>';
}

function write_iota_timeline($timeline_array, $custom_date_format, $bandselect, $modeselect, $award) {
    $i = count($timeline_array);
    echo '<table style="width:100%" class="table table-sm timelinetable table-bordered table-hover table-striped table-condensed text-center">
              <thead>
                    <tr>
                        <td>#</td>
                        <td>'.__("Date").'</td>
                        <td>'.__("IOTA").'</td>
                        <td>'.__("Name").'</td>
                        <td>'.__("Prefix").'</td>
                        <td>'.__("Show QSO's").'</td>
                    </tr>
                </thead>
                <tbody>';

    foreach ($timeline_array as $line) {
        $date_as_timestamp = strtotime($line->date);
        echo '<tr>
                <td>' . $i-- . '</td>
                <td>' . date($custom_date_format, $date_as_timestamp) . '</td>
                <td>' . $line->col_iota . '</td>
                <td>' . $line->name . '</td>
                <td>' . $line->prefix . '</td>
                <td><a href=javascript:displayTimelineContacts("' . $line->col_iota . '","'. $bandselect . '","'. $modeselect . '","' . $award .'")>'.__("Show").'</a></td>
               </tr>';
    }
    echo '</tfoot></table></div>';
}

function write_waz_timeline($timeline_array, $custom_date_format, $bandselect, $modeselect, $award) {
    $i = count($timeline_array);
    echo '<table style="width:100%" class="table table-sm timelinetable table-bordered table-hover table-striped table-condensed text-center">
              <thead>
                    <tr>
                        <td>#</td>
                        <td>'.__("Date").'</td>
                        <td>'.__("CQ Zone").'</td>
                        <td>'.__("Show QSO's").'</td>
                    </tr>
                </thead>
                <tbody>';

    foreach ($timeline_array as $line) {
        $date_as_timestamp = strtotime($line->date);
        echo '<tr>
                <td>' . $i-- . '</td>
                <td>' . date($custom_date_format, $date_as_timestamp) . '</td>
                <td>' . $line->col_cqz . '</td>
                <td><a href=javascript:displayTimelineContacts("' . $line->col_cqz . '","'. $bandselect . '","'. $modeselect . '","' . $award .'")>'.__("Show").'</a></td>
               </tr>';
    }
    echo '</tfoot></table></div>';
}

function write_vucc_timeline($timeline_array, $custom_date_format, $bandselect, $modeselect, $award) {
    $i = count($timeline_array);
    echo '<table style="width:100%" class="table table-sm timelinetable table-bordered table-hover table-striped table-condensed text-center">
              <thead>
                    <tr>
                        <td>#</td>
                        <td>'.__("Date").'</td>
                        <td>'.__("Time").'</td>
                        <td>'.__("Gridsquare").'</td>
                        <td>'.__("Show QSO's").'</td>
                    </tr>
                </thead>
                <tbody>';

    foreach ($timeline_array as $line) {
        $date_as_timestamp = strtotime($line['date']);
        echo '<tr>
                <td>' . $i-- . '</td>
                <td>' . date($custom_date_format, $date_as_timestamp) . '</td>
                <td>' . date('H:i', $date_as_timestamp) . '</td>
                <td>' . $line['gridsquare'] . '</td>
                <td><a href=javascript:displayTimelineContacts("' . $line['gridsquare'] . '","'. $bandselect . '","'. $modeselect . '","' . $award .'")>'.__("Show").'</a></td>
               </tr>';
    }
    echo '</tfoot></table></div>';
}
