<?php

function questionHtmlAllowedTags()
{
    return ['pre', 'code', 'i', 'b', 'p', 'br', 'ul', 'ol', 'li', 'strong', 'em'];
}

function escapeHtmlForAnswers($html, $deleteTags = [])
{
    // $html = str_replace('<pre>', '<b>', $html);
    // $html = str_replace('</pre>', '</b>', $html);
    // $html = str_replace('<code>', '<b>', $html);
    // $html = str_replace('</code>', '</b>', $html);

    // print_r("<pre>");
    // print_r($html);
    // print_r("</pre>");
    // exit;
    // return $html;

    return escapeHtmlExceptTags($html, $deleteTags, ['pre', 'code','i', 'b']);
}


function escapeHtmlExceptTags($html, $deleteTags = [], $allowedTags = null)
{
    $allowedTags = $allowedTags ?? questionHtmlAllowedTags();

    foreach ($deleteTags as $tag) {
        $html = str_replace('<' . $tag . '>', '', $html);
        $html = str_replace('</' . $tag . '>', '', $html);
    }

    // Escape all HTML characters in the string
    $escapedHtml = htmlspecialchars($html, ENT_QUOTES, 'UTF-8');

    // For each allowed tag, replace the escaped version back to HTML
    foreach ($allowedTags as $tag) {
        $escapedHtml = preg_replace(
            '/&lt;(' . preg_quote($tag, '/') . ')\s*\/?&gt;/i',
            '<$1>',
            $escapedHtml
        );
        $escapedHtml = preg_replace(
            '/&lt;\/(' . preg_quote($tag, '/') . ')\s*&gt;/i',
            '</$1>',
            $escapedHtml
        );
    }

    if (in_array('br', $allowedTags, true)) {
        $escapedHtml = preg_replace('/&lt;br\s*\/?&gt;/i', '<br>', $escapedHtml);
    }

    return $escapedHtml;
}


function validateAllowedTags($html, $allowedTags = null)
{
    $allowedTags = $allowedTags ?? questionHtmlAllowedTags();
    $errors = [];

    foreach ($allowedTags as $tag) {
        if (in_array($tag, ['br'], true)) {
            continue;
        }

        $open  = preg_match_all('/<' . $tag . '\b[^>]*>/i', $html, $unused);
        $close = preg_match_all('/<\/' . $tag . '>/i', $html, $unused);

        if ($open !== $close) {
            $errors[] = "Tag <$tag> is unbalanced (found $open open vs $close close).";
        }
    }

    return $errors;
}


?>
