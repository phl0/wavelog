<?php
if ($filtered) {
	echo '<table style="width:100%" class="table-sm table table-bordered table-hover table-striped table-condensed text-center">
				<tr id="toptable">
					<th>' . __("Satellite") . '</th>
					<th>' . __("AOS Time") . '</th>
					<th>' . __("Duration") . '</th>
					<th>' . __("AOS Az") . '</th>
					<th>' . __("AOS El") . '</th>
					<th>' . __("Max El") . '</th>
					<th>' . __("LOS Time") . '</th>
					<th>' . __("LOS Az") . '</th>
					<th>' . __("LOS El") . '</th>
				</tr>';
			foreach ($filtered as $pass) {
				echo '<tr>';
				echo '<td>' . $pass->satname . '</td>';
				echo '<td>' . Predict_Time::daynum2readable($pass->visible_aos, $zone, $format) . '</td>';
				echo '<td>' . returntimediff(Predict_Time::daynum2readable($pass->visible_aos, $zone, $format), Predict_Time::daynum2readable($pass->visible_los, $zone, $format), $format) . '</td>';
				echo '<td>' . round($pass->visible_aos_az) . ' (' . azDegreesToDirection($pass->visible_aos_az) . ')</td>';
				echo '<td>' . round($pass->visible_aos_el) . '</td>';
				echo '<td>' . round($pass->max_el) . '</td>';
				echo '<td>' . Predict_Time::daynum2readable($pass->visible_los, $zone, $format) . '</td>';
				echo '<td>' . round($pass->visible_los_az) . ' (' . azDegreesToDirection($pass->visible_los_az) . ')</td>';
				echo '<td>' . round($pass->visible_los_el) . '</td>';
				echo '</tr>';
			}
			echo '</table>';
}

function returntimediff($start, $end, $format) {
	$datetime1 = DateTime::createFromFormat($format, $end);
	$datetime2 = DateTime::createFromFormat($format, $start);
	$interval = $datetime1->diff($datetime2);

	$minutesDifference = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i + ($interval->s / 60);

	return round($minutesDifference) . ' min';
}

function azDegreesToDirection($az = 0) {
	$i = floor($az / 22.5);
	$m = (22.5 * (2 * $i + 1)) / 2;
	$i = ($az >= $m) ? $i + 1 : $i;

	return trim(substr('N  NNENE ENEE  ESESE SSES  SSWSW WSWW  WNWNW NNWN  ', $i * 3, 3));
}
