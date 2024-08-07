<script>
	$(function() {
		$( "#start_date" ).datepicker({ dateFormat: "yy-mm-dd" });
		$( "#end_date" ).datepicker({ dateFormat: "yy-mm-dd" });
	});
</script>

<div class="container statistics_custom">

<h2>
  <?php echo $page_title; ?>
  <small class="text-muted"><?= __("Explore the logbook."); ?></small>
</h2>


<ul class="nav nav-tabs" id="myTab" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" id="home-tab" data-bs-toggle="tab" href="/index.php/statistics" role="tab" aria-controls="home" aria-selected="true"><?= __("General"); ?></a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="satellite-tab" data-bs-toggle="tab" href="/index.php/statistics#space" role="tab" aria-controls="satellite" aria-selected="false"><?= __("Satellites"); ?></a>
  </li>
  <li class="nav-item">
    <a href="/index.php/statistics/custom" class="nav-link" role="tab"><?= __("Custom"); ?></a>
  </li>
</ul>

	
		<p><?= __("This is a work in-progress"); ?></p>
		
		<div id="filter_box">
		
			<h2><?= __("Options"); ?></h2>
			
			<?php echo validation_errors(); ?>

			<?php echo form_open('statistics/custom'); ?>
		
			<div class="type">
				<h3><?= __("Date"); ?></h3>
				<table>
					<tr>
						<td><?= __("Start"); ?></td>
						<td><input type="text" id="start_date" name="start_date" value="" autocomplete="off"/></td>
					</tr>
					
					<tr>
						<td><?= __("End"); ?></td>
						<td><input type="text" id="end_date" name="end_date" value="" autocomplete="off"/></td>
					</tr>
				</table>
			</div>
			
			<div class="type">
				<h3><?= __("Band"); ?></h3>
				<input type="checkbox" name="band_6m" value="6m" /> 6m
				<input type="checkbox" name="band_2m" value="2m" /> 2m
				<input type="checkbox" name="band_70cm" value="70cm" /> 70cm
				<input type="checkbox" name="band_23cm" value="23cm" /> 23cm
				<input type="checkbox" name="band_3cm" value="3cm" /> 3cm
				
				<h3><?= __("Mode"); ?></h3>
					<input type="checkbox" name="mode_ssb" value="ssb" /> SSB
					<input type="checkbox" name="mode_cw" value="cw" /> CW
					<input type="checkbox" name="mode_data" value="data" /> Data
					<input type="checkbox" name="mode_fm" value="FM" /> FM
					<input type="checkbox" name="mode_am" value="AM" /> AM
			</div>
			
			<div class="type">
				<p><?= __("Finished your selection? time to search!"); ?></p>
				<input type="submit" class="btn primary" name="submit" value="Search" />		
			</div>
			
			<div class="clear"></div>
		
			
			</form>
		</div>


	<div class="results">
			<table width="100%">
		<tr class="titles">
			<td><?= __("Date"); ?></td>
			<td><?= __("Time"); ?></td>
			<td><?= __("Call"); ?></td>
			<td><?= __("Mode"); ?></td>
			<td><?= __("Sent"); ?></td>
			<td><?= __("Rcvd"); ?></td>
			<td><?= __("Band"); ?></td>
			<td><?= __("Country"); ?></td>
		</tr>
		
		<?php  $i = 0;  foreach ($result->result() as $row) { ?>
			<?php  echo '<tr class="tr'.($i & 1).'">'; ?>
			<td><?php $timestamp = strtotime($row->COL_TIME_ON); echo date('d/m/y', $timestamp); ?></td>
			<td><?php $timestamp = strtotime($row->COL_TIME_ON); echo date('H:i', $timestamp); ?></td>
			<td><a class="qsobox" href="<?php echo site_url('logbook/view')."/".$row->COL_PRIMARY_KEY; ?>"><?php echo strtoupper($row->COL_CALL); ?></a></td>
			<td><?php echo $row->COL_MODE; ?></td>
			<td><?php echo $row->COL_RST_SENT; ?></td>
			<td><?php echo $row->COL_RST_RCVD; ?></td>
			<?php if($row->COL_SAT_NAME != null) { ?>
			<td><?php echo $row->COL_SAT_NAME; ?></td>
			<?php } else { ?>
			<td><?php echo $row->COL_BAND; ?></td>
			<?php } ?>
			<td><?php echo $row->COL_COUNTRY; ?></td>
		</tr>
		<?php $i++; } ?>
		
	</table>
	</div>

</div>
