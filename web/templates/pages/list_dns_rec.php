<?php
$domain = $_GET["domain"] ?? "";
$rec_total = is_array($data) ? count($data) : 0;
$rec_types = [];
if (is_array($data)) {
	foreach ($data as $d) {
		$t = strtoupper($d["TYPE"] ?? "");
		if ($t !== "") {
			$rec_types[$t] = ($rec_types[$t] ?? 0) + 1;
		}
	}
}
$common_types = ["A", "AAAA", "CNAME", "MX", "TXT", "NS"];
$shown_types = [];
foreach ($common_types as $t) {
	if (!empty($rec_types[$t])) {
		$shown_types[] = $t;
	}
}
?>
<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/dns/">
				<i class="fas fa-arrow-left"></i><?= tohtml(_("Back")) ?>
			</a>
			<?php if ($read_only !== "true") { ?>
				<a href="/add/dns/?<?= tohtml(http_build_query(["domain" => $domain])) ?>" class="button js-button-create">
					<i class="fas fa-plus"></i><?= tohtml(_("Add Record")) ?>
				</a>
				<a href="/edit/dns/?<?= tohtml(http_build_query(["domain" => $domain])) ?>" class="button button-secondary js-button-create">
					<i class="fas fa-pencil"></i><?= tohtml(_("Edit DNS Domain")) ?>
				</a>
			<?php } ?>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<button class="toolbar-sorting-toggle js-toggle-sorting-menu" type="button" title="<?= tohtml(_("Sort items")) ?>">
					<?= tohtml(_("Sort by")) ?>:
					<span class="u-text-bold">
						<?php if ($_SESSION["userSortOrder"] === "name") {
      	$label = _("Record");
      } else {
      	$label = _("Date");
      } ?>
						<?= tohtml($label) ?> <i class="fas fa-arrow-down-a-z"></i>
					</span>
				</button>
				<ul class="toolbar-sorting-menu js-sorting-menu u-hidden">
					<li data-entity="sort-date" data-sort-as-int="1">
						<span class="name <?php if ($_SESSION["userSortOrder"] === "date") {
      	echo "active";
      } ?>"><?= tohtml(_("Date")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-value">
						<span class="name"><?= tohtml(_("IP or Value")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-record">
						<span class="name"><?= tohtml(_("Record")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-ttl" data-sort-as-int="1">
						<span class="name"><?= tohtml(_("TTL")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-type">
						<span class="name"><?= tohtml(_("Type")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
				</ul>
				<?php if ($read_only !== "true") { ?>
					<form x-data x-bind="BulkEdit" action="/bulk/dns/" method="post">
						<input type="hidden" name="domain" value="<?= tohtml($domain) ?>">
						<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
						<select class="form-select" name="action">
							<option value=""><?= tohtml(_("Apply to selected")) ?></option>
							<option value="suspend"><?= tohtml(_("Suspend")) ?></option>
							<option value="unsuspend"><?= tohtml(_("Unsuspend")) ?></option>
							<option value="delete"><?= tohtml(_("Delete")) ?></option>
						</select>
						<button type="submit" class="toolbar-input-submit" title="<?= tohtml(_("Apply to selected")) ?>">
							<i class="fas fa-arrow-right"></i>
						</button>
					</form>
				<?php } ?>
			</div>
			<div class="toolbar-search">
				<form action="/search/" method="get">
					<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
					<input type="search" class="form-control js-search-input js-vz-live-filter" name="q" value="<?= tohtml(
     	$_GET["q"] ?? "",
     ) ?>" placeholder="<?= tohtml(_("Filter by name or type…")) ?>" title="<?= tohtml(_("Search")) ?>" autocomplete="off">
					<button type="submit" class="toolbar-input-submit" title="<?= tohtml(_("Search")) ?>">
						<i class="fas fa-magnifying-glass"></i>
					</button>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container" x-data="vzDnsRecList">

	<div class="vz-page-hero">
		<div>
			<h1 class="vz-page-title"><?= tohtml(_("DNS Records")) ?></h1>
			<p class="vz-page-subtitle"><?= tohtml(sprintf(_("Records for %s"), $domain)) ?></p>
		</div>
		<div class="vz-stat-pills">
			<span class="vz-stat-pill"><strong><?= (int) $rec_total ?></strong> <?= tohtml(_("records")) ?></span>
		</div>
	</div>

	<div class="vz-filter-bar">
		<button type="button" class="vz-chip" :class="filter === 'all' && 'active'" @click="filter = 'all'">
			<?= tohtml(_("All")) ?>
		</button>
		<?php foreach ($shown_types as $t) {
  	$tf = strtolower($t); ?>
			<button type="button" class="vz-chip" :class="filter === '<?= tohtml($tf) ?>' && 'active'" @click="filter = '<?= tohtml(
 	$tf,
 ) ?>'">
				<?= tohtml($t) ?>
			</button>
		<?php
  } ?>
		<span class="vz-filter-count" x-text="visibleCountLabel"></span>
	</div>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell">
				<input type="checkbox" class="js-toggle-all-checkbox" title="<?= tohtml(_("Select all")) ?>"<?= $display_mode ===
"disabled"
	? " disabled"
	: "" ?>>
			</div>
			<div class="units-table-cell"><?= tohtml(_("Record")) ?></div>
			<div class="units-table-cell"></div>
			<div class="units-table-cell u-text-center"><?= tohtml(_("Type")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml(_("Priority")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml(_("TTL")) ?></div>
			<div class="units-table-cell"><?= tohtml(_("IP or Value")) ?></div>
		</div>

		<!-- Begin DNS record list item loop -->
		<?php
  $i = 0;
  foreach ($data as $key => $value) {
  	++$i;
  	if ($data[$key]["SUSPENDED"] == "yes") {
  		$status = "suspended";
  	} else {
  		$status = "active";
  	}
  	$row_type = strtolower($data[$key]["TYPE"] ?? "");
  	$row_name = strtolower($data[$key]["RECORD"] ?? "");
  	?>
				<div class="units-table-row <?php if ($status == "suspended") {
    	echo "disabled";
    } ?> js-unit vz-filter-row"
					data-sort-date="<?= tohtml(strtotime($data[$key]["DATE"] . " " . $data[$key]["TIME"])) ?>"
					data-sort-record="<?= tohtml($data[$key]["RECORD"]) ?>"
					data-sort-type="<?= tohtml($data[$key]["TYPE"]) ?>"
					data-sort-ttl="<?= tohtml($data[$key]["TTL"]) ?>"
					data-sort-value="<?= tohtml($data[$key]["VALUE"]) ?>"
					data-status="<?= tohtml($status) ?>"
					data-name="<?= tohtml($row_name) ?>"
					data-type="<?= tohtml($row_type) ?>"
					x-show="isVisible($el)"
					x-transition.opacity
				>
					<div class="units-table-cell">
						<div>
							<input id="check<?= tohtml($data[$key]["ID"]) ?>" class="js-unit-checkbox" type="checkbox" title="<?= tohtml(
	_("Select"),
) ?>" name="record[]" value="<?= tohtml($data[$key]["ID"]) ?>"<?= $display_mode === "disabled" ? " disabled" : "" ?>>
							<label for="check<?= tohtml($data[$key]["ID"]) ?>" class="u-hide-desktop"><?= tohtml(_("Select")) ?></label>
						</div>
					</div>
					<div class="units-table-cell units-table-heading-cell u-text-bold">
						<span class="u-hide-desktop"><?= tohtml(_("Record")) ?>:</span>
							<?php if ($read_only === "true" || $data[$key]["SUSPENDED"] == "yes") { ?>
								<?= tohtml(substr($data[$key]["RECORD"], 0, 12)) ?><?php if (strlen($data[$key]["RECORD"]) > 12) {
 	echo "...";
 } ?>
							<?php } else { ?>
								<a href="/edit/dns/?<?= tohtml(
        	http_build_query([
        		"domain" => $domain,
        		"record_id" => $data[$key]["ID"],
        		"token" => $_SESSION["token"],
        	]),
        ) ?>" title="<?= tohtml(_("Edit DNS Record") . ": " . $data[$key]["RECORD"]) ?>">
									<?= tohtml(substr($data[$key]["RECORD"], 0, 12)) ?><?php if (strlen($data[$key]["RECORD"]) > 12) {
 	echo "...";
 } ?>
								</a>
							<?php } ?>
						</div>
					<div class="units-table-cell">
						<?php if ($read_only !== "true") { ?>
						<ul class="units-table-row-actions">
							<?php if ($read_only !== "true") { ?>
									<?php if ($data[$key]["SUSPENDED"] == "no") { ?>
										<li class="units-table-row-action shortcut-enter" data-key-action="href">
											<a
												class="units-table-row-action-link"
												href="/edit/dns/?<?= tohtml(
            	http_build_query([
            		"domain" => $domain,
            		"record_id" => $data[$key]["ID"],
            		"token" => $_SESSION["token"],
            	]),
            ) ?>"
												title="<?= tohtml(_("Edit DNS Record")) ?>"
											>
												<i class="fas fa-pencil icon-orange"></i>
												<span class="u-hide-desktop"><?= tohtml(_("Edit DNS Record")) ?></span>
											</a>
									</li>
								<?php } ?>
								<li class="units-table-row-action shortcut-delete" data-key-action="js">
									<a
										class="units-table-row-action-link data-controls js-confirm-action"
										href="/delete/dns/?<?= tohtml(
          	http_build_query([
          		"domain" => $domain,
          		"record_id" => $data[$key]["ID"],
          		"token" => $_SESSION["token"],
          	]),
          ) ?>"
										title="<?= tohtml(_("Delete")) ?>"
										data-confirm-title="<?= tohtml(_("Delete")) ?>"
										data-confirm-message="<?= tohtml(sprintf(_("Are you sure you want to delete record %s?"), $key)) ?>"
									>
										<i class="fas fa-trash icon-red"></i>
										<span class="u-hide-desktop"><?= tohtml(_("Delete")) ?></span>
									</a>
								</li>
							<?php } ?>
						</ul>
					<?php } ?>
					</div>
					<div class="units-table-cell u-text-bold u-text-center-desktop">
						<span class="u-hide-desktop"><?= tohtml(_("Type")) ?>:</span>
						<span class="vz-badge vz-badge-info"><?= tohtml($data[$key]["TYPE"]) ?></span>
					</div>
					<div class="units-table-cell u-text-center-desktop">
						<span class="u-hide-desktop u-text-bold"><?= tohtml(_("Priority")) ?>:</span>
						<?= tohtml($data[$key]["PRIORITY"]) ?>
					</div>
					<div class="units-table-cell u-text-center-desktop">
						<span class="u-hide-desktop u-text-bold"><?= tohtml(_("TTL")) ?>:</span>
						<?php if ($data[$key]["TTL"] == "") {
      	echo tohtml(_("Default"));
      } else {
      	echo tohtml($data[$key]["TTL"]);
      } ?>
					</div>
					<div class="units-table-cell">
						<span class="u-hide-desktop u-text-bold"><?= tohtml(_("IP or Value")) ?>:</span>
						<span class="u-text-break">
							<?= tohtml($data[$key]["VALUE"]) ?>
						</span>
					</div>
				</div>
			<?php
  }
  ?>
	</div>

	<div class="units-table-footer">
		<p>
			<?php printf(ngettext("%d DNS record", "%d DNS records", $i), $i); ?>
		</p>
	</div>

</div>
