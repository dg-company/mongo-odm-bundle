<?php

namespace DGC\MongoODMBundle\DependencyInjection;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class DGCMongoODMExtension extends Extension
{

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('dgc_mongo_odm.config', $config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        //Register autoloader for annotations
        AnnotationRegistry::registerAutoloadNamespace("DGC\\MongoODMBundle\\Annotation", __DIR__."/../../../");

        //Register autoloader for proxy classes
        /*
        $proxyDir = $container->getParameter('kernel.cache_dir')."/".$config['proxy_dir'];
        $proxyNamespace = $config['proxy_namespace'];

        spl_autoload_register(function($class) use($proxyDir, $proxyNamespace) {
            if (strpos($class, $proxyNamespace."\\") !== false) {
                require $proxyDir."/".str_replace("\\", "", $class).".php";
            }
        });
        */
    }

}
