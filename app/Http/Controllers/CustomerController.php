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
            $customer = Customer::where('original_order_id', $itn->order_id)
                ->where('original_ref', $itn->order_ref)
                ->firstOrFail();
            switch ($itn->status_code) {
                case 'CA':
                case 'PR':
                case 'RF':
                case 'CB':
                    if ($customer->status !== 'cancelled') {
                        $customer->status = 'cancelled';
                        $customer->save();
                    }
                    break;
            }
        } catch (ModelNotFoundException $e) {
            $xml = simplexml_load_string($itn->xml);
            parse_str(urldecode($xml->extra->request), $parameters);
            $customer = new Customer([
                'original_order_id' => $itn->order_id,
                'original_ref' => $itn->order_ref,
                'name' => $xml->customer->name,
                'email' => $xml->customer->email,
                'region' => $xml->customer->region ? $xml->customer->region : null,
                'country' => $xml->customer->country,
                'zip_postal' => empty($xml->customer->zip_postal) ? (empty($parameters['shipping_postal_code']) ? null : $parameters['shipping_postal_code']) : $xml->customer->zip_postal,
                'phone' => empty($xml->customer->phone_number) ? (empty($parameters['shipping_phone']) ? null : $parameters['shipping_phone']) : $xml->customer->phone_number,
                'language' => $xml->customer->language,
                'ip' => $xml->customer->ip,
                'currency' => $xml->customer->currency,
                'shipping_name' => empty($xml->customer->shipping_info->shipping_name) ? (empty($parameters['shipping_name']) ? null : $parameters['shipping_name']) : $xml->customer->shipping_info->shipping_name,
                'shipping_address' => empty($xml->customer->shipping_info->shipping_address) ? (empty($parameters['shipping_address']) ? null : $parameters['shipping_address']) : $xml->customer->shipping_info->shipping_address,
                'shipping_address2' => empty($xml->customer->shipping_info->shipping_address2) ? (empty($parameters['shipping_address2']) ? null : $parameters['shipping_address2']) : $xml->customer->shipping_info->shipping_address2,
                'shipping_city' => empty($xml->customer->shipping_info->shipping_city) ? (empty($parameters['shipping_city']) ? null : $parameters['shipping_city']) : $xml->customer->shipping_info->shipping_city,
                'shipping_state' => empty($xml->customer->shipping_info->shipping_state) ? (empty($parameters['shipping_state']) ? null : $parameters['shipping_state']) : $xml->customer->shipping_info->shipping_state,
                'shipping_country' => empty($xml->customer->shipping_info->shipping_country) ? (empty($parameters['shipping_country']) ? null : $parameters['shipping_country']) : $xml->customer->shipping_info->shipping_country,
                'shipping_postal_code' => empty($xml->customer->shipping_info->shipping_postal_code) ? (empty($parameters['shipping_postal_code']) ? null : $parameters['shipping_postal_code']) : $xml->customer->shipping_info->shipping_postal_code,
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