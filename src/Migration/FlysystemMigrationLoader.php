<?php

namespace Daikon\Flysystem\Migration;

use Daikon\Dbal\Migration\MigrationList;
use Daikon\Dbal\Migration\MigrationLoaderInterface;
use Daikon\Flysystem\Connector\FlysystemConnector;

final class FlysystemMigrationLoader implements MigrationLoaderInterface
{
    private $connector;

    private $settings;

    public function __construct(FlysystemConnector $connector, array $settings = [])
    {
        $this->connector = $connector;
        $this->settings = $settings;
    }

    public function load(): MigrationList
    {
        $filesystem = $this->connector->getConnection();
        $contents = $filesystem->listContents($this->settings['location'], true);
        $migrationFiles = array_filter($contents, function ($fileinfo) {
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

    private function getBaseDir()
    {
        $connectorSettings = $this->connector->getSettings();
        return $connectorSettings['mounts']['migration']['location'];
    }
}
