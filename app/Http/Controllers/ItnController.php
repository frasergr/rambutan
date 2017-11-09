<?php

namespace App\Http\Controllers;

use App\Itn;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class ItnController extends Controller
{
    public function itnTypeHandler(Request $request)
    {
        try {
            $xml = simplexml_load_string($request->getContent());
            $xml_raw = $request->getContent();

            if (isset($xml->order->orderId)) {
                $order_id = $xml->order->orderId;
                $order_ref = null;
                $type = 'cancellation';
                $status_code = 'CA';
                $email = $xml->order->customerEmail;
            } else {
                switch ($xml->event->attributes()->status_code) {
                    case 'SA':
                        $order_id = $xml->attributes()->id;
                        $order_ref = $xml->attributes()->ref;
                        $email = $xml->customer->email;
                        if ((string) $xml->event->sale->attributes()->amount === '0.00') {
                            $type = 'trial';
                            $status_code = 'TR';
                        } else {
                            $type = $xml->event->attributes()->type;
                            $status_code = $xml->event->attributes()->status_code;
                        }
                        break;
                    case 'RB':
                    case 'RF':
                    case 'PR':
                    case 'CB':
                    case 'TSA':
                        $order_id = $xml->attributes()->id;
                        $order_ref = $xml->attributes()->parent_ref;
                        $email = $xml->customer->email;
                        $type = $xml->event->attributes()->type;
                        $status_code = $xml->event->attributes()->status_code;
                }
            }

            $itnDetails = array(
                'type' => isset($type) ? $type : 'ERROR',
                'status_code' => isset($status_code) ? $status_code : 'ERROR',
                'email' => isset($email) ? $email : 'ERROR',
                'order_id' => isset($order_id) ? $order_id: 'ERROR',
                'order_ref' => isset($order_ref) ? $order_ref : 'ERROR',
                'xml' => isset($xml_raw) ? $xml_raw: 'ERROR',
            );
        } catch (\Exception $e) {
            $error = array(
                'function' => __FUNCTION__,
                'error_code' => $e->getCode(),
                'message' => $e->getMessage(),
            );

            ExceptionController::insertException('itn', __FUNCTION__, $error);

            return response($error,400)->throwResponse();
        }

        return $this->storeItn($itnDetails);
    }

    public function storeItn($itnDetails)
    {
        $itn = new Itn([
            'type' => $itnDetails['type'],
            'status_code' => $itnDetails['status_code'],
            'email' => $itnDetails['email'],
            'order_id' => $itnDetails['order_id'],
            'order_ref' => $itnDetails['order_ref'],
            'xml' => $itnDetails['xml']
        ]);

        $createOrUpdateCustomer = new CustomerController();
        $customerId = $createOrUpdateCustomer->createOrUpdateCustomerFromEvent($itn);

        try {
            $itn->customer()->associate($customerId);

            $itn->save();
        } catch (QueryException $e) {
            $error = array(
                'function' => __FUNCTION__,
                'error_code' => $e->errorInfo[1],
                'message' => $e->errorInfo[2],
            );

            ExceptionController::insertException('itn', __FUNCTION__, $error);

            return response($error,400)->throwResponse();
        }

        return response($itn, 200);
    }
}
