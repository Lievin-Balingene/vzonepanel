<?php

declare(strict_types=1);

namespace Hestia\WebApp;

use Hestia\System\HestiaApp;
use Hestia\WebApp\InstallationTarget\InstallationTarget;
use Hestia\WebApp\InstallationTarget\TargetDatabase;
use Hestia\WebApp\InstallationTarget\TargetDomain;
use RuntimeException;

use function array_filter;
use function bin2hex;
use function in_array;
use function max;
use function str_replace;
use function str_starts_with;
use function strtolower;

class AppWizard
{
    public function __construct(
        private readonly InstallerInterface $installer,
        private readonly string $domain,
        private readonly HestiaApp $appcontext,
    ) {
        if (!$appcontext->userOwnsDomain($domain)) {
            throw new RuntimeException('User does not have access to domain [$domain]');
        }
    }

    public function isDomainRootClean(): bool
    {
        try {
            $installationTarget = $this->getInstallationTarget($this->domain);
            $files = $this->appcontext->listFiles($installationTarget->getDocRoot());

            $filteredFiles = array_filter(
                $files,
                fn(string $file) => !in_array($file, ['index.html', 'robots.txt']),
            );

            return count($filteredFiles) <= 0;
        } catch (\Throwable) {
            // Prefer showing the form over a hard 500 if the docroot check fails
            return true;
        }
    }

    public function formNamespace(): string
    {
        return 'webapp_';
    }

    /**
     * @return mixed[]
     */
    public function getOptions(): array
    {
        $form = $this->installer->getConfig('form');
        $info = $this->installer->getInfo();

        if (!empty($info->supportedPHPVersions)) {
            $form = array_merge($form, [
                'php_version' => [
                    'type' => 'select',
                    'value' => (string) max($info->supportedPHPVersions),
                    'options' => $info->supportedPHPVersions,
                ],
            ]);
        }
        if ($this->installer->getConfig('database') === true) {
            $databaseName = $this->generateDatabaseName();
            $dbHosts = $this->appcontext->getDatabaseHosts('mysql');
            if ($dbHosts === []) {
                $dbHosts = ['localhost'];
            }

            $databaseOptions = [
                'database_create' => [
                    'type' => 'boolean',
                    'value' => true,
                    'label' => 'Create a MySQL database',
                ],
                'database_host' => [
                    'type' => 'select',
                    'options' => array_combine($dbHosts, $dbHosts) ?: ['localhost' => 'localhost'],
                    'value' => $dbHosts[0],
                    'label' => 'Database host',
                ],
                'database_name' => [
                    'type' => 'text',
                    'value' => $databaseName,
                    'label' => 'Database name',
                ],
                'database_user' => [
                    'type' => 'text',
                    'value' => $databaseName,
                    'label' => 'Database user',
                ],
                'database_password' => [
                    'type' => 'password',
                    'placeholder' => 'auto',
                    'label' => 'Database password',
                ],
            ];

            $form = array_merge($form, $databaseOptions);
        }

        return $form;
    }

    public function applicationName(): string
    {
        return $this->installer->getInfo()->name;
    }

    /**
     * @param mixed[] $options
     * @return mixed[]
     */
    public function filterOptions(array $options): array
    {
        $filteredOptions = [];

        foreach ($options as $key => $value) {
            if (!str_starts_with($key, $this->formNamespace())) {
                continue;
            }

            $filteredOptions[str_replace($this->formNamespace(), '', $key)] = $value;
        }

        return $filteredOptions;
    }

    public function execute(array $options): void
    {
        $target = $this->getInstallationTarget($this->domain);

        $options = $this->filterOptions($options);

        if ($this->installer->getConfig('database') === true) {
            if (empty($options['database_name'])) {
                $options['database_name'] = $this->generateDatabaseName();
            }

            if (empty($options['database_user'])) {
                $options['database_user'] = $this->generateDatabaseName();
            }

            if (empty($options['database_password'])) {
                $options['database_password'] = bin2hex(random_bytes(10));
            }

            $target->addTargetDatabase(
                new TargetDatabase(
                    (string) ($options['database_host'] ?? 'localhost'),
                    $this->appcontext->user() . '_' . $options['database_name'],
                    $this->appcontext->user() . '_' . $options['database_user'],
                    $options['database_password'],
                    !empty($options['database_create']),
                ),
            );
        }

        $this->installer->install($target, $options);
    }

    private function getInstallationTarget(string $domainName): InstallationTarget
    {
        $webDomain = $this->appcontext->getWebDomain($domainName);

        if (empty($webDomain->domainPath) || !is_dir($webDomain->domainPath)) {
            throw new RuntimeException(
                sprintf(
                    'Web domain path "%s" not found for domain "%s"',
                    $webDomain->domainPath,
                    $webDomain->domainName,
                ),
            );
        }

        return new InstallationTarget(
            new TargetDomain(
                $webDomain->domainName,
                $webDomain->domainPath,
                $webDomain->ipAddress,
                $webDomain->isSslEnabled,
            ),
            TargetDatabase::noDatabase(),
        );
    }

    private function generateDatabaseName(): string
    {
        // Make the database and user easy to recognise but hard to guess
        $safeAppName = str_replace(' ', '_', strtolower($this->applicationName()));
        $randomString = bin2hex(random_bytes(5));

        return $safeAppName . '_' . $randomString;
    }
}
