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

        .container {
            max-width: 700px;
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

    <form id="proxyForm">
        @csrf
        <div class="form-group">
            <label for="proxies">Введите прокси (IP:PORT, один на строку):</label>
            <textarea name="proxies" id="proxies" placeholder="Пример: 192.168.1.1:8080"></textarea>
        </div>
        <button type="button" class="button-primary" onclick="checkProxies()">Проверить Прокси</button>
    </form>

    <div id="results" class="results" style="display: none;"></div>
</div>

<script>
    async function checkProxies() {
        const proxies = document.getElementById('proxies').value;
        const resultsDiv = document.getElementById('results');

        try {
            const response = await fetch('/api/proxy/check', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '{{ csrf_token() }}'
                },
                body: JSON.stringify({proxies})
            });

            const data = await response.json();

            if (response.ok) {
                resultsDiv.style.display = 'block';
                resultsDiv.innerHTML = '<h4>Результаты:</h4>' +
                    data.results.map(result => `<p>${result}</p>`).join('');
            } else {
                resultsDiv.style.display = 'block';
                resultsDiv.innerHTML = `<p style="color:red;">Ошибка: ${data.message}</p>`;
            }
        } catch (error) {
            resultsDiv.style.display = 'block';
            resultsDiv.innerHTML = `<p style="color:red;">Произошла ошибка при проверке прокси.</p>`;
            console.error('Ошибка:', error);
        }
    }
</script>

</body>
</html>
