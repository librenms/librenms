<?php

namespace Database\Factories;

use App\Models\Syslog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class SyslogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Syslog::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $facilities = ['kern', 'user', 'mail', 'daemon', 'auth', 'syslog', 'lpr', 'news', 'uucp', 'cron', 'authpriv', 'ftp', 'ntp', 'security', 'console', 'solaris-cron', 'local0', 'local1', 'local2', 'local3', 'local4', 'local5', 'local6', 'local7'];
        $levels = ['emerg', 'alert', 'crit', 'err', 'warning', 'notice', 'info', 'debug'];

        return [
            'facility' => $this->faker->randomElement($facilities),
            'priority' => $this->faker->randomElement($levels),
            'level' => $this->faker->randomElement($levels),
            'tag' => $this->faker->asciify(str_repeat('*', $this->faker->numberBetween(0, 10))),
            'timestamp' => Carbon::now(),
            'program' => $this->faker->asciify(str_repeat('*', $this->faker->numberBetween(0, 32))),
            'msg' => $this->faker->text(),
        ];
    }
}
