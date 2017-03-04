<?php

namespace DGC\MongoODMBundle\Service;

use DGC\MongoODMBundle\Document\Document;
use Symfony\Component\DependencyInjection\Container;

class ProxyFactory
{
    protected $proxyDir;
    protected $proxyNamespace;
    protected $metadataManager;
    protected $debug;
    protected $serviceContainer;

    public function __construct(string $cacheDir, array $config, bool $debug, MetadataManager $metadataManager, Container $serviceContainer)
    {
        $this->proxyDir = $cacheDir."/".$config['proxy_dir'];
        $this->proxyNamespace = $config['proxy_namespace'];
        $this->debug = $debug;
        $this->metadataManager = $metadataManager;
        $this->serviceContainer = $serviceContainer;
    }

    protected function getProxyClassName(string $class): string
    {
        return substr($class, strrpos($class, "\\")+1);
    }

    protected function getProxyNamespace(string $class): string
    {
        return $this->proxyNamespace."\\".substr($class, 0, strrpos($class, "\\"));
    }

    protected function getProxyFilename(string $class): string
    {
        return $this->proxyDir."/".str_replace("\\", "/", $class).".php";
    }

    public function getProxyForClass(string $class, $hydrationData = null)
    {
        $class = preg_replace('@[^a-z0-9\\\\_-]*@i', '', $class); //strip all invalid chars from class name to prevent code injection

        $proxyClass = $this->getProxyNamespace($class)."\\".$this->getProxyClassName($class);

        if (!class_exists($proxyClass)) {
            if (!$this->debug AND file_exists($this->getProxyFilename($class))) {
                require_once $this->getProxyFilename($class);
            } else {
                $this->generateProxy($class);
            }
        }

        return new $proxyClass($this->serviceContainer, $hydrationData);
    }

    public function getProxyForDocument(Document $document)
    {
        return $this->getProxyForClass(get_class($document), $document);
    }

    protected function generateProxy(string $class)
    {
        $filename = $this->getProxyFilename($class);
        $dir = dirname($filename);

        $code = '<?php
        
        namespace '.$this->getProxyNamespace($class).';
        
        use Symfony\Component\DependencyInjection\Container;
        
        class '.$this->getProxyClassName($class).' extends \\'.$class.' implements \\DGC\\MongoODMBundle\\Document\\DocumentProxyInterface {
        
            private $_converterManager;
            private $_documentManager;
            
            private $_hydrated = false;
        
            public function __construct(Container $serviceContainer, $hydrationData = null)
            {
            
                $this->_converterManager = $serviceContainer->get("dgc_mongo_odm.converter");
                $this->_documentManager = $serviceContainer->get("dgc_mongo_odm.document_manager");
            
                if ($hydrationData instanceof \\DGC\\MongoODMBundle\\Document) {
                    $this->_hydrateFromDocument($hydrationData);
                } elseif ($hydrationData !== null) $this->_hydrate($hydrationData);
            }
            
            public function __debugInfo(): array
            {
                $debug = [];
                foreach (get_object_vars($this) as $k=>$v) {
                    if (substr($k, 0, 1) === "_") continue;
                    $debug[$k] = $v;
                }
                return $debug;
            }
            
            private function _load()
            {
                if ($this->_hydrated) return;
                $this->_hydrate($this->_documentManager->findRawById(get_parent_class($this), $this->id));
            }
            
            private function _hydrateFromDocument($document)
            {
                foreach (get_object_vars($document) as $k=>$v) {
                    $this->{$k} = $v;
                }
            }
            
            private function _hydrate(array $data)
            {
                if ($data !== null && count($data) > 1) {
                    $this->_hydrated = true;
                }
                
        ';

        //add hydration strategy for each property
        $propertyNames = $this->metadataManager->getPropertyNames($class);
        foreach ($propertyNames as $propertyName) {

            $converter = $this->metadataManager->getConverterNameForProperty($class, $propertyName);
            $options = $this->metadataManager->getConverterSettingsForProperty($class, $propertyName);

            if ($propertyName == "id") {
                $databasePropertyName = "_id";
            } else {
                $databasePropertyName = $propertyName;
            }

            $code .= '
            if (isset($data["'.$databasePropertyName.'"])) $this->'.$propertyName.' = $this->_converterManager->get("'.$converter.'")->toObjectValue($data["'.$databasePropertyName.'"] ?? null, [
                ';

                foreach ($options as $k=>$v) {
                    $code .= "'".$k."' => '".$v."',\n";
                }

                $code .= '
                ]);            
            ';

        }

        $code .= '
            }
            
            public function _getData()
            {
                $data = [];           
        ';

        //add strategy to get database value for each property
        $propertyNames = $this->metadataManager->getPropertyNames($class);
        foreach ($propertyNames as $propertyName) {

            $converter = $this->metadataManager->getConverterNameForProperty($class, $propertyName);
            $options = $this->metadataManager->getConverterSettingsForProperty($class, $propertyName);

            if ($propertyName == "id") {
                $databasePropertyName = "_id";
            } else {
                $databasePropertyName = $propertyName;
            }

            $code .= '
                $data["'.$databasePropertyName.'"] = $this->_converterManager->get("'.$converter.'")->toDatabaseValue($this->'.$propertyName.', [
            ';

            foreach ($options as $k=>$v) {
                $code .= "'".$k."' => '".$v."',\n";
            }

            $code .= '
                ]);            
            ';

        }

        $code .= '
                return $data;
            }
        ';

        //overwrite getter functions
        $getterFunctions = $this->metadataManager->getGetterFunctions($class);
        foreach ($getterFunctions as $getterFunction) {

            $code .= '
            public function '.$getterFunction['name'].'(): '.($getterFunction['returnTypeAllowsNull']?"?":"").(!$getterFunction['returnTypeIsBuiltin']?"\\":"").$getterFunction['returnType'].'
            {
            ';

            if ($getterFunction['propertyName'] != "id") {
                $code .= '$this->_load();'."\n";
            }

            $code .= '            
                return $this->'.$getterFunction['propertyName'].';
            }
            ';

        }

        $code .= '
        }
        ';

        @mkdir($dir, 0777, true);

        file_put_contents($filename, $code);

        require_once $filename;
    }

}
