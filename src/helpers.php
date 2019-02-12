<?php

if (!function_exists('app')) {
    /**
     * Get application instance.
     *
     * @param null $abstract
     * @param array $parameters
     *
     * @return mixed|\OpxCore\App\Application|\OpxCore\Container\Interfaces\Container
     *
     * @throws \OpxCore\Container\Exceptions\ContainerException
     * @throws \OpxCore\Container\Exceptions\NotFoundException
     */
    function app($abstract = null, array $parameters = [])
    {
        $container = \OpxCore\Container\Container::getContainer();
        if ($abstract === null) {
            return $container;
        }

        return $container->make($abstract, $parameters);
    }
}