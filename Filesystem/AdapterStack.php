<?php

namespace Pimcore\Filesystem;
use Pimcore\Filesystem\PathResolver;
use Gaufrette\Adapter;

class AdapterStack extends \ArrayObject {

    public $defaultAdaper = null;
    public $pathResolver = null;

    function __construct(Adapter $defaultAdaper) {
        $this->setDefaultAdapter($defaultAdaper);
    }

    /**
     * @return \Pimcore\Filesystem\PathResolver
     */
    public function getPathResolver() {
        if($this->pathResolver === null)
            $this->pathResolver = new PathResolver();

        return $this->pathResolver;
    }

    public function setPathResolver(PathResolver $pathResolver) {
        $this->pathResolver = $pathResolver;
    }

    function setDefaultAdapter($defaultAdaper) {
        $this->defaultAdaper = $defaultAdaper;
    }

    function getDefaultAdapter() {
        return $this->defaultAdaper;
    }

    function append($path, Adapter $adapter) {
        $key = $this->getPathResolver()->resolve($path);

        // TODO
        //if(!isset($this[$key]))
        //    throw new \InvalidArgumentException('adapter for Path '.$key.' is already registered');

        $this[$key] = $adapter;
    }

    /**
     * @param $filePathRaw
     * @return array
     *
     * this method returns the driver and the key (relativeFilename)
     * this is a bit ugly but if we need the key we must know the adapter
     * and if we need the adapter, we must know the key.
     */
    public function getAdapterAndKey($filePathRaw) {

        $filePath = $this->getPathResolver()->resolve($filePathRaw);

        // set default values
        $return['adapter'] = $this->getDefaultAdapter();
        $return['key'] = $filePath;
        $score = 0;

        $filePathParts = explode('/', $filePath);

        foreach($this as $currentAdapterPath => $currentAdapter) {

            $adapterPathParts = explode('/', $currentAdapterPath);
            $maxRows = min(count($adapterPathParts), count($filePathParts));

            if($score >= $maxRows)
                continue;

            for($i = 0; $i < $maxRows; $i++) {

                // if the directory Paths are different we dont have to
                // increase the score for the adapter
                if($adapterPathParts[$i] !== $filePathParts[$i])
                    break;

                // the adapter matchs some parts of the code,
                // but may there is a "better" adapter
                if($i < $score)
                    continue;

                $return['adapter'] = $currentAdapter;
                $return['key'] = join(DIRECTORY_SEPARATOR, array_slice($filePathParts, $i + 1));
                $score = $i + 1;
            }

        }

        return $return;
    }

}