<?php

namespace test\Pimcore;
use \Gaufrette\Adapter\InMemory as AdapterInMemory;
use \Gaufrette\File;
use \Pimcore\Filesystem as Filesystem;


require_once 'bootstrap.php';

class FilesystemTest extends \PHPUnit_Framework_TestCase {

    /**
     * this test covers the defult mount adapter
     */
    function testConstruct() {

        $adapter = new AdapterInMemory();
        $filesystem = new Filesystem($adapter);

        $file = new File('testFile', $filesystem);
        $file->setContent('Hello World!');

        $this->assertEquals($filesystem->get('/testFile')->getContent(), 'Hello World!');

    }

    /**
     * this test covers the default mount adapter
     */
    function testSubfile() {

        $adapter = new AdapterInMemory();
        $filesystem = new Filesystem($adapter);

        $file = new File('testFile', $filesystem);
        $file->setContent('Hello World!');

        $file = new File('testFile/File2', $filesystem);
        $file->setContent('File2!!');

        $this->assertEquals($filesystem->get('/testFile')->getContent(), 'Hello World!');
        $this->assertEquals($filesystem->get('/testFile/File2')->getContent(), 'File2!!');

    }

    /**
     * Tests if the driver also allows a / as first letter
     */
    function testRelativePath() {

        $adapter = new AdapterInMemory();
        $filesystem = new Filesystem($adapter);

        $file = new File('testFile', $filesystem);
        $file->setContent('Hello World!');

        $this->assertEquals($filesystem->get('/testFile')->getContent(), 'Hello World!');
        $this->assertEquals($filesystem->get('testFile')->getContent(), 'Hello World!');
        $this->assertEquals($filesystem->get('foo/../testFile')->getContent(), 'Hello World!');
        $this->assertEquals($filesystem->get('/foo/../testFile')->getContent(), 'Hello World!');
        $this->assertEquals($filesystem->get('foo/2/../4/../../testFile')->getContent(), 'Hello World!');
        $this->assertEquals($filesystem->get('/foo/2/../4/../../testFile')->getContent(), 'Hello World!');
    }

    function testRelativePathForMount() {

        $adapter = new AdapterInMemory();
        $filesystem = new Filesystem($adapter);

        $etc = new AdapterInMemory();
        $var = new AdapterInMemory();
        $var_www = new AdapterInMemory();

        $filesystem->mount('/etc', $etc);
        $filesystem->mount('/var', $var);
        $filesystem->mount('/var/www', $var_www);

        $this->assertSame($filesystem->getAdapter('/etc/../var'), $var);
        $this->assertSame($filesystem->getAdapter('/etc/../var/../etc'), $etc);
        $this->assertSame($filesystem->getAdapter('/var/www/../../var/www/../www'), $var_www);
    }

    function testGetAdapter() {

        $adapter = new AdapterInMemory();
        $filesystem = new Filesystem($adapter);

        $etc = new AdapterInMemory();
        $var = new AdapterInMemory();
        $var_www = new AdapterInMemory();

        $filesystem->mount('/etc', $etc);
        $filesystem->mount('/var', $var);
        $filesystem->mount('/var/www', $var_www);

        // Files
        // have a look at testRelativePath, we testet all variants here.
        $this->assertSame($filesystem->getAdapter('/etc/newFile'), $etc);
        $this->assertSame($filesystem->getAdapter('/var/newFile'), $var);
        $this->assertSame($filesystem->getAdapter('/var/www/newFile'), $var_www);
        $this->assertSame($filesystem->getAdapter('/unknown'), $adapter);

        // Directories
        // with / as prefix
        $this->assertSame($filesystem->getAdapter('/etc'), $etc);
        $this->assertSame($filesystem->getAdapter('/var'), $var);
        $this->assertSame($filesystem->getAdapter('/var/www'), $var_www);
        $this->assertSame($filesystem->getAdapter('/'), $adapter);
        // without / as prefix
        $this->assertSame($filesystem->getAdapter('etc'), $etc);
        $this->assertSame($filesystem->getAdapter('var'), $var);
        $this->assertSame($filesystem->getAdapter('var/www'), $var_www);
        $this->assertSame($filesystem->getAdapter(''), $adapter); // this looks a bit ugly but if we support the paths above...
        // without / as suffix
        $this->assertSame($filesystem->getAdapter('etc/'), $etc);
        $this->assertSame($filesystem->getAdapter('var/'), $var);
        $this->assertSame($filesystem->getAdapter('var/www/'), $var_www);
        // without / as pre and suffix
        $this->assertSame($filesystem->getAdapter('/etc/'), $etc);
        $this->assertSame($filesystem->getAdapter('/var/'), $var);
        $this->assertSame($filesystem->getAdapter('/var/www/'), $var_www);
        $this->assertSame($filesystem->getAdapter('//'), $adapter); // this looks a bit ugly but if we support the paths above...
    }

    function testHas() {
        $adapter = $this->getMock('\Gaufrette\Adapter\InMemory');
        $adapter->expects($this->once())
                ->method('exists')
                ->with($this->equalTo('foo'));

        $filesystem = new Filesystem($adapter);
        $filesystem->has('/foo');
    }

    function testRename() {
        $this->markTestIncomplete('todo');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testGet() {
        $adapter = new AdapterInMemory();
        $filesystem = new Filesystem($adapter);
        $filesystem->get('/file');
    }

    function testGetAndCreate() {
        $adapter = new AdapterInMemory();
        $filesystem = new Filesystem($adapter);
        $filesystem->get('/file', true)->setContent('test!');
        $this->assertEquals($filesystem->get('/file')->getContent(), 'test!');
    }

    function testWrite() {
        $adapter = $this->getMock('\Gaufrette\Adapter\InMemory');
        $adapter->expects($this->once())
            ->method('write')
            ->with($this->equalTo('foo'), $this->equalTo('content'), $this->equalTo(array('metdata')));

        $filesystem = new Filesystem($adapter);
        $filesystem->write('/foo', 'content', false, array('metdata'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testWriteOverwrite() {
        $adapter = new AdapterInMemory();
        $filesystem = new Filesystem($adapter);
        $filesystem->write('/foo', 'content', false, array('metdata'));
        $filesystem->write('/foo', 'content');
    }

    function testRead() {
        $adapter = new AdapterInMemory();
        $filesystem = new Filesystem($adapter);
        $filesystem->write('/foo', 'content');
        $this->assertEquals($filesystem->read('/foo'), 'content');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testReadFileNotFound() {
        $adapter = new AdapterInMemory();
        $filesystem = new Filesystem($adapter);
        $filesystem->read('/foo');
    }

    function testDelete() {
        $adapter = new AdapterInMemory();
        $filesystem = new Filesystem($adapter);
        $filesystem->write('/foo', 'content');
        $this->assertTrue($filesystem->has('/foo'));
        $filesystem->delete('/foo');
        $this->assertFalse($filesystem->has('/foo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testDeleteNotFound() {
        $adapter = new AdapterInMemory();
        $filesystem = new Filesystem($adapter);
        $filesystem->delete('/foo');
    }

    function testGetKeys() {
        $this->markTestIncomplete();
    }

    function testListDirectory() {
        $adapter = $this->getMock('\Gaufrette\Adapter\InMemory');
        $adapter->expects($this->once())
            ->method('keys');

        $filesystem = new Filesystem($adapter);
        $filesystem->listDirectory('/foo');
    }

    function testListDirectoryAdapterSupports() {
        $this->markTestIncomplete('find a solution to mock this. method_exists isnt easy to mock out of the box');
    }

    function testMtime() {
        $adapter = $this->getMock('\Gaufrette\Adapter\InMemory');
        $adapter->expects($this->once())
            ->method('mtime')
            ->with($this->equalTo('foo'));

        $filesystem = new Filesystem($adapter);
        $filesystem->mtime('/foo');
    }

    function testChecksum() {
        $adapter = $this->getMock('\Gaufrette\Adapter\InMemory');
        $adapter->expects($this->once())
            ->method('checksum')
            ->with($this->equalTo('foo'));

        $filesystem = new Filesystem($adapter);
        $filesystem->checksum('/foo');
    }

    function testSupportsMetadata() {
        $adapter = $this->getMock('\Gaufrette\Adapter\InMemory');
        $adapter->expects($this->once())
            ->method('supportsMetadata')
            ->with($this->equalTo('foo'));

        $filesystem = new Filesystem($adapter);
        $filesystem->supportsMetadata('/foo');
    }

    function testCreateFileStream() {
        $adapter = $this->getMock('\Gaufrette\Adapter\InMemory');
        $adapter->expects($this->once())
            ->method('createFileStream')
            ->with($this->equalTo('foo'));

        $filesystem = new Filesystem($adapter);
        $filesystem->createFileStream('/foo');
    }

    function testCreateFile() {
        $adapter = $this->getMock('\Gaufrette\Adapter\InMemory');
        $adapter->expects($this->once())
            ->method('createFile')
            ->with($this->equalTo('foo'));

        $filesystem = new Filesystem($adapter);
        $filesystem->createFile('/foo');
    }


}