<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $election->title }} Candidate Sheet</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #111;
            background: #fff;
        }

        .page {
            width: 100%;
            max-width: 1180px;
            margin: 0 auto;
            padding: 12px 14px 18px;
        }

        .toolbar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 10px;
        }

        .print-button {
            border: 1px solid #111;
            background: #111;
            color: #fff;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }

        .sheet-title {
            text-align: center;
            margin-bottom: 12px;
        }

        .sheet-title h1,
        .sheet-title h2,
        .sheet-title p {
            margin: 0;
        }

        .sheet-title h1 {
            font-size: 22px;
            font-weight: 800;
            text-transform: uppercase;
            line-height: 1.15;
        }

        .sheet-title h2 {
            margin-top: 4px;
            font-size: 20px;
            font-weight: 800;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .sheet-title p {
            margin-top: 4px;
            font-size: 15px;
            font-weight: 700;
        }

        .contest-grid {
            column-count: 2;
            column-gap: 18px;
        }

        .contest-card {
            border: 2px solid #333;
            padding: 8px 12px 10px;
            break-inside: avoid;
            margin-bottom: 12px;
            width: 100%;
            display: inline-block;
        }

        .contest-name {
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .contest-meta {
            font-size: 11px;
            margin-bottom: 6px;
        }

        .candidate-row {
            display: grid;
            grid-template-columns: 1fr 28px;
            gap: 8px;
            align-items: center;
            margin-bottom: 2px;
        }

        .candidate-row:last-child {
            margin-bottom: 0;
        }

        .candidate-name {
            font-size: 14px;
            line-height: 1.15;
            text-transform: uppercase;
        }

        .tick-box {
            width: 28px;
            height: 22px;
            border: 2px solid #444;
        }

        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        @media (max-width: 768px) {
            .contest-grid {
                column-count: 1;
            }

            .sheet-title h1 {
                font-size: 18px;
            }

            .sheet-title h2 {
                font-size: 16px;
            }

            .sheet-title p {
                font-size: 13px;
            }
        }

        @media print {
            .toolbar {
                display: none;
            }

            .page {
                max-width: none;
                padding: 0;
            }

            .contest-grid {
                column-count: 2;
                column-gap: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="toolbar">
            <button class="print-button" type="button" onclick="window.print()">Print / Save PDF</button>
        </div>

        <div class="sheet-title">
            <h1>
                {{ strtoupper($election->churchGroup?->name ?? config('app.name', 'Election System')) }}
            </h1>
            <h2>
                {{ strtoupper($election->title) }}
                @if ($election->start_at)
                    {{ $election->start_at->format('d.m.Y') }}
                @endif
            </h2>
            <p>{{ $instructionLine }}</p>
        </div>

        <div class="contest-grid">
            @foreach ($contests as $contest)
                <section class="contest-card">
                    <div class="contest-name">{{ strtoupper($contest->display_name) }}</div>
                    <div class="contest-meta">
                        Chagua {{ $contest->required_selections }}
                        {{ $contest->required_selections === 1 ? 'mjumbe' : 'wajumbe' }}
                    </div>

                    @foreach ($contest->candidates as $candidate)
                        <div class="candidate-row">
                            <div class="candidate-name">{{ strtoupper($candidate->name) }}</div>
                            <div class="tick-box" aria-hidden="true"></div>
                        </div>
                    @endforeach
                </section>
            @endforeach
        </div>
    </div>
</body>
</html>
