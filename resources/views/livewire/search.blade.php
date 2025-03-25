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
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Url;
use App\Models\User;
use App\Models\FamilyProfile;

new #[Title('DSWD ID Search')] #[Layout('components.layouts.app')] 
class extends Component {
    public $id_pic = null;
    #[Url]
    public $search;

    public $members;

    public $member_name = '';
    public $member_id = '';
    public $signature;
    public $success_img = false;

    public function mount()
    {
        $this->members = collect();

        if (!empty($this->search)) {
            $this->searchFamilyMembers();
        }
    }

    public function updatedSearch()
    {
        $this->searchFamilyMembers();
    }

    public function searchFamilyMembers()
    {
        $searchTerm = '%' . strtolower($this->search) . '%';

        // Fetch Users where role = 'member' and match name or UUID
        $users = User::where('role', 'member')
            ->where(function ($query) use ($searchTerm) {
                $query->whereRaw('LOWER(name) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(uuid) LIKE ?', [$searchTerm]);
            })
            ->where('id', '!=', auth()->id())
            ->get()
            ->map(fn($user) => array_merge($user->toArray(), ['type' => 'user']));

        // Fetch FamilyProfiles where name or UUID matches
        $familyProfiles = FamilyProfile::where(function ($query) use ($searchTerm) {
                $query->whereRaw('LOWER(name) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(uuid) LIKE ?', [$searchTerm]);
            })
            ->with('user')
            ->get()
            ->map(fn($profile) => array_merge($profile->toArray(), ['type' => 'family_profile']));

        // Merge both arrays and remove duplicates based on 'uuid'
        $this->members = collect(array_merge($users->toArray(), $familyProfiles->toArray()))
            ->unique('uuid')
            ->values();
    }

    public function setMember($name, $id)
    {
        $this->success_img = false;
        $this->search = '';
        $this->member_name = $name;
        $this->member_id = $id;
    }

    // ID settings
    public function generateId()
    {
        $profileId = $this->member_id;
        $signatureUrl = "http://192.168.1.10:8000/img/signature/{$profileId}.png";
        $qrCodeUrl = "http://dswd-pbp-v1.test/img/qr_code/{$profileId}.png";

        $tempSignaturePath = storage_path("app/temp-signature-{$profileId}.png");
        $tempQrCodePath = storage_path("app/temp-qr-{$profileId}.png");

        try {
            // Fetch signature image from the external system
            $headers = get_headers($signatureUrl, 1);
            if (strpos($headers[0], '200') !== false) {
                file_put_contents($tempSignaturePath, file_get_contents($signatureUrl));
                $this->signature = $tempSignaturePath;
            } else {
                $this->signature = null;
            }
        } catch (\Exception $e) {
            $this->signature = null;
        }

        try {
            // Fetch QR code from the external system
            $headers = get_headers($qrCodeUrl, 1);
            if (strpos($headers[0], '200') !== false) {
                file_put_contents($tempQrCodePath, file_get_contents($qrCodeUrl));
                $this->qr_code = $tempQrCodePath;
            } else {
                $this->qr_code = null;
            }
        } catch (\Exception $e) {
            $this->qr_code = null;
        }

        if (!$this->id_pic) {
            dd('No image received!');
        }

        $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $this->id_pic));
        if (!$imageData) {
            dd('Invalid Base64 image!');
        }

        $tempFrontImagePath = storage_path('app/temp-id-front.png');
        $tempBackImagePath = storage_path('app/temp-id-back.png');

        // Save two copies of the image to avoid conflicts
        file_put_contents($tempFrontImagePath, $imageData);
        file_put_contents($tempBackImagePath, $imageData);

        // Generate Front ID
        $this->generateFrontId($tempFrontImagePath);

        // Generate Back ID (includes QR Code)
        $this->generateBackId($tempBackImagePath);

        // Clean up temp images
        unlink($tempFrontImagePath);
        unlink($tempBackImagePath);
        if ($this->signature && file_exists($this->signature)) {
            unlink($this->signature);
        }
        if ($this->qr_code && file_exists($this->qr_code)) {
            unlink($this->qr_code);
        }

        $this->success_img = true;
    }


    private function generateFrontId($tempImagePath)
    {
        $image = Image::make(public_path('img/pbp-id-front.png'));
        $img_pic = Image::make($tempImagePath);

        // Resize uploaded image
        $img_pic->fit(238, 238);
        $image->insert($img_pic, 'top-left', 104, 245);

        // Text settings
        $name = $this->member_name;
        $idNumber = $this->member_id;
        $fontPath = public_path('fonts/love_black.otf');
        $rightMargin = strlen($name) > 24 ? 550 : 420;
        $lineSpacing = 8;
        $maxTextWidth = $image->width() / 2 + 250;

        // Draw ID number below image
        $this->drawWrappedText($image, [['text' => $idNumber, 'color' => '#000000', 'size' => 22]], 170, 256 + 258, $fontPath, $maxTextWidth, $lineSpacing);

        // Draw name text
        // $this->drawWrappedText($image, [['text' => $name, 'color' => '#293892', 'size' => strlen($name) > 24 ? 20 : 32]], $image->width() - $rightMargin, 320, $fontPath, $maxTextWidth, $lineSpacing);
        $image->text($name, 780, 310, function ($font) use ($fontPath, $name) {
                $fontSize = strlen($name) > 25 ? 24 : 32; // Adjust size based on name length
                $font->file($fontPath)->size($fontSize)->color('#293892')->align('center')->valign('top');
            });

        // Save Front ID
        $image->save(public_path('img-id/final-id-front.png'));
    }

    private function generateBackId($tempImagePath)
    {
        $image = Image::make(public_path('img/pbp-id-back.png'));

        // Insert Signature (if exists)
        if ($this->signature && file_exists($this->signature)) {
            $signatureImage = Image::make($this->signature)->resize(250, 100);
            $image->insert($signatureImage, 'right-bottom', 86, 140);
        }

        // Insert QR Code (if exists)
        if ($this->qr_code && file_exists($this->qr_code)) {
            $qrImage = Image::make($this->qr_code)->resize(265, 265);
            $image->insert($qrImage, 'right-bottom', 78, 248); // Adjust position as needed
        }

        // Save Back ID
        $image->save(public_path('img-id/final-id-back.png'));
    }


    private function imageExists($url)
    {
        $headers = @get_headers($url);
        return $headers && strpos($headers[0], '200') !== false;
    }

    // Make drawWrappedText a private method
    private function drawWrappedText($image, $details, $x, $y, $fontPath, $maxWidth, $lineSpacing)
    {
        foreach ($details as $item) {
            $lines = $this->wrapText($item['text'], $maxWidth, $fontPath, $item['size']);
            foreach ($lines as $line) {
                $image->text($line, $x, $y, function ($font) use ($fontPath, $item) {
                    $font->file($fontPath)->size($item['size'])->color($item['color'])->align('left');
                });
                $y += $item['size'] + $lineSpacing; // Move down for the next line
            }
        }
    }

    // Make wrapText a private method
    private function wrapText($text, $maxWidth, $fontPath, $fontSize)
    {
        $lines = [];
        $currentLine = '';
        foreach (explode(' ', $text) as $word) {
            $testLine = trim($currentLine . ' ' . $word);
            $textWidth = abs(imagettfbbox($fontSize, 0, $fontPath, $testLine)[2]);
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
}; 

?>

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
    class="justify-center flex w-full pt-12 pb-36" x-cloak>
    <div class="w-full max-w-xl relative px-5">
        <div class="text-center space-y-4">
            <p class="text-neutral-800 font-medium text-xl sm:text-4xl">DSWD ID Generation</p>
            <p class="text-neutral-600 sm:text-xl">Easily input your <span class="font-medium">Name</span> to proceed or register if you don’t have one,
                ensuring a seamless identification process.</p>
        </div>
        <div class="relative mt-16">
            <input
                class="bg-white w-full h-16 placeholder:text-slate-400 text-slate-700 border-3 border-blue-200 rounded-xl px-3 py-2 transition duration-300 ease focus:outline-none focus:border-blue-500 hover:border-blue-500 shadow-sm focus:shadow sm:text-xl"
                placeholder="Search your id here..." 
                wire:model.live.debounce.250ms="search"
            />
            @if (!$members->isEmpty() && $search)
                <div class="bg-white rounded-xl shadow-lg mt-2 max-h-72 overflow-y-auto text-xl">
                    @foreach ($members as $member)
                        <p 
                            x-on:click="container = true; openCamera = true; $wire.setMember('{{ $member['name'] }}', '{{ $member['uuid'] }}')"
                            class="hover:bg-gray-100 px-6 py-4"
                            wire:key="{{ $member['uuid'] }}"
                        >
                            {{ $member['name'] }} - <span class="text-sm">({{$member['uuid']}})</span> 
                        </p>
                    @endforeach
                </div> 
            @elseif($search && $members->isEmpty()) 
                <div class="bg-white rounded-xl shadow-lg mt-2 max-h-72 overflow-y-auto text-xl">
                    <p class="px-6 py-4">No Results found...</p>
                </div>       
            @endif
        </div>
    </div>

    <div x-show="container"
        class="absolute inset-0 min-h-screen w-full flex z-10 bg-gray-900/50 px-10 py-5 overflow-auto" x-cloak>
        {{-- Camera --}}
        <div x-on:click.outside="closeButtonClick; $wire.setMember('', '');"
            class="h-auto m-auto w-full max-w-xl shadow rounded-2xl bg-gray-100 p-5 relative" x-show="openCamera"
            x-transition >
            <div class="absolute -right-2 -top-3 shadow bg-white rounded-full h-8 w-8 grid place-content-center cursor-pointer hover:bg-gray-100"
                x-on:click="closeButtonClick; $wire.setMember('', '');">
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
                <div class="flex justify-center relative" wire:ignore>
                    {{-- Countdown --}}
                    <div class="absolute z-10 text-white flex justify-center items-center h-full font-bold text-6xl">
                        <span id="camera-countdown" style="text-shadow: 2px 2px 4px rgba(18, 143, 165, 0.8);"></span>
                    </div>
                    <video id="camera" class="rounded-xl h-72" autoplay></video>
                    <div class="hidden bg-gray-100 border border-neutral-400 p-5 rounded-xl" id="capturedImageBorder">
                        <img class="rounded-xl shadow h-72 hidden " id="capturedImage" />
                    </div>
                </div>

                <div class="text-center mt-8 mb-8 text-neutral-800">
                    <p>
                        <span>Name:</span> <span class="font-bold" wire:key="{{ $member_name }}">{{ $member_name }}</span>
                    </p>
                    <p>
                        <span>ID Number:</span> <span class="font-bold" wire:key="{{ $member_id }}">{{ $member_id }}</span>
                    </p>
                </div>

                <!-- Capture & Reset Buttons -->
                <div class="flex flex-col items-center"  wire:ignore>
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
        x-on:click.outside="closeButtonClick" x-transition 
        x-data="{
            printDiv() {
                let printWindow = window.open('', '', 'width=800,height=600');
    
                printWindow.document.write(`
                    <html>
                    <head>
                        <title>Print ID</title>
                        <style>
                            @page {
                                size: 85.60mm 53.98mm;
                                margin: 0;
                            }
                            @media print {
                                .page-break { 
                                    page-break-before: always; 
                                }
    
                                /* Ensure correct ID card image size */
                                .print-image {
                                    object-fit: cover;
                                    height: 100% !important;
                                    width: 100% !important;
                                }
                            }
                        </style>
                    </head>
                    <body>
                        <img class='print-image' src='{{ asset('img-id/final-id-front.png') }}?t={{ time() }}' alt='Front ID'>
    
                        <div class='page-break'></div>
    
                        <img class='print-image' src='{{ asset('img-id/final-id-back.png') }}?t={{ time() }}' alt='Back ID'>
    
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
                    <div class="h-72 w-full" id="id-card-front">
                        @if ($success_img)
                            <img 
                                id="id-image" 
                                src="{{ asset('img-id/final-id-front.png') }}?t={{ time() }}"
                                class="h-full w-full object-contain" alt="Front ID"
                            >
                            @else
                            <img 
                                id="id-image" 
                                src="{{ asset('img/pbp-id-front.png') }}?t={{ time() }}"
                                class="h-full w-full object-contain" alt="Front ID"
                            >
                        @endif

                    </div>

                    <button id="flip-button"
                        class="absolute bottom-0 right-0 p-2 bg-blue-500 text-white rounded-full shadow-lg hover:bg-blue-700"
                        onclick="flipID()">
                        🔄
                    </button>
                </div>
            </div>

            <script>
                let isFront = true;

                function flipID() {
                    const idImage = document.getElementById('id-image');
                    isFront = !isFront;
                    idImage.src = isFront 
                        ? "{{ asset('img-id/final-id-front.png') }}?t={{ time() }}" 
                        : "{{ asset('img-id/final-id-back.png') }}?t={{ time() }}";
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
