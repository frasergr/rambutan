<?php

namespace App\Http\Controllers;

use App\Customer;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class CustomerController extends Controller
{
    /**
     * Creates Customer from event if not found
     * Updates Customer from event if found; status set to Active from TSA and RB ITNs
     *
     * @param $itn
     * @return mixed
     */
    public function createOrUpdateCustomerFromEvent($itn)
    {
        try {
//            dd(Customer::where('original_order_id', $itn->order_id . '-' . $itn->order_ref)->firstOrFail());
            $customer = Customer::where('original_order_id', $itn->order_id . '-' . $itn->order_ref)->firstOrFail();

            switch ($itn->status_code) {
                case 'CA':
                    $customer->status = 'cancelled';
                    break;
                case 'TSA':
                case 'RB':
                case 'SA':
                    $customer->status = 'active';
                    break;
            }

            $customer->save();
        } catch (ModelNotFoundException $e) {
            $xml = simplexml_load_string($itn->xml);

            $customer = new Customer([
                'original_order_id' => $xml->attributes()->id . '-' . $xml->attributes()->ref,
                'name' => $xml->customer->name,
                'email' => $xml->customer->email,
                'region' => $xml->customer->region ? $xml->customer->region : null,
                'country' => $xml->customer->country,
                'zip_postal' => $xml->customer->zip_postal,
                'phone' => $xml->customer->phone_number ? $xml->customer->phone_number : null,
                'language' => $xml->customer->language,
                'ip' => $xml->customer->ip,
                'currency' => $xml->customer->currency,
                'shipping_name' => $xml->customer->shipping_info->shipping_name ? $xml->customer->shipping_info->shipping_name : null,
                'shipping_address' => $xml->customer->shipping_info->shipping_address ? $xml->customer->shipping_info->shipping_address : null,
                'shipping_address2' => $xml->customer->shipping_info->shipping_address2 ? $xml->customer->shipping_info->shipping_address2 : null,
                'shipping_city' => $xml->customer->shipping_info->shipping_city ? $xml->customer->shipping_info->shipping_city : null,
                'shipping_state' => $xml->customer->shipping_info->shipping_state ? $xml->customer->shipping_info->shipping_state : null,
                'shipping_country' => $xml->customer->shipping_info->shipping_country ? $xml->customer->shipping_info->shipping_country : null,
                'shipping_postal_code' => $xml->customer->shipping_info->shipping_postal_code ? $xml->customer->shipping_info->shipping_postal_code : null,
                'query' => $xml->extra->request ? $xml->extra->request : null,
            ]);

            switch ($itn->status_code) {
                case 'TR':
                case 'TSA':
                case 'RB':
                case 'SA':
                    $customer->status = 'active';
                    break;
            }

            try {
                $customer->save();
            } catch (QueryException $e) {
                $error = array(
                    'function' => __FUNCTION__,
                    'error_code' => $e->errorInfo[1],
                    'message' => $e->errorInfo[2],
                );

                ExceptionController::insertException('customer', __FUNCTION__, $error);

                return response($error,400)->throwResponse();
            }
        }

        return $customer;
    }
}
