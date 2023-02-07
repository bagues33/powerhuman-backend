<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Employee;
use App\Helpers\ResponseFormatter;

class EmployeeController extends Controller
{

    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $email = $request->input('email');
        $gender = $request->input('gender');
        $age = $request->input('age');
        $phone = $request->input('phone');
        $team_id = $request->input('team_id');
        $role_id = $request->input('role_id');
        $company_id = $request->input('company_id');
        $limit = $request->input('limit', 10);

        $employeeQueri = Employee::with('team', 'role');

        // powerhuman.com/api/employee?id=1
        // get single data
        if($id) {
            $employee = $employeeQueri->with(['team', 'role'])->find($id);

            if($employee) {
                return ResponseFormatter::success($employee, 'Employee found');
            }
            return ResponseFormatter::error('Employee not found', 404);
        }

        $employees = $employeeQueri;

        // powerhuman.com/api/employee?name=siapa
        // get multiple data
        if($name) {
            $employees->where('name', 'LIKE', '%'. $name . '%');
        }

        if($email) {
            $employees->where('email', $email);
        }

        if($age) {
            $employees->where('age', $age);
        }

        if($phone) {
            $employees->where('phone', 'LIKE', '%'. $phone . '%');
        }

        if($role_id) {
            $employees->where('role_id', $role_id);
        }

        if($team_id) {
            $employees->where('team_id', $team_id);
        }

        if($company_id) {
            $employees->whereHas('team', function ($query) use ($company_id) {
                $query->where('company_id', $company_id);
            });
        }


        return ResponseFormatter::success(
            $employees->paginate($limit),
            'Employees found'
        );
    }

    public function create(CreateEmployeeRequest $request) 
    {

        try {

            // upload logo
            if($request->hasFile('photo')) {
                $path = $request->file('photo')->store('public/photos');
            }

            // create company
            $employee = Employee::create([
                'name' => $request->name,
                'email' => $request->email,
                'gender' => $request->gender,
                'phone' => $request->phone,
                'age' => $request->age,
                'photo' => isset($path) ? $path : '',
                'team_id' => $request->team_id,
                'role_id' => $request->role_id,
            ]);

            if(!$employee) {
                throw new Exception('Employee not created');
            }

            return ResponseFormatter::success($employee, 'Employee created');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 501);
        }
    }

    public function update(UpdateEmployeeRequest $request, $id) 
    {

        try {

            // get employee
            $employee = Employee::find($id);

            if(!$employee) {
                throw new Exception('Employee not found');
            }

            // upload logo
            if($request->hasFile('photo')) {
                $path = $request->file('photo')->store('public/photos');
            }

            // update employee
            $employee->update([
                'name' => $request->name,
                'email' => $request->email,
                'gender' => $request->gender,
                'phone' => $request->phone,
                'age' => $request->age,
                'photo' => isset($path) ? $path : $employee->photo,
                'team_id' => $request->team_id,
                'role_id' => $request->role_id,
            ]);

            return ResponseFormatter::success($employee, 'Employee updated');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 501);
        }
    }

    public function destroy($id)
    {
        try {
            // get employee
            $employee = Employee::find($id);

            // check if employee exists
            if(!$employee) {
                throw new Exception('Employee not found');
            }

            $employee->delete();

            return ResponseFormatter::success('Employee deleted');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
