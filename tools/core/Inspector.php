<?php
declare(strict_types=1);

namespace PulsoCivico\Tools;

class Inspector
{
    /**
     * Inspecciona integridad básica del proyecto:
     * - Chequeo de sintaxis en src/, bin/, config/, database/, tools/
     * - Verificación de carpetas esenciales de storage
     */
    public static function checkHealth(string $rootDir): array
    {
        $directoriesToCheck = ['src', 'bin', 'config', 'tools'];
        $syntaxResults = [];
        $totalChecked = 0;
        $totalErrors = 0;

        foreach ($directoriesToCheck as $dir) {
            $fullPath = $rootDir . '/' . $dir;
            if (is_dir($fullPath)) {
                $res = Linter::checkDirectory($fullPath);
                $totalChecked += $res['checked'];
                if (!$res['ok']) {
                    $totalErrors += count($res['errors']);
                    $syntaxResults[$dir] = $res['errors'];
                }
            }
        }

        // Verificar storage
        $requiredStorage = [
            'storage/logs',
            'storage/data',
            'storage/data/json',
            'storage/raw/html',
            'storage/raw/photos',
        ];
        $storageStatus = [];
        foreach ($requiredStorage as $relPath) {
            $path = $rootDir . '/' . $relPath;
            $storageStatus[$relPath] = [
                'exists' => is_dir($path),
                'writable' => is_writable($path),
            ];
        }

        return [
            'healthy' => $totalErrors === 0,
            'php_files_checked' => $totalChecked,
            'syntax_errors_count' => $totalErrors,
            'syntax_errors' => $syntaxResults,
            'storage_status' => $storageStatus,
        ];
    }

    public static function searchCode(string $rootDir, string $term, string $dir = ''): array
    {
        $targetDir = $dir !== '' ? $rootDir . '/' . ltrim($dir, '/\\') : $rootDir;
        $matches = [];
        if (!is_dir($targetDir)) {
            return ['error' => "Directory not found: $targetDir"];
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($targetDir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) continue;
            $path = $file->getPathname();
            // Skip vendor or storage/raw or storage/logs
            if (str_contains($path, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR) ||
                str_contains($path, DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR) ||
                str_contains($path, DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'raw') ||
                str_contains($path, DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs')) {
                continue;
            }
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (!in_array($ext, ['php', 'js', 'html', 'json', 'css', 'sql'])) continue;

            $content = @file_get_contents($path);
            if ($content === false) continue;

            if (stripos($content, $term) !== false) {
                // Find matching line numbers
                $lines = explode("\n", $content);
                $fileMatches = [];
                foreach ($lines as $idx => $line) {
                    if (stripos($line, $term) !== false) {
                        $fileMatches[] = [
                            'line' => $idx + 1,
                            'text' => trim(mb_substr($line, 0, 140))
                        ];
                        if (count($fileMatches) >= 5) break;
                    }
                }
                $relPath = str_replace([$rootDir . DIRECTORY_SEPARATOR, $rootDir . '/'], '', $path);
                $matches[$relPath] = $fileMatches;
                if (count($matches) >= 20) break;
            }
        }

        return ['term' => $term, 'matches_count' => count($matches), 'matches' => $matches];
    }
}
