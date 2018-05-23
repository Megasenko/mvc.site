<?php

class ModelBlog extends Model
{

    private $title;
    private $sub_title;
    private $content;
    private $created_at;
    private $url;
    private $author;
    private $role;


    /**
     * Получение всех статей
     * Get Articles
     *
     * @return array|bool
     */
    public function getArticles()
    {
        if ($this->connect()) {
            $sql = "SELECT articles.*, users.login AS authorLogin
                FROM articles 
                INNER JOIN users ON users.id = articles.author
                ";

            return $this->connect()->query($sql)->fetchAll(PDO::FETCH_OBJ);
        }

        return false;
    }

    /**
     * Получение статьи по URl адрессу
     * @param $url
     * @return mixed
     */
    public function getArticle($url)
    {

        if ($this->connect()) {
            $sql = "SELECT articles.*, users.login AS authorLogin
            FROM articles 
            INNER JOIN users ON users.id = articles.author WHERE url='$url'
            ";

            return $this->connect()->query($sql)->fetch(PDO::FETCH_OBJ);
        }
    }

    /**
     * Удаление статьи
     * @param $url
     * @return bool
     */
    public function deleteArticle($url)
    {

        if ($this->connect()) {
            $sql = "DELETE FROM articles WHERE url='$url'";

            return $this->connect()->prepare($sql)->execute();
        }

        return false;
    }

    /**
     * Обновление статьи
     * @param $dataArticle
     * @param $urlArticle
     * @return bool
     */
    public function updateArticle($urlArticle)
    {


        if (isset($_POST['update'])) {
            $this->title = (isset($_POST['title'])) ? $_POST['title'] : '';
            $this->sub_title = (isset($_POST['sub_title'])) ? $_POST['sub_title'] : '';
            $this->content = (isset($_POST['content'])) ? $_POST['content'] : '';
            $this->role = (isset($_POST['role'])) ? $_POST['role'] : '';
        }
        if ($this->connect()) {
            $title = strip_tags(trim($this->title));
            $sub_title = strip_tags(trim($this->sub_title));
            $content = strip_tags(trim($this->content));
            $role = strip_tags(trim($this->role));

            $sql = "UPDATE articles SET title='$title',sub_title='$sub_title',content='$content',
              role=$role WHERE url='$urlArticle'";

            return $this->connect()->prepare($sql)->execute();
        } else {
            header("Location : /");
            exit;
        }

    }

    /**
     * Добавление статьи в базу
     * @param $dataArticle
     * @return bool
     */
    public function insertArticle()
    {
        if (isset($_POST['add'])) {
            $this->title = (isset($_POST['title'])) ? $_POST['title'] : '';
            $this->sub_title = (isset($_POST['sub_title'])) ? $_POST['sub_title'] : '';
            $this->content = (isset($_POST['content'])) ? $_POST['content'] : '';
        }

        if ($this->connect()) {
            $sql = "INSERT INTO articles(title , sub_title , content , created_at , url , author , role)
            VALUES ( :title , :sub_title , :content , :created_at , :url , :author , :role)";

            $stmt = $this->connect()->prepare($sql);


            $datetime = new DateTime();
            $createdAt = $datetime->format('Y-m-d H:i:s');
            $url = $this->getUrl($this->title);
//            $author = $this->getAuthorArticle(); todo author
            $author=1;
            $role = 1;

            $stmt->bindValue(':title', strip_tags(trim($this->title)), PDO::PARAM_STR);
            $stmt->bindValue(':sub_title', strip_tags(trim($this->sub_title)), PDO::PARAM_STR);
            $stmt->bindValue(':content', strip_tags(trim($this->content)), PDO::PARAM_STR);
            $stmt->bindValue(':created_at', $createdAt, PDO::PARAM_STR);
            $stmt->bindValue(':url', $url, PDO::PARAM_STR);
            $stmt->bindValue(':author', $author, PDO::PARAM_STR);
            $stmt->bindValue(':role', $role, PDO::PARAM_STR);

            return $stmt->execute();
        }
        return false;
    }


    /**
     * автор для статьи
     * @return mixed
     */
    public function getAuthorArticle()
    {

        if ($this->connect()) {
            $a = (isset($_SESSION['login']))?$_SESSION['login']:'';
            $sql = "SELECT id
                FROM users
                WHERE login='$a'
                ";

            $row = $this->connect()->query($sql)->fetch(PDO::FETCH_ASSOC);
            return $row['id'];
        }


    }


    /**
     * получение статьи по URL
     * @param $str
     * @return bool|mixed
     *
     */
    function getArticleByUrl($str)
    {

        if ($this->connect()) {
            $sql = "SELECT *
                FROM articles
                WHERE url='$str'
                ";

            return $this->connect()->query($sql)->fetch(PDO::FETCH_ASSOC);
        }

        return false;
    }


    /**
     * поиск статьи по запросу пользователя
     * @param $search
     * @return array
     */
    function getArticleByUser($search)
    {
        $search = "%$search%";

        if ($this->connect) {
            $stm = $this->connect->prepare("SELECT * FROM articles WHERE (title LIKE '$search') 
                              OR (sub_title LIKE '$search') OR (content LIKE '$search')");
            $stm->execute(array($search));
            return $stm->fetchAll();
        }

    }


    /**
     * вывод статей по роли пользователя
     * @param $role
     * @return mixed
     */
    function getArticlesRole($role)
    {

        if ($this->connect) {
            $sql = "SELECT *
                FROM articles WHERE role='$role'";

            return $this->connect->query($sql)->fetchAll(PDO::FETCH_OBJ);
        }
    }

    /**
     * генерируем URL
     * @param $str
     * @return mixed|string
     */
    private function getUrl($str)
    {
        $articleUrl = str_replace(' ', '-', $str);
        $articleUrl = $this->transliteration($articleUrl);
        $articleIsset = $this->getArticleByUrl($articleUrl);
        if (!$articleIsset) {
            return $articleUrl;
        } else {
            $url = $articleIsset['url'];
            $exUrl = explode('-', $url);
            if ($exUrl) {
                $temp = (int)end($exUrl);
                $newUrl = $exUrl[0] . '-' . ++$temp;
            } else {
                $temp = 0;
                $newUrl = $articleUrl . '-' . ++$temp;
            }

            return $this->getUrl($newUrl);
        }
    }


    /**
     *  перевод тектса
     * @param $str
     * @return string
     */
    private function transliteration($str)
    {
        $st = strtr($str,
            array(
                'а' => 'a',
                'б' => 'b',
                'в' => 'v',
                'г' => 'g',
                'д' => 'd',
                'е' => 'e',
                'ё' => 'e',
                'ж' => 'zh',
                'з' => 'z',
                'и' => 'i',
                'к' => 'k',
                'л' => 'l',
                'м' => 'm',
                'н' => 'n',
                'о' => 'o',
                'п' => 'p',
                'р' => 'r',
                'с' => 's',
                'т' => 't',
                'у' => 'u',
                'ф' => 'ph',
                'х' => 'h',
                'ы' => 'y',
                'э' => 'e',
                'ь' => '',
                'ъ' => '',
                'й' => 'y',
                'ц' => 'c',
                'ч' => 'ch',
                'ш' => 'sh',
                'щ' => 'sh',
                'ю' => 'yu',
                'я' => 'ya',
                ' ' => '_',
                '<' => '_',
                '>' => '_',
                '?' => '_',
                '"' => '_',
                '=' => '_',
                '/' => '_',
                '|' => '_'
            )
        );
        $st2 = strtr($st,
            array(
                'А' => 'a',
                'Б' => 'b',
                'В' => 'v',
                'Г' => 'g',
                'Д' => 'd',
                'Е' => 'e',
                'Ё' => 'e',
                'Ж' => 'zh',
                'З' => 'z',
                'И' => 'i',
                'К' => 'k',
                'Л' => 'l',
                'М' => 'm',
                'Н' => 'n',
                'О' => 'o',
                'П' => 'p',
                'Р' => 'r',
                'С' => 's',
                'Т' => 't',
                'У' => 'u',
                'Ф' => 'ph',
                'Х' => 'h',
                'Ы' => 'y',
                'Э' => 'e',
                'Ь' => '',
                'Ъ' => '',
                'Й' => 'y',
                'Ц' => 'c',
                'Ч' => 'ch',
                'Ш' => 'sh',
                'Щ' => 'sh',
                'Ю' => 'yu',
                'Я' => 'ya'
            )
        );
        $translit = $st2;

        return $translit;
    }


    /**
     * Все что касается пользователей
     */
    public function getUsers()
    {
        if ($this->connect) {
            $sql = "SELECT *
                FROM users
                ";

            return $this->connect->query($sql)->fetchAll(PDO::FETCH_OBJ);
        }
    }

    /**
     * Добавление пользователей в базу при регистрации
     * @return bool
     */
    public function insertUser($userData)
    {

        if ($this->connect) {
            $role = 3;
            $password = md5($userData['password']);
            $sql = "INSERT INTO users(name, last_name, login , email , password, role)
        VALUES ( :name, :last_name , :login , :email , :password , :role)";

            $stmt = $this->connect->prepare($sql);
            $stmt->bindParam(':name', $userData['name'], PDO::PARAM_STR);
            $stmt->bindParam(':last_name', $userData['last_name'], PDO::PARAM_STR);
            $stmt->bindParam(':login', $userData['login'], PDO::PARAM_STR);
            $stmt->bindParam(':email', $userData['email'], PDO::PARAM_STR);
            $stmt->bindParam(':password', $password, PDO::PARAM_STR);
            $stmt->bindParam(':role', $role, PDO::PARAM_STR);
            return $stmt->execute();
        }
    }


    /**
     * Регистрация пользователей
     * @param array $userData
     * @return bool|void
     */
    public function registerUser(array $userData)
    {


        if (!isset($userData['login']) || empty ($userData['login'])) {
            $_SESSION['error_message'] = 'Login can not be empty';
            return;
        }
        if (!isset($userData['email']) || empty ($userData['email'])) {
            $_SESSION['error_message'] = 'Email can not be empty';
            return;
        }
        if (!isset($userData['password']) || empty ($userData['passwordConfirm'])) {
            $_SESSION['error_message'] = 'Password can not be empty';
            return;
        }
        if ($userData['password'] !== $userData['passwordConfirm']) {
            $_SESSION['error_message'] = 'Inputted passwords not confirm!';
            return;
        }

        if ($this->insertUser($userData)) {
            $_SESSION['error_message'] = false;
            return true;
        } else {
            $_SESSION['error_message'] = 'Register user not complete';

        }

    }


    /**
     * Извлечение пользователя с базы по логину
     * @param array $userData
     * @return array
     *
     */
    public function getLogin(array $userData)
    {


        $sql = 'SELECT * FROM users WHERE login = :login';

        $stmt = $this->connect->prepare($sql);
        $stmt->bindValue(':login', $userData['login'], PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Вход пользователя на сайт и добавление значений в сессию
     * @param array $userData
     *
     */
    public function auth(array $userData)
    {
        $_SESSION['access'] = false;
        if (!isset($userData['login']) || empty ($userData['login'])) {
            $_SESSION['error_message'] = 'Login can not be empty';
            return;
        }
        if (!isset($userData['password']) || empty ($userData['password'])) {
            $_SESSION['error_message'] = 'Password can not be empty';
            return;
        }
        if ($this->getLogin($userData)) {
            $rows = $this->getLogin($userData);
            if (count($rows) > 0) {
                if (md5($userData['password']) == $rows[0]['password']) {
                    if ($rows[0]['login'] === 'admin') {
                        $_SESSION['access'] = true;
                        $role = $rows[0]['role'] * 1;
                        $_SESSION['role'] = $role;
                        $_SESSION['author'] = $rows[0]['id'];
                        $_SESSION['login'] = $rows[0]['login'];
                        $_SESSION['email'] = $rows[0]['email'];
                        $_SESSION['name'] = $rows[0]['name'];
                        header('Location:/admin/main.php');
                        exit;
                    } else {
                        $_SESSION['access'] = true;
                        $_SESSION['role'] = $rows[0]['role'];
                        $_SESSION['author'] = $rows[0]['id'];
                        $_SESSION['login'] = $rows[0]['login'];
                        $_SESSION['name'] = $rows[0]['name'];
                        $_SESSION['email'] = $rows[0]['email'];
                        header('Location:/');
                        exit;
                    }

                } else {
                    $_SESSION['error_message'] = 'You entered the wrong password ';
                }
            }
        } else {
            $_SESSION['error_message'] = 'Логин <b>' . $userData['login'] . '</b> не найден!';

        }


    }

    /**
     *  Удаление пользователя
     * @param $id
     * @return bool
     */
    public function deleteUser($id)
    {

        if ($this->connect) {
            $sql = "DELETE FROM users WHERE id=$id";

            return $this->connect->prepare($sql)->execute();
        }

        return false;
    }

    /**
     * обновление информации о пользователе или установка роли
     * @param $userData
     * @param $id
     * @return bool
     *
     */
    public function updateRole($userData, $id)
    {

        if ($this->connect) {

            $role = $userData['role'];

            $sql = "UPDATE users SET role=$role WHERE id='$id'";

            return $this->connect->prepare($sql)->execute();
        }
        return false;


    }


    /**
     * Выбор одного пользователя по id
     * @param $id
     * @return array
     *
     */
    public function getUser($id)
    {

        if ($this->connect) {
            $sql = "SELECT *
            FROM users WHERE id='$id'";
            return $this->connect->query($sql)->fetch(PDO::FETCH_OBJ);
        }
    }

    /**
     * Доступ на страничку только админу
     */
    public function accessAdmin()
    {
        if ($_SESSION['role'] !== 1) {
            header('Location: /');
            exit;
        }
    }
}