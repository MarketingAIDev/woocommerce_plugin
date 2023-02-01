<?php

namespace Acelle\Http\Controllers;

use Acelle\Model\GalleryImage;
use Acelle\Model\User;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function __construct()
    {
        $this->middleware('selected_customer');
        parent::__construct();
    }

    public function listing(Request $request)
    {
        $request->merge(array("customer_id" => $request->selected_customer->id));
        $images = GalleryImage::filter($request)->paginate($request->per_page);

        return response()->json([
            'view' => 'images._list',
            'images' => $images
        ]);
    }

    public function store(Request $request)
    {
        $data = GalleryImage::validateAndStore($request->selected_customer, $request->all());

        return $data;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return void
     */
    public function delete(Request $request)
    {
        $items = $request->selected_customer->gallery()->whereIn('uid', explode(',', $request->uids));

        foreach ($items->get() as $item) {
            $item->delete();
        }

        echo 'deleted';
    }
}
