<?php

namespace App\Http\Controllers\InternalMemo;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Model\UserMaintenanceVendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserMaintenanceVendorController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function all()
    {
        $record = UserMaintenanceVendor::where('is_active', 1)
            ->orderBy('created_at', 'DESC')
            ->get();

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $record
        );
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function paginate()
    {
        $record = UserMaintenanceVendor::where('is_active', 1)
            ->orderBy('created_at', 'DESC')
            ->paginate(15);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $record
        );
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $record = UserMaintenanceVendor::find($id);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $record
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $record = UserMaintenanceVendor::create([
                'vendor_name' => $request->vendor_name,
                'description' => $request->description,
                'created_by' => auth()->user()->id
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $record
        );
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function update($id, Request $request)
    {
        DB::beginTransaction();
        try {
            $record = UserMaintenanceVendor::where('id', $id)->first();

            $record->update([
                'vendor_name' => $request->vendor_name,
                'description' => $request->description,
                'is_active' => $request->is_active,
                'updated_by' => auth()->user()->id
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $record
        );
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $record = UserMaintenanceVendor::where('id', $id)->first();
            $record->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $record
        );
    }
}
