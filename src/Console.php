<?php

class Console
{
    public function GetArg($optname, $default = null)
    {
        global $argv;

        if (substr($optname, 0, 1) == '-') {
            // --opt=val
            $optname = '-' . $optname . '=';
            $optlen = strlen($optname);
            foreach ($argv as $arg) {
                if (substr($arg, 0, $optlen) == $optname) {
                    return substr($arg, $optlen);
                }
            }
        } else {
            // -opt val
            $pos = array_search((IS_WINDOWS ? '/' : '-').$optname, $argv);
            if ($pos !== false && isset($argv[$pos + 1]) && substr($argv[$pos + 1], 0, 1) != (IS_WINDOWS ? '/' : '-')) {
                return $argv[$pos + 1];
            }
        }
        return $default;
    }

    public function Clear()
    {
        if (IS_WINDOWS) {
            // since calling 'cls' doesn't work, we use the following hack...
            for ($l=0; $l<$this->GetHeight(); $l++) {
                $this->WriteLine(str_pad('', $this->GetWidth(), ' '));
            }
        } else {
            passthru('clear');
        }
    }

    public function HasArg($option)
    {
        global $argv;

        if (!is_array($option)) {
            return $this->HasArg(array($option));
        }

        foreach ($option as $opt) {
            if (in_array((IS_WINDOWS ? '/' : '-') . $opt, $argv)) {
                return true;
            }
        }

        return false;
    }

    public function GetWidth()
    {
        static $cache = null;
        if (is_null($cache)) {
            if (IS_WINDOWS) {
                $lines = array();
                exec('mode', $lines);
                foreach ($lines as $line) {
                    if (strpos($line, 'Columns') !== false) {
                        $cache = explode(':', $line);
                        $cache = (int)trim($cache[1]);
                        break;
                    }
                }
            } else {
                $cache = (int)exec('tput cols');
            }
            $cache = max($cache, 20);
        }
        return $cache;
    }

    public function GetHeight()
    {
        static $cache = null;
        if (is_null($cache)) {
            if (IS_WINDOWS) {
                $lines = array();
                exec('mode', $lines);
                foreach ($lines as $line) {
                    if (strpos($line, 'Lines') !== false) {
                        $cache = explode(':', $line);
                        $cache = (int)trim($cache[1]);
                        break;
                    }
                }
            } else {
                $cache = max(4, (int)exec('tput lines'));
            }
        }
        return $cache;
    }


    protected $parts = 0;
    
    public function ResetParts()
    {
        $this->parts = 0;
    }
    
    public function WriteLine($message = '')
    {
        $this->ResetParts();
        echo $message.PHP_EOL;
    }
    
    public function WritePart($parts)
    {
        // find the last line
        $part = explode(PHP_EOL, $parts);
        $part = array_pop($part);

        // count visible chars
        $this->parts += $this->StrLen($part);
        echo $parts;
    }
    
    /**
     * Strip ansi escape characters from string and return length.
     * @param string $str
     * @return int
     */
    public function StrLen($str)
    {
        $str = preg_replace('/\033\[[0-9;]*m/', '', $str);
        return strlen($str);
    }
    
    public function CountParts()
    {
        return $this->parts;
    }
    
    const C_BLACK      = '0;30'
    ,   C_DARK_GRAY    = '1;30'
    ,   C_RED          = '0;31'
    ,   C_LIGHT_RED    = '1;31'
    ,   C_GREEN        = '0;32'
    ,   C_LIGHT_GREEN  = '1;32'
    ,   C_BROWN        = '0;33'
    ,   C_YELLOW       = '1;33'
    ,   C_BLUE         = '0;34'
    ,   C_LIGHT_BLUE   = '1;34'
    ,   C_PURPLE       = '0;35'
    ,   C_LIGHT_PURPLE = '1;35'
    ,   C_CYAN         = '0;36'
    ,   C_LIGHT_CYAN   = '1;36'
    ,   C_LIGHT_GRAY   = '0;37'
    ,   C_WHITE        = '1;37'
    ,   C_RESET        = '0'
    ;

    public function Colorize($message, $color)
    {
        $colorize = !(defined('FORCE_PLAIN') && FORCE_PLAIN) && (!IS_WINDOWS || (defined('FORCE_COLOR') && FORCE_COLOR));
        $color = "\033[" . $color . 'm';
        $reset = "\033[" . self::C_RESET . 'm';
        return !$colorize ? $message : $color . $message . $reset;
    }
    
    public function OverwriteLine($message)
    {
        echo "\r" . substr(str_pad($message, cli_width(), ' ', STR_PAD_RIGHT), 0, cli_width());
    }

    public function ReadLine()
    {
        return fgets(STDIN);
    }
}