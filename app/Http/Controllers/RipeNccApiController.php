<?php

namespace App\Http\Controllers;

use App\ApiClients\RipeApi;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use LibreNMS\Exceptions\ApiClientException;

class RipeNccApiController extends Controller
{
    public function __invoke(Request $request, RipeApi $api): View
    {
        $this->authorize('tools.ripe');

        $validated = $request->validate([
            'type' => 'nullable|in:whois,abuse-contact-finder',
            'query' => 'nullable|ip_or_hostname',
        ]);
        $type = $validated['type'] ?? 'whois';
        $query = $validated['query'] ?? '';

        $results = null;
        $error = null;
        if ($query !== '') {
            try {
                $results = $type === 'whois'
                    ? array_map(fn (array $record) => [$record['key'], $record['value']], $api->getWhois($query)['data']['records'][0] ?? [])
                    : array_map(fn (string $email) => [__('Email'), $email], $api->getAbuseContact($query)['data']['abuse_contacts'] ?? []);
            } catch (ApiClientException $e) {
                $error = $e->getMessage();
                // RIPE returns messages as [level, text] pairs
                $messages = array_column(array_filter((array) ($e->getOutput()['messages'] ?? []), fn ($message) => ($message[0] ?? null) === 'error'), 1);
                if ($messages) {
                    $error .= ': ' . implode(', ', $messages);
                }
            }
        }

        return view('tools.ripe', [
            'type' => $type,
            'query' => $query,
            'results' => $results,
            'error' => $error,
        ]);
    }
}
