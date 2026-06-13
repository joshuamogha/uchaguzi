@extends('layouts.app')

@push('styles')
    <style>
        .candidates-page {
            display: grid;
            gap: 1.75rem;
        }
        .candidates-hero {
            border: 1px solid rgba(19, 34, 56, .08);
            background:
                linear-gradient(135deg, rgba(21, 76, 121, .96), rgba(31, 122, 76, .88)),
                radial-gradient(circle at top right, rgba(255, 255, 255, .22), transparent 40%);
            color: #fff;
            border-radius: 1.5rem;
            overflow: hidden;
        }
        .candidates-hero .card-body {
            padding: 2rem;
        }
        .candidates-kicker {
            letter-spacing: .12em;
            text-transform: uppercase;
            font-size: .78rem;
            font-weight: 700;
            opacity: .84;
            margin-bottom: .65rem;
        }
        .candidates-subtle {
            color: rgba(255, 255, 255, .82);
            max-width: 760px;
            margin-bottom: 0;
        }
        .contest-section {
            border: 1px solid rgba(19, 34, 56, .08);
            border-radius: 1.35rem;
            background: linear-gradient(180deg, rgba(255, 255, 255, .98), rgba(246, 249, 252, .98));
            box-shadow: 0 1.1rem 2rem rgba(19, 34, 56, .06);
            overflow: hidden;
        }
        .contest-header {
            padding: 1.35rem 1.5rem;
            border-bottom: 1px solid rgba(19, 34, 56, .07);
            background: rgba(255, 255, 255, .9);
        }
        .contest-header-inner {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
        }
        .contest-title {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 800;
            color: #16273d;
        }
        .contest-instruction {
            margin: .3rem 0 0;
            color: #5a6b7d;
        }
        .contest-cap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 88px;
            padding: .6rem .9rem;
            border-radius: 999px;
            background: rgba(21, 76, 121, .08);
            color: #154c79;
            font-weight: 700;
            font-size: .85rem;
        }
        .candidate-list {
            padding: 1rem;
            display: grid;
            gap: 1rem;
        }
        .candidate-card {
            display: grid;
            grid-template-columns: 124px minmax(0, 1fr);
            gap: 1rem;
            align-items: stretch;
            padding: .9rem;
            border: 1px solid rgba(19, 34, 56, .08);
            border-radius: 1.1rem;
            background: #fff;
            box-shadow: 0 .6rem 1.2rem rgba(19, 34, 56, .05);
        }
        .candidate-card-media {
            height: 124px;
            border-radius: .95rem;
            overflow: hidden;
            background: linear-gradient(180deg, #eef3f7, #dde7ef);
        }
        .candidate-card-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center center;
            display: block;
        }
        .candidate-card-body {
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .candidate-card-name {
            margin: 0 0 .45rem;
            font-size: 1.08rem;
            font-weight: 800;
            color: #16273d;
        }
        .candidate-card-bio {
            margin: 0;
            color: #617285;
            line-height: 1.55;
        }
        @media (max-width: 767.98px) {
            .candidates-hero .card-body {
                padding: 1.4rem;
            }
            .contest-header {
                padding: 1.1rem 1.1rem .95rem;
            }
            .contest-header-inner {
                flex-direction: column;
                align-items: stretch;
            }
            .candidate-list {
                padding: .9rem;
            }
            .candidate-card {
                grid-template-columns: 1fr;
            }
            .candidate-card-media {
                height: 190px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="candidates-page">
        <section class="card candidates-hero">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                    <div>
                        <div class="candidates-kicker">Election Candidates</div>
                        <h1 class="display-6 fw-bold mb-2">{{ $election->title }}</h1>
                        <p class="candidates-subtle">{{ $election->description ?: 'Review each contest carefully and become familiar with the candidates before voting.' }}</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('home') }}" class="btn btn-light">Back to Elections</a>
                        <a href="{{ route('vote.verify.form') }}" class="btn btn-warning">Proceed to Vote</a>
                    </div>
                </div>
            </div>
        </section>

        @foreach ($election->contests as $contest)
            <section class="contest-section">
                <div class="contest-header">
                    <div class="contest-header-inner">
                        <div>
                            <h2 class="contest-title">{{ $contest->name }}</h2>
                            <p class="contest-instruction">Choose {{ $contest->required_selections }} candidate(s) for this contest.</p>
                        </div>
                        <span class="contest-cap">Max {{ $contest->max_selections }}</span>
                    </div>
                </div>

                <div class="candidate-list">
                    @foreach ($contest->candidates as $candidate)
                        <article class="candidate-card">
                            <div class="candidate-card-media">
                                <img src="{{ $candidate->photo_url }}" alt="{{ $candidate->name }}">
                            </div>
                            <div class="candidate-card-body">
                                <h3 class="candidate-card-name">{{ $candidate->name }}</h3>
                                <p class="candidate-card-bio">{{ $candidate->bio ?: 'Candidate profile not provided.' }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
@endsection
