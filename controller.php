<?php

namespace Concrete\Package\BatchUserUpdate;

use Concrete\Core\Package\Package;
use Concrete\Core\Support\Facade\Application;
use Concrete\Package\BatchUserUpdate\Console\Command\UpdateUserCommand;
use Job;
use Core;


class Controller extends Package
{
    protected $pkgHandle = 'batch_user_update';

    protected $appVersionRequired = '8.2.0';

    protected $pkgVersion = '0.0.1';

    protected $pkgAutoloaderMapCoreExtensions = true;

    public function getPackageName()
    {
        return t('Batch User Update');
    }

    public function getPackageDescription()
    {
        return t('Simply run concrete/bin/concrete5 c5:update-user from your project root directory to update the user attributes.');
    }

    public function install()
    {
        $pkg = parent::install();
        $this->installJobs($pkg);
    }
    public function upgrade()
    {
        $pkg = parent::upgrade();
        $this->installJobs($pkg);
    }

    protected function installJobs($pkg)
    {
        $jobHandle = 'update_users';
        $job = Job::getByHandle($jobHandle);
        if (!is_object($job)) {
            Job::installByPackage($jobHandle, $pkg);
        }
    }

    public function on_start()
    {
        $app = Application::getFacadeApplication();
        if ($app->isRunThroughCommandLineInterface()) {
            /** @var Console $app */
            $app = Core::make('console');
            $app->add(new UpdateUserCommand());
        }
    }
}

