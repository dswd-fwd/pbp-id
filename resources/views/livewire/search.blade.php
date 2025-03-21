<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Geometry\Factories\CircleFactory;
use Intervention\Image\Geometry\Factories\RectangleFactory;
use App\Modifiers\MaskModifier;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

new #[Title('DSWD ID Search')] #[Layout('components.layouts.app')] class extends Component {
    public $id_pic = null;

    public function mount()
    {
        // $login = Http::post('https://fpa-api-dev.dswd.gov.ph/api/v1/auth/tokens', [
        //     'email' => 'plmpunzalan@dswd.gov.ph',
        //     'password' => env('PBP_PASSWORD'),
        // ]);
    }

    // To update
    public function generateId()
    {
        if (!$this->id_pic) {
            dd('No image received!');
        }

        $base64Str = preg_replace('/^data:image\/\w+;base64,/', '', $this->id_pic);
        $imageData = base64_decode($base64Str);
        if (!$imageData) {
            dd('Invalid Base64 image!');
        }

        $tempImagePath = storage_path('app/temp-id.png');
        file_put_contents($tempImagePath, $imageData);

        $image = Image::make(public_path('img/Front-ID.png'));

        $img_pic = Image::make($tempImagePath)->resize(null, 250, function ($constraint) {
            $constraint->aspectRatio();
        });

        $img_pic = $img_pic->resizeCanvas($img_pic->width() + 15, $img_pic->height() + 15, 'center', false, '#293892');

        $img_pic->mask(public_path('img/round.png'), true);

        $image->insert($img_pic, 'center', -285, 10);

        // Image dimensions
        $imageWidth = $image->width();
        $imageHeight = $image->height();
        $rightMargin = 50;
        $lineSpacing = 8;
        $fontPath = public_path('fonts/love_black.otf');

        // Example text
        $name = 'Jose R. Park';
        $address = 'Beneficiary';
        $idNumber = 'ID No: 2024-00123';

        $nameFontSize = 50;
        $addressFontSize = 45;
        $idFontSize = 32;

        // Calculate max width for text (from right edge to middle)
        $maxTextWidth = $imageWidth / 2 + 250 - $rightMargin;

        function wrapText($text, $maxWidth, $fontPath, $fontSize)
        {
            $words = explode(' ', $text);
            $lines = [];
            $currentLine = '';

            foreach ($words as $word) {
                $testLine = $currentLine ? $currentLine . ' ' . $word : $word;
                $box = imagettfbbox($fontSize, 0, $fontPath, $testLine);
                $textWidth = abs($box[2] - $box[0]);

                if ($textWidth <= $maxWidth) {
                    $currentLine = $testLine;
                } else {
                    $lines[] = $currentLine;
                    $currentLine = $word;
                }
            }
            if ($currentLine) {
                $lines[] = $currentLine;
            }

            return $lines;
        }

        function drawWrappedText($image, $text, $xRight, $startY, $fontPath, $fontSize, $color, $maxWidth, $lineSpacing)
        {
            $lines = wrapText($text, $maxWidth, $fontPath, $fontSize);
            foreach ($lines as $line) {
                $image->text($line, $xRight, $startY, function ($font) use ($fontPath, $fontSize, $color) {
                    $font->file($fontPath);
                    $font->size($fontSize);
                    $font->color($color);
                    $font->align('right');
                });
                $startY += $fontSize + $lineSpacing;
            }
            return $startY;
        }

        // Calculate total text block height for vertical centering
        $nameLines = wrapText($name, $maxTextWidth, $fontPath, $nameFontSize);
        $addressLines = wrapText($address, $maxTextWidth, $fontPath, $addressFontSize);
        $idLines = wrapText($idNumber, $maxTextWidth, $fontPath, $idFontSize);

        $totalHeight = count($nameLines) * ($nameFontSize + $lineSpacing) + count($addressLines) * ($addressFontSize + $lineSpacing) + count($idLines) * ($idFontSize + $lineSpacing) + 20; // Extra spacing before ID number

        // Remove extra line spacing from the last line of each block
        $totalHeight -= $lineSpacing * 3; // Adjust for 3 text blocks

        // Center vertically
        $startY = ($imageHeight - $totalHeight) / 1.8;

        // Right edge for text
        $xRight = $imageWidth - $rightMargin;

        // Draw text
        $nextY = drawWrappedText($image, $name, $xRight, $startY, $fontPath, $nameFontSize, '#293892', $maxTextWidth, $lineSpacing);
        $nextY = drawWrappedText($image, $address, $xRight, $nextY, $fontPath, $addressFontSize, '#000000', $maxTextWidth, $lineSpacing);
        drawWrappedText($image, $idNumber, $xRight, $nextY + 20, $fontPath, $idFontSize, '#000000', $maxTextWidth, $lineSpacing);

        $savePath = public_path('img-id/final-id.png');
        $image->save($savePath);

        if (file_exists($tempImagePath)) {
            unlink($tempImagePath);
        }
    }
}; ?>

<div x-data="{
    container: false,
    openCamera: false,
    printId: false,
    stream: null,
    video: null,
    capturedImageBorder: null,
    capturedImage: null,
    captureButton: null,
    resetButton: null,
    countdownDisplay: null,

    init() {
        this.video = document.getElementById('camera');
        this.capturedImageBorder = document.getElementById('capturedImageBorder');
        this.capturedImage = document.getElementById('capturedImage');
        this.captureButton = document.getElementById('capture');
        this.resetButton = document.getElementById('reset');
        this.countdownDisplay = document.getElementById('camera-countdown');
    },

    startCamera() {
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => {
                this.stream = stream;
                this.video.srcObject = stream;
            })
            .catch(error => {
                console.error('Error accessing camera:', error);
            });
    },
    stopCamera() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
    },
    captureButtonClick() {
        this.captureButton.disabled = true; // Disable button

        // Disable clicks but allow movement
        const preventClicks = (event) => event.stopPropagation();
        document.addEventListener('click', preventClicks, true);

        let timeCapture = 3;
        this.countdownDisplay.textContent = timeCapture;
        this.countdownDisplay.classList.remove('hidden');

        const countdown = setInterval(() => {
            if (timeCapture === 1) {
                clearInterval(countdown);
                this.countdownDisplay.classList.add('hidden'); // Hide countdown before reaching 0

                // Capture Image
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.width = this.video.videoWidth;
                canvas.height = this.video.videoHeight;
                context.drawImage(this.video, 0, 0, canvas.width, canvas.height);

                // Convert to Image
                this.capturedImage.src = canvas.toDataURL('image/png');
                this.capturedImageBorder.classList.remove('hidden');
                this.capturedImage.classList.remove('hidden');
                this.video.classList.add('hidden');

                let imageData = canvas.toDataURL('image/png');

                // Send image to Livewire
                $wire.set('id_pic', imageData);

                // Show Reset Button
                this.captureButton.classList.add('hidden');
                this.resetButton.classList.remove('hidden');

                // Re-enable button **after** the image is rendered
                setTimeout(() => {
                    document.removeEventListener('click', preventClicks, true);
                    this.captureButton.disabled = false;
                }, 100); // Small delay to ensure UI updates properly
            } else {
                timeCapture--; // Decrement only if not the last step
                this.countdownDisplay.textContent = timeCapture; // Update countdown
            }
        }, 1000);
    },
    resetButtonClick() {
        this.capturedImageBorder.classList.add('hidden');
        this.capturedImage.classList.add('hidden');
        this.video.classList.remove('hidden');

        this.captureButton.classList.remove('hidden');
        this.resetButton.classList.add('hidden');

        this.countdownDisplay.classList.add('hidden');
        this.countdownDisplay.textContent = '3';
    },
    closeButtonClick() {
        this.resetButtonClick();
        this.openCamera = false;
        this.printId = false;
        this.container = false;
    }
}" x-effect="if (openCamera) startCamera(); else stopCamera();"
    class="justify-center flex w-full pt-24 pb-36" x-cloak>
    <div class="w-full max-w-xl relative px-5">
        <div class="text-center space-y-4">
            <p class="text-neutral-800 font-medium text-xl sm:text-4xl">DSWD ID Generation</p>
            <p class="text-neutral-600 sm:text-xl">Easily input your ID to proceed or register if you don’t have one,
                ensuring a seamless identification process.</p>
        </div>
        <div class="relative mt-16">
            <input
                class="bg-white w-full h-16 placeholder:text-slate-400 text-slate-700 border-3 border-blue-200 rounded-xl pl-3 pr-28 py-2 transition duration-300 ease focus:outline-none focus:border-blue-500 hover:border-blue-500 shadow-sm focus:shadow sm:text-xl"
                placeholder="Search your id here..." />
            <button x-on:click="container = true; openCamera = true;"
                class="absolute h-14 top-1 right-1 flex items-center rounded-xl bg-slate-800 py-1 px-6 border border-transparent text-center sm:text-xl text-white transition-all shadow-sm hover:shadow focus:bg-slate-700 focus:shadow-none active:bg-slate-700 hover:bg-slate-700 active:shadow-none disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none cursor-pointer"
                type="button">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 mr-2">
                    <path fill-rule="evenodd"
                        d="M10.5 3.75a6.75 6.75 0 1 0 0 13.5 6.75 6.75 0 0 0 0-13.5ZM2.25 10.5a8.25 8.25 0 1 1 14.59 5.28l4.69 4.69a.75.75 0 1 1-1.06 1.06l-4.69-4.69A8.25 8.25 0 0 1 2.25 10.5Z"
                        clip-rule="evenodd" />
                </svg>
                Search
            </button>
        </div>

        {{-- <div class="h-64 w-full max-w-2xl bg-red-100">

        </div> --}}
    </div>

    <div x-show="container"
        class="absolute inset-0 min-h-screen w-full flex z-10 bg-gray-900/50 px-10 py-5 overflow-auto" x-cloak>
        {{-- Camera --}}
        <div x-on:click.outside="closeButtonClick"
            class="h-auto m-auto w-full max-w-xl shadow rounded-2xl bg-gray-100 p-5 relative" x-show="openCamera"
            x-transition wire:ignore>
            <div class="absolute -right-2 -top-3 shadow bg-white rounded-full h-8 w-8 grid place-content-center cursor-pointer hover:bg-gray-100"
                x-on:click="closeButtonClick">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="size-6 text-red-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </div>

            <div class="bg-white rounded-xl p-10">
                <p class="text-center text-xl font-semibold mb-4">
                    Capture a Photo
                </p>

                <!-- Video Stream / Captured Image -->
                <div class="flex justify-center relative">
                    {{-- Countdown --}}
                    <div class="absolute z-10 text-white flex justify-center items-center h-full font-bold text-6xl">
                        <span id="camera-countdown" style="text-shadow: 2px 2px 4px rgba(18, 143, 165, 0.8);"></span>
                    </div>
                    <video id="camera" class="rounded-xl h-72" autoplay></video>
                    <div class=" hidden bg-gray-100 border border-neutral-400 p-5 rounded-xl" id="capturedImageBorder">
                        <img class="rounded-xl shadow h-72 hidden " id="capturedImage" />
                    </div>
                </div>

                <!-- Capture & Reset Buttons -->
                <div class="flex flex-col items-center mt-8">
                    <div class="text-center mb-8 text-neutral-800">
                        <p>
                            <span>Name:</span> <span class="font-bold">John Doe</span>
                        </p>
                        <p>
                            <span>ID Number:</span> <span class="font-bold">xxxxxxxxxxx</span>
                        </p>
                    </div>

                    {{-- Capture --}}
                    <button x-on:click="captureButtonClick()" id="capture"
                        class="px-8 py-2 shadow rounded-xl bg-gray-100 hover:bg-gray-200 group cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-8 text-neutral-600 group-hover:text-blue-600">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                        </svg>
                    </button>

                    <div class="hidden space-x-4" id="reset">
                        <form wire:submit="generateId" class="inline-block">
                            {{-- <input type="file" wire:model="id_pic" hidden> --}}
                            <button
                                x-on:click="
                                    openCamera = false; 
                                    setTimeout(() => { printId = true; }, 150);
                                "
                                class="px-8 py-3 bg-sky-600 font-semibold text-white rounded-xl hover:bg-sky-700 cursor-pointer">
                                Generate ID
                            </button>
                        </form>

                        <button x-on:click="resetButtonClick"
                            class="px-8 py-3 bg-red-600 font-semibold text-white rounded-xl hover:bg-red-700 cursor-pointer">
                            Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <div x-cloak x-show="printId" class="h-auto m-auto w-full max-w-4xl shadow rounded-2xl bg-gray-100 p-5 relative"
            x-on:click.outside="closeButtonClick" x-transition x-data="{
                printDiv() {
                    let printContents = $refs.printableArea.innerHTML;
                    let printWindow = window.open('', '', 'width=800,height=600');
            
                    printWindow.document.write(`
                                    <html>
                                    <head>
                                        <title>Print ID</title>
                                        <style>
                                            @media print {
                                                .page-break { 
                                                    page-break-before: always; 
                                                }
            
                                                /* Fix Image Size */
                                                #id-card-front img,
                                                #id-card-back img {
                                                    {{-- width: auto !important; --}}
                                                    width: 3.39in;
                                                    height: 2.16in !important; /* h-52 */
                                                    {{-- object-fit: contain !important; --}}
                                                    {{-- width: 3.39in;
                                        height: 2.16in; --}}
                                                }
                                            }
                                        </style>
                                    </head>
                                    <body>
                                        ${printContents}
                                        <script>
                                            window.onload = function() {
                                                window.print();
                                                window.onafterprint = function() {
                                                    window.close();
                                                };
                                            };
                                        </script>
                                    </body>
                                    </html>
                                `);
            
                    printWindow.document.close();
                }
            
            }">
            <p class="text-center font-bold text-lg mb-4">Generated ID</p>

            <div class="flex justify-center flex-wrap gap-6" id="printableArea" x-ref="printableArea">
                
                <div class="relative">
                    {{-- <div id="id-card-front" class="h-72 w-48 transition-transform duration-500">
                        <img id="id-image" src="{{ asset('img-id/final-id.png') }}" class="h-full w-full object-contain" alt="Front ID">
                    </div> --}}

                    {{-- <p class="mb-4 text-center font-semibold text-neutral-800">Front ID</p> --}}
                    <div class="h-72 w-full" id="id-card-front">
                        <img id="id-image"  src="{{ asset('img-id/final-id.png') }}?t={{ time() }}"
                            class="h-full w-full object-contain" alt="">
                    </div>
        
                    <button 
                        id="flip-button" 
                        class="absolute bottom-0 right-0 p-2 bg-blue-500 text-white rounded-full shadow-lg hover:bg-blue-700"
                        onclick="flipID()">
                        🔄
                    </button>
                </div>

                {{-- old syntax for ID display --}}
                {{-- <div>
                    <p class="mb-4 text-center font-semibold text-neutral-800">Front ID</p>
                    <div class="h-72 w-full" id="id-card-front">
                        <img src="{{ asset('img-id/final-id.png') }}?t={{ time() }}"
                            class="h-full w-full object-contain" alt="">
                    </div>
                </div>

                <!-- Forces next section to print on a new page -->
                <div class="page-break"></div>
        
                <div>
                    <p class="mb-4 text-center font-semibold text-neutral-800">Back ID</p>
                    <div class="h-72 w-full" id="id-card-back">
                        <img src="{{ asset('img/Back-ID.png') }}" class="h-full w-full object-contain" alt="">
                    </div>
                </div> --}}
            </div>
            
            <script>
                let isFront = true;
        
                function flipID() {
                    const idImage = document.getElementById('id-image');
                    isFront = !isFront;
                    idImage.src = isFront ? "{{ asset('img-id/final-id.png') }}" : "{{ asset('img/Back-ID.png') }}";
                    idImage.alt = isFront ? "Front ID" : "Back ID";
                }
            </script>

            <button x-on:click="printDiv()"
                class="mt-6 mx-auto flex px-8 py-3 bg-sky-600 font-semibold text-white rounded-xl hover:bg-sky-700 cursor-pointer">
                Print ID
            </button>
        </div>
    </div>
</div>
