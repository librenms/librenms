<?php
namespace App\Http\Controllers\Api\Bill;

use App\Models\Bill\Bill;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;

class BillHistoryController extends ApiController
{
    /**
     *
     * @api {get} /api/v1/bills/:id/history Get Bill History
     * @apiName Get_Bill_History
     * @apiGroup Bill History
     * @apiVersion  1.0.0
     *
     * @apiParam  {Number} id Id of the bill.
     * @apiUse Pagination
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "data": [
     *             {
     *                 "bill_hist_id": "1",
     *                 "bill_id": "1",
     *                 "updated": "2018-05-24 09:01:02",
     *                 "bill_datefrom": "2018-05-01 00:00:00",
     *                 "bill_dateto": "2018-05-31 23:59:59",
     *                 "bill_type": "CDR",
     *                 "bill_allowed": "0",
     *                 "bill_used": "274796",
     *                 "bill_overuse": "274796",
     *                 "bill_percent": "0.00",
     *                 "rate_95th_in": "274796",
     *                 "rate_95th_out": "44087",
     *                 "rate_95th": "274796",
     *                 "dir_95th": "in",
     *                 "rate_average": "135278",
     *                 "rate_average_in": "122203",
     *                 "rate_average_out": "13074",
     *                 "traf_in": "24104620",
     *                 "traf_out": "2578891",
     *                 "traf_total": "26683511",
     *                 "pdf": null
     *             }
     *         ],
     *         "current_page": 1,
     *         "from": 1,
     *         "last_page": 1,
     *         "next_page_url": null,
     *         "path": "http://example.org/api/v1/bills/1/history",
     *         "per_page": 50,
     *         "prev_page_url": null,
     *         "to": 1,
     *         "total": 1
     *     }
     *
     * @apiUse NotFoundError
     *
     */
    public function index(Bill $bill)
    {
        return $this->paginateResponse($bill->history());
    }
}
