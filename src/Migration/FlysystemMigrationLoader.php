<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/flysystem-adapter project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Flysystem\Migration;

use Daikon\Dbal\Migration\MigrationList;
use Daikon\Dbal\Migration\MigrationLoaderInterface;
use Daikon\Flysystem\Connector\FlysystemConnector;
use League\Flysystem\MountManager;
use Psr\Container\ContainerInterface;

final class FlysystemMigrationLoader implements MigrationLoaderInterface
{
    private ContainerInterface $container;

    private FlysystemConnector $connector;

    private array $settings;

    public function __construct(ContainerInterface $container, FlysystemConnector $connector, array $settings = [])
    {
        $this->container = $container;
        $this->connector = $connector;
        $this->settings = $settings;
    }

    public function load(): MigrationList
    {
        /** @var MountManager $filesystem */
        $filesystem = $this->connector->getConnection();
        $contents = $filesystem->listContents($this->settings['location'], true);
        $migrationFiles = array_filter(
            $contents,
            fn(array $fileinfo): bool => isset($fileinfo['extension']) && $fileinfo['extension'] === 'php'
        );

        $migrations = [];
        foreach ($migrationFiles as $migrationFile) {
            // @todo better way to include migration classes
            $declaredClasses = get_declared_classes();
            require_once $this->getBaseDir().'/'.$migrationFile['path'];
            $migrationClass = current(array_diff(get_declared_classes(), $declaredClasses));
            $migrations[] = $this->container->get($migrationClass);
        }

        return new MigrationList($migrations);
    }

    private function getBaseDir(): string
    {
        $connectorSettings = $this->connector->getSettings();
        return $connectorSettings['mounts']['migration']['location'];
    }
}
