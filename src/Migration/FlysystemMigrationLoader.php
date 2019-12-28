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

final class FlysystemMigrationLoader implements MigrationLoaderInterface
{
    /** @var FlysystemConnector */
    private $connector;

    /** @var array */
    private $settings;

    public function __construct(FlysystemConnector $connector, array $settings = [])
    {
        $this->connector = $connector;
        $this->settings = $settings;
    }

    public function load(): MigrationList
    {
        /** @var MountManager $filesystem */
        $filesystem = $this->connector->getConnection();
        $contents = $filesystem->listContents($this->settings['location'], true);
        $migrationFiles = array_filter($contents, function (array $fileinfo): bool {
            return isset($fileinfo['extension']) && $fileinfo['extension'] === 'php';
        });

        $migrations = [];
        foreach ($migrationFiles as $migrationFile) {
            // @todo better way to include migration classes
            $declaredClasses = get_declared_classes();
            require_once $this->getBaseDir().'/'.$migrationFile['path'];
            $migrationClass = current(array_diff(get_declared_classes(), $declaredClasses));
            $migrations[] = new $migrationClass;
        }

        return new MigrationList($migrations);
    }

    private function getBaseDir(): string
    {
        $connectorSettings = $this->connector->getSettings();
        return $connectorSettings['mounts']['migration']['location'];
    }
}
