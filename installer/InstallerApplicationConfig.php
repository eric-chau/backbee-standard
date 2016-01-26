<?php

namespace BackBee\Installer;

use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Formatter\Style;
use Webmozart\Console\Config\DefaultApplicationConfig;

/**
 * @author Eric Chau <eriic.chau@gmail.com>
 */
class InstallerApplicationConfig extends DefaultApplicationConfig
{
    const VERSION = '1.0.0';

    protected function configure()
    {
        parent::configure();

        $checkCmdHandler = new CheckCommandHandler();

        $this
            ->setName('backbee')
            ->setVersion(self::VERSION)
            ->beginCommand('check')
                ->setDescription('Show requirements status for BackBee installation')
                ->setHandler($checkCmdHandler)
            ->end()
            ->beginCommand('install')
                ->setDescription('Install an instance of BackBee ')
                ->addOption('mysql-host', null, Option::OPTIONAL_VALUE, 'MySQL hostname', '127.0.0.1')
                ->addOption('mysql-port', null, Option::OPTIONAL_VALUE | Option::INTEGER, 'MySQL port', 3306)
                ->addOption('mysql-dbname', null, Option::OPTIONAL_VALUE, 'MySQL database name', 'backbee_demo')
                ->addOption('mysql-username', null, Option::OPTIONAL_VALUE, 'MySQL username', 'root')
                ->addOption('mysql-password', null, Option::OPTIONAL_VALUE | Option::NULLABLE, 'MySQL password', null)
                ->addOption('site-name', null, Option::OPTIONAL_VALUE, 'BackBee site name', 'BackBee Demo')
                ->addOption('site-domain', null, Option::OPTIONAL_VALUE, 'BackBee site domain', 'http://backbee.demo')
                ->setHandler(function() use ($checkCmdHandler) {
                    return new InstallCommandHandler($checkCmdHandler);
                })
            ->end()
            ->addStyle(Style::tag('success')->fgGreen())
            ->addStyle(Style::tag('fail')->fgRed())
        ;
    }
}
