<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Проверка Прокси</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/milligram/1.4.1/milligram.min.css">
    <style>
        body {
            padding-top: 2rem;
        }

        textarea {
            width: 100%;
            height: 150px;
            resize: vertical;
        }

        p {
            margin-bottom: 0.5rem;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .results {
            margin-top: 1rem;
            padding: 1rem;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
    </style>
</head>

<body>
<div class="container">
    <x-header/>

    <h1>История проверок прокси</h1>

    <table class="table table-bordered">
        <thead>
        <tr>
            <th>ID</th>
            <th>Статус</th>
            <th>Дата создания</th>
            <th>Количество прокси</th>
            <th>Активные прокси</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($proxyChecks as $check)
            <tr>
                <td>{{ $check->id }}</td>
                <td>{{ $check->completed ? 'Выполнена' : 'Невыполнена' }}</td>
                <td>{{ $check->created_at }}</td>
                <td>{{ $check->proxyResults()->count() }}</td>
                <td>{{ $check->proxyResults()->where('status', true)->count() }}</td>
                <td>
                    <a href="{{ route('check.details', $check->id) }}" class="btn btn-info">Просмотреть</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

</body>
</html>
