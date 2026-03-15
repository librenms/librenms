<?php

namespace App\Models;

use AcmePhp\Ssl\Exception\CertificateParsingException;
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

    public function scopeEnabled($query)
    {
        return $query->where('disabled', false);
    }

    public function scopeDisabled($query)
    {
        return $query->where('disabled', true);
    }

    /**
     * Scope to certificates the user is allowed to see (linked to device they have access to, or no device).
     */
    public function scopeHasAccess($query, $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->whereNull('device_id')
                ->orWhereIn('device_id', Device::hasAccess($user)->select('device_id'));
        });
    }

    public function isExpired(): bool
    {
        return $this->valid_to && $this->valid_to->isPast();
    }

    public function expiresWithinDays(int $days): bool
    {
        return $this->valid_to && $this->valid_to->lte(now()->addDays($days));
    }

    /**
     * Build attribute array from parser results (ParsedCertificate + raw openssl_x509_parse).
     *
     * @param  \Jalle19\CertificateParser\ParserResults  $results
     * @return array<string, mixed>
     */
    public static function attributesFromParserResults($results): array
    {
        $cert = $results->getParsedCertificate();
        $raw = $results->getRawCertificate();
        $validFrom = method_exists($cert, 'getValidFrom') ? $cert->getValidFrom() : null;
        $validTo = $cert->getValidTo();

        $issuerCountry = $raw['issuer']['C'] ?? null;
        $issuerOrg = $raw['issuer']['O'] ?? null;

        $serialNumber = method_exists($cert, 'getSerialNumber') ? $cert->getSerialNumber() : ($raw['serialNumber'] ?? null);
        $serialNumberHex = $raw['serialNumberHex'] ?? null;
        $selfSigned = method_exists($cert, 'isSelfSigned') ? $cert->isSelfSigned() : (method_exists($cert, 'getSelfSigned') ? $cert->getSelfSigned() : false);

        $signatureAlgorithm = $raw['signatureTypeSN'] ?? $raw['signatureTypeLN'] ?? null;
        $version = isset($raw['version']) ? (int) $raw['version'] + 1 : null; // OpenSSL: 2 = X.509 v3

        $extensions = $raw['extensions'] ?? [];
        $keyUsage = $extensions['keyUsage'] ?? null;
        $extendedKeyUsage = $extensions['extendedKeyUsage'] ?? null;
        $basicConstraints = $extensions['basicConstraints'] ?? null;
        $subjectKeyId = $extensions['subjectKeyIdentifier'] ?? null;
        $authorityKeyId = $extensions['authorityKeyIdentifier'] ?? null;

        $daysUntilExpiry = null;
        if ($validTo !== null) {
            $daysUntilExpiry = (int) round(\Carbon\Carbon::instance($validTo)->diffInSeconds(now(), false) / -86400);
        }

        return [
            'issuer' => $cert->getIssuer(),
            'issuer_country' => $issuerCountry,
            'issuer_organization' => $issuerOrg,
            'subject' => $cert->getSubject(),
            'subject_alternative_names' => $cert->getSubjectAlternativeNames(),
            'serial_number' => $serialNumber,
            'serial_number_hex' => $serialNumberHex,
            'self_signed' => $selfSigned,
            'signature_algorithm' => $signatureAlgorithm,
            'certificate_version' => $version,
            'key_usage' => $keyUsage,
            'extended_key_usage' => $extendedKeyUsage,
            'basic_constraints' => $basicConstraints,
            'subject_key_identifier' => $subjectKeyId,
            'authority_key_identifier' => $authorityKeyId,
            'valid_from' => $validFrom?->format('Y-m-d H:i:s'),
            'valid_to' => $validTo?->format('Y-m-d H:i:s'),
            'days_until_expiry' => $daysUntilExpiry,
            'fingerprint' => $results->getFingerprint(),
            'pem' => $results->getPemString(),
        ];
    }

    /**
     * Build a string describing which certificate attribute fields changed (old -> new).
     * Used for eventlog messages when discovering or refreshing certificates.
     *
     * @param  array<string, mixed>  $old  previous attribute values (e.g. from model only())
     * @param  array<string, mixed>  $new  new attribute values
     * @return string comma-separated "field (old → new)" for each changed field
     */
    public static function formatAttributeChanges(array $old, array $new): string
    {
        $parts = [];
        $fields = ['subject', 'issuer', 'valid_to', 'valid_from', 'fingerprint', 'days_until_expiry'];
        foreach ($fields as $field) {
            $o = $old[$field] ?? null;
            $n = $new[$field] ?? null;
            if ($o !== $n && (string) $o !== (string) $n) {
                $oStr = $o instanceof \DateTimeInterface ? $o->format('Y-m-d H:i:s') : (string) $o;
                $nStr = $n instanceof \DateTimeInterface ? $n->format('Y-m-d H:i:s') : (string) $n;
                $parts[] = "{$field} ({$oStr} → {$nStr})";
            }
        }

        return implode(', ', $parts);
    }

    /**
     * Fetch certificate from host:port and return array of attributes for create/update.
     *
     * @throws ProviderException|CertificateParsingException
     */
    public static function fetchAndParse(string $host, int $port = 443, int $timeout = 10): array
    {
        $context = new StreamContext;
        $context->setVerifyPeerName(false);
        $provider = new StreamSocketProvider($host, $port, $timeout, $context);
        $parser = new Parser;
        $results = $parser->parse($provider);

        return self::attributesFromParserResults($results);
    }
}
