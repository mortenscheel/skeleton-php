#!/usr/bin/env php
<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

if (! file_exists(__DIR__.'/vendor/autoload.php')) {
    if (confirm('Run composer install?', true)) {
        passthru('composer install');
    } else {
        exit(1);
    }
}

require_once __DIR__.'/vendor/autoload.php';

$name = $argv[1] ?? ask('What is the name of the package?');
if ($name === '') {
    echo 'Package name is required.'.PHP_EOL;
    exit(1);
}
$replacements = [
    'PackageName' => Str::studly($name),
    'package-name' => Str::kebab($name),
    'mortenscheel/skeleton-php' => 'mortenscheel/'.Str::kebab($name),
];
$finder = Finder::create()
    ->in(__DIR__)
    ->files()
    ->notName('init.php')
    ->ignoreVCSIgnored(true);
foreach ($finder as $file) {
    $path = $file->getRealPath();
    replaceInFile($path, $replacements);
    $renamed = str_replace(array_keys($replacements), array_values($replacements), $path);
    if ($path !== $renamed) {
        rename($path, $renamed);
    }
}
passthru('composer dump-autoload');
unlink(__FILE__);

function ask(string $question, string $default = ''): string
{
    $answer = readline($question.($default ? " ({$default})" : null).' ');

    if (! $answer) {
        return $default;
    }

    return $answer;
}

function confirm(string $question, bool $default = false): bool
{
    $answer = ask($question.' ('.($default ? 'Y/n' : 'y/N').')');

    if (! $answer) {
        return $default;
    }

    return strtolower($answer) === 'y';
}

function replaceInFile(string $file, array $replacements): void
{
    $contents = file_get_contents($file);

    file_put_contents(
        $file,
        str_replace(
            array_keys($replacements),
            array_values($replacements),
            $contents
        )
    );
}
