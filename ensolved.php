#!/usr/bin/php
<?php

declare(strict_types=1);

// Class here is just to not use arrays
class Settings
{
    static public string $letters = '';
    static public string $wordlist    = './en.dic';

    static public function validate(): bool
    {
       $errors = [];
       $count = strlen(self::$letters);

       if ($count !== 9) {
           $errors[] = "ERR: There is must be exactly 9 letters";
       }

       if (!ctype_alpha(self::$letters)) {
           $errors[] = "ERR: Letters must consist only of letters";
       }

       if (!file_exists(self::$wordlist)) {
           $errors[] = "ERR: Dictionary file is not found (try to download it with -g flag)";
       }

       foreach ($errors as $error) {
           echo $error . PHP_EOL;
       }

       return count($errors) === 0;
    }
}

class Wordlist
{
    static private $content = null;

    static public function getContent(): array
    {
        if (self::$content === null) {
            self::$content = explode("\r\n", file_get_contents(Settings::$wordlist));
        }

        return self::$content;
    }

    static public function isValidWord(string $word): bool
    {
        return in_array($word, self::getContent());
    }
}

function downloadWordlist(string $outputPath = null): void
{
    $outputPath = $outputPath ?? Settings::$wordlist;
    echo "Downloading wordlist to $outputPath..." . PHP_EOL;
    file_put_contents(
        $outputPath,
        file_get_contents('http://enspelled.com/dictionaries/en/en.dic')
    );

    echo "Done. Saved to $outputPath" . PHP_EOL;
}

function version(): void
{
    echo "Ensolved v2.0.0 - Find solutions for enspelled" . PHP_EOL;
}

function usage(): void
{
    version();
    echo "Usage: php ./ensolved.php -l IYDDRRALT [-d ./en.dic -h -v]" . PHP_EOL;
    echo "Flags:" . PHP_EOL;
    echo "-l --letters   Which letters to use to find solutions" . PHP_EOL;
    echo "-w --wordlist  Sorted dictionary to use with words delimitered with \\r\\n" . PHP_EOL;
    echo "-g --get       Download the wordlist. By default it will downlaod to `./en.dic`," . PHP_EOL;
    echo "               but you can change destination via passing an arg here or to -d flag " . PHP_EOL;
    echo "-h --help      Show this usage" . PHP_EOL;
    echo "-v --version   Show version" . PHP_EOL;
}

function handleArgs(): bool
{
    global $argv;
    $argc = count($argv);

    if ($argc === 1) {
        usage();
        return false;
    }

    for ($i = 0; $i < $argc; $i++) {
        if (in_array($argv[$i], ['-l', '--letters'])) {
            if ($i >= $argc - 1) {
                fprintf(STDERR, "ERR: argument " . $argv[$i] . " must have value");
                return false;
            }

            $i++;
            Settings::$letters = strtolower($argv[$i]);
        }

        else if (in_array($argv[$i], ['-w', '--wordlist'])) {
            if ($i >= $argc - 1) {
                fprintf(STDERR, "ERR: argument " . $argv[$i] . " must have value");
                return false;
            }

            $i++;
            Settings::$wordlist = $argv[$i];
        }

        else if (in_array($argv[$i], ['-g', '--get'])) {
            if ($i >= $argc - 1) {
                downloadWordlist();
                return false;
            }

            $i++;
            downloadWordlist($argv[$i]);
            return false;
        }

        else if (in_array($argv[$i], ['-h', '--help'])) {
            usage();
            return false;
        }

        else if (in_array($argv[$i], ['-v', '--version'])) {
            version();
            return false;
        }
    }

    return true;
}

function isTermSupportColors(): bool
{
    if (getenv('NO_COLOR') !== false) {
        return false;
    }

    if (!function_exists('stream_isatty') || !stream_isatty(STDOUT)) {
        return false;
    }

    if (DIRECTORY_SEPARATOR === '\\') {
        return getenv('ANSICON') !== false
            || getenv('WT_SESIION') !== false
            || getenv('ConEmuANSI') === 'ON'
            || getenv('TERM_PROGRAM') === 'vscode';
    }

    return true;
}

function scoreWord(string $word, int $lettersI = 0) {
    $letters  = str_split(Settings::$letters);
    $wordArr = str_split($word);

    $score    = 0;
    $wordI    = 0;
    $wordLen  = strlen($word);

    while ($wordI < $wordLen && $lettersI < 9) {
        if ($letters[$lettersI] === $wordArr[$wordI]) {

            if (isTermSupportColors()) {
                $wordArr[$wordI] = "\033[31m" . $wordArr[$wordI] . "\033[0m";
            } else {
                $wordArr[$wordI] = strtoupper($wordArr[$wordI]);
            }

            $score++;
            $lettersI++;
        }

        $wordI++;
    }

    return [ $score, implode('', $wordArr) ];
}

function main()
{
    if (!(handleArgs() && Settings::validate())) {
        return 1;
    }

    $lettersI = 0;
    $attempt = 0;

    while ($lettersI < 8 && $attempt < 3) {
        $topScore = 0;
        $bestWord = '';

        foreach (Wordlist::getContent() as $word) {
            [$newScore, $newWord] = scoreWord($word, $lettersI);

            if ($newScore > $topScore) {
                $topScore = $newScore;
                $bestWord = $newWord;
            }
        }

        echo $bestWord . PHP_EOL;
        $lettersI += $topScore;
        $attempt++;
    }

    return 0;
}

return main();
