<link rel="icon" href="/images/favicon.svg" type="image/svg+xml">
<link rel="alternate icon" href="/images/logo.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/images/logo.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/themes/default.min.css?<?= JS_LATEST_UPDATE ?>">
<script>
	(function () {
		try {
			var t = localStorage.getItem('vz-theme');
			if (t === 'dark') {
				document.documentElement.dataset.theme = 'dark';
				document.documentElement.classList.add('theme-dark');
			}
		} catch (e) {}
	})();
</script>

<?php
$selected_theme = !empty($_SESSION["userTheme"]) ? $_SESSION["userTheme"] : $_SESSION["THEME"];
// Load non-default theme
if ($selected_theme !== "default") {
	// Load HestiaCP-shipped themes (minified, updated/overwritten with updates) - ($HESTIA/web/css/themes/*.min.css)
	$non_default_theme_path = $_SERVER["HESTIA"] . "/web/css/themes/" . $selected_theme . ".min.css";
	if (file_exists($non_default_theme_path)) {
		echo '<link rel="stylesheet" href="/css/themes/' . $selected_theme . ".min.css?" . JS_LATEST_UPDATE . '">';
	}
	// Load custom theme files ($HESTIA/web/css/themes/custom/*.css)
	else {
		$custom_theme_path = $_SERVER["HESTIA"] . "/web/css/themes/custom/" . $selected_theme . ".min.css";
		if (file_exists($custom_theme_path)) {
			echo '<link rel="stylesheet" href="/css/themes/custom/' . $selected_theme . ".min.css?" . JS_LATEST_UPDATE . '">';
		} else {
			echo '<link rel="stylesheet" href="/css/themes/custom/' . $selected_theme . ".css?" . JS_LATEST_UPDATE . '">';
		}
	}
}

?>
