<?php

namespace Daikon\Flysystem\Connector;

use Daikon\Dbal\Connector\ConnectorInterface;
use Daikon\Dbal\Connector\ConnectorTrait;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;

final class FlysystemConnector implements ConnectorInterface
{
    use ConnectorTrait;

    private function connect(): MountManager
    {
        $mounts = [];
        foreach ($this->settings['mounts'] as $mountName => $mountConfig) {
            $adapterClass = $mountConfig['adapter'];
            /** @var AdapterInterface $adapter */
            $adapter = new $adapterClass($mountConfig['location']);
            $mounts[$mountName] = new Filesystem($adapter);
        }
        return new MountManager($mounts);
    }
}
