<?php

namespace App\Http\Requests\Admin;

use App\Models\Election;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ManualResultEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'votes' => ['required', 'array'],
            'votes.*' => ['required', 'array'],
            'votes.*.*' => ['nullable', 'integer', 'min:0'],
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

                $expectedContests = $election->contests()->with('candidates')->where('is_active', true)->get();
                $submittedVotes = $this->input('votes', []);

                foreach ($expectedContests as $contest) {
                    if (! array_key_exists((string) $contest->id, $submittedVotes) && ! array_key_exists($contest->id, $submittedVotes)) {
                        $validator->errors()->add("votes.{$contest->id}", "Enter vote totals for {$contest->name}.");
                        continue;
                    }

                    $contestVotes = $submittedVotes[$contest->id] ?? $submittedVotes[(string) $contest->id] ?? [];

                    foreach ($contest->candidates as $candidate) {
                        $candidateKey = (string) $candidate->id;

                        if (! array_key_exists($candidateKey, $contestVotes) && ! array_key_exists($candidate->id, $contestVotes)) {
                            $validator->errors()->add("votes.{$contest->id}.{$candidate->id}", "Enter a vote total for {$candidate->name}.");
                        }
                    }
                }
            },
        ];
    }
}
