<?php
//This is an example and test file how different scripts in different places can encode and decode ids
// to use in another file, simply copy the two require and the one use statements and set the paths
// probably advisable to keep the exception throw in case cannot read value
// The library this uses is at https://github.com/ivanakimov/hashids.php and its meant for things just like this
// using the same salt we can encode and decode ids without having to do a lookup in the database
// the ids are similar to youtubes ids.
// its a really nice library, maintained by a lot of people and aviable in every single language.
// I had no idea it existed until tonight but will start to use it in other projects
//   - Will W. Aug 8,2017
$real =   realpath( dirname( __FILE__ ) );
require_once $real.'/../vendor/autoload.php';
use Hashids\Hashids;

#I made a config file that is meant to be shared
require_once $real.'/../config/shared_config.php';

$salt = getenv('HASH_ID_SALT');
$min_length = intval( getenv('HASH_ID_MIN_LENGTH') );

if ($min_length == 0 || $salt == "") {
    throw new UnexpectedValueException("Hash ID has empty values, check include path for shared_config.php");
}

# see https://github.com/ivanakimov/hashids.php
$hashids = new Hashids($salt,$min_length);

#when encoding the same options in the constructor (salt and padding ) will always generate the same numbers
#cannot encode negative numbers , only 0 or positive
# only encode integers > 0
$what = $hashids->encode(1);
print "1 is encoded to ". $what . "\n";


#When decoding, output is always an array of numbers (even if you encode only one number)
$that = $hashids->decode($what);
print "$what is decoded to " . $that[0] . "\n";


$what = $hashids->encode(2);
print "2 is encoded to ". $what . "\n";
$that = $hashids->decode($what);
print "$what is decoded to " . $that[0] . "\n";


$what = $hashids->encode(100321);
print "100321 is encoded to ". $what . "\n";
$that = $hashids->decode($what);
print "$what is decoded to " . $that[0] . "\n";


$what = $hashids->encode(9009321);
print "9009321 is encoded to ". $what . "\n";
$that = $hashids->decode($what);
print "$what is decoded to " . $that[0] . "\n";

$what = $hashids->encode(89009321);
print "89009321 is encoded to ". $what . "\n";
$that = $hashids->decode($what);
print "$what is decoded to " . $that[0] . "\n";

