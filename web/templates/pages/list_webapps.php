<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/edit/web/?<?= tohtml(http_build_query(["domain" => $v_domain])) ?>">
				<i class="fas fa-arrow-left"></i><?= tohtml(_("Back")) ?>
			</a>
			<a class="button button-secondary" href="/list/apps/">
				<i class="fas fa-cubes"></i><?= tohtml(_("Application Manager")) ?>
			</a>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">
	<div class="vz-page-hero u-mb20">
		<div>
			<h1 class="vz-page-title"><?= tohtml(_("Quick Install App")) ?></h1>
			<p class="vz-page-subtitle"><?= tohtml(sprintf(_("Choose an application for %s"), $v_domain)) ?></p>
		</div>
	</div>

	<?php show_alert_message($_SESSION); ?>

	<?php
	$php_apps = [];
	$runtime_apps = [];
	foreach ($v_web_apps as $webapp) {
		if (($webapp->runtime ?? "php") === "php") {
			$php_apps[] = $webapp;
		} else {
			$runtime_apps[] = $webapp;
		}
	}
	?>

	<?php if ($runtime_apps) { ?>
		<h2 class="vz-section-title u-mb15"><?= tohtml(_("Runtime apps")) ?></h2>
		<div class="cards u-mb30">
			<?php foreach ($runtime_apps as $webapp) { ?>
				<div class="card <?= $webapp->isInstallable() ? "" : "disabled" ?>">
					<div class="card-thumb">
						<img src="/src/app/WebApp/Installers/<?= tohtml($webapp->name) ?>/<?= tohtml($webapp->thumbnail) ?>" alt="<?= tohtml($webapp->name) ?>">
					</div>
					<div class="card-content">
						<p class="card-title"><?php
      	$labels = ["Nodejs" => "Node.js", "Python" => "Python", "Django" => "Django"];
      	echo tohtml($labels[$webapp->name] ?? $webapp->name);
      ?></p>
						<p class="u-mb10">
							<span class="vz-badge vz-badge-info"><?= tohtml(strtoupper($webapp->runtime)) ?></span>
							<?= tohtml(_("Version")) ?>: <?= tohtml($webapp->version) ?>
						</p>
						<?php if ($webapp->name === "Python") { ?>
							<p class="card-desc"><?= tohtml(_("Django, Flask, FastAPI or your own app")) ?></p>
						<?php } elseif ($webapp->name === "Nodejs") { ?>
							<p class="card-desc"><?= tohtml(_("Express starter or any Node app with npm start")) ?></p>
						<?php } ?>
						<?php if ($webapp->isInstallable()) { ?>
							<a class="button" href="/add/webapp/?<?= tohtml(http_build_query(["app" => $webapp->name, "domain" => $v_domain])) ?>">
								<?= tohtml(_("Setup")) ?>
							</a>
						<?php } else { ?>
							<button class="button" type="button" disabled><?= tohtml(_("Unavailable")) ?></button>
						<?php } ?>
					</div>
				</div>
			<?php } ?>
		</div>
	<?php } ?>

	<h2 class="vz-section-title u-mb15"><?= tohtml(_("PHP applications")) ?></h2>
	<div class="cards">
		<?php foreach ($php_apps as $webapp) { ?>
			<div class="card <?= $webapp->isInstallable() ? "" : "disabled" ?>">
				<div class="card-thumb">
					<img src="/src/app/WebApp/Installers/<?= tohtml($webapp->name) ?>/<?= tohtml($webapp->thumbnail) ?>" alt="<?= tohtml($webapp->name) ?>">
				</div>
				<div class="card-content">
					<p class="card-title"><?= tohtml($webapp->name) ?></p>
					<p class="card-desc"><?= tohtml(_("Version")) ?>: <?= tohtml($webapp->version) ?></p>
					<?php if ($webapp->isInstallable()) { ?>
						<a class="button" href="/add/webapp/?<?= tohtml(http_build_query(["app" => $webapp->name, "domain" => $v_domain])) ?>">
							<?= tohtml(_("Setup")) ?>
						</a>
					<?php } else { ?>
						<button class="button" type="button" disabled><?= tohtml(_("Missing PHP version")) ?></button>
					<?php } ?>
				</div>
			</div>
		<?php } ?>
	</div>
</div>
