<?php

/**
 * ConsolePagerTest.php
 *
 * Test the pager mode selection without spawning a real pager.
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
 * @copyright  2026 LibreNMS
 */

namespace LibreNMS\Tests\Unit;

use LibreNMS\Tests\TestCase;
use LibreNMS\Util\ConsolePager;

final class ConsolePagerTest extends TestCase
{
    private string $buffer = '';

    private function pager(?string $command = null, bool $interactive = true, int $perPage = 2): ConsolePager
    {
        // the writer is injected so nothing reaches the real terminal
        return new ConsolePager(
            $command,
            $perPage,
            function (string $text): void {
                $this->buffer .= $text;
            },
            $interactive
        );
    }

    /**
     * Not a terminal, for example a pipe or a file: no paging at all, so the
     * output can be captured whole.
     */
    public function testNotInteractiveWritesDirectly(): void
    {
        $pager = $this->pager(interactive: false);

        $this->assertSame(ConsolePager::MODE_PLAIN, $pager->open());
        $pager->write("uno\ndue\ntre\n");

        $this->assertSame("uno\ndue\ntre\n", $this->buffer);
        $this->assertFalse($pager->quitRequested());
    }

    /**
     * cat means no pager, from the option as well as from the environment.
     */
    public function testCatDisablesPaging(): void
    {
        $pager = $this->pager('cat');

        $this->assertSame(ConsolePager::MODE_PLAIN, $pager->open());
        $pager->write("x\n");

        $this->assertSame("x\n", $this->buffer);
    }

    public function testEmptyCommandDisablesPaging(): void
    {
        $pager = $this->pager('');

        $this->assertSame(ConsolePager::MODE_PLAIN, $pager->open());
    }

    /**
     * A command that cannot be run must fall back to pausing instead of
     * spawning a shell that dies and swallows the report.
     */
    public function testMissingBinaryFallsBack(): void
    {
        $pager = $this->pager('definitely-not-installed-xyz');

        $this->assertSame(ConsolePager::MODE_ENTER, $pager->open());
    }

    public function testExistingBinaryIsAccepted(): void
    {
        $pager = $this->pager('cat -');

        $this->assertContains($pager->open(), [ConsolePager::MODE_LESS, ConsolePager::MODE_ENTER]);
    }

    public function testEmptyChunkIsIgnored(): void
    {
        $pager = $this->pager(interactive: false);
        $pager->open();
        $pager->page('');

        $this->assertSame('', $this->buffer);
    }
}
