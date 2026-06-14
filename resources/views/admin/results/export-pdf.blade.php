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
            font-size: 12px;
            
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
            margin-bottom: 10px;
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
            border: 0;
            padding: 0;
            margin: 0;
            page-break-inside: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
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

            .sheet-title {
                page-break-after: avoid;
            }

            table {
                page-break-inside: auto;
            }

            tr,
            td,
            th {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="toolbar">
            <button class="print-button" type="button" onclick="window.print()">Chapisha / Hifadhi PDF</button>
        </div>

        <div class="sheet-title ">
            <h4 style="margin: 0px">KKKT DAYOSISI YA MASHARIKI NA PWANI</h4>
            <h4 style="margin: 0px">JIMBO LA MAGHARIBI,USHARIKA WA TEMBONI</h4>
            <h4 style="margin: 0px">ORODHA YA WALIOCHAGULIWA KUWA WAZEE WA KANISA TAREHE {{ now()->format('d/m/Y') }}</h4>
        </div>

        @php
            $winnerRows = collect($contestResults)
                ->flatMap(fn (array $contestResult) => collect($contestResult['results'])
                    ->filter(fn (array $row) => $row['is_winner'] || $row['is_tied_winner'])
                    ->values())
                ->values();
        @endphp

        <section class="contest-card">
            <table>
                <thead>
                    <tr>
                        <th style="width: 60px;">Na.</th>
                        <th>Jina</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($winnerRows as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ strtoupper($row['candidate']->name) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2">Hakuna washindi waliopatikana.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>
