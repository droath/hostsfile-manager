<?php

namespace Droath\HostsFileManager\Tests;

use Droath\HostsFileManager\HostsFile;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;

/**
 * Define the hosts file test.
 */
class HostsFileTest extends TestCase
{
    /**
     * Mimic the unix /etc directory.
     *
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    protected $root;

    /**
     * Hosts file object.
     *
     * @var \Droath\HostsFileManager\HostsFile
     */
    protected $hostsFile;

    /**
     * Hosts file permissions.
     *
     * @var int
     */
    protected $hostsPerm = 0664;

    public function setUp()
    {
        $this->root = vfsStream::setup('etc');
        // Add hosts file to the /etc directory.
        $this->root->addChild(vfsStream::newFile('hosts', $this->hostsPerm));

        $this->hostsFile = new HostsFile($this->getHostsFileUrl());
    }

    public function testIsReadable()
    {
        $is_readable = $this->hostsFile->isReadable();
        $this->assertTrue($is_readable);
    }

    public function testIsWrittable()
    {
        $is_writable = $this->hostsFile->isWritable();
        $this->assertTrue($is_writable);
    }

    public function testSetLine()
    {
        $this->hostsFile
            ->setLine('127.0.0.1', 'local.google.com');

        $lines = $this->hostsFile->getLines();

        $this->assertArrayHasKey('local.google.com', $lines);
        $this->assertEquals('127.0.0.1', $lines['local.google.com'][0]);
    }

    public function testGetPermission()
    {
        $permission = $this->hostsFile->getPermission();
        $this->assertSame('0664', $permission);
    }

    public function testGetFormattedLines()
    {
        $this->hostsFile
            ->setLine('192.168.1.1', 'local.cnn.com')
            ->setLine('127.0.0.1', 'local.google.com');

        $formatted_lines = $this->hostsFile->getFormattedLines();

        $this->assertEquals("192.168.1.1\tlocal.cnn.com", $formatted_lines[0]);
        $this->assertEquals("127.0.0.1\tlocal.google.com", $formatted_lines[1]);
    }

    /**
     * @expectedException Exception
     */
    public function testRollbackPermission()
    {
        $filename = $this->getHostsFileUrl();
        chmod($filename, 0555);

        $this->assertEquals('0555', $this->getFilePermission($filename));

        $this->hostsFile->rollbackPermission();
    }

    protected function getFilePermission($filename)
    {
        return substr(sprintf('%o', fileperms($filename)), -4);
    }

    protected function getHostsFileUrl()
    {
        return vfsStream::url('etc/hosts');
    }
}
