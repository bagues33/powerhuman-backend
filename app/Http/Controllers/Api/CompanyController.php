<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCompanyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Models\User;
use App\Helpers\ResponseFormatter;

class CompanyController extends Controller
{
    //
    public function all(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        // powerhuman.com/api/company?id=1
        if($id) {
            $company = Company::with(['users'])->find($id);

            if($company) {
                return ResponseFormatter::success($company, 'Company found');
            }
            return ResponseFormatter::error('Company not found', 484);
        }

        $companies = Company::with(['users']);

        // powerhuman.com/api/company?name=siapa
        if($name) {
            $companies->where('name', 'LIKE', '%'. $name . '%');
        }
        return ResponseFormatter::success(
            $companies->paginate($limit),
            'Companies found'
        );
    }

    public function create(CreateCompanyRequest $request) 
    {

        try {

            // upload logo
            if($request->hasFile('logo')) {
                $path = $request->file('logo')->store('public/logos');
            }

            // create company
            $company = Company::create([
                'name' => $request->name,
                'logo' => $path,

            ]);

            if(!$company) {
                throw new Exception('Company not created');
            }

            // attach company to user
            $user = User::find(Auth::id());
            $user->companies()->attach($company->id);

            $company->load('users');

            return ResponseFormatter::success($company, 'Company created');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 501);
        }
    }

}
