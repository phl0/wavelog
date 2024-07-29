<div class="container">

<br>
	<?php if($this->session->flashdata('message')) { ?>
		<!-- Display Message -->
		<div class="alert-message error">
		  <p><?php echo $this->session->flashdata('message'); ?></p>
		</div>
	<?php } ?>

<h2><?= __("Satellites"); ?></h2>

<div class="card">
  <div class="card-body">
  <button onclick="createSatelliteDialog();" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> <?= __("Add a satellite"); ?></button>
    <div class="table-responsive">

    <table style="width:100%" class="sattable table table-sm table-striped">
			<thead>
				<tr>
					<th><?= __("Name"); ?></th>
					<th><?= __("Export Name"); ?></th>
					<th><?= __("Orbit"); ?></th>
					<th><?= __("Mode"); ?></th>
					<th><?= __("Edit"); ?></th>
					<th><?= __("Delete"); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($satellites as $sat) { ?>
				<tr>
					<td style="text-align: center; vertical-align: middle;" class="satellite_<?php echo $sat->id ?>"><?php echo htmlentities($sat->satname) ?></td>
					<td style="text-align: center; vertical-align: middle;"><?php echo htmlentities($sat->exportname) ?></td>
					<?php echo '<td style="text-align: center; vertical-align: middle;"><span class="badge ';
					switch (strtoupper($sat->orbit)) {
					case 'LEO':
						echo 'bg-primary';
						break;
					case 'MEO':
						echo 'bg-info';
						break;
					case 'GEO':
						echo 'bg-warning';
						break;
					default:
						echo 'bg-light';
						break;
					}
						echo '">'.$sat->orbit.'</span></td>';
					?>
					<td style="text-align: center; vertical-align: middle;"><?php echo htmlentities($sat->modename ?? '') ?></td>
					<td style="text-align: center; vertical-align: middle;"><button onclick="editSatelliteDialog(<?php echo $sat->id ?>)" class="btn btn-sm btn-success"><i class="fas fa-edit"></i></i></button></td>
					<td style="text-align: center; vertical-align: middle;"><button onclick="deleteSatellite('<?php echo $sat->id . '\',\'' . xss_clean(htmlentities(str_replace('\'',"\\'",str_replace('"','\"',str_replace('\\',' ',$sat->satname))))) ?>')" class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i></button></td>
				</tr>

				<?php } ?>
			</tbody>
		<table>

	</div>
  <br/>
</div>
</div>
