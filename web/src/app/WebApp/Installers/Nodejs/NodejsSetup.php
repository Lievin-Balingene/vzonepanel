<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\Nodejs;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class NodejsSetup extends BaseSetup {
	protected array $info = [
		"name" => "Nodejs",
		"group" => "framework",
		"version" => "LTS",
		"thumbnail" => "nodejs-logo.svg",
		"runtime" => "nodejs",
	];

	protected array $config = [
		"form" => [
			"framework" => [
				"type" => "select",
				"value" => "express",
				"options" => [
					"express" => "Express (starter)",
					"custom" => "Custom / existing app",
				],
				"label" => "Application type",
			],
			"app_directory" => [
				"type" => "text",
				"value" => "private/apps/nodejs",
				"label" => "Application root (relative to domain)",
				"placeholder" => "private/apps/nodejs",
			],
			"startup" => [
				"type" => "text",
				"value" => "npm start",
				"label" => "Startup command (Custom)",
				"placeholder" => "npm start  or  node server.js",
			],
			"install_dependencies" => [
				"type" => "boolean",
				"value" => true,
				"label" => "Run npm install",
			],
			"env_vars" => [
				"type" => "textarea",
				"value" => "",
				"label" => "Environment variables (KEY=VALUE per line)",
				"placeholder" => "NODE_ENV=production\nAPI_KEY=...",
			],
		],
		"database" => false,
		"resources" => [],
		"server" => [
			"backend" => "no-php",
		],
	];

	protected function setupApplication(InstallationTarget $target, array $options): void {
		$framework = strtolower((string) ($options["framework"] ?? "express"));
		if (!in_array($framework, ["express", "custom"], true)) {
			$framework = "express";
		}

		$appDirectory = trim((string) ($options["app_directory"] ?? "private/apps/nodejs"), "/");
		if ($appDirectory === "") {
			$appDirectory = "private/apps/nodejs";
		}

		$startup = trim((string) ($options["startup"] ?? "npm start"));
		if ($startup === "") {
			$startup = "npm start";
		}

		$payload = [
			"framework" => $framework,
			"app_directory" => $appDirectory,
			"startup" => $startup,
			"install_dependencies" => !empty($options["install_dependencies"]) ? "yes" : "no",
			"env_vars" => (string) ($options["env_vars"] ?? ""),
		];

		$this->appcontext->runWebAppCli("v-add-web-app", [
			$target->domain->domainName,
			"nodejs",
			json_encode($payload, JSON_THROW_ON_ERROR),
		]);
	}
}
