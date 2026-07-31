<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\Python;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class PythonSetup extends BaseSetup {
	protected array $info = [
		"name" => "Python",
		"group" => "framework",
		"version" => "3.x",
		"thumbnail" => "python-logo.svg",
		"runtime" => "python",
	];

	protected array $config = [
		"form" => [
			"framework" => [
				"type" => "select",
				"value" => "django",
				"options" => [
					"django" => "Django (starter)",
					"flask" => "Flask (starter)",
					"fastapi" => "FastAPI (starter)",
					"custom" => "Custom / existing app",
				],
				"label" => "Application type",
			],
			"project" => [
				"type" => "text",
				"value" => "config",
				"label" => "Django project package (Django only)",
			],
			"app_directory" => [
				"type" => "text",
				"value" => "private/apps/python",
				"label" => "Application root (relative to domain)",
				"placeholder" => "private/apps/python",
			],
			"entry_point" => [
				"type" => "text",
				"value" => "app:app",
				"label" => "Entry point (Flask/FastAPI/Custom)",
				"placeholder" => "app:app or main:app or myproject.wsgi:application",
			],
			"server_type" => [
				"type" => "select",
				"value" => "wsgi",
				"options" => [
					"wsgi" => "WSGI (Gunicorn — Flask, Django, most apps)",
					"asgi" => "ASGI (Uvicorn — FastAPI, Starlette, async)",
				],
				"label" => "Server (Custom only)",
			],
			"env_vars" => [
				"type" => "textarea",
				"value" => "",
				"label" => "Environment variables (KEY=VALUE per line)",
				"placeholder" => "DEBUG=0\nSECRET_KEY=change-me",
			],
		],
		"database" => true,
		"resources" => [],
		"server" => [
			"backend" => "no-php",
		],
	];

	protected function setupApplication(InstallationTarget $target, array $options): void {
		$framework = strtolower((string) ($options["framework"] ?? "django"));
		$allowed = ["django", "flask", "fastapi", "custom"];
		if (!in_array($framework, $allowed, true)) {
			$framework = "django";
		}

		$project = preg_replace("/[^A-Za-z0-9_]/", "", (string) ($options["project"] ?? "config")) ?: "config";
		$appDirectory = trim((string) ($options["app_directory"] ?? "private/apps/python"), "/");
		$entryPoint = trim((string) ($options["entry_point"] ?? "app:app"));
		$serverType = strtolower((string) ($options["server_type"] ?? "wsgi"));
		if (!in_array($serverType, ["wsgi", "asgi"], true)) {
			$serverType = "wsgi";
		}

		// Sensible defaults per framework so customers don't need to know internals
		$defaults = [
			"django" => ["dir" => "private/apps/django", "entry" => $project . ".wsgi:application"],
			"flask" => ["dir" => "private/apps/flask", "entry" => "app:app"],
			"fastapi" => ["dir" => "private/apps/fastapi", "entry" => "main:app", "server" => "asgi"],
			"custom" => ["dir" => $appDirectory ?: "private/apps/app", "entry" => $entryPoint ?: "app:app"],
		];

		$dir = $appDirectory !== "" && $appDirectory !== "private/apps/python"
			? $appDirectory
			: $defaults[$framework]["dir"];
		$entry = $entryPoint !== "" && $entryPoint !== "app:app"
			? $entryPoint
			: $defaults[$framework]["entry"];
		if ($framework === "fastapi") {
			$serverType = "asgi";
		}
		if ($framework === "django" || $framework === "flask") {
			$serverType = "wsgi";
		}

		$payload = [
			"framework" => $framework,
			"project" => $project,
			"app_directory" => $dir,
			"entry_point" => $entry,
			"server_type" => $serverType,
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
