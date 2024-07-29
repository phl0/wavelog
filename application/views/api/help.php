<div class="container">

<br>
<?php if($this->session->flashdata('notice')) { ?>
<div class="alert alert-success" role="alert">
	<?php echo $this->session->flashdata('notice'); ?>
</div>
<?php } ?>

<h2><?php echo $page_title; ?></h2>

<div class="card">
  <div class="card-header">
    <?= __("API Keys"); ?>
  </div>
  <div class="card-body">
	<p class="card-text"><?= __("The Wavelog API (Application Programming Interface) lets third party systems access Wavelog in a controlled way. Access to the API is managed via API keys."); ?></p>
	<p class="card-text"><?= __("You will need to generate an API key for each tool you wish to use (e.g. WLgate). Generate a read-write key if the application needs to send data to Wavelog. Generate a read-only key if the application only needs to obtain data from Wavelog."); ?></p>
   <p class="card-text"><span class="badge text-bg-warning"><?= __("API URL"); ?></span> <?= __("The API URL for this Wavelog instance is"); ?>: <span class="api-url" id="apiUrl"><a target="_blank" href="<?php echo base_url(); ?>"><?php echo base_url(); ?></a></span><span data-bs-toggle="tooltip" title="<?= __("Copy to clipboard"); ?>" onClick='copyApiUrl()'><i class="copy-icon fas fa-copy"></i></span></p>
	<p class="card-text"><span class="badge text-bg-info"><?= __("Info"); ?></span> <?= __("It's good practice to delete a key if you are no longer using the associated application."); ?></p>

		<?php if ($api_keys->num_rows() > 0) { ?>

		<table class="table table-striped">
		  <thead>
		    <tr>
		      <th scope="col"><?= __("API Key"); ?></th>
		      <th scope="col"><?= __("Description"); ?></th>
		      <th scope="col"><?= __("Last Used"); ?></th>
		      <th scope="col"><?= __("Permissions"); ?></th>
		      <th scope="col"><?= __("Status"); ?></th>
		      <th scope="col"><?= __("Actions"); ?></th>
		    </tr>
		  </thead>
		  <tbody>
			<?php foreach ($api_keys->result() as $row) { ?>
				<tr>
					<td><i class="fas fa-key"></i> <span class="api-key" id="<?php echo $row->key; ?>"><?php echo $row->key; ?></span> <span data-bs-toggle="tooltip" title="<?= __("Copy to clipboard"); ?>" onclick='copyApiKey("<?php echo $row->key; ?>")'><i class="copy-icon fas fa-copy"></span></td>
					<td><?php echo $row->description; ?></td>
					<td><?php echo $row->last_used; ?></td>
					<td>
						<?php
							
							if($row->rights == "rw") {
								echo "<span class=\"badge bg-warning\">" . __("Read & Write") . "</span>";
							} elseif($row->rights == "r") {
								echo "<span class=\"badge bg-success\">" . __("Read-Only") . "</span>";
							} else {
								echo "<span class=\"badge bg-dark\">" . __("Unknown") . "</span>";
							}
			
						?>

					</td>
					<td><span class="badge rounded-pill text-bg-success"><?php echo ucfirst($row->status); ?></span></td>
					<td>
						<a href="<?php echo site_url('api/edit'); ?>/<?php echo $row->key; ?>" class="btn btn-outline-primary btn-sm"><?= __("Edit"); ?></a>

						<a href="<?php echo site_url('api/auth/'.$row->key); ?>" target="_blank" class="btn btn-primary btn-sm"><?= __("Test"); ?></a>

						<a href="<?php echo site_url('api/delete/'.$row->key); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want delete API Key <?php echo $row->key; ?>?');"><?= __("Delete"); ?></a>
					</td>

				</tr>

			<?php } ?>

		</table>	

		<?php } else { ?>
			<p><?= __("You have no API Keys."); ?></p>
		<?php } ?>

		<p>
			<a href="<?php echo site_url('api/generate/rw'); ?>" class="btn btn-primary "><i class="fas fa-plus"></i> <?= __("Create a read & write key"); ?></a>
			<a href="<?php echo site_url('api/generate/r'); ?>" class="btn btn-primary"><i class="fas fa-plus"></i> <?= __("Create a read-only key"); ?></a>
		</p>

  </div>
</div>

</div>

