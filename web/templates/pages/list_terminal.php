<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/user/">
				<i class="fas fa-arrow-left icon-blue"></i><?= tohtml( _("Back")) ?>
			</a>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">
	<div class="vz-page-hero">
		<div>
			<h1 class="vz-page-title"><?= tohtml(_("Web Terminal")) ?></h1>
			<p class="vz-page-subtitle"><?= tohtml(_("Secure shell access in your browser.")) ?></p>
		</div>
	</div>
	<div class="form-container form-container-wide vz-form-card">
		<div class="js-web-terminal web-terminal"></div>
	</div>
</div>
