<?php
declare(strict_types=1);

namespace Smartresponsor\ProductSuiteInstaller;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem;

final class Plugin implements PluginInterface, EventSubscriberInterface
{
    private Composer $composer;
    private IOInterface $io;

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'postInstall',
            ScriptEvents::POST_UPDATE_CMD => 'postInstall'
        ];
    }

    public function postInstall(): void
    {
        $this->io->write('<info>[product-suite]</info> installing scaffolding…');
        $projRoot = getcwd();
        $fs = new Filesystem();

        $map = [
            // source within the package => destination in project
            'stubs/docker' => 'docker',
            'stubs/scripts' => 'scripts',
            'stubs/Makefile' => 'Makefile',
            'stubs/.env.example' => '.env.example',
            'stubs/qa' => 'qa',
            'stubs/.github' => '.github',
        ];

        foreach ($map as $src => $dst) {
            $srcPath = __DIR__ . '/../../' . $src;
            $dstPath = $projRoot . '/' . $dst;
            if (!file_exists($dstPath)) {
                $this->copy($srcPath, $dstPath);
                $this->io->write("  - copied <comment>$dst</comment>");
            } else {
                $this->io->write("  - skip $dst (exists)");
            }
        }

        // Ensure PSR-4 namespace
        $composerJson = $projRoot . '/composer.json';
        if (file_exists($composerJson)) {
            $json = json_decode((string)file_get_contents($composerJson), true, 512, JSON_THROW_ON_ERROR);
            $json['autoload']['psr-4']['App\\Component\\Product\\'] = 'src/';
            file_put_contents($composerJson, json_encode($json, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
            $this->io->write('  - updated composer.json autoload (App\\Component\\Product\\)');
        }

        $this->io->write('<info>[product-suite]</info> done.');
    }

    private function copy(string $src, string $dst): void
    {
        if (is_dir($src)) {
            @mkdir($dst, 0777, true);
            $items = scandir($src) ?: [];
            foreach ($items as $i) {
                if ($i === '.' || $i === '..') continue;
                $this->copy($src . '/' . $i, $dst . '/' . $i);
            }
        } else {
            @mkdir(dirname($dst), 0777, true);
            copy($src, $dst);
        }
    }
}
