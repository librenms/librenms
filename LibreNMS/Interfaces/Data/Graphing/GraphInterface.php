<?php

namespace LibreNMS\Interfaces\Data\Graphing;

use App\Data\Graphing\GraphImage;
use App\Data\Graphing\GraphPageContext;
use LibreNMS\Exceptions\GraphException;

/**
 * A graph that can be authorized and rendered to an image, independent of the backend that draws it
 */
interface GraphInterface
{
    /**
     * Can the current user view this graph
     *
     * @throws GraphException
     */
    public function authorize(): bool;

    /**
     * What the graph page shows around this graph, such as the graphed device and port
     *
     * @throws GraphException
     */
    public function getPageContext(): GraphPageContext;

    /**
     * @throws GraphException
     */
    public function getTitle(): string;

    /**
     * A shell command that draws this graph, for troubleshooting
     *
     * @throws GraphException
     */
    public function getDebugCommand(): ?string;

    /**
     * Validates input and authorization, then draws the image
     *
     * @throws GraphException
     */
    public function render(): GraphImage;
}
