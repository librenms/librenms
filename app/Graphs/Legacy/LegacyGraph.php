<?php

namespace App\Graphs\Legacy;

use App\Data\Graphing\GraphImage;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\GraphPageContext;
use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\Legacy\LegacyAuthResult;
use App\Data\Graphing\Legacy\LegacyGraphResult;
use App\Facades\Rrd;
use App\Graphs\AbstractGraph;
use App\Graphs\Rrd\RrdGraphRenderer;
use LibreNMS\Exceptions\GraphException;
use LibreNMS\Exceptions\InvalidGraph;

/**
 * Adapter for the graph include files in includes/html/graphs
 */
class LegacyGraph extends AbstractGraph
{
    private readonly LegacyGraphScript $script;
    private ?LegacyAuthResult $authResult = null;
    private ?LegacyGraphResult $graphResult = null;

    /**
     * @throws InvalidGraph
     */
    public function __construct(GraphParameters $params)
    {
        parent::__construct($params);

        $this->script = LegacyGraphScript::find($params->type, $params->subtype) ?? throw new InvalidGraph;
    }

    protected function isAuthorized(): bool
    {
        return $this->authResult()->authorized;
    }

    public function getPageContext(): GraphPageContext
    {
        return $this->authResult()->pageContext;
    }

    /**
     * Graph files may set the title, unauthorized users do not get to run them
     */
    public function getTitle(): string
    {
        $title = $this->authorize() ? $this->graphResult()->title : '';

        return $title ?: parent::getTitle();
    }

    protected function options(): GraphOptions
    {
        return $this->graphResult()->options;
    }

    /**
     * The full rrdtool graph argument list
     *
     * @return array<int, string>
     *
     * @throws GraphException
     */
    public function getRrdCommandOptions(): array
    {
        $this->checkRenderable();

        return $this->buildRrdOptions();
    }

    public function getDebugCommand(): ?string
    {
        return $this->renderer()->debugCommand($this->getRrdCommandOptions());
    }

    protected function draw(): GraphImage
    {
        return $this->renderer()->draw($this->getTitle(), $this->buildRrdOptions(), $this->dataFilesMissing(...));
    }

    private function renderer(): RrdGraphRenderer
    {
        return app(RrdGraphRenderer::class, ['params' => $this->params]);
    }

    /**
     * @return array<int, string>
     *
     * @throws GraphException
     */
    private function buildRrdOptions(): array
    {
        return $this->renderer()->commandOptions($this->getTitle(), $this->options(), $this->graphResult()->rrdOptions);
    }

    /**
     * Whether a failed draw is because of missing data files.
     * Only checked after a failed draw, each check may be a request to rrdcached
     */
    private function dataFilesMissing(): bool
    {
        $files = $this->graphResult()->rrdFiles;

        foreach ($files as $file) {
            if (Rrd::checkRrdExists($file)) {
                return false;
            }
        }

        return $files !== [];
    }

    /**
     * @throws GraphException
     */
    private function authResult(): LegacyAuthResult
    {
        return $this->authResult ??= $this->script->runAuth($this->requestedDevice(), $this->requestedPort(), $this->params->input->all());
    }

    /**
     * @throws GraphException
     */
    private function graphResult(): LegacyGraphResult
    {
        return $this->graphResult ??= $this->script->runGraph($this->authResult(), $this->params);
    }
}
