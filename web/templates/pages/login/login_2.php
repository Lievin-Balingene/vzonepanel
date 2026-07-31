<div class="login vz-login">
	<div class="vz-login-stage">
		<?php require $_SERVER["HESTIA"] . "/web/templates/includes/login-hero.php"; ?>

		<main class="vz-login-panel">
			<div class="vz-login-card" x-data="{ ready: false }" x-init="requestAnimationFrame(() => ready = true)" :class="ready && 'is-ready'">
				<div class="vz-login-steps" aria-hidden="true">
					<span class="vz-login-step is-done"><i class="fas fa-check"></i></span>
					<span class="vz-login-step-line is-active"></span>
					<span class="vz-login-step is-active">2FA</span>
				</div>

				<form id="login-form" method="post" action="/login/" class="vz-login-form">
					<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">

					<header class="vz-login-header">
						<h1 class="login-title"><?= tohtml(_("Two-factor authentication")) ?></h1>
						<p class="vz-login-subtitle"><?= tohtml(_("Open your authenticator app and enter the 6-digit code.")) ?></p>
					</header>

					<?php if (!empty($error)) { ?>
						<div class="vz-login-alert" role="alert">
							<i class="fas fa-circle-exclamation" aria-hidden="true"></i>
							<span><?= tohtml($error) ?></span>
						</div>
					<?php } ?>

					<div class="vz-login-field">
						<label for="twofa" class="form-label vz-login-label-row">
							<span><?= tohtml(_("Authentication code")) ?></span>
							<a class="login-form-link" href="/reset2fa/"><?= tohtml(_("Lost device?")) ?></a>
						</label>
						<input
							type="text"
							class="form-control vz-login-otp"
							name="twofa"
							id="twofa"
							inputmode="numeric"
							pattern="[0-9]*"
							autocomplete="one-time-code"
							placeholder="••••••"
							required
							autofocus
						>
					</div>

					<div class="vz-login-actions">
						<button type="submit" class="button vz-login-submit">
							<span><?= tohtml(_("Verify & sign in")) ?></span>
							<i class="fas fa-shield-halved" aria-hidden="true"></i>
						</button>
						<a href="/login/?logout" class="button button-secondary">
							<?= tohtml(_("Back")) ?>
						</a>
					</div>
				</form>
			</div>
		</main>
	</div>
</div>
