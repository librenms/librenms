<?php

namespace App\Restify;

use App\Models\Bill;
use Binaryk\LaravelRestify\Fields\BelongsToMany;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class BillRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Bill::class;

    public static string $id = 'bill_id';

    public static string $title = 'bill_name';




    public static function related(): array
    {
        return [
            'ports' => BelongsToMany::make('ports', PortRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'name' => SearchableFilter::make()->setColumn('bill_name'),
        ];
    }

    public static function matches(): array
    {
        return [
            'name' => MatchFilter::make()->setType('text')->setColumn('bill_name'),
            'category' => MatchFilter::make()->setType('text')->setColumn('bill_type'),
            'day' => MatchFilter::make()->setType('integer')->setColumn('bill_day'),
            'customerId' => MatchFilter::make()->setType('text')->setColumn('bill_custid'),
            'reference' => MatchFilter::make()->setType('text')->setColumn('bill_ref'),
            'notes' => MatchFilter::make()->setType('text')->setColumn('bill_notes'),
            'rate95thIn' => MatchFilter::make()->setType('integer')->setColumn('rate_95th_in'),
            'rate95thOut' => MatchFilter::make()->setType('integer')->setColumn('rate_95th_out'),
            'rate95th' => MatchFilter::make()->setType('integer')->setColumn('rate_95th'),
            'direction95th' => MatchFilter::make()->setType('text')->setColumn('dir_95th'),
            'totalData' => MatchFilter::make()->setType('integer')->setColumn('total_data'),
            'totalDataIn' => MatchFilter::make()->setType('integer')->setColumn('total_data_in'),
            'totalDataOut' => MatchFilter::make()->setType('integer')->setColumn('total_data_out'),
            'rateAverageIn' => MatchFilter::make()->setType('integer')->setColumn('rate_average_in'),
            'rateAverageOut' => MatchFilter::make()->setType('integer')->setColumn('rate_average_out'),
            'rateAverage' => MatchFilter::make()->setType('integer')->setColumn('rate_average'),
            'updatedAt' => MatchFilter::make()->setType('datetime')->setColumn('bill_last_calc'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'name' => SortableFilter::make()->setColumn('bill_name'),
            'category' => SortableFilter::make()->setColumn('bill_type'),
            'day' => SortableFilter::make()->setColumn('bill_day'),
            'customerId' => SortableFilter::make()->setColumn('bill_custid'),
            'reference' => SortableFilter::make()->setColumn('bill_ref'),
            'notes' => SortableFilter::make()->setColumn('bill_notes'),
            'rate95thIn' => SortableFilter::make()->setColumn('rate_95th_in'),
            'rate95thOut' => SortableFilter::make()->setColumn('rate_95th_out'),
            'rate95th' => SortableFilter::make()->setColumn('rate_95th'),
            'direction95th' => SortableFilter::make()->setColumn('dir_95th'),
            'totalData' => SortableFilter::make()->setColumn('total_data'),
            'totalDataIn' => SortableFilter::make()->setColumn('total_data_in'),
            'totalDataOut' => SortableFilter::make()->setColumn('total_data_out'),
            'rateAverageIn' => SortableFilter::make()->setColumn('rate_average_in'),
            'rateAverageOut' => SortableFilter::make()->setColumn('rate_average_out'),
            'rateAverage' => SortableFilter::make()->setColumn('rate_average'),
            'updatedAt' => SortableFilter::make()->setColumn('bill_last_calc'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('name', fn ($value, $model) => $model->bill_name)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->bill_name = $request->input($attribute);
                    }
                })
                ->rules('required', 'string'),
            field('category', fn ($value, $model) => $model->bill_type)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->bill_type = $request->input($attribute);
                    }
                })
                ->rules('nullable', 'string'),
            field('day', fn ($value, $model) => $model->bill_day)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->bill_day = $request->input($attribute);
                    }
                })
                ->rules('nullable', 'integer'),
            field('customerId', fn ($value, $model) => $model->bill_custid)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->bill_custid = $request->input($attribute);
                    }
                })
                ->rules('nullable', 'string'),
            field('reference', fn ($value, $model) => $model->bill_ref)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->bill_ref = $request->input($attribute);
                    }
                })
                ->rules('nullable', 'string'),
            field('notes', fn ($value, $model) => $model->bill_notes)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->bill_notes = $request->input($attribute);
                    }
                })
                ->rules('nullable', 'string'),
            field('rate95thIn', fn ($value, $model) => $model->rate_95th_in)->readonly(),
            field('rate95thOut', fn ($value, $model) => $model->rate_95th_out)->readonly(),
            field('rate95th', fn ($value, $model) => $model->rate_95th)->readonly(),
            field('direction95th', fn ($value, $model) => $model->dir_95th)->readonly(),
            field('totalData', fn ($value, $model) => $model->total_data)->readonly(),
            field('totalDataIn', fn ($value, $model) => $model->total_data_in)->readonly(),
            field('totalDataOut', fn ($value, $model) => $model->total_data_out)->readonly(),
            field('rateAverageIn', fn ($value, $model) => $model->rate_average_in)->readonly(),
            field('rateAverageOut', fn ($value, $model) => $model->rate_average_out)->readonly(),
            field('rateAverage', fn ($value, $model) => $model->rate_average)->readonly(),
            field('updatedAt', fn ($value, $model) => $model->bill_last_calc)->readonly(),
        ];
    }
}
