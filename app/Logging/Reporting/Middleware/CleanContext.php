<?php
/**
 * CleanContext.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Logging\Reporting\Middleware;

use Spatie\FlareClient\Report;

class CleanContext implements \Spatie\FlareClient\FlareMiddleware\FlareMiddleware
{
    /**
     * Middleware to remove sensitive data from the context.
     *
     * @return mixed
     */
    public function handle(Report $report, \Closure $next)
    {
        try {
            $report->setApplicationPath('');
            $context = $report->allContext();

            if (isset($context['request']['url'])) {
                $context['request']['url'] = str_replace($context['headers']['host'] ?? '', 'librenms', $context['request']['url']);
            }

            if (isset($context['session']['url']['intended'])) {
                $context['session']['url']['intended'] = str_replace($context['headers']['host'] ?? '', 'librenms', $context['session']['url']['intended']);
            }

            if (isset($context['session']['_previous']['url'])) {
                $context['session']['_previous']['url'] = str_replace($context['headers']['host'] ?? '', 'librenms', $context['session']['_previous']['url']);
            }

            $context['headers']['host'] = null;
            $context['headers']['referer'] = null;

            $report->userProvidedContext($context);
        } catch (\Exception $e) {
        }

        return $next($report);
    }
}
