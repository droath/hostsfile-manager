# Hosts File Manager

[![Build Status](https://travis-ci.org/droath/hostsfile-manager.svg?branch=master)](https://travis-ci.org/droath/hostsfile-manager)

Provides a class to add/remove lines from the hosts file. It has been tested to work on *nix based systems, otherwise you'll need to supply the path to the hosts file.

## Examples

**Add lines to hosts file:**
The below code adds two entries into the hosts file.
```php
    $hosts_file = (new \Droath\HostsFileManager\HostsFile())
        ->setLine('127.0.0.1', 'local.sickslap.com')
        ->setLine('127.0.0.2', 'local.hiphopsmack.com');

    (new \Droath\HostsFileManager\HostsFileWriter($hosts_file))
        ->add();
```


**Remove lines to hosts file:**
The below code removes the one entry from the hosts file.
```php
    $hosts_file = (new \Droath\HostsFileManager\HostsFile())
        ->setLine('127.0.0.2', 'local.hiphopsmack.com');

    (new \Droath\HostsFileManager\HostsFileWriter($hosts_file))
        ->remove();
```

