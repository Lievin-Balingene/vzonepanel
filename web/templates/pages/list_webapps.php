<?php
$display_name = static function (string $name): string {
	$labels = [
		"Nodejs" => "Node.js",
		"Python" => "Python",
		"Django" => "Django",
		"WordPress" => "WordPress",
		"ThirtyBees" => "thirty bees",
		"osTicket" => "osTicket",
		"QloApps" => "QloApps",
		"NamelessMC" => "NamelessMC",
		"ConcreteCMS" => "Concrete CMS",
		"MediaWiki" => "MediaWiki",
		"DokuWiki" => "DokuWiki",
		"Nextcloud" => "Nextcloud",
		"OpenCart" => "OpenCart",
		"PrestaShop" => "PrestaShop",
		"ClassicPress" => "ClassicPress",
	];
	return $labels[$name] ?? $name;
};

$group_label = static function (string $group): string {
	$map = [
		"cms" => _("CMS"),
		"ecommerce" => _("E-commerce"),
		"framework" => _("Frameworks & runtimes"),
		"crm" => _("CRM"),
		"helpdesk" => _("Helpdesk"),
		"podcasting" => _("Podcasting"),
	];
	return $map[$group] ?? _(ucfirst($group));
};

$grouped = [];
foreach ($v_web_apps as $webapp) {
	$g = $webapp->group ?: "other";
	$grouped[$g][] = $webapp;
}
uksort($grouped, static function ($a, $b) {
	$order = ["cms" => 0, "ecommerce" => 1, "framework" => 2, "crm" => 3, "helpdesk" => 4, "podcasting" => 5];
	return ($order[$a] ?? 50) <=> ($order[$b] ?? 50) ?: strcmp($a, $b);
});
?>
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/edit/web/?<?= tohtml(http_build_query(["domain" => $v_domain])) ?>">
				<i class="fas fa-arrow-left"></i><?= tohtml(_("Back")) ?>
			</a>
			<a class="button button-secondary" href="/list/apps/">
				<i class="fas fa-cubes"></i><?= tohtml(_("Application Manager")) ?>
			</a>
			<a class="button button-secondary" href="/list/web/">
				<i class="fas fa-globe"></i><?= tohtml(_("Domains")) ?>
			</a>
		</div>
	</div>
</div>

<div
	class="container vz-apps-catalog"
	x-data="{
		q: '',
		match(keys) {
			const needle = (this.q || '').trim().toLowerCase();
			return !needle || (keys || '').includes(needle);
		}
	}"
>
	<header class="vz-page-hero u-mb15">
		<div>
			<h1 class="vz-page-title"><?= tohtml(_("Install Application")) ?></h1>
			<p class="vz-page-subtitle">
				<?= tohtml(sprintf(_("Install on %s"), $v_domain)) ?>
			</p>
		</div>
	</header>

	<?php show_alert_message($_SESSION); ?>

	<div class="vz-tools-search u-mb20">
		<i class="fas fa-magnifying-glass" aria-hidden="true"></i>
		<input
			type="search"
			x-model="q"
			placeholder="<?= tohtml(_("Search apps (WordPress, Drupal, Django…)")) ?>"
			aria-label="<?= tohtml(_("Search applications")) ?>"
			autofocus
		>
	</div>

	<?php foreach ($grouped as $group => $apps) {
		$section_keys = "";
		foreach ($apps as $webapp) {
			$section_keys .=
				" " .
				strtolower($display_name($webapp->name) . " " . $webapp->name . " " . $webapp->group . " " . $webapp->runtime);
		} ?>
		<section class="vz-apps-group" x-show="match(<?= json_encode(trim($section_keys), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)" x-cloak>
			<h2 class="vz-section-title u-mb10"><?= tohtml($group_label($group)) ?></h2>
			<div class="cards vz-app-grid">
				<?php foreach ($apps as $webapp) {
					$label = $display_name($webapp->name);
					$keys = strtolower($label . " " . $webapp->name . " " . $webapp->group . " " . $webapp->runtime);
					$installable = $webapp->isInstallable();
					$href = "/add/webapp/?" . http_build_query(["app" => $webapp->name, "domain" => $v_domain]);
					$thumb =
						"/src/app/WebApp/Installers/" .
						rawurlencode($webapp->name) .
						"/" .
						rawurlencode($webapp->thumbnail); ?>
					<?php if ($installable) { ?>
						<a
							class="card vz-app-card"
							href="<?= tohtml($href) ?>"
							x-show="match(<?= json_encode($keys, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)"
							title="<?= tohtml(sprintf(_("Install %s"), $label)) ?>"
						>
							<span class="card-thumb">
								<img src="<?= tohtml($thumb) ?>" alt="" loading="lazy" width="48" height="48">
							</span>
							<span class="card-content">
								<span class="card-title"><?= tohtml($label) ?></span>
								<span class="card-desc">v<?= tohtml($webapp->version) ?></span>
								<span class="vz-app-cta"><?= tohtml(_("Install")) ?></span>
							</span>
						</a>
					<?php } else { ?>
						<div
							class="card vz-app-card disabled"
							x-show="match(<?= json_encode($keys, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)"
							title="<?= tohtml(_("Required runtime unavailable")) ?>"
						>
							<span class="card-thumb">
								<img src="<?= tohtml($thumb) ?>" alt="" loading="lazy" width="48" height="48">
							</span>
							<span class="card-content">
								<span class="card-title"><?= tohtml($label) ?></span>
								<span class="card-desc">v<?= tohtml($webapp->version) ?></span>
								<span class="vz-app-cta is-muted"><?= tohtml(_("Unavailable")) ?></span>
							</span>
						</div>
					<?php } ?>
				<?php } ?>
			</div>
		</section>
	<?php } ?>
</div>
