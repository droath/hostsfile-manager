# Hosts File Manager

[![Build Status](https://travis-ci.org/droath/hostsfile-manager.svg?branch=master)](https://travis-ci.org/droath/hostsfile-manager)

Provides a class to add/remove lines from the hosts file. It has been tested to work on *nix based systems, otherwise you'll need to supply the path to the hosts file.


## Getting Started

First, you'll need to download the hostsfile manager library using composer:

```bash
composer require droath/hostsfile-manager:^0.0.1
```

## Examples

**Add lines to the hosts file contents:**

The below code appends two entries to the hosts file contents. If any of those lines already exists then nothing is appended.

```php
<?php

    $hosts_file = (new \Droath\HostsFileManager\HostsFile())
        ->setLine('127.0.0.1', 'local.sickslap.com')
        ->setLine('127.0.0.2', 'local.hiphopsmack.com');

    (new \Droath\HostsFileManager\HostsFileWriter($hosts_file))
        ->add();
```


**Remove a single line from hosts file:**

The below code removes the one entry from the hosts file. All other lines within the hosts file remain untouched.

```php
<?php

    $hosts_file = (new \Droath\HostsFileManager\HostsFile())
        ->setLine('127.0.0.2', 'local.hiphopsmack.com');

    (new \Droath\HostsFileManager\HostsFileWriter($hosts_file))
        ->remove();
```

