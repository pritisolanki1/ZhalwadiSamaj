<?php

use App\Models\Member;
use App\Models\Report;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use LaravelFCM\Facades\FCM;
use LaravelFCM\Message\Exceptions\InvalidOptionsException;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;

function is_json($string): bool
{
    if (is_array($string)) {
        return false;
    }

    json_decode($string);

    return json_last_error() == JSON_ERROR_NONE;
}

function multiDimensionalArrayDecode($array)
{
    foreach ($array as $key => &$value) {
        if (is_json($value)) {
            $value = json_decode($value, true);
        } elseif (is_array($value)) {
            $value = multiDimensionalArrayDecode($value);
        }
    }

    return nullvalueConvert($array);
}

function nullvalueConvert($array)
{
    foreach ($array as $key => &$value) {
        if (is_array($value)) {
            $value = nullvalueConvert($value);
        } elseif ($value == null) {
            $value = '';
        }
    }

    return $array;
}

// PHP function to print a
// random string of length n
function RandomStringGenerator($n): string
{
    // Variable which store final string
    $generated_string = '';

    // Create a string with the help of
    // small letters, capital letters and
    // digits.
    $domain = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

    // Find the length of created string
    $len = strlen($domain);

    // Loop to create random string
    for ($i = 0; $i < $n; $i++) {
        // Generate a random index to pick
        // characters
        $index = rand(0, $len - 1);

        // Concatenating the character
        // in resultant string
        $generated_string = $generated_string . $domain[$index];
    }

    // Return the random generated string
    return $generated_string;
}

function jsonDecode($value)
{
    if ($value === null) {
        $value = [];
    }
    if (is_json($value)) {
        return json_decode((string) $value, true);
    } else {
        return $value;
    }
}

function singeValue($value)
{
    if ($value === null) {
        $value = '';
    }

    return $value;
}

function makeLastImageValueSet($iData): bool|array|string
{
    if (is_array($iData)) {
        foreach ($iData as &$value) {
            $iValue = explode('/', $value);
            $value = end($iValue);
        }

        return $iData;
    } else {
        return end(explode('/', $iData));
    }
}

function sendNotice($title, $body, $data = [], $user = [], $exclude_user = []): void
{
    Log::info('FCM enter');
    $optionBuilder = new OptionsBuilder();
    try {
        $optionBuilder->setTimeToLive(60 * 20);
    } catch (InvalidOptionsException $e) {
    }

    $notificationBuilder = new PayloadNotificationBuilder($title);
    $notificationBuilder->setBody($body)->setSound('default');

    $dataBuilder = new PayloadDataBuilder();
    $dataBuilder->addData($data);

    $option = $optionBuilder->build();
    $notification = $notificationBuilder->build();
    $data = $dataBuilder->build();

    // You must change it to get your tokens
    $tokens = Member::select('device_token');

    if (!empty($user)) {
        $tokens = $tokens->whereIn('id', $user);
    }

    if (!empty($exclude_user)) {
        $tokens = $tokens->whereNotIn('id', $user);
    }

    $tokens = $tokens->whereNotNull('device_token')->where(
        'device_token',
        '!=',
        ''
    )->groupBy('device_token')->get()->pluck('device_token')->toArray();

    if (!empty($tokens)) {
        Log::info('FCM total token count', [count($tokens)]);
        $downstreamResponse = FCM::sendTo($tokens, $option, $notification, $data);
        Log::info('FCM in number of success', [$downstreamResponse->numberSuccess()]);
        Log::error('FCM in number of failure', [$downstreamResponse->numberFailure()]);
        $downstreamResponse->numberModification();

        // return Array - you must remove all this tokens in your database
        Member::whereIn('device_token', $downstreamResponse->tokensToDelete())->update(['device_token' => null]);

        // return Array (key : oldToken, value : new token - you must change the token in your database)
        $downstreamResponse->tokensToModify();

        // return Array - you should try to resend the message to the tokens in the array
        if (!empty($downstreamResponse->tokensToRetry())) {
            FCM::sendTo($downstreamResponse->tokensToRetry(), $option, $notification, $data);
        }

        // return Array (key:token, value:error) - in production you should remove from your database the tokens present in this array
        if (!empty($downstreamResponse->tokensWithError())) {
            Log::error('FCM in error', $downstreamResponse->tokensWithError());
        }
        //        $downstreamResponse->tokensWithError();
    }
    Log::info('FCM end');
}

function checkDeferenceDeleteMedia($new, $old): void
{
    $medias = makeLastImageValueSet(array_values(array_diff($old, $new)));
    foreach ($medias as $media) {
        $media = Config::get('general.image_path.gallery_image.images') . $media;
        Report::where('value', 'like', "%$media%")->delete();
        File::delete($media);
    }
}

/**
 * Check if image file exists and return URL or empty string
 * 
 * @param string $imagePath The relative image path from public directory
 * @param string $configPath The config path for the image type
 * @return string URL if file exists, empty string otherwise
 */
function getImageUrlIfExists($imagePath, $configPath): string
{
    if (empty($imagePath)) {
        return '';
    }
    
    // If already a full URL, validate it exists before returning
    if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
        // Extract the path from URL to check if file exists
        $parsedUrl = parse_url($imagePath);
        if (isset($parsedUrl['path'])) {
            $pathFromUrl = ltrim($parsedUrl['path'], '/');
            // Remove /image/0/0/ prefix if present
            $pathFromUrl = preg_replace('#^image/0/0/#', '', $pathFromUrl);
            $fullPath = public_path($pathFromUrl);
            if (File::exists($fullPath)) {
                return $imagePath;
            }
        }
        return '';
    }
    
    // Build the full file path
    $fullPath = public_path($configPath . $imagePath);
    
    // Check if file exists
    if (File::exists($fullPath) && is_file($fullPath)) {
        return url('/image/0/0/' . $configPath . $imagePath);
    }
    
    // Return empty string if file doesn't exist
    return '';
}
