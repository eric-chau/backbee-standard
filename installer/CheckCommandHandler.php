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
class CheckCommandHandler
{
    const REQUIRED_PHP_VERSION = '5.4.0';
    const DB_CHARSET = 'utf8';
    const DB_COLLATION = 'utf8_general_ci';

    private $logs = [];

    public function handle(Args $args, IO $io, Command $command)
    {
        foreach ($this->getRequirements() as $item) {
            if ($item instanceof Requirement) {
                $io->writeLine($this->formatRequirementMsg($item));
            } elseif ($item instanceof RequirementBag) {
                $io->writeLine('     <b>' . $item->getTitle() . '</b>');
                foreach ($item->getRequirements() as $requirement) {
                    $io->writeLine("\t" . $this->formatRequirementMsg($requirement));
                }
            }
        }

        $io->writeLine("\n  <warn>Logs:</warn>");
        if ($this->logs) {
            foreach ($this->logs as $msg) {
                $io->writeLine("    - $msg");
            }
        } else {
            $io->writeLine('    -');
        }

        $io->writeLine("\n" . ($this->isOk()
            ? '  <success>Everything is fine, run the "installer install" command and start to use BackBee!</success>'
            : '  <fail>Whoops, seems that your environment is not ready to install BackBee.</fail>'
        ));

        return 0;
    }

    public function isOk()
    {
        foreach ($this->getRequirements() as $requirement) {
            if (!$requirement->isOk()) {
                return false;
            }
        }

        return true;
    }

    public function getCacheDir()
    {
        return realpath(__DIR__ . '/../cache');
    }

    public function getLogDir()
    {
        return realpath(__DIR__ . '/../log');
    }

    private function formatRequirementMsg(Requirement $requirement)
    {
        $isOk = $requirement->isOk() ? '✓' : 'X';
        $tag = $requirement->isOk() ? 'success' : 'fail';

        return sprintf("<$tag>  %s</$tag>  %s", $isOk, $requirement->getTitle());
    }

    private function getRequirements()
    {
        $requirements = [];

        $requirements[] = new Requirement(
            true,
            version_compare(phpversion(), self::REQUIRED_PHP_VERSION, '>='),
            'Version of PHP - required >= ' . self::REQUIRED_PHP_VERSION
        );

        $cacheDir = $this->getCacheDir();
        $requirements[] = new Requirement(
            true,
            is_dir($cacheDir) && is_writable($cacheDir) && is_readable($cacheDir),
            "Cache folder - readable and writable ($cacheDir)"
        );

        $logDir = $this->getLogDir();
        $requirements[] = new Requirement(
            true,
            is_dir($logDir) && is_writable($logDir) && is_readable($logDir),
            "Log folder - readable and writable ($logDir)"
        );

        $configDir = realpath(__DIR__ . '/../repository/Config');
        $requirements[] = new Requirement(
            true,
            is_dir($configDir) && is_readable($configDir) && is_writable($configDir),
            "Config directory - writable and readable ($configDir)"
        );

        return array_merge($requirements, $this->getAdvancedRequirement());
    }

    private function getAdvancedRequirement()
    {
        $dbFile = __DIR__ . '/settings.yml';
        $bag = new RequirementBag("Settings file ($dbFile)");

        $bag->addRequirement(new Requirement(
            true,
            is_file($dbFile) && is_readable($dbFile) && is_writable($dbFile),
            'Readable'
        ));

        if ($bag->isOk()) {
            $isValid = true;
            try {
                $settings = Yaml::parse(file_get_contents($dbFile));
            } catch (ParseException $e) {
                $isValid = false;
            }

            $bag->addRequirement(new Requirement(true, $isValid, 'Valid YAML format'));

            if ($bag->isOk()) {
                $isValid = false;

                $host = $settings['db_conn']['host'];
                $port = $settings['db_conn']['port'];
                $username = $settings['db_conn']['user'];
                $password = $settings['db_conn']['password'];
                $dbname = $settings['db_conn']['dbname'];

                try {
                    $pdo = new \PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
                    $this->logs[] = "Database '$dbname' already exists.";
                    $isValid = true;
                } catch (\PDOException $e) {
                    try {
                        $result = false;
                        if ($conn = mysqli_connect($host, $username, $password, null, $port)) {
                            $result = mysqli_query(
                                $conn,
                                sprintf(
                                    'create database IF NOT EXISTS `%s` character set %s collate %s;',
                                    addslashes($dbname),
                                    self::DB_CHARSET,
                                    self::DB_COLLATION
                                )
                            );

                            $this->logs[] = "Database '$dbname' created.";
                        }

                        $isValid = false == $result ? false : true;
                    } catch (\Exception $e) {
                        $isValid = false;
                    }
                }

                $bag->addRequirement(new Requirement(
                    true,
                    $isValid,
                    "Succeed to connect to MySQL and access to database '$dbname'"
                ));
            }
        }

        return [$bag];
    }
}
