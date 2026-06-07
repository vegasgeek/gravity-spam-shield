<?php
namespace VG\GravitySpamShield\History;

class GravityFormsHistory implements SubmissionHistory {
    public function countRecentByField(string $field, string $value, int $windowSeconds): int {
        if (!class_exists('GFAPI') || trim($value) === '') {
            return 0;
        }
        // Global exact-value search across all fields. We use operator '=' (not
        // 'contains') so a short value like a phone fragment can't over-count by
        // matching unrelated fields — over-counting here would cause spam false
        // positives, which we must avoid. Omitting 'key' makes GF search all fields.
        $criteria = [
            'start_date'    => $this->since($windowSeconds),
            'field_filters' => [
                ['operator' => '=', 'value' => $value],
            ],
        ];
        /** @var array<int,array<string,mixed>>|false $entries */
        $entries = \GFAPI::get_entries(0, $criteria, null, ['offset' => 0, 'page_size' => 200]);
        return is_array($entries) ? count($entries) : 0;
    }

    public function countRecentByIp(string $ip, int $windowSeconds): int {
        if (!class_exists('GFAPI') || $ip === '') {
            return 0;
        }
        $criteria = [
            'start_date'    => $this->since($windowSeconds),
            'field_filters' => [
                ['key' => 'ip', 'operator' => '=', 'value' => $ip],
            ],
        ];
        /** @var array<int,array<string,mixed>>|false $entries */
        $entries = \GFAPI::get_entries(0, $criteria, null, ['offset' => 0, 'page_size' => 200]);
        return is_array($entries) ? count($entries) : 0;
    }

    private function since(int $windowSeconds): string {
        return gmdate('Y-m-d H:i:s', time() - $windowSeconds);
    }
}
