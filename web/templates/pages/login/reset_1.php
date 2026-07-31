<div class="login vz-login">
	<div class="vz-login-stage">
		<?php require $_SERVER["HESTIA"] . "/web/templates/includes/login-hero.php"; ?>
		<main class="vz-login-panel">
			<div class="vz-login-card is-ready">
				<form method="post" action="/reset/" class="vz-login-form">
					<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
					<header class="vz-login-header">
						<h1 class="login-title"><?= tohtml(_("Forgot password")) ?></h1>
						<p class="vz-login-subtitle"><?= tohtml(_("We'll email reset instructions if the account matches.")) ?></p>
					</header>
					<?php if (!empty($error)) { ?>
						<div class="vz-login-alert" role="alert">
							<i class="fas fa-circle-exclamation" aria-hidden="true"></i>
							<span><?= tohtml($error) ?></span>
						</div>
					<?php } ?>
					<div class="vz-login-field">
						<label for="username" class="form-label"><?= tohtml(_("Username")) ?></label>
						<input type="text" class="form-control" name="user" id="username" autocomplete="username" required autofocus>
					</div>
					<div class="vz-login-field">
						<label for="email" class="form-label"><?= tohtml(_("Email")) ?></label>
						<input type="email" class="form-control" name="email" id="email" autocomplete="email" required>
					</div>
					<div class="vz-login-actions">
						<button type="submit" class="button vz-login-submit"><?= tohtml(_("Send reset link")) ?></button>
						<a href="/login/?logout" class="button button-secondary"><?= tohtml(_("Back to sign in")) ?></a>
					</div>
				</form>
			</div>
		</main>
	</div>
</div>
