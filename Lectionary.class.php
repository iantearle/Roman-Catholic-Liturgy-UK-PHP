<?php
error_reporting(E_ALL);
/*
 * This class uses date calculations to generate the lectionary dates for a
 * given year.
 */
if (file_exists('lectionary.xml')) {
    $xml = simplexml_load_string(file_get_contents('lectionary.xml'));

    // print_r($xml);
} else {
    exit('Failed to open lectionary.xml.');
}

class Lectionary
{

    public $year;
    public $calendar;
    public $lectionary;

    /*
     * Class initialization
     */
    public function __construct($year = '')
    {
        global $_SERVER;
        global $_SESSION;
        global $_REQUEST;
        global $_POST;
        global $_GET;
        $this->set_year($year);
    }

    /*
     * Sets the year or the current year if nothing is passed
     */
    public function set_year($year = ''): void
    {
        if ($year === '') {
            $this->year = gmstrftime("%Y", time());
        } else {
            $this->year = $year;
        }
        $this->load_lectionary();
        $this->generate_calendar();
    }

    /*
     * Returns the year
     */
    public function get_year()
    {
        return ($this->year);
    }

    public function load_lectionary(): bool
    {
        if (is_file('lectionary.xml')) {
            $this->lectionary = simplexml_load_string(file_get_contents('lectionary.xml'));
            return (true);
        }

        $this->lectionary = false;
        return (false);
    }

    public function get_cycle($unixdate): string
    {
        $y = array('A', 'B', 'C', 'A');
        $year = gmstrftime("%Y", $unixdate);
        $mod = (($year - 1) % 3);
        if ($unixdate < $this->get_advent_sunday()) {
            return ($y[$mod]);
        }

        return ($y[$mod + 1]);
    }

    /*
     * Get the name for a given number
     */
    public function get_number_name($number): string
    {
        $name = array(
            1 => 'First',
            2 => 'Second',
            3 => 'Third',
            4 => 'Fourth',
            5 => 'Fifth',
            6 => 'Sixth',
            7 => 'Seventh'
        );
        return $name[$number] ?? '';
    }

    /*
     * Get the english suffix for a given number
     */
    public function get_suffix(int $dayOfTheMonth): string
    {
        $suffix = array(
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            21 => 'st',
            22 => 'nd',
            23 => 'rd',
            31 => 'st'
        );
        return $suffix[$dayOfTheMonth] ?? 'th';
    }

    /*
     * Get the long format date for a given unix timestamp
     */
    public function get_long_date($timestamp): string
    {
        $s = $this->get_suffix((int)gmstrftime("%d", $timestamp));
        //return gmstrftime("%A, %B %d<sup>$s</sup>, %Y, %H:%M", $timestamp );
        return gmstrftime("%A, %B %d$s, %Y", $timestamp);
    }

    /*
     * Get the timestamp for the first sunday of the year
     */
    public function get_first_sunday(): int
    {
        $dow = (int)gmstrftime("%w", gmmktime(11, 0, 0, 1, 1, $this->year));
        if ($dow === 0) {
            $dow = 7;
        }
        $delta = 7 - $dow;
        return (int)gmmktime(11, 0, 0, 1, 1 + $delta, $this->year);
    }

    /*
     * Get the timestamp for the first sunday after Epiphany (Jan 6)
     */
    public function get_sunday_after_epiphany(): int
    {
        $dow = (int)gmstrftime("%w", gmmktime(11, 0, 0, 1, 6, $this->year));
        if ($dow === 0) {
            $delta = 7;
        } else {
            $delta = 7 - $dow;
        }
        return (int)gmmktime(11, 0, 0, 1, 6 + $delta, $this->year);
    }

    /*
     * Get the timestamp for easter in a given year
     */
    public function getEasterSunday(): int
    {
        return (int)gmmktime(11, 0, 0, 3, 21 + easter_days($this->year), $this->year);
    }

    /*
     * Get the timestamp for the first Sunday in advent
     */
    public function get_advent_sunday(): int
    {
        $dow = (int)gmstrftime("%w", gmmktime(11, 0, 0, 12, 25, $this->year));
        if ($dow === 0) {
            $dow = 7;
        }
        $delta = -28 + (7 - $dow);
        return (int)gmmktime(11, 0, 0, 12, 25 + $delta, $this->year);
    }

    /*
     * Calculate all the calendar dates
     */
    public function generate_calendar(): void
    {
        $day = 86400; # Number of seconds in a day

        ##
        ## These are the fixed days in the year
        ##

        if ($this->get_first_sunday() === gmmktime(11, 0, 0, 1, 1, $this->year)) {
            $this->calendar[$this->get_first_sunday()] = '8'; # First Sunday after Christmas Day
        } else {
            $this->calendar[$this->get_first_sunday()] = '9'; # Second Sunday after Christmas Day
        }
        $this->calendar[gmmktime(11, 0, 0, 1, 6, $this->year)] = '10'; # Epiphany of the Lord
        $this->calendar[$this->get_sunday_after_epiphany()] = '11'; # Baptism of the Lord
        $this->calendar[$this->getEasterSunday() - (49 * $day)] = '12'; # Transfiguration Sunday
        $this->calendar[$this->getEasterSunday() - (46 * $day)] = '13'; # Ash Wednesday
        $this->calendar[$this->getEasterSunday() - (42 * $day)] = '14'; # First Sunday in Lent
        $this->calendar[$this->getEasterSunday() - (35 * $day)] = '15'; # Second Sunday in Lent
        $this->calendar[$this->getEasterSunday() - (28 * $day)] = '16'; # Third Sunday in Lent
        $this->calendar[$this->getEasterSunday() - (21 * $day)] = '17'; # Fourth Sunday in Lent
        $this->calendar[$this->getEasterSunday() - (14 * $day)] = '18'; # Fifth Sunday in Lent
        $this->calendar[$this->getEasterSunday() - (7 * $day)] = '19'; # Palm/Passion Sunday
        $this->calendar[$this->getEasterSunday() - (6 * $day)] = '20'; # Monday of Holy Week
        $this->calendar[$this->getEasterSunday() - (5 * $day)] = '21'; # Tuesday of Holy Week
        $this->calendar[$this->getEasterSunday() - (4 * $day)] = '22'; # Wednesday of Holy Week
        $this->calendar[$this->getEasterSunday() - (3 * $day)] = '23'; # Maundy Thursday
        $this->calendar[$this->getEasterSunday() - (2 * $day)] = '24'; # Good Friday
        $this->calendar[$this->getEasterSunday() - (1 * $day)] = '25'; # Holy Saturday
        $this->calendar[$this->getEasterSunday()] = '26'; # Easter Sunday
        $this->calendar[$this->getEasterSunday() + (8 * 60 * 60)] = '27'; # Easter Evening
        $this->calendar[$this->getEasterSunday() + (7 * $day)] = '28'; # Second Sunday of Easter
        $this->calendar[$this->getEasterSunday() + (14 * $day)] = '29'; # Third Sunday of Easter
        $this->calendar[$this->getEasterSunday() + (21 * $day)] = '30'; # Fourth Sunday of Easter
        $this->calendar[$this->getEasterSunday() + (28 * $day)] = '31'; # Fifth Sunday of Easter
        $this->calendar[$this->getEasterSunday() + (35 * $day)] = '32'; # Sixth Sunday of Easter
        $this->calendar[$this->getEasterSunday() + (39 * $day)] = '33'; # Ascension Sunday
        $this->calendar[$this->getEasterSunday() + (42 * $day)] = '34'; # Seventh Sunday of Easter
        $this->calendar[$this->getEasterSunday() + (49 * $day)] = '35'; # Day of Pentecost
        $this->calendar[$this->getEasterSunday() + (56 * $day)] = '36'; # Trinity Sunday
        $this->calendar[gmmktime(8, 0, 0, 11, 1, $this->year)] = '37'; # All Saint's Day
        $this->calendar[$this->get_advent_sunday() - (7 * $day)] = '38'; # Christ the King Sunday
        $this->calendar[$this->get_advent_sunday()] = '1';  # First Sunday in Advent
        $this->calendar[$this->get_advent_sunday() + (7 * $day)] = '2';  # Second Sunday in Advent
        $this->calendar[$this->get_advent_sunday() + (14 * $day)] = '3';  # Third Sunday in Advent
        $this->calendar[$this->get_advent_sunday() + (21 * $day)] = '4';  # Fourth Sunday in Advent
        $this->calendar[gmmktime(21, 0, 0, 12, 24, $this->year)] = '5'; # Christmas Eve
        $this->calendar[gmmktime(8, 0, 0, 12, 25, $this->year)] = '6'; # Christmas Day (Sunrise)
        $this->calendar[gmmktime(11, 0, 0, 12, 25, $this->year)] = '7'; # Christmas Day
        if (0 !== (int)gmstrftime("%w", gmmktime(11, 0, 0, 12, 25, $this->year))) {
            $this->calendar[$this->get_advent_sunday() + (28 * $day)] = '8';  # First Sunday after Christmas Day
        }

        ##
        ## These are the variable days in the year
        ##

        # Variable Sundays after Epiphany counting forward from 2nd Sunday in Ordinary Time
        $index = 39;
        for ($i = $this->get_sunday_after_epiphany() + (7 * $day); $i < $this->getEasterSunday() - (49 * $day); $i = $i + (7 * $day)) {
            if (empty($this->calendar[$i])) {
                $this->calendar[$i] = $index; # Sunday's after Epiphany
                $index++;
            }
        }

        # Variable Sundays after Pentecost counting backwards from 33rd sunday in Ordinary Time
        $index = 70;
        for ($i = $this->get_advent_sunday() - (14 * $day); $i > $this->getEasterSunday() + (56 * $day); $i = $i - (7 * $day)) {
            if (empty($this->calendar[$i])) {
                $this->calendar[$i] = $index; # Sunday's after Pentecost
                $index--;
            }
        }

        ksort($this->calendar);
    }

    /*
     * Get the calendar array
     */
    public function get_calendar()
    {
        return ($this->calendar);
    }

    public function get_calendar_day($unixdate): array
    {
        $year = gmstrftime('%Y', $unixdate);
        $month = gmstrftime('%m', $unixdate);
        $day = gmstrftime('%d', $unixdate);
        $day_start = gmmktime(0, 0, 0, $month, $day, $year);
        $day_end = gmmktime(23, 59, 59, $month, $day, $year);
        $result = array();
        foreach ($this->calendar as $key => $val) {
            if ($key >= $day_start && $key <= $day_end) {
                $result[$key] = $val;
            }
        }
        return ($result);
    }

    public function get_title($unixdate, $index): string
    {
        $result = '';
        $xpath = "//lectionary/year[@name='" . $this->get_cycle($unixdate) . "']/day[@name='" . $index . "']/title";
        foreach ($this->lectionary->xpath($xpath) as $text) {
            $result .= (string)$text;
        }
        return ($result);
    }

    public function get_scripture($unixdate, $index, string $lesson): string
    {
        $result = '';
        $xpath = "//lectionary/year[@name='" . $this->get_cycle($unixdate) . "']/day[@name='" . $index . "']/scripture/$lesson";
        foreach ($this->lectionary->xpath($xpath) as $text) {
            $result .= (string)$text;
        }
        return ($result);
    }

}
