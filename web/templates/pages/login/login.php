<div class="login vz-login">
	<main class="vz-login-panel">
		<div class="vz-login-card">
			<?php require $_SERVER["HESTIA"] . "/web/templates/includes/login-hero.php"; ?>

			<form id="login-form" method="post" action="/login/" class="vz-login-form">
				<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">

				<header class="vz-login-header">
					<h1 class="login-title"><?= tohtml(_("Sign in")) ?></h1>
					<p class="vz-login-subtitle"><?= tohtml(_("Enter your username to continue")) ?></p>
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
		</div>
	</main>
</div>
