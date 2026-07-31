<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/mail/">
				<i class="fas fa-arrow-left"></i><?= tohtml(_("Back")) ?>
			</a>
		</div>
		<div class="toolbar-buttons">
			<?php if (($_SESSION["role"] == "admin" && $accept === "true") || $user_plain !== "admin") { ?>
				<button type="submit" class="button" form="main-form">
					<i class="fas fa-floppy-disk"></i><?= tohtml(_("Save")) ?>
				</button>
			<?php } ?>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">

	<form
		x-data="{
			hasSmtpRelay: <?= tohtml($v_smtp_relay == "true" ? "true" : "false") ?>
		}"
		id="main-form"
		name="v_add_mail"
		method="post"
	>
		<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
		<input type="hidden" name="ok" value="Add">

		<div class="vz-page-hero u-mb20">
			<div>
				<h1 class="vz-page-title"><?= tohtml(_("Add Mail Domain")) ?></h1>
				<p class="vz-page-subtitle"><?= tohtml(_("Create a mail domain with security and delivery options.")) ?></p>
			</div>
		</div>

		<div class="form-container vz-form-card">
			<?php show_alert_message($_SESSION); ?>
			<?php if ($_SESSION["role"] == "admin" && $accept !== "true") { ?>
				<div class="alert alert-danger" role="alert">
					<i class="fas fa-exclamation"></i>
					<p><?= htmlify_trans(
     	sprintf(
      		_("It is strongly advised to {create a standard user account} before adding %s to the server due to the increased privileges the admin account possesses and potential security risks."),
      		_("a mail domain"),
      	),
      	"</a>",
      	'<a href="/add/user/">',
      ) ?></p>
				</div>
			<?php } ?>
			<?php if ($_SESSION["role"] == "admin" && empty($accept)) { ?>
				<div class="u-side-by-side u-mt20">
					<a href="/add/user/" class="button u-width-full u-mr10"><?= tohtml(_("Add User")) ?></a>
					<a href="/add/mail/?<?= tohtml(
     	http_build_query(["accept" => "true"]),
     ) ?>" class="button button-danger u-width-full u-ml10"><?= tohtml(_("Continue")) ?></a>
				</div>
			<?php } ?>
			<?php if (($_SESSION["role"] == "admin" && $accept === "true") || $_SESSION["role"] !== "admin") { ?>
				<div class="vz-form-section">
					<h2 class="vz-form-section-title"><?= tohtml(_("Domain details")) ?></h2>
					<div class="u-mb10">
						<label for="v_domain" class="form-label"><?= tohtml(_("Domain")) ?></label>
						<input type="text" class="form-control" name="v_domain" id="v_domain" value="<?= tohtml(
      	trim($v_domain, "'"),
      ) ?>" required autofocus>
					</div>
					<?php if ($_SESSION["WEBMAIL_SYSTEM"]) { ?>
						<div class="u-mb20">
							<label for="v_webmail" class="form-label"><?= tohtml(_("Webmail Client")) ?></label>
							<select class="form-select" name="v_webmail" id="v_webmail" tabindex="6">
								<?php foreach ($webmail_clients as $client) {
        	echo "\t\t\t\t<option value=\"" . htmlentities($client) . "\"";
        	if ($v_webmail == $client) {
        		echo " selected";
        	}
        	echo ">" . htmlentities(ucfirst($client)) . "</option>\n";
        } ?>
								<option value="" <?php if (empty($v_webmail) || $v_webmail == "disabled") {
        	echo "selected";
        } ?>><?= tohtml(_("Disabled")) ?></option>
							</select>
						</div>
					<?php } ?>
				</div>

				<div class="vz-form-section">
					<h2 class="vz-form-section-title"><?= tohtml(_("Security options")) ?></h2>
					<p class="vz-form-section-hint"><?= tohtml(_("Enable protection and delivery features for this mail domain.")) ?></p>
					<div class="vz-option-cards">
						<?php if (!empty($_SESSION["ANTISPAM_SYSTEM"])) { ?>
							<label class="vz-option-card" for="v_antispam">
								<input class="form-check-input" type="checkbox" name="v_antispam" id="v_antispam" <?php if (
        	empty($v_antispam) ||
        	$v_antispam == "yes"
        ) {
        	echo "checked";
        } ?>>
								<span class="vz-option-card-icon"><i class="fas fa-filter"></i></span>
								<span>
									<strong><?= tohtml(_("Spam Filter")) ?></strong>
									<small><?= tohtml(_("Filter incoming spam messages")) ?></small>
								</span>
							</label>
							<label class="vz-option-card" for="v_reject">
								<input class="form-check-input" type="checkbox" name="v_reject" id="v_reject" <?php if (
        	empty($v_reject) ||
        	$v_reject == "yes"
        ) {
        	echo "checked";
        } ?>>
								<span class="vz-option-card-icon"><i class="fas fa-ban"></i></span>
								<span>
									<strong><?= tohtml(_("Reject Spam")) ?></strong>
									<small><?= tohtml(_("Reject messages marked as spam")) ?></small>
								</span>
							</label>
						<?php } ?>
						<?php if (!empty($_SESSION["ANTIVIRUS_SYSTEM"])) { ?>
							<label class="vz-option-card" for="v_antivirus">
								<input class="form-check-input" type="checkbox" name="v_antivirus" id="v_antivirus" <?php if (
        	empty($v_antivirus) ||
        	$v_antivirus == "yes"
        ) {
        	echo "checked";
        } ?>>
								<span class="vz-option-card-icon"><i class="fas fa-shield-virus"></i></span>
								<span>
									<strong><?= tohtml(_("Anti-Virus")) ?></strong>
									<small><?= tohtml(_("Scan messages for malware")) ?></small>
								</span>
							</label>
						<?php } ?>
						<label class="vz-option-card" for="v_dkim">
							<input class="form-check-input" type="checkbox" name="v_dkim" id="v_dkim" <?php if (
       	empty($v_dkim) ||
       	$v_dkim == "yes"
       ) {
       	echo "checked";
       } ?>>
							<span class="vz-option-card-icon"><i class="fas fa-key"></i></span>
							<span>
								<strong><?= tohtml(_("DKIM Support")) ?></strong>
								<small><?= tohtml(_("Sign outgoing mail with DKIM")) ?></small>
							</span>
						</label>
						<label class="vz-option-card" for="v_smtp_relay">
							<input x-model="hasSmtpRelay" class="form-check-input" type="checkbox" name="v_smtp_relay" id="v_smtp_relay">
							<span class="vz-option-card-icon"><i class="fas fa-share-nodes"></i></span>
							<span>
								<strong><?= tohtml(_("SMTP Relay")) ?></strong>
								<small><?= tohtml(_("Relay outbound mail through another host")) ?></small>
							</span>
						</label>
					</div>
					<div x-cloak x-show="hasSmtpRelay" id="smtp_relay_table" class="u-mt20">
						<div class="u-mb10">
							<label for="v_smtp_relay_host" class="form-label"><?= tohtml(_("Host")) ?></label>
							<input type="text" class="form-control" name="v_smtp_relay_host" id="v_smtp_relay_host" value="<?= tohtml(
       	trim($v_smtp_relay_host, "'"),
       ) ?>">
						</div>
						<div class="u-mb10">
							<label for="v_smtp_relay_port" class="form-label"><?= tohtml(_("Port")) ?></label>
							<input type="text" class="form-control" name="v_smtp_relay_port" id="v_smtp_relay_port" value="<?= tohtml(
       	trim($v_smtp_relay_port, "'"),
       ) ?>">
						</div>
						<div class="u-mb10">
							<label for="v_smtp_relay_user" class="form-label"><?= tohtml(_("Username")) ?></label>
							<input type="text" class="form-control" name="v_smtp_relay_user" id="v_smtp_relay_user" value="<?= tohtml(
       	trim($v_smtp_relay_user, "'"),
       ) ?>">
						</div>
						<div class="u-mb10">
							<label for="v_smtp_relay_pass" class="form-label"><?= tohtml(_("Password")) ?></label>
							<input type="text" class="form-control" name="v_smtp_relay_pass" id="v_smtp_relay_pass">
						</div>
					</div>
				</div>

				<div class="vz-form-actions">
					<button type="submit" class="button">
						<i class="fas fa-plus"></i><?= tohtml(_("Create")) ?>
					</button>
					<a href="/list/mail/" class="button button-secondary"><?= tohtml(_("Cancel")) ?></a>
				</div>
			<?php } ?>
		</div>

	</form>

</div>
