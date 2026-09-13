<?php

namespace App\Http\Controllers;

use App\Models\Vminfo;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VminfoController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Vminfo::class);
        $this->validateRequest($request);

        $perPage = $request->input('perPage', 50);
        $query = Vminfo::listing($request->user(), null, $request->array('filter'));
        $total = $query->toBase()->getCountForPagination();

        return view('vminfo.index', [
            'vms' => $query->paginate($perPage === 'all' ? max($total, 1) : (int) $perPage, total: $total)
                ->appends($request->query()),
            'filterFields' => Vminfo::filterFieldDefinitions(),
            'filter' => $request->array('filter'),
            'perPage' => $perPage,
        ]);
    }

    /**
     * directly stream CSV to browser
     */
    public function export(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', Vminfo::class);
        $this->validateRequest($request);

        $query = Vminfo::listing($request->user(), $request->integer('device_id') ?: null, $request->array('filter'));
        $perPage = $request->input('perPage', 50);

        $rows = $request->input('export') === 'page' && $perPage !== 'all'
            ? $query->forPage($request->integer('page') ?: 1, (int) $perPage ?: 50)->get()
            : $query->lazy();

        $headers = [
            __('VM Name'),
            __('Host'),
            __('Sysname'),
            __('Power Status'),
            __('Type'),
            __('Operating System'),
            __('Memory'),
            __('vCPUs'),
        ];

        return response()->streamDownload(function () use ($rows, $headers): void {
            $output = fopen('php://output', 'w');
            fwrite($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM so Excel reads UTF-8
            fputcsv($output, $headers);

            foreach ($rows as $vm) {
                fputcsv($output, [
                    $vm->parentDevice?->displayName() ?? $vm->vmwVmDisplayName,
                    $vm->device?->displayName(),
                    $vm->device?->sysName,
                    $vm->stateLabel[0],
                    $vm->vm_type,
                    $vm->operatingSystem,
                    $vm->memoryFormatted,
                    $vm->vmwVmCpus,
                ]);
            }

            fclose($output);
        }, 'vminfo-' . date('Y-m-d-His') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function validateRequest(Request $request): void
    {
        $request->validate([
            'device_id' => 'integer',
            'page' => 'integer',
            'perPage' => ['regex:/^(\d+|all)$/'],
            'export' => 'in:page,all',
            ...Vminfo::filterValidationRules(),
        ]);
    }
}
