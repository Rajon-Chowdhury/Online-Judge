<?php

namespace App\Http\Controllers\Administration\Problem;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Problem\ProblemService;
use App\Models\User;

class ModeratorController extends Controller
{
    private $problemData;
    protected $problemService;
    public function __construct(ProblemService $probServc)
    {
        $this->problemService  = $probServc;
        // $this->languageService = $languageService;
        if (isset(request()->slug)) {
            $this->problemData = $this->problemService->getProblemData(request()->slug);
        }
    }
    public function getModeratorsList()
    {
        $existing = $this->problemData->moderator;
        $moderators = User::where('handle','like',request()->search.'%')->whereRaw('type <= 30')->get();
        $moderators = $moderators->diff($existing)->take(2)->take(5);
        return json_encode($moderators,200);
    }

    public function addModerator()
    {
        if($this->problemData->authUserRole != "owner")
        {
            abort(401,"You Can Not Add Moderator. Only Problem owner can add moderator");
        }
        $this->problemData->moderator()->attach(request()->userId,[
            'role' => 'moderator',
            'is_accepted' => 0
        ]);
        return response()->json([
            'message' => "Moderator Added Successfully",
        ]);
    }

    public function deleteModerator()
    {
        if($this->problemData->authUserRole != "owner")
        {
            abort(401,"You Can Not Add Moderator");
        }
        $this->problemData->moderator()->detach(request()->userId);
        return response()->json([
            'message' => "Moderator Added Successfully",
        ]);
    }

    public function leaveModerator()
    {
        if($this->problemData->authUserRole == "owner")
        {
            abort(401,"You Can Not Leave From this Problem");
        }
        $this->problemData->moderator()->detach(auth()->user()->id);
        return response()->json([
            'message' => "You Leave From {$this->problemData->name}",
        ]);

    }

    public function cancelModeratorRequest()
    {
        # code...
        $this->problemData->moderator()->detach(auth()->user()->id);
        return response()->json([
            'message' => "Moderator Detach Successfully",
        ]);
        

    }

    public function acceptModetator()
    {
        $user = User::find(request()->userId);
        $user->problems()->updateExistingPivot($this->problemData,['is_accepted' => 1]);
        return response()->json([
            'message' => "Moderator accept successfully",
        ]);
    }
}
