<?php
/**
 * PHP Project Toolbox NG - CLI Simplify
 *
 * @author Christoph Vieth <christoph@vieth.me>
 * @license The MIT License (MIT)
 * @version 0.2
 * @package lib-cli-simplify
 */

class ptbCliSimplify
{
    /**
     * @since 0.1
     */
    const newline = "\n";
    /**
     * @since 0.1
     */
    const tab = "\t";

    /**
     * Prints a message
     * @param $message
     * @since 0.2
     */
    public static function printMessage($message)
    {
        self::writeLine(' - ' . $message);
    }

    /**
     * Writes a given message wit attached newline to stdOut
     * @param $message
     * @since 0.2
     */
    public static function writeLine($message)
    {
        self::writeStdOut($message . self::newline);
    }

    /**
     * Writes a given message to stdOut
     * @param $message
     * @since 0.2
     */
    public static function writeStdOut($message)
    {
        $stdout = fopen('php://stdout', 'w');
        fwrite($stdout, $message);
    }

    /**
     * Prints an error
     * @param $message
     * @since 0.2
     */
    public static function printError($message)
    {
        self::writeLine(' [ERROR] ' . $message);
    }

    /**
     * Prints a notice
     * @param $message
     * @since 0.2
     */
    public static function printNotice($message)
    {
        self::writeLine(' * ' . $message);
    }

    /**
     * Prints a banner
     * @param $message
     * @since 0.2
     */
    public static function printBanner($message)
    {
        $messageLength = strlen($message);
        $bannerBorderLine = '';
        for ($i = 0; $i < $messageLength; $i++) {
            $bannerBorderLine .= '-';
        }
        $bannerBorderLine = '+-' . $bannerBorderLine . '-+';
        $bannerTextLine = '| ' . $message . ' |';

        self::writeLine($bannerBorderLine);
        self::writeLine($bannerTextLine);
        self::writeLine($bannerBorderLine);

    }
} 