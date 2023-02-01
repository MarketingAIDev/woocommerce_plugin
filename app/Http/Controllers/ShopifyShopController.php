<?php

namespace Acelle\Http\Controllers;

use Acelle\Model\ShopifyShop;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;


class ShopifyShopController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return mixed
     */
    public function listing(Request $request)
    {
        $items = ShopifyShop::paged_search($request);

        return response()->json([
            'items' => $items,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function show($id)
    {
        $shop = ShopifyShop::findByUidOrFail($id);

        return response()->json([
            'shop' => $shop,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     *
     * @return mixed
     */
    public function update(Request $request, $id)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return void
     */
    public function destroy($id)
    {
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function delete(Request $request)
    {
        $items = ShopifyShop::whereIn('uid', explode(',', $request->uids));

        /** @var ShopifyShop $item */
        foreach ($items->get() as $item) {
            if (Gate::allows('delete', $item)) {
                $customer = $item->customer;
                $item->delete();
                $customer->delete();
            }
        }

        return response('', 204);
    }
}
