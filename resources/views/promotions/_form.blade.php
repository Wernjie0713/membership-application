@php
    $promotionTiers = old('tiers', $tiers);
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <x-input-label for="name" value="Promotion Name" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $promotion->name)" required />
    </div>
    <div>
        <x-input-label for="status" value="Status" />
        <select id="status" name="status" class="mt-1 block w-full rounded-lg border-chip-gray text-uber-black shadow-sm focus:border-uber-black focus:ring-uber-black">
            @foreach (['draft', 'active', 'inactive'] as $status)
                <option value="{{ $status }}" @selected(old('status', $promotion->status) === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <x-input-label for="start_date" value="Start Date" />
        <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" :value="old('start_date', optional($promotion->start_date)->toDateString())" />
    </div>
    <div>
        <x-input-label for="end_date" value="End Date" />
        <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" :value="old('end_date', optional($promotion->end_date)->toDateString())" />
    </div>
    <div class="md:col-span-2">
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-lg border-chip-gray text-uber-black shadow-sm focus:border-uber-black focus:ring-uber-black">{{ old('description', $promotion->description) }}</textarea>
    </div>
</div>

<div class="mt-10 pt-8 border-t border-chip-gray">
    <h3 class="text-2xl font-bold text-uber-black">Reward Tiers</h3>
    <div class="mt-6 space-y-6">
        @foreach ($promotionTiers as $index => $tier)
            <div class="grid gap-6 rounded-[8px] border border-chip-gray p-6 bg-white md:grid-cols-5">
                <div>
                    <x-input-label :for="'tier_'.$index.'_level'" value="Tier" />
                    <x-text-input :id="'tier_'.$index.'_level'" :name="'tiers['.$index.'][tier]'" type="number" min="1" class="mt-1 block w-full" :value="$tier['tier']" />
                </div>
                <div>
                    <x-input-label :for="'tier_'.$index.'_threshold'" value="Threshold" />
                    <x-text-input :id="'tier_'.$index.'_threshold'" :name="'tiers['.$index.'][referral_threshold]'" type="number" min="1" class="mt-1 block w-full" :value="$tier['referral_threshold']" />
                </div>
                <div>
                    <x-input-label :for="'tier_'.$index.'_amount'" value="Reward Amount" />
                    <x-text-input :id="'tier_'.$index.'_amount'" :name="'tiers['.$index.'][reward_amount]'" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="$tier['reward_amount']" />
                </div>
                <div>
                    <x-input-label :for="'tier_'.$index.'_currency'" value="Currency" />
                    <x-text-input :id="'tier_'.$index.'_currency'" :name="'tiers['.$index.'][currency]'" type="text" class="mt-1 block w-full" :value="$tier['currency']" />
                </div>
                <div>
                    <x-input-label :for="'tier_'.$index.'_step'" value="Step Increment" />
                    <x-text-input :id="'tier_'.$index.'_step'" :name="'tiers['.$index.'][step_increment]'" type="number" min="1" class="mt-1 block w-full" :value="$tier['step_increment']" />
                    <label class="mt-3 inline-flex items-center gap-3 text-sm font-medium text-uber-black">
                        <input type="hidden" name="tiers[{{ $index }}][is_recurring]" value="0">
                        <input type="checkbox" name="tiers[{{ $index }}][is_recurring]" value="1" class="h-5 w-5 rounded border-chip-gray text-uber-black shadow-sm focus:ring-uber-black" @checked($tier['is_recurring'])>
                        Recurring
                    </label>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="mt-10 flex items-center gap-4">
    <x-primary-button>{{ $submitLabel }}</x-primary-button>
    <a href="{{ route('promotions.index') }}" class="text-sm font-medium text-body-gray hover:text-uber-black transition">Cancel</a>
</div>
