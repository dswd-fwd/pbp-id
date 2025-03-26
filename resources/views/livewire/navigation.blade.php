<?php
use Intervention\Image\Facades\Image;
use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

{{-- <div>
    <nav class="h-20 w-full border-b border-neutral-300 z-10 bg-white">
        <div class="max-w-4xl mx-auto px-10 h-full w-full gap-8 sm:gap-16 flex items-center">
            {{-- <p class="text-neutral-800 text-2xl font-bold">DSWD</p> --}}
            {{-- <img src="{{ asset('img/PBP.png') }}" alt=""  class="max-w-16">
            <div class="space-x-2 sm:space-x-6"> --}}
                {{-- <a href="{{ route('signpad.index') }}" wire:navigate class="text-neutral-600 font-semibold hover:text-neutral-700" wire:current="!font-bold !text-neutral-700">
                    Signature
                </a> --}}
                {{-- <a href="{{ route('home') }}" wire:navigate class="text-neutral-600 font-semibold hover:text-neutral-700" wire:current="!font-bold !text-neutral-700">
                    ID Generation
                </a>
            </div>
        </div>
    </nav> --}}
{{-- </div> --}}

<div>
    <nav class="h-20 w-full border-b border-neutral-200 shadow-sm bg-white z-10">
        <div class="max-w-6xl mx-auto px-6 sm:px-10 h-full flex items-center justify-between">
            <!-- Logo Section -->
            <div class="flex items-center gap-12">
                <img src="{{ asset('img/PBP.png') }}" alt="Logo" class="w-60 h-18 object-cover">
                <p class="text-neutral-800 text-2xl font-normal tracking-tight hidden sm:block">Pamilya sa Bagong Pilipinas ID Card Generation</p>
            </div>

            <!-- Navigation Links -->
            {{-- <div class="flex gap-4 sm:gap-8">
                <a href="{{ route('home') }}" 
                   wire:navigate 
                   class="text-neutral-600 font-semibold transition duration-300 hover:text-sky-600 hover:underline underline-offset-4"
                   wire:current="!font-bold !text-neutral-700">
                    ID Generation
                </a>
                <a href="{{ route('signpad.index') }}" 
                   wire:navigate 
                   class="text-neutral-600 font-semibold transition duration-300 hover:text-sky-600 hover:underline underline-offset-4"
                   wire:current="!font-bold !text-neutral-700">
                    Signature
                </a>
            </div> --}}
        </div>
    </nav>
</div>



