<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->removeUnusedImports();
    $rectorConfig->phpVersion(Rector\ValueObject\PhpVersion::PHP_84);
    $rectorConfig->sets([LevelSetList::UP_TO_PHP_84]);
    $rectorConfig->paths([
        __DIR__.'/src',
    ]);
};
