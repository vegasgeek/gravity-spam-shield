<?php
namespace VG\GravitySpamShield\History;

interface SubmissionHistory {
    /**
     * Count recent submissions (within the last $windowSeconds) whose value
     * exactly matches $value. $field is a hint ("email", "name", "phone") for
     * implementations that can scope by field; implementations may match across
     * all fields. Count may be capped by the backing store for performance.
     */
    public function countRecentByField(string $field, string $value, int $windowSeconds): int;

    /** Count submissions from $ip within the last $windowSeconds. */
    public function countRecentByIp(string $ip, int $windowSeconds): int;
}
