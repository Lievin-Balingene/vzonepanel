<?php
$vz_is_admin = $_SESSION["userContext"] === "admin" && $_SESSION["look"] === "";
$vz_disk_used = humanize_usage_size($panel[$user]["U_DISK"]) . " " . humanize_usage_measure($panel[$user]["U_DISK"]);
$vz_disk_total = humanize_usage_size($panel[$user]["DISK_QUOTA"]) . " " . humanize_usage_measure($panel[$user]["DISK_QUOTA"]);
$vz_bw_used = humanize_usage_size($panel[$user]["U_BANDWIDTH"]) . " " . humanize_usage_measure($panel[$user]["U_BANDWIDTH"]);
$vz_bw_total = humanize_usage_size($panel[$user]["BANDWIDTH"]) . " " . humanize_usage_measure($panel[$user]["BANDWIDTH"]);

if ($TAB === "DASHBOARD") {
	$vz_page_label = _("Dashboard");
} elseif ($TAB === "WEB") {
	$vz_page_label = _("Web Hosting");
} elseif ($TAB === "DNS") {
	$vz_page_label = _("DNS");
} elseif ($TAB === "MAIL") {
	$vz_page_label = _("Emails");
} elseif ($TAB === "DB") {
	$vz_page_label = _("Databases");
} elseif ($TAB === "CRON") {
	$vz_page_label = _("Cron Jobs");
} elseif ($TAB === "BACKUP") {
	$vz_page_label = _("Backups");
} elseif ($TAB === "USER") {
	$vz_page_label = _("Users");
} elseif (in_array($TAB, ["SERVER", "IP", "RRD", "FIREWALL"], true)) {
	$vz_page_label = _("Monitoring");
} elseif ($TAB === "LOG") {
	$vz_page_label = _("Logs");
} elseif ($TAB === "STATS") {
	$vz_page_label = _("Statistics");
} elseif ($TAB === "PACKAGE") {
	$vz_page_label = _("Packages");
} elseif ($TAB === "TERMINAL") {
	$vz_page_label = _("Terminal");
} else {
	$vz_page_label = _(ucfirst(strtolower((string) $TAB)));
}
?>
<div id="token" token="<?= $_SESSION["token"] ?>"></div>

<header class="app-header" x-data="vzShell">
	<div
		class="vz-sidebar-backdrop"
		x-show="mobileOpen"
		x-transition.opacity
		x-on:click="mobileOpen = false"
		x-cloak
	></div>

	<aside class="vz-sidebar" aria-label="<?= _("Main navigation") ?>">
		<div class="vz-sidebar-brand">
			<a href="/list/dashboard/" title="<?= htmlentities($_SESSION["APP_NAME"] ?? "V-zone Panel") ?>">
				<img src="/images/logo.svg" alt="<?= htmlentities($_SESSION["APP_NAME"] ?? "V-zone Panel") ?>" width="34" height="34">
				<span class="vz-sidebar-brand-text"><span>V-zone</span> Panel</span>
			</a>
		</div>

		<nav class="vz-sidebar-nav">
			<div class="vz-nav-section">
				<span class="vz-nav-section-label"><?= _("Overview") ?></span>
				<ul class="vz-nav-list">
					<li>
						<a class="vz-nav-link <?= $TAB === "DASHBOARD" ? "active" : "" ?>" href="/list/dashboard/">
							<i class="fas fa-gauge-high"></i>
							<span><?= _("Dashboard") ?></span>
						</a>
					</li>
				</ul>
			</div>

			<div class="vz-nav-section">
				<span class="vz-nav-section-label"><?= _("Hosting") ?></span>
				<ul class="vz-nav-list">
					<?php if (!empty($_SESSION["WEB_SYSTEM"]) && $panel[$user]["WEB_DOMAINS"] != "0") { ?>
						<li>
							<a class="vz-nav-link <?= $TAB === "WEB" ? "active" : "" ?>" href="/list/web/">
								<i class="fas fa-globe"></i>
								<span><?= _("Web Hosting") ?></span>
								<span class="vz-nav-badge"><?= htmlspecialchars($panel[$user]["U_WEB_DOMAINS"]) ?></span>
							</a>
						</li>
					<?php } ?>

					<li>
						<a class="vz-nav-link <?= $TAB === "APPS" ? "active" : "" ?>" href="/list/apps/">
							<i class="fas fa-cubes"></i>
							<span><?= _("Applications") ?></span>
						</a>
					</li>

					<?php if (!empty($_SESSION["MAIL_SYSTEM"]) && $panel[$user]["MAIL_DOMAINS"] != "0") { ?>
						<li>
							<a class="vz-nav-link <?= $TAB === "MAIL" ? "active" : "" ?>" href="/list/mail/">
								<i class="fas fa-envelope"></i>
								<span><?= _("Emails") ?></span>
								<span class="vz-nav-badge"><?= htmlspecialchars($panel[$user]["U_MAIL_ACCOUNTS"]) ?></span>
							</a>
						</li>
					<?php } ?>

					<?php if (!empty($_SESSION["DB_SYSTEM"]) && $panel[$user]["DATABASES"] != "0") { ?>
						<li>
							<a class="vz-nav-link <?= $TAB === "DB" ? "active" : "" ?>" href="/list/db/">
								<i class="fas fa-database"></i>
								<span><?= _("Databases") ?></span>
								<span class="vz-nav-badge"><?= htmlspecialchars($panel[$user]["U_DATABASES"]) ?></span>
							</a>
						</li>
					<?php } ?>

					<?php if (!empty($_SESSION["DNS_SYSTEM"]) && $panel[$user]["DNS_DOMAINS"] != "0") { ?>
						<li>
							<a class="vz-nav-link <?= $TAB === "DNS" ? "active" : "" ?>" href="/list/dns/">
								<i class="fas fa-sitemap"></i>
								<span><?= _("DNS") ?></span>
								<span class="vz-nav-badge"><?= htmlspecialchars($panel[$user]["U_DNS_DOMAINS"]) ?></span>
							</a>
						</li>
					<?php } ?>

					<?php if (!empty($_SESSION["WEB_SYSTEM"]) && $panel[$user]["WEB_DOMAINS"] != "0") { ?>
						<li>
							<a class="vz-nav-link" href="/list/web/">
								<i class="fas fa-lock"></i>
								<span><?= _("SSL") ?></span>
								<span class="vz-nav-badge"><?= htmlspecialchars($panel[$user]["U_WEB_SSL"] ?? "0") ?></span>
							</a>
						</li>
					<?php } ?>
				</ul>
			</div>

			<div class="vz-nav-section">
				<span class="vz-nav-section-label"><?= _("Operations") ?></span>
				<ul class="vz-nav-list">
					<?php if (
     	!empty($_SESSION["BACKUP_SYSTEM"]) &&
     	($panel[$user]["BACKUPS"] != "0" ||
     		$panel[$user]["U_BACKUPS"] != "0" ||
     		($panel[$user]["BACKUPS_INCREMENTAL"] ?? "") == "yes")
     ) { ?>
						<li>
							<a class="vz-nav-link <?= $TAB === "BACKUP" ? "active" : "" ?>" href="/list/backup/">
								<i class="fas fa-cloud-arrow-up"></i>
								<span><?= _("Backups") ?></span>
								<span class="vz-nav-badge"><?= htmlspecialchars($panel[$user]["U_BACKUPS"]) ?></span>
							</a>
						</li>
					<?php } ?>

					<?php if (!empty($_SESSION["CRON_SYSTEM"]) && $panel[$user]["CRON_JOBS"] != "0") { ?>
						<li>
							<a class="vz-nav-link <?= $TAB === "CRON" ? "active" : "" ?>" href="/list/cron/">
								<i class="fas fa-clock"></i>
								<span><?= _("Cron Jobs") ?></span>
								<span class="vz-nav-badge"><?= htmlspecialchars($panel[$user]["U_CRON_JOBS"]) ?></span>
							</a>
						</li>
					<?php } ?>

					<li>
						<a class="vz-nav-link <?= $TAB === "LOG" ? "active" : "" ?>" href="/list/log/">
							<i class="fas fa-scroll"></i>
							<span><?= _("Logs") ?></span>
						</a>
					</li>

					<?php if (
     	($vz_is_admin || ($_SESSION["userContext"] === "admin" && $_SESSION["POLICY_SYSTEM_HIDE_SERVICES"] !== "yes")) &&
     	!($_SESSION["userContext"] === "admin" && $_SESSION["look"] !== "")
     ) { ?>
						<li>
							<a class="vz-nav-link <?= in_array($TAB, ["SERVER", "IP", "RRD", "FIREWALL"], true) ? "active" : "" ?>" href="/list/server/">
								<i class="fas fa-heart-pulse"></i>
								<span><?= _("Monitoring") ?></span>
							</a>
						</li>
					<?php } ?>
				</ul>
			</div>

			<div class="vz-nav-section">
				<span class="vz-nav-section-label"><?= _("Account") ?></span>
				<ul class="vz-nav-list">
					<?php if ($vz_is_admin) { ?>
						<li>
							<a class="vz-nav-link <?= in_array($TAB, ["USER", "PACKAGE"], true) ? "active" : "" ?>" href="/list/user/">
								<i class="fas fa-users"></i>
								<span><?= _("Users") ?></span>
								<span class="vz-nav-badge"><?= htmlspecialchars($panel[$user]["U_USERS"] ?? "0") ?></span>
							</a>
						</li>
					<?php } ?>

					<li>
						<a class="vz-nav-link" href="/edit/user/?user=<?= urlencode($user) ?>&token=<?= $_SESSION["token"] ?>">
							<i class="fas fa-sliders"></i>
							<span><?= _("Settings") ?></span>
						</a>
					</li>

					<?php if (!empty($_SESSION["FILE_MANAGER"]) && $_SESSION["FILE_MANAGER"] === "true") { ?>
						<?php if (
       	!(
       		$_SESSION["userContext"] === "admin" &&
       		$_SESSION["look"] === "admin" &&
       		($_SESSION["POLICY_SYSTEM_PROTECTED_ADMIN"] ?? "") === "yes"
       	)
       ) { ?>
							<li>
								<a class="vz-nav-link <?= $TAB === "FM" ? "active" : "" ?>" href="/fm/">
									<i class="fas fa-folder-open"></i>
									<span><?= _("File manager") ?></span>
								</a>
							</li>
						<?php } ?>
					<?php } ?>

					<?php if (
     	!empty($_SESSION["WEB_TERMINAL"]) &&
     	$_SESSION["WEB_TERMINAL"] === "true" &&
     	($_SESSION["login_shell"] ?? "") !== "nologin"
     ) { ?>
						<?php if (
       	!(
       		$_SESSION["userContext"] === "admin" &&
       		$_SESSION["look"] === "admin" &&
       		($_SESSION["POLICY_SYSTEM_PROTECTED_ADMIN"] ?? "") === "yes"
       	)
       ) { ?>
							<li>
								<a class="vz-nav-link <?= $TAB === "TERMINAL" ? "active" : "" ?>" href="/list/terminal/">
									<i class="fas fa-terminal"></i>
									<span><?= _("Terminal") ?></span>
								</a>
							</li>
						<?php } ?>
					<?php } ?>
				</ul>
			</div>
		</nav>
	</aside>

	<div class="vz-topbar">
		<div class="vz-topbar-left">
			<button type="button" class="vz-icon-btn vz-btn-mobile-menu" x-on:click="mobileOpen = !mobileOpen" title="<?= _("Menu") ?>">
				<i class="fas fa-bars"></i>
			</button>
			<button type="button" class="vz-icon-btn vz-btn-collapse-sidebar" x-on:click="toggleCollapsed()" title="<?= _("Toggle sidebar") ?>">
				<i class="fas fa-bars-staggered"></i>
			</button>
		</div>

		<div class="vz-topbar-center">
			<form class="vz-search" action="/search/" method="get" role="search">
				<i class="fas fa-magnifying-glass"></i>
				<input
					type="search"
					name="q"
					class="js-search-input"
					placeholder="<?= _("Search domains, users, databases…") ?>"
					aria-label="<?= _("Global search") ?>"
				>
				<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
			</form>
		</div>

		<div class="vz-topbar-right">
			<div class="vz-usage-pills">
				<span class="vz-pill" title="<?= _("Disk") ?>">
					<i class="fas fa-hard-drive"></i>
					<strong><?= htmlspecialchars($vz_disk_used) ?></strong>
					/ <?= htmlspecialchars($vz_disk_total) ?>
				</span>
				<span class="vz-pill" title="<?= _("Bandwidth") ?>">
					<i class="fas fa-right-left"></i>
					<strong><?= htmlspecialchars($vz_bw_used) ?></strong>
					/ <?= htmlspecialchars($vz_bw_total) ?>
				</span>
			</div>

			<button type="button" class="vz-icon-btn" x-on:click="toggleTheme()" title="<?= _("Toggle theme") ?>">
				<i class="fas" x-bind:class="theme === 'dark' ? 'fa-sun' : 'fa-moon'"></i>
			</button>

			<?php
   $impersonatingAdmin = $_SESSION["userContext"] === "admin" && $_SESSION["look"] !== "" && $user == "admin";
   if (!$impersonatingAdmin) {
   	?>
				<div x-data="notifications" class="top-bar-notifications">
					<button
						x-on:click="toggle()"
						x-bind:class="open && 'active'"
						class="vz-icon-btn"
						type="button"
						title="<?= _("Notifications") ?>"
					>
						<i
							x-bind:class="{
								'animate__animated animate__swing icon-orange': (!initialized && <?= $panel[$user]["NOTIFICATIONS"] == "yes" ? "true" : "false" ?>) || notifications.length != 0,
								'fas fa-bell': true
							}"
						></i>
					</button>
					<div x-cloak x-show="open" x-on:click.outside="open = false" class="top-bar-notifications-panel">
						<template x-if="!initialized">
							<div class="top-bar-notifications-empty">
								<i class="fas fa-circle-notch fa-spin icon-dim"></i>
								<p><?= _("Loading...") ?></p>
							</div>
						</template>
						<template x-if="initialized && notifications.length == 0">
							<div class="top-bar-notifications-empty">
								<i class="fas fa-bell-slash icon-dim"></i>
								<p><?= _("No notifications") ?></p>
							</div>
						</template>
						<template x-if="initialized && notifications.length > 0">
							<ul>
								<template x-for="notification in notifications" :key="notification.ID">
									<li
										x-bind:id="`notification-${notification.ID}`"
										x-bind:class="notification.ACK && 'unseen'"
										class="top-bar-notification-item"
										x-data="{ open: true }"
										x-show="open"
										x-collapse
									>
										<div class="top-bar-notification-inner">
											<div class="top-bar-notification-header">
												<p x-text="notification.TOPIC" class="top-bar-notification-title"></p>
												<button
													x-on:click="open = false; setTimeout(() => remove(notification.ID), 300);"
													type="button"
													class="top-bar-notification-delete"
													title="<?= _("Delete notification") ?>"
												>
													<i class="fas fa-xmark"></i>
												</button>
											</div>
											<div class="top-bar-notification-content" x-html="notification.NOTICE"></div>
											<p class="top-bar-notification-timestamp">
												<time
													:datetime="`${notification.TIMESTAMP_ISO}`"
													x-bind:title="`${notification.TIMESTAMP_TITLE}`"
													x-text="`${notification.TIMESTAMP_TEXT}`"
												></time>
											</p>
										</div>
									</li>
								</template>
							</ul>
						</template>
						<template x-if="initialized && notifications.length > 2">
							<button x-on:click="removeAll()" type="button" class="top-bar-notifications-delete-all">
								<i class="fas fa-check"></i>
								<?= _("Delete all notifications") ?>
							</button>
						</template>
					</div>
				</div>
			<?php
   } ?>

			<div class="vz-profile" x-data="{ open: false }">
				<button type="button" class="vz-profile-btn" x-on:click="open = !open">
					<span class="vz-avatar"><i class="fas fa-user"></i></span>
					<span class="vz-profile-meta">
						<strong><?= htmlspecialchars($user) ?></strong>
						<small><?= htmlspecialchars($panel[$user]["NAME"] ?? "") ?></small>
					</span>
					<i class="fas fa-chevron-down" style="font-size: 0.7rem; opacity: 0.6"></i>
				</button>
				<div class="vz-dropdown-panel" x-cloak x-show="open" x-on:click.outside="open = false">
					<a href="/edit/user/?user=<?= urlencode($user) ?>&token=<?= $_SESSION["token"] ?>">
						<i class="fas fa-circle-user"></i><?= _("Profile") ?>
					</a>
					<a href="/list/stats/">
						<i class="fas fa-chart-line"></i><?= _("Statistics") ?>
					</a>
					<?php if (($_SESSION["HIDE_DOCS"] ?? "") !== "yes") { ?>
						<a href="https://docs.hestiacp.com/" target="_blank" rel="noopener">
							<i class="fas fa-circle-question"></i><?= _("Help") ?>
						</a>
					<?php } ?>
					<a href="/logout/?token=<?= $_SESSION["token"] ?>">
						<i class="fas fa-right-from-bracket"></i><?= _("Log out") ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</header>

<main class="app-content">
	<div class="container">
		<nav class="vz-breadcrumb" aria-label="<?= _("Breadcrumb") ?>">
			<a href="/list/dashboard/"><?= _("Home") ?></a>
			<span class="vz-breadcrumb-sep">/</span>
			<span class="vz-breadcrumb-current"><?= htmlspecialchars($vz_page_label) ?></span>
		</nav>
	</div>
