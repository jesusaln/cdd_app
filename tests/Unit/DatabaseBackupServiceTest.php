<?php

namespace Tests\Unit;

use App\Services\DatabaseBackupService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class DatabaseBackupServiceTest extends TestCase
{
    /** @test */
    public function format_bytes_converts_sizes_correctly(): void
    {
        $service = new DatabaseBackupService;
        $method = new ReflectionMethod(DatabaseBackupService::class, 'formatBytes');
        $method->setAccessible(true);

        $this->assertSame('1 KB', $method->invoke($service, 1024));
        $this->assertSame('1 MB', $method->invoke($service, 1024 * 1024));
        $this->assertSame('0 B', $method->invoke($service, 0));
    }
}
