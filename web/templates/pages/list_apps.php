<?php
$app_total = is_array($data) ? count($data) : 0;
$app_python = 0;
$app_node = 0;
$app_running = 0;
$python_types = ["django", "flask", "fastapi", "python"];
$free_domains = [];
if (is_array($data)) {
	foreach ($data as $app) {
		$type = strtolower($app["TYPE"] ?? "");
		if (in_array($type, $python_types, true)) {
			$app_python++;
		}
		if ($type === "nodejs") {
			$app_node++;
		}
		if (($app["STATUS"] ?? "") === "active" || ($app["STATUS"] ?? "") === "running") {
			$app_running++;
		}
	}
}
if (!empty($domains) && is_array($domains)) {
	foreach ($domains as $domain_name => $domain_meta) {
		if (!isset($data[$domain_name])) {
			$free_domains[$domain_name] = $domain_meta;
		}
	}
}
?>
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a href="/list/web/" class="button button-secondary button-back js-button-back">
				<i class="fas fa-arrow-left"></i><?= tohtml(_("Back")) ?>
			</a>
			<?php if (!empty($free_domains)) {
   	$first_domain = array_key_first($free_domains); ?>
				<a href="/add/webapp/?<?= tohtml(
    	http_build_query(["domain" => $first_domain]),
    ) ?>" class="button">
					<i class="fas fa-plus"></i><?= tohtml(_("Deploy application")) ?>
				</a>
			<?php
   } ?>
		</div>
	</div>
</div>

<div class="container">
	<div class="vz-page-hero">
		<div>
			<h1 class="vz-page-title"><?= tohtml(_("Application Manager")) ?></h1>
			<p class="vz-page-subtitle"><?= tohtml(
   	_("Deploy Python and Node.js apps — starters or your own code."),
   ) ?></p>
		</div>
		<div class="vz-stat-pills">
			<span class="vz-stat-pill"><strong><?= (int) $app_total ?></strong> <?= tohtml(_("apps")) ?></span>
			<span class="vz-stat-pill is-success"><strong><?= (int) $app_python ?></strong> Python</span>
			<span class="vz-stat-pill is-info"><strong><?= (int) $app_node ?></strong> Node.js</span>
			<span class="vz-stat-pill"><strong><?= (int) $app_running ?></strong> <?= tohtml(_("running")) ?></span>
		</div>
	</div>

	<?php if (!empty($free_domains)) { ?>
		<h2 class="vz-section-title u-mb15"><?= tohtml(_("Deploy on a domain")) ?></h2>
		<div class="vz-tools-grid u-mb20">
			<?php foreach ($free_domains as $domain_name => $domain_meta) { ?>
				<a class="vz-tool" href="/add/webapp/?<?= tohtml(http_build_query(["domain" => $domain_name, "app" => "Python"])) ?>">
					<span class="vz-tool-icon"><i class="fas fa-code"></i></span>
					<span class="vz-tool-label">Python</span>
					<span class="vz-tool-desc"><?= tohtml($domain_name) ?></span>
				</a>
				<a class="vz-tool" href="/add/webapp/?<?= tohtml(http_build_query(["domain" => $domain_name, "app" => "Nodejs"])) ?>">
					<span class="vz-tool-icon"><i class="fas fa-server"></i></span>
					<span class="vz-tool-label">Node.js</span>
					<span class="vz-tool-desc"><?= tohtml($domain_name) ?></span>
				</a>
			<?php } ?>
		</div>
	<?php } ?>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell"><?= tohtml(_("Domain")) ?></div>
			<div class="units-table-cell"></div>
			<div class="units-table-cell u-text-center"><?= tohtml(_("Type")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml(_("Port")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml(_("Path")) ?></div>
		</div>

		<?php if ($app_total === 0) { ?>
			<div class="vz-empty-state u-mb20">
				<?= tohtml(_("No runtime applications yet. Deploy a Python or Node.js app on a free domain above.")) ?>
			</div>
		<?php } ?>

		<?php foreach ($data as $domain_name => $app) {
  	$status = $app["STATUS"] ?? "unknown";
  	$is_up = in_array($status, ["active", "running"], true); ?>
			<div class="units-table-row">
				<div class="units-table-cell units-table-heading-cell u-text-bold">
					<a href="http://<?= tohtml($domain_name) ?>/" target="_blank" rel="noopener"><?= tohtml($domain_name) ?></a>
					<div class="vz-web-badges">
						<span class="vz-badge <?= $is_up ? "vz-badge-success" : "vz-badge-danger" ?>"><?= tohtml($status) ?></span>
						<span class="vz-badge vz-badge-info"><?= tohtml(strtoupper($app["TYPE"] ?? "")) ?></span>
					</div>
				</div>
				<div class="units-table-cell">
					<ul class="units-table-row-actions">
						<li class="units-table-row-action">
							<a class="units-table-row-action-link" href="/list/apps/?<?= tohtml(
       	http_build_query(["action" => "restart", "domain" => $domain_name, "token" => $_SESSION["token"]]),
       ) ?>" title="<?= tohtml(_("Restart")) ?>">
								<i class="fas fa-arrows-rotate icon-green"></i>
								<span class="u-hide-desktop"><?= tohtml(_("Restart")) ?></span>
							</a>
						</li>
						<li class="units-table-row-action">
							<a
								class="units-table-row-action-link data-controls js-confirm-action"
								href="/list/apps/?<?= tohtml(
        	http_build_query(["action" => "delete", "domain" => $domain_name, "token" => $_SESSION["token"]]),
        ) ?>"
								data-confirm-title="<?= tohtml(_("Delete")) ?>"
								data-confirm-message="<?= tohtml(sprintf(_("Delete application for %s?"), $domain_name)) ?>"
							>
								<i class="fas fa-trash icon-red"></i>
								<span class="u-hide-desktop"><?= tohtml(_("Delete")) ?></span>
							</a>
						</li>
					</ul>
				</div>
				<div class="units-table-cell u-text-center-desktop"><?= tohtml($app["TYPE"] ?? "") ?></div>
				<div class="units-table-cell u-text-center-desktop"><?= tohtml($app["PORT"] ?? "") ?></div>
				<div class="units-table-cell u-text-center-desktop"><code><?= tohtml($app["APP_ROOT"] ?? "") ?></code></div>
			</div>
		<?php
  } ?>
	</div>
</div>
