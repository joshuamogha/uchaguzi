@extends('layouts.app')

@push('styles')
    <style>
        .ballot-candidate-card {
            flex-direction: row;
            align-items: stretch;
            border: 1px solid #d9e3ec;
            background: #ffffff;
        }
        .ballot-candidate-card .candidate-photo {
            width: 180px;
            min-width: 180px;
            height: 100%;
            min-height: 220px;
            border-radius: .85rem 0 0 .85rem;
        }
        .ballot-candidate-card .card-body {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .ballot-candidate-card .form-check-input {
            width: 1.5rem;
            height: 1.5rem;
            min-width: 1.5rem;
            margin-top: .05rem;
            margin-right: .45rem;
            border: 2px solid #7f93a8;
            box-shadow: none;
        }
        .ballot-candidate-card .form-check-input:checked {
            background-color: #1f7a4c;
            border-color: #1f7a4c;
        }
        .ballot-candidate-card .form-check-input:focus {
            border-color: #1f7a4c;
            box-shadow: 0 0 0 .2rem rgba(31, 122, 76, .18);
        }
        .ballot-candidate-card.selected {
            background: linear-gradient(135deg, rgba(31, 122, 76, .14), rgba(243, 182, 31, .12));
            border-color: #1f7a4c;
            box-shadow: 0 0 0 .2rem rgba(31, 122, 76, .16), 0 .9rem 1.6rem rgba(19, 34, 56, .08);
        }
        .ballot-candidate-card.selected .fw-semibold {
            color: #14532d;
        }
        .ballot-candidate-card.selected .small.text-muted {
            color: #35586f !important;
        }
        @media (max-width: 575.98px) {
            .ballot-candidate-card {
                flex-direction: column;
            }
            .ballot-candidate-card .candidate-photo {
                width: 100%;
                min-width: 100%;
                min-height: 220px;
                border-radius: .85rem .85rem 0 0;
            }
        }
    </style>
@endpush

@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card hero-card mb-4">
                <div class="card-body p-4">
                    <p class="text-uppercase small fw-semibold mb-2">Ballot</p>
                    <h1 class="h2 mb-2">{{ $election->title }}</h1>
                    <p class="mb-0 opacity-75">Select the required number of candidates in each contest before proceeding to review.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('vote.review', $election) }}" id="ballotForm">
                @csrf
                @foreach ($election->contests as $contest)
                    @php $selected = $savedSelections[$contest->id] ?? []; @endphp
                    <section
                        class="card surface-card mb-4 contest-section d-none"
                        data-required="{{ $contest->required_selections }}"
                        data-max="{{ $contest->max_selections }}"
                        data-step="{{ $loop->index }}"
                    >
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <div class="small text-uppercase text-muted fw-semibold mb-2">Contest {{ $loop->iteration }} of {{ $election->contests->count() }}</div>
                                    <h2 class="h4 mb-1">{{ $contest->name }}</h2>
                                    <p class="text-muted mb-0">Select exactly {{ $contest->required_selections }} candidate(s)</p>
                                </div>
                                <span class="badge contest-badge">Max {{ $contest->max_selections }}</span>
                            </div>

                            <div class="row g-4">
                                @foreach ($contest->candidates as $candidate)
                                    <div class="col-md-6">
                                        <label class="card selection-card ballot-candidate-card h-100 cursor-pointer">
                                            <img src="{{ $candidate->photo_url }}" alt="{{ $candidate->name }}" class="candidate-photo">
                                            <div class="card-body">
                                                <div class="form-check">
                                                    <input
                                                        class="form-check-input contest-choice"
                                                        type="{{ $contest->required_selections === 1 ? 'radio' : 'checkbox' }}"
                                                        name="selections[{{ $contest->id }}]{{ $contest->required_selections === 1 ? '' : '[]' }}"
                                                        value="{{ $candidate->id }}"
                                                        {{ in_array($candidate->id, $selected, true) ? 'checked' : '' }}
                                                    >
                                                    <span class="fw-semibold">{{ $candidate->name }}</span>
                                                </div>
                                                <p class="small text-muted mt-2 mb-0">{{ $candidate->bio ?: 'Candidate bio not provided.' }}</p>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4 pt-2">
                                <button
                                    class="btn btn-outline-secondary step-prev"
                                    type="button"
                                    {{ $loop->first ? 'disabled' : '' }}
                                >
                                    Previous
                                </button>

                                <div class="small text-muted text-center">
                                    You can move between contests before the final review.
                                </div>

                                @if ($loop->last)
                                    <button class="btn btn-primary step-review" type="submit">Review Selections</button>
                                @else
                                    <button class="btn btn-primary step-next" type="button">Next Contest</button>
                                @endif
                            </div>
                        </div>
                    </section>
                @endforeach
            </form>
        </div>

        <div class="col-lg-4">
            <div class="card surface-card sticky-summary">
                <div class="card-body p-4">
                    <h2 class="h5">Progress</h2>
                    <div class="progress mb-3" role="progressbar">
                        <div class="progress-bar" id="progressBar" style="width: 0%"></div>
                    </div>
                    <p class="mb-3"><strong id="progressText">0</strong> of {{ $election->contests->count() }} contests completed</p>
                    <p class="mb-3"><strong id="stepText">Contest 1</strong> of {{ $election->contests->count() }}</p>
                    <div class="small text-muted">Your ballot is only submitted after you confirm it on the review screen.</div>
                    <hr>
                    <div class="small text-muted mb-2">Contest Navigator</div>
                    <div class="d-grid gap-2" id="contestNavigator">
                        @foreach ($election->contests as $contest)
                            <button type="button" class="btn btn-outline-secondary text-start navigator-btn" data-target-step="{{ $loop->index }}">
                                {{ $loop->iteration }}. {{ $contest->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const sections = Array.from(document.querySelectorAll('.contest-section'));
        const navigatorButtons = Array.from(document.querySelectorAll('.navigator-btn'));
        let activeStep = 0;

        document.querySelectorAll('.contest-section').forEach((section) => {
            const max = Number(section.dataset.max);
            const inputs = section.querySelectorAll('.contest-choice');

            const updateCardState = () => {
                inputs.forEach((input) => input.closest('.selection-card').classList.toggle('selected', input.checked));
            };

            inputs.forEach((input) => {
                input.addEventListener('change', () => {
                    const checked = Array.from(inputs).filter((item) => item.checked);
                    if (input.type === 'checkbox' && checked.length > max) {
                        input.checked = false;
                        alert(`You can select up to ${max} candidate(s) in this contest.`);
                    }
                    updateCardState();
                    updateProgress();
                });
            });

            updateCardState();
        });

        const updateProgress = () => {
            let completed = 0;

            sections.forEach((section) => {
                const required = Number(section.dataset.required);
                const checked = section.querySelectorAll('.contest-choice:checked').length;
                if (checked === required) {
                    completed += 1;
                }
            });

            const percent = sections.length ? (completed / sections.length) * 100 : 0;
            document.getElementById('progressBar').style.width = `${percent}%`;
            document.getElementById('progressText').textContent = completed;
        };

        const updateNavigator = () => {
            navigatorButtons.forEach((button, index) => {
                const section = sections[index];
                const required = Number(section.dataset.required);
                const checked = section.querySelectorAll('.contest-choice:checked').length;
                const isComplete = checked === required;

                button.classList.remove('btn-outline-secondary', 'btn-success', 'btn-dark');

                if (index === activeStep) {
                    button.classList.add('btn-dark');
                } else if (isComplete) {
                    button.classList.add('btn-success');
                } else {
                    button.classList.add('btn-outline-secondary');
                }
            });
        };

        const showStep = (step) => {
            activeStep = Math.max(0, Math.min(step, sections.length - 1));

            sections.forEach((section, index) => {
                section.classList.toggle('d-none', index !== activeStep);
            });

            document.getElementById('stepText').textContent = `Contest ${activeStep + 1}`;
            updateNavigator();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

        document.querySelectorAll('.step-next').forEach((button) => {
            button.addEventListener('click', () => showStep(activeStep + 1));
        });

        document.querySelectorAll('.step-prev').forEach((button) => {
            button.addEventListener('click', () => showStep(activeStep - 1));
        });

        navigatorButtons.forEach((button) => {
            button.addEventListener('click', () => showStep(Number(button.dataset.targetStep)));
        });

        updateProgress();
        updateNavigator();
        showStep(0);
    </script>
@endpush
