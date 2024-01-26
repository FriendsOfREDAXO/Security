<?php

namespace FriendsOfRedaxo\Securit\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * usage.
 *
 *  bin/console securit:ip_access -help
 */
final class IPAccess extends \rex_console_command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('securit IP Access Init Process')
            ->addOption('add', 'a', InputOption::VALUE_OPTIONAL, 'add ip address', 'none')
            ->addOption('list', 'l', InputOption::VALUE_OPTIONAL, 'list ip addresses, type and envirement', 'none')
            ->addOption('delete', 'd', InputOption::VALUE_OPTIONAL, 'list ip address by id', 'none')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getStyle($input, $output);
        $io->title('securit IP Access');

        $table = \rex_yform_manager_table::get(\FriendsOfRedaxo\Securit\IPAccess::table_name);

        if (!$table) {
            $io->warning('table not found');
            return 0;
        }

        if ('none' !== $input->getOption('list')) {
            $items = $table->query();
            $io->text(
                str_pad('id', 5, ' ', STR_PAD_LEFT).
                    str_pad('environment', 15, ' ', STR_PAD_LEFT).
                    str_pad('type', 10, ' ', STR_PAD_LEFT).
                    str_pad('ip', 25, ' ', STR_PAD_LEFT).
                    str_pad('comment', 30, ' ', STR_PAD_LEFT)
            );
            foreach ($items as $item) {
                $io->text(
                    str_pad((string) $item->getId(), 5, ' ', STR_PAD_LEFT).
                        str_pad($item->getValue('environment'), 15, ' ', STR_PAD_LEFT).
                        str_pad($item->getValue('type'), 10, ' ', STR_PAD_LEFT).
                        str_pad($item->getValue('ip'), 25, ' ', STR_PAD_LEFT).
                        str_pad($item->getValue('comment'), 30, ' ', STR_PAD_LEFT)
                );
            }

            $io->text('');
        }

        if ('none' !== $input->getOption('add')) {
            $environment = $io->ask('environment', 'backend', static function ($environment) {
                if ('backend' == $environment) {
                    return $environment;
                }

                if ('frontend' == $environment) {
                    return $environment;
                }

                throw new \InvalidArgumentException('backend or frontend?');
            });

            $io->text('environment: '.$environment);

            $type = $io->ask('type', 'allow', static function ($allow) {
                if ('allow' == $allow) {
                    return $allow;
                }

                if ('block' == $allow) {
                    return $allow;
                }

                throw new \InvalidArgumentException('allow or block?');
            });

            $io->text('type: '.$type);

            $ip = $io->ask('ip or ip range. Format: x.x.x.x/xx or x.x.x.x-y.y.y.y', '', static function ($ip) {
                if ('' == $ip) {
                    throw new \InvalidArgumentException('please enter ip or ip range');
                }

                return $ip;
            });

            $io->text('ip/range: '.$ip);

            $comment = $io->ask('comment', '', static function ($comment) {
                if ('' == $comment) {
                    throw new \InvalidArgumentException('please enter a comment/description');
                }

                return $comment;
            });

            $io->text('comment: '.$comment);

            $dataset = $table->createDataset()
                ->setValue('environment', $environment)
                ->setValue('type', $type)
                ->setValue('ip', $ip)
                ->setValue('comment', $comment);
            $dataset
                ->save();

            if (0 == \count($dataset->getMessages())) {
                $io->success('ip added');
                \FriendsOfRedaxo\Securit\IPAccess::getConfig(true);
            } else {
                $io->error(print_r($dataset->getMessages(), true));
            }
        }

        if ('none' !== $input->getOption('delete')) {
            $id_to_be_deleted = $io->ask('id to be deleted', '', static function ($id) {
                $table = \rex_yform_manager_table::get(\FriendsOfRedaxo\Securit\IPAccess::table_name);
                if (!$table) {
                    return '';
                }

                $items = $table->query()
                    ->where('id', $id)
                    ->find();
                if (1 != \count($items)) {
                    throw new \InvalidArgumentException('id not found');
                }

                return $id;
            });

            $table->query()
                ->where('id', $id_to_be_deleted)
                ->find()
                ->delete();

            \FriendsOfRedaxo\Securit\IPAccess::getConfig(true);

            $io->success('id deleted');
        }

        return 0;
    }
}
