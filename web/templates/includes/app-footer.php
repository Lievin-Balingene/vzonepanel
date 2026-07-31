<footer class="app-footer">
	<div class="container">
		<p>
			<a href="/list/dashboard/" class="app-footer-link">
				<?= htmlspecialchars($_SESSION["APP_NAME"] ?? "V-zone Panel") ?>
			</a>
			v<?= $_SESSION["VERSION"] ?>
		</p>
		<p><?= _("Powered by V-zone Cloud") ?></p>
	</div>
</footer>
