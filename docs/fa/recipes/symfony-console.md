---
title: "استفاده در Symfony Console"
description: "بررسی گروهی کد ملی از فایل متنی در خط فرمان."
---

# استفاده در Symfony Console

این command کد ملی را خط به خط از فایل می‌خواند و تعداد نتیجه معتبر و نامعتبر را نشان می‌دهد. آن را در برنامه Symfony به عنوان command service ثبت کنید؛ autoconfiguration معمول برنامه، `AsCommand` را می‌شناسد.

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

اجرا:

```bash
bin/console abzar:validate-national-ids customers.csv
```

## خروجی

اگر فایل شامل `0013542419` و `1234567890`، هر کدام در یک خط باشد، متن پیام‌ها به شکل زیر است. Console ممکن است فاصله، کادر یا رنگ ANSI اضافه کند:

```text
✗ 1234567890 کد ملی نامعتبر است
[OK] Valid: 1, invalid: 1
```

وجود ردیف نامعتبر کد خروج `Command::FAILURE` یعنی `1` می‌دهد. ظاهر success در خلاصه فقط یعنی اسکن تمام شده است. بدون ردیف نامعتبر کد خروج `0` است. تبدیل نتیجه به string پیام‌ها را با `; ` به هم وصل می‌کند.

## محدودیت‌ها

این نمونه فایل متنی خطی می‌خواند، نه CSV عمومی با header، quote یا چند ستون. هشدار lookup در شمارش معتبر قرار می‌گیرد. شماره‌ها در خروجی چاپ می‌شوند؛ آن را متناسب با سیاست لاگ برنامه تغییر دهید. اجرای کامل نیاز به Symfony Console و ثبت command در برنامه دارد.

مطالب مرتبط: [فریم‌ورک‌ها](../framework-integration.md)، [کد ملی](../national-id.md)، [خطاها](../error-handling.md).
