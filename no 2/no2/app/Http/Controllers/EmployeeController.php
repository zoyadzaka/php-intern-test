<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;

class EmployeeController extends Controller{
    public function store(Request $request){
        $request->validate(
            [
                'nomor' => 'required|unique:employees,nomor',
                'nama' => 'required|string|max:255',
                'jabatan' => 'required|string|max:255',
                'talahir' => 'required|date',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]
        );

        $path = $request->file('photo')->store('employee', 's3');
        $url = Storage::disk('s3')->url($path);

        $employee = Employee::create([
            'nomor' => $request->input('nomor'),
            'nama' => $request->input('nama'),
            'jabatan' => $request->input('jabatan'),
            'talahir' => $request->input('talahir'),
            'photo_upload_path' => $url,
            'created_on' => now(),
            'created_by' => 'admin',
        ]);

        Redis::set("emp_{$employee->nomor}", $employee->toJson());

        return response()->json($employee);
    }


    public function show($id){
        $employee = Employee::findOrFail($id);
        return response()->json($employee);
    }

    public function update(Request $request, $id){
        $employee = Employee::findOrFail($id);

        if ($request->hasFile('photo')) {
            $request->validate(['photo' => 'image|mimes:jpeg,png,jpg|max:2048']);
            $path = $request->file('photo')->store('employee', 's3');
            $url = Storage::disk('s3')->url($path);
            $employee->photo_upload_path = $url;
        }

        $employee->fill($request->except('photo'));
        $employee->updated_on = now();
        $employee->updated_by = 'admin';
        $employee->save();

        Redis::set("emp_{$employee->nomor}", $employee->toJson());

        return response()->json($employee);
    }

    public function destroy($id){
        $employee = Employee::findOrFail($id);
        $employee->deleted_on = now();
        $employee->save();

        Redis::del("emp_{$employee->nomor}");

        return response()->json(['message' => 'Employee deleted successfully']);
    }
}
