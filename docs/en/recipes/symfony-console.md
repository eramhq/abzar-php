---
title: "Symfony Console integration"
description: "Validate national IDs line by line in a console command."
---

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
        $this->addArgument('file', InputArgument::REQUIRED, 'Text file with one national ID per line');
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

## Result and limitations

Register this class as a command service in a Symfony application (the standard service autoconfiguration handles `AsCommand`). A file containing `0013542419` and `1234567890`, one per line, produces these message contents; Symfony Console may add padding, borders or ANSI styling:

```text
✗ 1234567890 کد ملی نامعتبر است
[OK] Valid: 1, invalid: 1
```

The command exits with `Command::FAILURE` (`1`) when a row is invalid. The existing success-styled summary only means the scan completed. A file with no invalid rows exits `0`. It reads plain lines, not general CSV with headers, quotes or multiple columns. Lookup warnings count as valid. The sample prints identifiers: adjust logging and terminal output for your application.

Related: [integration overview](../framework-integration.md), [national ID](../national-id.md), [errors](../error-handling.md).
