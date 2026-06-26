<?php

namespace App\Http\Controllers\Admin\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\StatusTask;
use App\Models\TaskDifficulty;
use App\Models\Task;
use App\Models\User;
use App\Support\StatusSdmManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreProjectTaskController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        abort(403, 'Executive tidak dapat membuat task.');
    }
}
