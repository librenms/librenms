<?php

namespace LibreNMS\Tests\Unit\Graphs;

use App\Data\Graphing\GraphImage;
use App\Data\Graphing\GraphParameters;
use App\Graphs\AbstractGraph;
use LibreNMS\Enum\ImageFormat;
use LibreNMS\Exceptions\GraphException;
use LibreNMS\Exceptions\NoGraphData;
use LibreNMS\Tests\TestCase;
use RuntimeException;

class AbstractGraphTest extends TestCase
{
    public function test_errors_from_drawing_become_graph_errors(): void
    {
        $graph = $this->graph(fn () => throw new RuntimeException('jpgraph failed'));

        try {
            $graph->render();
            $this->fail('Expected a graph exception');
        } catch (GraphException $e) {
            $this->assertSame('Graph Error', $e->getMessage());
            $this->assertInstanceOf(RuntimeException::class, $e->getPrevious());
        }
    }

    public function test_graph_errors_are_kept(): void
    {
        $this->expectException(NoGraphData::class);

        $this->graph(fn () => throw new NoGraphData)->render();
    }

    public function test_image_is_named_after_the_user_title(): void
    {
        $this->assertSame('Core uplink', $this->graph(params: ['graph_title' => 'Core uplink'])->render()->title);
        $this->assertSame('Drawn', $this->graph()->render()->title);
    }

    public function test_authorization_is_checked_once(): void
    {
        $checks = 0;
        $graph = $this->graph(isAuthorized: function () use (&$checks) {
            $checks++;

            return true;
        });

        $graph->authorize();
        $graph->render();

        $this->assertSame(1, $checks);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function graph(?\Closure $draw = null, array $params = [], ?\Closure $isAuthorized = null): AbstractGraph
    {
        $draw ??= fn () => new GraphImage(ImageFormat::Png, 'Drawn', 'image-data');
        $isAuthorized ??= fn () => true;

        return new class(new GraphParameters(['type' => 'device_test', ...$params]), $draw, $isAuthorized) extends AbstractGraph
        {
            public function __construct(GraphParameters $params, private readonly \Closure $drawer, private readonly \Closure $authorizer)
            {
                parent::__construct($params);
            }

            protected function isAuthorized(): bool
            {
                return ($this->authorizer)();
            }

            protected function draw(): GraphImage
            {
                return ($this->drawer)();
            }
        };
    }
}
