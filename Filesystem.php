<?php

namespace Pimcore;

class Filesystem extends \Gaufrette\Filesystem {

    private $adapters = array();
    private $defaultAdapter = null;

    /**
     * Constructor
     * Sets the Fallback Adapter
     *
     * @param  Adapter $adapter A configured Adapter instance
     */
    public function __construct(\Gaufrette\Adapter $defaultAdapter) {
        $this->defaultAdapter = $defaultAdapter;
    }

    public function mount($path, \Gaufrette\Adapter $adapter) {
        $this->adapters[$this->resolvePath($path)] = $adapter;
    }

    public function getAdapter($filePath) {
        $adapterAndkey = $this->getAdapterAndKey($filePath);
        return $adapterAndkey['adapter'];
    }

    protected function relativePath($path) {
        $path = str_replace(array('/', '\\'), '/', $path);
        $parts = array_filter(explode('/', $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }

        // may we now have an empty path, if we have an empty path
        // you have to make sure, that we return a / for the main diectory
        if($path = implode(DIRECTORY_SEPARATOR, $absolutes))
            return $path;

        return '/';
    }

    protected function resolvePath($filePath) {
        if(empty($filePath))
            return '/';

        // resolving a path is very expensive, so lets first check if it it necessary
        if(strpos($filePath, '/../') !== -1)
            $filePath = $this->relativePath($filePath);

        // revmove the first / if necessary, we always start from the root node.
        if($filePath[0] == '/')
            $filePath = substr($filePath, 1);

        return $filePath;
    }

    public function getAdapterAndKey($filePathRaw) {

        $filePath = $this->resolvePath($filePathRaw);

        $return['adapter'] = $this->defaultAdapter;
        $return['key'] = $filePath;
        $score = 0;

        $filePathParts = explode('/', $filePath);

        foreach($this->adapters as $currentAdapterPath => $currentAdapter) {

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

    /**
     * Indicates whether the file matching the specified key exists
     *
     * @param  string $key
     *
     * @return boolean TRUE if the file exists, FALSE otherwise
     */
    public function has($key)
    {
        $keyAndAdapter = $this->getAdapterAndKey($key);
        return $keyAndAdapter['adapter']->exists($keyAndAdapter['key']);
    }

    /**
     * Renames a file
     *
     * @param string $key
     * @param string $new
     *
     * @return boolean TRUE if the rename was successful, FALSE otherwise
     */
    public function rename($key, $new)
    {
        throw new \Exception('not implemented now');

        $keyAndAdapter = $this->getAdapterAndKey($key);
        return $keyAndAdapter['adapter']->rename($keyAndAdapter['key']);
    }

    /**
     * Writes the given content into the file
     *
     * @param  string  $key       Key of the file
     * @param  string  $content   Content to write in the file
     * @param  boolean $overwrite Whether to overwrite the file if exists
     *
     * @return integer The number of bytes that were written into the file
     */
    public function write($key, $content, $overwrite = false, array $metadata = null)
    {
        if (!$overwrite && $this->has($key)) {
            throw new \InvalidArgumentException(sprintf('The file %s already exists and can not be overwritten.', $key));
        }

        $keyAndAdapter = $this->getAdapterAndKey($key);
        return $keyAndAdapter['adapter']->write($keyAndAdapter['key'], $content, $metadata);
    }

    /**
     * Reads the content from the file
     *
     * @param  string $key Key of the file
     *
     * @return string
     */
    public function read($key)
    {
        if (!$this->has($key)) {
            throw new \InvalidArgumentException(sprintf('The file %s does not exist.', $key));
        }

        $keyAndAdapter = $this->getAdapterAndKey($key);
        return $keyAndAdapter['adapter']->read($keyAndAdapter['key']);
    }

    /**
     * Deletes the file matching the specified key
     *
     * @param  string $key
     *
     * @return boolean
     */
    public function delete($key)
    {
        if (!$this->has($key)) {
            throw new \InvalidArgumentException(sprintf('The file %s does not exist.', $key));
        }

        $keyAndAdapter = $this->getAdapterAndKey($key);
        return $keyAndAdapter['adapter']->delete($keyAndAdapter['key']);
    }

    /**
     * Returns an array of all keys matching the specified pattern
     *
     * @return array
     */
    public function keys()
    {
        throw new \Exception('not implemented yet');
    }

    /**
     * Returns an array of all items (files and directories) matching the specified pattern
     *
     * @return array
     */
    public function listDirectory($directory = '')
    {
        $keyAndAdapter = $this->getAdapterAndKey($directory);

        if (method_exists($keyAndAdapter['adapter'], 'listDirectory'))
            return $keyAndAdapter['adapter']->listDirectory($keyAndAdapter['key']);

        return $keyAndAdapter['adapter']->keys();
    }

    /**
     * Returns the last modified time of the specified file
     *
     * @param  string $key
     *
     * @return integer An UNIX like timestamp
     */
    public function mtime($key)
    {
        $keyAndAdapter = $this->getAdapterAndKey($key);
        return $keyAndAdapter['adapter']->mtime($keyAndAdapter['key']);
    }

    /**
     * Returns the checksum of the specified file's content
     *
     * @param  string $key
     *
     * @return string A MD5 hash
     */
    public function checksum($key)
    {
        $keyAndAdapter = $this->getAdapterAndKey($key);
        return $keyAndAdapter['adapter']->checksum($keyAndAdapter['key']);
    }

    public function supportsMetadata($key)
    {
        $keyAndAdapter = $this->getAdapterAndKey($key);
        return $keyAndAdapter['adapter']->supportsMetadata($keyAndAdapter['key']);
    }

    /**
     * Creates a new file stream for the specified key
     *
     * @param  string $key
     *
     * @return FileStream
     */
    public function createFileStream($key)
    {
        $keyAndAdapter = $this->getAdapterAndKey($key);
        return $keyAndAdapter['adapter']->createFileStream($keyAndAdapter['key'], $this);
    }

    /**
     * Creates a new File instance and returns it
     *
     * @param  string $key
     *
     * @return \Gaufrette\File
     */
    public function createFile($key)
    {
        $keyAndAdapter = $this->getAdapterAndKey($key);
        return $keyAndAdapter['adapter']->createFile($keyAndAdapter['key'], $this);
    }

}