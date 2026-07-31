<div class="login vz-login">
	<div class="vz-login-stage">
		<?php require $_SERVER["HESTIA"] . "/web/templates/includes/login-hero.php"; ?>
		<main class="vz-login-panel">
			<div class="vz-login-card is-ready">
				<?php if (!empty($success)) { ?>
					<header class="vz-login-header">
						<h1 class="login-title"><?= tohtml(_("Account unlocked")) ?></h1>
						<p class="vz-login-subtitle"><?= tohtml(_("Two-factor authentication is now turned off. You can sign in with your password.")) ?></p>
					</header>
					<a href="/login/" class="button vz-login-submit"><?= tohtml(_("Sign in")) ?></a>
				<?php } else { ?>
					<form method="post" action="/reset2fa/" class="vz-login-form">
						<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
						<header class="vz-login-header">
							<h1 class="login-title"><?= tohtml(_("Unlock account")) ?></h1>
							<p class="vz-login-subtitle"><?= tohtml(_("Use your backup 2FA reset code to disable two-factor authentication.")) ?></p>
						</header>
						<?php if (!empty($error)) { ?>
							<div class="vz-login-alert" role="alert">
								<i class="fas fa-circle-exclamation" aria-hidden="true"></i>
								<span><?= tohtml($error) ?></span>
							</div>
						<?php } ?>
						<div class="vz-login-field">
							<label for="user" class="form-label"><?= tohtml(_("Username")) ?></label>
							<input type="text" class="form-control" name="user" id="user" autocomplete="username" required autofocus>
						</div>
						<div class="vz-login-field">
							<label for="twofa" class="form-label"><?= tohtml(_("2FA reset code")) ?></label>
							<input type="text" class="form-control" name="twofa" id="twofa" autocomplete="off" required>
						</div>
						<div class="vz-login-actions">
							<button type="submit" class="button vz-login-submit"><?= tohtml(_("Unlock")) ?></button>
							<a href="/login/?logout" class="button button-secondary"><?= tohtml(_("Back to sign in")) ?></a>
						</div>
					</form>
				<?php } ?>
			</div>
		</main>
	</div>
</div>
