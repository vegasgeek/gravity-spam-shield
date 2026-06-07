<?php
namespace VG\GravitySpamShield\Log;

interface LogRepository {
    public function insert(LogRecord $record): void;

    /**
     * @return array<int,LogRecord> Undigested rows newest-first.
     */
    public function undigested(): array;

    /**
     * @param array<int,int> $ids
     */
    public function markDigested(array $ids): void;

    /**
     * @return array<int,LogRecord> Most recent rows for the admin log viewer.
     */
    public function recent(int $limit): array;
}
