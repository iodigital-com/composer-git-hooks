<?php

declare(strict_types=1);

namespace IODigital\ComposerGitHooks;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

class ComposerPlugin implements PluginInterface, EventSubscriberInterface
{
    public function activate(Composer $composer, IOInterface $io): void
    {
        // noop
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
        // noop
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
        // noop
    }

    /**
     * @inheritDoc
     * @return array<string, string|array{0: string, 1?: int}|array<array{0: string, 1?: int}>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'process',
            ScriptEvents::POST_UPDATE_CMD => 'process',
        ];
    }

    public static function process(Event $event): void
    {
        $io = $event->getIO();

        $fileSystem = new FileSystem($io);
        $gitHooks = new GitHooks($io, $fileSystem);
        $gitHooks->install();
    }
}
