<?php

namespace Pimcore;
use Pimcore\Filesystem\AdapterStack;

class Filesystem extends \Gaufrette\Filesystem {

    /**
     * @var AdapterStack
     */
    protected $adapterStack = null;

    /**
     * Constructor
     * Sets the Fallback Adapter
     *
     * @param  Adapter $adapter A configured Adapter instance
     */
    public function __construct(\Gaufrette\Adapter $defaultAdapter) {
        $this->defaultAdapter = $defaultAdapter;
        $this->adapterStack = new AdapterStack($defaultAdapter);
    }

    public function setAdapterStack(AdapterStack $adapter) {
        return $this->adapterStack = $adapter;
    }

    public function mount($path, \Gaufrette\Adapter $adapter) {
        $this->adapterStack->append($path, $adapter);
    }

    public function getAdapter($filePath) {
        $adapterAndkey = $this->adapterStack->getAdapterAndKey($filePath);
        return $adapterAndkey['adapter'];
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
        $keyAndAdapter = $this->adapterStack->getAdapterAndKey($key);
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

        #$keyAndAdapter = $this->adapterStack->getAdapterAndKey($key);
        #return $keyAndAdapter['adapter']->rename($keyAndAdapter['key']);
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

        $keyAndAdapter = $this->adapterStack->getAdapterAndKey($key);
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

        $keyAndAdapter = $this->adapterStack->getAdapterAndKey($key);
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

        $keyAndAdapter = $this->adapterStack->getAdapterAndKey($key);
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
        $keyAndAdapter = $this->adapterStack->getAdapterAndKey($directory);

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
        $keyAndAdapter = $this->adapterStack->getAdapterAndKey($key);
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
        $keyAndAdapter = $this->adapterStack->getAdapterAndKey($key);
        return $keyAndAdapter['adapter']->checksum($keyAndAdapter['key']);
    }

    public function supportsMetadata($key)
    {
        $keyAndAdapter = $this->adapterStack->getAdapterAndKey($key);
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
        $keyAndAdapter = $this->adapterStack->getAdapterAndKey($key);
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
        $keyAndAdapter = $this->adapterStack->getAdapterAndKey($key);
        return $keyAndAdapter['adapter']->createFile($keyAndAdapter['key'], $this);
    }

}