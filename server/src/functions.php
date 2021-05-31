<?php
// IMPRESSUM:
// (c) Martin Sonnberger 2021
// Dieses Projekt ist im Rahmen des 
// MultiMediaTechnology Bachelorstudiums
// an der FH Salzburg entstanden.
// Kontakt: msonnberger.mmt-b2020@fh-salzburg.ac.at

require "config.php";

if ( ! $DB_NAME ) die ('please create config.php, define $DB_NAME, $DSN, $DB_USER, $DB_PASS there. See config_sample.php');

try {
    $dbh = new PDO($DSN, $DB_USER, $DB_PASS);
    $dbh->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die ("Problem connecting to database $DB_NAME as $DB_USER: " . $e->getMessage() );
}


// zwei verschiedene Formatter
$day_short = new IntlDateFormatter('de_DE', IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
$day_long = new IntlDateFormatter('de_DE', IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
$day_db = new IntlDateFormatter('de_DE', IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);

// Formatierung nach http://www.icu-project.org/apiref/icu4c/classSimpleDateFormat.html#details
$day_short->setPattern('d. LLL');
$day_long->setPattern('EEEE d. LLLL yyyy');
$day_db->setPattern('yyyy-LL-dd');

?>
