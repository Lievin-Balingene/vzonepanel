<div class="login vz-login">
	<main class="vz-login-panel">
		<div class="vz-login-card">
			<?php require $_SERVER["HESTIA"] . "/web/templates/includes/login-hero.php"; ?>
			<form method="get" action="/reset/" class="vz-login-form">
				<header class="vz-login-header">
					<h1 class="login-title"><?= tohtml(_("Check your email")) ?></h1>
					<p class="vz-login-subtitle"><?= tohtml(_("A password reset code was sent to your email address.")) ?></p>
				</header>
				<?php if (!empty($error)) { ?>
					<div class="vz-login-alert" role="alert">
						<i class="fas fa-circle-exclamation" aria-hidden="true"></i>
						<span><?= tohtml($error) ?></span>
					</div>
				<?php } ?>
				<div class="vz-login-field">
					<input type="hidden" name="action" value="confirm">
					<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
					<input type="hidden" name="user" value="<?= tohtml($_GET["user"] ?? "") ?>">
					<label for="code" class="form-label"><?= tohtml(_("Reset code")) ?></label>
					<input type="text" class="form-control" name="code" id="code" required autofocus>
				</div>
				<div class="vz-login-actions">
					<button type="submit" class="button vz-login-submit"><?= tohtml(_("Confirm code")) ?></button>
					<a href="/reset/" class="button button-secondary"><?= tohtml(_("Back")) ?></a>
				</div>
			</form>
		</div>
	</main>
</div>
