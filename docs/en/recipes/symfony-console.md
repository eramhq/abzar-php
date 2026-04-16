# Symfony Console — CLI usage

A pattern for bulk validation from the command line.

```php
<?php

declare(strict_types=1);

namespace App\Command;

use Eram\Abzar\Validation\NationalId;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'abzar:validate-national-ids')]
final class ValidateNationalIdsCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::REQUIRED, 'CSV file with one national ID per line');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $input->getArgument('file');

        $valid = 0;
        $invalid = 0;

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            $io->error("Cannot open {$path}");
            return Command::FAILURE;
        }

        while (($row = fgets($handle)) !== false) {
            $id = trim($row);
            if ($id === '') {
                continue;
            }

            $result = NationalId::validate($id);
            if ($result->isValid()) {
                $valid++;
            } else {
                $invalid++;
                $io->writeln(sprintf('<error>✗ %s</error> %s', $id, (string) $result));
            }
        }

        fclose($handle);

        $io->success("Valid: {$valid}, invalid: {$invalid}");
        return $invalid === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
```

Run:

```bash
bin/console abzar:validate-national-ids customers.csv
```

`(string) $result` uses `ValidationResult::__toString()`, which joins the Persian error messages with `; `.
