<!-- Header -->
            <p class="text-center font-bold text-2xl text-gray-800 mb-6 tracking-wide">
                Your Generated ID
            </p>

            <!-- Printable Area -->
            <div class="flex justify-center flex-wrap gap-8 p-6 bg-gray-100 shadow-lg rounded-lg" id="printableArea"
                x-ref="printableArea">

                <!-- Front ID Section -->
                <div class="relative id-card transform transition hover:scale-105">
                    <div class="h-72 w-full border-2 border-gray-300 rounded-lg overflow-hidden shadow-md">
                        <img id="id-image" src="{{ asset('img-id/front-id.png') }}?t={{ time() }}"
                            class="h-full w-full object-cover" alt="Front ID">
                    </div>
                </div>

                <!-- Back ID (hidden in UI, shown on print) -->
                <div class="page-break id-card hidden-for-ui transform transition hover:scale-105">
                    <div class="h-72 w-full border-2 border-gray-300 rounded-lg overflow-hidden shadow-md">
                        <img src="{{ asset('img-id/back-id.png') }}?t={{ time() }}"
                            class="h-full w-full object-cover" alt="Back ID">
                    </div>
                </div>
            </div>

            <!-- Styling to handle visibility and modern aesthetics -->
            <style>
                @page {
                    size: 3.39in 2.16in;
                    margin: 0;
                }

                @media print {

                    body,
                    html {
                        margin: 0;
                        padding: 0;
                        height: 100%;
                        width: 100%;
                    }

                    #printableArea {
                        width: 3.39in;
                        height: 2.16in;
                        display: block;
                        position: relative;
                        overflow: hidden;
                    }

                    .id-card img {
                        width: 3.39in;
                        height: 2.16in;
                        object-fit: cover;
                    }

                    .page-break {
                        page-break-before: always;
                    }

                    .no-print {
                        display: none !important;
                        position: absolute;
                        visibility: hidden;
                    }

                    .hidden-for-ui {
                        display: block !important;
                    }
                }

                .hidden-for-ui {
                    display: none;
                }

                /* Smooth scaling effect */
                .id-card {
                    transition: transform 0.3s ease-in-out;
                }

                .id-card:hover {
                    transform: scale(1.05);
                }
            </style>

            <!-- Buttons Section -->
            <div class="flex justify-center gap-4 mt-6 no-print">
                <!-- Print Button -->
                <button x-on:click="printDiv()"
                    class="flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-sky-500 to-blue-600 font-semibold text-white rounded-full shadow-lg hover:from-blue-600 hover:to-sky-500 transition">
                    🖨️ Print ID
                </button>

                <!-- Flip Button -->
                <button id="flip-button" x-on:click="flipID()"
                    class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-500 to-indigo-600 font-semibold text-white rounded-full shadow-lg hover:from-indigo-600 hover:to-purple-500 transition">
                    🔄 Flip ID
                </button>
            </div>

            <!-- AlpineJS Flip Logic -->
            <script>
                function flipID() {
                    const frontImage = document.getElementById('id-image');
                    frontImage.src = frontImage.src.includes('front-id.png') ?
                        "{{ asset('img-id/back-id.png') }}?t={{ time() }}" :
                        "{{ asset('img-id/front-id.png') }}?t={{ time() }}";
                }

                function printDiv() {
                    let printContents = document.getElementById("printableArea").innerHTML;
                    let printWindow = window.open('', '', 'width=800,height=600');
                    printWindow.document.write(`
                            <html>
                                <head>
                                    <title>Print ID</title>
                                    <link rel="stylesheet" href="https://cdn.tailwindcss.com">
                                </head>
                                <body style="display:flex;justify-content:center;align-items:center;height:100vh;margin:0;">
                                    ${printContents}
                                    <script>
                                        window.onload = () => {
                                            window.print();
                                            window.onafterprint = window.close;
                                        };
                                    <\/script>
                                </body>
                            </html>
                        `);
                printWindow.document.close();
                }
            </script>