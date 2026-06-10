@extends('layouts.app')

@push('styles')
    <style>
        .manual-sheet-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px 18px;
        }
        .manual-sheet-card {
            border: 2px solid #2f2f2f;
            border-radius: 0;
            box-shadow: none;
        }
        .manual-sheet-card .card-body {
            padding: 12px 14px;
        }
        .manual-sheet-title {
            font-size: 1.05rem;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: .15rem;
        }
        .manual-sheet-meta {
            color: #5f6f80;
            font-size: .8rem;
            margin-bottom: .6rem;
        }
        .manual-sheet-option {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 10px;
            align-items: center;
            padding: 4px 0;
        }
        .manual-sheet-option label {
            display: contents;
            cursor: pointer;
        }
        .manual-sheet-option input[type="checkbox"],
        .manual-sheet-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .manual-sheet-option-name {
            font-weight: 600;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .manual-sheet-option-tally {
            min-width: 48px;
            text-align: right;
            font-size: .85rem;
            color: #495a6b;
        }
        .manual-sheet-tick {
            width: 30px;
            height: 24px;
            border: 2px solid #444;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: #14532d;
            background: #fff;
        }
        .manual-sheet-option.is-selected .manual-sheet-tick {
            background: #e8f6ec;
            border-color: #1f7a4c;
        }
        .manual-sheet-option.is-selected .manual-sheet-tick::before {
            content: "✓";
            font-size: 1rem;
        }
        .manual-sheet-option.is-selected .manual-sheet-option-name {
            color: #14532d;
        }
        .manual-sheet-summary {
            position: sticky;
            top: 1rem;
        }
        .manual-sheet-summary .list-group-item {
            padding-left: 0;
            padding-right: 0;
        }
        @media (max-width: 991.98px) {
            .manual-sheet-grid {
                grid-template-columns: 1fr;
            }
            .manual-sheet-summary {
                position: static;
            }
        }
    </style>
@endpush

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4">
        <div>
            <h1 class="h2 mb-1">Manual Result Entry</h1>
            <p class="page-subtle mb-0">{{ $election->title }} | Tick one paper ballot exactly as it appears on the printed candidate sheet.</p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-lg-0">
            <a class="btn btn-outline-dark" href="{{ route('admin.elections.candidates.export-sheet', $election) }}" target="_blank">Open Candidate Sheet</a>
            <a class="btn btn-outline-dark" href="{{ route('admin.elections.candidates.export-contest-pdf', $election) }}" target="_blank">Open Candidate List PDF</a>
            @can('viewResults', $election)
                <a class="btn btn-outline-secondary" href="{{ route('admin.elections.results.index', $election) }}">Back to Results</a>
            @else
                <a class="btn btn-outline-secondary" href="{{ route('admin.elections.index') }}">Back to Elections</a>
            @endcan
        </div>
    </div>

    @if ($isReadOnly)
        <div class="alert alert-warning surface-card border-0 mb-4">
            This election is closed. The manual ballot sheet is now read only, so no more ballots can be entered.
        </div>
    @else
        <div class="alert alert-info surface-card border-0 mb-4">
            Tick the boxes the same way they were marked on the paper ballot, then save. Each save records one ballot and increases the candidate totals automatically.
        </div>
    @endif

    <div class="row g-4">
        <div class="col-xl-8">
            <form method="POST" action="{{ route('admin.elections.results.manual-entry.ballots.store', $election) }}" id="manualBallotForm">
                @csrf

                <div class="manual-sheet-grid">
                    @foreach ($contests as $contest)
                        @php
                            $contestField = "selections.{$contest->id}";
                            $selectedIds = array_map('intval', (array) old("selections.{$contest->id}", []));
                        @endphp
                        <section class="card manual-sheet-card contest-section" data-required="{{ $contest->required_selections }}" data-max="{{ $contest->max_selections }}">
                            <div class="card-body">
                                <div class="manual-sheet-title">{{ $contest->community?->name ?: $contest->name }}</div>
                                <div class="manual-sheet-meta">
                                    {{ $contest->name }} | Chagua 
                                    {{ $contest->required_selections === 1 ? 'mjumbe' : 'wajumbe' }} {{ $contest->required_selections }}
                                </div>

                                @foreach ($contest->candidates as $candidate)
                                    @php
                                        $selected = in_array($candidate->id, $selectedIds, true);
                                    @endphp
                                    <div class="manual-sheet-option {{ $selected ? 'is-selected' : '' }}">
                                        <label>
                                            <span class="manual-sheet-option-name">{{ $candidate->name }}</span>
                                            {{-- <span class="manual-sheet-option-tally">Total: {{ $candidate->manualTallies->first()?->votes ?? 0 }}</span> --}}
                                            <span class="manual-sheet-tick" aria-hidden="true"></span>
                                            <input
                                                class="manual-ballot-choice"
                                                type="checkbox"
                                                name="selections[{{ $contest->id }}][]"
                                                value="{{ $candidate->id }}"
                                                {{ $selected ? 'checked' : '' }}
                                                {{ $isReadOnly ? 'disabled' : '' }}
                                            >
                                        </label>
                                    </div>
                                @endforeach

                                @error($contestField)
                                    <div class="text-danger small mt-3">{{ $message }}</div>
                                @enderror
                            </div>
                        </section>
                    @endforeach
                </div>
            </form>
        </div>

        <div class="col-xl-4">
            <div class="card surface-card manual-sheet-summary">
                <div class="card-body">
                    <h2 class="h5 mb-3">Entry Summary</h2>
                    <div class="list-group list-group-flush mb-4">
                        <div class="list-group-item d-flex justify-content-between">
                            <span>Paper ballots entered</span>
                            <strong>{{ $enteredBallots }}</strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between">
                            <span>Contests on this ballot</span>
                            <strong>{{ $contests->count() }}</strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between">
                            <span>Ready contests now</span>
                            <strong id="readyContestCount">0</strong>
                        </div>
                    </div>

                    <div class="small text-muted mb-3">
                        Each contest must have exactly the required number of ticks before this ballot can be recorded.
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" type="submit" form="manualBallotForm" {{ $isReadOnly ? 'disabled' : '' }}>Record This Paper Ballot</button>
                        <button class="btn btn-outline-secondary" type="button" id="clearTickForm" {{ $isReadOnly ? 'disabled' : '' }}>Clear Current Ticks</button>
                        @can('viewResults', $election)
                            <a class="btn btn-outline-dark" href="{{ route('admin.elections.results.index', $election) }}">View Running Results</a>
                        @endcan
                    </div>

                    @can('viewResults', $election)
                        <hr>

                        <div class="small text-muted">
                            If you still want bulk numeric editing later, the backend route for total overwrite remains available.
                        </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const manualSections = Array.from(document.querySelectorAll('.contest-section'));
        const readyContestCount = document.getElementById('readyContestCount');

        const updateManualSelectionState = (section) => {
            section.querySelectorAll('.manual-sheet-option').forEach((option) => {
                const input = option.querySelector('.manual-ballot-choice');
                option.classList.toggle('is-selected', input.checked);
            });
        };

        const updateManualProgress = () => {
            let completed = 0;

            manualSections.forEach((section) => {
                const required = Number(section.dataset.required);
                const checked = section.querySelectorAll('.manual-ballot-choice:checked').length;
                if (checked === required) {
                    completed += 1;
                }
            });

            readyContestCount.textContent = completed;
        };

        manualSections.forEach((section) => {
            const max = Number(section.dataset.max);
            const inputs = Array.from(section.querySelectorAll('.manual-ballot-choice'));

            inputs.forEach((input) => {
                input.addEventListener('change', () => {
                    const checked = inputs.filter((item) => item.checked);

                    if (input.type === 'checkbox' && checked.length > max) {
                        input.checked = false;
                        alert(`You can tick up to ${max} candidate(s) in this contest.`);
                    }

                    updateManualSelectionState(section);
                    updateManualProgress();
                });
            });

            updateManualSelectionState(section);
        });

        document.getElementById('clearTickForm').addEventListener('click', () => {
            document.querySelectorAll('.manual-ballot-choice').forEach((input) => {
                input.checked = false;
            });

            manualSections.forEach((section) => updateManualSelectionState(section));
            updateManualProgress();
        });

        updateManualProgress();
    </script>
@endpush
