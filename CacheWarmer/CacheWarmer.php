<?php

namespace DGC\MongoODMBundle\CacheWarmer;

use DGC\MongoODMBundle\Document\Document;
use DGC\MongoODMBundle\Service\ProxyFactory;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\HttpKernel\Kernel;

class CacheWarmer implements CacheWarmerInterface
{
    protected $kernel;
    protected $proxyFactory;

    public function __construct(Kernel $kernel, ProxyFactory $proxyFactory)
    {
        $this->kernel = $kernel;
        $this->proxyFactory = $proxyFactory;
    }


    /**
     * @inheritdoc
     */
    public function warmUp($cacheDir)
    {
        foreach ($this->kernel->getBundles() as $bundle) {
            if ($bundle->getName() == "DGCMongoODMBundle") continue;

            $documentDir = $bundle->getPath()."/Document";

            if (is_dir($documentDir)) {
                foreach (scandir($documentDir) as $file) {
                    if (stripos($file, ".php") === false) continue;

                    $class = $bundle->getNamespace()."/Document/".str_replace(".php", "", $file);
                    $class = str_replace("/", "\\", $class);

                    $object = new $class();

                    if ($object instanceof Document) {

                        $this->proxyFactory->getProxyForClass($class);

                    }

                }
            }

        }
    }

    /**
     * @inheritdoc
     */
    public function isOptional()
    {
        return true;
    }

}
