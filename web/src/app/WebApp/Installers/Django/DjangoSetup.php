<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\Django;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

/**
 * Legacy shortcut — prefer the Python installer (Django/Flask/FastAPI/Custom).
 */
class DjangoSetup extends BaseSetup {
	protected array $info = [
		"name" => "Django",
		"group" => "framework",
		"version" => "5.x",
		"thumbnail" => "django-logo.svg",
		"runtime" => "python",
	];

	protected array $config = [
		"form" => [
			"project" => [
				"type" => "text",
				"value" => "config",
				"label" => "Project package name",
			],
			"env_vars" => [
				"type" => "textarea",
				"value" => "",
				"label" => "Environment variables (KEY=VALUE per line)",
			],
		],
		"database" => true,
		"resources" => [],
		"server" => [
			"nginx" => [
				"template" => "django",
			],
			"apache2" => [
				"template" => "django",
			],
			"backend" => "no-php",
		],
	];

	protected function setupApplication(InstallationTarget $target, array $options): void {
		$project = preg_replace("/[^A-Za-z0-9_]/", "", (string) ($options["project"] ?? "config")) ?: "config";
		$payload = [
			"framework" => "django",
			"project" => $project,
			"app_directory" => "private/apps/django",
			"entry_point" => $project . ".wsgi:application",
			"env_vars" => (string) ($options["env_vars"] ?? ""),
			"create_db" => !empty($options["database_create"]) ? "yes" : "no",
		];
		if (!empty($options["database_create"])) {
			$payload["database_name"] = (string) ($options["database_name"] ?? "");
			$payload["database_user"] = (string) ($options["database_user"] ?? "");
			$payload["database_password"] = (string) ($options["database_password"] ?? "");
			$payload["database_host"] = (string) ($options["database_host"] ?? "localhost");
		}

		$this->appcontext->runWebAppCli("v-add-web-app", [
			$target->domain->domainName,
			"python",
			json_encode($payload, JSON_THROW_ON_ERROR),
		]);
	}
}
