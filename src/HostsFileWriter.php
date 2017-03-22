<?php

namespace Droath\HostsFileManager;

/**
 * Define the hosts file writer object.
 */
class HostsFileWriter
{
    /**
     * Hosts file object.
     *
     * @var \Droath\HostsFileManager\HostsFile
     */
    protected $hostsFile;

    /**
     * Hosts file permission updated state.
     *
     * @var bool
     */
    protected $permissionUpdated = false;

    /**
     * Constructor for the \Droath\HostsFileManager\HostsFileWriter class.
     *
     * @param \Droath\HostsFileManager\HostsFile $hosts_file
     *   The hosts file object.
     */
    public function __construct(HostsFile $hosts_file)
    {
        $this->hostsFile = $hosts_file;
    }

    /**
     * Add lines that were set on the hosts file object.
     */
    public function add()
    {
        $contents = $this
            ->checkPermissions()
            ->addLinesToHostsFile();

        return $this->updateHostsFileContents($contents);
    }

    /**
     * Remove lines that were set on the hosts file object.
     */
    public function remove()
    {
        $contents = $this
            ->checkPermissions()
            ->getHostsFileContentsWithoutDupes();

        return $this->updateHostsFileContents($contents);
    }

    /**
     * Add hosts file lines to existing hosts file contents.
     */
    protected function addLinesToHostsFile()
    {
        $contents = $this
            ->getHostsFileContentsWithoutDupes();

        return array_merge(
            $contents,
            $this->hostsFile->getFormattedLines()
        );
    }

    /**
     * Update hosts file contents.
     *
     * @param array $contents
     *   An array of the host file contents.
     *
     * @return int|bool
     *   The amount of bytes written to the filesystem; otherwise false.
     */
    protected function updateHostsFileContents(array $contents)
    {
        $contents = implode("\n", $contents);
        $file_status = file_put_contents($this->hostsFileName(), $contents);

        // Rollback permission on hosts file if they've been updated.
        if ($this->permissionUpdated) {
            $this->hostsFile->rollbackPermission();
        }

        return $file_status;
    }

    /**
     * Get hosts file contents without line duplications.
     *
     * @return array
     *   The processed hosts file contents; without line duplicates.
     */
    protected function getHostsFileContentsWithoutDupes()
    {
        $lines = $this->hostsFile->getLines();

        return $this->processHostsFile(function ($line, &$contents) use ($lines) {
            if (!empty($line) && strpos($line, '#') !== 0) {
                $parsed_line = array_values(
                    array_filter(preg_split('/\s/', $line))
                );
                $domain = $parsed_line[1];

                if (isset($lines[$domain])
                    && $lines[$domain][0] === $parsed_line[0]) {
                    return;
                }
            }

            $contents[] = $line;
        });
    }

    /**
     * Process hosts file contents.
     *
     * @param callable $callable
     *   A callable function to process the hosts file line-by-line.
     *
     * @return array
     *   An array of the hosts file contents.
     */
    protected function processHostsFile(callable $callable)
    {
        $handle = fopen($this->hostsFileName(), 'r');
        $contents = [];

        while (($line = fgets($handle)) !== false) {
            call_user_func_array($callable, [trim($line), &$contents]);
        }
        fclose($handle);

        return $contents;
    }

    /**
     * Check if hosts file is accessible.
     *
     * If hosts file is not accessible then ask for sudo access to change the
     * permissions temporarily, the original permissions will be rolled back
     * after the hosts file contents has been updated.
     *
     * @see \Droath\HostsFileManager\updateHostsFileContents
     */
    protected function checkPermissions()
    {
        if (!$this->isAccessible()) {
            $command = sprintf(
                'sudo chmod o+w %s',
                $this->hostsFile->getPath()
            );
            exec($command);

            // Set the permission updated state.
            $this->permissionUpdated = true;
        }

        return $this;
    }

    /**
     * Determine if hosts files is accessible.
     *
     * @return bool
     */
    protected function isAccessible()
    {
        return $this->hostsFile->isReadable()
            && $this->hostsFile->isWritable();
    }

    /**
     * Hosts file path.
     *
     * @return string
     */
    protected function hostsFileName()
    {
        return $this->hostsFile->getPath();
    }
}
