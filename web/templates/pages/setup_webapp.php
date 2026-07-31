<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/add/webapp/?<?= tohtml(http_build_query(["domain" => $v_domain])) ?>">
				<i class="fas fa-arrow-left"></i><?= tohtml(_("Back")) ?>
			</a>
		</div>
		<div class="toolbar-buttons">
			<button type="submit" class="button" form="main-form">
				<i class="fas fa-floppy-disk"></i><?= tohtml(_("Save")) ?>
			</button>
		</div>
	</div>
</div>
<!-- End toolbar -->

<!-- Begin form -->
<div class="container">

	<?php
	$webapp_options = [];
	try {
		$webapp_options = $WebappInstaller->getOptions();
	} catch (Throwable $e) {
		$_SESSION["error_msg"] = $e->getMessage();
	}
	?>
	<?php if (!empty($webapp_options)) { ?>
		<form id="main-form" method="POST" name="v_setup_webapp">
			<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
			<input type="hidden" name="ok" value="true">

			<div class="vz-page-hero u-mb20">
				<div>
					<h1 class="vz-page-title"><?= tohtml(sprintf(_("Install %s"), $WebappInstaller->applicationName())) ?></h1>
					<p class="vz-page-subtitle"><?= tohtml(sprintf(_("Configure and deploy on %s"), $v_domain)) ?></p>
				</div>
			</div>

			<div class="form-container vz-form-card">
				<?php show_alert_message($_SESSION); ?>
				<?php
				$domain_root_clean = true;
				try {
					$domain_root_clean = $WebappInstaller->isDomainRootClean();
				} catch (Throwable) {
					$domain_root_clean = true;
				}
				?>
				<?php if (!$domain_root_clean) { ?>
					<div class="alert alert-info u-mb10" role="alert">
						<i class="fas fa-info"></i>
						<div>
							<p class="u-mb10"><?= tohtml(_("Data Loss Warning!")) ?></p>
							<p class="u-mb10"><?= tohtml(_("Your web folder already has files uploaded to it. The installer will overwrite your files and/or the installation might fail.")) ?></p>
							<p><?php echo sprintf(_("Please make sure ~/web/%s/public_html is empty!"), $v_domain); ?></p>
						</div>
					</div>
				<?php } ?>

				<div class="vz-form-section">
					<h2 class="vz-form-section-title"><?= tohtml(_("Application settings")) ?></h2>
					<p class="vz-form-section-hint"><?= tohtml(_("Starters create a ready-to-run app. For Custom, upload your code with File Manager first.")) ?></p>

					<?php foreach ($webapp_options as $form_name => $form_control) {
						$field_name = $WebappInstaller->formNamespace() . $form_name;
						$field_type = "text";
						$field_value = "";
						$field_label = ucwords(str_replace([".", "_"], " ", (string) $form_name));
						$field_placeholder = "";
						$field_options = [];

						if (is_array($form_control)) {
							$field_type = !empty($form_control["type"]) ? (string) $form_control["type"] : "text";
							$field_value = !empty($form_control["value"]) ? (string) $form_control["value"] : "";
							$field_placeholder = !empty($form_control["placeholder"])
								? (string) $form_control["placeholder"]
								: "";
							if (!empty($form_control["label"])) {
								$field_label = (string) $form_control["label"];
							}
							if (!empty($form_control["options"]) && is_array($form_control["options"])) {
								$field_options = $form_control["options"];
							}
						} elseif (is_string($form_control)) {
							$field_type = $form_control;
						}
					?>
						<div class="u-mb10">
							<?php if ($field_type != "boolean"): ?>
								<label for="<?= tohtml($field_name) ?>" class="form-label">
									<?= tohtml($field_label) ?>
									<?php if ($field_type == "password"): ?>
										<button type="button" title="<?= tohtml(_("Generate")) ?>" class="u-unstyled-button u-ml5 js-generate-password">
											<i class="fas fa-arrows-rotate icon-green"></i>
										</button>
									<?php endif; ?>
								</label>
							<?php endif; ?>

							<?php if ($field_type == "select" && count($field_options) > 0): ?>
								<select class="form-select" name="<?= tohtml($field_name) ?>" id="<?= tohtml($field_name) ?>">
									<?php foreach ($field_options as $key => $option):
										$option_key = !is_numeric($key) ? (string) $key : (string) $option;
										$selected = $field_value !== "" && (string) $option_key === $field_value ? "selected" : ""; ?>
										<option value="<?= tohtml($option_key) ?>" <?= $selected ?>>
											<?= tohtml((string) $option) ?>
										</option>
									<?php endforeach; ?>
								</select>
							<?php elseif ($field_type == "textarea"): ?>
								<textarea
									class="form-control"
									name="<?= tohtml($field_name) ?>"
									id="<?= tohtml($field_name) ?>"
									rows="5"
									placeholder="<?= tohtml($field_placeholder) ?>"
								><?= tohtml($field_value) ?></textarea>
							<?php elseif ($field_type == "boolean"):
								$checked = $field_value !== "" && $field_value !== "0" && $field_value !== "false" ? "checked" : ""; ?>
								<div class="form-check">
									<input
										class="form-check-input"
										type="checkbox"
										name="<?= tohtml($field_name) ?>"
										id="<?= tohtml($field_name) ?>"
										value="true"
										<?= $checked ?>
									>
									<label for="<?= tohtml($field_name) ?>">
										<?= tohtml($field_label) ?>
									</label>
								</div>
							<?php else: ?>
								<?php if ($field_type == "password"): ?>
									<div class="u-pos-relative">
										<input
											type="text"
											class="form-control js-password-input"
											name="<?= tohtml($field_name) ?>"
											id="<?= tohtml($field_name) ?>"
											placeholder="<?= tohtml($field_placeholder) ?>"
										>
										<div class="password-meter">
											<meter max="4" class="password-meter-input js-password-meter"></meter>
										</div>
									</div>
								<?php else: ?>
									<input
										type="text"
										class="form-control"
										name="<?= tohtml($field_name) ?>"
										id="<?= tohtml($field_name) ?>"
										placeholder="<?= tohtml($field_placeholder) ?>"
										value="<?= tohtml($field_value) ?>"
									>
								<?php endif; ?>
							<?php endif; ?>
						</div>
					<?php } ?>
				</div>
			</div>
		</form>
	<?php } else { ?>
		<div class="form-container vz-form-card">
			<?php show_alert_message($_SESSION); ?>
			<p><?= tohtml(_("Unable to load application settings. Check the server logs or try again.")) ?></p>
			<a class="button button-secondary" href="/add/webapp/?<?= tohtml(http_build_query(["domain" => $v_domain])) ?>">
				<?= tohtml(_("Back")) ?>
			</a>
		</div>
	<?php } ?>
</div>
<!-- End form -->
