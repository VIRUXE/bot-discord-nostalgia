<?php

class BugsCommand
{
    public static function fetchBugs()
    {
        $jsonUrl = 'http://sv.scavengenostalgia.fun/bugs.json';
        $jsonContent = file_get_contents($jsonUrl);
        $bugs = json_decode($jsonContent, true);

        return $bugs;
    }

    public static function displayBugs($bugs)
    {
        $output = "```\n";
        $output .= "Bug Reports:\n";
        $output .= "============\n";

        foreach ($bugs as $bug) {
            $name = $bug['name'];
            $reason = $bug['reason'];
            $date = date('Y-m-d H:i:s', $bug['date']);

            $output .= "Name: {$name}\n";
            $output .= "Reason: {$reason}\n";
            $output .= "Date: {$date}\n";
            $output .= "------------\n";
        }

        $output .= "```";
        return $output;
    }
}
