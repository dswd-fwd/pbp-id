<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SignatureController extends Controller
{
    // Display all signatures from MySQL
    public function index()
    {
        $signatures = DB::connection('mysql')->table('signatures')->get();
        return view('livewire.signature-pad', compact('signatures'));
    }

    // Save to SQLite, MySQL, and public folder
    public function saveSignature(Request $request)
    {
        $request->validate(['sign_image' => 'required']);

        $imageData = $request->input('sign_image');
        $imageName = 'signature_' . time() . '.png';
        $imagePath = public_path('signatures/' . $imageName);

        // Ensure folder exists
        if (!is_dir(public_path('signatures'))) {
            mkdir(public_path('signatures'), 0755, true);
        }

        // Save file safely
        file_put_contents($imagePath, base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData)));

        try {
            DB::beginTransaction();

            $signatureData = [
                'sign_image' => $imageName,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Save to both databases
            DB::connection('sqlite')->table('signatures')->insert($signatureData);
            DB::connection('mysql')->table('signatures')->insert($signatureData);

            DB::commit();

            return redirect()->back()->with('success', 'Signature saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to save signature: ' . $e->getMessage());
        }
    }

    // Delete the signature from both SQLite and MySQL
    public function delete($id)
    {
        try {
            DB::beginTransaction();

            $signature = DB::connection('mysql')->table('signatures')->where('id', $id)->first();

            if (!$signature) {
                throw new \Exception('Signature not found.');
            }

            $imagePath = public_path('signatures/' . $signature->sign_image);

            // Delete image file if exists
            if (file_exists($imagePath) && !unlink($imagePath)) {
                throw new \Exception('Failed to delete signature image.');
            }

            // Delete from both databases
            $deletedMySQL = DB::connection('mysql')->table('signatures')->where('id', $id)->delete();
            $deletedSQLite = DB::connection('sqlite')->table('signatures')->where('id', $id)->delete();

            if (!$deletedMySQL || !$deletedSQLite) {
                throw new \Exception('Failed to delete record from one or both databases.');
            }

            DB::commit();
            return redirect()->route('signpad.index')->with('success', 'Signature deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('signpad.index')->with('error', 'Failed to delete signature: ' . $e->getMessage());
        }
    }
}