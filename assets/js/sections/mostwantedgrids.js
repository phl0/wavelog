var modalloading=false;
var maxPerc = 0;

let confirmedColor = 'rgba(144,238,144)';
if (typeof(user_map_custom.qsoconfirm) !== 'undefined') {
      confirmedColor = user_map_custom.qsoconfirm.color;
}
let workedColor = 'rgba(229, 165, 10)';
if (typeof(user_map_custom.qso) !== 'undefined') {
      workedColor = user_map_custom.qso.color;
}

document.addEventListener("DOMContentLoaded", function() {
  document.querySelectorAll('.dropdown').forEach(dd => {
		dd.addEventListener('hide.bs.dropdown', function (e) {
			if (e.clickEvent && e.clickEvent.target.closest('.dropdown-menu')) {
				e.preventDefault(); // stop Bootstrap from closing
			}
		});
	});
});

var map;
var maidenhead;
var percFilterMin = 0;
var percFilterMax = 100;
var grid_four = '';
var grid_four_confirmed = '';

function gridPlot(form) {
    // If map is already initialized
    var container = L.DomUtil.get('gridsquare_map');

    if(container != null){
        container._leaflet_id = null;
        container.remove();
        $("#gridmapcontainer").append('<div id="gridsquare_map" class="map-leaflet" style="width: 100%;"></div>');
        set_map_height(50);
    }
    $.ajax({
       url: site_url+'/mostwantedgrids/getGridsjs',
       type: 'get',
       success: function(data) {
          mwgrids = data.grid_4char;
          plot();
       },
       error: function (data) {
       },
    });
}

function plot() {
            let layer = L.tileLayer(jslayer, {
                maxZoom: 12,
                attribution: jsattribution,
                id: 'mapbox.streets'
            });

            let mapCenter = [19, 0];
            let mapZoom = 3;
            for (key in mwgrids) {
                if (mwgrids[key] > maxPerc) {
                    maxPerc = mwgrids[key];
                }
            }

            map = L.map('gridsquare_map', {
            layers: [layer],
            center: mapCenter,
            zoom: mapZoom,
            minZoom: 2,
            fullscreenControl: true,
                fullscreenControlOptions: {
                    position: 'topleft'
                },
            });

            maidenhead = L.maidenhead().addTo(map);
            map.on('mousemove', onMapMove);
            map.on('click', onMapClick);
}

function spawnGridsquareModal(loc_4char) {
	if (!(modalloading)) {
		var ajax_data = ({
			'Searchphrase': loc_4char,
			'Band': 'SAT',
			'Mode': 'All',
			'Sat': 'All',
			'Orbit': 'All',
			'Propagation': 'SAT',
			'Type': 'VUCC',
		})
		modalloading=true;
		$.ajax({
			url: base_url + 'index.php/awards/qso_details_ajax',
			type: 'post',
			data: ajax_data,
			success: function (html) {
		    		var dialog = new BootstrapDialog({
					title: lang_general_word_qso_data,
					cssClass: 'qso-dialog',
					size: BootstrapDialog.SIZE_WIDE,
					nl2br: false,
					message: html,
					onshown: function(dialog) {
						modalloading=false;
						$('[data-bs-toggle="tooltip"]').tooltip();
						$('.displaycontactstable').DataTable({
							"pageLength": 25,
							responsive: false,
							ordering: false,
							"scrollY":        "550px",
							"scrollCollapse": true,
							"paging":         false,
							"scrollX": true,
                            "language": {
                                url: getDataTablesLanguageUrl(),
                            },
							dom: 'Bfrtip',
							buttons: [
								'csv'
							]
						});
						// change color of csv-button if dark mode is chosen
						if (isDarkModeTheme()) {
							$(".buttons-csv").css("color", "white");
						}
                        $('.table-responsive .dropdown-toggle').off('mouseenter').on('mouseenter', function () {
                            showQsoActionsMenu($(this).closest('.dropdown'));
                        });
					},
                    onhide: function(dialog) {
                        enableMap();
                    },
					buttons: [{
						label: lang_admin_close,
						action: function(dialogItself) {
							dialogItself.close();
						}
					}]
				});
			    dialog.realize();
                    $('#gridsquare_map').append(dialog.getModal());
                    disableMap();
		    		dialog.open();
                },
			error: function(e) {
				modalloading=false;
			}
		});
	}
}

function hexToRgba(hex, alpha = 1) {
	if (!hex) return null;
	// Remove the leading "#"
	hex = hex.replace(/^#/, '');

	// Expand short form (#f0a → #ff00aa)
	if (hex.length === 3) {
		hex = hex.split('').map(c => c + c).join('');
	}

	const num = parseInt(hex, 16);
	const r = (num >> 16) & 255;
	const g = (num >> 8) & 255;
	const b = num & 255;

	return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

function colorGradient(color1, color2, percent) {
	const scaleMax = Math.min(percFilterMax, maxPerc);
	const f = scaleMax > percFilterMin ? Math.max(0, Math.min(1, (percent - percFilterMin) / (scaleMax - percFilterMin))) : 0;

	function parseToRgba(colorStr) {
		const match = colorStr.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d.]+))?\)/);
		if (!match) return [0, 0, 0, 1];

		return [
			parseInt(match[1], 10),
			parseInt(match[2], 10),
			parseInt(match[3], 10),
			match[4] !== undefined ? parseFloat(match[4]) : 1
		];
	}

	const [r1, g1, b1, a1] = parseToRgba(color1);
	const [r2, g2, b2, a2] = parseToRgba(color2);

	const r = Math.round(r1 + (r2 - r1) * f);
	const g = Math.round(g1 + (g2 - g1) * f);
	const b = Math.round(b1 + (b2 - b1) * f);
	const a = parseFloat((a1 + (a2 - a1)).toFixed(3));

	return `rgba(${r}, ${g}, ${b}, ${a})`;
}

$(document).ready(function(){
	gridPlot(this.form);
	$(window).resize(function () {
		set_map_height();
	});
	['perc_min', 'perc_max'].forEach(function(id) {
		document.getElementById(id).addEventListener('input', function() {
			var min = parseInt(document.getElementById('perc_min').value, 10);
			var max = parseInt(document.getElementById('perc_max').value, 10);
			if (min > max) {
				if (this.id === 'perc_min') {
					max = min;
					document.getElementById('perc_max').value = max;
				} else {
					min = max;
					document.getElementById('perc_min').value = min;
				}
			}
			percFilterMin = min;
			percFilterMax = max;
			document.getElementById('perc_min_val').textContent = min + '%';
			document.getElementById('perc_max_val').textContent = max + '%';
			var fill = document.getElementById('perc_fill');
			fill.style.left = min + '%';
			fill.style.right = (100 - max) + '%';
			if (maidenhead) {
				maidenhead.redraw();
			}
		});
	});
});
