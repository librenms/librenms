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

use DateTime;
use Exception;
use Requests;
use Requests_Response;

class GitHub
{
    protected $tag;
    protected $from;
    protected $token;
    protected $file;
    protected $pr;
    protected $stop = false;
    protected $pull_requests = [];
    protected $changelog = [
        'feature' => [],
        'enhancement' => [],
        'breaking change' => [],
        'security' => [],
        'device' => [],
        'webui' => [],
        'graphs' => [],
        'snmp traps' => [],
        'applications' => [],
        'api' => [],
        'alerting' => [],
        'billing' => [],
        'discovery' => [],
        'polling' => [],
        'bug' => [],
        'documentation' => [],
        'translation' => [],
        'misc' => [],
    ];
    protected $changelog_users = [];
    protected $markdown;
    protected $github = 'https://api.github.com/repos/librenms/librenms';

    public function __construct($tag, $from, $file, $token = null, $pr = null)
    {
        $this->tag = $tag;
        $this->from = $from;
        $this->file = $file;
        $this->pr = $pr;
        if (!is_null($token) || getenv('GH_TOKEN')) {
            $this->token = $token ?: getenv('GH_TOKEN');
        }
    }

    /**
     *
     * Return the GitHub Authorization header for the API call
     *
     * @return array
     */
    public function getHeaders()
    {
        if (!is_null($this->token)) {
            return ['Authorization' => "token {$this->token}"];
        }
        return [];
    }

    /**
     *
     * Get the release information for a specific tag
     *
     * @param $tag
     * @return mixed
     */
    public function getRelease($tag)
    {
        $release = Requests::get($this->github . "/releases/tags/$tag", $this->getHeaders());
        return json_decode($release->body, true);
    }

    /**
     *
     * Get a single pull request information
     *
     */
    public function getPullRequest()
    {
        $pull_request = Requests::get($this->github . "/pulls/{$this->pr}", $this->getHeaders());
        $this->pr = json_decode($pull_request->body, true);
    }

    /**
     *
     * Get all closed pull requests up to a certain date
     *
     * @param $date
     * @param int $page
     */
    public function getPullRequests($date, $page = 1)
    {
        $prs = Requests::get($this->github . "/pulls?state=closed&sort=updated&direction=desc&page=$page", $this->getHeaders());
        $prs = json_decode($prs->body, true);
        foreach ($prs as $k => $pr) {
            if ($pr['merged_at']) {
                try {
                    $created = new DateTime($pr['created_at']);
                    $merged = new DateTime($pr['merged_at']);
                    $updated = new DateTime($pr['updated_at']);
                    $end_date = new DateTime($date);
                    if (isset($this->pr['merged_at']) && $merged > new DateTime($this->pr['merged_at'])) {
                        // If the date of this PR is newer than the final PR then skip over it
                        continue;
                    } elseif ($created < $end_date && $merged < $end_date && $updated >= $end_date) {
                        // If this PR was created and merged before the last tag but has been updated since then skip over
                        continue;
                    } elseif ($created < $end_date && $merged < $end_date && $updated < $end_date) {
                        // If the date of this PR is older than the last release we're done
                        return;
                    } else {
                        // If not, assign this PR to the array
                        $this->pull_requests[] = $pr;
                    }
                } catch (Exception $e) {
                    return;
                }
            }
        }

        // recurse through the pages
        $this->getPullRequests($date, $page + 1);
    }

    /**
     *
     * Build the data for the change log.
     *
     */
    public function buildChangeLog()
    {
        $valid_labels = array_keys($this->changelog);

        foreach ($this->pull_requests as $k => $pr) {
            if (isset($this->changelog_users[$pr['user']['login']]) === false) {
                $this->changelog_users[$pr['user']['login']] = 0;
            }
            if ($pr['merged_at']) {
                $labels = array_map(function ($label) {
                    $name = preg_replace('/ :[\S]+:/', '', strtolower($label['name']));
                    return str_replace('-', ' ', $name);
                }, $pr['labels']);

                // check valid labels in order
                $category = 'misc';
                foreach ($valid_labels as $valid_label) {
                    if (in_array($valid_label, $labels)) {
                        $category = $valid_label;
                        break; // only put in the first found label
                    }
                }

                // only add the changelog if it isn't set to ignore
                if (!in_array('ignore changelog', $labels)) {
                    $title = addcslashes(ucfirst(trim(preg_replace('/^[\S]+: /', '', $pr['title']))), '<>');
                    $this->changelog[$category][] = "$title ([#{$pr['number']}]({$pr['html_url']})) - [{$pr['user']['login']}]({$pr['user']['html_url']})" . PHP_EOL;
                }


                $this->changelog_users[$pr['user']['login']] += 1;
            }
        }
    }

    /**
     *
     * Format the change log into Markdown.
     *
     */
    public function formatChangeLog()
    {
        $tmp_markdown = "## $this->tag" . PHP_EOL;
        $tmp_markdown .= '*(' . date('Y-m-d') . ')*' . PHP_EOL . PHP_EOL;
        if (!empty($this->changelog_users)) {
            $tmp_markdown .= "A big thank you to the following " . count($this->changelog_users) . " contributors this last month:" . PHP_EOL . PHP_EOL;
            arsort($this->changelog_users);
            foreach ($this->changelog_users as $user => $count) {
                $tmp_markdown .= "  - $user ($count)" . PHP_EOL;
            }
        }

        $tmp_markdown .= PHP_EOL;

        foreach ($this->changelog as $section => $items) {
            if (!empty($items)) {
                $tmp_markdown .= "#### " . ucwords($section) . PHP_EOL;
                $tmp_markdown .= '* ' . implode('* ', $items) . PHP_EOL;
            }
        }

        $this->markdown = $tmp_markdown;
    }

    /**
     *
     * Update the specified file with the new Change log info.
     *
     */
    public function writeChangeLog()
    {
        if (file_exists($this->file)) {
            $existing = file_get_contents($this->file);
            $content = $this->getMarkdown() . PHP_EOL . $existing;
            if (is_writable($this->file)) {
                file_put_contents($this->file, $content);
            }
        } else {
            echo "Couldn't write to file {$this->file}" . PHP_EOL;
            exit;
        }
    }

    /**
     *
     * Return the generated markdown.
     *
     * @return mixed
     */
    public function getMarkdown()
    {
        return $this->markdown;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function createRelease()
    {
        // push the changelog and version bump
        $this->pushFileContents($this->file, file_get_contents($this->file), "Changelog for $this->tag");
        $updated_sha = $this->pushVersionBump();

        // make sure the markdown is built
        if (empty($this->markdown)) {
            $this->createChangelog(false);
        }

        $release = Requests::post($this->github . "/releases", $this->getHeaders(), json_encode([
            'tag_name' => $this->tag,
            'target_commitish' => $updated_sha,
            'body' => $this->markdown,
            'draft' => false,
        ]));

        return $release->status_code == 201;
    }

    /**
     * Function to control the creation of creating a change log.
     * @param bool $write
     * @throws Exception
     */
    public function createChangelog($write = true)
    {
        $previous_release = $this->getRelease($this->from);
        if (!is_null($this->pr)) {
            $this->getPullRequest();
        }

        if (!isset($previous_release['published_at'])) {
            throw new Exception("Could not find previous release tag.");
        }

        $this->getPullRequests($previous_release['published_at']);
        $this->buildChangeLog();
        $this->formatChangeLog();

        if ($write) {
            $this->writeChangeLog();
        }
    }

    private function pushVersionBump()
    {
        $version_file = 'LibreNMS/Util/Version.php';
        $contents = file_get_contents(base_path($version_file));
        $updated_contents = preg_replace("/const VERSION = '[^']+';/", "const VERSION = '$this->tag';", $contents);

        return $this->pushFileContents($version_file, $updated_contents, "Bump version to $this->tag");
    }

    /**
     * @param string $file Path in git repo
     * @param string $contents new file contents
     * @param string $message The commit message
     * @return Requests_Response
     */
    private function pushFileContents($file, $contents, $message)
    {
        $existing = Requests::get($this->github . '/contents/' . $file, $this->getHeaders());
        $existing_sha = json_decode($existing->body)->sha;

        $updated = Requests::put($this->github . '/contents/' . $file, $this->getHeaders(), json_encode([
            'message' => $message,
            'content' => base64_encode($contents),
            'sha' => $existing_sha,
        ]));

        return json_decode($updated->body)->commit->sha;
    }
}
