<?php
namespace VG\GravitySpamShield\Digest;

use VG\GravitySpamShield\Log\LogRepository;
use VG\GravitySpamShield\Mail\Mailer;

class DigestService {
    /**
     * @param array<int,string> $recipients
     */
    public function __construct(
        private LogRepository $repository,
        private Mailer $mailer,
        private DigestBuilder $builder,
        private array $recipients
    ) {
    }

    public function run(): void {
        if ($this->recipients === []) {
            return;
        }
        $records = $this->repository->undigested();
        if ($records === []) {
            return;
        }

        $sent = $this->mailer->send(
            $this->recipients,
            $this->builder->subject($records),
            $this->builder->body($records)
        );

        if (!$sent) {
            return;
        }

        $ids = [];
        foreach ($records as $record) {
            if ($record->id !== null) {
                $ids[] = $record->id;
            }
        }
        $this->repository->markDigested($ids);
    }
}
