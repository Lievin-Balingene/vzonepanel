<?php

declare(strict_types=1);

namespace Hestia\WebApp;

use Hestia\System\HestiaApp;
use Hestia\WebApp\InstallationTarget\InstallationTarget;
use Hestia\WebApp\InstallationTarget\TargetDatabase;
use RuntimeException;

use function basename;
use function dirname;
use function is_string;
use function sprintf;
use function str_replace;
use function str_starts_with;

abstract class BaseSetup implements InstallerInterface
{
    protected array $info;
    protected array $config;

    public function __construct(protected HestiaApp $appcontext)
    {
    }

    public function getInfo(): InstallerInfo
    {
        $supportedPHPVersions = [];
        if (!empty($this->config['server']['php']['supported'])) {
            $supportedPHPVersions = $this->appcontext->getSupportedPHPVersions(
                $this->config['server']['php']['supported'],
            );
        }

        return InstallerInfo::fromArray([
            ...$this->info,
            'supportedPHPVersions' => $supportedPHPVersions,
            'runtime' => $this->info['runtime'] ?? 'php',
        ]);
    }

    public function getConfig(string $section = ''): mixed
    {
        return !empty($section) ? $this->config[$section] : $this->config;
    }

    public function install(InstallationTarget $target, array $options): void
    {
        $this->appcontext->deleteFile($target->getDocRoot('robots.txt'));
        $this->appcontext->deleteFile($target->getDocRoot('index.html'));

        $this->retrieveResources($target, $options);
        $this->setupDatabase($target->database);
        $this->setupWebServer($target->domain->domainName, $options['php_version'] ?? null);
        $this->setupApplication($target, $options);
    }

    abstract protected function setupApplication(InstallationTarget $target, array $options): void;

    private function setupWebServer(string $domainName, ?string $phpVersion): void
    {
        if ($_SESSION['WEB_SYSTEM'] === 'nginx') {
            if (isset($this->config['server']['nginx']['template'])) {
                $this->appcontext->changeWebTemplate(
                    $domainName,
                    $this->config['server']['nginx']['template'],
                );
            }
        } else {
            if (isset($this->config['server']['apache2']['template'])) {
                $this->appcontext->changeWebTemplate(
                    $domainName,
                    $this->config['server']['apache2']['template'],
                );
            }
        }
        if ($_SESSION['WEB_BACKEND'] === 'php-fpm') {
            if (!empty($this->config['server']['php']['supported']) && !empty($phpVersion)) {
                $supportedPHPVersions = $this->appcontext->getSupportedPHPVersions(
                    $this->config['server']['php']['supported'],
                );
                if (empty($supportedPHPVersions)) {
                    throw new RuntimeException('Required PHP version is not supported');
                }
                $this->appcontext->changeBackendTemplate(
                    $domainName,
                    'PHP-' . str_replace('.', '_', $phpVersion),
                );
            } elseif (!empty($this->config['server']['backend'])) {
                $this->appcontext->changeBackendTemplate(
                    $domainName,
                    $this->config['server']['backend'],
                );
            }
        }
    }

    private function setupDatabase(TargetDatabase $database): void
    {
        if ($database->createDatabase) {
            if (!$this->appcontext->checkDatabaseLimit()) {
                throw new RuntimeException('Unable to add database! Limit reached!');
            }

            $userPrefix = $this->appcontext->user() . '_';

            $databaseName = str_replace($userPrefix, '', $database->name);
            $databaseUser = str_replace($userPrefix, '', $database->user);

            $this->appcontext->databaseAdd(
                $databaseName,
                $databaseUser,
                $database->password,
                $database->host,
            );
        }
    }

    private function retrieveResources(InstallationTarget $target, array $options): void
    {
        foreach ($this->config['resources'] as $resourceType => $resourceData) {
            $resourceLocation = $resourceData['src'];

            if (!empty($resourceData['dst']) && is_string($resourceData['dst'])) {
                $destinationPath = $target->getDocRoot($resourceData['dst']);
            } else {
                $destinationPath = $target->getDocRoot();
            }

            if ($resourceType === 'composer') {
                $this->appcontext->runComposer($options['php_version'], [
                    'create-project',
                    '--no-progress',
                    '--no-interaction',
                    '--prefer-dist',
                    $resourceData['src'],
                    '-d',
                    dirname($destinationPath),
                    basename($destinationPath),
                ]);

                return;
            }

            if ($resourceType === 'wp') {
                $this->appcontext->runWp($options['php_version'], [
                    'core',
                    'download',
                    '--locale=' . $options['language'],
                    '--version=' . $this->info['version'],
                    '--path=' . $destinationPath,
                ]);

                return;
            }

            if ($resourceType === 'archive') {
                if (
                    !str_starts_with($resourceLocation, 'http://') &&
                    !str_starts_with($resourceLocation, 'https://')
                ) {
                    // only unpack file archive
                    $this->appcontext->archiveExtract($resourceLocation, $destinationPath);

                    return;
                }

                // Download archive, unpack, delete download
                $resourceLocation = $this->appcontext->downloadUrl(
                    $resourceLocation,
                    $destinationPath,
                );

                $this->appcontext->archiveExtract($resourceLocation, $destinationPath);
                $this->appcontext->deleteFile($resourceLocation);

                return;
            }

            throw new RuntimeException(sprintf('Unknown resource type "%s"', $resourceType));
        }
    }
}
