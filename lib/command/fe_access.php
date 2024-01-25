<?php

declare(strict_types=1);

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * usage.
 *
 *  bin/console securit:fe_access -help
 */
final class rex_securit_command_fe_access extends rex_console_command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('securit Frontend Access')
            ->addOption('info', 'i', InputOption::VALUE_OPTIONAL, 'info about fe password and activation status', 'none')
            ->addOption('set-password', 'p', InputOption::VALUE_OPTIONAL, 'set frontend password', 'none')
            ->addOption('set-status', 's', InputOption::VALUE_OPTIONAL, 'set activation status', 'none')
            ->addOption('set-domains', 'd', InputOption::VALUE_OPTIONAL, 'set domain status', 'none')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getStyle($input, $output);
        $io->title('securit Frontend Access');

        if ('none' !== $input->getOption('info')) {
            $io->text((rex_securit_fe_access::getStatus()) ? 'Status: active' : 'Status: inactive');
            $io->text('Frontend-Password: '. rex_securit_fe_access::getPassword());

            $domainIds = rex_config::get('securit', 'fe_access_domains');

            if ('' == $domainIds) {
                $io->text('Domain: No domains specified!');
            } else {
                foreach (array_map('intval', explode(',', $domainIds)) as $domainId) {
                    $domain = rex_yrewrite::getDomainById($domainId);
                    if ($domain) {
                        $io->text('Active Domain: '. $domain->getUrl());
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
                    rex_securit_fe_access::deactivate();
                    $io->success('Frontend Passwort has been deactivated');
                    break;
                default:
                    rex_securit_fe_access::activate();
                    $io->success('Frontend Passwort has been activated');
            }
        }

        if ('none' !== $input->getOption('set-domains')) {
            $domains = [];
            foreach (rex_yrewrite::getDomains() as $domain) {
                $domains[] = $domain->getUrl();
            }

            $defaultKeys = [];

            $domainIds = rex_config::get('securit', 'fe_access_domains');
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
                'select domains for frontend access service (default: '. (([] !== $defaultKeys) ? implode(',', $defaultKeys) : '-').')',
                $domains,
                implode(',', $defaultKeys)
            );
            $question->setMultiselect(true);

            $selectedDomains = $helper->ask($input, $output, $question);

            $domainIds = [];
            foreach (rex_yrewrite::getDomains() as $domain) {
                if (in_array($domain->getUrl(), $selectedDomains, true)) {
                    $domainIds[] = $domain->getId();
                }
            }

            rex_addon::get('securit')
                ->setConfig('fe_access_domains', implode(',', $domainIds));

            $io->success('your selection has been saved');
        }

        if ('none' !== $input->getOption('set-password')) {
            $password = $io->ask('enter password: ', md5((string) time()), static function ($password) {
                if ('' == $password) {
                    throw new InvalidArgumentException('please enter a passwort');
                }

                return $password;
            });

            rex_securit_fe_access::setPassword($password);

            $io->success('This Password has been saved: '.$password);
        }

        return 0;
    }
}
