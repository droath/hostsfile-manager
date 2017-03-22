<?php

namespace Droath\HostsFileManager\Tests;

use Droath\HostsFileManager\HostsFile;
use Droath\HostsFileManager\HostsFileWriter;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;

/**
 * Define hosts file writer test.
 */
class HostsFileWriterTest extends TestCase
{
    /**
     * Mimic the unix /etc directory.
     *
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    protected $root;

    /**
     * Hosts file writer object.
     *
     * @var \Droath\HostsFileManager\HostsFileWriter
     */
    protected $hostsFileWriter;

    public function setUp()
    {
        $this->root = vfsStream::setup('etc');
        // Add hosts file to the /etc directory.
        $this->root->addChild(vfsStream::newFile('hosts', 0664));

        $hosts_file = (new HostsFile($this->getHostsFileUrl()))
            ->setLine('127.0.0.1', 'local.sickslap.com')
            ->setLine('127.0.0.2', 'local.hiphopsmack.com');

        $this->hostsFileWriter = (new HostsFileWriter($hosts_file));
    }

    public function testAdd()
    {
        $status = $this->hostsFileWriter
            ->add();

        $contents = file_get_contents($this->getHostsFileUrl());

        $excepted = "127.0.0.1\tlocal.sickslap.com";
        $excepted .= "\n127.0.0.2\tlocal.hiphopsmack.com";

        $this->assertNotFalse($status);
        $this->assertEquals($excepted, $contents);
    }

    public function testRemove()
    {
        $contents = "127.0.0.1\tlocal.sickslap.com";
        $contents .= "\n127.0.0.2\tlocal.hiphopsmack.com";
        $contents .= "\n127.0.0.3\tlocal.wackmactack.com";
        file_put_contents($this->getHostsFileUrl(), $contents);

        $status = $this->hostsFileWriter
            ->remove();

        $hosts_contents = file_get_contents($this->getHostsFileUrl());

        $excepted = "127.0.0.3\tlocal.wackmactack.com";

        $this->assertNotFalse($status);
        $this->assertEquals($excepted, $hosts_contents);
    }

    protected function getHostsFileUrl()
    {
        return vfsStream::url('etc/hosts');
    }
}
