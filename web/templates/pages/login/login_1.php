<div class="login vz-login">
	<main class="vz-login-panel">
		<div class="vz-login-card" x-data="{ show: false }">
			<?php require $_SERVER["HESTIA"] . "/web/templates/includes/login-hero.php"; ?>

			<form id="login-form" method="post" action="/login/" class="vz-login-form">
				<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">

				<header class="vz-login-header">
					<p class="vz-login-userchip">
						<i class="fas fa-user" aria-hidden="true"></i>
						<span><?= tohtml($_SESSION["login"]["username"] ?? "") ?></span>
					</p>
					<h1 class="login-title"><?= tohtml(_("Enter your password")) ?></h1>
					<p class="vz-login-subtitle"><?= tohtml(_("Welcome back")) ?></p>
				</header>

				<?php if (!empty($error)) { ?>
					<div class="vz-login-alert" role="alert">
						<i class="fas fa-circle-exclamation" aria-hidden="true"></i>
						<span><?= tohtml($error) ?></span>
					</div>
				<?php } ?>

				<div class="vz-login-field">
					<label for="password" class="form-label vz-login-label-row">
						<span><?= tohtml(_("Password")) ?></span>
						<?php if (($_SESSION["POLICY_SYSTEM_PASSWORD_RESET"] ?? "") !== "no") { ?>
							<a class="login-form-link" href="/reset/"><?= tohtml(_("Forgot password?")) ?></a>
						<?php } ?>
					</label>
					<div class="vz-login-password-wrap">
						<input
							:type="show ? 'text' : 'password'"
							class="form-control"
							name="password"
							id="password"
							autocomplete="current-password"
							required
							autofocus
						>
						<button
							type="button"
							class="vz-login-eye"
							@click="show = !show"
							:aria-label="show ? '<?= tohtml(_("Hide password")) ?>' : '<?= tohtml(_("Show password")) ?>'"
						>
							<i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'" aria-hidden="true"></i>
						</button>
					</div>
				</div>

				<div class="vz-login-actions">
					<button type="submit" class="button vz-login-submit">
						<span><?= tohtml(_("Sign in")) ?></span>
						<i class="fas fa-right-to-bracket" aria-hidden="true"></i>
					</button>
					<a href="/login/?<?= tohtml(http_build_query(["logout" => "true"])) ?>" class="button button-secondary">
						<?= tohtml(_("Use another account")) ?>
					</a>
				</div>
			</form>
		</div>
	</main>
</div>
