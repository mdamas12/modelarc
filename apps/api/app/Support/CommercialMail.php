<?php

namespace App\Support;

final class CommercialMail
{
    public const DEFAULT_TO = 'modelarcca@gmail.com';

    /**
     * Destinatarios comerciales desde MAIL_TO_ADDRESS (coma-separados).
     *
     * @return list<string>
     */
    public static function recipients(?string $raw = null): array
    {
        $raw ??= (string) config('mail.to.address', self::DEFAULT_TO);

        $emails = array_map(
            static fn (string $email): string => strtolower(trim($email)),
            explode(',', $raw),
        );

        return array_values(array_unique(array_filter(
            $emails,
            static fn (string $email): bool => $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL),
        )));
    }
}
