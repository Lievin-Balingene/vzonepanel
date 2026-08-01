<?php
$u = $panel[$user];
$limit_label = function ($used, $limit) {
	if ($limit === "unlimited") {
		return $used . " / ∞";
	}
	return $used . " / " . $limit;
};
$pct = function ($used, $limit) {
	if ($limit === "unlimited" || !is_numeric($limit) || (float) $limit == 0) {
		return null;
	}
	return min(100, round(((float) $used / (float) $limit) * 100));
};
$disk_pct = $pct($u["U_DISK"] ?? 0, $u["DISK_QUOTA"] ?? "unlimited");
$bw_pct = $pct($u["U_BANDWIDTH"] ?? 0, $u["BANDWIDTH"] ?? "unlimited");
$uptime = isset($sys["sysinfo"]["UPTIME"]) ? humanize_time($sys["sysinfo"]["UPTIME"]) : "—";
$hostname = $sys["sysinfo"]["HOSTNAME"] ?? get_hostname();

$vz_section_icons = [
	_("Domains") => "fa-globe",
	_("DNS") => "fa-sitemap",
	_("Email") => "fa-envelope",
	_("Databases") => "fa-database",
	_("Files & tools") => "fa-folder-open",
	_("Server") => "fa-server",
];

$vz_tools = [];

if (!empty($_SESSION["WEB_SYSTEM"]) && ($u["WEB_DOMAINS"] ?? "0") != "0") {
	$vz_tools[] = [
		"cat" => _("Domains"),
		"items" => [
			[
				"href" => "/list/web/",
				"icon" => "fa-globe",
				"label" => _("Web Hosting"),
				"keys" => "web domain site hosting",
			],
			[
				"href" => "/add/web/",
				"icon" => "fa-plus",
				"label" => _("Create Domain"),
				"keys" => "add create domain",
			],
			[
				"href" => "/list/web/",
				"icon" => "fa-lock",
				"label" => _("SSL/TLS"),
				"keys" => "ssl tls certificate letsencrypt https",
			],
			[
				"href" => "/list/apps/",
				"icon" => "fa-cubes",
				"label" => _("Applications"),
				"keys" => "app php python node django flask fastapi wordpress express",
			],
		],
	];
}

if (!empty($_SESSION["DNS_SYSTEM"]) && ($u["DNS_DOMAINS"] ?? "0") != "0") {
	$vz_tools[] = [
		"cat" => _("DNS"),
		"items" => [
			[
				"href" => "/list/dns/",
				"icon" => "fa-sitemap",
				"label" => _("DNS Zones"),
				"keys" => "dns zone record mx a cname",
			],
			[
				"href" => "/add/dns/",
				"icon" => "fa-plus",
				"label" => _("Add DNS Zone"),
				"keys" => "add dns zone",
			],
		],
	];
}

if (!empty($_SESSION["MAIL_SYSTEM"]) && ($u["MAIL_DOMAINS"] ?? "0") != "0") {
	$vz_tools[] = [
		"cat" => _("Email"),
		"items" => [
			[
				"href" => "/list/mail/",
				"icon" => "fa-envelope",
				"label" => _("Email Accounts"),
				"keys" => "email mail mailbox account",
			],
			[
				"href" => "/add/mail/",
				"icon" => "fa-plus",
				"label" => _("Add Mail Domain"),
				"keys" => "add mail email domain",
			],
		],
	];
}

if (!empty($_SESSION["DB_SYSTEM"]) && ($u["DATABASES"] ?? "0") != "0") {
	$db_items = [
		[
			"href" => "/list/db/",
			"icon" => "fa-database",
			"label" => _("Manage Databases"),
			"keys" => "database mysql pgsql postgres",
		],
		[
			"href" => "/add/db/",
			"icon" => "fa-plus",
			"label" => _("Create Database"),
			"keys" => "add create database",
		],
	];
	if (strpos($_SESSION["DB_SYSTEM"], "mysql") !== false) {
		array_unshift($db_items, [
			"href" => "/phpmyadmin/",
			"icon" => "fa-table",
			"label" => "phpMyAdmin",
			"keys" => "phpmyadmin mysql",
		]);
	}
	if (strpos($_SESSION["DB_SYSTEM"], "pgsql") !== false) {
		array_unshift($db_items, [
			"href" => "/phppgadmin/",
			"icon" => "fa-table",
			"label" => "phpPgAdmin",
			"keys" => "phppgadmin postgres",
		]);
	}
	$vz_tools[] = [
		"cat" => _("Databases"),
		"items" => $db_items,
	];
}

$ops = ["cat" => _("Files & tools"), "items" => []];
if (!empty($_SESSION["FILE_MANAGER"]) && $_SESSION["FILE_MANAGER"] === "true") {
	$ops["items"][] = [
		"href" => "/fm/",
		"icon" => "fa-folder-open",
		"label" => _("File Manager"),
		"keys" => "file manager files ftp",
	];
}
if (!empty($_SESSION["BACKUP_SYSTEM"]) && (($u["BACKUPS"] ?? "0") != "0" || ($u["U_BACKUPS"] ?? "0") != "0")) {
	$ops["items"][] = [
		"href" => "/list/backup/",
		"icon" => "fa-cloud-arrow-up",
		"label" => _("Backups"),
		"keys" => "backup restore",
	];
}
if (!empty($_SESSION["CRON_SYSTEM"]) && ($u["CRON_JOBS"] ?? "0") != "0") {
	$ops["items"][] = [
		"href" => "/list/cron/",
		"icon" => "fa-clock",
		"label" => _("Cron Jobs"),
		"keys" => "cron job schedule",
	];
}
$ops["items"][] = [
	"href" => "/list/log/",
	"icon" => "fa-scroll",
	"label" => _("Logs"),
	"keys" => "log history",
];
$ops["items"][] = [
	"href" => "/edit/user/?user=" . urlencode($user) . "&token=" . urlencode($_SESSION["token"]),
	"icon" => "fa-sliders",
	"label" => _("Settings"),
	"keys" => "settings profile preferences",
];
if (count($ops["items"])) {
	$vz_tools[] = $ops;
}

if ($_SESSION["userContext"] === "admin" && empty($_SESSION["look"])) {
	$vz_tools[] = [
		"cat" => _("Server"),
		"items" => [
			[
				"href" => "/list/server/",
				"icon" => "fa-heart-pulse",
				"label" => _("Monitoring"),
				"keys" => "server monitor service",
			],
			[
				"href" => "/list/user/",
				"icon" => "fa-users",
				"label" => _("Users"),
				"keys" => "user account package",
			],
			[
				"href" => "/list/rrd/",
				"icon" => "fa-chart-area",
				"label" => _("Performance"),
				"keys" => "rrd graph cpu ram",
			],
			[
				"href" => "/list/firewall/",
				"icon" => "fa-shield-halved",
				"label" => _("Firewall"),
				"keys" => "firewall security ban",
			],
		],
	];
}
?>

<div class="container vz-dashboard vz-tools-home" x-data="vzToolsHome">
	<div class="vz-tools-layout">
		<div class="vz-tools-main">
			<header class="vz-tools-pagehead">
				<h1><?= _("Tools") ?></h1>
			</header>

			<div class="vz-tools-search">
				<i class="fas fa-magnifying-glass" aria-hidden="true"></i>
				<input
					type="search"
					x-model="q"
					placeholder="<?= _("Search Tools (/)") ?>"
					aria-label="<?= _("Search tools") ?>"
					autofocus
				>
			</div>

			<?php foreach ($vz_tools as $i => $section) {
				$sec_icon = $vz_section_icons[$section["cat"]] ?? "fa-grip";
				$open_default = $i === 0 ? "true" : "false"; ?>
				<section
					class="vz-tools-panel"
					x-show="sectionVisible($el)"
					x-data="{ open: <?= $open_default ?> }"
				>
					<button
						type="button"
						class="vz-tools-panel-head"
						@click="open = !open"
						:aria-expanded="open"
					>
						<span class="vz-tools-panel-title">
							<i class="fas <?= htmlspecialchars($sec_icon) ?>" aria-hidden="true"></i>
							<?= htmlspecialchars($section["cat"]) ?>
						</span>
						<i class="fas fa-chevron-down vz-tools-chevron" :class="open && 'is-open'" aria-hidden="true"></i>
					</button>
					<div class="vz-tools-panel-body" x-show="open" x-collapse>
						<div class="vz-tools-list">
							<?php foreach ($section["items"] as $tool) { ?>
								<a
									class="vz-tool"
									href="<?= htmlspecialchars($tool["href"]) ?>"
									data-keys="<?= htmlspecialchars(strtolower($tool["label"] . " " . ($tool["keys"] ?? ""))) ?>"
									x-show="toolVisible($el)"
								>
									<span class="vz-tool-icon"><i class="fas <?= htmlspecialchars($tool["icon"]) ?>"></i></span>
									<span class="vz-tool-label"><?= htmlspecialchars($tool["label"]) ?></span>
								</a>
							<?php } ?>
						</div>
					</div>
				</section>
			<?php } ?>
		</div>

		<aside class="vz-tools-aside">
			<div class="vz-aside-card">
				<h2><?= _("General Information") ?></h2>
				<dl class="vz-aside-meta">
					<div>
						<dt><?= _("User") ?></dt>
						<dd><?= htmlspecialchars($user) ?></dd>
					</div>
					<div>
						<dt><?= _("Server") ?></dt>
						<dd><?= htmlspecialchars($hostname) ?></dd>
					</div>
					<div>
						<dt><?= _("Uptime") ?></dt>
						<dd><?= htmlspecialchars($uptime) ?></dd>
					</div>
				</dl>
			</div>

			<div class="vz-aside-card">
				<h2><?= _("Usage") ?></h2>
				<div class="vz-aside-usage">
					<div>
						<div class="vz-aside-usage-row">
							<span><?= _("Disk") ?></span>
							<strong>
								<?= humanize_usage_size($u["U_DISK"]) ?>
								<?= humanize_usage_measure($u["U_DISK"]) ?>
							</strong>
						</div>
						<?php if ($disk_pct !== null) { ?>
							<div class="vz-progress <?= $disk_pct > 85 ? "is-danger" : ($disk_pct > 70 ? "is-warning" : "") ?>">
								<span style="width: <?= (int) $disk_pct ?>%"></span>
							</div>
						<?php } ?>
					</div>
					<div>
						<div class="vz-aside-usage-row">
							<span><?= _("Bandwidth") ?></span>
							<strong>
								<?= humanize_usage_size($u["U_BANDWIDTH"]) ?>
								<?= humanize_usage_measure($u["U_BANDWIDTH"]) ?>
							</strong>
						</div>
						<?php if ($bw_pct !== null) { ?>
							<div class="vz-progress"><span style="width: <?= (int) $bw_pct ?>%"></span></div>
						<?php } ?>
					</div>
					<div class="vz-aside-usage-row">
						<span><?= _("Web domains") ?></span>
						<strong><?= $limit_label($u["U_WEB_DOMAINS"] ?? 0, $u["WEB_DOMAINS"] ?? "0") ?></strong>
					</div>
					<div class="vz-aside-usage-row">
						<span><?= _("Databases") ?></span>
						<strong><?= $limit_label($u["U_DATABASES"] ?? 0, $u["DATABASES"] ?? "0") ?></strong>
					</div>
					<div class="vz-aside-usage-row">
						<span><?= _("Apps") ?></span>
						<strong><?= (int) (($vz_app_counts["python"] ?? 0) + ($vz_app_counts["nodejs"] ?? 0)) ?></strong>
					</div>
				</div>
			</div>
		</aside>
	</div>
</div>
