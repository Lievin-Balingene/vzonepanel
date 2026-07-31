<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

$TAB = "APPS";
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Optional look-as user
if ($_SESSION["user"] === "admin" && !empty($_GET["user"])) {
	$user = quoteshellarg($_GET["user"]);
	$user_plain = $_GET["user"];
}

$token = $_SESSION["token"];

// Restart / delete actions
if (!empty($_GET["action"]) && !empty($_GET["domain"])) {
	verify_csrf($_GET);
	$domain = quoteshellarg($_GET["domain"]);
	if ($_GET["action"] === "restart") {
		exec(HESTIA_CMD . "v-restart-web-app " . $user . " " . $domain, $output, $return_var);
		check_return_code($return_var, $output);
		$_SESSION["ok_msg"] = _("Application restarted.");
	}
	if ($_GET["action"] === "delete") {
		exec(HESTIA_CMD . "v-delete-web-app " . $user . " " . $domain, $output, $return_var);
		check_return_code($return_var, $output);
		$_SESSION["ok_msg"] = _("Application deleted.");
	}
	header("Location: /list/apps/");
	exit();
}

exec(HESTIA_CMD . "v-list-web-app " . $user . " json", $output, $return_var);
$data = json_decode(implode("", $output), true);
if (!is_array($data)) {
	$data = [];
}
unset($output);

// Domains available for new apps
exec(HESTIA_CMD . "v-list-web-domains " . $user . " json", $output, $return_var);
$domains = json_decode(implode("", $output), true) ?: [];
unset($output);

render_page($user, $TAB, "list_apps");
$_SESSION["back"] = $_SERVER["REQUEST_URI"];
