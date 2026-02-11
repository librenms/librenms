<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // move discovery
        $old_discovery_dir = base_path('includes/definitions/discovery');
        $this->moveFiles(
            $old_discovery_dir,
            resource_path('definitions/os_discovery'),
        );
        $this->deleteIfEmpty($old_discovery_dir);

        // move definitions
        $old_definition_dir = base_path('includes/definitions');
        $this->moveFiles(
            $old_definition_dir,
            resource_path('definitions/os_detection'),
        );
        $this->deleteIfEmpty($old_definition_dir);
    }

    /**
     * @param  string  $old_discovery_dir
     * @param  string  $new_discovery_dir
     * @return void
     */
    private function moveFiles(string $old_discovery_dir, string $new_discovery_dir): void
    {
        if (! is_dir($old_discovery_dir)) {
            return; // nothing to do
        }

        if (! is_dir($new_discovery_dir)) {
            mkdir($new_discovery_dir);
        }

        foreach (new DirectoryIterator($old_discovery_dir) as $fileinfo) {
            if ($fileinfo->isDot()) {
                continue;
            }

            if ($fileinfo->isFile()) {
                $moved = rename($fileinfo->getPathname(), $new_discovery_dir . '/' . $fileinfo->getFilename());
                echo 'moved detection ' . $fileinfo->getFilename() . PHP_EOL;

                if (! $moved) {
                    throw new Exception('Failed to move: ' . $fileinfo->getFilename());
                }
            }
        }
    }

    private function deleteIfEmpty($dir): void
    {
        if (is_dir($dir)) {
            $remainingIterator = new DirectoryIterator($dir);
            $notEmpty = false;

            foreach ($remainingIterator as $fileinfo) {
                if (! $fileinfo->isDot()) {
                    $notEmpty = true;
                    break;
                }
            }

            if ($notEmpty) {
                return;
            }

            rmdir($dir);
        }
    }
};
