<?php

namespace Pimcore\Filesystem;
use Pimcore\Filesystem\PathResolver;

class AdapterStack extends \ArrayObject {

    public $defaultAdaper = null;
    public $pathResolver = null;

    function __construct($defaultAdaper) {
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

    function setDefaultAdapter($defaultAdaper) {
        $this->defaultAdaper = $defaultAdaper;
    }

    function getDefaultAdapter() {
        return $this->defaultAdaper;
    }

    function append($path, $adapter) {
        $this[$this->getPathResolver()->resolve($path)] = $adapter;
    }

    public function getAdapterAndKey($filePathRaw) {

        $filePath = $this->getPathResolver()->resolve($filePathRaw);

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