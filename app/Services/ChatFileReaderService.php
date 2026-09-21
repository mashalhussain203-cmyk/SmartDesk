<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Smalot\PdfParser\Parser as PdfParser;
use Throwable;
use ZipArchive;

class ChatFileReaderService
{
    public const MAX_FILES = 5;

    public const MAX_FILE_BYTES = 10 * 1024 * 1024;

    public const MAX_CHARS_PER_FILE = 40000;

    public const MAX_TOTAL_CHARS = 90000;

    /** @var array<int,string> */
    private const IMAGE_EXTENSIONS = [
        'jpg',
        'jpeg',
        'png',
        'webp',
    ];

    /** @var array<int,string> */
    private const TEXT_EXTENSIONS = [
        'txt', 'md', 'markdown', 'csv', 'json', 'xml', 'html', 'htm',
        'log', 'php', 'blade.php', 'js', 'mjs', 'cjs', 'ts', 'jsx', 'tsx',
        'css', 'scss', 'sass', 'less', 'sql', 'yaml', 'yml', 'ini', 'conf',
        'env', 'py', 'java', 'kt', 'kts', 'c', 'cc', 'cpp', 'h', 'hpp',
        'cs', 'go', 'rs', 'sh', 'bash', 'zsh', 'ps1', 'rb', 'swift', 'dart',
        'vue', 'svelte',
    ];

    /** @var array<int,string> */
    private const DOCUMENT_EXTENSIONS = [
        'pdf',
        'docx',
        'xlsx',
        'pptx',
    ];

    public function __construct(
        private readonly GroqVisionService $vision
    ) {
    }

    /**
     * @param array<int,UploadedFile> $files
     *
     * @return array{context:string,files:array<int,array<string,mixed>>}
     */
    public function readMany(
        array $files,
        string $userQuestion = ''
    ): array {
        if (count($files) > self::MAX_FILES) {
            throw ValidationException::withMessages([
                'files' => sprintf(
                    'Je kunt maximaal %d bestanden tegelijk uploaden.',
                    self::MAX_FILES
                ),
            ]);
        }

        $sections = [];
        $metadata = [];
        $totalChars = 0;

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $result = $this->read(
                $file,
                $userQuestion
            );

            $remaining = self::MAX_TOTAL_CHARS - $totalChars;

            if ($remaining <= 0) {
                break;
            }

            $text = mb_substr(
                $result['text'],
                0,
                $remaining
            );

            $wasTotalTruncated =
                mb_strlen($result['text']) > mb_strlen($text);

            $sections[] =
                "===== BESTAND: {$result['name']} =====\n"
                . $text
                . "\n===== EINDE BESTAND =====";

            $chars = mb_strlen($text);

            $metadata[] = [
                'name' => $result['name'],
                'extension' => $result['extension'],
                'chars' => $chars,
                'truncated' => $result['truncated'] || $wasTotalTruncated,
                'analysis' => $result['analysis'],
            ];

            $totalChars += $chars;
        }

        return [
            'context' => implode("\n\n", $sections),
            'files' => $metadata,
        ];
    }

    /**
     * @return array{name:string,extension:string,text:string,truncated:bool,analysis:string}
     */
    public function read(
        UploadedFile $file,
        string $userQuestion = ''
    ): array {
        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                'files' => 'Een van de bestanden kon niet correct worden geüpload.',
            ]);
        }

        $size = (int) ($file->getSize() ?? 0);

        if ($size < 1 || $size > self::MAX_FILE_BYTES) {
            throw ValidationException::withMessages([
                'files' => 'Elk bestand mag maximaal 10 MB groot zijn.',
            ]);
        }

        $name = $this->safeFileName(
            $file->getClientOriginalName()
        );

        $extension = $this->extensionFor($name);

        $allowed = array_merge(
            self::TEXT_EXTENSIONS,
            self::DOCUMENT_EXTENSIONS,
            self::IMAGE_EXTENSIONS
        );

        if (! in_array($extension, $allowed, true)) {
            throw ValidationException::withMessages([
                'files' => sprintf(
                    'Bestandstype .%s wordt niet ondersteund. Gebruik bijvoorbeeld PDF, DOCX, XLSX, PPTX, TXT, CSV, JSON, JPG, PNG of WEBP.',
                    $extension !== '' ? $extension : 'onbekend'
                ),
            ]);
        }

        $absolutePath = $file->getRealPath();

        if (
            ! is_string($absolutePath)
            || $absolutePath === ''
            || ! is_file($absolutePath)
        ) {
            throw ValidationException::withMessages([
                'files' => 'Het tijdelijke uploadbestand kon niet worden gelezen.',
            ]);
        }

        $analysis = 'text';

        try {
            if (in_array($extension, self::IMAGE_EXTENSIONS, true)) {
                $analysis = 'vision';
                $text = $this->readImage(
                    $absolutePath,
                    $extension,
                    $name,
                    $userQuestion
                );
            } else {
                $text = match ($extension) {
                    'pdf' => $this->readPdfSmart(
                        $absolutePath,
                        $name,
                        $userQuestion
                    ),

                    'docx' => $this->readDocx($absolutePath),
                    'xlsx' => $this->readXlsx($absolutePath),
                    'pptx' => $this->readPptx($absolutePath),
                    default => $this->readPlainText($absolutePath),
                };

                if ($extension === 'pdf') {
                    $analysis = 'pdf+vision';
                }
            }
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new RuntimeException(
                sprintf(
                    'Bestand "%s" kon niet worden uitgelezen: %s',
                    $name,
                    $exception->getMessage()
                ),
                (int) $exception->getCode(),
                $exception
            );
        }

        $text = $this->normalizeText($text);

        if ($text === '') {
            throw ValidationException::withMessages([
                'files' => sprintf(
                    'In "%s" kon geen leesbare inhoud worden gevonden.',
                    $name
                ),
            ]);
        }

        $truncated = mb_strlen($text) > self::MAX_CHARS_PER_FILE;

        if ($truncated) {
            $text = mb_substr(
                $text,
                0,
                self::MAX_CHARS_PER_FILE
            ) . "\n\n[Bestand ingekort omdat het te lang is.]";
        }

        return [
            'name' => $name,
            'extension' => $extension,
            'text' => $text,
            'truncated' => $truncated,
            'analysis' => $analysis,
        ];
    }

    private function readImage(
        string $absolutePath,
        string $extension,
        string $name,
        string $userQuestion
    ): string {
        if (! $this->vision->isConfigured()) {
            throw ValidationException::withMessages([
                'files' => 'Afbeeldingen vereisen Groq Vision. Controleer GROQ_API_KEY en GROQ_VISION_MODEL.',
            ]);
        }

        $mime = $this->imageMime($absolutePath, $extension);

        return $this->vision->analyzeMany([
            [
                'path' => $absolutePath,
                'mime' => $mime,
                'label' => $name,
            ],
        ], $userQuestion);
    }

    private function readPdfSmart(
        string $absolutePath,
        string $name,
        string $userQuestion
    ): string {
        $text = '';

        if (class_exists(PdfParser::class)) {
            try {
                $parser = new PdfParser();
                $pdf = $parser->parseFile($absolutePath);
                $text = $this->normalizeText($pdf->getText());
            } catch (Throwable) {
                $text = '';
            }
        }

        $visionText = '';
        $shouldUseVision = $this->pdfVisionEnabled()
            && $this->vision->isConfigured();

        if ($shouldUseVision) {
            $pages = $this->renderPdfPages(
                $absolutePath,
                $this->pdfVisionPageLimit($text)
            );

            if ($pages !== []) {
                try {
                    $visionText = $this->vision->analyzeMany(
                        array_map(
                            static fn (array $page): array => [
                                'path' => $page['path'],
                                'mime' => 'image/png',
                                'label' => $name . ' - pagina ' . $page['page'],
                            ],
                            $pages
                        ),
                        $userQuestion
                    );
                } finally {
                    $this->deleteRenderedPages($pages);
                }
            }
        }

        if ($text === '' && $visionText === '') {
            $hint = $this->pdftoppmAvailable()
                ? 'De PDF bevat geen uitleesbare tekst of beelden.'
                : 'Dit lijkt een gescande PDF. Voeg op Railway RAILPACK_DEPLOY_APT_PACKAGES="... poppler-utils" toe zodat gescande PDF-pagina\'s als afbeeldingen kunnen worden gelezen.';

            throw ValidationException::withMessages([
                'files' => $hint,
            ]);
        }

        $parts = [];

        if ($text !== '') {
            $parts[] = "--- Uitgelezen PDF-tekst ---\n" . $text;
        }

        if ($visionText !== '') {
            $parts[] = "--- Visuele/OCR-analyse van PDF-pagina's ---\n" . $visionText;
        }

        return implode("\n\n", $parts);
    }

    /**
     * @return array<int,array{path:string,page:int}>
     */
    private function renderPdfPages(
        string $absolutePath,
        int $maxPages
    ): array {
        if (! $this->pdftoppmAvailable()) {
            return [];
        }

        $directory = rtrim(
            sys_get_temp_dir(),
            DIRECTORY_SEPARATOR
        ) . DIRECTORY_SEPARATOR . 'mashal-ai-pdf-' . bin2hex(random_bytes(8));

        if (! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            return [];
        }

        $prefix = $directory . DIRECTORY_SEPARATOR . 'page';

        $result = $this->runProcess([
            'pdftoppm',
            '-png',
            '-f',
            '1',
            '-l',
            (string) $maxPages,
            '-scale-to',
            '1800',
            $absolutePath,
            $prefix,
        ], 90);

        if ($result['exit_code'] !== 0) {
            $this->deleteDirectory($directory);
            return [];
        }

        $paths = glob($prefix . '-*.png') ?: [];

        natsort($paths);
        $paths = array_values($paths);

        $pages = [];

        foreach ($paths as $index => $path) {
            if (! is_file($path)) {
                continue;
            }

            $pages[] = [
                'path' => $path,
                'page' => $index + 1,
            ];
        }

        if ($pages === []) {
            $this->deleteDirectory($directory);
        }

        return $pages;
    }

    /**
     * @param array<int,array{path:string,page:int}> $pages
     */
    private function deleteRenderedPages(array $pages): void
    {
        $directory = null;

        foreach ($pages as $page) {
            $path = (string) ($page['path'] ?? '');

            if ($path !== '' && is_file($path)) {
                @unlink($path);
            }

            if ($directory === null && $path !== '') {
                $directory = dirname($path);
            }
        }

        if (is_string($directory)) {
            $this->deleteDirectory($directory);
        }
    }

    private function pdfVisionEnabled(): bool
    {
        return (bool) config(
            'groq-vision.pdf.enabled',
            true
        );
    }

    private function pdfVisionPageLimit(string $extractedText): int
    {
        $textRich = mb_strlen($extractedText) >= 300;

        $value = $textRich
            ? (int) config('groq-vision.pdf.visual_pages_with_text', 3)
            : (int) config('groq-vision.pdf.max_scanned_pages', 6);

        return max(1, min(12, $value));
    }

    private function pdftoppmAvailable(): bool
    {
        $result = $this->runProcess(
            ['pdftoppm', '-v'],
            5
        );

        return $result['exit_code'] === 0
            || str_contains(
                strtolower($result['stderr']),
                'pdftoppm'
            );
    }

    /**
     * @param array<int,string> $command
     * @return array{exit_code:int,stdout:string,stderr:string}
     */
    private function runProcess(
        array $command,
        int $timeoutSeconds
    ): array {
        if (! function_exists('proc_open')) {
            return [
                'exit_code' => 127,
                'stdout' => '',
                'stderr' => 'proc_open is niet beschikbaar.',
            ];
        }

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $pipes = [];
        $process = @proc_open(
            $command,
            $descriptors,
            $pipes,
            null,
            null,
            ['bypass_shell' => true]
        );

        if (! is_resource($process)) {
            return [
                'exit_code' => 127,
                'stdout' => '',
                'stderr' => 'Proces kon niet worden gestart.',
            ];
        }

        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $stdout = '';
        $stderr = '';
        $started = microtime(true);
        $timedOut = false;
        $observedExitCode = null;

        while (true) {
            $stdout .= stream_get_contents($pipes[1]) ?: '';
            $stderr .= stream_get_contents($pipes[2]) ?: '';

            $status = proc_get_status($process);

            if (! ($status['running'] ?? false)) {
                $candidateExitCode = (int) ($status['exitcode'] ?? -1);

                if ($candidateExitCode >= 0) {
                    $observedExitCode = $candidateExitCode;
                }

                break;
            }

            if ((microtime(true) - $started) > $timeoutSeconds) {
                $timedOut = true;
                proc_terminate($process, 9);
                break;
            }

            usleep(50000);
        }

        $stdout .= stream_get_contents($pipes[1]) ?: '';
        $stderr .= stream_get_contents($pipes[2]) ?: '';

        fclose($pipes[1]);
        fclose($pipes[2]);

        $closedExitCode = proc_close($process);
        $exitCode = $observedExitCode ?? $closedExitCode;

        if ($timedOut) {
            $exitCode = 124;
            $stderr .= "\nProces timeout.";
        }

        return [
            'exit_code' => (int) $exitCode,
            'stdout' => $stdout,
            'stderr' => $stderr,
        ];
    }

    private function imageMime(
        string $absolutePath,
        string $extension
    ): string {
        $detected = '';

        if (function_exists('mime_content_type')) {
            $value = @mime_content_type($absolutePath);
            $detected = is_string($value) ? strtolower($value) : '';
        }

        $allowed = [
            'image/jpeg',
            'image/png',
            'image/webp',
        ];

        if (in_array($detected, $allowed, true)) {
            return $detected;
        }

        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => throw new RuntimeException('Onbekend afbeeldingstype.'),
        };
    }

    private function readPlainText(string $absolutePath): string
    {
        $contents = file_get_contents($absolutePath);

        if ($contents === false) {
            throw new RuntimeException('Het tekstbestand kon niet worden geopend.');
        }

        if (str_contains($contents, "\0")) {
            throw ValidationException::withMessages([
                'files' => 'Dit bestand lijkt binair en kan niet als tekst worden gelezen.',
            ]);
        }

        return $contents;
    }

    private function readDocx(string $absolutePath): string
    {
        $zip = new ZipArchive();
        $opened = $zip->open($absolutePath);

        if ($opened !== true) {
            throw new RuntimeException('Het DOCX-bestand kon niet worden geopend.');
        }

        try {
            $xml = $zip->getFromName('word/document.xml');

            if (! is_string($xml)) {
                throw new RuntimeException('DOCX bevat geen leesbaar document.xml.');
            }

            $xml = preg_replace('/<\/w:p>/i', "\n", $xml) ?? $xml;
            $xml = preg_replace('/<w:tab[^>]*\/>/i', "\t", $xml) ?? $xml;
            $xml = preg_replace('/<w:br[^>]*\/>/i', "\n", $xml) ?? $xml;

            return html_entity_decode(
                strip_tags($xml),
                ENT_QUOTES | ENT_XML1,
                'UTF-8'
            );
        } finally {
            $zip->close();
        }
    }

    private function readPptx(string $absolutePath): string
    {
        $zip = new ZipArchive();
        $opened = $zip->open($absolutePath);

        if ($opened !== true) {
            throw new RuntimeException('Het PPTX-bestand kon niet worden geopend.');
        }

        try {
            $slides = [];

            for ($index = 0; $index < $zip->numFiles; $index++) {
                $entry = $zip->getNameIndex($index);

                if (
                    ! is_string($entry)
                    || ! preg_match('#^ppt/slides/slide(\d+)\.xml$#', $entry, $matches)
                ) {
                    continue;
                }

                $xml = $zip->getFromIndex($index);

                if (! is_string($xml)) {
                    continue;
                }

                $xml = preg_replace('/<\/a:p>/i', "\n", $xml) ?? $xml;
                $xml = preg_replace('/<a:br[^>]*\/>/i', "\n", $xml) ?? $xml;
                $text = trim(
                    html_entity_decode(
                        strip_tags($xml),
                        ENT_QUOTES | ENT_XML1,
                        'UTF-8'
                    )
                );

                if ($text !== '') {
                    $slides[(int) $matches[1]] = $text;
                }
            }

            ksort($slides);

            $parts = [];
            foreach ($slides as $number => $text) {
                $parts[] = "--- Dia {$number} ---\n{$text}";
            }

            return implode("\n\n", $parts);
        } finally {
            $zip->close();
        }
    }

    private function readXlsx(string $absolutePath): string
    {
        $zip = new ZipArchive();
        $opened = $zip->open($absolutePath);

        if ($opened !== true) {
            throw new RuntimeException('Het XLSX-bestand kon niet worden geopend.');
        }

        try {
            $sharedStrings = $this->xlsxSharedStrings($zip);
            $sheets = [];

            for ($index = 0; $index < $zip->numFiles; $index++) {
                $entry = $zip->getNameIndex($index);

                if (
                    ! is_string($entry)
                    || ! preg_match('#^xl/worksheets/sheet\d+\.xml$#', $entry)
                ) {
                    continue;
                }

                $xml = $zip->getFromIndex($index);

                if (! is_string($xml)) {
                    continue;
                }

                $sheetText = $this->xlsxSheetText(
                    $xml,
                    $sharedStrings
                );

                if ($sheetText !== '') {
                    $sheets[] = '--- ' . basename($entry, '.xml') . " ---\n" . $sheetText;
                }
            }

            return implode("\n\n", $sheets);
        } finally {
            $zip->close();
        }
    }

    /** @return array<int,string> */
    private function xlsxSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if (! is_string($xml)) {
            return [];
        }

        $document = simplexml_load_string($xml);

        if ($document === false) {
            return [];
        }

        $document->registerXPathNamespace(
            'm',
            'http://schemas.openxmlformats.org/spreadsheetml/2006/main'
        );

        $items = $document->xpath('//m:si') ?: [];
        $strings = [];

        foreach ($items as $item) {
            $parts = $item->xpath('.//m:t') ?: [];
            $value = '';

            foreach ($parts as $part) {
                $value .= (string) $part;
            }

            $strings[] = $value;
        }

        return $strings;
    }

    /** @param array<int,string> $sharedStrings */
    private function xlsxSheetText(
        string $xml,
        array $sharedStrings
    ): string {
        $document = simplexml_load_string($xml);

        if ($document === false) {
            return '';
        }

        $document->registerXPathNamespace(
            'm',
            'http://schemas.openxmlformats.org/spreadsheetml/2006/main'
        );

        $rows = $document->xpath('//m:sheetData/m:row') ?: [];
        $lines = [];

        foreach ($rows as $row) {
            $cells = $row->xpath('./m:c') ?: [];
            $values = [];

            foreach ($cells as $cell) {
                $attributes = $cell->attributes();
                $type = (string) ($attributes['t'] ?? '');
                $valueNodes = $cell->xpath('./m:v') ?: [];
                $value = isset($valueNodes[0]) ? (string) $valueNodes[0] : '';

                if ($type === 's' && $value !== '') {
                    $sharedIndex = (int) $value;
                    $value = $sharedStrings[$sharedIndex] ?? $value;
                } elseif ($type === 'inlineStr') {
                    $inline = $cell->xpath('.//m:t') ?: [];
                    $value = '';
                    foreach ($inline as $part) {
                        $value .= (string) $part;
                    }
                }

                $values[] = trim($value);
            }

            if ($values !== []) {
                $lines[] = implode("\t", $values);
            }
        }

        return implode("\n", $lines);
    }

    private function normalizeText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace("/[ \t]+\n/", "\n", $text) ?? $text;
        $text = preg_replace("/\n{4,}/", "\n\n\n", $text) ?? $text;

        return trim($text);
    }

    private function safeFileName(string $name): string
    {
        $name = trim(
            basename(
                str_replace('\\', '/', $name)
            )
        );

        return $name === ''
            ? 'bestand'
            : mb_substr($name, 0, 180);
    }

    private function extensionFor(string $name): string
    {
        $lower = mb_strtolower($name);

        if (str_ends_with($lower, '.blade.php')) {
            return 'blade.php';
        }

        return mb_strtolower(
            pathinfo($name, PATHINFO_EXTENSION)
        );
    }

    private function deleteDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $items = scandir($directory) ?: [];

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;

            if (is_file($path) || is_link($path)) {
                @unlink($path);
            }
        }

        @rmdir($directory);
    }
}
