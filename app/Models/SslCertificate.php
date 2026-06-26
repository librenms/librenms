<?php

namespace App\Models;

use AcmePhp\Ssl\Exception\CertificateParsingException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Jalle19\CertificateParser\Parser;
use Jalle19\CertificateParser\Provider\Exception\ProviderException;
use Jalle19\CertificateParser\Provider\StreamContext;
use Jalle19\CertificateParser\Provider\StreamSocketProvider;

class SslCertificate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'device_id',
        'host',
        'port',
        'issuer',
        'issuer_country',
        'issuer_organization',
        'subject',
        'subject_alternative_names',
        'serial_number',
        'serial_number_hex',
        'self_signed',
        'signature_algorithm',
        'certificate_version',
        'key_usage',
        'extended_key_usage',
        'basic_constraints',
        'subject_key_identifier',
        'authority_key_identifier',
        'valid_from',
        'valid_to',
        'days_until_expiry',
        'fingerprint',
        'pem',
        'last_checked_at',
        'disabled',
    ];

    protected function casts(): array
    {
        return [
            'subject_alternative_names' => 'array',
            'valid_from' => 'datetime',
            'valid_to' => 'datetime',
            'last_checked_at' => 'datetime',
            'disabled' => 'boolean',
            'self_signed' => 'boolean',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id', 'device_id');
    }

    /**
     * @param  Builder<SslCertificate>  $query
     * @return Builder<SslCertificate>
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('disabled', false);
    }

    /**
     * @param  Builder<SslCertificate>  $query
     * @return Builder<SslCertificate>
     */
    public function scopeDisabled(Builder $query): Builder
    {
        return $query->where('disabled', true);
    }

    /**
     * Scope to certificates the user is allowed to see (linked to device they have access to, or no device).
     *
     * @param  Builder<SslCertificate>  $query
     * @param  mixed  $user
     * @return Builder<SslCertificate>
     */
    public function scopeHasAccess(Builder $query, $user): Builder
    {
        return $query->where(function (Builder $q) use ($user): void {
            $q->whereNull('device_id')
                ->orWhereIn('device_id', Device::hasAccess($user)->select('device_id'));
        });
    }

    public function isExpired(): bool
    {
        $validTo = $this->valid_to;

        return $validTo !== null && $validTo->isPast();
    }

    public function expiresWithinDays(int $days): bool
    {
        $validTo = $this->valid_to;

        return $validTo !== null && $validTo->lte(now()->addDays($days));
    }

    /**
     * Get a formatted string of changes for tracked attributes.
     */
    public function getTrackedChanges(): string
    {
        $dirty = $this->getDirty();
        $parts = [];
        $fields = ['subject', 'issuer', 'valid_to', 'valid_from', 'fingerprint', 'days_until_expiry'];

        foreach ($fields as $field) {
            if (array_key_exists($field, $dirty)) {
                $o = $this->getOriginal($field);
                $n = $this->getAttribute($field);

                $oStr = $o instanceof \DateTimeInterface ? $o->format('Y-m-d H:i:s') : (string) $o;
                $nStr = $n instanceof \DateTimeInterface ? $n->format('Y-m-d H:i:s') : (string) $n;

                if ($oStr !== $nStr) {
                    $parts[] = "{$field} ({$oStr} → {$nStr})";
                }
            }
        }

        return implode(', ', $parts);
    }

    /**
     * Fetch certificate details from the host and port and update this model's attributes.
     *
     * @throws \Exception
     */
    public function updateFromHost(int $timeout = 10): void
    {
        $context = new StreamContext;
        $context->setVerifyPeerName(false);
        $provider = new StreamSocketProvider($this->host, $this->port, $timeout, $context);
        $parser = new Parser;

        try {
            $results = $parser->parse($provider);
        } catch (ProviderException|CertificateParsingException $e) {
            throw new \Exception("Failed to fetch certificate from $this->host:$this->port: {$e->getMessage()}");
        }

        $cert = $results->getParsedCertificate();
        $raw = $results->getRawCertificate();
        $validFrom = method_exists($cert, 'getValidFrom') ? $cert->getValidFrom() : null;
        $validTo = $cert->getValidTo();

        $this->issuer = $cert->getIssuer();
        $this->issuer_country = $raw['issuer']['C'] ?? null;
        $this->issuer_organization = $raw['issuer']['O'] ?? null;
        $this->subject = $cert->getSubject();
        $this->subject_alternative_names = $cert->getSubjectAlternativeNames();
        $this->serial_number = method_exists($cert, 'getSerialNumber') ? $cert->getSerialNumber() : ($raw['serialNumber'] ?? null);
        $this->serial_number_hex = $raw['serialNumberHex'] ?? null;
        $this->self_signed = method_exists($cert, 'isSelfSigned') ? $cert->isSelfSigned() : (method_exists($cert, 'getSelfSigned') ? $cert->getSelfSigned() : false);
        $this->signature_algorithm = $raw['signatureTypeSN'] ?? $raw['signatureTypeLN'] ?? null;
        $this->certificate_version = isset($raw['version']) ? (int) $raw['version'] + 1 : null; // OpenSSL: 2 = X.509 v3

        $extensions = $raw['extensions'] ?? [];
        $this->key_usage = $extensions['keyUsage'] ?? null;
        $this->extended_key_usage = $extensions['extendedKeyUsage'] ?? null;
        $this->basic_constraints = $extensions['basicConstraints'] ?? null;
        $this->subject_key_identifier = $extensions['subjectKeyIdentifier'] ?? null;
        $this->authority_key_identifier = $extensions['authorityKeyIdentifier'] ?? null;

        $this->valid_from = $validFrom !== null ? Carbon::instance($validFrom) : null;
        $this->valid_to = Carbon::instance($validTo);
        $this->days_until_expiry = (int) round($this->valid_to->diffInSeconds(now()) / -86400);

        $this->fingerprint = $results->getFingerprint();
        $this->pem = $results->getPemString();
        $this->last_checked_at = now();
    }
}
