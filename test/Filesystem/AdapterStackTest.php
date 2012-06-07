<?php

namespace test\Pimcore\Filesystem;
use \Pimcore\Filesystem\AdapterStack;

class AdapterStackTest extends \PHPUnit_Framework_TestCase {

    /**
     * @return \Gaufrette\Adapter
     */
    function getNewAdapterMock() {
        return $this->getMock('\Gaufrette\Adapter\InMemory');
    }

    function testConstruct() {
        $adapter = $this->getNewAdapterMock();
        $adapterStack = new AdapterStack($adapter);
        $this->assertSame($adapter, $adapterStack->getDefaultAdapter());
    }

    function testGetSetDefaultAdapter() {
        $adapterStack = new AdapterStack($this->getNewAdapterMock());
        $adapter = $this->getNewAdapterMock();
        $adapterStack->setDefaultAdapter($adapter);
        $this->assertSame($adapter, $adapterStack->getDefaultAdapter());
    }

    function testAppend() {

        $pathResolver = $this->getMock('\Pimcore\Filesystem\PathResolver');
        $pathResolver->expects($this->once())
            ->method('resolve')
            ->with($this->equalTo('/etc'))
            ->will($this->returnValue('foo'));

        $adapter = $this->getNewAdapterMock();

        $adapterStack = new AdapterStack($this->getNewAdapterMock());
        $adapterStack->setPathResolver($pathResolver);
        $adapterStack->append('/etc', $adapter);

        $this->assertSame($adapterStack['foo'], $adapter);
        $this->assertEquals(count($adapterStack), 1);



        // add one more
        $pathResolver = $this->getMock('\Pimcore\Filesystem\PathResolver');
        $pathResolver->expects($this->once())
            ->method('resolve')
            ->with($this->equalTo('/etc'))
            ->will($this->returnValue('foo2'));

        $adapterStack->setPathResolver($pathResolver);

        $adapter2 = $this->getNewAdapterMock();
        $adapterStack->append('/etc', $adapter2);

        $this->assertSame($adapterStack['foo2'], $adapter2);
        $this->assertEquals(count($adapterStack), 2);

    }


}