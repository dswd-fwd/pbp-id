<x-layouts.app title="Signatures">
    <style>
        .kbw-signature {
            width: 100%;
            height: 200px;
        }

        #sigpad canvas {
            width: 100% !important;
            height: auto;
        }
    </style>

    <div class="container mx-auto p-4 w-full max-w-4xl text-center">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Signature Pad</h1>

        <div class="overflow-hidden rounded-xl shadow-lg border border-gray-200 bg-white">
            <table class="table-auto w-full text-left text-sm text-gray-600">
                <thead class="bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                    <tr>
                        <th class="px-4 py-3 font-semibold uppercase">ID</th>
                        <th class="px-4 py-3 font-semibold uppercase">Signature Image</th>
                        <th class="px-4 py-3 font-semibold uppercase text-center">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @foreach ($signatures as $signature)
                        <tr class="hover:bg-gray-100 transition duration-200 ease-in-out">
                            <td class="px-4 py-3">{{ $signature->id }}</td>
                            <td class="px-4 py-3">
                                <img src="{{ asset('signatures/' . $signature->sign_image) }}"
                                    class="w-24 h-auto rounded-lg shadow-sm border border-gray-300" />
                            </td>
                            <td class="px-4 py-3 text-center flex justify-center gap-2">

                                <!-- Delete Button Only -->
                                <form action="{{ route('signpad.delete', $signature->id) }}" method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this signature?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-block px-3 py-2 bg-red-500 text-white font-medium text-sm rounded-lg shadow hover:bg-red-600 transition-transform duration-300 hover:scale-105">
                                        ❌ Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card mt-5 p-6 border shadow-lg mx-auto w-full max-w-md bg-white rounded-xl">
            <h5 class="text-xl font-bold mb-4 text-gray-800 text-center">✍️ Draw Your Signature</h5>
            <form method="POST" action="{{ route('signpad.save') }}" class="space-y-4">
                @csrf
                <div id="sigpad" class="mb-4 border-2 border-gray-300 rounded-lg"></div>

                <div class="flex justify-between items-center gap-2">
                    <button id="clear" type="button"
                        class="flex items-center justify-center w-1/2 py-2 text-gray-700 bg-gray-200 font-semibold rounded-lg hover:bg-gray-300 transition duration-300 ease-in-out shadow">
                        ❌ Clear
                    </button>

                    <textarea id="signature" name="sign_image" style="display: none;"></textarea>

                    <button type="submit"
                        class="flex items-center justify-center w-1/2 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold rounded-lg hover:scale-105 transition-transform duration-300 ease-in-out shadow-md">
                        ✅ Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script src="http://keith-wood.name/js/jquery.signature.js"></script>

    <script>
        const sigpad = $("#sigpad").signature({
            syncField: '#signature',
            syncFormat: 'PNG'
        });

        $('#clear').click(function(e) {
            e.preventDefault();
            sigpad.signature('clear');
            $("#signature").val('');
        });
    </script>
</x-layouts.app>