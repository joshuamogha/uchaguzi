<?php

namespace App\Http\Requests\Admin;

use App\Models\Election;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ManualBallotEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'selections' => ['required', 'array'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var Election|null $election */
                $election = $this->route('election');

                if (! $election) {
                    return;
                }

                $contests = $election->contests()
                    ->where('is_active', true)
                    ->with(['candidates' => fn ($query) => $query->where('is_active', true)])
                    ->get();

                $submittedSelections = $this->input('selections', []);

                foreach ($contests as $contest) {
                    $selectedIds = $submittedSelections[$contest->id] ?? $submittedSelections[(string) $contest->id] ?? [];
                    $selectedIds = array_values(array_unique(array_map('intval', (array) $selectedIds)));

                    if (count($selectedIds) !== (int) $contest->required_selections) {
                        $validator->errors()->add("selections.{$contest->id}", "Tick exactly {$contest->required_selections} candidate(s) for {$contest->name}.");
                        continue;
                    }

                    if (count($selectedIds) < (int) $contest->min_selections || count($selectedIds) > (int) $contest->max_selections) {
                        $validator->errors()->add("selections.{$contest->id}", "The number of ticks for {$contest->name} is outside the allowed range.");
                        continue;
                    }

                    $allowedIds = $contest->candidates->pluck('id')->all();

                    foreach ($selectedIds as $candidateId) {
                        if (! in_array($candidateId, $allowedIds, true)) {
                            $validator->errors()->add("selections.{$contest->id}", "One or more ticks are invalid for {$contest->name}.");
                            continue 2;
                        }
                    }
                }
            },
        ];
    }
}
