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

    <h1>Детали проверки #{{ $proxyCheck->id }}</h1>

    <p><strong>Статус:</strong> {{ $proxyCheck->completed ? 'Выполнена' : 'Невыполнена' }}</p>
    <p><strong>Дата создания:</strong> {{ $proxyCheck->created_at }}</p>

    <h2>Результаты</h2>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Прокси</th>
            <th>Статус</th>
            <th>Тип</th>
            <th>Город</th>
            <th>Скорость</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($proxyCheck->proxyResults as $result)
            <tr>
                <td>{{ $result->proxy }}</td>
                <td>{{ $result->status ? 'Активен' : 'Неактивен' }}</td>
                <td>{{ $result->type }}</td>
                <td>{{ $result->city }}</td>
                <td>{{ $result->speed }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

</body>
</html>
