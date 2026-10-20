<?php

declare(strict_types=1);

use Contao\Rector\Set\ContaoLevelSetList;
use Contao\Rector\Set\ContaoSetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        instanceOf: true,
        earlyReturn: true,
    )
    ->withPhpSets()
    ->withAttributesSets(symfony: true, doctrine: true, phpunit: true)
    ->withComposerBased(twig: true, doctrine: true, phpunit: true, symfony: true)
    ->withSets([
        ContaoLevelSetList::UP_TO_CONTAO_57,
        ContaoSetList::ANNOTATIONS_TO_ATTRIBUTES,
    ])
    ->withPaths([
        __DIR__.'/contao',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withParallel()
;
