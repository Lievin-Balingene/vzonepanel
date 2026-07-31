<?php
$TAB = "DASHBOARD";

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// System info (uptime / load) — available to all logged-in users for dashboard cards
exec(HESTIA_CMD . "v-list-sys-info json", $output, $return_var);
$sys = json_decode(implode("", $output), true);
unset($output);

$php_versions = [];
if ($_SESSION["userContext"] === "admin") {
	exec(HESTIA_CMD . "v-list-sys-php json", $output, $return_var);
	$php_versions = json_decode(implode("", $output), true) ?: [];
	unset($output);
}

$vz_app_counts = ["python" => 0, "nodejs" => 0];
exec(HESTIA_CMD . "v-list-web-app " . $user . " json", $output, $return_var);
$vz_apps = json_decode(implode("", $output), true);
unset($output);
if (is_array($vz_apps)) {
	foreach ($vz_apps as $app) {
		$type = strtolower($app["TYPE"] ?? "");
		if (in_array($type, ["django", "flask", "fastapi", "python"], true)) {
			$vz_app_counts["python"]++;
		}
		if ($type === "nodejs") {
			$vz_app_counts["nodejs"]++;
		}
	}
}

// Memory / CPU snapshots for admin (best-effort, Linux /proc)
$vz_metrics = [
	"cpu_percent" => null,
	"mem_percent" => null,
	"mem_used" => null,
	"mem_total" => null,
	"disk_root_percent" => null,
];

if (is_readable("/proc/meminfo")) {
	$meminfo = @file_get_contents("/proc/meminfo");
	if (
		$meminfo &&
		preg_match("/MemTotal:\s+(\d+)/", $meminfo, $mt) &&
		preg_match("/MemAvailable:\s+(\d+)/", $meminfo, $ma)
	) {
		$total = (int) $mt[1];
		$avail = (int) $ma[1];
		$used = max(0, $total - $avail);
		$vz_metrics["mem_total"] = round($total / 1024);
		$vz_metrics["mem_used"] = round($used / 1024);
		$vz_metrics["mem_percent"] = $total > 0 ? round(($used / $total) * 100) : 0;
	}
}

if (is_readable("/proc/loadavg") && is_readable("/proc/cpuinfo")) {
	$load = @file_get_contents("/proc/loadavg");
	$cpuinfo = @file_get_contents("/proc/cpuinfo");
	$cores = max(1, substr_count((string) $cpuinfo, "processor"));
	if ($load) {
		$parts = explode(" ", trim($load));
		$load1 = (float) ($parts[0] ?? 0);
		$vz_metrics["cpu_percent"] = min(100, round(($load1 / $cores) * 100));
	}
}

if (function_exists("disk_total_space") && function_exists("disk_free_space")) {
	$total = @disk_total_space("/");
	$free = @disk_free_space("/");
	if ($total > 0) {
		$vz_metrics["disk_root_percent"] = round((($total - $free) / $total) * 100);
	}
}

render_page($user, $TAB, "list_dashboard");

$_SESSION["back"] = $_SERVER["REQUEST_URI"];
