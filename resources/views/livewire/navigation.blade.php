<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div>
    <nav class="h-20 w-full border-b border-neutral-300 z-10 bg-white">
        <div class="max-w-4xl mx-auto px-10 h-full w-full gap-16 flex items-center">
            <p class="text-neutral-800 text-2xl font-bold">DSWD</p>
            <div class="space-x-6">
                <a href="/tutorial" class="text-neutral-600 font-semibold hover:text-neutral-700" wire:current="!font-bold !text-neutral-700">
                    Tutorial
                </a>
                <a href="/search" class="text-neutral-600 font-semibold hover:text-neutral-700" wire:current="!font-bold !text-neutral-700">
                    ID Generation
                </a>
            </div>
        </div>
    </nav>
</div>
