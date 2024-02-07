<?php

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


?>