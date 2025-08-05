<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Rules\UniqueRucRule;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companies = Company::where('user_id', JWTAuth::user()->id)->get();
        return response()->json($companies);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name' => 'required|string',
            'ruc' => [
                'required',
                'string',
                'size:11',
                'regex:/^(10|20)\d{9}$/',
                // 'unique:companies,ruc',
                new UniqueRucRule,
            ],
            'address' => 'required|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'logo' => 'nullable|image',
            'sol_user' => 'required|string',
            'sol_pass' => 'required|string',
            // ext .pem
            'cert' => 'required|file|mimes:txt',
            'client_id' => 'nullable|string',
            'client_secret' => 'nullable|string',
            'production' => 'nullable|boolean',
        ]); 

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('logos');
        }

        $data['cert_path'] = $request->file('cert')->store('certs');
        $data['user_id'] = JWTAuth::user()->id;

        $company = Company::create($data);

        return response()->json([
            'message' => 'Company created successfully',
            'company' => $company,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($company)
    {
        $company = Company::where('ruc', $company)
                        ->where('user_id', JWTAuth::user()->id)
                        ->firstOrFail();
        
        return response()->json($company);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $company)
    {
        $companyDB = Company::where('ruc', $company)
                        ->where('user_id', JWTAuth::user()->id)
                        ->firstOrFail();

        $data = $request->validate([
            'company_name' => 'nullable|string|min:5',
            'ruc' => [
                'nullable',
                'string',
                'size:11',
                'regex:/^(10|20)\d{9}$/',
                // 'unique:companies,ruc',
                new UniqueRucRule($companyDB->id),
            ],
            'address' => 'nullable|string|min:5',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'logo' => 'nullable|image',
            'sol_user' => 'nullable|string',
            'sol_pass' => 'nullable|string',
            // ext .pem
            'cert' => 'nullable|file|mimes:txt',
            'client_id' => 'nullable|string',
            'client_secret' => 'nullable|string',
            'production' => 'nullable|boolean',
        ]); 

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('logos');
        }

        if ($request->hasFile('cert')) {
            $data['cert_path'] = $request->file('cert')->store('certs');
        }

        $companyDB->update($data);

        return response()->json([
            'message' => 'Company updated successfully',
            'company' => $companyDB,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($company)
    {
        $company = Company::where('ruc', $company)
                        ->where('user_id', JWTAuth::user()->id)
                        ->firstOrFail();
        
        $company->delete();

        return response()->json([
            'message' => 'Company deleted successfully',
        ]);
    }
}
