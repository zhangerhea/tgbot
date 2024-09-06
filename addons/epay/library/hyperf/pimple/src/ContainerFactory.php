<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Pimple;

use Hyperf\Utils\ApplicationContext;
use Pimple;

class ContainerFactory
{
    protected $providers = [];

    public function __construct(array $providers = [])
    {
        $this->providers = $providers;
    }

    public function __invoke()
    {
        $container = new Container(new Pimple\Container());
        foreach ($this->providers as $provider) {
            /** @var ProviderInterface $instance */
            $instance = new $provider();
            $instance->register($container);
        }

        return ApplicationContext::setContainer($container);
    }
}
