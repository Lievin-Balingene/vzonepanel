<div class="login vz-login">
	<div class="vz-login-stage">
		<?php require $_SERVER["HESTIA"] . "/web/templates/includes/login-hero.php"; ?>
		<main class="vz-login-panel">
			<div class="vz-login-card is-ready" x-data="{ show: false }">
				<form method="post" class="vz-login-form">
					<header class="vz-login-header">
						<h1 class="login-title"><?= tohtml(_("Choose a new password")) ?></h1>
						<p class="vz-login-subtitle"><?= tohtml(_("Pick a strong password you don't reuse elsewhere.")) ?></p>
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
						<input type="hidden" name="code" value="<?= tohtml($_GET["code"] ?? "") ?>">
						<label for="password" class="form-label"><?= tohtml(_("New password")) ?></label>
						<div class="vz-login-password-wrap">
							<input :type="show ? 'text' : 'password'" class="form-control" name="password" id="password" autocomplete="new-password" required autofocus>
							<button type="button" class="vz-login-eye" @click="show = !show" :aria-label="show ? '<?= tohtml(_("Hide password")) ?>' : '<?= tohtml(_("Show password")) ?>'">
								<i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'" aria-hidden="true"></i>
							</button>
						</div>
					</div>
					<div class="vz-login-field">
						<label for="password_confirm" class="form-label"><?= tohtml(_("Confirm password")) ?></label>
						<input type="password" class="form-control" name="password_confirm" id="password_confirm" autocomplete="new-password" required>
					</div>
					<div class="vz-login-actions">
						<button type="submit" class="button vz-login-submit"><?= tohtml(_("Reset password")) ?></button>
						<a href="/login/" class="button button-secondary"><?= tohtml(_("Back to sign in")) ?></a>
					</div>
				</form>
			</div>
		</main>
	</div>
</div>
