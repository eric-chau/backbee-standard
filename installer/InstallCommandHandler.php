<?php

namespace BackBee\Installer;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\IO\IO;

/**
 * @author Eric Chau <eriic.chau@gmail.com>
 */
class InstallCommandHandler
{
    const DIR_READABLE = 1;
    const DIR_WRITABLE = 1;

    /**
     * @var IO
     */
    private $io;
    private $stopJob = false;

    public function handle(Args $args, IO $io, Command $command)
    {
        $this->io = $io;

        try {
            $this
                ->runFoundationJob()
                ->runBootstrapJob()
                ->runDatabaseJob()
                ->runSiteJob()
                ->runFixtureJob()
            ;
        } catch (\RuntimeException $e) {
            $msg =
                "\n  <fail>Whoops, seems that something went wrong with your environment.\n"
                . "  Please check and fix errors listed above to continue BackBee installation.</fail>"
            ;

            $this->io->writeLine($msg);
        }

        return 0;
    }

    protected function getCacheDir()
    {
        return __DIR__ . '/../cache';
    }

    protected function getLogDir()
    {
        return __DIR__ . '/../log';
    }

    protected function getDataDir()
    {
        return __DIR__ . '/../repository/Data';
    }

    protected function mkdir($dir, $mode = 0755)
    {
        $msg = null;
        if (!is_dir($dir)) {
            mkdir($dir, $mode);
            $msg = sprintf(
                '    <success>✓   Directory "%s" has been created with mode "%s".</success>',
                realpath($dir),
                decoct($mode)
            );
        } else {
            $msg = sprintf(
                '    o   Directory "%s" already exists.',
                realpath($dir),
                decoct($mode)
            );
        }


        $this->io->writeLine($msg);

        return $this;
    }

    protected function reachabledir($dir, $mode = self::DIR_READABLE, $exceptionOnError = false)
    {
        if ($mode & self::DIR_READABLE && !is_readable($dir)) {
            $this->io->writeLine(sprintf(
                '<fail>    x   Directory "%s" must be readable.</fail>',
                realpath($dir)
            ));

            $this->stopJob = true;
        }

        if ($mode & self::DIR_WRITABLE && !is_writable($dir)) {
            $this->io->writeLine(sprintf(
                '<fail>    x   Directory "%s" must be writable.</fail>',
                realpath($dir)
            ));

            $this->stopJob = true;
        }

        return $this;
    }

    protected function runFoundationJob()
    {
        $this->io->writeLine("\n  <b># Running 'Foundation Job'</b>");
        $this->io->writeLine('');

        $umask = umask();
        umask(0);

        $this->mkdir($cacheDir = $this->getCacheDir(), 0777)->reachabledir($cacheDir);
        $this->mkdir($logDir = $this->getLogDir(), 0777);

        self::mkdir($dataDir = $this->getDataDir(), 0777);
        self::mkdir($dataDir . '/Media', 0777);
        self::mkdir($dataDir . '/Storage', 0777);
        self::mkdir($dataDir . '/Tmp', 0777);

        umask($umask);

        $this->io->writeLine('');

        if ($this->stopJob) {
            throw new \RuntimeException();
        }

        return $this;
    }

    protected function runBootstrapJob()
    {
        $this->io->writeLine("\n  <b># Running 'Bootstrap Job'</b>");
        $this->io->writeLine('');



        $this->io->writeLine('');

        return $this;
    }

    protected function runDatabaseJob()
    {
        $this->io->writeLine("\n  <b># Running 'Database Job'</b>");
        $this->io->writeLine('');



        $this->io->writeLine('');

        return $this;
    }

    protected function runSiteJob()
    {
        $this->io->writeLine("\n  <b># Running 'Site Job'</b>");
        $this->io->writeLine('');



        $this->io->writeLine('');

        return $this;
    }

    protected function runFixtureJob()
    {
        $this->io->writeLine("\n  <b># Running 'Fixture Job'</b>");
        $this->io->writeLine('');



        $this->io->writeLine('');

        return $this;
    }

    private function buildBootstrapFile()
    {
        $containerDir = $this->checkCmdHandler->getCacheDir().'/container';
        if (!is_dir($containerDir)) {
            mkdir($containerDir, 755);
        }

        $bootstrap = [
            'debug'     => (bool) intval($_POST['debug']),
            'container' => [
                'dump_directory' => $containerDir,
                'autogenerate'   => true
            ]
        ];

        file_put_contents(dirname(__DIR__).'/repository/Config/bootstrap.yml', $yaml->dump($bootstrap));
    }
}
