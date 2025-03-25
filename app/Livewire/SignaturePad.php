<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SignaturePad extends Component
{
    public function index()
    {
        $respondentSignatures = DB::connection('mysql')
            ->table('signatures')
            ->where('type', 'respondent')
            ->get();

        $interviewerSignatures = DB::connection('mysql')
            ->table('signatures')
            ->where('type', 'interviewer')
            ->get();

        return view('livewire.signature-pad', compact('respondentSignatures', 'interviewerSignatures'));
    }

    // Save signature to SQLite, MySQL, and public folder
    public function save(Request $request)
    {
        $request->validate([
            'sign_image' => 'required|string',
            'type' => 'required|in:respondent,interviewer',
        ]);

        $imageData = $request->input('sign_image');
        $imageName = 'signature_' . time() . '.png';
        $imagePath = public_path('signatures/' . $imageName);

        if (!is_dir(public_path('signatures'))) {
            mkdir(public_path('signatures'), 0755, true);
        }

        file_put_contents($imagePath, base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData)));

        $signatureData = [
            'type' => $request->input('type'),  // 'respondent' or 'interviewer'
            'sign_image' => $imageName,         // Store only the image file name (path)
            'created_at' => now(),
            'updated_at' => now(),
        ];

        try {
            DB::beginTransaction();

            // Insert the signature data into both MySQL and SQLite
            DB::connection('sqlite')->table('signature')->insert($signatureData);
            DB::connection('mysql')->table('signature')->insert($signatureData);

            DB::commit();

            return redirect()->back()->with('success', 'Signature saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to save signature: ' . $e->getMessage());
        }
    }

    // Delete signature from both SQLite and MySQL
    public function delete($id)
    {
        try {
            DB::beginTransaction();

            $signature = DB::connection('mysql')->table('signature')->where('id', $id)->first();

            if (!$signature) {
                throw new \Exception('Signature not found.');
            }

            $imagePath = public_path('signatures/' . $signature->sign_image);

            // Delete image file if exists
            if (file_exists($imagePath)) {
                if (!unlink($imagePath)) {
                    throw new \Exception('Failed to delete signature image.');
                }
            }

            // Delete record from both databases
            $deletedMySQL = DB::connection('mysql')->table('signature')->where('id', $id)->delete();
            $deletedSQLite = DB::connection('sqlite')->table('signature')->where('id', $id)->delete();

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
