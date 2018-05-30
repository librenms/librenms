<?php

/**
 * GitHub.php
 *
 * An interface to GitHubs api
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */

namespace LibreNMS\Util;

use Requests;
use DateTime;

class GitHub
{
    protected $tag;
    protected $from;
    protected $token;
    protected $file;
    protected $stop = false;
    protected $pull_requests = [];
    protected $changelog = [];
    protected $markdown;
    protected $labels = ['webui', 'api', 'documentation', 'security', 'feature', 'enhancement', 'device', 'bug', 'alerting'];
    protected $github = 'https://api.github.com/repos/librenms/librenms';

    public function __construct($tag, $from, $file, $token = null)
    {
        $this->tag  = $tag;
        $this->from = $from;
        $this->file = $file;
        if (is_null($token) === false || getenv('GH_TOKEN')) {
            $this->token = $token ?: getenv('GH_TOKEN');
        }
    }

    public function getToken()
    {
        if (is_null($this->token) === false) {
            return ['Authorization' => "token {$this->token}"];
        }
        return [];
    }

    public function getRelease($tag)
    {
        $release = Requests::get($this->github . "/releases/tags/$tag", self::getToken());
        return json_decode($release->body, true);
    }

    public function getPullRequests($date, $page = 1)
    {
        $prs = Requests::get($this->github . "/pulls?state=closed&page=$page", self::getToken());
        $prs = json_decode($prs->body, true);
        foreach ($prs as $k => $pr) {
            if ($pr['merged_at']) {
                $merged = new DateTime($pr['merged_at']);
                $end_date = new DateTime($date);
                if ($merged < $end_date) {
                    return true;
                } else {
                    $this->pull_requests[] = $pr;
                }
            }
        }
        $this->getPullRequests($date, $page+1);
    }

    public function buildChangeLog()
    {
        $output = [];
        $users  = [];
        foreach ($this->pull_requests as $k => $pr) {
            if (isset($users[$pr['user']['login']]) === false) {
                $users[$pr['user']['login']] = 0;
            }
            if ($pr['merged_at']) {
                foreach ($pr['labels'] as $k => $label) {
                    $name = preg_replace('/ :[\S]+:/', '', strtolower($label['name']));
                    if (in_array($name, $this->labels)) {
                        $title = ucfirst(trim(preg_replace('/^[\S]+: /', '', $pr['title'])));
                        $output[$name][] = "$title ([#{$pr['number']}]({$pr['html_url']})) - [{$pr['user']['login']}]({$pr['user']['html_url']})" . PHP_EOL;
                    }
                }
                $users[$pr['user']['login']] += 1;
            }
        }
        $this->changelog = ['changelog' => $output, 'users' => $users];
    }

    public function formatChangeLog()
    {
        $tmp_markdown = "##$this->tag" . PHP_EOL;
        $tmp_markdown .= '*(' . date('Y-m-d') . ')*' . PHP_EOL;
        if (!empty($this->changelog['users'])) {
            $tmp_markdown .= count($this->changelog['users']) . " contributing users" . PHP_EOL;
            asort($this->changelog['users']);
            foreach (array_reverse($this->changelog['users']) as $user => $count) {
                $tmp_markdown .= "  - $user ($count)" . PHP_EOL;
            }
        }

        $tmp_markdown .= PHP_EOL;

        foreach ($this->changelog['changelog'] as $section => $items) {
            $tmp_markdown .= "#### " . ucfirst($section) . PHP_EOL;
            $tmp_markdown .= '* ' . implode('* ', $items) . PHP_EOL;
        }

        $this->markdown = $tmp_markdown;
    }

    public function writeChangeLog()
    {
        $existing = file_get_contents($this->file);
        $content  = $this->markdown . PHP_EOL . $existing;
        file_put_contents($this->file, $content);
    }

    public function createRelease()
    {
        $previous_release = $this->getRelease($this->from);
        $this->getPullRequests($previous_release['published_at']);
        $this->buildChangeLog();
        $this->formatChangeLog();
        $this->writeChangeLog();
    }

}
