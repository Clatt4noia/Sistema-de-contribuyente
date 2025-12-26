<?php

namespace Tests\Unit;

use App\Enums\Documents\DocumentComputedStatus;
use App\Models\Document;
use Tests\TestCase;

class DocumentStatusTest extends TestCase
{
    public function test_computed_status_returns_expired(): void
    {
        $document = new Document();
        $document->expires_at = now()->subDay();

        $this->assertSame(DocumentComputedStatus::EXPIRED, $document->computed_status);
    }

    public function test_computed_status_returns_expiring(): void
    {
        $document = new Document();
        $document->expires_at = now()->addDays(10);

        $this->assertSame(DocumentComputedStatus::EXPIRING, $document->computed_status);
    }

    public function test_computed_status_returns_valid(): void
    {
        config(['documents.expiring_days' => 30]);

        $document = new Document();
        $document->expires_at = now()->addDays(31);

        $this->assertSame(DocumentComputedStatus::VALID, $document->computed_status);
    }
}

