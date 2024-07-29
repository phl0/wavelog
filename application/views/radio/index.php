<div class="container">

	<br>

		<?php if($this->session->flashdata('message')) { ?>
			<!-- Display Message -->
			<div class="alert-message error">
			  <p><?php echo $this->session->flashdata('message'); ?></p>
			</div>
		<?php } ?>

	<h2><?php echo $page_title; ?></h2>

	<div class="card">
	  <div class="card-header">
	    <?= __("Active Radios"); ?>
	  </div>
	  <div class="card-body">
	    <p class="card-text"><?= __("Below is a list of active radios that are connected to Wavelog."); ?></p>
	    <p class="card-text"><?= __("If you haven't connected any radios yet, see the API page to generate API keys."); ?></p>
	    <div class="table-responsive">
		    <!-- Display Radio Statuses -->
			<table class="table table-sm table-condensated table-striped status"></table>
		</div>

		<p class="card-text">
	    	<span class="badge text-bg-info"><?= __("Info"); ?></span> <?= sprintf(__("You can find out how to use the %s in the wiki."), '<a href="https://github.com/wavelog/wavelog/wiki/Radio-Interface" target="_blank">' . __("radio functions") . '</a>'); ?>
	    </p>

	  </div>
	</div>

</div>
