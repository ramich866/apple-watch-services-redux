<?php

namespace App\Http\Controllers\HealthMetric;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\HealthMetric;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class HealthMetricController extends Controller
{
    public function uploadCsv(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:csv',
            ]);

            if ($validator->fails()) {
                return responseMessage(
                    false,
                    401,
                    $validator->errors()
                );
            }
            $file = $request->file('file');
            $filePath = $file->storeAs('health_metrics_csv_files', $file->getClientOriginalName(), 'public');
            $csvData = array_map('str_getcsv', file(storage_path('app/public/' . $filePath)));

            array_shift($csvData);

            $user_id = $request->user()->id;
            $oldUserData = HealthMetric::where('user_id', $user_id)->get();

            foreach ($csvData as $row) {
                $date = Carbon::createFromFormat('d/m/Y', $row[1])->format('Y-m-d');
                $found = HealthMetric::where('user_id', $user_id)
                    ->where('date', $date)
                    ->first();
                $metric = new HealthMetric();

                if ($found) {
                    $metric = $found;
                    $oldUserData = $oldUserData->filter(function ($item) use ($found) {
                        return $item->id !== $found->id;
                    });
                }
                $metric->user_id = $user_id;
                $metric->date = $date;
                $metric->steps = intval($row[2]);
                $metric->distance = floatval($row[3]);
                $metric->active_minutes = intval($row[4]);
                $metric->save();

            }

            if ($oldUserData->isNotEmpty()) {
                foreach ($oldUserData as $each) {
                    $each->delete();
                }
            }
            return responseMessage(
                true,
                200,
                "CSV File uploaded successfully"
            );
        } catch (\Throwable $e) {
            return responseMessage(
                false,
                401,
                $e->getMessage()
            );
        }
    }
}
