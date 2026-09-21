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

    public const MAX_CHARS_PER_FILE = 30000;

    public const MAX_TOTAL_CHARS = 70000;

    /**
     * @var array<int, string>
     */
    private const TEXT_EXTENSIONS = [
        'txt',
        'md',
        'markdown',
        'csv',
        'json',
        'xml',
        'html',
        'htm',
        'log',
        'php',
        'blade.php',
        'js',
        'mjs',
        'cjs',
        'ts',
        'jsx',
        'tsx',
        'css',
        'scss',
        'sass',
        'less',
        'sql',
        'yaml',
        'yml',
        'ini',
        'conf',
        'env',
        'py',
        'java',
        'kt',
        'kts',
        'c',
        'cc',
        'cpp',
        'h',
        'hpp',
        'cs',
        'go',
        'rs',
        'sh',
        'bash',
        'zsh',
        'ps1',
        'rb',
        'swift',
        'dart',
        'vue',
        'svelte',
    ];

    /**
     * @var array<int, string>
     */
    private const DOCUMENT_EXTENSIONS = [
        'pdf',
        'docx',
        'xlsx',
    ];

    /**
     * @param array<int, UploadedFile> $files
     *
     * @return array{
     *     context:string,
     *     files:array<int, array{
     *         name:string,
     *         extension:string,
     *         chars:int,
     *         truncated:bool
     *     }>
     * }
     */
    public function readMany(array $files): array
    {
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
                $file
            );

            $remaining =
                self::MAX_TOTAL_CHARS -
                $totalChars;

            if ($remaining <= 0) {
                break;
            }

            $text = mb_substr(
                $result['text'],
                0,
                $remaining
            );

            $wasTotalTruncated =
                mb_strlen($result['text']) >
                mb_strlen($text);

            $sections[] =
                "===== BESTAND: " .
                $result['name'] .
                " =====\n" .
                $text .
                "\n===== EINDE BESTAND =====";

            $chars =
                mb_strlen($text);

            $metadata[] = [
                'name' => $result['name'],
                'extension' => $result['extension'],
                'chars' => $chars,
                'truncated' =>
                    $result['truncated'] ||
                    $wasTotalTruncated,
            ];

            $totalChars +=
                $chars;
        }

        return [
            'context' => implode(
                "\n\n",
                $sections
            ),
            'files' => $metadata,
        ];
    }

    /**
     * @return array{
     *     name:string,
     *     extension:string,
     *     text:string,
     *     truncated:bool
     * }
     */
    public function read(
        UploadedFile $file
    ): array {
        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                'files' => 'Een van de bestanden kon niet correct worden geüpload.',
            ]);
        }

        $size = (int) (
            $file->getSize() ?? 0
        );

        if (
            $size < 1 ||
            $size > self::MAX_FILE_BYTES
        ) {
            throw ValidationException::withMessages([
                'files' => 'Elk bestand mag maximaal 10 MB groot zijn.',
            ]);
        }

        $name = $this->safeFileName(
            $file->getClientOriginalName()
        );

        $extension =
            $this->extensionFor(
                $name
            );

        $allowed = array_merge(
            self::TEXT_EXTENSIONS,
            self::DOCUMENT_EXTENSIONS
        );

        if (
            ! in_array(
                $extension,
                $allowed,
                true
            )
        ) {
            throw ValidationException::withMessages([
                'files' => sprintf(
                    'Bestandstype .%s wordt niet ondersteund.',
                    $extension !== ''
                        ? $extension
                        : 'onbekend'
                ),
            ]);
        }

        $absolutePath =
            $file->getRealPath();

        if (
            ! is_string($absolutePath) ||
            $absolutePath === '' ||
            ! is_file($absolutePath)
        ) {
            throw ValidationException::withMessages([
                'files' => 'Het tijdelijke uploadbestand kon niet worden gelezen.',
            ]);
        }

        try {
            $text = match ($extension) {
                'pdf' =>
                    $this->readPdf(
                        $absolutePath
                    ),

                'docx' =>
                    $this->readDocx(
                        $absolutePath
                    ),

                'xlsx' =>
                    $this->readXlsx(
                        $absolutePath
                    ),

                default =>
                    $this->readPlainText(
                        $absolutePath
                    ),
            };
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new RuntimeException(
                sprintf(
                    'Bestand "%s" kon niet worden uitgelezen.',
                    $name
                ),
                previous: $exception
            );
        }

        $text =
            $this->normalizeText(
                $text
            );

        if ($text === '') {
            throw ValidationException::withMessages([
                'files' => sprintf(
                    'In "%s" kon geen leesbare tekst worden gevonden.',
                    $name
                ),
            ]);
        }

        $truncated =
            mb_strlen($text) >
            self::MAX_CHARS_PER_FILE;

        if ($truncated) {
            $text =
                mb_substr(
                    $text,
                    0,
                    self::MAX_CHARS_PER_FILE
                ) .
                "\n\n[Bestand ingekort omdat het te lang is.]";
        }

        return [
            'name' => $name,
            'extension' => $extension,
            'text' => $text,
            'truncated' => $truncated,
        ];
    }

    private function readPlainText(
        string $absolutePath
    ): string {
        $contents =
            file_get_contents(
                $absolutePath
            );

        if ($contents === false) {
            throw new RuntimeException(
                'Het tekstbestand kon niet worden geopend.'
            );
        }

        /*
         * Binary/NUL-byte check om willekeurige binaire bestanden niet als
         * prompt naar het taalmodel te sturen.
         */
        if (str_contains(
            $contents,
            "\0"
        )) {
            throw ValidationException::withMessages([
                'files' => 'Dit bestand lijkt binair en kan niet als tekst worden gelezen.',
            ]);
        }

        return $contents;
    }

    private function readPdf(
        string $absolutePath
    ): string {
        if (! class_exists(PdfParser::class)) {
            throw ValidationException::withMessages([
                'files' => 'PDF-ondersteuning ontbreekt. Installeer eerst smalot/pdfparser met Composer.',
            ]);
        }

        $parser =
            new PdfParser();

        $pdf =
            $parser->parseFile(
                $absolutePath
            );

        return $pdf->getText();
    }

    private function readDocx(
        string $absolutePath
    ): string {
        $zip =
            new ZipArchive();

        $opened =
            $zip->open(
                $absolutePath
            );

        if ($opened !== true) {
            throw new RuntimeException(
                'Het DOCX-bestand kon niet worden geopend.'
            );
        }

        try {
            $xml =
                $zip->getFromName(
                    'word/document.xml'
                );

            if (! is_string($xml)) {
                throw new RuntimeException(
                    'DOCX bevat geen leesbaar document.xml.'
                );
            }

            $xml = preg_replace(
                '/<\/w:p>/i',
                "\n",
                $xml
            ) ?? $xml;

            $xml = preg_replace(
                '/<w:tab[^>]*\/>/i',
                "\t",
                $xml
            ) ?? $xml;

            $xml = preg_replace(
                '/<w:br[^>]*\/>/i',
                "\n",
                $xml
            ) ?? $xml;

            return html_entity_decode(
                strip_tags(
                    $xml
                ),
                ENT_QUOTES | ENT_XML1,
                'UTF-8'
            );
        } finally {
            $zip->close();
        }
    }

    private function readXlsx(
        string $absolutePath
    ): string {
        $zip =
            new ZipArchive();

        $opened =
            $zip->open(
                $absolutePath
            );

        if ($opened !== true) {
            throw new RuntimeException(
                'Het XLSX-bestand kon niet worden geopend.'
            );
        }

        try {
            $sharedStrings =
                $this->xlsxSharedStrings(
                    $zip
                );

            $sheets = [];

            for (
                $index = 0;
                $index < $zip->numFiles;
                $index++
            ) {
                $entry =
                    $zip->getNameIndex(
                        $index
                    );

                if (
                    ! is_string($entry) ||
                    ! preg_match(
                        '#^xl/worksheets/sheet\d+\.xml$#',
                        $entry
                    )
                ) {
                    continue;
                }

                $xml =
                    $zip->getFromIndex(
                        $index
                    );

                if (! is_string($xml)) {
                    continue;
                }

                $sheetText =
                    $this->xlsxSheetText(
                        $xml,
                        $sharedStrings
                    );

                if ($sheetText !== '') {
                    $sheets[] =
                        '--- ' .
                        basename(
                            $entry,
                            '.xml'
                        ) .
                        " ---\n" .
                        $sheetText;
                }
            }

            return implode(
                "\n\n",
                $sheets
            );
        } finally {
            $zip->close();
        }
    }

    /**
     * @return array<int, string>
     */
    private function xlsxSharedStrings(
        ZipArchive $zip
    ): array {
        $xml =
            $zip->getFromName(
                'xl/sharedStrings.xml'
            );

        if (! is_string($xml)) {
            return [];
        }

        $document =
            simplexml_load_string(
                $xml
            );

        if ($document === false) {
            return [];
        }

        $document->registerXPathNamespace(
            'm',
            'http://schemas.openxmlformats.org/spreadsheetml/2006/main'
        );

        $items =
            $document->xpath('//m:si') ?:
            [];

        $strings = [];

        foreach ($items as $item) {
            $parts =
                $item->xpath('.//m:t') ?:
                [];

            $value = '';

            foreach ($parts as $part) {
                $value .=
                    (string) $part;
            }

            $strings[] =
                $value;
        }

        return $strings;
    }

    /**
     * @param array<int, string> $sharedStrings
     */
    private function xlsxSheetText(
        string $xml,
        array $sharedStrings
    ): string {
        $document =
            simplexml_load_string(
                $xml
            );

        if ($document === false) {
            return '';
        }

        $document->registerXPathNamespace(
            'm',
            'http://schemas.openxmlformats.org/spreadsheetml/2006/main'
        );

        $rows =
            $document->xpath('//m:sheetData/m:row') ?:
            [];

        $lines = [];

        foreach ($rows as $row) {
            $cells =
                $row->xpath('./m:c') ?:
                [];

            $values = [];

            foreach ($cells as $cell) {
                $attributes =
                    $cell->attributes();

                $type =
                    (string) (
                        $attributes['t'] ??
                        ''
                    );

                $valueNodes =
                    $cell->xpath('./m:v') ?:
                    [];

                $value =
                    isset($valueNodes[0])
                        ? (string) $valueNodes[0]
                        : '';

                if (
                    $type === 's' &&
                    $value !== ''
                ) {
                    $sharedIndex =
                        (int) $value;

                    $value =
                        $sharedStrings[$sharedIndex] ??
                        $value;
                } elseif ($type === 'inlineStr') {
                    $inline =
                        $cell->xpath('.//m:t') ?:
                        [];

                    $value = '';

                    foreach ($inline as $part) {
                        $value .=
                            (string) $part;
                    }
                }

                $values[] =
                    trim($value);
            }

            if ($values !== []) {
                $lines[] =
                    implode(
                        "\t",
                        $values
                    );
            }
        }

        return implode(
            "\n",
            $lines
        );
    }

    private function normalizeText(
        string $text
    ): string {
        $text =
            str_replace(
                ["\r\n", "\r"],
                "\n",
                $text
            );

        $text = preg_replace(
            "/[ \t]+\n/",
            "\n",
            $text
        ) ?? $text;

        $text = preg_replace(
            "/\n{4,}/",
            "\n\n\n",
            $text
        ) ?? $text;

        return trim(
            $text
        );
    }

    private function safeFileName(
        string $name
    ): string {
        $name =
            trim(
                basename(
                    str_replace(
                        '\\',
                        '/',
                        $name
                    )
                )
            );

        if ($name === '') {
            return 'bestand';
        }

        return mb_substr(
            $name,
            0,
            180
        );
    }

    private function extensionFor(
        string $name
    ): string {
        $lower =
            mb_strtolower(
                $name
            );

        if (str_ends_with(
            $lower,
            '.blade.php'
        )) {
            return 'blade.php';
        }

        return mb_strtolower(
            pathinfo(
                $name,
                PATHINFO_EXTENSION
            )
        );
    }
}
