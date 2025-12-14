<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\PoolUser;
use Exception;

class PoolUserController extends Controller
{
    private $externalApiUrl = 'https://bit.ly/48ejMhW';

    private function fetchExternalData()
    {
        try {
            $response = Http::withOptions([
                'verify' => false
            ])->get($this->externalApiUrl);
            
            if (!$response->successful()) {
                throw new Exception('External API returned status: ' . $response->status());
            }
            
            $responseData = $response->json();
            
            if (empty($responseData) || !isset($responseData['DATA'])) {
                throw new Exception('External API returned invalid data structure');
            }
            
            $dataString = $responseData['DATA'];
            $lines = explode("\n", trim($dataString));
            
            if (count($lines) <= 1) {
                throw new Exception('No data rows found in DATA');
            }
            
            $headers = explode('|', $lines[0]);
            
            $parsedData = [];
            for ($i = 1; $i < count($lines); $i++) {
                $row = explode('|', trim($lines[$i]));
                
                if (count($row) === count($headers)) {
                    $rowData = [];
                    foreach ($headers as $index => $header) {
                        $rowData[strtolower($header)] = $row[$index] ?? '';
                    }
                    $parsedData[] = $rowData;
                }
            }
            
            if (empty($parsedData)) {
                throw new Exception('Failed to parse any data from external API');
            }
            
            return $parsedData;
            
        } catch (Exception $e) {
            Log::error('Error fetching external data: ' . $e->getMessage());
            throw $e;
        }
    }

    public function searchByName(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to validate input!',
                    'errors' => $validator->errors()
                ], 422);
            }

            $name = $request->name;
            
            $externalData = $this->fetchExternalData();

            foreach ($externalData as $data) {
                if (isset($data['nim'])) {
                    PoolUser::updateOrCreate(
                        ['nim' => $data['nim']],
                        [
                            'nama' => $data['nama'] ?? '',
                            'ymd' => $data['ymd'] ?? '',
                            'data' => $data
                        ]
                    );
                }
            }

            $results = PoolUser::where('nama', 'like', '%' . $name . '%')->get();

            if ($results->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data with name: "' . $name . '" is not found!',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Successfully fetched data by name: ' . $name,
                'count' => $results->count(),
                'data' => $results
            ]);

        } catch (Exception $e) {
            Log::error('Error in searchByName: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function searchByNim(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nim' => 'required|string|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to validate input!',
                    'errors' => $validator->errors()
                ], 422);
            }

            $nim = $request->nim;
            
            $externalData = $this->fetchExternalData();

            foreach ($externalData as $data) {
                if (isset($data['nim'])) {
                    PoolUser::updateOrCreate(
                        ['nim' => $data['nim']],
                        [
                            'nama' => $data['nama'] ?? '',
                            'ymd' => $data['ymd'] ?? '',
                            'data' => $data
                        ]
                    );
                }
            }

            $result = PoolUser::where('nim', $nim)->first();

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data with NIM ' . $nim . ' is not found!'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Successfully fetched data by NIM: ' . $nim,
                'data' => $result
            ]);

        } catch (Exception $e) {
            Log::error('Error in searchByNim: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function searchByYmd(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ymd' => 'required|string|size:8|regex:/^\d{8}$/'
            ], [
                'ymd.regex' => 'YMD format should be 8 digits (YYYYMMDD)'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to validate input!',
                    'errors' => $validator->errors()
                ], 422);
            }

            $ymd = $request->ymd;
            
            $externalData = $this->fetchExternalData();

            foreach ($externalData as $data) {
                if (isset($data['nim'])) {
                    PoolUser::updateOrCreate(
                        ['nim' => $data['nim']],
                        [
                            'nama' => $data['nama'] ?? '',
                            'ymd' => $data['ymd'] ?? '',
                            'data' => $data
                        ]
                    );
                }
            }

            $results = PoolUser::where('ymd', $ymd)->get();

            if ($results->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data with YMD ' . $ymd . ' is not found!',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Successfully fetched data by YMD: ' . $ymd,
                'count' => $results->count(),
                'data' => $results
            ]);

        } catch (Exception $e) {
            Log::error('Error in searchByYmd: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAllData(Request $request)
    {
        try {
            $externalData = $this->fetchExternalData();

            foreach ($externalData as $data) {
                if (isset($data['nim']) && !empty($data['nim'])) {
                    PoolUser::updateOrCreate(
                        ['nim' => $data['nim']],
                        [
                            'nama' => $data['nama'] ?? '',
                            'ymd' => $data['ymd'] ?? '',
                            'data' => $data
                        ]
                    );
                }
            }

            $dataCount = count($externalData);

            return response()->json([
                'success' => true,
                'message' => 'Successfully fetched data from external API',
                'count' => $dataCount,
                'data' => $externalData
            ]);

        } catch (Exception $e) {
            Log::warning('External API failed, falling back to database: ' . $e->getMessage());
            
            $poolUsers = PoolUser::all();
            
            if ($poolUsers->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error to fetch data neither from external API nor database: ' . $e->getMessage()
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data fetched from database (fallback)',
                'count' => $poolUsers->count(),
                'data' => $poolUsers
            ]);
        }
    }
}
