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
		if (in_array($app["STATUS"] ?? "", ["active", "running"], true)) {
			$app_running++;
		}
	}
}
if (!empty($domains) && is_array($domains)) {
	foreach ($domains as $domain_name => $domain_meta) {
		$free_domains[$domain_name] = $domain_meta;
	}
}
$first_domain = $free_domains ? array_key_first($free_domains) : null;
?>
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a href="/list/dashboard/" class="button button-secondary button-back js-button-back">
				<i class="fas fa-arrow-left"></i><?= tohtml(_("Back")) ?>
			</a>
			<?php if ($first_domain) { ?>
				<a href="/add/webapp/?<?= tohtml(http_build_query(["domain" => $first_domain])) ?>" class="button">
					<i class="fas fa-plus"></i><?= tohtml(_("Install application")) ?>
				</a>
			<?php } ?>
			<a href="/list/web/" class="button button-secondary">
				<i class="fas fa-globe"></i><?= tohtml(_("Domains")) ?>
			</a>
		</div>
	</div>
</div>

<div class="container vz-apps-manager">
	<?php show_alert_message($_SESSION); ?>

	<header class="vz-page-hero">
		<div>
			<h1 class="vz-page-title"><?= tohtml(_("Applications")) ?></h1>
			<p class="vz-page-subtitle"><?= tohtml(_("Install CMS, e-commerce, and runtime apps on your domains.")) ?></p>
		</div>
		<div class="vz-stat-pills">
			<span class="vz-stat-pill"><strong><?= (int) $app_total ?></strong> <?= tohtml(_("runtimes")) ?></span>
			<span class="vz-stat-pill is-success"><strong><?= (int) $app_python ?></strong> Python</span>
			<span class="vz-stat-pill is-info"><strong><?= (int) $app_node ?></strong> Node.js</span>
			<span class="vz-stat-pill"><strong><?= (int) $app_running ?></strong> <?= tohtml(_("running")) ?></span>
		</div>
	</header>

	<?php if (!empty($free_domains)) { ?>
		<section class="vz-apps-deploy u-mb25">
			<h2 class="vz-section-title u-mb10"><?= tohtml(_("Install on a domain")) ?></h2>
			<div class="vz-domain-install-grid">
				<?php foreach ($free_domains as $domain_name => $domain_meta) { ?>
					<div class="vz-domain-install-card">
						<div class="vz-domain-install-meta">
							<strong><?= tohtml($domain_name) ?></strong>
							<span><?= tohtml(_("Quick install catalog")) ?></span>
						</div>
						<div class="vz-domain-install-actions">
							<a class="button" href="/add/webapp/?<?= tohtml(http_build_query(["domain" => $domain_name])) ?>">
								<?= tohtml(_("Browse apps")) ?>
							</a>
							<a class="button button-secondary" href="/add/webapp/?<?= tohtml(http_build_query(["domain" => $domain_name, "app" => "WordPress"])) ?>">
								WordPress
							</a>
							<a class="button button-secondary" href="/add/webapp/?<?= tohtml(http_build_query(["domain" => $domain_name, "app" => "Python"])) ?>">
								Python
							</a>
						</div>
					</div>
				<?php } ?>
			</div>
		</section>
	<?php } else { ?>
		<div class="vz-empty-state u-mb20">
			<?= tohtml(_("Create a web domain first, then install applications here.")) ?>
			<div class="u-mt10">
				<a class="button" href="/add/web/"><?= tohtml(_("Create Domain")) ?></a>
			</div>
		</div>
	<?php } ?>

	<section>
		<h2 class="vz-section-title u-mb10"><?= tohtml(_("Running applications")) ?></h2>
		<?php if ($app_total === 0) { ?>
			<div class="vz-empty-state">
				<?= tohtml(_("No Python/Node runtime apps yet. Use Browse apps to install WordPress, Drupal, Django, and more.")) ?>
			</div>
		<?php } else { ?>
			<div class="vz-runtime-grid">
				<?php foreach ($data as $domain_name => $app) {
					$status = $app["STATUS"] ?? "unknown";
					$is_up = in_array($status, ["active", "running"], true); ?>
					<article class="vz-runtime-card">
						<header class="vz-runtime-card-head">
							<a href="http://<?= tohtml($domain_name) ?>/" target="_blank" rel="noopener"><?= tohtml($domain_name) ?></a>
							<span class="vz-badge <?= $is_up ? "vz-badge-success" : "vz-badge-danger" ?>"><?= tohtml($status) ?></span>
						</header>
						<div class="vz-runtime-card-body">
							<span class="vz-badge vz-badge-info"><?= tohtml(strtoupper($app["TYPE"] ?? "")) ?></span>
							<span class="vz-runtime-meta"><?= tohtml(_("Port")) ?>: <?= tohtml($app["PORT"] ?? "—") ?></span>
							<code class="vz-runtime-path"><?= tohtml($app["APP_ROOT"] ?? "") ?></code>
						</div>
						<footer class="vz-runtime-card-actions">
							<a class="button button-secondary" href="/list/apps/?<?= tohtml(
       	http_build_query(["action" => "restart", "domain" => $domain_name, "token" => $_SESSION["token"]]),
       ) ?>">
								<i class="fas fa-arrows-rotate"></i> <?= tohtml(_("Restart")) ?>
							</a>
							<a
								class="button button-secondary data-controls js-confirm-action"
								href="/list/apps/?<?= tohtml(
        	http_build_query(["action" => "delete", "domain" => $domain_name, "token" => $_SESSION["token"]]),
        ) ?>"
								data-confirm-title="<?= tohtml(_("Delete")) ?>"
								data-confirm-message="<?= tohtml(sprintf(_("Delete application for %s?"), $domain_name)) ?>"
							>
								<i class="fas fa-trash"></i> <?= tohtml(_("Delete")) ?>
							</a>
							<a class="button button-secondary" href="/add/webapp/?<?= tohtml(http_build_query(["domain" => $domain_name])) ?>">
								<?= tohtml(_("Install more")) ?>
							</a>
						</footer>
					</article>
				<?php } ?>
			</div>
		<?php } ?>
	</section>
</div>
