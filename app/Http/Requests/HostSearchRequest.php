<?php

namespace App\Http\Requests;

use App\Http\Controllers\Search\HostListQuery;
use App\Models\Port;
use Illuminate\Foundation\Http\FormRequest;
use LibreNMS\Util\SearchTerm;

/**
 * A network search, shared by the results and the export of those results
 */
class HostSearchRequest extends FormRequest
{
    /** @var list<SearchTerm>|null */
    private ?array $allTerms = null;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q' => 'nullable|string|max:8000', // room for a pasted list of addresses
            'page' => 'nullable|integer|min:1',
            'sort' => 'nullable|in:ip,mac,seen',
            'dir' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|in:10,25,50,100',
            'view' => 'nullable|in:host',
            // the table export button's own parameters
            'export' => 'nullable|in:page,all',
            'perPage' => 'nullable|integer|in:10,25,50,100',
            ...Port::filterValidationRules(),
        ];
    }

    /**
     * The terms that get searched, empty lists every host
     *
     * @return list<SearchTerm>
     */
    public function terms(): array
    {
        return array_slice($this->allTerms(), 0, SearchTerm::MAX_TERMS);
    }

    /**
     * How many terms were given, this may be more than get searched
     */
    public function termCount(): int
    {
        return count($this->allTerms());
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function filters(): array
    {
        return array_filter($this->validated('filter') ?? []);
    }

    /**
     * Asking for the forwarding tables lists every port a host is on, not just the closest
     */
    public function listsEveryPort(): bool
    {
        return ($this->filters()['source']['eq'] ?? null) === 'fdb';
    }

    public function listQuery(): HostListQuery
    {
        return new HostListQuery($this->terms(), $this->filters(), $this->user());
    }

    /**
     * @return list<SearchTerm>
     */
    private function allTerms(): array
    {
        return $this->allTerms ??= SearchTerm::parseMany($this->validated('q') ?? '');
    }
}
