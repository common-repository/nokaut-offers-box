<?php


namespace NokautOffersBox\Logger;

use Psr\Log\LoggerInterface;

class PrintHtml implements LoggerInterface
{
    public function emergency($message, array $context = array())
    {
        echo "<p><strong>" . __FUNCTION__ . "</strong>: " . strip_tags($message) . "</p>";
    }

    public function alert($message, array $context = array())
    {
        echo "<p><strong>" . __FUNCTION__ . "</strong>: " . strip_tags($message) . "</p>";
    }

    public function critical($message, array $context = array())
    {
        echo "<p><strong>" . __FUNCTION__ . "</strong>: " . strip_tags($message) . "</p>";
    }

    public function error($message, array $context = array())
    {
        echo "<p><strong>" . __FUNCTION__ . "</strong>: " . strip_tags($message) . "</p>";
    }

    public function warning($message, array $context = array())
    {
        echo "<p><strong>" . __FUNCTION__ . "</strong>: " . strip_tags($message) . "</p>";
    }

    public function notice($message, array $context = array())
    {
        echo "<p><strong>" . __FUNCTION__ . "</strong>: " . strip_tags($message) . "</p>";
    }

    public function info($message, array $context = array())
    {
        echo "<p><strong>" . __FUNCTION__ . "</strong>: " . strip_tags($message) . "</p>";
    }

    public function debug($message, array $context = array())
    {
        echo "<p><strong>" . __FUNCTION__ . "</strong>: " . strip_tags($message) . "</p>";
    }

    public function log($level, $message, array $context = array())
    {
        echo "<p><strong>" . __FUNCTION__ . "</strong>: " . strip_tags($message) . "</p>";
    }
} 