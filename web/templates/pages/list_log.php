<?php
$log_total = is_array($data) ? count($data) : 0;
?>
<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<?php if ($_SESSION["userContext"] === "admin" && $_SESSION["look"] === "admin") { ?>
				<a href="/list/user/" class="button button-secondary button-back js-button-back">
					<i class="fas fa-arrow-left"></i><?= tohtml(_("Back")) ?>
				</a>
			<?php } elseif ($_SESSION["userContext"] === "admin" && htmlentities($_GET["user"]) === "system") { ?>
				<a href="/list/server/" class="button button-secondary button-back js-button-back">
					<i class="fas fa-arrow-left"></i><?= tohtml(_("Back")) ?>
				</a>
			<?php } else { ?>
				<?php if ($_SESSION["userContext"] === "admin" && $_SESSION["look"] !== "" && $_GET["user"] !== "admin") { ?>
					<a href="/edit/user/?<?= tohtml(
     	http_build_query(["user" => $_SESSION["look"], "token" => $_SESSION["token"]]),
     ) ?>" class="button button-secondary button-back js-button-back">
						<i class="fas fa-arrow-left"></i><?= tohtml(_("Back")) ?>
					</a>
				<?php } else { ?>
					<a href="/edit/user/?<?= tohtml(
     	http_build_query(["user" => $_SESSION["user"], "token" => $_SESSION["token"]]),
     ) ?>" class="button button-secondary button-back js-button-back">
						<i class="fas fa-arrow-left"></i><?= tohtml(_("Back")) ?>
					</a>
				<?php } ?>
			<?php } ?>
			<?php if ($_SESSION["DEMO_MODE"] != "yes") {
   	if ($_SESSION["userContext"] === "admin" && htmlentities($_GET["user"]) !== "admin") { ?>
				<?php if ($_SESSION["userContext"] === "admin" && $_GET["user"] != "" && htmlentities($_GET["user"]) !== "admin") { ?>
					<?php if (htmlentities($_GET["user"]) !== "system") { ?>
						<a href="/list/log/auth/?<?= tohtml(
      	http_build_query(["user" => $_GET["user"], "token" => $_SESSION["token"]]),
      ) ?>" class="button button-secondary" title="<?= tohtml(_("Login History")) ?>">
							<i class="fas fa-binoculars"></i><?= tohtml(_("Login History")) ?>
						</a>
					<?php } ?>
				<?php } else { ?>
					<a href="/list/log/auth/" class="button button-secondary" title="<?= tohtml(_("Login History")) ?>">
						<i class="fas fa-binoculars"></i><?= tohtml(_("Login History")) ?>
					</a>
				<?php } ?>
			<?php } ?>
			<?php if ($_SESSION["userContext"] === "user") { ?>
				<a href="/list/log/auth/" class="button button-secondary" title="<?= tohtml(_("Login History")) ?>">
					<i class="fas fa-binoculars"></i><?= tohtml(_("Login History")) ?>
				</a>
			<?php }
   } ?>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-search">
				<form action="#" method="get" onsubmit="return false;">
					<input type="search" class="form-control js-vz-live-filter" name="q" value="" placeholder="<?= tohtml(
     	_("Filter logs…"),
     ) ?>" title="<?= tohtml(_("Search")) ?>" autocomplete="off">
				</form>
			</div>
			<div class="toolbar-buttons">
				<a href="javascript:location.reload();" class="button button-secondary"><i class="fas fa-arrow-rotate-right"></i><?= tohtml(
    	_("Refresh"),
    ) ?></a>
				<?php if (
    	$_SESSION["userContext"] === "admin" &&
    	$_SESSION["look"] === "admin" &&
    	$_SESSION["POLICY_SYSTEM_PROTECTED_ADMIN"] === "yes"
    ) { ?>
					<!-- Hide delete buttons-->
				<?php } else { ?>
					<?php if (
     	$_SESSION["userContext"] === "admin" ||
     	($_SESSION["userContext"] === "user" && $_SESSION["POLICY_USER_DELETE_LOGS"] !== "no")
     ) { ?>
						<a
							class="button button-secondary button-danger data-controls js-confirm-action"
							<?php if ($_SESSION["userContext"] === "admin" && isset($_GET["user"])) { ?>
								href="/delete/log/?<?= tohtml(http_build_query(["user" => $_GET["user"], "token" => $_SESSION["token"]])) ?>"
							<?php } else { ?>
								href="/delete/log/?<?= tohtml(http_build_query(["token" => $_SESSION["token"]])) ?>"
							<?php } ?>
							data-confirm-title="<?= tohtml(_("Delete")) ?>"
							data-confirm-message="<?= tohtml(_("Are you sure you want to delete the logs?")) ?>"
						>
							<i class="fas fa-circle-xmark icon-red"></i><?= tohtml(_("Delete")) ?>
						</a>
					<?php } ?>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container" x-data="vzLogList">

	<div class="vz-page-hero">
		<div>
			<h1 class="vz-page-title"><?= tohtml(_("Logs")) ?></h1>
			<p class="vz-page-subtitle"><?= tohtml(_("Review recent panel activity and system events.")) ?></p>
		</div>
		<div class="vz-stat-pills">
			<span class="vz-stat-pill"><strong><?= (int) $log_total ?></strong> <?= tohtml(_("records")) ?></span>
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
			<div class="units-table-cell"></div>
			<div class="units-table-cell"><?= tohtml(_("Date")) ?></div>
			<div class="units-table-cell"><?= tohtml(_("Time")) ?></div>
			<div class="units-table-cell"><?= tohtml(_("Category")) ?></div>
			<div class="units-table-cell"><?= tohtml(_("Message")) ?></div>
		</div>

		<!-- Begin log history entry loop -->
		<?php
  $i = 0;
  foreach ($data as $key => $value) {
  	++$i;

  	if ($data[$key]["LEVEL"] === "Info") {
  		$level_icon = "fa-info-circle icon-blue";
  		$level_title = _("Information");
  	}
  	if ($data[$key]["LEVEL"] === "Warning") {
  		$level_icon = "fa-triangle-exclamation icon-orange";
  		$level_title = _("Warning");
  	}
  	if ($data[$key]["LEVEL"] === "Error") {
  		$level_icon = "fa-circle-xmark icon-red";
  		$level_title = _("Error");
  	}
  	$row_name = strtolower(
  		($data[$key]["CATEGORY"] ?? "") . " " . ($data[$key]["MESSAGE"] ?? "") . " " . ($data[$key]["LEVEL"] ?? ""),
  	);
  	?>
			<div class="units-table-row js-unit vz-filter-row"
				data-status="active"
				data-name="<?= tohtml($row_name) ?>"
				x-show="isVisible($el)"
				x-transition.opacity
			>
				<div class="units-table-cell u-text-center-desktop">
					<i class="fas <?= tohtml($level_icon) ?>" title="<?= tohtml($level_title) ?>"></i>
				</div>
				<div class="units-table-cell units-table-heading-cell u-text-bold">
					<span class="u-hide-desktop"><?= tohtml(_("Date")) ?>:</span>
					<time datetime="<?= tohtml($data[$key]["DATE"]) ?>" class="u-text-no-wrap">
						<?= tohtml(translate_date($data[$key]["DATE"])) ?>
					</time>
				</div>
				<div class="units-table-cell u-text-bold">
					<span class="u-hide-desktop"><?= tohtml(_("Time")) ?>:</span>
					<time datetime="<?= tohtml($data[$key]["TIME"]) ?>">
						<?= tohtml($data[$key]["TIME"]) ?>
					</time>
				</div>
				<div class="units-table-cell u-text-bold">
					<span class="u-hide-desktop"><?= tohtml(_("Category")) ?>:</span>
					<span class="u-text-no-wrap">
						<?= tohtml($data[$key]["CATEGORY"]) ?>
					</span>
				</div>
				<div class="units-table-cell">
					<span class="u-hide-desktop u-text-bold"><?= tohtml(_("Message")) ?>:</span>
					<?= tohtml($data[$key]["MESSAGE"]) ?>
				</div>
			</div>
		<?php
  }
  ?>
	</div>

	<div class="units-table-footer">
		<p>
			<?php printf(ngettext("%d log record", "%d log records", $i), $i); ?>
		</p>
	</div>

</div>
