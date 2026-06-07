<?php
namespace VG\GravitySpamShield\GravityForms;

use VG\GravitySpamShield\Engine\ScoringEngine;
use VG\GravitySpamShield\Log\LogRecord;
use VG\GravitySpamShield\Log\LogRepository;
use VG\GravitySpamShield\Settings\Settings;
use VG\GravitySpamShield\Support\Submission;

class GravityFormsAdapter {
    public function __construct(
        private ScoringEngine $engine,
        private LogRepository $repository,
        private Settings $settings
    ) {
    }

    public function register(): void {
        add_filter('gform_get_form_filter', [$this, 'injectFields'], 10, 2);
        add_filter('gform_entry_is_spam', [$this, 'filterSpam'], 10, 3);
    }

    /**
     * @param string $formHtml
     * @param array<string,mixed> $form
     */
    public function injectFields($formHtml, $form): string {
        $formHtml = (string) $formHtml;
        $ts = (int) time();
        $hidden = '<div style="position:absolute;left:-9999px" aria-hidden="true">'
            . '<input type="text" name="vggss_hp" value="" tabindex="-1" autocomplete="off" />'
            . '<input type="hidden" name="vggss_ts" value="' . esc_attr((string) $ts) . '" />'
            . '</div>';
        $pos = strripos($formHtml, '</form>');
        if ($pos === false) {
            return $formHtml . $hidden;
        }
        return substr($formHtml, 0, $pos) . $hidden . substr($formHtml, $pos);
    }

    /**
     * @param bool $isSpam
     * @param array<string,mixed> $form
     * @param array<string,mixed> $entry
     */
    public function filterSpam($isSpam, $form, $entry): bool {
        if ($isSpam) {
            return true; // respect upstream decisions (e.g. GF honeypot, akismet)
        }
        if (!$this->settings->detectionEnabled) {
            return false;
        }
        $formId = (int) ($form['id'] ?? 0);
        if (!$this->settings->appliesToForm($formId)) {
            return false;
        }

        $submission = $this->buildSubmission($formId, $form, $entry);
        $verdict = $this->engine->evaluate($submission);

        if (!$verdict->isSpam) {
            return false;
        }

        $this->repository->insert(new LogRecord(
            formId: $formId,
            entryId: (int) ($entry['id'] ?? 0),
            score: $verdict->score,
            reasons: $verdict->reasons,
            ip: $submission->ip,
            email: $submission->email,
            name: $submission->name,
            phone: $submission->phone
        ));

        return true;
    }

    /**
     * @param array<string,mixed> $form
     * @param array<string,mixed> $entry
     */
    private function buildSubmission(int $formId, array $form, array $entry): Submission {
        $fields = [];
        $email = '';
        $name = '';
        $phone = '';

        $formFields = is_array($form['fields'] ?? null) ? $form['fields'] : [];
        foreach ($formFields as $field) {
            $type = (string) ($field->type ?? '');
            $label = (string) ($field->label ?? '');
            $value = $this->fieldValue($field, $entry);
            if ($value === '') {
                continue;
            }
            $fields[$label !== '' ? $label : (string) ($field->id ?? '')] = $value;

            if ($email === '' && $type === 'email') {
                $email = $value;
            } elseif ($email === '' && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $email = $value;
            }
            if ($phone === '' && $type === 'phone') {
                $phone = $value;
            }
            if ($name === '' && $type === 'name') {
                $name = trim($value);
            }
        }

        if ($name === '') {
            $name = $this->guessName($fields);
        }

        $hp = isset($_POST['vggss_hp']) ? trim((string) wp_unslash($_POST['vggss_hp'])) : '';
        $elapsed = null;
        if (isset($_POST['vggss_ts'])) {
            $ts = (int) wp_unslash($_POST['vggss_ts']);
            if ($ts > 0) {
                $elapsed = max(0, time() - $ts);
            }
        }

        return new Submission(
            formId: $formId,
            fields: $fields,
            email: $email,
            name: $name,
            phone: $phone,
            ip: (string) ($entry['ip'] ?? ''),
            elapsedSeconds: $elapsed,
            honeypotFilled: $hp !== ''
        );
    }

    /**
     * @param object $field
     * @param array<string,mixed> $entry
     */
    private function fieldValue($field, array $entry): string {
        $id = $field->id ?? null;
        if ($id === null) {
            return '';
        }
        if (isset($entry[(string) $id])) {
            return trim((string) $entry[(string) $id]);
        }
        $parts = [];
        foreach ($entry as $key => $val) {
            if (is_string($key) && str_starts_with($key, (string) $id . '.')) {
                $val = trim((string) $val);
                if ($val !== '') {
                    $parts[] = $val;
                }
            }
        }
        return implode(' ', $parts);
    }

    /**
     * @param array<string,string> $fields
     */
    private function guessName(array $fields): string {
        foreach ($fields as $label => $value) {
            if (stripos($label, 'name') !== false) {
                return $value;
            }
        }
        return '';
    }
}
