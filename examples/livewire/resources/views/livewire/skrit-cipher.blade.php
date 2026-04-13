<div class="space-y-4">
    <div>
        <label for="input" class="block text-sm font-medium">Input</label>
        <textarea id="input" wire:model.live.debounce.300ms="input" rows="4" class="mt-1 w-full rounded border p-2"></textarea>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
            <label class="block text-sm font-medium">Mode</label>
            <select wire:model.live="mode" class="mt-1 w-full rounded border p-2">
                <option value="auto">auto</option>
                <option value="satro">satro</option>
                <option value="utro">utro</option>
                <option value="leet">leet</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium">Leet Base</label>
            <select wire:model.live="leetBase" class="mt-1 w-full rounded border p-2">
                <option value="auto">auto</option>
                <option value="satro">satro</option>
                <option value="utro">utro</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium">Leet Profile</label>
            <select wire:model.live="leetProfile" class="mt-1 w-full rounded border p-2">
                <option value="basic">basic</option>
                <option value="readable">readable</option>
                <option value="full">full</option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div>
            <label class="block text-sm font-medium">Cyrillic c target</label>
            <select wire:model.live="plainCTarget" class="mt-1 w-full rounded border p-2">
                <option value="ц">ц</option>
                <option value="ч">ч</option>
                <option value="ћ">ћ</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium">ZA Style</label>
            <select wire:model.live="zaStyle" class="mt-1 w-full rounded border p-2">
                <option value="24">24</option>
                <option value="z4">z4</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium">NJE Style</label>
            <select wire:model.live="njeStyle" class="mt-1 w-full rounded border p-2">
                <option value="n73">n73</option>
                <option value="nj3">nj3</option>
                <option value="њ">њ</option>
            </select>
        </div>

        <div class="flex items-end pb-2">
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" wire:model.live="softTjToCyrillic">
                <span class="text-sm">soft tj -> ћ</span>
            </label>
        </div>
    </div>

    <div>
        <p class="text-sm text-gray-600">Resolved mode: <strong>{{ $resolvedMode }}</strong></p>
        <div class="mt-1 rounded border bg-gray-50 p-3 font-mono">
            {{ $output }}
        </div>
    </div>
</div>
