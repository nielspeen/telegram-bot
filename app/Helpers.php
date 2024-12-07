<?php

if (!function_exists('escape_markdown_v2')) {
    /**
     * Escape special characters for Telegram's MarkdownV2 format
     * @param string $text Text to escape
     * @return string Escaped text
     */
    function escape_markdown_v2(string $text): string
    {
        $specialChars = [
            '_', '*', '[', ']', '(', ')', '~', '`', '>', '#',
            '+', '-', '=', '|', '{', '}', '.', '!'
        ];

        foreach ($specialChars as $char) {
            $text = str_replace($char, '\\' . $char, $text);
        }

        return $text;
    }
}
