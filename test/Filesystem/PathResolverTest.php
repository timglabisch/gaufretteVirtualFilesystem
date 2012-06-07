<?php

namespace test\Pimcore\Filesystem;
use \Pimcore\Filesystem\PathResolver;


class PathResolverTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var \Pimcore\Filesystem\PathResolver
     */
    public $pathResolver;

    function setUp() {
        $this->pathResolver = new PathResolver();
    }

    function testResolve() {

        // checks ..
        $this->assertEquals($this->pathResolver->resolve(''), '/');
        $this->assertEquals($this->pathResolver->resolve('/..'), '/');
        $this->assertEquals($this->pathResolver->resolve('/../..'), '/');
        $this->assertEquals($this->pathResolver->resolve('/foo/..'), '/');
        $this->assertEquals($this->pathResolver->resolve('/foo/../../foo/..'), '/');
        $this->assertEquals($this->pathResolver->resolve('/foo/../../foo'), 'foo');
        $this->assertEquals($this->pathResolver->resolve('/foo/../../foo/foo2'), 'foo/foo2');
        $this->assertEquals($this->pathResolver->resolve('foo/../../foo/foo2'), 'foo/foo2');
        $this->assertEquals($this->pathResolver->resolve('foo/../../foo/foo2/'), 'foo/foo2');

        // checks .
        $this->assertEquals($this->pathResolver->resolve('.'), '/');
        $this->assertEquals($this->pathResolver->resolve('/.'), '/');
        $this->assertEquals($this->pathResolver->resolve('/./././foo'), 'foo');

        // complex mixes
        $this->assertEquals($this->pathResolver->resolve('/foo/../../foo/../foo2/blub/.././bla/blubb'), 'foo2/bla/blubb');
        $this->assertEquals($this->pathResolver->resolve('foo/../../foo/../foo2/blub/.././bla/blubb'), 'foo2/bla/blubb');
        $this->assertEquals($this->pathResolver->resolve('/foo/../../foo/../foo2/blub/.././bla/blubb/'), 'foo2/bla/blubb');

        //paths with dots
        $this->assertEquals($this->pathResolver->resolve('/foo/../../foo/../foo2/blub/.././bla/blubb/foo.jpg'), 'foo2/bla/blubb/foo.jpg');
        $this->assertEquals($this->pathResolver->resolve('foo/../../foo/../foo2/blub/.././bla/blubb/foo.jpg'), 'foo2/bla/blubb/foo.jpg');

    }

}