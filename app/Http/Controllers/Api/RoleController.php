<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateroleRequest;
use App\Http\Requests\UpdateroleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Role;
use App\Models\Responsibility;
use App\Helpers\ResponseFormatter;

class RoleController extends Controller
{

    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);
        $with_responsibilities = $request->input('with_responsibilities', false);

        $roleQueri = Role::withCount('employees');

        // powerhuman.com/api/role?id=1
        // get single data
        if($id) {
            $role = $roleQueri->with('responsibilities')->find($id);

            if($role) {
                return ResponseFormatter::success($role, 'Role found');
            }
            return ResponseFormatter::error('Role not found', 404);
        }

        $roles = $roleQueri->where('company_id', $request->company_id);

        // powerhuman.com/api/role?name=siapa
        // get multiple data
        if($name) {
            $roles->where('name', 'LIKE', '%'. $name . '%');
        }

        if($with_responsibilities) {
            $roles->with('responsibilities');
        }

        return ResponseFormatter::success(
            $roles->paginate($limit),
            'Roles found'
        );
    }

    public function create(CreateRoleRequest $request) 
    {

        try {

            // create role
            $role = Role::create([
                'name' => $request->name,
                'company_id' => $request->company_id,
            ]);

            if(!$role) {
                throw new Exception('Role not created');
            }

            return ResponseFormatter::success($role, 'Role created');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 501);
        }
    }

    public function update(UpdateRoleRequest $request, $id) 
    {

        try {

            // get company
            $role = Role::find($id);

            if(!$role) {
                throw new Exception('Role not found');
            }

            // update company
            $role->update([
                'name' => $request->name,
                'company_id' => $request->company_id,
            ]);

            return ResponseFormatter::success($role, 'Role updated');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 501);
        }
    }

    public function destroy($id)
    {
        try {
            // get role
            $role = Role::find($id);

            // check if role exists
            if(!$role) {
                throw new Exception('Role not found');
            }

            $role->delete();

            return ResponseFormatter::success('Role deleted');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
