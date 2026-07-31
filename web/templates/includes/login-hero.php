<aside class="vz-login-hero" aria-hidden="false">
	<div class="vz-login-hero-glow" aria-hidden="true"></div>
	<div class="vz-login-hero-grid" aria-hidden="true"></div>
	<div class="vz-login-hero-content">
		<p class="vz-login-kicker"><?= tohtml(_("Hosting control panel")) ?></p>
		<a href="/" class="vz-login-hero-brand">
			<img src="/images/logo.svg" alt="" width="56" height="56">
			<span><?= tohtml($_SESSION["APP_NAME"] ?? "V-zone Panel") ?></span>
		</a>
		<p class="vz-login-tagline">
			<?= tohtml(_("Manage websites, email, databases, and apps from one calm workspace.")) ?>
		</p>
	</div>
</aside>
