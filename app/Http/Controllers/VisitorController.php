<?php
namespace App\Http\Controllers;

use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VisitorController extends Controller
{
    public function index()
    {
        $visitors = Visitor::all();
        return response()->json($visitors);
    }

    public function show($id)
    {
        $visitor = Visitor::find($id);
        if (!$visitor) {
            return response()->json(['error' => 'Visitor not found'], 404);
        }
        return response()->json($visitor);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'cpf' => 'required|string|max:14|unique:visitors,cpf',
            'email' => 'required|string|email|max:255|unique:visitors,email',
            'birthdate' => 'required|date',
        ]);

        $visitor = Visitor::create($request->all());
        return response()->json($visitor, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'cpf' => 'sometimes|required|string|max:14|unique:visitors,cpf,' . $id,
            'email' => 'sometimes|required|string|email|max:255|unique:visitors,email,' . $id,
            'birthdate' => 'sometimes|required|date',
        ]);

        $visitor = Visitor::find($id);
        if (!$visitor) {
            return response()->json(['error' => 'Visitor not found'], 404);
        }

        $visitor->update($request->all());
        return response()->json($visitor);
    }

    public function destroy($id)
    {
        $visitor = Visitor::find($id);
        if (!$visitor) {
            return response()->json(['error' => 'Visitor not found'], 404);
        }

        $visitor->delete();
        return response()->json(null, 204);
    }
}