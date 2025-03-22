<x-layouts.app title="Signatures">
    <style>
        .kbw-signature {
            width: 100%;
            height: 200px;
        }

        #sigpad {
            /* Fixed height */
            display: block;
            border-radius: 8px;
        }

        /* Ensure the signature pad container doesn't overlap */
        #signature-card {
            display: none;
            margin-top: 1rem;
            z-index: 10;
            position: relative;
        }
    </style>

    <div class="container mx-auto p-4 w-full max-w-4xl text-center">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Signature Pad</h1>

        <!-- Signature Table -->
        <div class="overflow-hidden rounded-xl shadow-lg border border-gray-200 bg-white w-full max-w-4xl mx-auto">
            <table class="table-auto w-full text-left text-sm text-gray-600">
                <thead class="bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                    <tr>
                        <th class="px-4 py-3 font-semibold uppercase">Signature Image</th>
                        <th class="px-4 py-3 font-semibold uppercase text-center">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @foreach ($signatures as $signature)
                        <tr class="hover:bg-gray-100 transition duration-200 ease-in-out">
                            <td class="px-4 py-3">
                                <img src="{{ asset('signatures/' . $signature->sign_image) }}"
                                    class="w-24 h-auto rounded-lg shadow-sm border border-gray-300" />
                            </td>
                            <td class="px-4 py-3 text-center flex justify-center gap-2">

                                <!-- Delete Button -->
                                <form action="{{ route('signpad.delete', $signature->id) }}" method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this signature?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-block px-3 py-2 bg-red-500 text-white font-medium text-sm rounded-lg shadow hover:bg-red-600 transition-transform duration-300 hover:scale-105">
                                        ✖️ Remove
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Add Signature Button -->
        <div class="mt-5">
            <button id="show-signature-pad"
                class="px-4 py-2 bg-green-500 text-white font-semibold rounded-lg hover:bg-green-600 transition duration-300 ease-in-out shadow-md">
                ➕ Add Signature
            </button>
        </div>

        <!-- Hidden Signature Pad Card -->
        <div id="signature-card" class="card mt-5 p-6 border shadow-lg mx-auto w-full max-w-md bg-white rounded-xl">
            <h5 class="text-xl font-bold mb-4 text-gray-800 text-center">✍️ Draw Your Signature</h5>
            <form method="POST" action="{{ route('signpad.save') }}" class="space-y-4">
                @csrf
                <div id="sigpad" class="mb-4 border-2 border-gray-300 rounded-lg"></div>

                <div class="flex justify-between items-center gap-2">
                    <button id="clear" type="button"
                        class="flex items-center justify-center w-1/2 py-2 text-gray-700 bg-gray-200 font-semibold rounded-lg hover:bg-gray-300 transition duration-300 ease-in-out shadow">
                        Clear
                    </button>

                    <textarea id="signature" name="sign_image" style="display: none;"></textarea>

                    <button type="submit"
                        class="flex items-center justify-center w-1/2 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold rounded-lg hover:scale-105 transition-transform duration-300 ease-in-out shadow-md">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- jQuery & Signature Pad Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script src="http://keith-wood.name/js/jquery.signature.js"></script>

    <script>
        // Initialize Signature Pad
        const sigpad = $("#sigpad").signature({
            syncField: '#signature',
            syncFormat: 'PNG'
        });

        // Show/Hide Signature Pad
        $("#show-signature-pad").click(function() {
            $("#signature-card").slideDown();
            $(this).fadeOut(); // Hide "Add Signature" button
        });

        // Clear Button Functionality
        $('#clear').click(function(e) {
            e.preventDefault();
            sigpad.signature('clear');
            $("#signature").val('');
        });
    </script>
</x-layouts.app>
