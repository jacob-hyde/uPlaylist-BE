<?php

use App\Services\Activity\Recorder;
use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use Faker\Provider\Uuid;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Activitylog\ActivityLogStatus;
use Carbon\Carbon;

/**
 * Respond with a server error.
 *
 * @return JsonResponse - The response
 */
function respondServerError(): JsonResponse
{
    return response()->json([
        'error' => 'SERVER_ERROR',
        'message' => 'Internal server error',
    ], 500);
}

/**
 * Convert dollars to cents.
 *
 * @param float $amount - Amount in dollars
 * @return int - The amount in cents
 */
function convertDollarsToCents(float $amount): int
{
    return intval($amount * 100);
}

function convertCentsToDollars(int $amount): string
{
    return number_format(($amount / 100), 2, '.', '');
}

function amountWithFee($amount, $fee)
{
    return $amount + ($amount * $fee);
}
/**
 * Saves a uploaded file to s3 in a usernames folder
 * Creates a unique file name.
 *
 * @param UploadedFile $file - The uploaded file
 * @param string $path - The path
 * @param string $disk - The s3 disk to use
 * @return string|null - The file path
 */
function saveUploadedFile(UploadedFile $file, string $path = null, string $disk = 's3', bool $stream = false): ?string
{
    $file_name = Uuid::uuid() . '.' . preg_replace("/.*\./", '', $file->getClientOriginalName());
    if ($stream) {
        return saveFileStream($file, $file_name, $path, $disk);
    }

    return saveFile($file, $file_name, $path, $disk);
}

/**
 * Saves a file from path to s3 in a usernames folder
 * Creates a unique file name.
 *
 * @param string $file - The file path
 * @param string $path - The path
 * @param string $disk - The s3 disk to use
 * @return string|null - The file path
 */
function saveFileFromPath(string $file, string $path = null, string $disk = 's3'): ?string
{
    $file_name = Uuid::uuid() . '.' . preg_replace("/.*\./", '', getFileNameFromPath($file));

    return saveFile($file, $file_name, $path, $disk);
}

/**
 * Saves a file to s3 in a usernames folder
 * Creates a unique file name.
 *
 * @param UploadedFile|string $file - The uploaded file or file path
 * @param string $file_name - The file name
 * @param string $path - The path
 * @param string $disk - The s3 disk to use
 * @return string|null - The file path
 */
function saveFile($file, string $file_name, string $path = null, string $disk = 's3'): ?string
{
    $file_path = ($path ? $path . '/' : '') . $file_name;

    if (! Storage::disk($disk)->put($file_path, file_get_contents($file))) {
        return null;
    }

    return $file_path;
}

/**
 * Save a file string to s3 in a usernames folder.
 *
 * @param UploadedFile $file - The uploaded file stream
 * @param string $file_name - The file name
 * @param string $path - The path
 * @param string $distk - The s3 disk
 * @return string|null - the file path
 */
function saveFileStream(UploadedFile $file, string $file_name, string $path = null, string $disk = 's3'): ?string
{
    $file_path = ($path ? $path . '/' : '');

    if (! Storage::disk($disk)->putFileAs($file_path, $file, $file_name)) {
        return null;
    }

    return $file_path . $file_name;
}

/**
 * Gets a file Stream.
 *
 * @param string $file_path - File path
 * @param string $disk - The disk to use
 * @return resource|null - The stream of the file
 */
function getFileStream(string $file_path, $disk = 's3')
{
    if (Storage::disk($disk)->exists($file_path)) {
        return Storage::disk($disk)->readStream($file_path);
    }

    return null;
}

/**
 * Get the file name from a path.
 *
 * @param string $path - The file path
 * @return string - The file name
 */
function getFileNameFromPath(string $path): string
{
    return basename($path);
}

/**
 * Create a temporary public URL for a file path.
 *
 * @param string $file_path - The file path
 * @param string $disk - The disk
 * @param int $seconds - Seconds to expiration
 * @return string|null - The public url
 */
function getTempPublicURLFromPath(string $file_path, $disk = 's3', $seconds = 3600): ?string
{
    if (Storage::disk($disk)->exists($file_path)) {
        return Storage::disk($disk)->temporaryUrl($file_path, now()->addSeconds($seconds));
    }

    return null;
}

function getQuery($query)
{
    $escapedBindings = [];
    foreach ($query->getBindings() as $item) {
        $escapedBindings[] = '"' . $item . '"';
    }

    return Str::replaceArray('?', $escapedBindings, $query->toSql());
}

function logQuery($query)
{
    $escapedBindings = [];
    foreach ($query->bindings as $item) {
        $escapedBindings[] = '"' . $item . '"';
    }

    return Str::replaceArray('?', $escapedBindings, $query->sql);
}

/**
 * Dump, Continue.
 *
 * @return void
 */
function dc()
{
    array_map(function ($value) {
        if (class_exists(Symfony\Component\VarDumper\Dumper\CliDumper::class)) {
            $dumper = 'cli' === PHP_SAPI ?
                new Symfony\Component\VarDumper\Dumper\CliDumper :
                new Symfony\Component\VarDumper\Dumper\HtmlDumper;
            $dumper->dump((new Symfony\Component\VarDumper\Cloner\VarCloner)->cloneVar($value));
        } else {
            var_dump($value);
        }
    }, func_get_args());
}

/**
 * Format a regular response.
 *
 * @param array $data
 * @param bool $success
 * @param string $error_code
 * @return JsonResponse
 */
function regularResponse(array $data = [], bool $success = true, string $error_code = null, $code = 200, string $error_detail = null): JsonResponse
{
    $data['success'] = $success;
    if ($error_code) {
        $data['error'] = $error_code;
    }
    if ($error_detail) {
        $data['error_detail'] = $error_detail;
    }

    return response()->json([
        'data' => $data,
    ], $code);
}

function roundDownToNearestTenth(int $number): int
{
    return floor($number / 10) * 10;
}

function trimString(string $string, int $length = 100, bool $add_dots = true): string
{
    if (strlen($string) <= $length) {
        return $string;
    }
    $string = substr($string, 0, $length);
    if ($add_dots) {
        $string .= '...';
    }

    return $string;
}

function getUrlFromPath(string $path = '', string $disk = 's3', bool $is_public = true): string
{
    if (Storage::disk($disk)->exists($path)) {
        try {
            $path = $is_public ? Storage::disk($disk)->url($path) : Storage::disk($disk)->temporaryUrl($path, now()->addDay());
        } catch (ClientException $e) {
            Log::error($e->getMessage());
        }
    }

    return $path;
}

function priceFormat(float $price): string
{
    return number_format($price, 2, '.', ',');
}

function convertImageFromUrlToOutputFile(string $original_url, string $output_location, $quality = 100): bool
{
    $file_info = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $file_info->buffer(file_get_contents($original_url));

    switch ($mime_type) {
        case 'image/jpeg':
            $img_tmp = imagecreatefromjpeg($original_url);
            break;
        case 'image/png':
            $img_tmp = imagecreatefrompng($original_url);
            break;
        default:
            return false;
        break;
    }

    imagejpeg($img_tmp, $output_location, $quality);
    imagedestroy($img_tmp);

    return true;
}

if (! function_exists('record')) {
    /**
     * Helper to create a new activity log.
     *
     * @return \App\Services\Activity\Recorder
     */
    function record()
    {
        if ('cli' !== PHP_SAPI) {
            $user = Auth::user();

            return app(Recorder::class)->setLogStatus(app(ActivityLogStatus::class))->causedBy($user);
        }

        return app(Recorder::class)->setLogStatus(app(ActivityLogStatus::class));
    }
}

if (! function_exists('secondsToTimeString')) {
    function secondsToTimeString(float $time): string
    {
        $times = [
            'day'   =>  86400,
            'hour'   => 3600,
            'minute' => 60,
            'second' => 1,
        ];

        foreach ($times as $unit => $value) {
            if ($time >= $value) {
                $time = floor($time / $value);

                return $time . ' ' . ($time == 1 ? $unit : $unit . 's');
            }
        }

        return $time . ' s';
    }
}

function getS3Instance()
{
    $credentials = new Credentials(config('filesystems.disks.s3.key'), config('filesystems.disks.s3.secret'));
    return new S3Client([
        'version'     => 'latest',
        'region'      => config('filesystems.disks.s3.region'),
        'credentials' => $credentials
    ]);

}

function checkEmailWhitelist(string $receiverEmail = '', bool $multipleEmails = false, array $receiverEmails = [])
{
    $whitelistAddresses = config('whitelist-emails');
    if (App::environment() !== 'production') {
        if ($multipleEmails) {
            $return = false;
            foreach ($receiverEmails as $address=>$rr) {
                if (in_array($address, $whitelistAddresses) || strpos($address, 'artistrepublik.com') !== false) {
                    $return = true;
                } else {
                    return false;
                }
                return $return;
            }
        } elseif (in_array($receiverEmail, $whitelistAddresses) || strpos($receiverEmail, 'artistrepbublik.com') !== false) {
            return true;
        }
    }
    return true;
}

function remove_accents($str) {
    $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', 'Ά', 'ά', 'Έ', 'έ', 'Ό', 'ό', 'Ώ', 'ώ', 'Ί', 'ί', 'ϊ', 'ΐ', 'Ύ', 'ύ', 'ϋ', 'ΰ', 'Ή', 'ή');
    $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o', 'Α', 'α', 'Ε', 'ε', 'Ο', 'ο', 'Ω', 'ω', 'Ι', 'ι', 'ι', 'ι', 'Υ', 'υ', 'υ', 'υ', 'Η', 'η');
    return str_replace($a, $b, $str);
}

function similar_string(string $a, string $b, $explain = false): float {
    // normalize case
    $a = strtolower($a);
    $b = strtolower($b);
    // perfect match?
    if ($a == $b) {
        if ($explain) {
            echo "<br>$a/$b Instant perfect match<br><br>";
        }
        return 1.00;
    }
    // remove accent characters
    $a = remove_accents($a);
    $b = remove_accents($b);
    // match after removing accents?
    if ($a == $b) {
        if ($explain) {
            echo "<br>$a/$b fast-matched after removing accents<br><br>";
        }
        return 0.98;
    }
    // remove non-alphanum characters (commas etc)
    $a = preg_replace("/[^A-Za-z0-9 ]/", ' ', $a);
    $b = preg_replace("/[^A-Za-z0-9 ]/", ' ', $b);
    // remove multiple spaces
    $a = preg_replace('/\s+/', ' ', $a);
    $b = preg_replace('/\s+/', ' ', $b);
    if ($a == $b) {
        if ($explain) {
            echo "<br>$a/$b fast-matched after removing non-alpha num characters<br><br>";
        }
        return 0.90;
    }
    // make sure we have same number of components!
    $parts_a = explode(' ', $a);
    $parts_b = explode(' ', $b);
    if (count($parts_a) !== count($parts_b)) {
        if (count($parts_a) > 2)
        {
            $a = $parts_a[0] . ' ' . end($parts_a);
        }
        if (count($parts_b) > 2)
        {
            $b = $parts_b[0] . ' ' . end($parts_b);
        }
    }
    // match after reducing components (assuming middle name)
    if ($a === $b) {
        if ($explain) {
            echo "<br>$a/$b fast-matched after reducing components<br><br>";
        }
        return 0.85;
    }
    // flip names?
    $flip_a = $parts_a[1] . ' ' . $parts_a[0];
    $flip_b = $parts_b[1] . ' ' . $parts_b[0];
    if ($flip_a === $b || $a === $flip_b) {
        if ($explain) {
            echo "<br>$a/$b fast-matched after flipping first/last name<br><br>";
        }
        return 0.75;
    }
    // if not, now we need to apply scores - we leave it for the end because of computing time
    $score_soundex   = 0;
    $score_metaphone = 0;
    $snd_a = soundex($a);
    $snd_b = soundex($b);
    $meta_a = metaphone($a);
    $meta_b = metaphone($b);
    // perfect soundex?
    if ($snd_a === $snd_b) {
        $score_soundex = 1;
    } else {
        // partial soundex?
        $score_soundex = 0;
        // leading characters of soundex key are more relevant, so they will get higher weight
        if ($snd_a[0] == $snd_b[0]) {
            $score_soundex += 0.30;
        }
        if ($snd_a[1] == $snd_b[1]) {
            $score_soundex += 0.25;
        }
        if ($snd_a[2] == $snd_b[2]) {
            $score_soundex += 0.20;
        }
    }
    if ($meta_a == $meta_b) {
        $score_metaphone = 1;
    }
    $lev = levenshtein($a, $b);
    $score_lev = 1.00;
    // we increasingly reduce the score the bigger lev distance is
    if ($lev >= 1) {
        $score_lev -= 0.05;
    }
    if ($lev >= 2) {
        $score_lev -= 0.07;
    }
    if ($lev >= 3) {
        $score_lev -= 0.10;
    }
    if ($lev >= 4) {
        $score_lev -= 0.12;
    }
    if ($lev >= 5) {
        $score_lev -= 0.15;
    }
    if ($lev >= 6) {
        $score_lev -= 0.20;
    }
    if ($lev >= 7) {
        $score_lev -= 0.30;
    }
    if ($lev >= 8) {
        $score_lev = 0;
    }
    // should not be less than 0, but just in case
    $score_lev = max($score_lev, 0);
    similar_text($a, $b, $sim);
    $sim = round($sim, 2);
    $score_sim = $sim / 100;
    // apply weight to each category and calculate final score
    $score = 0;
    // extra debug info for fine tuning the weights
    if ($explain) {
        echo "<br>";
        echo "[$a] vs [$b]:<BR>";
        echo "Soundex: ($snd_a / $snd_b) => $score_soundex<br>";
        echo "Meta: ($meta_a / $meta_b) => $score_metaphone<br>";
        echo "Lev: $lev => $score_lev<br>";
        echo "Sim: $sim =>  $score_sim<br>";
        echo "<br>";
    }
    // calculate score
    $score += $score_soundex   * 0.30;
    $score += $score_metaphone * 0.10;
    $score += $score_lev       * 0.40;
    $score += $score_sim       * 0.20;
    return round($score, 2);
}

function convertNumberToWord($num = false)
{
    $num = str_replace(array(',', ' '), '' , trim($num));
    if (! $num) {
        return false;
    }
    $num = (int) $num;
    $words = array();
    $list1 = array('', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven',
        'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'
    );
    $list2 = array('', 'ten', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety', 'hundred');
    $list3 = array('', 'thousand', 'million', 'billion', 'trillion', 'quadrillion', 'quintillion', 'sextillion', 'septillion',
        'octillion', 'nonillion', 'decillion', 'undecillion', 'duodecillion', 'tredecillion', 'quattuordecillion',
        'quindecillion', 'sexdecillion', 'septendecillion', 'octodecillion', 'novemdecillion', 'vigintillion'
    );
    $num_length = strlen($num);
    $levels = (int) (($num_length + 2) / 3);
    $max_length = $levels * 3;
    $num = substr('00' . $num, -$max_length);
    $num_levels = str_split($num, 3);
    for ($i = 0; $i < count($num_levels); $i++) {
        $levels--;
        $hundreds = (int) ($num_levels[$i] / 100);
        $hundreds = ($hundreds ? ' ' . $list1[$hundreds] . ' hundred' . ' ' : '');
        $tens = (int) ($num_levels[$i] % 100);
        $singles = '';
        if ( $tens < 20 ) {
            $tens = ($tens ? ' ' . $list1[$tens] . ' ' : '' );
        } else {
            $tens = (int)($tens / 10);
            $tens = ' ' . $list2[$tens] . ' ';
            $singles = (int) ($num_levels[$i] % 10);
            $singles = ' ' . $list1[$singles] . ' ';
        }
        $words[] = $hundreds . $tens . $singles . ( ( $levels && ( int ) ( $num_levels[$i] ) ) ? ' ' . $list3[$levels] . ' ' : '' );
    } //end for loop
    $commas = count($words);
    if ($commas > 1) {
        $commas = $commas - 1;
    }
    return implode(' ', $words);
}

function formatDate(string $date, string $format = 'm/d/Y') {
    return Carbon::parse($date)->format($format);
}


function shuffleAssocArray(array $list) {
  if (!is_array($list)) return $list;

  $keys = array_keys($list);
  $values = array_values($list);
  shuffle($keys);
  shuffle($values);
  $random = array();
  foreach ($keys as $index => $key) {
    $random[$key] = $values[$index];
  }
  return $random;
}

function object_to_array($obj, &$arr)
{
    if (!is_object($obj) && !is_array($obj))
    {
        $arr = $obj;
        return $arr;
    }
    foreach ($obj as $key => $value)
    {
        if (!empty($value))
        {
            $arr[$key] = array();
            object_to_array($value, $arr[$key]);
        }
        else {$arr[$key] = $value;}
    }
    return $arr;
}

