<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
//use Illuminate\Support\Facades\Mail;
//use App\Mail\ExceptionEmail;
use App\Exception;

class ExceptionController extends Controller
{
//    /**
//     * Sends an email to administrators with error details
//     *
//     * @param $subject
//     * @param $error
//     */
//    public static function emailError($subject, $error)
//    {
//        Mail::to(config('mailto.error_notify'))->send(new ExceptionEmail($subject, $error));
//    }

    /**
     * Inserts an Exception entry into DB
     * $message is JSON encoded
     *
     * @param $class
     * @param $function
     * @param $messageArray
     * @return Exception
     */
    public static function insertException($class, $function, $messageArray)
    {
        $exception = new Exception();
        $exception->class = $class;
        $exception->function = $function;
        $exception->message = json_encode($messageArray);

        try {
            $exception->save();
        } catch (QueryException $e) {
//            self::emailError('Unable to save Exception to DB', $e->getMessage());
        }

        return $exception;
    }
}
