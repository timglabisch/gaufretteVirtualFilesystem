<?php

namespace Pimcore\Filesystem;

class PathResolver {

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

    public function resolve($filePath) {
        if(empty($filePath))
            return '/';

        // resolving a path is very expensive, so lets first check if it it necessary
        if(strpos($filePath, '/../') !== -1)
            $filePath = $this->relativePath($filePath);

        // remove the first / if necessary, we always start from the root node.
        if($filePath[0] == '/')
            $filePath = substr($filePath, 1);

        // Path is invalide, normal times this happens if we have something
        // like /.. as path. so just use the root instead
        if(!$filePath)
            return '/';

        return $filePath;
    }

}