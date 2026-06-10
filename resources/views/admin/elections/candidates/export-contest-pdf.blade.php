<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $election->title }} Candidate List</title>
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
            max-width: 960px;
            margin: 0 auto;
            padding: 14px 16px 24px;
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
            margin-bottom: 18px;
        }

        .sheet-title h1,
        .sheet-title h2,
        .sheet-title h3 {
            margin: 0;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .sheet-title h1,
        .sheet-title h2 {
            font-size: 18px;
            font-weight: 800;
        }

        .sheet-title h3 {
            margin-top: 6px;
            font-size: 17px;
            font-weight: 800;
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
            margin: 0 0 10px;
        }

        .candidate-list {
            margin: 0;
            padding-left: 22px;
        }

        .candidate-list li {
            margin-bottom: 6px;
            font-size: 14px;
            line-height: 1.5;
            text-transform: uppercase;
        }

        .candidate-list li:last-child {
            margin-bottom: 0;
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
            <button class="print-button" type="button" onclick="window.print()">Print / Save PDF</button>
        </div>

        <div class="sheet-title">
            <h1>DAYOSISI YA MASHARIKI NA PWANI</h1>
            <h2>JIMBO LA MAGHARIBI</h2>
            <h2>USHARIKA WA TEMBONI</h2>
            <h3>ORODHA YA MAJINA YALIYOPENDEKEZWA KWENYE {{ strtoupper($election->title) }}</h3>
        </div>

        @foreach ($contests as $contest)
            <section class="contest-card">
                <h4 class="contest-name">{{ strtoupper($contest->display_name) }}</h4>

                <ol class="candidate-list">
                    @foreach ($contest->candidates as $candidate)
                        <li>{{ strtoupper($candidate->name) }}</li>
                    @endforeach
                </ol>
            </section>
        @endforeach
    </div>
</body>
</html>
