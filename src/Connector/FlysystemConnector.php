<?php

namespace Daikon\Flysystem\Connector;

use Daikon\Dbal\Connector\ConnectorInterface;
use Daikon\Dbal\Connector\ConnectorTrait;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;

final class FlysystemConnector implements ConnectorInterface
{
    use ConnectorTrait;

    private function connect()
    {
        $mounts = [];
        foreach ($this->settings['mounts'] as $mountName => $mountConfig) {
            $adapterClass = $mountConfig['adapter'];
            $adapter = new $adapterClass($mountConfig['location']);
            $mounts[$mountName] = new Filesystem($adapter);
        }
        return new MountManager($mounts);
    }
}
