## Renovation estimator

### Установка приложения

На борту контейнер с 4 сервисами:
1. sail-8.4 (php 8.4 + laravel 12)<br>
2. postgreSql 17<br>
3. Quasar 2.18<br>
4. Redis<br>

Для начала нужно установить php 8.4 + composer (у меня windows 10 + wsl2 (ubuntu 24.04)).<br>
В открытом windows terminal пишем:
<pre><code>sudo apt update
sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update</code>
</pre>

Перехожу в папку с проектом. Установить зависимости:
<pre><code>composer install</code></pre>

Скопировать .env.example в .env, и заменить значения переменных:
<pre><code>DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password</code></pre>

При необходимости нужно поменять порты так, чтобы не было конфликтов в .env:
<pre><code># Docker ports
APP_PORT=8010
FORWARD_DB_PORT=54410
FORWARD_REDIS_PORT=63810
QUASAR_PORT=9010
QUASAR_NODE=3410</code></pre>

Для запуска sail в папке с проектом выполнить:
<pre><code>sail up -d</code></pre>

Если команда не работает, т.к. не найдена, предварительно нужно создать алиас:
<pre><code>echo "alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'" >> ~/.bashrc
source ~/.bashrc
</code></pre>
а затем снова выполнить команду с sail. 

### laravel framework 12
Нужно сделать ключ приложения
<pre><code>sail artisan key:generate</code></pre>

выполнить миграции:
<pre><code>sail artisan migrate</code></pre>

Создаю папку **tests/Unit**, без него будет ошибка <br>

запускаю тесты бекэнда:
<pre><code>sail artisan test</code></pre>

### quasar framework 2.18
Поключаюсь к сервису контейнера, примерная команда имеет вид:
<pre><code>docker exec -it 593c571c890fc22c3b202ae5f83e3ea5083c21bb9af55ee108a34e20b7e7d861 sh</code></pre>

Устанавливаю зависимости
<pre><code>npm i</code></pre>

Запускаю quasar framework
<pre><code>npm run dev</code></pre>

Еще 1 раз подключаюсь к терминалу и выполняю тесты:
<pre><code>docker exec -it 593c571c890fc22c3b202ae5f83e3ea5083c21bb9af55ee108a34e20b7e7d861 sh
npm run test:unit</code></pre>

В данный момент приходится вручную запускать из контейнера сервис с quasar framework. 
Для исправления иду в docker-compose.yml в корне проект: <br>
раскомментирую строку
<pre><code>#command: sh -c "npm run dev"</code></pre>
закомментирую строку
<pre><code>command: ["node", "-e", "console.log('Node 22 is running'); setInterval(() => {}, 1000); "]</code></pre>

Чтобы увидеть результат работы фронтенда, нужно открыть localhost:9010, он может открыться нормально, а вот запросы 
проходить не будут, например чтобы войти в систему. 
<br>
Чтобы запросы проходили по нужному адрессу, нужно изменить порт, в данном случае это 8010. Это точно входа для бекенда. 
<br>
Его нужно прописать в этом файле
<pre><code>frontend_quasar/src/config.js</code></pre>
<pre><code>// src/config.js
export const API_CONFIG = {
  baseURL: 'http://localhost:8010/api',
};
</code></pre>

Чтобы у нас были какие нибудь стартовые данные нужно запустить сидер:
<pre><code>sail artisan db:seed</code></pre>
После этого можно войти в систему, используя любого пользователя из таблицы users. Паролем будет password

#### все готово! 
