<?php

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


function escapeHtmlExceptTags($html, $deleteTags = [], $allowedTags = ['pre', 'code', 'i', 'b'])
{
    foreach ($deleteTags as $tag) {
        $html = str_replace('<' . $tag . '>', '', $html);
        $html = str_replace('</' . $tag . '>', '', $html);
    }

    // Escape all HTML characters in the string
    $escapedHtml = htmlspecialchars($html, ENT_QUOTES, 'UTF-8');

    // For each allowed tag, replace the escaped version back to HTML
    foreach ($allowedTags as $tag) {
        $escapedStartTag = '&lt;' . $tag . '&gt;';
        $escapedEndTag = '&lt;/' . $tag . '&gt;';
        $escapedHtml = str_replace($escapedStartTag, '<' . $tag . '>', $escapedHtml);
        $escapedHtml = str_replace($escapedEndTag, '</' . $tag . '>', $escapedHtml);
    }

    return $escapedHtml;
}


function validateAllowedTags($html, $allowedTags = ['pre', 'code', 'i', 'b'])
{
    $errors = [];

    foreach ($allowedTags as $tag) {
        $open  = preg_match_all('/<' . $tag . '\b[^>]*>/i', $html, $unused);
        $close = preg_match_all('/<\/' . $tag . '>/i', $html, $unused);

        if ($open !== $close) {
            $errors[] = "Tag <$tag> is unbalanced (found $open open vs $close close).";
        }
    }

    return $errors;
}


?>