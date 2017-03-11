<?php

namespace Brendt\Stitcher\Command;

use Brendt\Stitcher\Stitcher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class CleanCommand extends Command
{
    const FORCE = 'force';

    /**
     * @var Filesystem
     */
    private $fs;

    protected function configure() {
        $this->setName('site:clean')
            ->setDescription('Do a cleanup')
            ->setHelp("This command cleans all generated files.")
            ->addOption(self::FORCE, InputArgument::OPTIONAL);

        $this->fs = new Filesystem();
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        Stitcher::create();

        $force = $input->getOption(self::FORCE);
        $publicDir = Stitcher::getParameter('directories.public');
        $cacheDir = Stitcher::getParameter('directories.cache');


        $this->checkForcedClean($force, $publicDir, $cacheDir, $input, $output);

        $log = [];
        $log[] = $this->cleanPublicDir($publicDir);
        $log[] = $this->cleanCacheDir($cacheDir);

        $output->writeln("Successfully cleaned up\n");

        foreach ($log as $line) {
            $output->writeln("- {$line}");
        }

        $output->writeln("\nRun <fg=green>site:generate</> to generate these files again.");
    }

    /**
     * @param bool            $force
     * @param string          $publicDir
     * @param string          $cacheDir
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    private function checkForcedClean($force = false, string $publicDir, string $cacheDir, InputInterface $input, OutputInterface $output) : void {
        if ($force) {
            return;
        }

        $questionHelper = $this->getHelper('question');
        $question = new Question("Are you sure you want to clean all generated files ({$publicDir} and {$cacheDir}) [y/N] ", false);

        if (!$questionHelper->ask($input, $output, $question)) {
            return;
        }
    }

    /**
     * @param string $publicDir
     *
     * @return null|string
     */
    private function cleanPublicDir(string $publicDir) : string {
        if (!$this->fs->exists($publicDir)) {
            return '';
        }

        $publicDirectories = Finder::create()->directories()->in($publicDir);
        $directoryPaths = [];

        /** @var SplFileInfo $directory */
        foreach ($publicDirectories as $directory) {
            $directoryPaths[] = $directory->getPathname();
        }

        $this->fs->remove($directoryPaths);

        $publicFiles = Finder::create()->files()->in($publicDir)->notName('.htaccess')->ignoreDotFiles(true);

        /** @var SplFileInfo $file */
        foreach ($publicFiles as $file) {
            $this->fs->remove($file->getPathname());
        }

        return "Cleaned the public directory: {$publicDir}";
    }

    /**
     * @param string $cacheDir
     *
     * @return null|string
     */
    private function cleanCacheDir(string $cacheDir) : ?string {
        if (!$this->fs->exists($cacheDir)) {
            return '';
        }

        $this->fs->remove($cacheDir);

        return "Removed the cache directory: {$cacheDir}";
    }
}
