<?php

declare(strict_types=1);

namespace IODigital\ComposerGitHooks;

interface FileSystemAction
{
    public function invoke(string $source, string $dest): bool;
}
