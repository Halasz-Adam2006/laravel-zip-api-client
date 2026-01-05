<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Cities - {{ $county->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }
        
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #3b82f6;
        }
        
        .logo {
            max-width: 80px;
            height: auto;
        }
        
        .header-text {
            flex: 1;
            text-align: center;
        }
        
        .header-text h1 {
            font-size: 20px;
            color: #3b82f6;
            margin-bottom: 0.25rem;
        }
        
        .header-text p {
            font-size: 11px;
            color: #666;
        }
        
        .content {
            margin-bottom: 2rem;
        }
        
        .content h2 {
            font-size: 16px;
            margin-bottom: 1rem;
            color: #1f2937;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        
        thead {
            background-color: #3b82f6;
            color: white;
        }
        
        th {
            padding: 0.75rem;
            text-align: left;
            font-weight: 600;
            border: 1px solid #ddd;
        }
        
        td {
            padding: 0.75rem;
            border: 1px solid #ddd;
        }
        
        tbody tr:nth-child(even) {
            background-color: #f3f4f6;
        }
        
        tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        
        .footer {
            margin-top: 3rem;
            padding-top: 1rem;
            border-top: 2px solid #3b82f6;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        
        .footer p {
            margin: 0.25rem 0;
        }
        
        .page-number {
            text-align: right;
            margin-top: 1rem;
            font-size: 10px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <div style="width: 80px;">
            @if(file_exists(public_path('img/logo.png')))
                <img src="{{ public_path('img/logo.png') }}" alt="Logo" class="logo">
            @else
                <div style="width: 80px; height: 80px; background-color: #3b82f6; color: white; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-weight: bold;">LOGO</div>
            @endif
        </div>
        <div class="header-text">
            <h1>{{ config('app.name', 'ZipCodes') }}</h1>
            <p>Városok listája</p>
        </div>
    </div>

    <div class="content">
        <h2>Városok a "{{ $letter }}" betűvel - {{ $county->name }} megye</h2>
        
        @if($cities->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Irányítószám</th>
                        <th>Város neve</th>
                        <th>Megye</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cities as $city)
                        <tr>
                            <td>{{ is_array($city) ? $city['id'] : $city->id }}</td>
                            <td>{{ is_array($city) ? $city['zip'] : $city->zip }}</td>
                            <td>{{ is_array($city) ? $city['city'] : $city->city }}</td>
                            <td>{{ is_array($city) ? $city['county']['name'] : $city->county->name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="color: #b91c1c; font-style: italic;">Nincs város a "{{ $letter }}" betűvel ebben a megyében.</p>
        @endif
    </div>

    <div class="footer">
        <p>{{ config('app.name', 'ZipCodes') }} v{{ env('APP_VERSION', '1.0.0') }}</p>
        <p>Lekérdezés dátuma: {{ now()->format('Y. m. d. H:i:s') }}</p>
        <div class="page-number">
            <script type="text/php">
                if (isset($pdf)) {
                    $pdf->page_script(function($pageNumber, $pageCount) {
                        echo "Oldal: $pageNumber / $pageCount";
                    });
                }
            </script>
        </div>
    </div>
</body>
</html>
