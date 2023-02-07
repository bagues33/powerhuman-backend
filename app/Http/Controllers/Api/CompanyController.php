<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Models\User;
use App\Helpers\ResponseFormatter;

class CompanyController extends Controller
{
    //
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $companyQueri = Company::with(['users'])->whereHas('users', function ($query) {
            $query->where('user_id', Auth::id());
        });

        // powerhuman.com/api/company?id=1
        // get single data
        if($id) {
            $company = $companyQueri->find($id);

            if($company) {
                return ResponseFormatter::success($company, 'Company found');
            }
            return ResponseFormatter::error('Company not found', 404);
        }

        // get multiple data
        $companies = $companyQueri;

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
                'logo' => isset($path) ? $path : ''

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

    public function update(UpdateCompanyRequest $request, $id) 
    {

        try {

            // get company
            $company = Company::find($id);

            if(!$company) {
                throw new Exception('Company not found');
            }

            // upload logo
            if($request->hasFile('logo')) {
                $path = $request->file('logo')->store('public/logos');
            }

            // update company
            $company->update([
                'name' => $request->name,
                'logo' => isset($path) ? $path : $company->logo,
            ]);

            // attach company to user
            $user = User::find(Auth::id());
            $user->companies()->attach($company->id);

            $company->load('users');

            return ResponseFormatter::success($company, 'Company updated');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 501);
        }
    }

    public function destroy($id)
    {
        try {
            // get team
            $company = Company::find($id);

            // check if team exists
            if(!$company) {
                throw new Exception('Compnay not found');
            }

            $company->delete();

            return ResponseFormatter::success('Company deleted');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }


}
