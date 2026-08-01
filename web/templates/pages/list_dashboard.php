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
$token = $_SESSION["token"] ?? "";
$vz_is_admin = ($_SESSION["userContext"] ?? "") === "admin" && empty($_SESSION["look"]);
$can_services =
	($vz_is_admin ||
		(($_SESSION["userContext"] ?? "") === "admin" &&
			($_SESSION["POLICY_SYSTEM_HIDE_SERVICES"] ?? "") !== "yes")) &&
	!(($_SESSION["userContext"] ?? "") === "admin" && ($_SESSION["look"] ?? "") !== "");

$tool = function ($href, $icon, $label, $keys = "") {
	return [
		"href" => $href,
		"icon" => $icon,
		"label" => $label,
		"keys" => $keys,
	];
};

$vz_section_icons = [
	_("Email") => "fa-envelope",
	_("Files") => "fa-folder-open",
	_("Databases") => "fa-database",
	_("Domains") => "fa-globe",
	_("Metrics") => "fa-chart-line",
	_("Security") => "fa-shield-halved",
	_("Software") => "fa-cubes",
	_("Advanced") => "fa-gears",
	_("Preferences") => "fa-sliders",
	_("Server") => "fa-server",
];

$vz_tools = [];

// —— Email (cPanel: Email) ——
if (!empty($_SESSION["MAIL_SYSTEM"]) && ($u["MAIL_DOMAINS"] ?? "0") != "0") {
	$items = [
		$tool("/list/mail/", "fa-envelope", _("Email Accounts"), "email mail mailbox account webmail"),
		$tool("/add/mail/", "fa-plus", _("Create Mail Domain"), "add mail email domain"),
		$tool("/list/mail/", "fa-inbox", _("Mail Domains"), "mail domain mx catchall"),
		$tool("/list/log/", "fa-envelope-open-text", _("Mail Delivery"), "mail log delivery bounce"),
	];
	$webmail_alias = $_SESSION["WEBMAIL_ALIAS"] ?? "webmail";
	if (!empty($hostname)) {
		$items[] = $tool(
			"https://" . $webmail_alias . "." . $hostname . "/",
			"fa-at",
			_("Webmail"),
			"webmail roundcube",
		);
	}
	$vz_tools[] = ["cat" => _("Email"), "items" => $items];
}

// —— Files (cPanel: Files) ——
$files = ["cat" => _("Files"), "items" => []];
if (!empty($_SESSION["FILE_MANAGER"]) && $_SESSION["FILE_MANAGER"] === "true") {
	$files["items"][] = $tool("/fm/", "fa-folder-open", _("File Manager"), "file manager files browse");
}
if (!empty($_SESSION["BACKUP_SYSTEM"]) && (($u["BACKUPS"] ?? "0") != "0" || ($u["U_BACKUPS"] ?? "0") != "0")) {
	$files["items"][] = $tool("/list/backup/", "fa-cloud-arrow-up", _("Backup"), "backup restore download");
	$files["items"][] = $tool(
		"/list/backup/exclusions/",
		"fa-folder-minus",
		_("Backup Exclusions"),
		"backup exclusion skip",
	);
	if (($u["BACKUPS_INCREMENTAL"] ?? "") === "yes") {
		$files["items"][] = $tool(
			"/list/backup/incremental/",
			"fa-vault",
			_("Incremental Backups"),
			"incremental backup snapshot",
		);
	}
}
if (!empty($_SESSION["WEB_SYSTEM"]) && ($u["WEB_DOMAINS"] ?? "0") != "0") {
	$files["items"][] = $tool("/list/web/", "fa-network-wired", _("FTP Accounts"), "ftp sftp file transfer");
}
$files["items"][] = $tool("/list/stats/", "fa-hard-drive", _("Disk Usage"), "disk usage storage quota");
if (count($files["items"])) {
	$vz_tools[] = $files;
}

// —— Databases (cPanel: Databases) ——
if (!empty($_SESSION["DB_SYSTEM"]) && ($u["DATABASES"] ?? "0") != "0") {
	$items = [];
	if (strpos((string) $_SESSION["DB_SYSTEM"], "mysql") !== false) {
		$items[] = $tool("/phpmyadmin/", "fa-table", "phpMyAdmin", "phpmyadmin mysql mariadb");
	}
	if (strpos((string) $_SESSION["DB_SYSTEM"], "pgsql") !== false) {
		$items[] = $tool("/phppgadmin/", "fa-table", "phpPgAdmin", "phppgadmin postgres postgresql");
	}
	$items[] = $tool("/list/db/", "fa-database", _("Manage Databases"), "database mysql pgsql postgres");
	$items[] = $tool("/add/db/", "fa-plus", _("Create Database"), "add create database wizard");
	$vz_tools[] = ["cat" => _("Databases"), "items" => $items];
}

// —— Domains (cPanel: Domains + Zone Editor) ——
if (
	(!empty($_SESSION["WEB_SYSTEM"]) && ($u["WEB_DOMAINS"] ?? "0") != "0") ||
	(!empty($_SESSION["DNS_SYSTEM"]) && ($u["DNS_DOMAINS"] ?? "0") != "0")
) {
	$items = [];
	if (!empty($_SESSION["WEB_SYSTEM"]) && ($u["WEB_DOMAINS"] ?? "0") != "0") {
		$items[] = $tool("/list/web/", "fa-globe", _("Domains"), "web domain site hosting addon");
		$items[] = $tool("/add/web/", "fa-plus", _("Create Domain"), "add create domain subdomain");
		$items[] = $tool("/list/web/", "fa-lock", _("SSL/TLS Status"), "ssl tls certificate letsencrypt https");
		$items[] = $tool("/list/web/", "fa-arrow-right", _("Redirects"), "redirect 301 302 url");
	}
	if (!empty($_SESSION["DNS_SYSTEM"]) && ($u["DNS_DOMAINS"] ?? "0") != "0") {
		$items[] = $tool("/list/dns/", "fa-sitemap", _("Zone Editor"), "dns zone record mx a cname txt");
		$items[] = $tool("/add/dns/", "fa-plus", _("Create DNS Zone"), "add dns zone");
	}
	$vz_tools[] = ["cat" => _("Domains"), "items" => $items];
}

// —— Metrics (cPanel: Metrics) ——
$metrics = [
	"cat" => _("Metrics"),
	"items" => [
		$tool("/list/stats/", "fa-chart-column", _("Statistics"), "stats metrics bandwidth visitors"),
		$tool("/list/log/", "fa-scroll", _("Errors / Logs"), "log error history debug"),
		$tool("/list/log/auth/", "fa-clock-rotate-left", _("Login History"), "auth login history security"),
	],
];
if (!empty($_SESSION["WEB_SYSTEM"]) && ($u["WEB_DOMAINS"] ?? "0") != "0") {
	$metrics["items"][] = $tool(
		"/list/web/",
		"fa-chart-area",
		_("Raw Access / Web Logs"),
		"access log apache nginx visitors",
	);
}
if ($can_services) {
	$metrics["items"][] = $tool("/list/rrd/", "fa-heart-pulse", _("Resource Usage"), "rrd cpu ram load performance");
	$metrics["items"][] = $tool("/list/server/", "fa-server", _("Server Status"), "monitor service status");
}
$vz_tools[] = $metrics;

// —— Security (cPanel: Security) ——
$security = [
	"cat" => _("Security"),
	"items" => [
		$tool(
			"/edit/user/?user=" . urlencode($user) . "&token=" . urlencode($token),
			"fa-user-shield",
			_("Password & Security"),
			"password 2fa two factor security",
		),
		$tool("/list/key/", "fa-key", _("SSH Keys"), "ssh key public private"),
		$tool("/list/access-key/", "fa-lock", _("API Access Keys"), "api access key token"),
		$tool("/list/log/auth/", "fa-user-lock", _("Login Attempts"), "auth login fail brute"),
	],
];
if (!empty($_SESSION["WEB_SYSTEM"]) && ($u["WEB_DOMAINS"] ?? "0") != "0") {
	$security["items"][] = $tool(
		"/list/web/",
		"fa-certificate",
		_("SSL/TLS"),
		"ssl tls certificate letsencrypt",
	);
}
if ($can_services) {
	$security["items"][] = $tool("/list/firewall/", "fa-shield-halved", _("Firewall"), "firewall iptables ban");
	$security["items"][] = $tool(
		"/list/firewall/banlist/",
		"fa-ban",
		_("IP Ban List"),
		"banlist fail2ban blocked ip",
	);
}
$vz_tools[] = $security;

// —— Software (cPanel: Software) ——
$software = ["cat" => _("Software"), "items" => []];
if (!empty($_SESSION["WEB_SYSTEM"]) && ($u["WEB_DOMAINS"] ?? "0") != "0") {
	$software["items"][] = $tool(
		"/list/apps/",
		"fa-cubes",
		_("Application Manager"),
		"app php python node django flask fastapi wordpress express softaculous",
	);
	$software["items"][] = $tool(
		"/list/web/",
		"fa-code",
		_("MultiPHP / PHP Settings"),
		"php version extensions multiphp",
	);
}
if (!empty($_SESSION["CRON_SYSTEM"]) && ($u["CRON_JOBS"] ?? "0") != "0") {
	$software["items"][] = $tool("/list/cron/", "fa-clock", _("Cron Jobs"), "cron job schedule task");
}
if (
	!empty($_SESSION["WEB_TERMINAL"]) &&
	$_SESSION["WEB_TERMINAL"] === "true" &&
	($_SESSION["login_shell"] ?? "") !== "nologin"
) {
	$software["items"][] = $tool("/list/terminal/", "fa-terminal", _("Terminal"), "ssh terminal shell console");
}
if (count($software["items"])) {
	$vz_tools[] = $software;
}

// —— Advanced (cPanel: Advanced) ——
$advanced = [
	"cat" => _("Advanced"),
	"items" => [
		$tool("/list/notifications/", "fa-bell", _("Notifications"), "notification alert"),
	],
];
if (!empty($_SESSION["CRON_SYSTEM"]) && ($u["CRON_JOBS"] ?? "0") != "0") {
	$advanced["items"][] = $tool("/add/cron/", "fa-plus", _("Add Cron Job"), "add cron schedule");
}
if (
	!empty($_SESSION["WEB_TERMINAL"]) &&
	$_SESSION["WEB_TERMINAL"] === "true" &&
	($_SESSION["login_shell"] ?? "") !== "nologin"
) {
	$advanced["items"][] = $tool("/list/terminal/", "fa-terminal", _("Terminal"), "terminal console");
}
$advanced["items"][] = $tool("/list/access-key/", "fa-key", _("Manage API Keys"), "api key advanced");
$vz_tools[] = $advanced;

// —— Preferences (cPanel: Preferences) ——
$prefs = [
	"cat" => _("Preferences"),
	"items" => [
		$tool(
			"/edit/user/?user=" . urlencode($user) . "&token=" . urlencode($token),
			"fa-circle-user",
			_("User Settings"),
			"profile preferences language theme",
		),
		$tool("/list/stats/", "fa-chart-pie", _("Account Statistics"), "stats usage"),
		$tool("/list/log/", "fa-clipboard-list", _("User History"), "log activity history"),
	],
];
if ($vz_is_admin) {
	$prefs["items"][] = $tool("/list/package/", "fa-box", _("Packages"), "package plan quota");
}
$vz_tools[] = $prefs;

// —— Server (admin / reseller monitoring) ——
if ($can_services) {
	$server_items = [
		$tool("/list/server/", "fa-heart-pulse", _("Services"), "server service nginx apache mysql"),
		$tool("/list/rrd/", "fa-chart-area", _("Performance Graphs"), "rrd graph cpu ram network"),
		$tool("/list/ip/", "fa-network-wired", _("IP Addresses"), "ip address network"),
		$tool("/list/updates/", "fa-arrows-rotate", _("Updates"), "update upgrade package"),
		$tool(
			"/list/log/?user=system&token=" . urlencode($token),
			"fa-scroll",
			_("System Logs"),
			"system log",
		),
	];
	if ($vz_is_admin) {
		array_unshift(
			$server_items,
			$tool("/list/user/", "fa-users", _("User Accounts"), "user account package"),
			$tool("/edit/server/", "fa-screwdriver-wrench", _("Configure Server"), "server config settings"),
		);
	}
	$vz_tools[] = ["cat" => _("Server"), "items" => $server_items];
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
							<?php foreach ($section["items"] as $item) { ?>
								<a
									class="vz-tool"
									href="<?= htmlspecialchars($item["href"]) ?>"
									data-keys="<?= htmlspecialchars(strtolower($item["label"] . " " . ($item["keys"] ?? ""))) ?>"
									x-show="toolVisible($el)"
									<?= str_starts_with($item["href"], "http") ? 'target="_blank" rel="noopener"' : "" ?>
								>
									<span class="vz-tool-icon"><i class="fas <?= htmlspecialchars($item["icon"]) ?>"></i></span>
									<span class="vz-tool-label"><?= htmlspecialchars($item["label"]) ?></span>
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
				<h2><?= _("Statistics") ?></h2>
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
						<span><?= _("Email accounts") ?></span>
						<strong><?= $limit_label($u["U_MAIL_ACCOUNTS"] ?? 0, $u["MAIL_ACCOUNTS"] ?? "0") ?></strong>
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
