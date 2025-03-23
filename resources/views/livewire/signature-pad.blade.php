<x-layouts.app title="Signatures">
    <style>
        .kbw-signature {
            width: 100%;
            height: 200px;
        }

        .signature-pad canvas {
            width: 100% !important;
            height: 100% !important;
            display: block;
            border-radius: 8px;
        }

        .signature-card {
            display: none;
            margin-top: 1rem;
            z-index: 10;
            position: relative;
        }

        /* Modal Styles sa delete*/
        .modal-background {
            background-color: rgba(0, 0, 0, 0.5);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 999;
            display: none;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 400px;
            margin: 100px auto;
            text-align: center;
        }
    </style>

    <div class="container mx-auto p-4 w-full max-w-4xl text-center">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Signature Pad</h1>

        <!-- Signature Table -->
        <div class="flex flex-col md:flex-row gap-4 justify-center items-start">
            <!-- Respondent Signatures Table -->
            <div class="overflow-hidden rounded-xl shadow-lg border border-gray-200 bg-white w-full md:w-1/2">
                <table class="table-auto w-full text-left text-sm text-gray-600">
                    <thead class="bg-gradient-to-r from-green-500 to-green-600 text-white">
                        <tr>
                            <th class="px-4 py-3 font-semibold uppercase">Respondent Signature</th>
                            <th class="px-4 py-3 font-semibold uppercase text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($respondentSignatures as $signature)
                            <tr class="hover:bg-gray-100 transition duration-200 ease-in-out">
                                <td class="px-4 py-3">
                                    <img src="{{ asset('signatures/' . $signature->sign_image) }}"
                                        class="w-24 h-auto rounded-lg shadow-sm border border-gray-300" />
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <!-- Delete button that triggers the confirmation modal -->
                                    <button
                                        class="px-3 py-2 bg-red-500 text-white font-medium text-sm rounded-lg shadow hover:bg-red-600 transition-transform duration-300 hover:scale-105 delete-btn"
                                        data-action="{{ route('signpad.delete', $signature->id) }}">
                                        ✖️ Remove
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Interviewer Signatures Table -->
            <div class="overflow-hidden rounded-xl shadow-lg border border-gray-200 bg-white w-full md:w-1/2">
                <table class="table-auto w-full text-left text-sm text-gray-600">
                    <thead class="bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                        <tr>
                            <th class="px-4 py-3 font-semibold uppercase">Interviewer Signature</th>
                            <th class="px-4 py-3 font-semibold uppercase text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($interviewerSignatures as $signature)
                            <tr class="hover:bg-gray-100 transition duration-200 ease-in-out">
                                <td class="px-4 py-3">
                                    <img src="{{ asset('signatures/' . $signature->sign_image) }}"
                                        class="w-24 h-auto rounded-lg shadow-sm border border-gray-300" />
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <!-- Delete button that triggers the confirmation modal -->
                                    <button
                                        class="px-3 py-2 bg-red-500 text-white font-medium text-sm rounded-lg shadow hover:bg-red-600 transition-transform duration-300 hover:scale-105 delete-btn"
                                        data-action="{{ route('signpad.delete', $signature->id) }}">
                                        ✖️ Remove
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Signature Buttons -->
        <div class="mt-5 flex justify-center gap-4">
            <button id="show-respondent-signature-pad"
                class="px-4 py-2 bg-green-500 text-white font-semibold rounded-lg hover:bg-green-600 transition duration-300 ease-in-out shadow-md">
                ➕ Add Respondent Signature
            </button>
            <button id="show-interviewer-signature-pad"
                class="px-4 py-2 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-600 transition duration-300 ease-in-out shadow-md">
                ➕ Add Interviewer Signature
            </button>
        </div>

        <!-- Respondent Signature Pad Card -->
        <div id="respondent-signature-card"
            class="signature-card mt-5 p-6 border shadow-lg mx-auto w-full max-w-md bg-white rounded-xl">
            <h5 class="text-xl font-bold mb-4 text-gray-800 text-center">✍️ Respondent Signature</h5>
            <form method="POST" action="{{ route('signpad.save') }}" class="space-y-4">
                @csrf
                <div id="respondent-sigpad" class="signature-pad mb-4 border-2 border-gray-300 rounded-lg"></div>

                <div class="flex justify-between items-center gap-2">
                    <button type="button"
                        class="clear-btn flex items-center justify-center w-1/2 py-2 text-gray-700 bg-gray-200 font-semibold rounded-lg hover:bg-gray-300 transition duration-300 ease-in-out shadow">
                        Clear
                    </button>

                    <!-- Changed the name to 'sign_image' to match the controller -->
                    <textarea name="sign_image" style="display: none;"></textarea>

                    <input type="hidden" name="type" value="respondent">

                    <button type="submit"
                        class="flex items-center justify-center w-1/2 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold rounded-lg hover:scale-105 transition-transform duration-300 ease-in-out shadow-md">
                        Save
                    </button>
                </div>
            </form>
        </div>

        <!-- Interviewer Signature Pad Card -->
        <div id="interviewer-signature-card"
            class="signature-card mt-5 p-6 border shadow-lg mx-auto w-full max-w-md bg-white rounded-xl">
            <h5 class="text-xl font-bold mb-4 text-gray-800 text-center">✍️ Interviewer Signature</h5>
            <form method="POST" action="{{ route('signpad.save') }}" class="space-y-4">
                @csrf
                <div id="interviewer-sigpad" class="signature-pad mb-4 border-2 border-gray-300 rounded-lg"></div>

                <div class="flex justify-between items-center gap-2">
                    <button type="button"
                        class="clear-btn flex items-center justify-center w-1/2 py-2 text-gray-700 bg-gray-200 font-semibold rounded-lg hover:bg-gray-300 transition duration-300 ease-in-out shadow">
                        Clear
                    </button>

                    <!-- Changed the name to 'sign_image' to match the controller -->
                    <textarea name="sign_image" style="display: none;"></textarea>

                    <input type="hidden" name="type" value="interviewer">

                    <button type="submit"
                        class="flex items-center justify-center w-1/2 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold rounded-lg hover:scale-105 transition-transform duration-300 ease-in-out shadow-md">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal-background">
        <div class="modal-content">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Are you sure you want to delete this signature?</h3>
            <div class="flex justify-between">
                <button id="cancelDelete"
                    class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">Cancel</button>
                <form id="deleteForm" action="" method="POST" class="flex items-center gap-2">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition duration-200">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery & Signature Pad Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script src="http://keith-wood.name/js/jquery.signature.js"></script>

    <script>
        const respondentSigPad = $("#respondent-sigpad").signature({
            syncField: 'textarea[name="sign_image"]',
            syncFormat: 'PNG'
        });

        const interviewerSigPad = $("#interviewer-sigpad").signature({
            syncField: 'textarea[name="sign_image"]',
            syncFormat: 'PNG'
        });

        $("#show-respondent-signature-pad").click(function() {
            $("#respondent-signature-card").slideDown();
            $("#interviewer-signature-card").slideUp();
        });

        $("#show-interviewer-signature-pad").click(function() {
            $("#interviewer-signature-card").slideDown();
            $("#respondent-signature-card").slideUp();
        });

        $(".clear-btn").click(function(e) {
            e.preventDefault();
            const pad = $(this).closest('form').find('.signature-pad');
            pad.signature('clear');
            pad.siblings('textarea').val('');
        });

        // Show modal for signature delete
        $('.delete-btn').click(function() {
            var deleteUrl = $(this).data('action');
            $('#deleteForm').attr('action', deleteUrl);
            $('#deleteModal').show();
        });

        // Cancel button to hide the modal
        $('#cancelDelete').click(function() {
            $('#deleteModal').hide();
        });
    </script>
</x-layouts.app>
