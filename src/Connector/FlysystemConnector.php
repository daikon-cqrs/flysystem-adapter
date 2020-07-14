<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/flysystem-adapter project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Flysystem\Connector;

use Daikon\Dbal\Connector\ConnectorInterface;
use Daikon\Dbal\Connector\ProvidesConnector;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;

final class FlysystemConnector implements ConnectorInterface
{
    use ProvidesConnector;

    protected function connect(): MountManager
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
