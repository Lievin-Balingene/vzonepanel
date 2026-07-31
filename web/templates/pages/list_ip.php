<?php
$ip_total = is_array($data) ? count($data) : 0;
$ip_shared = 0;
$ip_dedicated = 0;
if (is_array($data)) {
	foreach ($data as $d) {
		$status = strtolower($d["STATUS"] ?? "");
		if ($status === "shared") {
			$ip_shared++;
		} elseif ($status === "dedicated") {
			$ip_dedicated++;
		}
	}
}
?>
<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/edit/server/">
				<i class="fas fa-arrow-left"></i><?= tohtml(_("Back")) ?>
			</a>
			<a href="/add/ip/" class="button js-button-create">
				<i class="fas fa-plus"></i><?= tohtml(_("Add IP Address")) ?>
			</a>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<button class="toolbar-sorting-toggle js-toggle-sorting-menu" type="button" title="<?= tohtml(_("Sort items")) ?>">
					<?= tohtml(_("Sort by")) ?>:
					<span class="u-text-bold">
						<?= tohtml(_("Date")) ?> <i class="fas fa-arrow-down-a-z"></i>
					</span>
				</button>
				<ul class="toolbar-sorting-menu js-sorting-menu u-hidden">
					<li data-entity="sort-date" data-sort-as-int="1">
						<span class="name <?php if ($_SESSION["userSortOrder"] === "date") {
      	echo "active";
      } ?>"><?= tohtml(_("Date")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-ip">
						<span class="name"><?= tohtml(_("IP Address")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-netmask">
						<span class="name"><?= tohtml(_("Netmask")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-interface">
						<span class="name"><?= tohtml(_("Interface")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-domains" data-sort-as-int="1">
						<span class="name"><?= tohtml(_("Domains")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-owner">
						<span class="name"><?= tohtml(_("Owner")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
				</ul>
				<form x-data x-bind="BulkEdit" action="/bulk/ip/" method="post">
					<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
					<select class="form-select" name="action">
						<option value=""><?= tohtml(_("Apply to selected")) ?></option>
						<option value="reread IP"><?= tohtml(_("Refresh IP Addresses")) ?></option>
						<option value="delete"><?= tohtml(_("Delete")) ?></option>
					</select>
					<button type="submit" class="toolbar-input-submit" title="<?= tohtml(_("Apply to selected")) ?>">
						<i class="fas fa-arrow-right"></i>
					</button>
				</form>
			</div>
			<div class="toolbar-search">
				<input type="search" class="form-control js-vz-live-filter" placeholder="<?= tohtml(
    	_("Filter IPs…"),
    ) ?>" title="<?= tohtml(_("Search")) ?>" autocomplete="off">
			</div>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container" x-data="vzIpList">

	<div class="vz-page-hero">
		<div>
			<h1 class="vz-page-title"><?= tohtml(_("IP Addresses")) ?></h1>
			<p class="vz-page-subtitle"><?= tohtml(_("Manage server IP addresses and assignments")) ?></p>
		</div>
		<div class="vz-stat-pills">
			<span class="vz-stat-pill"><strong><?= (int) $ip_total ?></strong> <?= tohtml(_("addresses")) ?></span>
			<?php if ($ip_shared > 0) { ?>
				<span class="vz-stat-pill is-info"><strong><?= (int) $ip_shared ?></strong> <?= tohtml(_("shared")) ?></span>
			<?php } ?>
			<?php if ($ip_dedicated > 0) { ?>
				<span class="vz-stat-pill is-success"><strong><?= (int) $ip_dedicated ?></strong> <?= tohtml(_("dedicated")) ?></span>
			<?php } ?>
		</div>
	</div>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell">
				<input type="checkbox" class="js-toggle-all-checkbox" title="<?= tohtml(_("Select all")) ?>">
			</div>
			<div class="units-table-cell"><?= tohtml(_("IP Address")) ?></div>
			<div class="units-table-cell"></div>
			<div class="units-table-cell u-text-center"><?= tohtml(_("Netmask")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml(_("Interface")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml(_("Status")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml(_("Domains")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml(_("Owner")) ?></div>
		</div>

		<!-- Begin IP address list item loop -->
		<?php
  $i = 0;
  foreach ($data as $key => $value) {
  	++$i;
  	$row_status = strtolower($data[$key]["STATUS"] ?? "");
  	?>
			<div class="units-table-row js-unit vz-filter-row"
				data-sort-ip="<?= tohtml(str_replace(".", "", $key)) ?>"
				data-sort-date="<?= tohtml(strtotime($data[$key]["DATE"] . " " . $data[$key]["TIME"])) ?>"
				data-sort-netmask="<?= tohtml(str_replace(".", "", $data[$key]["NETMASK"])) ?>"
				data-sort-interface="<?= tohtml($data[$key]["INTERFACE"]) ?>"
				data-sort-domains="<?= tohtml($data[$key]["U_WEB_DOMAINS"]) ?>"
				data-sort-owner="<?= tohtml($data[$key]["OWNER"]) ?>"
				data-name="<?= tohtml(strtolower($key . " " . ($data[$key]["NAT"] ?? "") . " " . ($data[$key]["OWNER"] ?? ""))) ?>"
				data-status="<?= tohtml($row_status) ?>"
				x-show="isVisible($el)"
				x-transition.opacity
			>
				<div class="units-table-cell">
					<div>
						<input id="check<?= tohtml($i) ?>" class="js-unit-checkbox" type="checkbox" title="<?= tohtml(
	_("Select"),
) ?>" name="ip[]" value="<?= tohtml($key) ?>">
						<label for="check<?= tohtml($i) ?>" class="u-hide-desktop"><?= tohtml(_("Select")) ?></label>
					</div>
				</div>
				<div class="units-table-cell units-table-heading-cell u-text-bold">
					<span class="u-hide-desktop"><?= tohtml(_("IP Address")) ?>:</span>
					<div class="vz-web-name">
						<a href="/edit/ip/?<?= tohtml(
      	http_build_query(["ip" => $key, "token" => $_SESSION["token"]]),
      ) ?>" title="<?= tohtml(_("Edit IP Address")) ?>">
							<?= tohtml($key) ?> <?php if (!empty($data[$key]["NAT"])) {
 	echo " → " . tohtml($data[$key]["NAT"]);
 } ?>
						</a>
						<div class="vz-web-badges">
							<span class="vz-badge <?= $row_status === "dedicated" ? "vz-badge-success" : "vz-badge-info" ?>">
								<?= tohtml(_($data[$key]["STATUS"])) ?>
							</span>
						</div>
					</div>
				</div>
				<div class="units-table-cell">
					<ul class="units-table-row-actions">
						<li class="units-table-row-action shortcut-enter" data-key-action="href">
							<a
								class="units-table-row-action-link"
								href="/edit/ip/?<?= tohtml(http_build_query(["ip" => $key, "token" => $_SESSION["token"]])) ?>"
								title="<?= tohtml(_("Edit IP Address")) ?>"
							>
								<i class="fas fa-pencil icon-orange"></i>
								<span class="u-hide-desktop"><?= tohtml(_("Edit IP Address")) ?></span>
							</a>
						</li>
						<li class="units-table-row-action shortcut-delete" data-key-action="js">
							<a
								class="units-table-row-action-link data-controls js-confirm-action"
								href="/delete/ip/?<?= tohtml(http_build_query(["ip" => $key, "token" => $_SESSION["token"]])) ?>"
								title="<?= tohtml(_("Delete")) ?>"
								data-confirm-title="<?= tohtml(_("Delete")) ?>"
								data-confirm-message="<?= tohtml(sprintf(_("Are you sure you want to delete IP address %s?"), $key)) ?>"
							>
								<i class="fas fa-trash icon-red"></i>
								<span class="u-hide-desktop"><?= tohtml(_("Delete")) ?></span>
							</a>
						</li>
					</ul>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml(_("Netmask")) ?>:</span>
					<?= tohtml($data[$key]["NETMASK"]) ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml(_("Interface")) ?>:</span>
					<?= tohtml($data[$key]["INTERFACE"]) ?>
				</div>
				<div class="units-table-cell u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= tohtml(_("Status")) ?>:</span>
					<?= tohtml(_($data[$key]["STATUS"])) ?>
				</div>
				<div class="units-table-cell u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= tohtml(_("Domains")) ?>:</span>
					<?= tohtml($data[$key]["U_WEB_DOMAINS"]) ?>
				</div>
				<div class="units-table-cell u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= tohtml(_("Owner")) ?>:</span>
					<?= tohtml($data[$key]["OWNER"]) ?>
				</div>
			</div>
		<?php
  }
  ?>
	</div>

	<div class="units-table-footer">
		<p>
			<?php printf(ngettext("%d IP address", "%d IP addresses", $i), $i); ?>
		</p>
	</div>

</div>
