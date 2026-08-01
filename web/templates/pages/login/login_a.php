<div class="login vz-login">
	<main class="vz-login-panel">
		<div class="vz-login-card" x-data="{ show: false }">
			<?php require $_SERVER["HESTIA"] . "/web/templates/includes/login-hero.php"; ?>
			<form id="login-form" method="post" action="/login/" class="vz-login-form">
				<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
				<header class="vz-login-header">
					<h1 class="login-title"><?= tohtml(sprintf(_("Welcome to %s"), $_SESSION["APP_NAME"] ?? "V-zone Panel")) ?></h1>
					<p class="vz-login-subtitle"><?= tohtml(_("Sign in with your username and password")) ?></p>
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
					<label for="password" class="form-label vz-login-label-row">
						<span><?= tohtml(_("Password")) ?></span>
						<?php if (($_SESSION["POLICY_SYSTEM_PASSWORD_RESET"] ?? "") !== "no") { ?>
							<a class="login-form-link" href="/reset/"><?= tohtml(_("Forgot password?")) ?></a>
						<?php } ?>
					</label>
					<div class="vz-login-password-wrap">
						<input :type="show ? 'text' : 'password'" class="form-control" name="password" id="password" autocomplete="current-password" required>
						<button type="button" class="vz-login-eye" @click="show = !show" :aria-label="show ? '<?= tohtml(_("Hide password")) ?>' : '<?= tohtml(_("Show password")) ?>'">
							<i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'" aria-hidden="true"></i>
						</button>
					</div>
				</div>
				<button type="submit" class="button vz-login-submit">
					<span><?= tohtml(_("Sign in")) ?></span>
					<i class="fas fa-right-to-bracket" aria-hidden="true"></i>
				</button>
			</form>
		</div>
	</main>
</div>
