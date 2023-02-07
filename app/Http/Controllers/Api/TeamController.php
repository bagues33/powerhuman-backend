<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Team;
use App\Helpers\ResponseFormatter;

class TeamController extends Controller
{

    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $teamQueri = Team::withCount('employees');

        // powerhuman.com/api/team?id=1
        // get single data
        if($id) {
            $team = $teamQueri->find($id);

            if($team) {
                return ResponseFormatter::success($team, 'Team found');
            }
            return ResponseFormatter::error('Team not found', 404);
        }

        $teams = $teamQueri->where('company_id', $request->company_id);

        // powerhuman.com/api/team?name=siapa
        // get multiple data
        if($name) {
            $teams->where('name', 'LIKE', '%'. $name . '%');
        }
        return ResponseFormatter::success(
            $teams->paginate($limit),
            'Teams found'
        );
    }

    public function create(CreateTeamRequest $request) 
    {

        try {

            // upload logo
            if($request->hasFile('icon')) {
                $path = $request->file('icon')->store('public/icons');
            } 

            // create company
            $team = Team::create([
                'name' => $request->name,
                'icon' =>isset($path) ? $path : '',
                'company_id' => $request->company_id,
            ]);

            if(!$team) {
                throw new Exception('Team not created');
            }

            return ResponseFormatter::success($team, 'Team created');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 501);
        }
    }

    public function update(UpdateTeamRequest $request, $id) 
    {

        try {

            // get team
            $team = Team::find($id);

            if(!$team) {
                throw new Exception('Team not found');
            }

            // upload logo
            if($request->hasFile('icon')) {
                $path = $request->file('icon')->store('public/icons');
            }

            // update team
            $team->update([
                'name' => $request->name,
                'icon' => isset($path) ? $path : $team->icon,
                'company_id' => $request->company_id,
            ]);

            return ResponseFormatter::success($team, 'Team updated');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 501);
        }
    }

    public function destroy($id)
    {
        try {
            // get team
            $team = Team::find($id);

            // check if team exists
            if(!$team) {
                throw new Exception('Team not found');
            }

            $team->delete();

            return ResponseFormatter::success('Team deleted');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
