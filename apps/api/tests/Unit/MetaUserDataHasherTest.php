<?php

namespace Tests\Unit;

use App\Support\MetaUserDataHasher;
use Tests\TestCase;

class MetaUserDataHasherTest extends TestCase
{
    public function test_email_normalization_and_hash(): void
    {
        $hash = MetaUserDataHasher::hashEmail('  Ana.Perez@Example.com ');
        $this->assertSame(hash('sha256', 'ana.perez@example.com'), $hash);
    }

    public function test_phone_digits_only(): void
    {
        $this->assertSame(
            hash('sha256', '584241112233'),
            MetaUserDataHasher::hashPhone('+58 424-111-2233'),
        );
        $this->assertNull(MetaUserDataHasher::hashPhone('   '));
    }

    public function test_country_map_venezuela(): void
    {
        $this->assertSame(hash('sha256', 've'), MetaUserDataHasher::hashCountry('Venezuela'));
        $this->assertNull(MetaUserDataHasher::hashCountry('Atlantis'));
    }

    public function test_split_name(): void
    {
        [$fn, $ln] = MetaUserDataHasher::splitName('Ana María Pérez');
        $this->assertSame('Ana', $fn);
        $this->assertSame('Pérez', $ln);
    }
}
