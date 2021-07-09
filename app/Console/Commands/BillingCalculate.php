<?php

namespace App\Console\Commands;

use App\Models\Bill;
use App\Models\BillHistory;
use Illuminate\Console\Command;
use LibreNMS\Util\Number;


require_once base_path() . '/includes/init.php';
class BillingCalculate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:calculate {--r}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'calculate the port usage for the billing.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        if($this->option('r')){
            $this->info("Clearing history table.\n");
            BillHistory::truncate();

        }
        //get all bills and order by bill_id
        foreach (Bill::orderBy("bill_id")->get() as $bill) {
            $this->info(str_pad($bill->bill_id . ' ' . $bill->bill_name, 30) . " \n");
            $i = 0;
            while ($i <= 24) {
                unset($class);
                unset($rate_data);
                $day_data = getDates($bill->bill_day, $i);

                $datefrom = $day_data['0'];
                $dateto = $day_data['1'];
                $check = BillHistory::where(['bill_id' => $bill->bill_id, 'bill_datefrom' => $datefrom, 'bill_dateto' => $dateto])->first();
                $period = getPeriod($bill['bill_id'], $datefrom, $dateto);
                $date_updated = str_replace('-', '', str_replace(':', '', str_replace(' ', '', $check['updated'])));
                if ($period > 0 && $dateto > $date_updated) {
                    $rate_data = getRates($bill['bill_id'], $datefrom, $dateto, $dir_95th);
                    $rate_95th = $rate_data['rate_95th'];
                    $dir_95th = $rate_data['dir_95th'];
                    $total_data = $rate_data['total_data'];
                    $rate_average = $rate_data['rate_average'];

                    if ($bill['bill_type'] == 'cdr') {
                        $type = 'CDR';
                        $allowed = $bill['bill_cdr'];
                        $used = $rate_data['rate_95th'];
                        $allowed_text = Number::formatSi($allowed, 2, 3, 'bps');
                        $used_text = Number::formatSi($used, 2, 3, 'bps');
                        $overuse = ($used - $allowed);
                        $overuse = (($overuse <= 0) ? '0' : $overuse);
                        $percent = round((($rate_data['rate_95th'] / $bill['bill_cdr']) * 100), 2);
                    } elseif ($bill['bill_type'] == 'quota') {
                        $type = 'Quota';
                        $allowed = $bill['bill_quota'];
                        $used = $rate_data['total_data'];
                        $allowed_text = format_bytes_billing($allowed);
                        $used_text = format_bytes_billing($used);
                        $overuse = ($used - $allowed);
                        $overuse = (($overuse <= 0) ? '0' : $overuse);
                        $percent = round((($rate_data['total_data'] / $bill['bill_quota']) * 100), 2);
                    }

                    $this->info(strftime('%x @ %X', strtotime($datefrom)) . ' to ' . strftime('%x @ %X', strtotime($dateto)) . ' ' . str_pad($type, 8) . ' ' . str_pad($allowed_text, 10) . ' ' . str_pad($used_text, 10) . ' ' . $percent . '%');

                    if ($i == '0') {
                        $update = [
                            'rate_95th'        => $rate_data['rate_95th'],
                            'rate_95th_in'     => $rate_data['rate_95th_in'],
                            'rate_95th_out'    => $rate_data['rate_95th_out'],
                            'dir_95th'         => $rate_data['dir_95th'],
                            'total_data'       => $rate_data['total_data'],
                            'total_data_in'    => $rate_data['total_data_in'],
                            'total_data_out'   => $rate_data['total_data_out'],
                            'rate_average'     => $rate_data['rate_average'],
                            'rate_average_in'  => $rate_data['rate_average_in'],
                            'rate_average_out' => $rate_data['rate_average_out'],
                            'bill_last_calc'   => ['NOW()'],
                        ];
                        Bill::where("bill_id", $bill->bill_id)->update($update);
                        $this->info('Updated!');
                    }
                    if ($check['bill_id'] == $bill['bill_id']) {
                        $update = [
                            'rate_95th'        => $rate_data['rate_95th'],
                            'rate_95th_in'     => $rate_data['rate_95th_in'],
                            'rate_95th_out'    => $rate_data['rate_95th_out'],
                            'dir_95th'         => $rate_data['dir_95th'],
                            'rate_average'     => $rate_data['rate_average'],
                            'rate_average_in'  => $rate_data['rate_average_in'],
                            'rate_average_out' => $rate_data['rate_average_out'],
                            'traf_total'       => $rate_data['total_data'],
                            'traf_in'          => $rate_data['total_data_in'],
                            'traf_out'         => $rate_data['total_data_out'],
                            'bill_used'        => $used,
                            'bill_overuse'     => $overuse,
                            'bill_percent'     => $percent,
                            'updated'          => ['NOW()'],
                        ];
                        BillHistory::where("bill_hist_id",$check->bill_hist_id)->update($update);
                        $this->info('Updated history!');
                    } else {
                        $update = [
                            'rate_95th'        => $rate_data['rate_95th'],
                            'rate_95th_in'     => $rate_data['rate_95th_in'],
                            'rate_95th_out'    => $rate_data['rate_95th_out'],
                            'dir_95th'         => $rate_data['dir_95th'],
                            'rate_average'     => $rate_data['rate_average'],
                            'rate_average_in'  => $rate_data['rate_average_in'],
                            'rate_average_out' => $rate_data['rate_average_out'],
                            'traf_total'       => $rate_data['total_data'],
                            'traf_in'          => $rate_data['total_data_in'],
                            'traf_out'         => $rate_data['total_data_out'],
                            'bill_datefrom'    => $datefrom,
                            'bill_dateto'      => $dateto,
                            'bill_type'        => $type,
                            'bill_allowed'     => $allowed,
                            'bill_used'        => $used,
                            'bill_overuse'     => $overuse,
                            'bill_percent'     => $percent,
                            'bill_id'          => $bill['bill_id'],
                        ];
                        BillHistory::insert($update);
//                        dbInsert($update, 'bill_history');
                        $this->info('Generated history!');
                    }//end if
                    echo "\n\n";
                }//end if

                $i++;
            }
        }
        return 0;
    }
}
