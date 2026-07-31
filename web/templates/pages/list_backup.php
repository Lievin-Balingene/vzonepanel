<?php
$backup_total = is_array($data) ? count($data) : 0;
$backup_size = 0;
if (is_array($data)) {
	foreach ($data as $d) {
		$backup_size += (float) ($d["SIZE"] ?? 0);
	}
}
?>
<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<?php if ($read_only !== "true") { ?>
				<a href="/schedule/backup/?<?= tohtml(
    	http_build_query(["token" => $_SESSION["token"]]),
    ) ?>" class="button"><i class="fas fa-plus"></i><?= tohtml(_("Create Backup")) ?></a>
				<a href="/list/backup/exclusions/" class="button button-secondary"><i class="fas fa-folder-minus"></i><?= tohtml(
    	_("Backup Exclusions"),
    ) ?></a>
			<?php } ?>
			<?php if ($panel[$user_plain]["BACKUPS_INCREMENTAL"] === "yes") { ?>
				<a href="/list/backup/incremental/" class="button button-secondary"><i class="fas fa-vault"></i><?= tohtml(
    	_("Incremental Backups"),
    ) ?></a>
			<?php } ?>
		</div>
		<div class="toolbar-right">
			<?php if ($read_only !== "true") { ?>
				<form x-data x-bind="BulkEdit" action="/bulk/backup/" method="post">
					<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
					<select class="form-select" name="action">
						<option value=""><?= tohtml(_("Apply to selected")) ?></option>
						<option value="delete"><?= tohtml(_("Delete")) ?></option>
					</select>
					<button type="submit" class="toolbar-input-submit" title="<?= tohtml(_("Apply to selected")) ?>">
						<i class="fas fa-arrow-right"></i>
					</button>
				</form>
			<?php } ?>
			<div class="toolbar-search">
				<form action="/search/" method="get">
					<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
					<input type="search" class="form-control js-search-input js-vz-live-filter" name="q" value="<?= tohtml(
     	$_POST["q"] ?? "",
     ) ?>" placeholder="<?= tohtml(_("Filter backups…")) ?>" title="<?= tohtml(_("Search")) ?>" autocomplete="off">
					<button type="submit" class="toolbar-input-submit" title="<?= tohtml(_("Search")) ?>">
						<i class="fas fa-magnifying-glass"></i>
					</button>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container" x-data="vzBackupList">

	<div class="vz-page-hero">
		<div>
			<h1 class="vz-page-title"><?= tohtml(_("Backups")) ?></h1>
			<p class="vz-page-subtitle"><?= tohtml(_("Download, restore, or delete account backups.")) ?></p>
		</div>
		<div class="vz-stat-pills">
			<span class="vz-stat-pill"><strong><?= (int) $backup_total ?></strong> <?= tohtml(_("backups")) ?></span>
			<?php if ($backup_total > 0) { ?>
				<span class="vz-stat-pill is-info"><strong><?= tohtml(
    	humanize_usage_size($backup_size),
    ) ?></strong> <?= tohtml(humanize_usage_measure($backup_size)) ?></span>
			<?php } ?>
		</div>
	</div>

	<div class="vz-filter-bar">
		<button type="button" class="vz-chip" :class="filter === 'all' && 'active'" @click="filter = 'all'">
			<?= tohtml(_("All")) ?>
		</button>
		<span class="vz-filter-count" x-text="visibleCountLabel"></span>
	</div>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell">
				<input type="checkbox" class="js-toggle-all-checkbox" title="<?= tohtml(_("Select all")) ?>" <?= tohtml(
	$display_mode,
) ?>>
			</div>
			<div class="units-table-cell"><?= tohtml(_("File Name")) ?></div>
			<div class="units-table-cell"></div>
			<div class="units-table-cell u-text-center"><?= tohtml(_("Date")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml(_("Size")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml(_("Type")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml(_("Runtime")) ?></div>
		</div>

		<!-- Begin user backup list item loop -->
		<?php
  $i = 0;
  foreach ($data as $key => $value) {
  	++$i;
  	$web = _("No");
  	$dns = _("No");
  	$mail = _("No");
  	$db = _("No");
  	$cron = _("No");
  	$udir = _("No");

  	if (!empty($data[$key]["WEB"])) {
  		$web = _("Yes");
  	}
  	if (!empty($data[$key]["DNS"])) {
  		$dns = _("Yes");
  	}
  	if (!empty($data[$key]["MAIL"])) {
  		$mail = _("Yes");
  	}
  	if (!empty($data[$key]["DB"])) {
  		$db = _("Yes");
  	}
  	if (!empty($data[$key]["CRON"])) {
  		$cron = _("Yes");
  	}
  	if (!empty($data[$key]["UDIR"])) {
  		$udir = _("Yes");
  	}
  	?>
			<div class="units-table-row js-unit vz-filter-row"
				data-status="active"
				data-name="<?= tohtml(strtolower($key)) ?>"
				x-show="isVisible($el)"
				x-transition.opacity
			>
				<div class="units-table-cell">
					<div>
						<input id="check<?= tohtml($i) ?>" class="js-unit-checkbox" type="checkbox" title="<?= tohtml(
	_("Select"),
) ?>" name="backup[]" value="<?= tohtml($key) ?>" <?= tohtml($display_mode) ?>>
						<label for="check<?= tohtml($i) ?>" class="u-hide-desktop"><?= tohtml(_("Select")) ?></label>
					</div>
				</div>
				<div class="units-table-cell units-table-heading-cell u-text-bold">
					<span class="u-hide-desktop"><?= tohtml(_("File Name")) ?>:</span>
					<div class="vz-web-name">
						<?php if ($read_only === "true") { ?>
							<?= tohtml($key) ?>
						<?php } else { ?>
							<a href="/list/backup/?<?= tohtml(
        	http_build_query(["backup" => $key, "token" => $_SESSION["token"]]),
        ) ?>" title="<?= tohtml(_("Restore")) ?>">
								<?= tohtml($key) ?>
							</a>
						<?php } ?>
						<div class="vz-web-badges">
							<?php if (!empty($data[$key]["TYPE"])) { ?>
								<span class="vz-badge vz-badge-info"><?= tohtml($data[$key]["TYPE"]) ?></span>
							<?php } ?>
						</div>
					</div>
				</div>
				<div class="units-table-cell">
					<?php if (!($_SESSION["userContext"] === "admin" && $_SESSION["look"] === "admin" && $read_only === "true")) { ?>
						<ul class="units-table-row-actions">
							<li class="units-table-row-action shortcut-d" data-key-action="href">
								<a
									class="units-table-row-action-link"
									href="/download/backup/?<?= tohtml(
         	http_build_query(["backup" => $key, "token" => $_SESSION["token"]]),
         ) ?>"
									title="<?= tohtml(_("Download")) ?>"
								>
									<i class="fas fa-file-arrow-down icon-lightblue"></i>
									<span class="u-hide-desktop"><?= tohtml(_("Download")) ?></span>
								</a>
							</li>
							<?php if ($read_only !== "true") { ?>
								<li class="units-table-row-action shortcut-enter" data-key-action="href">
									<a
										class="units-table-row-action-link data-controls"
										href="/list/backup/?<?= tohtml(
          	http_build_query(["backup" => $key, "token" => $_SESSION["token"]]),
          ) ?>"
										title="<?= tohtml(_("Restore")) ?>"
									>
										<i class="fas fa-arrow-rotate-left icon-green"></i>
										<span class="u-hide-desktop"><?= tohtml(_("Restore")) ?></span>
									</a>
								</li>
								<li class="units-table-row-action shortcut-delete" data-key-action="js">
									<a
										class="units-table-row-action-link data-controls js-confirm-action"
										href="/delete/backup/?<?= tohtml(
          	http_build_query(["backup" => $key, "token" => $_SESSION["token"]]),
          ) ?>"
										title="<?= tohtml(_("Delete")) ?>"
										data-confirm-title="<?= tohtml(_("Delete")) ?>"
										data-confirm-message="<?= tohtml(sprintf(_("Are you sure you want to delete backup %s?"), $key)) ?>"
									>
										<i class="fas fa-trash icon-red"></i>
										<span class="u-hide-desktop"><?= tohtml(_("Delete")) ?></span>
									</a>
								</li>
							<?php } ?>
						</ul>
					<?php } ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml(_("Date")) ?>:</span>
					<span class="u-text-bold">
						<?= tohtml(translate_date($data[$key]["DATE"])) ?>
					</span>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml(_("Size")) ?>:</span>
					<span class="u-text-bold">
						<?= tohtml(humanize_usage_size($data[$key]["SIZE"])) ?>
					</span>
					<span class="u-text-small">
						<?= tohtml(humanize_usage_measure($data[$key]["SIZE"])) ?>
					</span>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml(_("Type")) ?>:</span>
					<?= tohtml($data[$key]["TYPE"]) ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml(_("Runtime")) ?>:</span>
					<?= tohtml(humanize_time($data[$key]["RUNTIME"])) ?>
				</div>
			</div>
		<?php
  }
  ?>
	</div>

	<div class="units-table-footer">
		<p>
			<?php printf(ngettext("%d backup", "%d backups", $i), $i); ?>
		</p>
	</div>

</div>
