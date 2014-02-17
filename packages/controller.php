<?php
defined('C5_EXECUTE') or die(_("Access Denied."));

class FcmSeoUrlsPackage extends Package {

	protected $pkgHandle = 'fcm_seo_urls';
	protected $appVersionRequired = '5.6';
	protected $pkgVersion = '0.9.0';

	public function on_start() {
		$objEnv = Environment::get();
		$objEnv->overrideCoreByPackage('models/page.php', $this);
	}

	public function getPackageName() {
		return t("SEO URLs");
	}

	public function getPackageDescription() {
		return t("Prevents parent pages from appearing page URLs.");
	}

	public function install() {
		$pkg = parent::install();
	}

}