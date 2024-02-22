<?php

namespace FriendsOfRedaxo\Security\Command;

use FriendsOfRedaxo\Security\FrontendAccess as FrontendAccessNoCommand;
use InvalidArgumentException;
use rex_addon;
use rex_config;
use rex_console_command;
use rex_yrewrite;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

use function in_array;

/**
 * usage.
 *
 *  bin/console security:fe_access -help
 */
final class FrontendAccess extends rex_console_command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('security Frontend Access')
            ->addOption('info', 'i', InputOption::VALUE_OPTIONAL, 'info about fe password and activation status', 'none')
            ->addOption('set-password', 'p', InputOption::VALUE_OPTIONAL, 'set frontend password', 'none')
            ->addOption('set-status', 's', InputOption::VALUE_OPTIONAL, 'set activation status', 'none')
            ->addOption('set-domains', 'd', InputOption::VALUE_OPTIONAL, 'set domain status', 'none')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getStyle($input, $output);
        $io->title('security Frontend Access');

        if ('none' !== $input->getOption('info')) {
            $io->text((FrontendAccessNoCommand::getStatus()) ? 'Status: active' : 'Status: inactive');
            $io->text('Frontend-Password: ' . FrontendAccessNoCommand::getPassword());

            $domainIds = rex_config::get('security', 'fe_access_domains');

            if ('' == $domainIds) {
                $io->text('Domain: No domains specified!');
            } else {
                foreach (array_map('intval', explode(',', $domainIds)) as $domainId) {
                    $domain = rex_yrewrite::getDomainById($domainId);
                    if ($domain) {
                        $io->text('Active Domain: ' . $domain->getUrl());
                    }
                }
            }

            $io->text('');
        }

        if ('none' !== $input->getOption('set-status')) {
            switch ($input->getOption('set-status')) {
                case '0':
                case 'false':
                case 'deactive':
                case 'inactive':
                case 'deactivate':
                    FrontendAccessNoCommand::deactivate();
                    $io->success('Frontend Passwort has been deactivated');
                    break;
                default:
                    FrontendAccessNoCommand::activate();
                    $io->success('Frontend Passwort has been activated');
            }
        }

        if ('none' !== $input->getOption('set-domains')) {
            $domains = [];
            foreach (rex_yrewrite::getDomains() as $domain) {
                $domains[] = $domain->getUrl();
            }

            $defaultKeys = [];

            $domainIds = rex_config::get('security', 'fe_access_domains');
            foreach (array_map('intval', explode(',', (string) @$domainIds)) as $activeDomainId) {
                $activeDomain = rex_yrewrite::getDomainById($activeDomainId);
                if ($activeDomain) {
                    foreach ($domains as $domainKey => $domain) {
                        if ($activeDomain->getUrl() == $domain) {
                            $defaultKeys[] = $domainKey;
                        }
                    }
                }
            }

            $helper = $this->getHelper('question');
            $question = new ChoiceQuestion(
                'select domains for frontend access service (default: ' . (([] !== $defaultKeys) ? implode(',', $defaultKeys) : '-') . ')',
                $domains,
                implode(',', $defaultKeys),
            );
            $question->setMultiselect(true);

            $selectedDomains = $helper->ask($input, $output, $question);

            $domainIds = [];
            foreach (rex_yrewrite::getDomains() as $domain) {
                if (in_array($domain->getUrl(), $selectedDomains, true)) {
                    $domainIds[] = $domain->getId();
                }
            }

            rex_addon::get('security')
                ->setConfig('fe_access_domains', implode(',', $domainIds));

            $io->success('your selection has been saved');
        }

        if ('none' !== $input->getOption('set-password')) {
            $bytes = random_bytes(12);
            $defaultPassword = rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');

            $password = $io->ask('enter password: ', $defaultPassword, static function ($password) {
                if ('' == $password) {
                    throw new InvalidArgumentException('please enter a passwort');
                }

                return $password;
            });

            FrontendAccessNoCommand::setPassword($password);

            $io->success('This Password has been saved: ' . $password);
        }

        return 0;
    }
}
