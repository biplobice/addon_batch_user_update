<?php
namespace Concrete\Package\BatchUserUpdate\Console\Command;

use Concrete\Core\User\UserInfo;
use Concrete\Core\User\UserList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Class UpdateUserCommand.
 */
class UpdateUserCommand extends Command
{
    /** @var OutputInterface $output */
    protected $output;
    /** @var array $errorList */
    protected $errorList = [];
    /** @var int $updatedUsers */
    protected $updatedUsers = 0;
    /** @var ProgressBar $progress */
    protected $progress;
    /** @var string $password */
    protected $password;

    /**
     * Function used to configure the command.
     */
    protected function configure()
    {
        $this->setName('c5:update-user')
            ->setDescription('Batch update users')
            ->setAliases(['c5:user:update'])
            ->setHelp(<<<EOT
<info>Simply run this command to update all users', except super user and admins

<fg=cyan;options=bold>Examples:</>
<fg=green;options=bold>c5:update-users</> <fg=cyan>- Will update all users except super user and admins</>

            
<fg=red;options=bold>Returns codes:</>
  <fg=cyan;options=bold>0</> operation completed successfully
  <fg=cyan;options=bold>1</> errors occurred


EOT
            );
    }

    /**
     * Function used when executing via the command line.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $returnCode = 0;
        $this->output = $output;
        $this->output->write(t('Updating users....'));

        $userList = new UserList();
        // Exclude Administrators group users
        $userList->filterByGroup('Administrators', false);

        if ($userList->getTotalResults() > 1) {
            $this->progress = new ProgressBar($output, $userList->getTotalResults());
            $this->progress->setFormat(
                implode(
                    "\n",
                    [
                        '<fg=green;options=bold>' . t('Updating User') . ' </> %current%/%max%',
                        '    <info>Progress : %percent:3s%%</info>',
                        '    <fg=cyan;options=reverse>[%bar%]</>',
                        '    <comment>%elapsed:6s%/%estimated:-6s% %memory:6s%</comment>',
                    ]
                )
            );
        } else {
            $this->progress = new ProgressBar($output);
            $this->progress->setFormat(
                implode(
                    "\n",
                    [
                        '<fg=green;options=bold>' . t('Updating User') . ' </> %current%',
                        '    <info>Progress : %percent:3s%%</info>',
                        '    <fg=cyan;options=reverse>[%bar%]</>',
                        '    <comment>%elapsed:6s% %memory:6s%</comment>',
                    ]
                )
            );
        }

        $this->output->write(t('Updating Users'));

        $this->progress->start();
        /** @var UserInfo $user */
        foreach ($userList->getResults() as $user) {
            if (!$user->getUserObject()->isSuperUser()) {
                $this->updateUser($user);
            } else {
                $this->progress->advance();
            }
        }
        $this->progress->finish();
        $this->output->writeln(['', '<info>' . t2('Updated %s user', 'Updated %s users', $this->updatedUsers) . '</info>']);
        if (!empty($this->errorList)) {
            $this->output->writeln(['<error>', t2('%s Error Occurred:', '%s Errors Occurred : ', count($this->errorList))]);
            $this->output->writeln($this->errorList);
            $this->output->writeln('</error>');
            $returnCode = 1;
        }

        return $returnCode;
    }

    /**
     * @param UserInfo $user
     */
    protected function updateUser(UserInfo $user)
    {
        try {
            if ($user->getAttribute('profile_private_messages_enabled') === null) {
                $user->setAttribute('profile_private_messages_enabled', true);
            }

            if ($user->getAttribute('profile_private_messages_notification_enabled') === null) {
                $user->setAttribute('profile_private_messages_notification_enabled', true);
            }

            ++$this->updatedUsers;
        } catch (\Exception $e) {
            $this->errorList[] = $e->getMessage();
        }
        $this->progress->advance();
    }
}
