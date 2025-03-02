<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
#[Title('DSWD ID Search')] 
#[Layout('components.layouts.app')] 
class extends Component {
    //
}; ?>

<div 
    x-data="{ 
        openCamera: false,
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
            this.captureButton.disabled = true;
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

                    // Show Reset Button
                    this.captureButton.classList.add('hidden');
                    this.resetButton.classList.remove('hidden');
                } else {
                    timeCapture--; // Decrement only if not the last step
                    this.countdownDisplay.textContent = timeCapture; // Update countdown
                }
            }, 1000);

            this.captureButton.disabled = false;
        },
        resetButtonClick() {
            this.capturedImageBorder.classList.add('hidden');
            this.capturedImage.classList.add('hidden');
            this.video.classList.remove('hidden');

            this.captureButton.classList.remove('hidden');
            this.resetButton.classList.add('hidden');

            this.countdownDisplay.classList.add('hidden'); // Hide countdown
            this.countdownDisplay.textContent = '3'; // Reset countdown text
        }
    }"
    x-effect="if (openCamera) startCamera(); else stopCamera();"
    class="justify-center flex w-full py-36"
    x-cloak
>
    <div class="w-full max-w-sm min-w-xl relative">
        <div class="text-center space-y-4">
            <p class="text-neutral-800 font-medium text-4xl">DSWD ID Generation</p>
            <p class="text-neutral-600 text-xl">Easily input your ID to proceed or register if you don’t have one, ensuring a seamless identification process.</p>
        </div>
        <div class="relative mt-16">
            <input
                class="bg-white w-full h-16 placeholder:text-slate-400 text-slate-700 border-3 border-blue-200 rounded-xl pl-3 pr-28 py-2 transition duration-300 ease focus:outline-none focus:border-blue-500 hover:border-blue-500 shadow-sm focus:shadow text-xl"
                placeholder="Search your id here..." 
            />
            <button
                x-on:click="openCamera = true;"
                class="absolute h-14 top-1 right-1 flex items-center rounded-xl bg-slate-800 py-1 px-6 border border-transparent text-center text-xl text-white transition-all shadow-sm hover:shadow focus:bg-slate-700 focus:shadow-none active:bg-slate-700 hover:bg-slate-700 active:shadow-none disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none cursor-pointer"
                type="button"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 mr-2">
                <path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 1 0 0 13.5 6.75 6.75 0 0 0 0-13.5ZM2.25 10.5a8.25 8.25 0 1 1 14.59 5.28l4.69 4.69a.75.75 0 1 1-1.06 1.06l-4.69-4.69A8.25 8.25 0 0 1 2.25 10.5Z" clip-rule="evenodd" />
                </svg>
                Search
            </button> 
        </div>
    </div>

    <div 
        x-show="openCamera"
        class="absolute inset-0 min-h-screen w-full flex z-10 bg-gray-900/50 px-10"
        x-cloak
    >
        <div 
            x-on:click.outside="openCamera = false"
            class="h-auto m-auto w-full max-w-xl shadow rounded-2xl bg-gray-100 p-5 relative"
            x-show="openCamera"
            x-transition
        >
            <div class="absolute -right-2 -top-3 shadow bg-white rounded-full h-8 w-8 grid place-content-center cursor-pointer hover:bg-gray-100" x-on:click="openCamera = false">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6 text-red-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </div>

            <div class="bg-white rounded-xl p-10">
                <p  class="text-center text-xl font-semibold mb-4">
                    Capture a Photo
                </p>
        
                <!-- Video Stream / Captured Image -->
                <div class="flex justify-center relative">
                    {{-- Countdown --}}
                    <div class="absolute z-10 text-white flex justify-center items-center h-full font-bold text-6xl">
                        <span id="camera-countdown" style="text-shadow: 2px 2px 4px rgba(18, 143, 165, 0.8);"></span>
                    </div>
                    <video id="camera" class="rounded-xl h-72" autoplay></video>
                    <div class=" hidden bg-gray-100 p-5 rounded-xl" id="capturedImageBorder">
                        <img class="rounded-xl shadow h-72 hidden" id="capturedImage" />
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
                    <button x-on:click="captureButtonClick()" id="capture" class="px-8 py-2 shadow rounded-xl bg-gray-100 hover:bg-gray-200 group cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-neutral-600 group-hover:text-blue-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                        </svg>
                    </button>

                    <div class="hidden space-x-4" id="reset">
                        <button class="px-8 py-3 bg-sky-600 font-semibold text-white rounded-xl hover:bg-sky-700 cursor-pointer">
                            Generate ID
                        </button>
                        <button x-on:click="resetButtonClick" class="px-8 py-3 bg-red-600 font-semibold text-white rounded-xl hover:bg-red-700 cursor-pointer">
                            Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>