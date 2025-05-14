<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index() {
        $project_property = DB::table('project_properties')
            ->select('project_properties.*')
            ->join('project_property_keys', 'project_property_keys.id', '=', 'project_properties.project_property_key_id')
            ->where('project_property_keys.deleted_at', '=', '00-00-00 00:00:00');

        $projects = DB::table('project')
            ->select('project.*')
            ->where('project.deleted', 0)
            ->leftJoinSub($project_property, 'project_properties', function ($join) {
                $join->on('project.id', '=', 'project_properties.project_id');
            })
            ->selectRaw(
                '(CASE WHEN (SELECT COUNT(task2.id)
                         FROM task AS task2
                         LEFT JOIN task_group ON task_group.id = task2.task_group_id
                         WHERE task_group.project_id = project.id AND task2.status = 1) = 0
                  THEN 0 ELSE 1 END) AS hold_status'
            )
            ->get();


        return view('welcome', compact('projects'));
    }
}
