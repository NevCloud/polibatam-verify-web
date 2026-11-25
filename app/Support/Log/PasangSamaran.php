<?php

namespace App\Support\Log;

use Illuminate\Log\Logger;
use Monolog\Logger as Monolog;
use Monolog\LogRecord;

/**
 * Tap kanal log (config/logging.php): setiap catatan disamarkan sebelum ditulis (NFR-32).
 */
final class PasangSamaran
{
    public function __invoke(Logger $logger): void
    {
        $monolog = $logger->getLogger();
        if (! $monolog instanceof Monolog) {
            return;
        }

        $monolog->pushProcessor(fn (LogRecord $r) => $r->with(
            message: Samarkan::teks($r->message),
            context: Samarkan::nilai($r->context),
            extra: Samarkan::nilai($r->extra),
        ));
    }
}
