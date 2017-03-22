<?php

namespace Droath\HostsFileManager;

/**
 * Define the hosts file object.
 */
class HostsFile
{
    /**
     * Hosts file path.
     *
     * @var string
     */
    protected $path;

    /**
     * Hosts file lines.
     *
     * @var array
     */
    protected $lines = [];

    /**
     * Hosts file permission.
     *
     * @var string
     */
    protected $permission;

    /**
     * Constructor for \Droath\HostsFileManager\HostsFile.
     */
    public function __construct($path_to_hostsfile = null)
    {
        $this->path = isset($path_to_hostsfile) && file_exists($path_to_hostsfile)
            ? $path_to_hostsfile
            : $this->findHostsfile();

        if (!isset($this->path)) {
            throw new \Exception(
                'Unable to locate hosts file on the filesystem.'
            );
        }

        // Set hosts file original permission.
        $this->permission = substr(sprintf('%o', fileperms($this->path)), -4);
    }

    /**
     * Is hosts file readable.
     *
     * @return bool
     */
    public function isReadable()
    {
        return is_readable($this->path);
    }

    /**
     * Is hosts file writable.
     *
     * @return bool
     */
    public function isWritable()
    {
        return is_writable($this->path);
    }

    /**
     * Set line on the hosts file.
     *
     * @param string $ip_address
     *   The hosts IP address.
     * @param string $domain
     *   The hosts domain name.
     * @param string $hostnames
     *   The hosts hostnames.
     */
    public function setLine($ip_address, $domain, $hostnames = null)
    {
        $this->lines[$domain] = [
            $ip_address,
            $domain,
            $hostnames,
        ];

        return $this;
    }

    /**
     * Get hosts file lines.
     *
     * @return array
     */
    public function getLines()
    {
        return $this->lines;
    }

    /**
     * Get hosts file path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get hosts file permission.
     *
     * @return string
     */
    public function getPermission()
    {
        return  $this->permission;
    }

    /**
     * Get hosts file formatted lines.
     *
     * @return array
     */
    public function getFormattedLines()
    {
        $lines = $this->lines;

        foreach ($lines as &$line) {
            $line = trim(implode("\t", $line));
        }

        return array_values($lines);
    }

    /**
     * Rollback hosts file permission.
     */
    public function rollbackPermission()
    {
        $status = 0;
        $output = [];
        $command = sprintf(
            'sudo chmod %d %s',
            $this->permission,
            $this->path
        );
        exec($command, $output, $status);

        if ($status !== 0) {
            throw new \Exception(sprintf(
                'Unable to rollback hosts file (%s) permission.',
                $this->path
            ));
        }

        return $output;
    }

    /**
     * Attempt to find the hosts file based on the OS.
     *
     * @return string
     *   The path to the hosts file.
     */
    protected function findHostsfile()
    {
        $os = strtolower(php_uname('s'));

        if (strpos($os, 'win') === 0) {
            return 'c:\Windows\System32\Drivers\etc\hosts';
        }

        return '/etc/hosts';
    }
}
