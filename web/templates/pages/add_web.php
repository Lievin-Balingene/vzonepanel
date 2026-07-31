<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/web/">
				<i class="fas fa-arrow-left"></i><?= tohtml(_("Back")) ?>
			</a>
		</div>
		<div class="toolbar-buttons">
			<?php if (($_SESSION["role"] == "admin" && $accept === "true") || $_SESSION["role"] !== "admin") { ?>
				<button type="submit" class="button" form="main-form">
					<i class="fas fa-floppy-disk"></i><?= tohtml(_("Save")) ?>
				</button>
			<?php } ?>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">

	<form id="main-form" name="v_add_web" method="post" class="js-enable-inputs-on-submit">
		<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
		<input type="hidden" name="ok" value="Add">

		<div class="vz-page-hero u-mb20">
			<div>
				<h1 class="vz-page-title"><?= tohtml(_("Add Web Domain")) ?></h1>
				<p class="vz-page-subtitle"><?= tohtml(_("Create a new site with optional DNS and mail support.")) ?></p>
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
      		_("a web domain"),
      	),
      	"</a>",
      	'<a href="/add/user/">',
      ) ?></p>
				</div>
			<?php } ?>
			<?php if ($_SESSION["role"] == "admin" && empty($accept)) { ?>
				<div class="u-side-by-side u-mt20">
					<a href="/add/user/" class="button u-width-full u-mr10"><?= tohtml(_("Add User")) ?></a>
					<a href="/add/web/?<?= tohtml(
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
      ) ?>" placeholder="example.com" required autofocus>
					</div>
					<div class="u-mb20">
						<label for="v_ip" class="form-label"><?= tohtml(_("IP Address")) ?></label>
						<select class="form-select" name="v_ip" id="v_ip">
							<?php foreach ($ips as $ip => $value) {
       	$display_ip = htmlentities(empty($value["NAT"]) ? $ip : "{$value["NAT"]}");
       	$ip_selected = !empty($v_ip) && $ip == $_POST["v_ip"] ? "selected" : "";
       	echo "\t\t\t\t<option value=\"{$ip}\" {$ip_selected}>{$display_ip}</option>\n";
       } ?>
						</select>
					</div>
				</div>

				<div class="vz-form-section">
					<h2 class="vz-form-section-title"><?= tohtml(_("Optional services")) ?></h2>
					<p class="vz-form-section-hint"><?= tohtml(_("Enable related services for this domain in one step.")) ?></p>
					<div class="vz-option-cards">
						<?php if (isset($_SESSION["DNS_SYSTEM"]) && !empty($_SESSION["DNS_SYSTEM"])) { ?>
							<?php if ($panel[$user_plain]["DNS_DOMAINS"] != "0") { ?>
								<label class="vz-option-card" for="v_dns">
									<input class="form-check-input" type="checkbox" name="v_dns" id="v_dns" <?php if (
         	empty($v_dns) &&
         	$panel[$user_plain]["DNS_DOMAINS"] != "0"
         ); ?>>
									<span class="vz-option-card-icon"><i class="fas fa-sitemap"></i></span>
									<span>
										<strong><?= tohtml(_("DNS Support")) ?></strong>
										<small><?= tohtml(_("Create a DNS zone for this domain")) ?></small>
									</span>
								</label>
							<?php } ?>
						<?php } ?>
						<?php if (isset($_SESSION["IMAP_SYSTEM"]) && !empty($_SESSION["IMAP_SYSTEM"])) { ?>
							<?php if ($panel[$user_plain]["MAIL_DOMAINS"] != "0") { ?>
								<label class="vz-option-card" for="v_mail">
									<input class="form-check-input" type="checkbox" name="v_mail" id="v_mail" <?php if (
         	empty($v_mail) &&
         	$panel[$user_plain]["MAIL_DOMAINS"] != "0"
         ); ?>>
									<span class="vz-option-card-icon"><i class="fas fa-envelope"></i></span>
									<span>
										<strong><?= tohtml(_("Mail Support")) ?></strong>
										<small><?= tohtml(_("Create a mail domain for this domain")) ?></small>
									</span>
								</label>
							<?php } ?>
						<?php } ?>
					</div>
				</div>

				<div class="vz-form-actions">
					<button type="submit" class="button">
						<i class="fas fa-plus"></i><?= tohtml(_("Create domain")) ?>
					</button>
					<a href="/list/web/" class="button button-secondary"><?= tohtml(_("Cancel")) ?></a>
				</div>
			<?php } ?>
		</div>

	</form>

</div>
