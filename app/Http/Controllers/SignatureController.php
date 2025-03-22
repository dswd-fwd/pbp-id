<?php

namespace App\Http\Controllers;

// use App\Models\Signature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Storage;

class SignatureController extends Controller
{
    // Display all signatures from MySQL
    public function index()
    {
        $signatures = DB::connection('mysql')->table('signatures')->get();
        return view('livewire.signature-pad', compact('signatures'));
    }

    // Save to SQLite, MySQL, and public folder (signatures folder)
    public function saveSignature(Request $request)
    {
        $request->validate([
            'sign_image' => 'required'
        ]);

        $imageData = $request->input('sign_image');
        $imageName = 'signature_' . time() . '.png';
        $imagePath = public_path('signatures/' . $imageName);

        if (!file_exists(public_path('signatures'))) {
            mkdir(public_path('signatures'), 0755, true);
        }

        // Save file to public/signatures folder -.-
        file_put_contents($imagePath, base64_decode(explode(',', $imageData)[1]));

        try {
            DB::beginTransaction();

            // Save to SQLite (sa local)-,-
            DB::connection('sqlite')->table('signatures')->insert([
                'sign_image' => $imageName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Save to MySQL -_-
            DB::connection('mysql')->table('signatures')->insert([
                'sign_image' => $imageName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Signature saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Failed to save signature: ' . $e->getMessage());
        }
    }

    // Delete from both SQLite and MySQL 0_0
    public function delete($id)
    {
        try {
            DB::beginTransaction();

            $signature = DB::connection('mysql')->table('signatures')->where('id', $id)->first();
            if ($signature) {
                $imagePath = public_path('signatures/' . $signature->sign_image);

                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }

                DB::connection('mysql')->table('signatures')->where('id', $id)->delete();
            }

            // Delete from SQLite (sa local)
            DB::connection('sqlite')->table('signatures')->where('id', $id)->delete();

            DB::commit();

            return redirect()->route('signpad.index')->with('success', 'Signature deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('signpad.index')->with('error', 'Failed to delete signature: ' . $e->getMessage());
        }
    }
}