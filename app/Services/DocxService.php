<?php

namespace App\Services;

use Illuminate\Http\Response;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;

class DocxService
{
    /**
     * Render a Blade view to HTML, convert to DOCX, and return a download response.
     *
     * PhpWord's HTML parser supports tables, headings, paragraphs, bold/italic.
     * It does NOT support CSS classes or complex inline styles — those are stripped
     * before parsing so the document is clean in Word.
     */
    public static function download(
        string $viewName,
        array  $data,
        string $filename,
        string $orientation = 'portrait'
    ): Response {
        $html = view($viewName, $data)->render();

        // Strip style blocks and script blocks — PhpWord ignores CSS classes anyway
        $html = preg_replace('/<style[^>]*>.*?<\/style>/si', '', $html);
        $html = preg_replace('/<script[^>]*>.*?<\/script>/si', '', $html);

        // Strip inline style attributes — prevents PhpWord parser errors on complex CSS
        $html = preg_replace('/\s+style="[^"]*"/i', '', $html);

        // Strip class attributes
        $html = preg_replace('/\s+class="[^"]*"/i', '', $html);

        // Remove <img> tags (logos etc.) — PhpWord needs file paths, not URLs
        $html = preg_replace('/<img[^>]*>/i', '', $html);

        // Collapse excessive whitespace from removed elements
        $html = preg_replace('/\n{3,}/', "\n\n", $html);

        // PhpWord::Html::addHtml() uses DOMDocument::loadXML() internally, which
        // requires strict XHTML — HTML5 entities (&nbsp; etc.) and unclosed tags
        // (<br>, <input>) cause a fatal parse error. We sanitize by round-tripping
        // through DOMDocument::loadHTML() (lenient) → saveXML() (strict XML output).
        $html = self::htmlToXhtml($html);

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection([
            'orientation'  => $orientation === 'landscape' ? 'landscape' : 'portrait',
            'marginLeft'   => 1080,
            'marginRight'  => 1080,
            'marginTop'    => 1080,
            'marginBottom' => 1080,
        ]);

        Html::addHtml($section, $html, false, false);

        // Save to a real temp file — avoids ob_start() capturing stray PHP output
        // (notices, debug output) that would corrupt the ZIP structure of DOCX.
        $tmp = tempnam(sys_get_temp_dir(), 'phpdocx_');

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tmp);

        $content = file_get_contents($tmp);
        @unlink($tmp);

        return response($content, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.docx"',
            'Content-Length'      => strlen($content),
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ]);
    }

    private static function htmlToXhtml(string $html): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');

        libxml_use_internal_errors(true);
        // The charset meta tag tells DOMDocument to treat input as UTF-8
        $dom->loadHTML(
            '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body>'
            . $html
            . '</body></html>',
            LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors(false);

        // Extract only the <body> children as XHTML fragments
        $body = $dom->getElementsByTagName('body')->item(0);
        if (! $body) {
            return $html;
        }

        $xhtml = '';
        foreach ($body->childNodes as $child) {
            $xhtml .= $dom->saveXML($child);
        }

        return $xhtml ?: $html;
    }
}
