<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task as TaskModel;
use Illuminate\Support\Facades\Session;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = TaskModel::all();


    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $task = new TaskModel();
        $task->title = $request->input('title');
        $task->description = $request->input('description');
        $task->user_id= $request->input('user_id');
        if ($request->hasFile('attach_pdf')) {
            $task->pdf_file = $request->file('pdf_file')->store('pdfs');
        }

        if ($request->hasFile('attach_img')) {
            $task->image_file = $request->file('image_file')->store('images');
        }
        $task->save();
        $task->load('user');
        return response()->json($task, 201); // 201 es el código de estado para "Creado"

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $task_id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $task_id)
    {
        $task = TaskModel::find($task_id);
        $task->title = $request->input('title');
        $task->description = $request->input('description');
        $task->user_id= $request->input('user_id');
        $task->save();
        $task->load('user');
        return response()->json($task, 200); // 200 es el código de estado para
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $task_id)
    {
        $task = TaskModel::find($task_id);

        if ($task) {
            $task->delete();
            return response()->json($task, 200); // Devuelve la tarea eliminada en formato JSON
        } else {
            return response()->json(['message' => 'Task no encontrada'], 404);
        }
    }

    public function complete(Request $request, string $task_id){
        $task = TaskModel::find($task_id);
        if ($request->hasFile('attach_pdf')) {
            $task->pdf_file = $request->file('pdf_file')->store('pdfs');
        }
        if ($request->hasFile('attach_img')) {
            $task->image_file = $request->file('image_file')->store('images');
        }
        $task->completed = true;
        $task->save();
        $task->load('user');
        return response()->json($task, 200);


    }

    public function getTasks(){
        $tasks = TaskModel::with('user')->orderBy('created_at', 'desc')->get();
        return response()->json($tasks);

    }

}
