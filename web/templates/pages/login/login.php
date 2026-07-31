<div class="login vz-login">
	<div class="vz-login-stage">
		<?php require $_SERVER["HESTIA"] . "/web/templates/includes/login-hero.php"; ?>

		<main class="vz-login-panel">
			<div class="vz-login-card" x-data="{ ready: false }" x-init="requestAnimationFrame(() => ready = true)" :class="ready && 'is-ready'">
				<div class="vz-login-steps" aria-hidden="true">
					<span class="vz-login-step is-active">1</span>
					<span class="vz-login-step-line"></span>
					<span class="vz-login-step">2</span>
				</div>

				<form id="login-form" method="post" action="/login/" class="vz-login-form">
					<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">

					<header class="vz-login-header">
						<h1 class="login-title"><?= tohtml(_("Sign in")) ?></h1>
						<p class="vz-login-subtitle"><?= tohtml(sprintf(_("Enter your %s username to continue"), $_SESSION["APP_NAME"] ?? "V-zone Panel")) ?></p>
					</header>

					<?php if (!empty($error)) { ?>
						<div class="vz-login-alert" role="alert">
							<i class="fas fa-circle-exclamation" aria-hidden="true"></i>
							<span><?= tohtml($error) ?></span>
						</div>
					<?php } ?>

					<div class="vz-login-field">
						<label for="username" class="form-label"><?= tohtml(_("Username")) ?></label>
						<input
							type="text"
							class="form-control"
							name="user"
							id="username"
							autocomplete="username"
							placeholder="<?= tohtml(_("e.g. admin")) ?>"
							required
							autofocus
						>
					</div>

					<button type="submit" class="button vz-login-submit">
						<span><?= tohtml(_("Continue")) ?></span>
						<i class="fas fa-arrow-right" aria-hidden="true"></i>
					</button>
				</form>

				<p class="vz-login-footnote">
					<?= tohtml(_("Secure access to your hosting account")) ?>
				</p>
			</div>
		</main>
	</div>
</div>
