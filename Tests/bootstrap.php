<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$file = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($file)) {
    throw new RuntimeException('Install dependencies to run test suite.');
}

$loader = require $file;

$databaseCreatedFile = __DIR__ . '/Application/var/cache/admin/test/adminAdminTestDebugProjectContainer';

// For dev performance create database only in case of not exist cache directory.
if (!file_exists($databaseCreatedFile)) {
    // Create test database
    $cmd = sprintf(
        'php "%s/Application/bin/console" doctrine:database:create --if-not-exists',
        __DIR__
    );

    passthru($cmd, $exitCode);

    if ($exitCode) {
        exit($exitCode);
    }

    // Create or update test database schema
    $cmd = sprintf(
        'php "%s/Application/bin/console" doctrine:schema:update --force',
        __DIR__
    );

    passthru($cmd, $exitCode);

    if ($exitCode) {
        exit($exitCode);
    }
}
