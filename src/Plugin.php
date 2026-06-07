<?php
namespace VG\GravitySpamShield;

use VG\GravitySpamShield\Admin\LogViewer;
use VG\GravitySpamShield\Admin\Menu;
use VG\GravitySpamShield\Admin\SettingsPage;
use VG\GravitySpamShield\Digest\DigestBuilder;
use VG\GravitySpamShield\Digest\DigestService;
use VG\GravitySpamShield\Engine\ScoringEngine;
use VG\GravitySpamShield\Engine\Signal;
use VG\GravitySpamShield\GravityForms\GravityFormsAdapter;
use VG\GravitySpamShield\History\GravityFormsHistory;
use VG\GravitySpamShield\History\SubmissionHistory;
use VG\GravitySpamShield\Install\Activator;
use VG\GravitySpamShield\Log\LogRepository;
use VG\GravitySpamShield\Log\WpdbLogRepository;
use VG\GravitySpamShield\Mail\WpMailer;
use VG\GravitySpamShield\Settings\Settings;
use VG\GravitySpamShield\Settings\SettingsStore;
use VG\GravitySpamShield\Signals\DomainBlocklistSignal;
use VG\GravitySpamShield\Signals\DuplicateSignal;
use VG\GravitySpamShield\Signals\GibberishSignal;
use VG\GravitySpamShield\Signals\HoneypotSignal;
use VG\GravitySpamShield\Signals\IpBlocklistSignal;
use VG\GravitySpamShield\Signals\KeywordBlocklistSignal;
use VG\GravitySpamShield\Signals\TimingSignal;

class Plugin {
    private SettingsStore $store;
    private LogRepository $repository;
    private SubmissionHistory $history;

    public function __construct() {
        $this->store = new SettingsStore();
        $this->repository = new WpdbLogRepository();
        $this->history = new GravityFormsHistory();
    }

    public function boot(): void {
        $settings = $this->store->load();

        $engine = new ScoringEngine($this->buildSignals($settings), $settings->threshold);
        (new GravityFormsAdapter($engine, $this->repository, $settings))->register();

        $menu = new Menu(
            new SettingsPage($this->store),
            new LogViewer($this->repository)
        );
        $menu->register();

        add_action(Activator::CRON_HOOK, [$this, 'runDigest']);
    }

    public function runDigest(): void {
        $settings = $this->store->load();
        if (!$settings->digestEnabled) {
            return;
        }
        $service = new DigestService(
            $this->repository,
            new WpMailer(),
            new DigestBuilder(
                (string) get_bloginfo('name'),
                admin_url()
            ),
            $settings->recipients
        );
        $service->run();
    }

    /**
     * @return array<int,Signal>
     */
    private function buildSignals(Settings $settings): array {
        $signals = [];

        // Instant-block blocklists always run (they are empty-safe).
        $signals[] = new IpBlocklistSignal($settings->ipBlocklist);
        $signals[] = new DomainBlocklistSignal($settings->domainBlocklist);
        $signals[] = new KeywordBlocklistSignal($settings->keywordBlocklist);

        if ($settings->signalEnabled('honeypot')) {
            $signals[] = new HoneypotSignal($settings->signalWeight('honeypot', 5));
        }
        if ($settings->signalEnabled('gibberish')) {
            $signals[] = new GibberishSignal($settings->signalWeight('gibberish', 3));
        }
        if ($settings->signalEnabled('timing')) {
            $signals[] = new TimingSignal(
                $this->history,
                $settings->signalWeight('timing', 2),
                minSeconds: 3,
                velocityMax: 10,
                velocityWindow: HOUR_IN_SECONDS
            );
        }
        if ($settings->signalEnabled('duplicate')) {
            $signals[] = new DuplicateSignal(
                $this->history,
                $settings->signalWeight('duplicate', 3),
                maxRepeats: 1,
                windowSeconds: DAY_IN_SECONDS
            );
        }

        return $signals;
    }
}
