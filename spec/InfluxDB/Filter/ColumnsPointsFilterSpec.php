<?php

namespace spec\InfluxDB\Filter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ColumnsPointsFilterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('InfluxDB\Filter\ColumnsPointsFilter');
    }

    function it_is_a_valid_filter()
    {
        $this->shouldImplement("InfluxDb\\Filter\\FilterInterface");
    }

    function it_should_map_columns_with_points()
    {
        $response = json_decode('[{"name":"hd_used","columns":["time","sequence_number","value","host","mount","time_precision"],"points":[[1410591684,11820001,23.2,"serverA","/mnt","s"]]}]', true);

        $this->filter($response)->shouldBeEqualTo([
            "hd_used" => [
                [
                    "time" => 1410591684,
                    "sequence_number" => 11820001,
                    "value" => 23.2,
                    "host" => "serverA",
                    "mount" => "/mnt",
                    "time_precision" => "s",
                ],
            ],
        ]);
    }

    function it_should_map_also_a_series_list()
    {
        $response = json_decode('[{"name":"list_series_result","columns":["time","name"],"points":[[0,"hd_used"]]}]', true);

        $this->filter($response)->shouldBeEqualTo([
            "list_series_result" => [
                [
                    "time" => 0,
                    "name" => "hd_used",
                ],
            ],
        ]);
    }

    function it_should_reply_to_an_empty_set()
    {
        $response = json_decode('[]', true);

        $this->filter($response)->shouldBeEqualTo([]);
    }
}
