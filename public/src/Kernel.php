<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        $contents = require $this->getProjectDir().'/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if ($envs[$this->getEnvironment()] ?? false) {
                yield new $class();
            }
        }
    }

    protected function configureContainer(LoaderInterface $loader, array $configs): void
    {
        $loader->load($this->getProjectDir().'/config/{packages}/*.yaml');
        $loader->load($this->getProjectDir().'/config/{services}.yaml');
    }

    protected function configureRoutes(LoaderInterface $loader): void
    {
        $loader->load($this->getProjectDir().'/config/{routes}/*.yaml');
        $loader->load($this->getProjectDir().'/config/routes.yaml');
    }
}
