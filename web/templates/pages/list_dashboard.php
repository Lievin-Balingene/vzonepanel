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
$load = $sys["sysinfo"]["LOADAVERAGE"] ?? "—";
$uptime = isset($sys["sysinfo"]["UPTIME"]) ? humanize_time($sys["sysinfo"]["UPTIME"]) : "—";
$hostname = $sys["sysinfo"]["HOSTNAME"] ?? get_hostname();
?>

<div class="container vz-dashboard" x-data="vzToolsHome">
	<header class="vz-dashboard-hero">
		<div class="vz-dashboard-hero-copy">
			<p class="vz-dashboard-kicker"><?= tohtml(_("Control panel")) ?></p>
			<h1><?= _("Dashboard") ?></h1>
			<p class="vz-dashboard-welcome">
				<?= htmlspecialchars(sprintf(_("Welcome back, %s"), $u["NAME"] ?? $user)) ?>
			</p>
			<p class="vz-dashboard-host">
				<i class="fas fa-server" aria-hidden="true"></i>
				<span><?= htmlspecialchars($hostname) ?></span>
				<span class="vz-dashboard-host-sep" aria-hidden="true">·</span>
				<span><?= htmlspecialchars(_("Uptime")) ?>: <?= htmlspecialchars($uptime) ?></span>
			</p>
		</div>
		<div class="vz-dashboard-hero-actions">
			<?php if (!empty($_SESSION["WEB_SYSTEM"]) && ($u["WEB_DOMAINS"] ?? "0") != "0") { ?>
				<a href="/add/web/" class="button">
					<i class="fas fa-plus"></i><?= _("Add domain") ?>
				</a>
			<?php } ?>
			<a href="/list/apps/" class="button button-secondary">
				<i class="fas fa-cubes"></i><?= _("Applications") ?>
			</a>
		</div>
	</header>

	<div class="vz-tools-search">
		<i class="fas fa-magnifying-glass"></i>
		<input
			type="search"
			x-model="q"
			placeholder="<?= _("Search tools… (domains, email, SSL, backups…)") ?>"
			aria-label="<?= _("Search tools") ?>"
			autofocus
		>
	</div>

	<?php
 $vz_tools = [];

 if (!empty($_SESSION["WEB_SYSTEM"]) && ($u["WEB_DOMAINS"] ?? "0") != "0") {
 	$vz_tools[] = [
 		"cat" => _("Domains"),
 		"items" => [
 			[
 				"href" => "/list/web/",
 				"icon" => "fa-globe",
 				"label" => _("Web Hosting"),
 				"desc" => _("Domains & sites"),
 				"keys" => "web domain site hosting",
 			],
 			[
 				"href" => "/add/web/",
 				"icon" => "fa-plus",
 				"label" => _("Create Domain"),
 				"desc" => _("Add a new website"),
 				"keys" => "add create domain",
 			],
			[
				"href" => "/list/web/",
				"icon" => "fa-lock",
				"label" => _("SSL/TLS"),
				"desc" => _("Certificates"),
				"keys" => "ssl tls certificate letsencrypt https",
			],
			[
				"href" => "/list/apps/",
				"icon" => "fa-cubes",
				"label" => _("Applications"),
				"desc" => _("Python · Node.js · PHP apps"),
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
 				"desc" => _("Manage records"),
 				"keys" => "dns zone record mx a cname",
 			],
 			[
 				"href" => "/add/dns/",
 				"icon" => "fa-plus",
 				"label" => _("Add DNS Zone"),
 				"desc" => _("New zone"),
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
 				"desc" => _("Domains & mailboxes"),
 				"keys" => "email mail mailbox account",
 			],
 			[
 				"href" => "/add/mail/",
 				"icon" => "fa-plus",
 				"label" => _("Add Mail Domain"),
 				"desc" => _("New mail domain"),
 				"keys" => "add mail email domain",
 			],
 		],
 	];
 }

 if (!empty($_SESSION["DB_SYSTEM"]) && ($u["DATABASES"] ?? "0") != "0") {
 	$vz_tools[] = [
 		"cat" => _("Databases"),
 		"items" => [
 			[
 				"href" => "/list/db/",
 				"icon" => "fa-database",
 				"label" => _("MySQL / PostgreSQL"),
 				"desc" => _("Manage databases"),
 				"keys" => "database mysql pgsql postgres phpmyadmin",
 			],
 			[
 				"href" => "/add/db/",
 				"icon" => "fa-plus",
 				"label" => _("Create Database"),
 				"desc" => _("New database"),
 				"keys" => "add create database",
 			],
 		],
 	];
 }

 $ops = ["cat" => _("Files & tools"), "items" => []];
 if (!empty($_SESSION["FILE_MANAGER"]) && $_SESSION["FILE_MANAGER"] === "true") {
 	$ops["items"][] = [
 		"href" => "/fm/",
 		"icon" => "fa-folder-open",
 		"label" => _("File Manager"),
 		"desc" => _("Browse files"),
 		"keys" => "file manager files ftp",
 	];
 }
 if (!empty($_SESSION["BACKUP_SYSTEM"]) && (($u["BACKUPS"] ?? "0") != "0" || ($u["U_BACKUPS"] ?? "0") != "0")) {
 	$ops["items"][] = [
 		"href" => "/list/backup/",
 		"icon" => "fa-cloud-arrow-up",
 		"label" => _("Backups"),
 		"desc" => _("Restore & download"),
 		"keys" => "backup restore",
 	];
 }
 if (!empty($_SESSION["CRON_SYSTEM"]) && ($u["CRON_JOBS"] ?? "0") != "0") {
 	$ops["items"][] = [
 		"href" => "/list/cron/",
 		"icon" => "fa-clock",
 		"label" => _("Cron Jobs"),
 		"desc" => _("Scheduled tasks"),
 		"keys" => "cron job schedule",
 	];
 }
 $ops["items"][] = [
 	"href" => "/list/log/",
 	"icon" => "fa-scroll",
 	"label" => _("Logs"),
 	"desc" => _("Activity history"),
 	"keys" => "log history",
 ];
 $ops["items"][] = [
 	"href" => "/edit/user/?user=" . urlencode($user) . "&token=" . urlencode($_SESSION["token"]),
 	"icon" => "fa-sliders",
 	"label" => _("Settings"),
 	"desc" => _("Account preferences"),
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
 				"desc" => _("Services & health"),
 				"keys" => "server monitor service",
 			],
 			[
 				"href" => "/list/user/",
 				"icon" => "fa-users",
 				"label" => _("Users"),
 				"desc" => _("Accounts & packages"),
 				"keys" => "user account package",
 			],
 			[
 				"href" => "/list/rrd/",
 				"icon" => "fa-chart-area",
 				"label" => _("Performance"),
 				"desc" => _("Graphs & load"),
 				"keys" => "rrd graph cpu ram",
 			],
 			[
 				"href" => "/list/firewall/",
 				"icon" => "fa-shield-halved",
 				"label" => _("Firewall"),
 				"desc" => _("IP rules"),
 				"keys" => "firewall security ban",
 			],
 		],
 	];
 }
 ?>

	<?php foreach ($vz_tools as $section) { ?>
		<section class="vz-tools-section" x-show="sectionVisible($el)">
			<h2 class="vz-section-title"><?= htmlspecialchars($section["cat"]) ?></h2>
			<div class="vz-tools-grid">
				<?php foreach ($section["items"] as $tool) { ?>
					<a
						class="vz-tool"
						href="<?= htmlspecialchars($tool["href"]) ?>"
						data-keys="<?= htmlspecialchars(strtolower($tool["label"] . " " . $tool["desc"] . " " . $tool["keys"])) ?>"
						x-show="toolVisible($el)"
					>
						<span class="vz-tool-icon"><i class="fas <?= htmlspecialchars($tool["icon"]) ?>"></i></span>
						<span class="vz-tool-label"><?= htmlspecialchars($tool["label"]) ?></span>
						<span class="vz-tool-desc"><?= htmlspecialchars($tool["desc"]) ?></span>
					</a>
				<?php } ?>
			</div>
		</section>
	<?php } ?>

	<div class="vz-dashboard-stack">
		<section class="vz-dashboard-section">
			<h2 class="vz-section-title"><?= _("Server health") ?></h2>
			<div
				class="vz-dash-grid"
				x-data="vzDashboard"
				data-cpu="<?= htmlspecialchars((string) ($vz_metrics["cpu_percent"] ?? "")) ?>"
				data-mem="<?= htmlspecialchars((string) ($vz_metrics["mem_percent"] ?? "")) ?>"
				data-disk="<?= htmlspecialchars((string) ($disk_pct ?? $vz_metrics["disk_root_percent"] ?? "")) ?>"
			>
				<a class="vz-card" href="/list/server/">
					<div class="vz-card-body">
						<div class="vz-card-header">
							<div>
								<p class="vz-card-label"><?= _("CPU") ?></p>
								<p class="vz-card-value"><?= $vz_metrics["cpu_percent"] !== null ? (int) $vz_metrics["cpu_percent"] . "%" : "—" ?></p>
							</div>
							<div class="vz-card-icon is-info"><i class="fas fa-microchip"></i></div>
						</div>
						<canvas class="vz-card-chart js-spark-cpu" height="48"></canvas>
						<p class="vz-card-meta"><?= _("Load") ?>: <?= htmlspecialchars($load) ?></p>
					</div>
				</a>

				<a class="vz-card" href="/list/server/?mem">
					<div class="vz-card-body">
						<div class="vz-card-header">
							<div>
								<p class="vz-card-label"><?= _("RAM") ?></p>
								<p class="vz-card-value"><?= $vz_metrics["mem_percent"] !== null ? (int) $vz_metrics["mem_percent"] . "%" : "—" ?></p>
							</div>
							<div class="vz-card-icon"><i class="fas fa-memory"></i></div>
						</div>
						<?php if ($vz_metrics["mem_percent"] !== null) { ?>
							<div class="vz-progress <?= $vz_metrics["mem_percent"] > 85 ? "is-danger" : ($vz_metrics["mem_percent"] > 70 ? "is-warning" : "") ?>">
								<span style="width: <?= (int) $vz_metrics["mem_percent"] ?>%"></span>
							</div>
							<p class="vz-card-meta"><?= (int) $vz_metrics["mem_used"] ?> / <?= (int) $vz_metrics["mem_total"] ?> MB</p>
						<?php } else { ?>
							<p class="vz-card-meta"><?= _("Metrics available on Linux hosts") ?></p>
						<?php } ?>
					</div>
				</a>

				<a class="vz-card" href="/list/server/?disk">
					<div class="vz-card-body">
						<div class="vz-card-header">
							<div>
								<p class="vz-card-label"><?= _("Disk") ?></p>
								<p class="vz-card-value">
									<?= humanize_usage_size($u["U_DISK"]) ?>
									<?= humanize_usage_measure($u["U_DISK"]) ?>
								</p>
							</div>
							<div class="vz-card-icon is-warning"><i class="fas fa-hard-drive"></i></div>
						</div>
						<?php if ($disk_pct !== null) { ?>
							<div class="vz-progress <?= $disk_pct > 85 ? "is-danger" : ($disk_pct > 70 ? "is-warning" : "") ?>">
								<span style="width: <?= (int) $disk_pct ?>%"></span>
							</div>
						<?php } ?>
						<p class="vz-card-meta"><?= $limit_label(humanize_usage_size($u["U_DISK"]) . " " . humanize_usage_measure($u["U_DISK"]), $u["DISK_QUOTA"] === "unlimited" ? "unlimited" : humanize_usage_size($u["DISK_QUOTA"]) . " " . humanize_usage_measure($u["DISK_QUOTA"])) ?></p>
					</div>
				</a>

				<div class="vz-card">
					<div class="vz-card-body">
						<div class="vz-card-header">
							<div>
								<p class="vz-card-label"><?= _("Server load") ?></p>
								<p class="vz-card-value" style="font-size: 1.15rem"><?= htmlspecialchars($load) ?></p>
							</div>
							<div class="vz-card-icon is-danger"><i class="fas fa-wave-square"></i></div>
						</div>
						<p class="vz-card-meta">1m / 5m / 15m</p>
					</div>
				</div>

				<div class="vz-card">
					<div class="vz-card-body">
						<div class="vz-card-header">
							<div>
								<p class="vz-card-label"><?= _("Uptime") ?></p>
								<p class="vz-card-value" style="font-size: 1.25rem"><?= htmlspecialchars($uptime) ?></p>
							</div>
							<div class="vz-card-icon is-success"><i class="fas fa-clock"></i></div>
						</div>
						<p class="vz-card-meta"><?= htmlspecialchars($hostname) ?></p>
					</div>
				</div>

				<a class="vz-card" href="/list/stats/">
					<div class="vz-card-body">
						<div class="vz-card-header">
							<div>
								<p class="vz-card-label"><?= _("Bandwidth") ?></p>
								<p class="vz-card-value">
									<?= humanize_usage_size($u["U_BANDWIDTH"]) ?>
									<?= humanize_usage_measure($u["U_BANDWIDTH"]) ?>
								</p>
							</div>
							<div class="vz-card-icon is-info"><i class="fas fa-right-left"></i></div>
						</div>
						<?php if ($bw_pct !== null) { ?>
							<div class="vz-progress"><span style="width: <?= (int) $bw_pct ?>%"></span></div>
						<?php } ?>
						<canvas class="vz-card-chart js-spark-bw" height="40"></canvas>
					</div>
				</a>
			</div>
		</section>

		<section class="vz-dashboard-section">
			<h2 class="vz-section-title"><?= _("Resources") ?></h2>
			<div class="vz-dash-grid">
				<a class="vz-card" href="/list/web/">
					<div class="vz-card-body">
						<div class="vz-card-header">
							<div>
								<p class="vz-card-label"><?= _("Web domains") ?></p>
								<p class="vz-card-value"><?= htmlspecialchars($u["U_WEB_DOMAINS"] ?? "0") ?></p>
							</div>
							<div class="vz-card-icon"><i class="fas fa-globe"></i></div>
						</div>
						<p class="vz-card-meta">
							<?= $limit_label($u["U_WEB_DOMAINS"] ?? 0, $u["WEB_DOMAINS"] ?? "0") ?>
							· <?= _("Aliases") ?>: <?= htmlspecialchars($u["U_WEB_ALIASES"] ?? "0") ?>
						</p>
					</div>
				</a>

				<a class="vz-card" href="/list/mail/">
					<div class="vz-card-body">
						<div class="vz-card-header">
							<div>
								<p class="vz-card-label"><?= _("Email accounts") ?></p>
								<p class="vz-card-value"><?= htmlspecialchars($u["U_MAIL_ACCOUNTS"] ?? "0") ?></p>
							</div>
							<div class="vz-card-icon is-warning"><i class="fas fa-envelope"></i></div>
						</div>
						<p class="vz-card-meta"><?= _("Domains") ?>: <?= htmlspecialchars($u["U_MAIL_DOMAINS"] ?? "0") ?></p>
					</div>
				</a>

				<a class="vz-card" href="/list/db/">
					<div class="vz-card-body">
						<div class="vz-card-header">
							<div>
								<p class="vz-card-label"><?= _("Databases") ?></p>
								<p class="vz-card-value"><?= htmlspecialchars($u["U_DATABASES"] ?? "0") ?></p>
							</div>
							<div class="vz-card-icon"><i class="fas fa-database"></i></div>
						</div>
						<p class="vz-card-meta"><?= $limit_label($u["U_DATABASES"] ?? 0, $u["DATABASES"] ?? "0") ?></p>
					</div>
				</a>

				<a class="vz-card" href="/list/apps/">
					<div class="vz-card-body">
						<div class="vz-card-header">
							<div>
								<p class="vz-card-label"><?= _("Applications") ?></p>
								<p class="vz-card-value"><?= (int) (($vz_app_counts["python"] ?? 0) + ($vz_app_counts["nodejs"] ?? 0)) ?></p>
							</div>
							<div class="vz-card-icon is-info"><i class="fas fa-cubes"></i></div>
						</div>
						<p class="vz-card-meta">
							Python <?= (int) ($vz_app_counts["python"] ?? 0) ?>
							· Node.js <?= (int) ($vz_app_counts["nodejs"] ?? 0) ?>
							<?php if (is_array($php_versions) && count($php_versions)) { ?>
								· PHP <?= htmlspecialchars(implode(", ", $php_versions)) ?>
							<?php } ?>
						</p>
					</div>
				</a>

				<a class="vz-card" href="/list/backup/">
					<div class="vz-card-body">
						<div class="vz-card-header">
							<div>
								<p class="vz-card-label"><?= _("Backups") ?></p>
								<p class="vz-card-value"><?= htmlspecialchars($u["U_BACKUPS"] ?? "0") ?></p>
							</div>
							<div class="vz-card-icon"><i class="fas fa-cloud-arrow-up"></i></div>
						</div>
						<p class="vz-card-meta"><?= $limit_label($u["U_BACKUPS"] ?? 0, $u["BACKUPS"] ?? "0") ?></p>
					</div>
				</a>

				<a class="vz-card" href="/list/web/">
					<div class="vz-card-body">
						<div class="vz-card-header">
							<div>
								<p class="vz-card-label"><?= _("SSL certificates") ?></p>
								<p class="vz-card-value"><?= htmlspecialchars($u["U_WEB_SSL"] ?? "0") ?></p>
							</div>
							<div class="vz-card-icon is-success"><i class="fas fa-lock"></i></div>
						</div>
						<p class="vz-card-meta"><?= _("Let's Encrypt & custom certs") ?></p>
					</div>
				</a>

				<a class="vz-card" href="/list/cron/">
					<div class="vz-card-body">
						<div class="vz-card-header">
							<div>
								<p class="vz-card-label"><?= _("Cron jobs") ?></p>
								<p class="vz-card-value"><?= htmlspecialchars($u["U_CRON_JOBS"] ?? "0") ?></p>
							</div>
							<div class="vz-card-icon is-danger"><i class="fas fa-clock"></i></div>
						</div>
						<p class="vz-card-meta"><?= $limit_label($u["U_CRON_JOBS"] ?? 0, $u["CRON_JOBS"] ?? "0") ?></p>
					</div>
				</a>

				<a class="vz-card is-wide" href="/list/dns/">
					<div class="vz-card-body">
						<div class="vz-card-header">
							<div>
								<p class="vz-card-label"><?= _("DNS zones") ?></p>
								<p class="vz-card-value"><?= htmlspecialchars($u["U_DNS_DOMAINS"] ?? "0") ?></p>
							</div>
							<div class="vz-card-icon is-info"><i class="fas fa-sitemap"></i></div>
						</div>
						<p class="vz-card-meta"><?= _("Records") ?>: <?= htmlspecialchars($u["U_DNS_RECORDS"] ?? "0") ?> · <?= $limit_label($u["U_DNS_DOMAINS"] ?? 0, $u["DNS_DOMAINS"] ?? "0") ?></p>
					</div>
				</a>

				<?php if ($_SESSION["userContext"] === "admin" && empty($_SESSION["look"])) { ?>
					<a class="vz-card is-wide" href="/list/user/">
						<div class="vz-card-body">
							<div class="vz-card-header">
								<div>
									<p class="vz-card-label"><?= _("Users") ?></p>
									<p class="vz-card-value"><?= htmlspecialchars($u["U_USERS"] ?? "0") ?></p>
								</div>
								<div class="vz-card-icon"><i class="fas fa-users"></i></div>
							</div>
							<p class="vz-card-meta"><?= _("Suspended") ?>: <?= htmlspecialchars($u["SUSPENDED_USERS"] ?? "0") ?></p>
						</div>
					</a>
				<?php } ?>
			</div>
		</section>
	</div>
</div>
