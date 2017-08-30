<?php

namespace App\Http\Controllers;

use App\Itn;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class ItnController extends Controller
{
    private $itn_type, $itn_status_code, $xml, $xml_raw, $order_id;

    /**
     * Handles unique types of ITNs
     *
     * SA ITN with $0.00 = trial
     *
     * Profile changed unique XML structure handling
     * Recurring 0 = cancelled
     * Recurring 1 = reactivated (currently, provider ITN for this does not exist)
     *
     * The route for this function is protected by the ValidateItn.php middleware
     *
     * @param Request $request
     * @return bool|string
     */
    public function itnTypeHandler(Request $request)
    {
        $this->xml_raw = $request->getContent();
        $this->xml = simplexml_load_string($request->getContent());

        if ($this->xml->order && $this->xml->order->orderId) {
            if ((int) $this->xml->order->item->recurring === 0) {
                $this->itn_type = 'cancellation';
                $this->itn_status_code = 'CA';
                $this->order_id = $this->xml->order->orderId;
            } else if ((int) $this->xml->order->item->recurring === 1) {
                $this->itn_type = 'reactivation';
                $this->itn_status_code = 'RA';
                $this->order_id = $this->xml->order->orderId;
            }
        } else {
            if ((string) $this->xml->event->attributes()->status_code === 'SA' && (string) $this->xml->event->sale->attributes()->amount === '0.00') {
                $this->itn_type = 'trial';
                $this->itn_status_code = 'TR';
            }
        }

        return $this->storeItn();
    }

    /**
     * Stores ITN in itns table
     *
     * @return bool|string
     */
    public function storeItn()
    {
        parse_str(urldecode($this->xml->extra->request), $query);

        $itn = new Itn([
            'type' => $this->itn_type ? $this->itn_type : (string) $this->xml->event->attributes()->type,
            'status_code' => $this->itn_status_code ? $this->itn_status_code : (string) $this->xml->event->attributes()->status_code,
            'email' => $this->xml->order->customerEmail ? $this->xml->order->customerEmail : $this->xml->customer->email,
            'order_id' => $this->order_id ? $this->order_id : $this->xml->attributes()->id . '-' . $this->xml->attributes()->ref,
            'xml' => $this->xml_raw
        ]);

        try {
            $itn->save();
        } catch (QueryException $e) {
            $error = array(
                'error_code' => $e->errorInfo[1],
                'message' => $e->errorInfo[2],
            );

            ExceptionController::insertException('itn', 'storeItn', $error);

            return response($error,400)->throwResponse();
        }

        $customer = new CustomerController();
        $customer->createOrUpdateCustomerFromEvent($itn);

        return $itn;
    }
}
