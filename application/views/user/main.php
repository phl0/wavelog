<script>
	var lang_admin_confirm_pwd_reset = "<?= __("Do you really want to send this user a password-reset link?"); ?>";
	var lang_admin_user = "<?= __("User"); ?>";
	var lang_gen_hamradio_callsign = "<?= __("Callsign"); ?>";

	var lang_general_word_please_wait = "<?= __("Please Wait ..."); ?>"

	var lang_admin_email_settings_incorrect = "<?= __("Email settings are incorrect."); ?>";
	var lang_admin_password_reset_processed = "<?= __("Password-reset e-mail sent to user:"); ?>";
</script>
<div class="container">

	<br>

	<h2><?php echo $page_title; ?></h2>

	<?php if ($this->session->flashdata('notice')) { ?>
		<!-- Display Message -->
		<div class="alert alert-info" role="alert">
			<?php echo $this->session->flashdata('notice'); ?>
		</div>

	<?php } ?>
	
	<!-- This Info will be shown by the admin password reset -->
	<div class="alert" id="pwd_reset_message" style="display: hide" role="alert"></div>

	<div class="card">
		<div class="card-header">
			<?= __("User List"); ?>
		</div>
		<div class="card-body">
			<p class="card-text"><?= __("Wavelog needs at least one user configured in order to operate."); ?></p>
			<p class="card-text"><?= __("Users can be assigned roles which give them different permissions, such as adding QSOs to the logbook and accessing Wavelog APIs."); ?></p>
			<p class="card-text"><?= __("The currently logged-in user is displayed at the upper-right of each page."); ?></p>
			<p class="card-text"><?= __("With the password reset button, you can send a user an email containing a link to reset their password. To achieve this, ensure that the email settings in the global options are configured correctly."); ?></p>
			<p>
				<a class="btn btn-primary" href="<?php echo site_url('user/add'); ?>"><i class="fas fa-user-plus"></i> <?= __("Create user"); ?></a>
				<a class="btn btn-secondary" style="float: right;" href="<?php echo site_url('user'); ?>"><i class="fas fa-sync"></i> <?= __("Refresh List"); ?></a>
			</p>

			<div class="table-responsive">
				<table class="table table-striped" id="adminusertable">
					<thead>
						<tr>
							<th style="text-align: center; vertical-align: middle;" scope="col"><?= __("User"); ?></th>
							<th style="text-align: center; vertical-align: middle;" scope="col"><?= __("Callsign"); ?></th>
							<th style="text-align: center; vertical-align: middle;" scope="col"><?= __("E-mail"); ?></th>
							<th style="text-align: center; vertical-align: middle;" scope="col"><?= __("Type"); ?></th>
							<th style="text-align: center; vertical-align: middle;" scope="col"><?= __("Last seen"); ?></th>
							<th></th>
							<th style="text-align: center; vertical-align: middle;" scope="col"><?= __("Edit"); ?></th>
							<th style="text-align: center; vertical-align: middle;" scope="col"><?= __("Password Reset"); ?></th>
							<th style="text-align: center; vertical-align: middle;" scope="col"><?= __("Delete"); ?></th>
						</tr>
					</thead>
					<tbody>

						<?php

						$i = 0;
						foreach ($results->result() as $row) { ?>
							<?php echo '<tr class="tr' . ($i & 1) . '">'; ?>
							<td style="text-align: left; vertical-align: middle;"><a href="<?php echo site_url('user/edit') . "/" . $row->user_id; ?>"><?php echo $row->user_name; ?></a></td>
							<td style="text-align: left; vertical-align: middle;"><?php echo $row->user_callsign; ?></td>
							<td style="text-align: left; vertical-align: middle;"><?php echo $row->user_email; ?></td>
							<td style="text-align: left; vertical-align: middle;"><?php $l = $this->config->item('auth_level');
								echo $l[$row->user_type]; ?></td>
							<td style="text-align: left; vertical-align: middle;"><?php 
								if ($row->last_seen != null) { // if the user never logged in before the value is null. We can show "never" then.
									$lastSeenTimestamp = strtotime($row->last_seen);
									$currentTimestamp = time();
									if (($currentTimestamp - $lastSeenTimestamp) < 120) {
										echo "<a><i style=\"color: green;\" class=\"fas fa-circle\"></i> " . $row->last_seen . "</a>";
									} else {
										echo "<a><i style=\"color: red;\" class=\"fas fa-circle\"></i> " . $row->last_seen . "</a>";
									}
								} else {
									echo __("Never");
								}?>
							</td>
							<td style="text-align: left; vertical-align: middle;">
								<span class="badge text-bg-success"><?= __("Locations"); ?>: <?php echo $row->stationcount; ?></span>
								<br>
								<span class="badge text-bg-info"><?= __("Logbooks"); ?>: <?php echo $row->logbookcount; ?></span>
								<?php if ($row->qsocount > 0) { ?>
									<span class="badge text-bg-light" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="<?= __("Last QSO:"); ?><br><?php echo $row->lastqso; ?>"><?php echo $row->qsocount; ?> <?= __("QSO"); ?></span>
								<?php } else { ?>
									<span class="badge text-bg-light" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="<?= __("No QSOs in Log"); ?>"><?php echo $row->qsocount; ?> <?= __("QSO"); ?></span>
								<?php } ?>
							</td>
							<td style="text-align: center; vertical-align: middle;"><a href="<?php echo site_url('user/edit') . "/" . $row->user_id; ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-user-edit"></i></a>
							<td style="text-align: center; vertical-align: middle;">
								<?php
								if ($_SESSION['user_id'] != $row->user_id) {
									echo '<a class="btn btn-primary btn-sm ms-1 admin_pwd_reset" data-username="' . $row->user_name . '" data-callsign="' . $row->user_callsign . '" data-userid="' . $row->user_id . '" data-usermail="' . $row->user_email . '"><i class="fas fa-key"></i></a>';
								}
								?></td>
							<td style="text-align: center; vertical-align: middle;">
								<?php
								if ($_SESSION['user_id'] != $row->user_id) {
									echo "<a href=" . site_url('user/delete') . "/" . $row->user_id . " class=\"btn btn-danger btn-sm\"><i class=\"fas fa-user-minus\"></i></a>";
								}
								?></td>
							</td>
							</tr>
						<?php $i++;
						} ?>
					</tbody>
				</table>
			</div>

		</div>
	</div>
</div>
