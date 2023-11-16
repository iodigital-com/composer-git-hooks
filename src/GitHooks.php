<?php

declare(strict_types=1);

namespace IODigital\ComposerGitHooks;

use IODigital\ComposerGitHooks\Exception\ProjectRootNotFoundException;
use Psr\Log\LoggerInterface;

use function chmod;
use function file_exists;
use function is_link;
use function readlink;
use function sprintf;
use function symlink;

class GitHooks
{
    private const HOOKS = [
        'applypatch-msg',
        'commit-msg',
        'fsmonitor-watchman',
        'p4-pre-submit',
        'post-applypatch',
        'post-checkout',
        'post-commit',
        'post-index-change',
        'post-merge',
        'post-rewrite',
        'pre-applypatch',
        'pre-auto-gc',
        'pre-commit',
        'pre-merge-commit',
        'pre-push',
        'pre-rebase',
        'prepare-commit-msg',
        'sendemail-validate',
    ];
    private const GIT_HOOKS_DIRECTORY = '.git/hooks';
    private const PROJECT_HOOKS_DIRECTORY = 'bin/git-hooks';
    private const PROJECT_DEFAULT_HOOK_DIRECTORIES = [
        'pre-commit',
    ];
    private const CHAIN_HOOK_FILENAME = 'scripts/chain-hook';

    private LoggerInterface $logger;
    private FileSystem $fileSystem;
    private string $projectRoot;

    public function __construct(
        LoggerInterface $logger,
        FileSystem $fileSystem
    ) {
        $this->logger = $logger;
        $this->fileSystem = $fileSystem;

        try {
            $this->projectRoot = $this->fileSystem->getProjectRoot();
            $this->logger->info(sprintf('Using project root %s', $this->projectRoot));
        } catch (ProjectRootNotFoundException $e) {
            $this->logger->warning('No project root found');
            exit(1);
        }
    }

    public function install(): void
    {
        if (!file_exists(sprintf('%s/.git', $this->projectRoot))) {
            $this->logger->error(sprintf('No .git directory found in %s', $this->projectRoot));
            return;
        }

        $this->symlinkHooks();
        $this->createProjectDefaultHookDirectories();

        $this->logger->info('Updated git-hooks');
    }

    private function symlinkHooks(): void
    {
        $target = sprintf('%s/../%s', __DIR__, self::CHAIN_HOOK_FILENAME);
        $this->setExecutablePermission($target); // Ensure the chain-hook script has the correct permissions

        foreach (self::HOOKS as $hook) {
            $link = sprintf('%s/%s/%s', $this->projectRoot, self::GIT_HOOKS_DIRECTORY, $hook);
            $relativeTarget = $this->fileSystem->getRelativePath($link, $target);

            if (!is_link($link) && !file_exists($link)) {
                symlink(
                    $relativeTarget,
                    $link
                );
                $this->setExecutablePermission($link);
                $this->logger->info(sprintf('Created symlink %s -> %s', $link, $relativeTarget));
            } elseif (!is_link($link) || readlink($link) === false || readlink($link) !== $relativeTarget) {
                $this->logger->warning(sprintf('Git hook %s already exists, not using project hooks. ' .
                    'Consider moving your custom hook to %s.', $link, self::PROJECT_HOOKS_DIRECTORY));
            }
        }
    }

    private function createProjectDefaultHookDirectories(): void
    {
        foreach (self::PROJECT_DEFAULT_HOOK_DIRECTORIES as $hook) {
            $directory = sprintf('%s/%s/%s.d', $this->projectRoot, self::PROJECT_HOOKS_DIRECTORY, $hook);
            $this->fileSystem->createDirectoryIfNotExists($directory);
        }
    }

    private function setExecutablePermission(string $filepath): bool
    {
        if (chmod($filepath, 0755) === false) {
            $this->logger->error(sprintf('Failed to set permissions on %s', $filepath));
            return false;
        }

        return true;
    }
}
