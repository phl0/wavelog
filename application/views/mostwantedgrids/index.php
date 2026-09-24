<style>
	/* Force left alignment for Bootstrap Multiselect button */
	.multiselect.dropdown-toggle {
		text-align: left !important;
	}

	.dropdown-filters-responsive {
		width: 900px;
	}

	@media (max-width: 992px) {
		.dropdown-filters-responsive {
			width: 90vw;
			max-width: none;
		}
	}
</style>
<script>
	let user_map_custom = JSON.parse('<?php echo $user_map_custom; ?>');
</script>
<div class="container gridsquare_map_form px-3 px-lg-4 mt-3 mb-3">

    <h2><?php echo $page_title; ?></h2>

	<div class="card">
		<div class="card-header">
			<?= __("View a map of most wanted gridsquares on satellite"); ?>
		</div>
		<div class="card-body">

			<?php if ($this->session->flashdata('message')) { ?>
				<div class="alert-message error">
					<p><?php echo $this->session->flashdata('message'); ?></p>
				</div>
			<?php } ?>

		</div> <!-- /card-body -->

		<div id="gridmapcontainer">
			<div id="gridsquare_map" class="map-leaflet" style="width: 100%;"></div>
		</div>

		<div class="card-body">
			<div class="coordinates" style="position: static;">
				<div class="cohidden coord-pair"><span><?= __("Latitude") ?>:&nbsp;</span><span class="text-success fw-bold" id="latDeg"></span></div>
				<div class="cohidden coord-pair"><span><?= __("Longitude") ?>:&nbsp;</span><span class="text-success fw-bold" id="lngDeg"></span></div>
				<div class="cohidden coord-pair"><span><?= __("Gridsquare") ?>:&nbsp;</span><span class="text-success fw-bold" id="locator"></span></div>
				<div class="cohidden coord-pair"><span><?= __("Distance") ?>:&nbsp;</span><span class="text-success fw-bold" id="distance"></span></div>
				<div class="cohidden coord-pair"><span><?= __("Bearing") ?>:&nbsp;</span><span class="text-success fw-bold" id="bearing"></span></div>
				<div class="cohidden coord-pair"><span><?= __("CQ Zone") ?>:&nbsp;</span><span class="text-success fw-bold" id="cqzonedisplay"></span></div>
				<div class="cohidden coord-pair"><span><?= __("ITU Zone") ?>:&nbsp;</span><span class="text-success fw-bold" id="ituzonedisplay"></span></div>
			</div>
		</div>

		<script>
			<?php
				echo "var homegrid = \"" . strtoupper($homegrid[0]) . "\";\n";
			?>
			var gridsquaremap = true;
			var type = "worked";
			<?php
			echo "var jslayer = \"" . $layer . "\";\n";
			echo "var jsattribution ='" . $attribution . "';";

			?>
		</script>
	</div> <!-- /card -->
</div> <!-- /container -->
