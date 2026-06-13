<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $election->title }} Ripoti ya Matokeo</title>
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
            max-width: 980px;
            margin: 0 auto;
            padding: 14px 18px 30px;
        }

        .toolbar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 12px;
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
            margin-bottom: 18px;
        }

        .sheet-title h1,
        .sheet-title h2,
        .sheet-title h3,
        .sheet-title p {
            margin: 0;
            line-height: 1.3;
        }

        .sheet-title h1,
        .sheet-title h2 {
            font-size: 18px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .sheet-title h3 {
            margin-top: 6px;
            font-size: 17px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .sheet-title p {
            margin-top: 8px;
            font-size: 13px;
        }

        .contest-card {
            border: 1.5px solid #333;
            padding: 12px 14px;
            margin-bottom: 14px;
            page-break-inside: avoid;
        }

        .contest-name {
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
            margin: 0 0 6px;
        }

        .contest-meta {
            margin: 0 0 10px;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th,
        td {
            border: 1px solid #444;
            padding: 7px 8px;
            font-size: 12px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f2f2f2;
            text-transform: uppercase;
        }

        .text-right {
            text-align: right;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 16px;
        }

        .summary-box {
            border: 1px solid #444;
            padding: 10px 12px;
        }

        .summary-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #444;
            margin-bottom: 4px;
        }

        .summary-value {
            font-size: 18px;
            font-weight: 800;
        }

        @page {
            size: A4 portrait;
            margin: 10mm 12mm;
        }

        @media print {
            .toolbar {
                display: none;
            }

            .page {
                max-width: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="toolbar">
            <button class="print-button" type="button" onclick="window.print()">Chapisha / Hifadhi PDF</button>
        </div>

        <div class="sheet-title">
            <h1>DAYOSISI YA MASHARIKI NA PWANI</h1>
            <h2>JIMBO LA MAGHARIBI</h2>
            <h2>USHARIKA WA TEMBONI</h2>
            <h3>RIPOTI YA MATOKEO YA {{ strtoupper($election->title) }}</h3>
            <p>
                Chanzo: {{ $summary['result_source'] === 'manual' ? 'Uingizaji wa matokeo kwa mkono' : 'Upigaji kura wa kidijitali' }}
                @if ($summary['result_source'] === 'manual')
                    | Karatasi za kura zilizoingizwa: {{ $summary['manual_ballots_entered'] }}
                    | Kura zilizoharibika: {{ $summary['destroyed_manual_entries'] }}
                @else
                    | Kura zilizopigwa: {{ $summary['votes_cast'] }}
                    | Ushiriki: {{ $summary['turnout_percentage'] }}%
                @endif
            </p>
        </div>

        <div class="summary-grid">
            <div class="summary-box">
                <div class="summary-label">Wapiga Kura Waliosajiliwa</div>
                <div class="summary-value">{{ $summary['registered_voters'] }}</div>
            </div>
            <div class="summary-box">
                <div class="summary-label">Karatasi za Kura</div>
                <div class="summary-value">{{ $summary['result_source'] === 'manual' ? $summary['manual_ballots_entered'] : $summary['votes_cast'] }}</div>
            </div>
            <div class="summary-box">
                <div class="summary-label">Kura Zilizoharibika</div>
                <div class="summary-value">{{ $summary['result_source'] === 'manual' ? $summary['destroyed_manual_entries'] : 0 }}</div>
            </div>
        </div>

        @foreach ($contestResults as $contestResult)
            <section class="contest-card">
                <h4 class="contest-name">{{ strtoupper($contestResult['contest']->name) }}</h4>
                <p class="contest-meta">
                    Nafasi: {{ $loop->iteration }} kati ya {{ count($contestResults) }}
                    | Jumla ya kura: {{ $contestResult['total_votes'] }}
                    | Kura zilizoharibika: {{ $contestResult['destroyed_entries'] }}
                    | Kura za juu zaidi: {{ $contestResult['top_votes'] }}
                    | Wenye kura nyingi:
                    {{ $contestResult['top_candidates']->isNotEmpty() ? strtoupper($contestResult['top_candidates']->join(', ')) : 'HAKUNA' }}
                </p>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 60px;">Na.</th>
                            <th style="width: 70px;">Nafasi</th>
                            <th>Mgombea</th>
                            <th style="width: 100px;">Kura</th>
                            <th style="width: 120px;">Hali</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($contestResult['results'] as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $row['ranking'] }}</td>
                                <td>{{ strtoupper($row['candidate']->name) }}</td>
                                <td class="text-right">{{ $row['votes'] }}</td>
                                <td>
                                    @if ($row['is_tied_winner'])
                                        Mshindi wa Sare
                                    @elseif ($row['is_winner'])
                                        Mshindi
                                    @else
                                        Mshiriki
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endforeach
    </div>
</body>
</html>
